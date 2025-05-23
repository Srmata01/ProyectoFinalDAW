<?php
$host = 'localhost';
$dbname = 'proyecto_final';
$user = 'root';
$password = '';

// Código de administración (cámbialo en producción)
define('ADMIN_CODE', '8869');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>