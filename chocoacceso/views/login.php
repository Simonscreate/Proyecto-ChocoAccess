<?php include "../includes/navbar.php"; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>ChocoAcceso - Login</title>
    <link rel="stylesheet" href="../css/styleV.css">
</head>
<body>

    </header>

    <main class="container">
        <section class="form-card">
            <header><h2>Validación de Identidad</h2></header>
            <form action="../controllers/AccessController.php" method="POST">
                <div class="form-group">
                    <label>Cédula</label>
                    <input type="text" name="cedula" class="form-control" required placeholder="V-00000000">
                </div>
                <div class="form-group">
                    <label>Tipo</label>
                    <select name="tipo" class="form-control">
                        <option value="ENTRADA">ENTRADA</option>
                        <option value="SALIDA">SALIDA</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Departamento</label>
                    <select name="departamento" class="form-control">
                        <option value="ADMINISTRACION">Administración</option>
                        <option value="PRODUCCION">Producción</option>
                        <option value="ALMACEN">Almacén</option>
                    </select>
                </div>
                <button type="submit" class="btn-submit">REGISTRAR</button>
            </form>
            <nav style="margin-top:20px; text-align:center;">
                <a href="dashboard.php">Ver Monitor</a>
            </nav>
        </section>
    </main>
    <script src="../includes/formato.js"></script>
</body>
</html>