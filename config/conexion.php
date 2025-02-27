<?php
$host = 'localhost';
$dbname = 'proyecto_final';
$user = 'root'; // Cambia esto por tu usuario
$password = ''; // Cambia esto por tu contraseña

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error en la conexión: " . $e->getMessage());
}
?>