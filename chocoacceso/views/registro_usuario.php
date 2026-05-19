<?php
session_start();
// Solo administradores o gerentes pueden crear otros usuarios operativos
$roles_autorizados = ['Administrador', 'Gerencia'];
$puede_gestionar = isset($_SESSION['rol']) && in_array($_SESSION['rol'], $roles_autorizados);

include "../includes/navbar.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>ChocoAcceso - Gestión de Personal</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <main class="container">
        <section class="form-card">
            <header>
                <h2>Gestión de Personal y Operadores</h2>
                <p>Complete los datos para el sistema de Chocolates El Rey.</p>
            </header>

            <?php if (!$puede_gestionar): ?>
                <div class="alert error">No tiene permisos para dar de alta nuevos usuarios.</div>
            <?php else: ?>
                <form action="../controllers/UserController.php" method="POST" id="userForm">
                    <div class="form-group">
                        <label>Cédula de Identidad</label>
                        <input type="text" name="cedula" class="form-control" required placeholder="V-00000000">
                    </div>

                    <div class="form-group">
                        <label>Nombre Completo</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Rol / Nivel de Acceso</label>
                        <select name="rol" id="rolSelect" class="form-control" onchange="togglePassword()">
                            <option value="Visitante">Visitante (Sin clave)</option>
                            <option value="Personal">Personal Fijo (Sin clave)</option>
                            <option value="Operador_Seguridad">Portero / Operador (Requiere Clave)</option>
                            <option value="Administrador">Administrador (Requiere Clave)</option>
                        </select>
                    </div>

                    <div class="form-group" id="passField" style="display:none;">
                        <label>Contraseña para el Sistema</label>
                        <input type="password" name="password" class="form-control" placeholder="Mínimo 8 caracteres">
                        <small>Esta clave le permitirá al usuario iniciar sesión para operar el software.</small>
                    </div>

                    <div class="form-group" id="passField2" style="display:none;">
                        <label>Empresa deOrigen (Opcional)</label>
                        <input type="text" name="empresa" class="form-control" placeholder="Empresa">
                        <small>Aduda a la Auditoria de datos</small>
                    </div>

                    <button type="submit" class="btn-submit">GUARDAR USUARIO</button>
                </form>
            <?php endif; ?>
        </section>
    </main>
    <script src="../includes/formato.js"></script>
    <script>
    function togglePassword() {
        const rol = document.getElementById('rolSelect').value;
        const passField = document.getElementById('passField');
        const passField2 = document.getElementById('passField2');
        const passInput = passField.querySelector('input');
        
        // Resetear estados
        passField.style.display = 'none';
        passField2.style.display = 'none';
        passInput.required = false;

        if (rol === 'Operador_Seguridad' || rol === 'Administrador') {
            passField.style.display = 'block';
            passInput.required = true; // Solo obligatorio para personal del sistema
        } else if (rol === 'Visitante'){
            passField2.style.display = 'block';
            // No pedimos contraseña a visitantes
        }
    }
    </script>

</body>
</html>