<?php
include "../includes/navbar.php";
require_once "../config/Database.php";
require_once "../models/Movimiento.php";

$db = (new Database())->Conexion();
$mov = new Movimiento($db);
$fecha = $_GET['fecha'] ?? date('Y-m-d');
$registros = $mov->consultarAuditoriaGlobal($fecha);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChocoAcceso - Auditoría de Planta</title>
    <link rel="stylesheet" href="../css/styleV.css">
</head>
<body>



    <main class="container">
    <section class="filter-section">
        <form method="GET">
            <label>Auditar día:</label>
            <input type="date" name="fecha" value="<?php echo $fecha; ?>" onchange="this.form.submit()">
        </form>
    </section>

    <section class="log-section">
        <header>
            <h3>Historial Unificado de Operaciones - El Rey</h3>
        </header>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Hora</th>
                    <th>Sujeto / Usuario</th>
                    <th>Rol</th>
                    <th>Acción</th>
                    <th>Responsable (Operador)</th>
                    <th>Detalles</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($registros as $r): 
                    // Lógica de colores para diferenciar acciones
                    $esRegistro = ($r['accion'] == 'REGISTRO_USUARIO');
                    $colorFila = $esRegistro ? '#f0faff' : 'transparent';
                ?>
                <tr style="background-color: <?php echo $colorFila; ?>">
                    <td><?php echo date('H:i:s', strtotime($r['fecha_hora'])); ?></td>
                    <td><strong><?php echo $r['sujeto']; ?></strong></td>
                    <td><small><?php echo $r['sujeto_rol']; ?></small></td>
                    <td>
                        <span class="badge" style="background: <?php echo $esRegistro ? '#007bff' : '#28a745'; ?>; color:white; padding:3px 8px; border-radius:10px; font-size:0.7rem;">
                            <?php echo $r['accion']; ?>
                        </span>
                    </td>
                    <td><?php echo $r['responsable']; ?></td>
                    <td><?php echo $r['detalle']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>

    <footer class="main-footer" style="margin-top: 40px; border-top: 1px solid #ddd;">
        <p>Sistema ChocoAcceso v2.0 | Reporte Generado para Auditoría de Planta El Rey</p>
    </footer>

</body>
</html>