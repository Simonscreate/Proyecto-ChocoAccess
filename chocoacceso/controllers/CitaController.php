<?php
session_start();
require_once "../config/Database.php";
require_once "../models/Cita.php";
require_once "../models/Usuario.php";

// CONTROL DE EXCEPCIÓN PARA LA API DE GOOGLE:
// Si Composer está instalado en el entorno actual, cargamos el SDK. 
// Si no existe (como en una instalación limpia local), el sistema no se cae.
if (file_exists("../vendor/autoload.php")) {
    require_once "../vendor/autoload.php";
    $google_api_disponible = true;
} else {
    $google_api_disponible = false;
}

$db = (new Database())->Conexion();
$cita = new Cita($db);
$usuario = new Usuario($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Protección de ruta mediante Control de Acceso Basado en Roles (RBAC)
    if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['Administrador', 'Gerencia'])) {
        header("Location: ../views/calendario.php?status=forbidden");
        exit();
    }

    // 2. Capturar y limpiar la Cédula introducida en el formulario
    $cedula_ingresada = isset($_POST['cedula']) ? trim($_POST['cedula']) : '';
    
    if (empty($cedula_ingresada)) {
        header("Location: ../views/calendario.php?status=error");
        exit();
    }
    
    // 3. Validar existencia del usuario/visitante mediante la Cédula
    $datos_usuario = $usuario->validarCedula($cedula_ingresada);

    if (!$datos_usuario) {
        // Redirección UX controlada si la cédula no pertenece a nadie en el sistema
        header("Location: ../views/calendario.php?status=user_not_found");
        exit();
    }

    // 4. BLOQUE ASÍNCRONO: SINCRONIZACIÓN CON GOOGLE CALENDAR API
    $google_id_guardado = null;
    
    if ($google_api_disponible) {
        try {
            // Instanciar el cliente oficial de Google
            $client = new Google\Client();
            $client->setAuthConfig('../config/credentials.json'); // Archivo de credenciales Cloud
            $client->addScope(Google\Service\Calendar::CALENDAR);
            
            $service = new Google\Service\Calendar($client);
            
            // Convertir la fecha y hora al formato internacional ISO 8601 (-04:00 Zona Horaria Venezuela)
            $fecha_inicio_iso = $_POST['fecha_cita'] . 'T' . $_POST['hora_cita'] . ':00-04:00';
            $fecha_fin_iso = date('Y-m-d\TH:i:s', strtotime($fecha_inicio_iso . ' + 1 hour')) . '-04:00';
            
            // Construir la estructura meta del evento para los servidores de Google
            $event = new Google\Service\Calendar\Event([
                'summary' => 'ChocoAcceso - Control de Visita',
                'location' => 'Chocolates El Rey - Área: ' . $_POST['departamento_destino'],
                'description' => 'Motivo de ingreso: ' . $_POST['motivo'] . ' | Asistente: ' . $datos_usuario['nombre_completo'],
                'start' => [
                    'dateTime' => $fecha_inicio_iso,
                    'timeZone' => 'America/Caracas',
                ],
                'end' => [
                    'dateTime' => $fecha_fin_iso,
                    'timeZone' => 'America/Caracas',
                ],
            ]);
            
            // Insertar en el calendario principal de la cuenta corporativa
            $resultado_google = $service->events->insert('primary', $event);
            
            // Extraer el ID único generado en la nube para guardarlo de forma relacional
            $google_id_guardado = $resultado_google->getId();
            
        } catch (Exception $e) {
            // Si la API falla por falta de internet, guardamos el log para auditoría técnica
            error_log("Error controlado de conexión con Google API: " . $e->getMessage());
        }
    }
    // -----------------------------------------------------------------

    // 5. MAPEO DE ATRIBUTOS E INSERCIÓN EN LA BASE DE DATOS LOCAL (PERSISTENCIA)
    $cita->id_usuario = $datos_usuario['id_usuario'];
    $cita->id_administrador = $_SESSION['id_usuario']; // Admin que realiza la sesión activa
    $cita->fecha_cita = $_POST['fecha_cita'];
    $cita->hora_cita = $_POST['hora_cita'];
    $cita->departamento_destino = $_POST['departamento_destino'];
    $cita->motivo = $_POST['motivo'];
    $cita->google_event_id = $google_id_guardado; // Será NULL si el SDK de Google no estaba listo

    // Ejecutar la transacción en MariaDB/MySQL
    if ($cita->crear()) {
        header("Location: ../views/calendario.php?status=success");
    } else {
        header("Location: ../views/calendario.php?status=error");
    }
    exit();
} else {
    // Si intentan entrar directo al controlador por URL (GET), rebote preventivo
    header("Location: ../views/calendario.php");
    exit();
}
?>