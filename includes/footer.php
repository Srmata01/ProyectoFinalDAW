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

// Incluir el CSS específico del footer utilizando scoped styles para evitar conflictos
echo '<style>
/* Estilos exclusivos para el footer, no afectan al resto de la página */
body > footer {
    background-color: rgba(210, 210, 210, 0.5);
    padding: var(--space-sm, 0.5rem) 0;
    width: 100%;
    margin-top: auto;
}

body > footer .footer-container {
    max-width: 1200px;
    height: auto;
    min-height: 50px;
    margin: 0 auto;
    padding: var(--space-xs, 0.25rem) var(--space-md, 1rem);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
}

body > footer .footer-section {
    flex: 1 1 150px;
    margin: var(--space-xs, 0.25rem);
}

body > footer .footer-section h4 {
    font-size: var(--font-size-xs, 0.75rem);
    font-weight: bold;
    color: var(--color-primary-dark, #E08A00);
    margin-bottom: var(--space-xs, 0.25rem);
}

body > footer .footer-section ul {
    list-style: none;
    padding: 0;
}

body > footer .footer-section ul li a {
    text-decoration: none;
    color: var(--color-text-lighter, #555);
    font-size: var(--font-size-xs, 0.75rem);
    line-height: 1.5;
}

body > footer .social-icons img {
    width: 24px;
    margin: 0 var(--space-xs, 0.25rem);
    transition: transform 0.3s ease;
}

body > footer .social-icons img:hover {
    transform: translateY(-2px);
}

body > footer .footer-logo {
    text-align: center;
}

body > footer .footer-logo img {
    width: 80px;
}

/* Responsive styles - Específicos para el footer */
@media (max-width: 768px) {
    body > footer .footer-container {
        flex-wrap: wrap;
        justify-content: center;
        gap: var(--space-sm, 0.5rem);
    }
    
    body > footer .footer-section {
        flex: 0 0 100%;
        margin: var(--space-xs, 0.25rem) 0;
        text-align: center;
    }
    
    body > footer .footer-logo {
        flex: 0 0 100%;
        order: -1;
        margin-bottom: var(--space-sm, 0.5rem);
    }
}

@media (max-width: 576px) {
    body > footer {
        padding: var(--space-xs, 0.25rem) 0;
    }
    
    body > footer .footer-container {
        flex-direction: column;
        padding: var(--space-xs, 0.25rem);
    }
    
    body > footer .footer-logo img {
        width: 60px;
    }
}
</style>';
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