<?php
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Cookies - FixItNow</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="includes/responsive-header.css">
    <link rel="stylesheet" href="includes/footer.css">
    <link rel="icon" type="image/png" href="media/logo.png">
    <script src="services/js/buscador.js" defer></script>
</head>

<body class="app">
    <header>
        <div class="header-container">
            <div class="logo-container">
                <a href="index.php">
                    <img src="media/logo.png" alt="Logo FixItNow" class="logo" style="height: 45px;">
                </a>
            </div>

            <div class="search-container">
                <div class="search-box">
                    <input type="text" placeholder="Buscar por servicio o localidad..." class="search-input">
                    <img src="media/lupa.png" alt="Buscar" class="search-icon">
                </div>
            </div>

            <div class="login-profile-box">
                <?php include 'includes/profile_header.php'; ?>
            </div>
        </div>
    </header>

    <div class="app-main">
        <div class="policy-container">
            <h1 class="document-title">Política de Cookies</h1>

            <div class="policy-content">
                <div class="policy-section">
                    <h2>¿Qué son las Cookies?</h2>
                    <p>
                        En Fix It Now utilizamos cookies propias y de terceros con fines técnicos, analíticos y para mejorar
                        la experiencia de navegación. Una cookie es un pequeño archivo que se almacena en tu dispositivo cuando
                        visitas nuestro sitio web, permitiéndonos recordar tus preferencias y analizar el uso del sitio.
                    </p>
                </div>

                <div class="policy-section">
                    <h2>Tipos de Cookies que Utilizamos</h2>
                    <ul class="policy-list">
                        <li>Cookies técnicas: Esenciales para el funcionamiento del sitio</li>
                        <li>Cookies analíticas: Nos ayudan a mejorar basándonos en el uso</li>
                        <li>Cookies de preferencias: Recuerdan tus elecciones y personalización</li>
                        <li>Cookies de búsqueda: Mejoran tus resultados de búsqueda de servicios</li>
                    </ul>
                </div>

                <div class="policy-section">
                    <h2>Control de Cookies</h2>
                    <p>
                        Puedes configurar tu navegador para aceptar, rechazar o eliminar cookies. Al continuar navegando
                        en nuestro sitio, consientes el uso de cookies conforme a esta política. Algunas cookies son
                        esenciales para el funcionamiento de la web, mientras que otras nos ayudan a ofrecer un mejor
                        servicio, como identificar tus búsquedas recientes de profesionales.
                    </p>
                    <p>
                        Para más información sobre cómo gestionamos las cookies, puedes consultar la configuración de
                        tu navegador o escribirnos a <a href="mailto:info@fixitnow.com" class="link-primary">info@fixitnow.com</a>
                    </p>
                </div>

                <div class="policy-image">
                    <img src="media/politica-cookies.jpg" alt="Política de Cookies">
                </div>

                <div class="policy-download">
                    <a href="media/politica_cookies_document.pdf" download class="submit-btn">
                        Descargar Documento PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>