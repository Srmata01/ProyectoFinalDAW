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
                <a href="../main.php">
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
        <?php else: ?>
            <div class="no-images">
                <p>No hay imágenes en la galería.</p>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h4>Información Personal</h4>
                <ul>
                    <li><a href="../politicaprivacidad.html">Política de privacidad</a></li>
                    <li><a href="../politicacookiesdatos.html">Política de Cookies y protección de datos</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Contacto</h4>
                <ul>
                    <li><a href="mailto:fixitnow@gmail.com">fixitnow@gmail.com</a></li>
                    <li><a href="tel:+34690096690">+34 690 096 690</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>¿Eres miembro?</h4>
                <ul>
                    <li><a href="../create_users/index.php">Únete a Nosotros</a></li>
                </ul>
            </div>
            
            <div class="footer-section social-media">
                <div class="social-icons">
                    <a href="#"><img src="../media/twitter-icon.png" alt="Twitter"></a>
                    <a href="#"><img src="../media/instagram-icon.png" alt="Instagram"></a>
                    <a href="#"><img src="../media/facebook-icon.png" alt="Facebook"></a>
                    <a href="#"><img src="../media/tiktok-icon.png" alt="TikTok"></a>
                </div>
            </div>
            
            <div class="footer-logo">
                <img src="../media/logo.png" alt="FixItNow Logo">
            </div>
        </div>
    </footer>
</body>
</html>