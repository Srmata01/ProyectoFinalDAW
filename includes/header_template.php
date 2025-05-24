<?php
/**
 * Plantilla de header con barra de búsqueda centrada
 * Para incluirlo, usar este código:
 * include '../includes/header_template.php';
 * 
 * Si la página está en una subcarpeta, asegúrate de definir $base_path
 * $base_path = '../'; // para archivos en subdirectorios
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
        strpos($current_path, '/valoraciones/') !== false ||
        strpos($current_path, '/admin/') !== false) {
        $base_path = '../';
    }
}

// Función para generar la ruta correcta para la barra de búsqueda
function getSearchPath($base_path) {
    // Si estamos en una subcarpeta, la ruta al search_form.php debe ajustarse
    if ($base_path === '../') {
        return '../includes/search_form.php';
    } else {
        return 'includes/search_form.php';
    }
}

// Función para generar la ruta correcta para la imagen de logo
function getLogoPath($base_path) {
    return $base_path . 'media/logo.png';
}

// Función para generar la ruta correcta para el script de búsqueda
function getBuscadorPath($base_path) {
    return $base_path . 'services/js/buscador.js';
}

// Ruta para la barra de búsqueda
$search_path = getSearchPath($base_path);
$logo_path = getLogoPath($base_path);
$buscador_path = getBuscadorPath($base_path);

// Detectar si estamos en una página de perfil
$is_profile_page = strpos($_SERVER['PHP_SELF'], 'perfil_') !== false;
?>

<header>
    <div class="header-container">
        <!-- Parte izquierda: Logo -->
        <div class="header-left">
            <div class="logo-container">
                <a href="<?= $base_path ?>index.php">
                    <img src="<?= $logo_path ?>" alt="Logo FixItNow" class="logo">
                </a>
            </div>
        </div>
        
        <!-- Parte central: Barra de búsqueda -->
        <div class="header-center">
            <div class="header-search-container">
                <?php 
                // Verificar si estamos en la página principal
                $is_index = basename($_SERVER['PHP_SELF']) === 'index.php' && !$base_path;
                // Solo incluir el buscador si no estamos en la página principal
                if (!$is_index) {
                    include $search_path; 
                }
                ?>
            </div>
        </div>
        
        <!-- Parte derecha: Perfil de usuario -->
        <div class="header-right">
            <div class="user-container">
                <?php include $base_path . 'includes/profile_header.php'; ?>
                <?php if ($is_profile_page && isset($_SESSION['usuario'])): ?>
                    <a href="<?= $base_path ?>includes/logout.php" class="logout-btn">Cerrar sesión</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="<?= $buscador_path ?>"></script>
</header>
