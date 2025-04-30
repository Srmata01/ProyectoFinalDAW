<?php
session_start();
require_once 'config/database.php';

try {
    $query = "SELECT s.*, u.nombre as nombre_autonomo, u.foto_perfil as imagen_autonomo 
              FROM servicios s 
              INNER JOIN usuarios u ON s.id_autonomo = u.id_usuario 
              ORDER BY RAND() LIMIT 4";
    $stmt = $pdo->query($query);
    $servicios = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al obtener los servicios: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FixItNow</title>
    <link rel="stylesheet" href="main.css">
    <link rel="icon" type="image/png" href="media/logo.png">
</head>

<body>
    <header>
        <div class="header-container">
            <div class="logo-container">
                <a href="main.php">
                    <img src="media/logo.png" alt="Logo FixItNow" class="logo">
                </a>
            </div>
            <div class="login-profile-box">
                <?php include 'includes/profile_header.php'; ?>
            </div>

        </div>
    </header>

    <!-- Sección de video y búsqueda -->
    <div class="video-background">
        <video autoplay muted loop>
            <source src="media/videocorp1.mp4" type="video/mp4">
            Tu navegador no soporta videos en HTML5.
        </video>
        <div class="content">
            <h1>Encuentra a tu profesional cerca de ti</h1>
            <p class="subtitulo1">Soluciona tus obras de la forma más rápida</p>
            <div class="search-container">
                <div class="search-box">
                    <input type="text" placeholder="Buscar lampistas, fontaneros, paletas..." class="search-input">
                    <img src="media/lupa.png" alt="Buscar">
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de servicios destacados -->
    <div class="servicios-section">
        <div class="servicios-destacados">
            <h2>Servicios Destacados</h2>
            <div class="servicios-grid">
                <?php foreach($servicios as $servicio): ?>
                    <div class="servicio-card">
                        <h3 class="servicio-titulo"><?php echo htmlspecialchars($servicio['nombre']); ?></h3>
                        <p class="servicio-descripcion"><?php echo htmlspecialchars($servicio['descripcion']); ?></p>
                        <p class="servicio-precio"><?php echo number_format($servicio['precio'], 2); ?>€</p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h4>Información Personal</h4>
                <ul>
                    <li><a href="politicaprivacidad.html">Política de privacidad</a></li>
                    <li><a href="politicacookiesdatos.html">Política de Cookies y protección de datos</a></li>
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
                <h4>Eres miembro?</h4>
                <ul>
                    <li><a href="create_users/index.php">Únete a Nosotros</a></li>
                </ul>
            </div>

            <div class="footer-section social-media">
                <div class="social-icons">
                    <a href="#"><img src="media/twitter-icon.png" alt="Twitter"></a>
                    <a href="#"><img src="media/instagram-icon.png" alt="Instagram"></a>
                    <a href="#"><img src="media/facebook-icon.png" alt="Facebook"></a>
                    <a href="#"><img src="media/tiktok-icon.png" alt="TikTok"></a>
                </div>
            </div>

            <div class="footer-logo">
                <img src="media/logo.png" alt="FixItNow Logo">
            </div>
        </div>
    </footer>
</body>

</html>