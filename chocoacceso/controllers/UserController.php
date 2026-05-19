<?php
session_start();
require_once "../config/Database.php";
require_once "../models/Usuario.php";

$db = (new Database())->Conexion();
$usuario = new Usuario($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Verificación de permisos
    if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['Administrador', 'Gerencia'])) {
        header("Location: ../views/registro_usuario.php?status=forbidden");
        exit();
    }

    $usuario->cedula = trim($_POST['cedula']);
    $usuario->nombre_completo = trim($_POST['nombre']);
    $usuario->rol = $_POST['rol'];
    $usuario->empresa = !empty($_POST['empresa']) ? trim($_POST['empresa']) : null;
    
    // 2. Lógica de Hasheo de Contraseña
    if (!empty($_POST['password'])) {
        // Usamos BCRYPT: genera un hash de 60 caracteres con 'salt' incluido
        $usuario->password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    } else {
        $usuario->password = null; // Para visitantes o personal sin acceso al sistema
    }

    // 3. Guardar en DB
    if ($usuario->crear()) {
        header("Location: ../views/registro_usuario.php?status=success");
    } else {
        header("Location: ../views/registro_usuario.php?status=error");
    }
}