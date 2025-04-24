<?php
session_start();
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
            <!-- Logo a la izquierda -->
            <div class="logo-container">
                <a href="main.php">
                    <img src="media/logo.png" alt="Logo FixItNow" class="logo">
                </a>
            </div>

            <!-- Perfil de usuario a la derecha -->
            <div class="user-container">
                <?php include 'includes/profile_header.php'; ?>
            </div>
        </div>
    </header>

    <style>
        /* Estilos para el contenedor del video */
        .video-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        /* Ajustar el video para cubrir toda la pantalla */
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
        }

        /* Contenido encima del video */
        .content {
            position: relative;
            z-index: 1;
            color: white;
            text-align: center;
            font-size: 2rem;
            padding: 20px;
        }
    </style>
    </head>

    <body>

        <div class="video-background">
            <video autoplay muted loop>
                <source src="media/videocorp1.mp4" type="video/mp4">
                Tu navegador no soporta videos en HTML5.
            </video>
        </div>


        <div class="container1">
            <div class="content">
                <h1>Encuentra a tu profesional cerca de ti</h1>
                <p class="subtitulo1">Soluciona tus obras de la forma más rápida</p>
            </div>

            <div class="search-container">
                <div class="search-box">
                    <input type="text" placeholder="Buscar lampistas, fontaneros, paletas..." class="search-input">
                    <img src="media/lupa.png" alt="" onclick="">
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
