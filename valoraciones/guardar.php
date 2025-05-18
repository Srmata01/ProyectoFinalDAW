<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['id'])) {
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

// Verificar si es una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Obtener datos del formulario
$id_emisor = $_SESSION['usuario']['id'];
$id_receptor = filter_var($_POST['id_receptor'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
$puntuacion = filter_var($_POST['puntuacion'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
// Preservar saltos de línea y evitar inyección de código
$comentario = htmlspecialchars(trim($_POST['comentario'] ?? ''), ENT_QUOTES, 'UTF-8');

// Validar datos
if (empty($id_receptor) || $id_receptor <= 0) {
    echo json_encode(['error' => 'ID de receptor no válido']);
    exit;
}

if ($id_emisor == $id_receptor) {
    echo json_encode(['error' => 'No puedes valorarte a ti mismo']);
    exit;
}

if ($puntuacion < 1 || $puntuacion > 5) {
    echo json_encode(['error' => 'La puntuación debe ser entre 1 y 5']);
    exit;
}

try {
    // Verificar si ya existe una valoración para actualizar o insertar una nueva
    $stmt = $pdo->prepare("SELECT id_valoracion FROM valoraciones_usuarios WHERE id_emisor = ? AND id_receptor = ?");
    $stmt->execute([$id_emisor, $id_receptor]);
    $valoracion_existente = $stmt->fetch();

    if ($valoracion_existente) {
        // Actualizar valoración existente
        $stmt = $pdo->prepare("
            UPDATE valoraciones_usuarios 
            SET puntuacion = ?, comentario = ?, fecha_creacion = CURRENT_TIMESTAMP 
            WHERE id_emisor = ? AND id_receptor = ?
        ");
        $stmt->execute([$puntuacion, $comentario, $id_emisor, $id_receptor]);
        echo json_encode(['success' => 'Valoración actualizada correctamente', 'id_valoracion' => $valoracion_existente['id_valoracion']]);
    } else {
        // Insertar nueva valoración
        $stmt = $pdo->prepare("
            INSERT INTO valoraciones_usuarios (id_emisor, id_receptor, puntuacion, comentario) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$id_emisor, $id_receptor, $puntuacion, $comentario]);
        echo json_encode(['success' => 'Valoración guardada correctamente', 'id_valoracion' => $pdo->lastInsertId()]);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error al guardar la valoración: ' . $e->getMessage()]);
}
?>
