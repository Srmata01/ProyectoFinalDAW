<?php
// Archivo para marcar una reserva como completada
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
        $_SESSION['error'] = 'La reserva no existe o no tienes permiso para completarla.';
        header('Location: ../vistas_usuarios/perfil_autonomo.php');
        exit();
    }

    // Verificar que la reserva está aceptada
    if ($reserva['estado_confirmacion'] != 'aceptada') {
        $_SESSION['error'] = 'La reserva debe estar aceptada para poder completarla.';
        header('Location: ../vistas_usuarios/perfil_autonomo.php');
        exit();
    }

    // Actualizar el estado a 'completada'
    $stmt = $pdo->prepare("UPDATE reservas SET estado = 'completada' WHERE id_reserva = ?");
    $stmt->execute([$id_reserva]);

    $_SESSION['mensaje'] = 'La reserva ha sido marcada como completada.';
    header('Location: ../vistas_usuarios/perfil_autonomo.php');
    exit();
    
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error al procesar la solicitud: ' . $e->getMessage();
    header('Location: ../vistas_usuarios/perfil_autonomo.php');
    exit();
}
?>
