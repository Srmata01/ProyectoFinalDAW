<?php
// filepath: c:\xampp\htdocs\smata\ProyectoFinalDAW\vistas_usuarios\contactar.php
require_once '../config/database.php';
session_start();

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario'])) {
    header('Location: ../login.php');
    exit();
}

// Verificar que se ha proporcionado un ID de servicio
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: perfil_cliente.php');
    exit();
}

$id_servicio = $_GET['id'];
$id_cliente = $_SESSION['usuario']['id'];

try {
    // Verificar si el cliente tiene una reserva aceptada para este servicio
    $stmt = $pdo->prepare("
        SELECT r.id_reserva, s.id_autonomo
        FROM reservas r
        JOIN servicios s ON r.id_servicio = s.id_servicio
        WHERE r.id_servicio = ? AND r.id_cliente = ? AND r.estado_confirmacion = 'aceptada'
    ");
    $stmt->execute([$id_servicio, $id_cliente]);
    $reserva = $stmt->fetch();

    // Si no hay una reserva aceptada, redirigir al perfil del cliente
    if (!$reserva) {
        $_SESSION['error'] = "Solo puedes contactar con profesionales que hayan aceptado tu reserva.";
        header('Location: perfil_cliente.php');
        exit();
    }

    // Redirigir al perfil del autónomo con un parámetro para mostrar la información de contacto
    header('Location: ver_autonomo.php?id=' . $reserva['id_autonomo'] . '&mostrar_contacto=1');
    exit();
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
