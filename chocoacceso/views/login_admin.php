<?php include "../includes/navbar.php"; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso Administrativo - El Rey</title>
    <link rel="stylesheet" href="../css/styleV.css">
</head>
<body>
    <main class="container">
        <section class="form-card">
            <header>
                <h2>Panel Administrativo</h2>
                <p>Identifíquese para gestionar personal</p>
            </header>
            
            <form action="../controllers/LoginController.php" method="POST">
                <div class="form-group">
                    <label>Cédula</label>
                    <input type="text" name="cedula" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn-submit">ENTRAR AL PANEL</button>
            </form>
        </section>
    </main>
</body>
</html>