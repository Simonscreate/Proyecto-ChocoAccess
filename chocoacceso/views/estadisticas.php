<?php
session_start();
// Protección de ruta estándar RBAC
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

// 1. Cargar Datos para Gráfica de Departamentos
$datos_deptos = $mov->obtenerPersonasPorDepartamento();

// 2. Cargar Datos para Filtro de Usuario Específico
$id_usuario_buscar = $_GET['id_usuario'] ?? null;
$accesos_usuario = [];
if ($id_usuario_buscar) {
    $accesos_usuario = $mov->obtenerAccesosPorUsuario($id_usuario_buscar);
}

// 3. PROCESAMIENTO ESTADÍSTICO (Media, Mediana y Moda de accesos diarios generales)
$muestra = $mov->obtenerFrecuenciasAccesosDiarios(); // Array ya ordenado de menor a mayor

$media = 0;
$mediana = 0;
$moda = "No definida";

$n = count($muestra);

if ($n > 0) {
    // A. Cálculo de la Media (Promedio)
    $media = array_sum($muestra) / $n;

    // B. Cálculo de la Mediana (Dato central)
    $mitad = floor($n / 2);
    if ($n % 2 !== 0) {
        $mediana = $muestra[$mitad]; // Posición central impar
    } else {
        $mediana = ($muestra[$mitad - 1] + $muestra[$mitad]) / 2; // Promedio de los dos centrales pares
    }

    // C. Cálculo de la Moda (Valor con mayor frecuencia)
    $valores_frecuencias = array_count_values($muestra);
    arsort($valores_frecuencias); // Ordena de mayor a menor frecuencia
    $max_frecuencia = reset($valores_frecuencias);
    
    // Validar si es unimodal o si no hay moda (todos se repiten igual)
    if (count(array_unique($valores_frecuencias)) > 1) {
        $modas = array_keys($valores_frecuencias, $max_frecuencia);
        $moda = implode(", ", $modas) . " (Aparece " . $max_frecuencia . " veces)";
    } else {
        $moda = "No hay moda (Distribución uniforme)";
    }
}

include "../includes/navbar.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>ChocoAcceso - Panel Estadístico</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .grid-estadisticas { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
        .card-analytics { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .metric-box { display: flex; justify-content: space-around; margin-top: 15px; }
        .metric { text-align: center; background: #fdf6f0; padding: 15px; border-radius: 6px; border-top: 4px solid var(--choco-gold); min-width: 100px; }
        .metric h4 { margin: 0 0 5px 0; color: #555; font-size: 0.85rem; }
        .metric p { margin: 0; font-size: 1.4rem; font-weight: bold; color: var(--choco-dark); }
    </style>
</head>
<body>
    <main class="container">
        <header>
            <h2>Módulo de Analítica Industrial</h2>
            <p>Monitoreo estadístico de tráfico y distribución en Chocolates El Rey.</p>
        </header>

        <section class="card-analytics" style="margin-bottom: 20px;">
            <h3>Tendencia Central de Accesos Diarios a Planta</h3>
            <p style="font-size: 0.9rem; color:#666;">Cálculos matemáticos puros aplicados sobre la muestra histórica de eventos.</p>
            <div class="metric-box">
                <div class="metric">
                    <h4>Media (μ)</h4>
                    <p><?php echo number_format($media, 2); ?></p>
                </div>
                <div class="metric">
                    <h4>Mediana (Me)</h4>
                    <p><?php echo number_format($mediana, 1); ?></p>
                </div>
                <div class="metric">
                    <h4>Moda (Mo)</h4>
                    <p style="font-size: 1.1rem; padding-top: 5px;"><?php echo $moda; ?></p>
                </div>
            </div>
        </section>

        <div class="grid-estadisticas">
            <article class="card-analytics">
                <h3>Personal Actual por Departamento</h3>
                <canvas id="chartDeptos"></canvas>
            </article>

            <article class="card-analytics">
                <h3>Historial de Áreas Frecuentadas por Usuario</h3>
                <form method="GET" style="margin-bottom: 15px; display: flex; gap: 10px;">
                    <input type="number" name="id_usuario" class="form-control" placeholder="ID de Usuario" 
                           value="<?php echo $id_usuario_buscar; ?>" required>
                    <button type="submit" class="btn-submit" style="width: auto; margin:0;">Analizar</button>
                </form>
                
                <?php if ($id_usuario_buscar): ?>
                    <canvas id="chartUsuario"></canvas>
                <?php else: ?>
                    <p style="text-align: center; color: #999; margin-top: 50px;">Ingrese un ID de usuario para mapear su comportamiento espacial.</p>
                <?php endif; ?>
            </article>
        </div>
    </main>

    <script>
        // Configuración Gráfica 1: Departamentos
        const datosDeptos = <?php echo json_encode($datos_deptos); ?>;
        const labelsDeptos = datosDeptos.map(item => item.departamento);
        const valoresDeptos = datosDeptos.map(item => item.total);

        new Chart(document.getElementById('chartDeptos'), {
            type: 'bar',
            data: {
                labels: labelsDeptos,
                datasets: [{
                    label: 'Personas en área',
                    data: valoresDeptos,
                    backgroundColor: ['#3e2723', '#d4af37', '#795548', '#ff9800'],
                    borderWidth: 1
                }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
        });

        // Configuración Gráfica 2: Usuario (Solo si se buscó uno)
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
            options: { responsive: true }
        });
        <?php endif; ?>
    </script>
</body>
</html>