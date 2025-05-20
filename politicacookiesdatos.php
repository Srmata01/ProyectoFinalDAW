<?php
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FixItNow</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" type="image/png" href="media/logo.png">
    <!-- Agregar referencia al script del buscador -->
    <script src="services/js/buscador.js" defer></script>
    <style>
        /* Estilo para limitar tamaño de la imagen de perfil en el header */
        .user-avatar img {
            max-width: 40px;
            max-height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
</head>

<body class="app">
    <header class="app-header">
        <div class="header-container">
            <div class="logo-container">
                <a href="main.php" class="logo-link">
                    <img src="media/logo.png" alt="Logo FixItNow" class="logo">
                </a>
            </div>

            <div class="search-container">
                <div class="search-box">
                    <input type="text" placeholder="Buscar por servicio o localidad..." class="search-input">
                    <img src="media/lupa.png" alt="Buscar" class="search-icon">
                </div>
            </div>

            <div class="user-container">
                <?php
                if (isset($_SESSION['usuario'])) {
                    // Determinar perfil URL
                    $perfil_url = '';
                    switch ($_SESSION['usuario']['tipo']) {
                        case 1:
                            $perfil_url = 'vistas_usuarios/perfil_admin.php';
                            break;
                        case 2:
                            $perfil_url = 'vistas_usuarios/perfil_cliente.php';
                            break;
                        case 3:
                            $perfil_url = 'vistas_usuarios/perfil_autonomo.php';
                            break;
                    }

                    // Obtener la foto de perfil del usuario
                    $stmt = $pdo->prepare("SELECT foto_perfil FROM usuarios WHERE id_usuario = ?");
                    $stmt->execute([$_SESSION['usuario']['id']]);
                    $usuario = $stmt->fetch();
                    $foto_perfil = $usuario['foto_perfil'];
                ?>
                    <div class="profile-container">
                        <a href="<?= $perfil_url ?>" class="profile-btn" style="text-decoration: none;">
                            <?php if ($foto_perfil): ?>
                                <div class="user-avatar">
                                    <img src="data:image/jpeg;base64,<?= base64_encode($foto_perfil) ?>" alt="Foto de perfil">
                                </div>
                            <?php else: ?>
                                <div class="user-avatar"><?= strtoupper(substr($_SESSION['usuario']['nombre'], 0, 1)) ?></div>
                            <?php endif; ?>
                            <span class="user-name"><?= htmlspecialchars($_SESSION['usuario']['nombre'] . ' ' . $_SESSION['usuario']['apellido']) ?></span>
                        </a>
                    </div>
                <?php } else { ?>
                    <a href="login.php" class="profile-btn">
                        <span class="user-name">Iniciar Sesión</span>
                    </a>
                <?php } ?>
            </div>
        </div>
    </header>

    <main class="app-main">
        <div class="document-container">
            <h2 class="document-title">Documentación sobre la Política de Cookies</h2>
            <br><br>
            <p class="document-text">
                En Fix It Now utilizamos cookies propias y de terceros con fines técnicos, analíticos y para mejorar la experiencia de navegación. Una cookie es un pequeño archivo que se almacena en tu dispositivo cuando visitas nuestro sitio web, permitiéndonos recordar tus preferencias y analizar el uso del sitio.
                <br><br>
                Puedes configurar tu navegador para aceptar, rechazar o eliminar cookies. Al continuar navegando en nuestro sitio, consientes el uso de cookies conforme a esta política. Algunas cookies son esenciales para el funcionamiento de la web, mientras que otras nos ayudan a ofrecer un mejor servicio, como identificar tus búsquedas recientes de fontaneros o lampistas.

                <br><br>
                Para más información sobre cómo gestionamos las cookies, puedes consultar la configuración de tu navegador o escribirnos a info@fixitnow.com.
            </p>
            <br><br>
            <a href="media/politica_cookies_document.pdf" download class="download-button">Descargar Documento</a>
        </div>
    </main>

    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h4>Información Personal</h4>
                <ul>
                    <li><a href="politicaprivacidad.php">Política de privacidad</a></li>
                    <li><a href="politicacookiesdatos.php">Política de Cookies y protección de datos</a></li>
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

            <div class="footer-section">
                <h4>¿Tienes algún problema?</h4>
                <ul>
                    <li><a href="incidencias/crear.php">Reportar incidencia</a></li>
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
    </footer> <!-- Agregar referencia al script del buscador reutilizable -->
</body>

</html>