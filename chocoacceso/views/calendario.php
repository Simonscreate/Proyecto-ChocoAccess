<?php
session_start();

if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['Administrador', 'Gerencia'])) {
    header("Location: dashboard.php");
    exit();
}

require_once "../config/Database.php";
require_once "../models/Cita.php";

$db = (new Database())->Conexion();
$modeloCita = new Cita($db);
$listado_citas = $modeloCita->consultarTodos();

include "../includes/navbar.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>ChocoAcceso - Agenda Corporativa</title>
    <link rel="stylesheet" href="../css/styleV.css">
    <style>
        .layout-agenda { display: grid; grid-template-columns: 350px 1fr; gap: 25px; margin-top: 20px; }
        .tabla-agenda { width: 100%; border-collapse: collapse; background: white; border-radius: 6px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .tabla-agenda th, .tabla-agenda td { padding: 12px 15px; text-align: left; font-size: 0.9rem; }
        .tabla-agenda th { background-color: #3e2723; color: white; }
        .tabla-agenda tr:nth-child(even) { background-color: #fcf8f5; }
        .badge-status { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; background: #e8f5e9; color: #2e7d32; }
        .badge-cloud { padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: bold; background: #e3f2fd; color: #1565c0; border: 1px solid #bbdefb; }
    </style>
</head>
<body>
    <main class="container">
        <header style="margin-bottom: 20px;">
            <h2>Control de Asistencias Integrado con Google Calendar API</h2>
            <p>Planificación de visitas industriales sincronizadas automáticamente con la nube de la organización.</p>
        </header>

        <?php if (isset($_GET['status'])): ?>
            <?php if ($_GET['status'] == 'success'): ?>
                <div class="alert success" style="background:#e8f5e9; color:#2e7d32; padding:10px; margin-bottom:15px; border-radius:5px;">✓ Cita agendada de forma exitosa y sincronizada con Google Calendar.</div>
            <?php elseif ($_GET['status'] == 'user_not_found'): ?>
                <div class="alert error" style="background:#ffebee; color:#c62828; padding:10px; margin-bottom:15px; border-radius:5px;">✗ Error: Cédula no registrada en el sistema de personal.</div>
            <?php elseif ($_GET['status'] == 'error'): ?>
                <div class="alert error" style="background:#ffebee; color:#c62828; padding:10px; margin-bottom:15px; border-radius:5px;">✗ Ocurrió un error interno en la transacción.</div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="layout-agenda">
            <section class="card-analytics" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <h3 style="margin-bottom: 15px; color:#3e2723;">Planificar Evento</h3>
                <form action="../controllers/CitaController.php" method="POST">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label style="font-size:0.85rem; font-weight:bold; color:#555;">Cédula del Asistente</label>
                        <input type="text" name="cedula" class="form-control" placeholder="Ej: V-12345678" required style="width:100%; margin-top:5px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label style="font-size:0.85rem; font-weight:bold; color:#555;">Fecha Pautada</label>
                        <input type="date" name="fecha_cita" class="form-control" required style="width:100%; margin-top:5px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label style="font-size:0.85rem; font-weight:bold; color:#555;">Hora de Ingreso</label>
                        <input type="time" name="hora_cita" class="form-control" required style="width:100%; margin-top:5px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label style="font-size:0.85rem; font-weight:bold; color:#555;">Departamento Destino</label>
                        <select name="departamento_destino" class="form-control" required style="width:100%; margin-top:5px; padding:8px;">
                            <option value="ADMINISTRACION">Administración</option>
                            <option value="PRODUCCION">Producción (Planta)</option>
                            <option value="ALMACEN">Almacén de Materia Prima</option>
                            <option value="MANTENIMIENTO">Mantenimiento Técnico</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 18px;">
                        <label style="font-size:0.85rem; font-weight:bold; color:#555;">Motivo del Ingreso</label>
                        <input type="text" name="motivo" class="form-control" placeholder="Ej: Auditoría" required style="width:100%; margin-top:5px;">
                    </div>
                    <button type="submit" class="btn-submit" style="width: 100%; margin: 0; padding: 10px;">AGENDAR Y SINCRONIZAR</button>
                </form>
            </section>

            <section>
                <table class="tabla-agenda">
                    <thead>
                        <tr>
                            <th>Fecha / Hora</th>
                            <th>Personal</th>
                            <th>Destino</th>
                            <th>Motivo</th>
                            <th>Ecosistema</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($listado_citas)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: #999; padding: 30px;">No existen asistencias programadas.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($listado_citas as $c): ?>
                                <tr>
                                    <td><strong><?php echo date('d-m-Y', strtotime($c['fecha_cita'])); ?></strong><br><small style="color:#666;"><?php echo date('h:i A', strtotime($c['hora_cita'])); ?></small></td>
                                    <td><?php echo htmlspecialchars($c['nombre_personal']); ?><br><small style="color:#777;"><?php echo htmlspecialchars($c['cedula']); ?></small></td>
                                    <td><span style="font-size:0.8rem; background:#efebe9; padding:2px 6px; border-radius:4px; color:#3e2723; font-weight:bold;"><?php echo htmlspecialchars($c['departamento_destino']); ?></span></td>
                                    <td><?php echo htmlspecialchars($c['motivo']); ?></td>
                                    <td>
                                        <?php if ($c['google_event_id']): ?>
                                            <span class="badge-cloud">☁ Google Activo</span>
                                        <?php else: ?>
                                            <span style="font-size:0.75rem; color:#999;">Local</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge-status"><?php echo $c['estado']; ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </main>
</body>
</html>