<?php
session_start();
require_once "../config/Database.php";
require_once "../models/Usuario.php";

$db = (new Database())->Conexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = trim($_POST['cedula']);
    $password_ingresada = $_POST['password'];

    // 1. Buscamos al usuario por cédula
    $query = "SELECT * FROM usuarios WHERE cedula = :cedula LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":cedula", $cedula);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Validamos existencia y contraseña
    if ($user && password_verify($password_ingresada, $user['password'])) {
        // La clave coincide con el hash
        $_SESSION['id_usuario'] = $user['id_usuario'];
        $_SESSION['nombre'] = $user['nombre_completo'];
        $_SESSION['rol'] = $user['rol'];

        header("Location: ../views/dashboard.php");
    } else {
        // Error genérico por seguridad (no decir si falló la clave o el usuario)
        header("Location: ../views/login_admin.php?error=1");
    }
    exit();
}