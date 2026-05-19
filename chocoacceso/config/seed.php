<?php
/**
 * SEEDER DE EMERGENCIA - CREACIÓN DEL PRIMER ADMINISTRADOR
 */
require_once "Database.php"; // Ajusta la ruta según donde lo coloques

$database = new Database();
$db = $database->Conexion();

// Datos del primer Administrador (Tú)
$cedula = 'V-12345678'; // Pon tu cédula real aquí
$nombre = 'Simón Toledo';
$password_plana = 'ChocoAdmin2026'; // Esta será tu clave inicial
$rol = 'Administrador';

// Hasheamos la clave para que sea segura
$password_hash = password_hash($password_plana, PASSWORD_BCRYPT);

try {
    $query = "INSERT INTO usuarios (cedula, nombre_completo, rol, password, activo) 
              VALUES (:cedula, :nombre, :rol, :pass, 1)
              ON DUPLICATE KEY UPDATE password = :pass, rol = :rol";
              
    $stmt = $db->prepare($query);
    $stmt->bindParam(':cedula', $cedula);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':rol', $rol);
    $stmt->bindParam(':pass', $password_hash);
    
    if ($stmt->execute()) {
        echo "<h1>Éxito: Usuario Administrador Creado</h1>";
        echo "<p>Cédula: $cedula</p>";
        echo "<p>Clave: $password_plana</p>";
        echo "<p><strong>BORRE ESTE ARCHIVO AHORA POR SEGURIDAD.</strong></p>";
    }
} catch (PDOException $e) {
    echo "Error al crear el usuario: " . $e->getMessage();
}