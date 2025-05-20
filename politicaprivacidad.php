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
        /* Estilo para limitar el tamaño de la imagen de perfil */
        .login-profile-box img {
            max-height: 40px;
            max-width: 40px;
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
                    <input type="text" placeholder="Buscar por servicio o ciudad..."
                        id="buscador-principal"
                        class="search-input">
                    <img src="media/lupa.png" alt="Buscar" class="search-icon" id="btn-buscar">
                </div>
            </div>
            <div class="login-profile-box">
                <?php include 'includes/profile_header.php'; ?>
            </div>
        </div>
    </header>

    <main class="app-main">
        <div class="document-container">
            <h2 class="document-title">Documentación sobre la Política de Privacidad</h2>
            <br><br>
            <p class="document-text">
            En Fix It Now, respetamos tu privacidad y protegemos tus datos personales conforme al Reglamento General de Protección de Datos (RGPD) y la legislación española vigente. Los datos personales que nos proporciones a través de formularios, correos electrónicos o cualquier otro medio serán tratados con confidencialidad y únicamente para gestionar los servicios solicitados, mejorar la atención al cliente, y facilitar el contacto entre usuarios y profesionales del sector de la fontanería y lampistería.

                <br><br>
                El responsable del tratamiento es nuestra empresa, ubicada en Cataluña, España. No compartimos tus datos con terceros sin tu consentimiento, salvo obligación legal. Puedes ejercer tus derechos de acceso, rectificación, supresión, limitación y oposición enviando un correo electrónico a info@fixitnow.com. Al usar nuestra web, aceptas esta política de privacidad.
                <img src="media/politica-privacidad.jpg" alt="" style="display: block; margin: 40px auto; width: 50%; max-width: 350px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);">
            </p>
            <a href="media/politica_privacidad_document.pdf" download class="download-button">Descargar Documento</a>
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