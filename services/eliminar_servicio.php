<?php
require_once '../config/database.php';
session_start();

// Verificar que el usuario es un autónomo
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] != 3) {
    http_response_code(403);
    echo "No autorizado";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_servicio = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
    $id_autonomo = $_SESSION['usuario']['id'];
    
    try {
        // Verificar que el servicio pertenece al autónomo antes de eliminarlo
        $stmt = $pdo->prepare("DELETE FROM servicios WHERE id_servicio = ? AND id_autonomo = ?");
        $stmt->execute([$id_servicio, $id_autonomo]);
        
        if ($stmt->rowCount() > 0) {
            http_response_code(200);
            echo "OK";
        } else {
            http_response_code(404);
            echo "Servicio no encontrado o no autorizado";
        }
    } catch (PDOException $e) {
        // Verificar si hay restricciones de clave foránea
        if ($e->getCode() == '23000') {
            http_response_code(400);
            echo "No se puede eliminar el servicio porque tiene reservas asociadas";
        } else {
            http_response_code(500);
            echo "Error al eliminar el servicio";
        }
    }
} else {
    http_response_code(405);
    echo "Método no permitido";
}
?>