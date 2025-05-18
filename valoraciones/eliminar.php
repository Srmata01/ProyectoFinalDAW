<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['id'])) {
    if (isset($_GET['redirect'])) {
        $_SESSION['error'] = 'Usuario no autenticado';
        header('Location: ../main.php');
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Usuario no autenticado']);
    }
    exit;
}

// Determinar si debe devolver JSON o redirigir
$is_api_request = !isset($_GET['confirm']) || !isset($_GET['redirect']);

// Verificar si es una solicitud POST o si hay confirmación vía GET
$valid_request = ($_SERVER['REQUEST_METHOD'] === 'POST') || 
                 (isset($_GET['id_receptor']) && isset($_GET['confirm']) && $_GET['confirm'] == 1);

if (!$valid_request) {
    if ($is_api_request) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Método no permitido']);
    } else {
        $_SESSION['error'] = 'Método no permitido';
        header('Location: ../main.php');
    }
    exit;
}

// Obtener datos
$id_emisor = $_SESSION['usuario']['id'];
$id_receptor = filter_var($_POST['id_receptor'] ?? $_GET['id_receptor'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
$tipo_redirect = filter_var($_GET['redirect'] ?? 'usuario', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// Validar datos
if (empty($id_receptor) || $id_receptor <= 0) {
    if ($is_api_request) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'ID de receptor no válido']);
    } else {
        $_SESSION['error'] = 'ID de receptor no válido';
        header('Location: ../main.php');
    }
    exit;
}

try {
    // Eliminar valoración
    $stmt = $pdo->prepare("DELETE FROM valoraciones_usuarios WHERE id_emisor = ? AND id_receptor = ?");
    $stmt->execute([$id_emisor, $id_receptor]);
    
    if ($stmt->rowCount() > 0) {
        if ($is_api_request) {
            header('Content-Type: application/json');
            echo json_encode(['success' => 'Valoración eliminada correctamente']);
        } else {
            $_SESSION['mensaje'] = 'Valoración eliminada correctamente';
            header("Location: ../vistas_usuarios/ver_{$tipo_redirect}.php?id={$id_receptor}");
        }
    } else {
        if ($is_api_request) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No se encontró la valoración a eliminar']);
        } else {
            $_SESSION['error'] = 'No se encontró la valoración a eliminar';
            header("Location: ../vistas_usuarios/ver_{$tipo_redirect}.php?id={$id_receptor}");
        }
    }
} catch (PDOException $e) {
    if ($is_api_request) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Error al eliminar la valoración: ' . $e->getMessage()]);
    } else {
        $_SESSION['error'] = 'Error al eliminar la valoración: ' . $e->getMessage();
        header("Location: ../vistas_usuarios/ver_{$tipo_redirect}.php?id={$id_receptor}");
    }
}
?>
