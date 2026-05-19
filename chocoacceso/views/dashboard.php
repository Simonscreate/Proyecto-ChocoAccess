<?php
require_once "../config/Database.php";
require_once "../models/Movimiento.php";
$db = (new Database())->Conexion();
$mov = new Movimiento($db);
include "../includes/navbar.php";

$filtro = $_GET['depto'] ?? 'TODOS';
$personalEnPlanta = $mov->consultarUbicacionesActuales($filtro);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>ChocoAcceso - Monitor Pro</title>
    <link rel="stylesheet" href="../css/styleV.css">
</head>
<body>

    <main class="container">
        <section class="kpi-grid">
            <article class="kpi-card">
                <header>En Planta</header>
                <data value="<?php echo count($personalEnPlanta); ?>"><?php echo count($personalEnPlanta); ?></data>
            </article>
            <article class="kpi-card" style="border-top-color: var(--danger);">
                <header>Área de Producción</header>
                <?php 
                    $soloProduccion = array_filter($personalEnPlanta, function($p) { return $p['ubicacion_actual'] == 'PRODUCCION'; });
                ?>
                <data value="<?php echo count($soloProduccion); ?>"><?php echo count($soloProduccion); ?></data>
            </article>
        </section>

        <section class="filter-section">
            <form method="GET" action="dashboard.php">
                <label>Filtrar por Departamento:</label>
                <select name="depto" onchange="this.form.submit()">
                    <option value="TODOS" <?php if($filtro == 'TODOS') echo 'selected'; ?>>Toda la Planta</option>
                    <option value="ADMINISTRACION" <?php if($filtro == 'ADMINISTRACION') echo 'selected'; ?>>Administración</option>
                    <option value="PRODUCCION" <?php if($filtro == 'PRODUCCION') echo 'selected'; ?>>Producción</option>
                    <option value="ALMACEN" <?php if($filtro == 'ALMACEN') echo 'selected'; ?>>Almacén</option>
                </select>
            </form>
        </section>

        <section class="status-grid">
            <header><h3>Localización de Personal: <?php echo $filtro; ?></h3></header>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Rol</th>
                        <th>Ubicación Actual</th>
                        <th>Última Actividad</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($personalEnPlanta) > 0): ?>
                        <?php foreach($personalEnPlanta as $p): ?>
                        <tr>
                            <td><?php echo $p['nombre_completo']; ?></td>
                            <td>
                                <span style="font-weight:bold; color: <?php echo ($p['rol'] == 'Visitante') ? 'orange' : 'inherit'; ?>">
                                    <?php echo $p['rol']; ?>
                                </span>
                            </td>
                            <td><mark><?php echo $p['ubicacion_actual']; ?></mark></td>
                            <td><?php echo date('H:i d/m', strtotime($p['ultima_actualizacion'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4">No hay personal detectado en esta área.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>