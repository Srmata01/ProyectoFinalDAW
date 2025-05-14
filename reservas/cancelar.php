<?php
require_once '../config/database.php';
session_start();

// Verificar que el usuario está logueado y es un cliente
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] != 2) {
    header('Location: ../login.php');
    exit();
}

$id_cliente = $_SESSION['usuario']['id'];
$id_reserva = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id_reserva) {
    $_SESSION['error'] = "ID de reserva no válido.";
    header('Location: ../vistas_usuarios/perfil_cliente.php');
    exit();
}

try {    // Verificar que la reserva pertenece al cliente, está en estado pendiente y no ha sido rechazada
    $stmt = $pdo->prepare("
        SELECT r.* 
        FROM reservas r
        WHERE r.id_reserva = ? AND r.id_cliente = ? AND r.estado = 'pendiente' AND r.estado_confirmacion != 'rechazada'
    ");
    $stmt->execute([$id_reserva, $id_cliente]);
    $reserva = $stmt->fetch();
    
    if (!$reserva) {
        $_SESSION['error'] = "La reserva no existe, no te pertenece o no se puede cancelar.";
        header('Location: ../vistas_usuarios/perfil_cliente.php');
        exit();
    }
    
    // Actualizar el estado de la reserva a cancelada
    $stmt = $pdo->prepare("
        UPDATE reservas SET estado = 'cancelada' WHERE id_reserva = ?
    ");
    $stmt->execute([$id_reserva]);
    
    $_SESSION['mensaje'] = "Reserva cancelada correctamente.";
} catch (PDOException $e) {
    $_SESSION['error'] = "Error al cancelar la reserva: " . $e->getMessage();
}

header('Location: ../vistas_usuarios/perfil_cliente.php');
exit();
?>