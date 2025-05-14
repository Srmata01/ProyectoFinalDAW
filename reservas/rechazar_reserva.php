<?php
// Archivo para rechazar reservas
require_once '../config/database.php';
session_start();

// Verificar que el usuario es un autónomo
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] != 3) {
    header('Location: ../login.php');
    exit();
}

$id_autonomo = $_SESSION['usuario']['id'];
$id_reserva = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    // Verificar que la reserva existe y pertenece a un servicio del autónomo
    $stmt = $pdo->prepare("
        SELECT r.*, s.id_autonomo 
        FROM reservas r
        JOIN servicios s ON r.id_servicio = s.id_servicio
        WHERE r.id_reserva = ? AND s.id_autonomo = ?
    ");
    $stmt->execute([$id_reserva, $id_autonomo]);
    $reserva = $stmt->fetch();

    if (!$reserva) {
        $_SESSION['error'] = 'La reserva no existe o no tienes permiso para rechazarla.';
        header('Location: ../vistas_usuarios/perfil_autonomo.php');
        exit();
    }

    // Actualizar el estado de confirmación a 'rechazada'
    $stmt = $pdo->prepare("UPDATE reservas SET estado_confirmacion = 'rechazada' WHERE id_reserva = ?");
    $stmt->execute([$id_reserva]);    // Obtener información del cliente para notificar
    $stmt = $pdo->prepare("
        SELECT c.*, s.nombre as nombre_servicio
        FROM reservas r
        JOIN usuarios c ON r.id_cliente = c.id_usuario
        JOIN servicios s ON r.id_servicio = s.id_servicio
        WHERE r.id_reserva = ?
    ");
    $stmt->execute([$id_reserva]);
    $info_cliente = $stmt->fetch();

    // Guardar mensaje en la sesión para mostrar en perfil autónomo
    $_SESSION['mensaje'] = 'Has rechazado la reserva de ' . $info_cliente['nombre'] . ' ' . $info_cliente['apellido'] . ' para el servicio ' . $info_cliente['nombre_servicio'];
    
    // En un sistema real, aquí enviaríamos notificación por email o SMS al cliente
    
    header('Location: ../vistas_usuarios/perfil_autonomo.php');
    exit();
    
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error al procesar la solicitud: ' . $e->getMessage();
    header('Location: ../vistas_usuarios/perfil_autonomo.php');
    exit();
}
?>
