<?php
session_start();
require_once 'config/database.php';

try {
    $query = "SELECT s.id_servicio, s.nombre, s.descripcion, s.precio, 
              u.nombre as nombre_autonomo, u.foto_perfil as imagen_autonomo 
              FROM servicios s 
              INNER JOIN usuarios u ON s.id_autonomo = u.id_usuario 
              ORDER BY RAND() LIMIT 4";
    $stmt = $pdo->query($query);
    $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Verificar que cada servicio tiene todos los campos necesarios
    foreach ($servicios as &$servicio) {
        if (!isset($servicio['id_servicio'])) {
            $servicio['id_servicio'] = '';
        }
        if (!isset($servicio['imagen_autonomo'])) {
            $servicio['imagen_autonomo'] = 'media/autonomo.jpg'; // imagen por defecto
        }
        if (!isset($servicio['nombre_autonomo'])) {
            $servicio['nombre_autonomo'] = 'Autónomo';
        }
    }
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
                    <a href="services/ver_servicio.php?id=<?php echo htmlspecialchars($servicio['id_servicio']); ?>" class="servicio-link">
                        <div class="servicio-card">
                            <div class="autonomo-info">
                                <?php if (!empty($servicio['imagen_autonomo'])): ?>
                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($servicio['imagen_autonomo']); ?>" alt="Foto de perfil" class="autonomo-imagen">
                                <?php else: ?>
                                    <img src="media/autonomo.jpg" alt="Foto de perfil por defecto" class="autonomo-imagen">
                                <?php endif; ?>
                                <span class="autonomo-nombre"><?php echo htmlspecialchars($servicio['nombre_autonomo']); ?></span>
                            </div>
                            <h3 class="servicio-titulo"><?php echo htmlspecialchars($servicio['nombre']); ?></h3>
                            <p class="servicio-descripcion"><?php echo htmlspecialchars($servicio['descripcion']); ?></p>
                            <p class="servicio-precio"><?php echo number_format($servicio['precio'], 2); ?>€</p>
                        </div>
                    </a>
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

/* Estilos generales */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

/* Header */
header {
    background-color: rgba(210, 210, 210, 0.5);
    padding: 0;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    position: fixed;
    width: 100%;
    z-index: 1000;
}

.header-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 24px;
    height: 100px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* Logo */
.logo-container {
    display: flex;
    align-items: center;
}

.logo {
    height: 80px;
}

/* Video Background Section */
.video-background {
    position: relative;
    height: 100vh;
    width: 100%;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.video-background video {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    min-width: 100%;
    min-height: 100%;
    width: auto;
    height: auto;
    object-fit: cover;
    z-index: -1;
}

.content {
    position: relative;
    z-index: 1;
    color: white;
    text-align: center;
    padding: 20px;
    width: 100%;
    max-width: 800px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
    margin-top: -50px; /* Ajuste para compensar el header fijo */
}

.content h1 {
    font-size: 3rem;
    margin-bottom: 10px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

.content .subtitulo1 {
    font-size: 1.5rem;
    margin-bottom: 30px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

/* Buscador */
.search-container {
    width: 100%;
    max-width: 600px;
    margin: 0 auto;
}

.search-box {
    position: relative;
    display: flex;
    width: 100%;
}

.search-input {
    width: 100%;
    height: 60px;
    padding: 10px 16px 10px 40px;
    border-radius: 20px;
    border: 1px solid #D2D2D2;
    font-size: 14px;
    color: #000000;
    outline: none;
    transition: all 0.3s;
    background-color: rgba(255, 255, 255, 0.9);
}

.search-box img {
    position: absolute;
    top: 50%;
    right: 10px;
    transform: translateY(-50%);
    cursor: pointer;
    width: 20px;
    height: 20px;
}

/* Servicios Destacados Section */
.servicios-section {
    background-color: #f5f5f5;
    padding: 60px 0;
    width: 100%;
}

.servicios-destacados {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.servicios-destacados h2 {
    text-align: center;
    color: #333;
    margin-bottom: 40px;
    font-size: 2.5rem;
}

.servicios-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    padding: 20px;
}

.servicios-grid a {
    text-decoration: none;
    color: inherit;
    display: block;
    height: 100%;
}

.servicio-link-container {
    width: 100%;
    height: 100%;
}

.servicio-link {
    text-decoration: none;
    color: inherit;
    display: block;
    height: 100%;
}

.servicio-link:hover {
    text-decoration: none;
}

.servicio-card {
    background-color: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.servicio-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
}

.autonomo-info {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.autonomo-imagen {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 15px;
}

.autonomo-nombre {
    color: #333;
    font-size: 1rem;
    font-weight: 500;
}

.servicio-titulo {
    color: #FF9B00;
    font-size: 1.5rem;
    margin-bottom: 10px;
}

.servicio-descripcion {
    color: #666;
    margin-bottom: 15px;
    line-height: 1.5;
}

.servicio-precio {
    color: #E08A00;
    font-size: 1.25rem;
    font-weight: bold;
}

/* Footer */
footer {
    background-color: rgba(210, 210, 210, 0.5);
    padding: 20px 0;
    width: 100%;
    margin-top: auto;
}

.footer-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
}

.footer-section {
    flex: 1;
    min-width: 150px;
    margin: 10px;
}

.footer-section h4 {
    font-size: 13px;
    font-weight: bold;
    color: #E08A00;
    margin-bottom: 10px;
}

.footer-section ul {
    list-style: none;
    padding: 0;
}

.footer-section ul li a {
    text-decoration: none;
    color: #555;
    font-size: 12px;
    line-height: 1.8;
}

.social-icons img {
    width: 30px;
    margin: 0 5px;
    transition: transform 0.3s ease;
}

.social-icons img:hover {
    transform: translateY(-3px);
}

.footer-logo {
    text-align: center;
}

.footer-logo img {
    width: 100px;
}

/* Responsive */
@media (max-width: 768px) {
    .footer-container {
        flex-direction: column;
        text-align: center;
    }
    
    .footer-section {
        margin: 15px 0;
    }
    
    .servicios-grid {
        grid-template-columns: 1fr;
        padding: 10px;
    }
    
    .content {
        font-size: 1.5rem;
    }
}

/* Estilo del enlace (botón) */
.login-profile-box a {
    display: inline-block;
    color: #2d2d2d; /* Texto oscuro */
    border: 1px solid #d2d2d2; /* Borde gris claro */
    padding: 0.75rem 2rem;
    font-size: 0.8rem;
    border-radius: 20px;
    text-decoration: none;
    transition: background-color 0.3s ease, color 0.3s ease;
  }

  .login-profile-box img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    margin-left: 10px;
}
  
  /* Hover sobre el enlace (botón) */
  .login-profile-box a:hover {
    background-color: #ff9b00; /* Fondo naranja */
    color: white; /* Texto blanco */
    border: 1px solid #ff9b00; /* Borde naranja */
  }

