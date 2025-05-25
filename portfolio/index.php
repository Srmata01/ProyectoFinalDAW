<?php
require_once '../config/database.php';
session_start();

$base_path = '../'; // Definimos base_path ya que estamos en un subdirectorio

// Validación de sesión
if (!isset($_SESSION['usuario'])) {
    header('Location: ../login.php');
    exit();
}

$id_usuario = isset($_GET['id']) ? $_GET['id'] : $_SESSION['usuario']['id'];

try {
    // Obtener información del autónomo
    $stmt = $pdo->prepare("SELECT nombre, apellido FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$id_usuario]);
    $autonomo = $stmt->fetch();

    // Obtener las imágenes del portfolio
    $stmt = $pdo->prepare("SELECT * FROM portfolios WHERE id_usuario = ? ORDER BY id_imagen DESC");
    $stmt->execute([$id_usuario]);
    $imagenes = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galería de Trabajos</title>
    <link rel="stylesheet" href="../includes/responsive-header.css">
    <link rel="stylesheet" href="../includes/compact-forms.css">
    <link rel="stylesheet" href="../vistas_usuarios/vistas.css">
    <link rel="stylesheet" href="../includes/footer.css">
</head>
<body>
    <?php include '../includes/header_template.php'; ?>

    <div class="gallery-container">        <div class="gallery-header">
            <h1>Galería de Trabajos - <?= htmlspecialchars($autonomo['nombre'] . ' ' . $autonomo['apellido']) ?></h1>
            <div class="button-group">
                <?php if ($_SESSION['usuario']['id'] === $id_usuario): ?>
                    <a href="subir.php" class="submit-btn">Añadir Imágenes</a>
                <?php endif; ?>
                <a href="../vistas_usuarios/perfil_autonomo.php" class="submit-btn btn-secondary">Volver al perfil</a>
            </div>
        </div>

        <?php if ($imagenes): ?>
            <div class="gallery-grid">
                <?php foreach ($imagenes as $imagen): ?>
                    <div class="gallery-item">
                        <img src="data:image/jpeg;base64,<?= base64_encode($imagen['imagen']) ?>" 
                             alt="Imagen de trabajo">
                        <?php if ($imagen['descripcion']): ?>
                            <div class="description">
                                <?= htmlspecialchars($imagen['descripcion']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-images">
                <p>No hay imágenes en la galería.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>