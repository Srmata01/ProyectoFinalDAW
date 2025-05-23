<?php
require_once '../config/database.php';
session_start();

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
    <link rel="stylesheet" href="../vistas_usuarios/vistas.css">
    <style>
        .gallery-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .gallery-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .gallery-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .gallery-item img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        .gallery-item .description {
            padding: 10px;
            background: rgba(255,255,255,0.9);
            font-size: 0.9em;
        }
        .no-images {
            text-align: center;
            padding: 40px;
            font-size: 1.2em;
            color: #666;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo-container">
                <a href="../index.php">
                    <img src="../media/logo.png" alt="Logo FixItNow" class="logo">
                </a>
            </div>
            <div class="user-container">
                <div class="profile-container">
                    <?php include '../includes/profile_header.php'; ?>
                </div>
            </div>
        </div>
    </header>

    <div class="gallery-container">
        <div class="gallery-header">
            <h1>Galería de Trabajos - <?= htmlspecialchars($autonomo['nombre'] . ' ' . $autonomo['apellido']) ?></h1>
            <?php if ($_SESSION['usuario']['id'] === $id_usuario): ?>
                <a href="subir.php" class="submit-btn">Añadir Imágenes</a>
            <?php endif; ?>
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
        <?php else: ?>            <div class="no-images">
                <p>No hay imágenes en la galería.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php 
    // Definir la ruta base para el footer
    $base_path = '../';
    include '../includes/footer.php'; 
    ?>
</body>
</html>