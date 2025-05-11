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
                    <input type="text" placeholder="Buscar proyectos, servicios..." id="buscador-principal" class="search-input">
                    <img src="media/lupa.png" alt="Buscar" class="search-icon" id="btn-buscar">
                </div>
                <!-- Contenedor de resultados para el buscador -->
                <div id="resultados-busqueda" class="resultados-busqueda-container"></div>
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
            <h2 class="document-title">Documentación sobre la Política de Privacidad</h2>
            <br><br>
            <p class="document-text">Aquí puedes descargar el documento con toda la información necesaria sobre nuestra
                política de cookies.Aquí puedes descargar el documento con toda la información necesaria sobre nuestra
                política de cookies.Aquí puedes descargar el documento con toda la información necesaria sobre nuestra
                política de cookies.
                <br><br>
                Aquí puedes descargar el documento con toda la información necesaria sobre nuestra política de
                cookies.Aquí puedes descargar el documento con toda la información necesaria sobre nuestra política de
                cookies.Aquí puedes descargar el documento con toda la información necesaria sobre nuestra política de
                cookies.
                <br><br>
                Aquí puedes descargar el documento con toda la información necesaria sobre nuestra política de
                cookies.Aquí puedes descargar el documento con toda la información necesaria sobre nuestra política de
                cookies.Aquí puedes descargar el documento con toda la información necesaria sobre nuestra política de
                cookies.
                <br><br>
                Aquí puedes descargar el documento con toda la información necesaria sobre nuestra política de cookies.
            </p>
            <br><br>
            <a href="media/politica_privacidad_document.pdf" download class="download-button">Descargar Documento</a>
        </div>
    </main>

    <footer class="app-footer">
        <div class="footer-container">
            <div class="footer-section">
                <h4 class="footer-title">Información Personal</h4>
                <ul class="footer-list">
                    <li><a href="politicaprivacidad.php" class="footer-link">Política de privacidad</a></li>
                    <li><a href="politicacookiesdatos.php" class="footer-link">Política de Cookies y protección de
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
                <h4 class="footer-title">¿Eres miembro?</h4>
                <ul class="footer-list">
                    <li><a href="create_users/index.php" class="footer-link">Únete a Nosotros</a></li>
                </ul>
            </div>

            <div class="footer-section social-media">
                <div class="social-icons">
                    <a href="#" class="social-link"><img src="media/twitter-icon.png" alt="Twitter"
                            class="social-icon"></a>
                    <a href="#" class="social-link"><img src="media/instagram-icon.png" alt="Instagram"
                            class="social-icon"></a>
                    <a href="#" class="social-link"><img src="media/facebook-icon.png" alt="Facebook"
                            class="social-icon"></a>
                    <a href="#" class="social-link"><img src="media/tiktok-icon.png" alt="TikTok"
                            class="social-icon"></a>
                </div>
            </div>

            <div class="footer-logo">
                <img src="media/logo.png" alt="FixItNow Logo" class="footer-logo-img">
            </div>
        </div>
    </footer>

    <!-- Inicializar el buscador -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar el buscador con las opciones adecuadas
            const buscador = new Buscador({
                inputSelector: '#buscador-principal',
                resultsSelector: '#resultados-busqueda',
                searchUrl: 'services/php/buscar.php',
                autocompleteUrl: 'services/php/autocompletar.php',
                tipo: 'general',
                minChars: 2,
                submitButton: '#btn-buscar',
                onResultsLoaded: function(html) {
                    // Mostrar el contenedor de resultados cuando hay resultados
                    const resultadosContainer = document.getElementById('resultados-busqueda');
                    resultadosContainer.style.display = html.trim() ? 'block' : 'none';
                }
            });
        });
    </script>
</body>

</html>