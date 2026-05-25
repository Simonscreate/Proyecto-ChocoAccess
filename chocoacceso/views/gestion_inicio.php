<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: dashboard.php");
    exit();
}

require_once "../config/Database.php";
require_once "../models/Landing.php";

$db = (new Database())->Conexion();
$landing = new Landing($db);
$items = $landing->obtenerTodoContenido();

include "../includes/navbar.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>ChocoAcceso - Gestión de Contenidos</title>
    <link rel="stylesheet" href="../css/styleV.css">
    <style>
        .panel-gestion { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .seccion-bloque { border-left: 4px solid #d4af37; padding-left: 15px; margin-bottom: 35px; background: #fafafa; padding: 20px; border-radius: 4px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; color: #3e2723; font-size: 0.9rem; }
        .form-control-edit { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-family: inherit; }
        .preview-img { max-width: 180px; height: auto; border-radius: 4px; display: block; margin-top: 8px; border: 1px solid #ddd; }
        .alert { padding: 10px; border-radius: 4px; margin-bottom: 15px; font-weight: bold; font-size: 0.9rem; text-align: center; }
        .alert-success { background-color: #d4edda; color: #155724; }
        .alert-error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <main class="container">
        <header style="margin-bottom: 25px;">
            <h2>Gestión de Contenidos de la Página de Inicio</h2>
            <p>Portal administrativo para actualizar la información pública del sistema sin alterar código.</p>
        </header>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="alert alert-success">✓ Sección de la Landing Page actualizada exitosamente.</div>
        <?php $_GET['status']=null; endif; ?>

        <div class="panel-gestion">
            
            <div class="seccion-bloque">
                <h3>Slider Principal - Imagen de Presentación 1</h3>
                <form action="../controllers/LandingController.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="seccion" value="carrusel_1">
                    
                    <div class="form-group">
                        <label>Etiqueta Superior (Prefijo):</label>
                        <input type="text" name="contenido" class="form-control-edit" value="<?php echo htmlspecialchars($items['carrusel_1']['contenido']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Título Principal:</label>
                        <input type="text" name="titulo" class="form-control-edit" value="<?php echo htmlspecialchars($items['carrusel_1']['titulo']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Subtítulo / Eslogan:</label>
                        <input type="text" name="subtitulo" class="form-control-edit" value="<?php echo htmlspecialchars($items['carrusel_1']['subtitulo']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Fotografía de Fondo:</label>
                        <input type="file" name="imagen" accept="image/*">
                        <img src="../<?php echo $items['carrusel_1']['imagen_url']; ?>" class="preview-img" alt="Vista previa">
                    </div>
                    <button type="submit" class="btn-submit" style="width: auto; padding: 8px 20px;">Guardar Cambios Carrusel 1</button>
                </form>
            </div>

            <div class="seccion-bloque">
                <h3>Slider Principal - Imagen de Presentación 2</h3>
                <form action="../controllers/LandingController.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="seccion" value="carrusel_2">
                    
                    <div class="form-group">
                        <label>Etiqueta Superior (Prefijo):</label>
                        <input type="text" name="contenido" class="form-control-edit" value="<?php echo htmlspecialchars($items['carrusel_2']['contenido']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Título Principal:</label>
                        <input type="text" name="titulo" class="form-control-edit" value="<?php echo htmlspecialchars($items['carrusel_2']['titulo']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Subtítulo / Eslogan:</label>
                        <textarea name="subtitulo" class="form-control-edit" rows="2" required><?php echo htmlspecialchars($items['carrusel_2']['subtitulo']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Fotografía de Fondo:</label>
                        <input type="file" name="imagen" accept="image/*">
                        <img src="../<?php echo $items['carrusel_2']['imagen_url']; ?>" class="preview-img" alt="Vista previa">
                    </div>
                    <button type="submit" class="btn-submit" style="width: auto; padding: 8px 20px;">Guardar Cambios Carrusel 2</button>
                </form>
            </div>

            <div class="seccion-bloque">
                <h3>Sección - Sobre Nosotros (Planta Barquisimeto)</h3>
                <form action="../controllers/LandingController.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="seccion" value="sobre_nosotros">
                    
                    <div class="form-group">
                        <label>Subtítulo de Sección:</label>
                        <input type="text" name="contenido" class="form-control-edit" value="<?php echo htmlspecialchars($items['sobre_nosotros']['contenido']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Título de Sección:</label>
                        <input type="text" name="titulo" class="form-control-edit" value="<?php echo htmlspecialchars($items['sobre_nosotros']['titulo']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Descripción Corporativa / Reseña Industrial:</label>
                        <textarea name="subtitulo" class="form-control-edit" rows="4" required><?php echo htmlspecialchars($items['sobre_nosotros']['subtitulo'] ?? $items['sobre_nosotros']['contenido']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Imagen Destacada (Muestra Industrial):</label>
                        <input type="file" name="imagen" accept="image/*">
                        <img src="../<?php echo $items['sobre_nosotros']['imagen_url']; ?>" class="preview-img" alt="Vista previa">
                    </div>
                    <button type="submit" class="btn-submit" style="width: auto; padding: 8px 20px;">Guardar Cambios Sobre Nosotros</button>
                </form>
            </div>

        </div>
    </main>
</body>
</html>