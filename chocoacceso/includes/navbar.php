<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$rol = $_SESSION['rol'] ?? null;
?>

<nav class="main-navbar">
    <div class="nav-brand"><a href="index.php">Choco Acceso</div>
    <ul class="nav-links">
        <li><a href="login.php">Portería</a></li>
        <li><a href="dashboard.php">Monitor</a></li>
        <li><a href="estadisticas.php">Graficos</a></li>
        <?php if ($rol): ?>
            <?php if (in_array($rol, ['Administrador', 'Gerencia', 'Operador_Seguridad'])): ?>
                <li><a href="registro_usuario.php">Registrar Personal</a></li>
                <li><a href="auditoria.php">Auditoría</a></li>
                <li><a href="calendario.php">Citas</a></li>
                <li><a href="gestion_inicio.php">Singlepage</a></li>
            <?php endif; ?>

            <li class="nav-auth">
                <span class="user-name"><?php echo $_SESSION['nombre']; ?> (<?php echo $rol; ?>)</span>
                <a href="../controllers/LogoutController.php" class="btn-logout">Salir</a>
            </li>
        <?php else: ?>
            <li><a href="login_admin.php" class="btn-admin">Admin Login</a></li>
        <?php endif; ?>
    </ul>
</nav>