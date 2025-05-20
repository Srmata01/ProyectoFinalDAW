<?php
/**
 * Footer reutilizable para todas las páginas del sitio
 * Para incluirlo, usar include/require y definir $base_path si es necesario:
 * $base_path = '../'; // para archivos en subdirectorios, ajustar según profundidad
 * include 'includes/footer.php';
 */

// Detectar automáticamente la profundidad del directorio
if (!isset($base_path)) {
    $current_path = $_SERVER['PHP_SELF'];
    $base_path = '';
    
    // Determinar la profundidad del directorio actual
    if (strpos($current_path, '/services/') !== false || 
        strpos($current_path, '/vistas_usuarios/') !== false ||
        strpos($current_path, '/reservas/') !== false ||
        strpos($current_path, '/portfolio/') !== false ||
        strpos($current_path, '/create_users/') !== false ||
        strpos($current_path, '/incidencias/') !== false ||
        strpos($current_path, '/valoraciones/') !== false) {
        $base_path = '../';
    }
}
?>
<footer>
    <div class="footer-container">
        <div class="footer-section">
            <h4>Información Personal</h4>
            <ul>
                <li><a href="<?= $base_path ?>politicaprivacidad.php">Política de privacidad</a></li>
                <li><a href="<?= $base_path ?>politicacookiesdatos.php">Política de Cookies y protección de datos</a></li>
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
            <h4>¿Eres miembro?</h4>
            <ul>
                <li><a href="<?= $base_path ?>create_users/index.php">Únete a Nosotros</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h4>¿Tienes algún problema?</h4>
            <ul>
                <li><a href="<?= $base_path ?>incidencias/crear.php">Reportar incidencia</a></li>
            </ul>
        </div>
        
        <div class="footer-section social-media">
            <div class="social-icons">
                <a href="#"><img src="<?= $base_path ?>media/twitter-icon.png" alt="Twitter"></a>
                <a href="#"><img src="<?= $base_path ?>media/instagram-icon.png" alt="Instagram"></a>
                <a href="#"><img src="<?= $base_path ?>media/facebook-icon.png" alt="Facebook"></a>
                <a href="#"><img src="<?= $base_path ?>media/tiktok-icon.png" alt="TikTok"></a>
            </div>
        </div>
        
        <div class="footer-logo">
            <img src="<?= $base_path ?>media/logo.png" alt="FixItNow Logo">
        </div>
    </div>
</footer>