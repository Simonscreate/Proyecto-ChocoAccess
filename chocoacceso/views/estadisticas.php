<?php
session_start();

// 1. Protección de ruta estándar mediante Control de Acceso Basado en Roles (RBAC)
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['Administrador', 'Gerencia'])) {
    header("Location: dashboard.php");
    exit();
}

require_once "../config/Database.php";
require_once "../models/Movimiento.php";
require_once "../models/Usuario.php";

$db = (new Database())->Conexion();
$mov = new Movimiento($db);
$usr = new Usuario($db);

// 2. Cargar Datos para Gráfica 1: Distribución actual de Planta
$datos_deptos = $mov->obtenerPersonasPorDepartamento();

// 3. Cargar Datos para Gráfica 2: Historial por Cédula (Búsqueda UX optimizada)
$cedula_buscar = $_GET['cedula'] ?? null;
$accesos_usuario = [];

if ($cedula_buscar) {
    $cedula_buscar = trim($cedula_buscar);
    $accesos_usuario = $mov->obtenerAccesosPorCedula($cedula_buscar);
}

// 4. PROCESAMIENTO ESTADÍSTICO (Media, Mediana y Moda de accesos diarios generales)
$muestra = $mov->obtenerFrecuenciasAccesosDiarios(); // Array ordenado numéricamente gracias al SQL

$media = 0;
$mediana = 0;
$moda = "No definida";
$n = count($muestra);

if ($n > 0) {
    // A. Cálculo de la Media (Promedio Aritmético)
    $media = array_sum($muestra) / $n;

    // B. Cálculo de la Mediana (Dato de posición central)
    $mitad = floor($n / 2);
    if ($n % 2 !== 0) {
        $mediana = $muestra[$mitad]; // Universo Impar: posición central directa
    } else {
        $mediana = ($muestra[$mitad - 1] + $muestra[$mitad]) / 2; // Universo Par: promedio de centros
    }

    // C. Cálculo de la Moda (Puntuación con mayor frecuencia absoluta)
    $valores_frecuencias = array_count_values($muestra);
    arsort($valores_frecuencias); // Ordena de mayor a menor según su repetición
    $max_frecuencia = reset($valores_frecuencias);
    
    // Verificación de existencia real de moda (evitar distribuciones uniformes)
    if (count(array_unique($valores_frecuencias)) > 1) {
        $modas = array_keys($valores_frecuencias, $max_frecuencia);
        $moda = implode(", ", $modas) . " (" . $max_frecuencia . " rep.)";
    } else {
        $moda = "No hay moda";
    }
}

include "../includes/navbar.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>ChocoAcceso - Panel Analítico e Industrial</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .grid-estadisticas { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 20px; 
            margin-top: 20px; 
        }
        .card-analytics { 
            background: white; 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.05); 
        }
        .metric-box { 
            display: flex; 
            justify-content: space-around; 
            margin-top: 15px; 
        }
        .metric { 
            text-align: center; 
            background: #fdf6f0; 
            padding: 15px; 
            border-radius: 6px; 
            border-top: 4px solid var(--choco-gold, #d4af37); 
            min-width: 120px; 
        }
        .metric h4 { 
            margin: 0 0 5px 0; 
            color: #666; 
            font-size: 0.85rem; 
        }
        .metric p { 
            margin: 0; 
            font-size: 1.5rem; 
            font-weight: bold; 
            color: #3e2723; 
        }
    </style>
</head>
<body>
    <main class="container">
        <header style="margin-bottom: 25px;">
            <h2>Módulo de Analítica y Tendencia Central</h2>
            <p>Monitoreo algorítmico y matemático de flujos en Chocolates El Rey.</p>
        </header>

        <section class="card-analytics" style="margin-bottom: 25px;">
            <h3>Tendencia Central de Accesos Diarios a Planta</h3>
            <p style="font-size: 0.85rem; color:#777; margin-bottom: 15px;">
                Métricas descriptivas inferidas sobre el volumen total de ingresos físicos ejecutados por jornada de trabajo.
            </p>
            <div class="metric-box">
                <div class="metric">
                    <h4>Media (Promedio)</h4>
                    <p><?php echo number_format($media, 2); ?></p>
                </div>
                <div class="metric">
                    <h4>Mediana (Centro)</h4>
                    <p><?php echo number_format($mediana, 1); ?></p>
                </div>
                <div class="metric">
                    <h4>Moda (Repetición)</h4>
                    <p style="font-size: 1.1rem; padding-top: 5px;"><?php echo htmlspecialchars($moda); ?></p>
                </div>
            </div>
        </section>

        <div class="grid-estadisticas">
            <article class="card-analytics">
                <h3>Personal Técnico Actual por Departamento</h3>
                <div style="position: relative; margin: auto; height: 300px; width: 100%;">
                    <canvas id="chartDeptos"></canvas>
                </div>
            </article>

            <article class="card-analytics">
                <h3>Historial de Espacios Frecuentados por Personal</h3>
                <form method="GET" style="margin-bottom: 20px; display: flex; gap: 10px;">
                    <input type="text" name="cedula" class="form-control" placeholder="Ej: V-12345678" 
                           value="<?php echo htmlspecialchars($cedula_buscar); ?>" required style="margin: 0;">
                    <button type="submit" class="btn-submit" style="width: auto; margin: 0; padding: 10px 20px;">Filtrar</button>
                </form>
                
                <div style="position: relative; margin: auto; height: 260px; width: 100%;">
                    <?php if ($cedula_buscar): ?>
                        <?php if (!empty($accesos_usuario)): ?>
                            <canvas id="chartUsuario"></canvas>
                        <?php else: ?>
                            <p style="text-align: center; color: #ffa000; padding-top: 60px;">No se registran entradas físicas asociadas a esa cédula.</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #999; padding-top: 60px;">Introduzca una Cédula de Identidad para mapear su comportamiento espacial.</p>
                    <?php endif; ?>
                </div>
            </article>
        </div>
    </main>

    <script>
        // Data inyectada desde el Modelo PHP para el Gráfico 1
        const datosDeptos = <?php echo json_encode($datos_deptos); ?>;
        const labelsDeptos = datosDeptos.map(item => item.departamento);
        const valoresDeptos = datosDeptos.map(item => item.total);

        new Chart(document.getElementById('chartDeptos'), {
            type: 'bar',
            data: {
                labels: labelsDeptos,
                datasets: [{
                    label: 'Cantidad de Personas',
                    data: valoresDeptos,
                    backgroundColor: ['#3e2723', '#d4af37', '#795548', '#ff9800'],
                    borderColor: '#3e2723',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });

        // Data inyectada desde el Modelo PHP para el Gráfico 2 (Solo si existe búsqueda válida)
        <?php if (!empty($accesos_usuario)): ?>
        const datosUser = <?php echo json_encode($accesos_usuario); ?>;
        const labelsUser = datosUser.map(item => item.departamento);
        const valoresUser = datosUser.map(item => item.accesos);

        new Chart(document.getElementById('chartUsuario'), {
            type: 'doughnut',
            data: {
                labels: labelsUser,
                datasets: [{
                    data: valoresUser,
                    backgroundColor: ['#2e7d32', '#c62828', '#1565c0', '#ef6c00', '#6a1b9a']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>