<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: ../views/dashboard.php");
    exit();
}

require_once "../config/Database.php";
require_once "../models/Landing.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = (new Database())->Conexion();
    $landing = new Landing($db);

    $seccion = $_POST['seccion'];
    $titulo = $_POST['titulo'];
    $subtitulo = $_POST['subtitulo'] ?? null;
    $contenido = $_POST['contenido'];
    $imagen_url = null;

    // Procesamiento seguro de imágenes de la planta
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['imagen']['tmp_name'];
        $fileName = $_FILES['imagen']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($fileExtension, $extensiones_permitidas)) {
            // Renombrado único para evitar colisiones en entornos operativos
            $newFileName = "upd_" . $seccion . "_" . time() . "." . $fileExtension;
            $uploadFileDir = '../img/';
            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $imagen_url = "img/" . $newFileName; // Ruta relativa guardada
            }
        }
    }

    if ($landing->actualizarSeccion($seccion, $titulo, $subtitulo, $contenido, $imagen_url)) {
        header("Location: ../views/gestion_inicio.php?status=success");
    } else {
        header("Location: ../views/gestion_inicio.php?status=error");
    }
    exit();
}