<?php
require_once "../config/Database.php";
require_once "../models/Usuario.php";
require_once "../models/Movimiento.php";

$db = (new Database())->Conexion();
$usuario = new Usuario($db);
$movimiento = new Movimiento($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cedula'])) {
    $cedula = trim($_POST['cedula']);
    $tipo = $_POST['tipo'];
    $depto = $_POST['departamento'];
    
    $datosUser = $usuario->validarCedula($cedula);

    if ($datosUser && $datosUser['activo'] == 1) {
        $rol = $datosUser['rol'];

        // --- LÓGICA DE NIVEL DE ACCESO (Seguridad Industrial) ---
        $acceso_autorizado = true;
        $error_msg = "";

        // Regla: Visitantes NO entran a Producción o Almacén
        if ($rol === 'Visitante' && ($depto === 'PRODUCCION' || $depto === 'ALMACEN')) {
            $acceso_autorizado = false;
            $error_msg = "Acceso_Denegado_Area_Restringida";
        }

        // Regla: Personal puede entrar a todo excepto Gerencia (si no es su rol)
        if ($rol === 'Personal' && $depto === 'GERENCIA') {
            $acceso_autorizado = false;
            $error_msg = "Acceso_Solo_Gerentes";
        }

        if (!$acceso_autorizado) {
            header("Location: ../views/login.php?status=denied&msg=$error_msg");
            exit();
        }
        // --- FIN DE LÓGICA DE SEGURIDAD ---

        // 1. Registrar en Historial
        $movimiento->id_usuario = $datosUser['id_usuario'];
        $movimiento->id_operador = 1; 
        $movimiento->tipo_movimiento = $tipo;
        $movimiento->observaciones = "Ubicación: " . $depto;

        if ($movimiento->registrar()) {
            // 2. Sincronizar Estado Actual
            $nueva_ubi = ($tipo == 'SALIDA') ? 'FUERA' : $depto;
            $sql_estado = "INSERT INTO estado_planta (id_usuario, ubicacion_actual) 
                           VALUES (:id_u, :ubi) 
                           ON DUPLICATE KEY UPDATE ubicacion_actual = :ubi";
            $stmt = $db->prepare($sql_estado);
            $stmt->execute([':id_u' => $datosUser['id_usuario'], ':ubi' => $nueva_ubi]);

            header("Location: ../views/login.php?status=success&nombre=" . urlencode($datosUser['nombre_completo']));
            exit();
        }
    } else {
        header("Location: ../views/login.php?status=not_found");
        exit();
    }
}