<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

// Verificar si es una solicitud GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Obtener el ID del usuario del cual se quieren obtener las valoraciones
$id_usuario = filter_var($_GET['id_usuario'] ?? 0, FILTER_SANITIZE_NUMBER_INT);

// Validar datos
if (empty($id_usuario) || $id_usuario <= 0) {
    echo json_encode(['error' => 'ID de usuario no válido']);
    exit;
}

try {
    // Obtener todas las valoraciones recibidas por el usuario
    $stmt = $pdo->prepare("
        SELECT v.*, u.nombre, u.apellido, u.foto_perfil 
        FROM valoraciones_usuarios v
        JOIN usuarios u ON v.id_emisor = u.id_usuario
        WHERE v.id_receptor = ?
        ORDER BY v.fecha_creacion DESC
    ");
    $stmt->execute([$id_usuario]);
    $valoraciones = $stmt->fetchAll();
    
    // Calcular la media de puntuación
    $stmt = $pdo->prepare("
        SELECT AVG(puntuacion) as media, COUNT(*) as total
        FROM valoraciones_usuarios 
        WHERE id_receptor = ?
    ");
    $stmt->execute([$id_usuario]);
    $stats = $stmt->fetch();
    
    $resultado = [
        'valoraciones' => $valoraciones,
        'media' => round(floatval($stats['media'] ?? 0), 1),
        'total' => intval($stats['total'] ?? 0)
    ];
    
    // Verificar si el usuario actual ha valorado a este usuario
    if (isset($_SESSION['usuario']) && isset($_SESSION['usuario']['id'])) {
        $id_emisor = $_SESSION['usuario']['id'];
        
        $stmt = $pdo->prepare("
            SELECT * FROM valoraciones_usuarios 
            WHERE id_emisor = ? AND id_receptor = ?
        ");
        $stmt->execute([$id_emisor, $id_usuario]);
        $mi_valoracion = $stmt->fetch();
        
        $resultado['mi_valoracion'] = $mi_valoracion;
    }
    
    echo json_encode($resultado);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error al obtener las valoraciones: ' . $e->getMessage()]);
}
?>
