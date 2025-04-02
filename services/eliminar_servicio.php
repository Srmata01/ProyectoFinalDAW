<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_servicio = $_POST['id'] ?? 0;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM servicios WHERE id_servicio = ?");
        $stmt->execute([$id_servicio]);
        
        if ($stmt->rowCount() > 0) {
            http_response_code(200);
            echo "OK";
        } else {
            http_response_code(404);
            echo "Servicio no encontrado";
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo "Error al eliminar el servicio";
    }
} else {
    http_response_code(405);
    echo "Método no permitido";
}
?>