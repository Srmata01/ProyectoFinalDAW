<?php
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidad - FixItNow</title>
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
            <h1 class="document-title">Política de Privacidad</h1>
            
            <div class="policy-content">
                <div class="policy-section">
                    <h2>Compromiso con tu Privacidad</h2>
                    <p>
                        En Fix It Now, valoramos y protegemos tu privacidad. Todos los datos personales que nos proporcionas 
                        son tratados conforme al Reglamento General de Protección de Datos (RGPD) y la legislación española vigente. 
                        Nuestra misión es garantizar la seguridad y confidencialidad de tu información mientras facilitamos 
                        conexiones significativas entre usuarios y profesionales del sector.
                    </p>
                </div>

                <div class="policy-section">
                    <h2>Uso de tus Datos</h2>
                    <p>
                        La información que recopilamos se utiliza exclusivamente para:
                    </p>
                    <ul class="policy-list">
                        <li>Gestionar los servicios que solicitas</li>
                        <li>Mejorar la calidad de atención al cliente</li>
                        <li>Facilitar la comunicación entre usuarios y profesionales</li>
                        <li>Garantizar la seguridad de las transacciones</li>
                    </ul>
                </div>

                <div class="policy-section">
                    <h2>Tus Derechos</h2>
                    <p>
                        Como usuario de Fix It Now, tienes derecho a:
                    </p>
                    <ul class="policy-list">
                        <li>Acceder a tus datos personales</li>
                        <li>Rectificar la información inexacta</li>
                        <li>Solicitar la supresión de tus datos</li>
                        <li>Limitar u oponerte al tratamiento</li>
                    </ul>
                    <p>
                        Para ejercer estos derechos, puedes contactarnos en cualquier momento a través de 
                        <a href="mailto:info@fixitnow.com" class="link-primary">info@fixitnow.com</a>
                    </p>
                </div>

                <div class="policy-image">
                    <img src="media/politica-privacidad.jpg" alt="Política de Privacidad">
                </div>

                <div class="policy-download">
                    <a href="media/politica_privacidad_document.pdf" download class="submit-btn">
                        Descargar Documento PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>