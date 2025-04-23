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
    <style>.grid-layout {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
        padding: 20px;
    }

    /* ✅ Fondo gris claro en las tarjetas */
    .option-card {
        background-color: rgba(210, 210, 210, 0.5);
        /* este color estaba mal declarado */
        border-radius: 14px;
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        text-align: center;
        padding: 20px;
        transition: transform 0.2s ease;
        margin-top: -50px;
    }

    .option-card:hover {
        transform: translateY(-5px);
    }

    .option-image {
        width: 100%;
        height: auto;
        border-radius: 10px;
        margin-bottom: 10px;
    }

    .option-title a {
        color:rgb(78, 78, 78);
        text-decoration: none;
    }

    /* ❌ Quitar fondo de document-container3 */
    .document-container3 {
        background-color: transparent !important;
        box-shadow: none;
    }
</style>

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
            <p class="option-description"><a href="../login.php">Ya tienes cuenta?</a></p>
        </div>
        <br>
        <div class="document-container3 grid-layout">

            <div class="option-card">
                <h2 class="option-title"><a href="registro_cliente.php" class="option-link">Cliente</a></h2>
                <br>
                <img src="../media/cliente.jpg" alt="Cliente" class="option-image">
                <br><br>
                <p class="option-description">Busca y contrata servicios</p>
            </div>
            <br>
            <div class="option-card">
                <h2 class="option-title"><a href="registro_autonomo.php" class="option-link">Autónomo</a></h2>
                <br>
                <img src="../media/autonomo.jpg" alt="Autónomo" class="option-image">
                <br><br>
                <p class="option-description">Ofrece tus servicios profesionales</p>
            </div>
            <br>
            <div class="option-card">
                <h2 class="option-title"><a href="registro_admin.php" class="option-link">Administrador</a></h2>
                <br>
                <img src="../media/admin.jpg" alt="Administrador" class="option-image">
                <br><br>
                <p class="option-description">Gestiona la plataforma</p>
            </div>
        </div>
    </main>

    <footer class="app-footer">
        <div class="footer-container">
            <div class="footer-section">
                <h4 class="footer-title">Información Personal</h4>
                <ul class="footer-list">
                    <li><a href="../politicaprivacidad.html" class="footer-link">Política de privacidad</a></li>
                    <li><a href="../politicacookiesdatos.html" class="footer-link">Política de Cookies y protección de
                            datos</a></li>
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
                    <a href="#" class="social-link"><img src="../media/twitter-icon.png" alt="Twitter"
                            class="social-icon"></a>
                    <a href="#" class="social-link"><img src="../media/instagram-icon.png" alt="Instagram"
                            class="social-icon"></a>
                    <a href="#" class="social-link"><img src="../media/facebook-icon.png" alt="Facebook"
                            class="social-icon"></a>
                    <a href="#" class="social-link"><img src="../media/tiktok-icon.png" alt="TikTok"
                            class="social-icon"></a>
                </div>
            </div>

            <div class="footer-logo">
                <img src="../media/logo.png" alt="FixItNow Logo" class="footer-logo-img">
            </div>
        </div>
    </footer>
</body>

</html>