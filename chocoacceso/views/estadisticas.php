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

// 2. Capturar el intervalo seleccionado para Tendencia Central (Por defecto: dia)
$intervalo = $_GET['intervalo'] ?? 'dia';

// 3. Cargar Datos para el Croquis y Gráfica 1 (Estado en Caliente de la Planta)
$datos_deptos = $mov->obtenerPersonasPorDepartamento();

// Convertir los datos a un array asociativo indexado por departamento para el Croquis CSS
$ocupacion_mapa = [];
foreach ($datos_deptos as $d) {
    $ocupacion_mapa[strtoupper($d['departamento'])] = $d['total'];
}

// Asegurar que los 4 departamentos principales existan en el array para evitar warnings
$areas_planta = ['ADMINISTRACION', 'PRODUCCION', 'ALMACEN', 'MANTENIMIENTO'];
foreach ($areas_planta as $area) {
    if (!isset($ocupacion_mapa[$area])) {
        $ocupacion_mapa[$area] = 0;
    }
}

// 4. Cargar Datos para Gráfica 2: Historial por Cédula (Búsqueda UX optimizada)
$cedula_buscar = $_GET['cedula'] ?? null;
$accesos_usuario = [];

if ($cedula_buscar) {
    $cedula_buscar = trim($cedula_buscar);
    $accesos_usuario = $mov->obtenerAccesosPorCedula($cedula_buscar);
}

// 5. PROCESAMIENTO ESTADÍSTICO DINÁMICO (Media, Mediana y Moda)
// Nota: Recuerda haber cambiado en Movimiento.php el fetchAll a: fetchAll(PDO::FETCH_COLUMN, 0)
$muestra = $mov->obtenerFrecuenciasAnaliticas($intervalo); 

$media = 0;
$mediana = 0;
$moda = "No definida";
$n = count($muestra);

if ($n > 0) {
    // Cálculo de la Media Aritmética
    $media = array_sum($muestra) / $n;

    // Algoritmo de la Mediana (Muestra previamente ordenada por el SQL mediante ORDER BY)
    $mitad = floor($n / 2);
    if ($n % 2 !== 0) {
        $mediana = $muestra[$mitad]; // Cantidad impar
    } else {
        $mediana = ($muestra[$mitad - 1] + $muestra[$mitad]) / 2; // Cantidad par
    }

    // Identificación de la Moda
    if ($intervalo === 'depto') {
        $moda = $mov->obtenerDepartamentoModa();
    } else {
        $valores_frecuencias = array_count_values($muestra);
        arsort($valores_frecuencias); 
        $max_frecuencia = reset($valores_frecuencias);
        
        if (count(array_unique($valores_frecuencias)) > 1) {
            $modas = array_keys($valores_frecuencias, $max_frecuencia);
            $moda = implode(", ", $modas) . " (" . $max_frecuencia . " rep.)";
        } else {
            $moda = "No hay moda (Uniforme)";
        }
    }
}

include "../includes/navbar.php";

// Función helper exclusiva de la vista para determinar el gradiente de calor del croquis (Heatmap)
function obtenerColorCalor($cantidad) {
    if ($cantidad == 0) return '#e8f5e9'; // Verde muy claro / Vacío
    if ($cantidad <= 2) return '#c8e6c9'; // Verde / Baja concurrencia
    if ($cantidad <= 5) return '#ffe082'; // Amarillo / Tránsito moderado
    return '#ffcdd2'; // Rojo suave / Alta densidad o aforo lleno
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>ChocoAcceso - Panel Analítico de Planta</title>
    <link rel="stylesheet" href="../css/styleV.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .grid-estadisticas { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
        .card-analytics { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .metric-box { display: flex; justify-content: space-around; margin-top: 15px; }
        .metric { text-align: center; background: #fdf6f0; padding: 15px; border-radius: 6px; border-top: 4px solid #d4af37; min-width: 130px; }
        .metric h4 { margin: 0 0 5px 0; color: #666; font-size: 0.85rem; }
        .metric p { margin: 0; font-size: 1.6rem; font-weight: bold; color: #3e2723; }
        
        /* ESTILOS DEL CROQUIS ARQUITECTÓNICO INDUSTRIAL */
        .croquis-planta {
            display: grid;
            grid-template-columns: 1.2fr 1.8fr 1fr;
            grid-template-rows: 150px 150px;
            gap: 15px;
            background: #fdfdfd;
            padding: 20px;
            border-radius: 8px;
            border: 3px dashed #3e2723;
            margin-top: 15px;
            position: relative;
        }
        .croquis-planta::before {
            content: "🚪 ACCESO / PORTERÍA PRINCIPAL";
            position: absolute;
            bottom: -12px;
            left: 38%;
            background: #3e2723;
            color: #d4af37;
            padding: 2px 14px;
            font-size: 0.75rem;
            font-weight: bold;
            border-radius: 4px;
            border: 1px solid #d4af37;
            z-index: 10;
        }
        .zona-croquis {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border: 2px solid #3e2723;
            border-radius: 4px;
            position: relative;
            transition: all 0.3s ease;
        }
        .zona-croquis:hover {
            filter: brightness(0.96);
            box-shadow: 0 4px 10px rgba(62, 39, 35, 0.12);
        }
        .croquis-label { font-weight: bold; font-size: 0.85rem; color: #3e2723; letter-spacing: 0.5px; text-align: center; }
        .croquis-contador { font-size: 2.2rem; font-weight: 900; margin-top: 5px; color: #2e7d32; }
        .croquis-contador.alerta { color: #c62828; }
        
        /* Proporciones de las áreas dentro del croquis */
        .area-admin { grid-column: 1; grid-row: 1 / span 2; border-right: 4px double #3e2723; }
        .area-produccion { grid-column: 2; grid-row: 1 / span 2; }
        .area-almacen { grid-column: 3; grid-row: 1; }
        .area-mantenimiento { grid-column: 3; grid-row: 2; border-top: 2px dashed #3e2723; }
    </style>
</head>
<body>
    <main class="container">
        <header style="margin-bottom: 25px;">
            <h2>Módulo de Analítica y Tendencia Central</h2>
            <p>Monitoreo algorítmico e indicadores de flujo para Chocolates El Rey.</p>
        </header>

        <section class="card-analytics">
            <h3>Croquis de Distribución Espacial y Densidad (Tiempo Real)</h3>
            <p style="font-size: 0.85rem; color:#777; margin-bottom: 15px;">
                Representación arquitectónica de la planta de producción. Las zonas varían su color de forma algorítmica según la densidad del aforo en vivo.
            </p>
            
            <div class="croquis-planta">
                <div class="zona-croquis area-admin" style="background-color: <?php echo obtenerColorCalor($ocupacion_mapa['ADMINISTRACION']); ?>;">
                    <span class="croquis-label">📁 DPTO.<br>ADMINISTRACIÓN</span>
                    <span class="croquis-contador <?php echo $ocupacion_mapa['ADMINISTRACION'] > 5 ? 'alerta' : ''; ?>">
                        <?php echo $ocupacion_mapa['ADMINISTRACION']; ?><small style="font-size:0.9rem; font-weight:normal;"> Pers.</small>
                    </span>
                </div>

                <div class="zona-croquis area-produccion" style="background-color: <?php echo obtenerColorCalor($ocupacion_mapa['PRODUCCION']); ?>;">
                    <span class="croquis-label">🏭 PLANTA CENTRAL<br>DE PRODUCCIÓN</span>
                    <span class="croquis-contador <?php echo $ocupacion_mapa['PRODUCCION'] > 5 ? 'alerta' : ''; ?>">
                        <?php echo $ocupacion_mapa['PRODUCCION']; ?><small style="font-size:0.9rem; font-weight:normal;"> Pers.</small>
                    </span>
                </div>

                <div class="zona-croquis area-almacen" style="background-color: <?php echo obtenerColorCalor($ocupacion_mapa['ALMACEN']); ?>;">
                    <span class="croquis-label">📦 ALMACÉN M.P.</span>
                    <span class="croquis-contador <?php echo $ocupacion_mapa['ALMACEN'] > 5 ? 'alerta' : ''; ?>">
                        <?php echo $ocupacion_mapa['ALMACEN']; ?><small style="font-size:0.9rem; font-weight:normal;"> Pers.</small>
                    </span>
                </div>

                <div class="zona-croquis area-mantenimiento" style="background-color: <?php echo obtenerColorCalor($ocupacion_mapa['MANTENIMIENTO']); ?>;">
                    <span class="croquis-label">🛠️ SOPORTE TÉCNICO</span>
                    <span class="croquis-contador <?php echo $ocupacion_mapa['MANTENIMIENTO'] > 5 ? 'alerta' : ''; ?>">
                        <?php echo $ocupacion_mapa['MANTENIMIENTO']; ?><small style="font-size:0.9rem; font-weight:normal;"> Pers.</small>
                    </span>
                </div>
            </div>
        </section>

        <section class="card-analytics">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div>
                    <h3>Tendencia Central de Accesos a Planta</h3>
                    <p style="font-size: 0.85rem; color:#777; margin: 0;">Resumen descriptivo de comportamiento operacional.</p>
                </div>
                
                <form method="GET" action="estadisticas.php" id="formIntervalo">
                    <?php if ($cedula_buscar): ?>
                        <input type="hidden" name="cedula" value="<?php echo htmlspecialchars($cedula_buscar); ?>">
                    <?php endif; ?>
                    <label style="font-size: 0.85rem; font-weight: bold; color: #555;">Agrupar por:</label>
                    <select name="intervalo" class="form-control" onchange="document.getElementById('formIntervalo').submit()" style="display: inline-block; width: auto; margin: 0 0 0 10px; padding: 5px 10px;">
                        <option value="hora" <?php echo $intervalo == 'hora' ? 'selected' : ''; ?>>Hora (Bloques de 60 min)</option>
                        <option value="dia" <?php echo $intervalo == 'dia' ? 'selected' : ''; ?>>Día (Historial Diario)</option>
                        <option value="mes" <?php echo $intervalo == 'mes' ? 'selected' : ''; ?>>Mes</option>
                        <option value="anio" <?php echo $intervalo == 'anio' ? 'selected' : ''; ?>>Año</option>
                        <option value="depto" <?php echo $intervalo == 'depto' ? 'selected' : ''; ?>>Departamento (Analítica Espacial)</option>
                    </select>
                </form>
            </div>

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
                    <p style="font-size: 1.05rem; padding-top: 5px; color: #2e7d32; font-weight:bold;"><?php echo htmlspecialchars($moda); ?></p>
                </div>
            </div>
        </section>

        <div class="grid-estadisticas">
            <article class="card-analytics" style="margin-bottom:0;">
                <h3>Ocupación Actual por Departamento</h3>
                <div style="position: relative; margin: auto; height: 280px; width: 100%;">
                    <canvas id="chartDeptos"></canvas>
                </div>
            </article>

            <article class="card-analytics" style="margin-bottom:0;">
                <h3>Historial de Espacios Frecuentados por Personal</h3>
                <form method="GET" style="margin-bottom: 15px; display: flex; gap: 10px;">
                    <input type="hidden" name="intervalo" value="<?php echo htmlspecialchars($intervalo); ?>">
                    <input type="text" name="cedula" class="form-control" placeholder="Ej: V-12345678" 
                           value="<?php echo htmlspecialchars($cedula_buscar); ?>" required style="margin: 0; padding: 6px 12px;">
                    <button type="submit" class="btn-submit" style="width: auto; margin: 0; padding: 6px 15px;">Filtrar</button>
                </form>
                
                <div style="position: relative; margin: auto; height: 210px; width: 100%;">
                    <?php if ($cedula_buscar): ?>
                        <?php if (!empty($accesos_usuario)): ?>
                            <canvas id="chartUsuario"></canvas>
                        <?php else: ?>
                            <p style="text-align: center; color: #ffa000; padding-top: 40px; font-size:0.9rem;">No se registran movimientos para la cédula consultada.</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #999; padding-top: 40px; font-size:0.9rem;">Ingrese una Cédula para generar la analítica de comportamiento.</p>
                    <?php endif; ?>
                </div>
            </article>
        </div>
    </main>

    <script>
        const datosDeptos = <?php echo json_encode($datos_deptos); ?>;
        const labelsDeptos = datosDeptos.map(item => item.departamento);
        const valoresDeptos = datosDeptos.map(item => item.total);

        new Chart(document.getElementById('chartDeptos'), {
            type: 'bar',
            data: {
                labels: labelsDeptos,
                datasets: [{
                    label: 'Personal Interno',
                    data: valoresDeptos,
                    backgroundColor: ['#3e2723', '#d4af37', '#795548', '#ff9800'],
                    borderColor: '#3e2723',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });

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