<?php require_once '../config/database.php'; ?>
<!DOCTYPE html>
<html class="app">

<head>
    <title>Únete a nosotros</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../styles.css">
    <link rel="icon" type="image/png" href="../media/logo.png">
</head>

<style>
    .grid-layout {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
        padding: 20px;
    }

    .option-card {
        background-color: rgba(200, 200, 200, 0.4);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        /* border-radius eliminado */
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        text-align: center;
        padding: 20px;
        border-radius: 4px;
        transition: transform 0.2s ease;
    }

    .option-card:hover {
        transform: translateY(-5px);
    }

    .option-image {
        width: 100%;
        height: auto;
        /* se puede mantener redondeo de imagen si lo deseas */
        border-radius: 10px;
        margin-bottom: 10px;
    }

    .option-title a {
        color: rgb(78, 78, 78);
        text-decoration: none;
    }

    .document-container3 {
        background-color: transparent !important;
        box-shadow: none;
        margin-top: -50px;
    }

    .document-title {
        font-size: 2rem;
        margin-top: -30px;
        margin-bottom: 15px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .option-description {
        font-size: 1.3rem;
        color: white;
        margin: 0;
    }

    .register-block {
        text-align: center;
        margin-top: 25px;
    }

    .register-block .option-description {
        font-size: 1.3rem;
        color: #2c2c2c; /* Gris antracita */
        margin: 0;
    }

    .register-link {
        color: orange;
        text-decoration: underline;
        margin-left: 8px;
    }
</style>

<body class="app-body">
    <header class="app-header">
        <div class="header-container">
            <div class="logo-container">
                <a href="../main.html" class="logo-link">
                    <img src="../media/logo.png" alt="Logo FixItNow" class="logo">
                </a>
            </div>
        </div>
    </header>

    <main class="app-main">
        <div class="document-container2">
            <h1 class="document-title">Cómo quieres unirte a nosotros?</h1>
        </div>

        <div class="document-container3 grid-layout">
            <div class="option-card">
                <h2 class="option-title"><a href="registro_cliente.php" class="option-link">Cliente</a></h2>
                <br>
                <img src="../media/cliente.jpg" alt="Cliente" class="option-image">
                <br><br>
                <p class="option-description">Busca y contrata servicios</p>
            </div>
            <div class="option-card">
                <h2 class="option-title"><a href="registro_autonomo.php" class="option-link">Autónomo</a></h2>
                <br>
                <img src="../media/autonomo.jpg" alt="Autónomo" class="option-image">
                <br><br>
                <p class="option-description">Ofrece tus servicios profesionales</p>
            </div>
            <div class="option-card">
                <h2 class="option-title"><a href="registro_admin.php" class="option-link">Administrador</a></h2>
                <br>
                <img src="../media/admin.jpg" alt="Administrador" class="option-image">
                <br><br>
                <p class="option-description">Gestiona la plataforma</p>
            </div>
        </div>

        <!-- Bloque de registro debajo de las cards -->
        <div class="register-block">
            <p class="option-description">
                ¿Ya tienes cuenta?
                <a href="../login.php" class="register-link">Regístrate.</a>
            </p>
        </div>
    </main>

    <footer class="app-footer">
        <div class="footer-container">
            <div class="footer-section">
                <h4 class="footer-title">Información Personal</h4>
                <ul class="footer-list">
                    <li><a href="../politicaprivacidad.html" class="footer-link">Política de privacidad</a></li>
                    <li><a href="../politicacookiesdatos.html" class="footer-link">Política de Cookies y protección de datos</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4 class="footer-title">Contacto</h4>
                <ul class="footer-list">
                    <li><a href="mailto:fixitnow@gmail.com" class="footer-link">fixitnow@gmail.com</a></li>
                    <li><a href="tel:+34690096690" class="footer-link">+34 690 096 690</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4 class="footer-title">Eres miembro?</h4>
                <ul class="footer-list">
                    <li><a href="index.php" class="footer-link">Únete a Nosotros</a></li>
                </ul>
            </div>

            <div class="footer-section social-media">
                <div class="social-icons">
                    <a href="#" class="social-link"><img src="../media/twitter-icon.png" alt="Twitter" class="social-icon"></a>
                    <a href="#" class="social-link"><img src="../media/instagram-icon.png" alt="Instagram" class="social-icon"></a>
                    <a href="#" class="social-link"><img src="../media/facebook-icon.png" alt="Facebook" class="social-icon"></a>
                    <a href="#" class="social-link"><img src="../media/tiktok-icon.png" alt="TikTok" class="social-icon"></a>
                </div>
            </div>

            <div class="footer-logo">
                <img src="../media/logo.png" alt="FixItNow Logo" class="footer-logo-img">
            </div>
        </div>
    </footer>
</body>

</html>
