<?php
require_once __DIR__ . '/../config/database.php';

if (isset($_SESSION['usuario'])) {
    $in_profile_page = strpos($_SERVER['PHP_SELF'], 'perfil_') !== false;
      if (!$in_profile_page) {        // Determinar si estamos en un subdirectorio
        $is_subdirectory = strpos($_SERVER['PHP_SELF'], '/services/') !== false || 
        strpos($_SERVER['PHP_SELF'], '/vistas_usuarios/') !== false ||
        strpos($_SERVER['PHP_SELF'], '/reservas/') !== false ||
        strpos($_SERVER['PHP_SELF'], '/portfolio/') !== false ||
        strpos($_SERVER['PHP_SELF'], '/incidencias/') !== false ||
        strpos($_SERVER['PHP_SELF'], '/valoraciones/') !== false;
        $base_path = $is_subdirectory ? '../' : '';
        
        $perfil_url = '';
        switch ($_SESSION['usuario']['tipo']) {
            case 1:
                $perfil_url = $base_path . 'vistas_usuarios/perfil_admin.php';
                break;
            case 2:
                $perfil_url = $base_path . 'vistas_usuarios/perfil_cliente.php';
                break;
            case 3:
                $perfil_url = $base_path . 'vistas_usuarios/perfil_autonomo.php';
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
    <?php
    }
} else {    // También ajustar la ruta del login
    $is_subdirectory = strpos($_SERVER['PHP_SELF'], '/services/') !== false ||
                      strpos($_SERVER['PHP_SELF'], '/vistas_usuarios/') !== false ||
                      strpos($_SERVER['PHP_SELF'], '/reservas/') !== false ||
                      strpos($_SERVER['PHP_SELF'], '/portfolio/') !== false ||
                      strpos($_SERVER['PHP_SELF'], '/create_users/') !== false ||
                      strpos($_SERVER['PHP_SELF'], '/incidencias/') !== false ||
                      strpos($_SERVER['PHP_SELF'], '/valoraciones/') !== false;
    
    // Usar una ruta absoluta desde la raíz del servidor para evitar problemas de redirección
    $login_url = "/smata/ProyectoFinalDAW/login.php";
    ?>
    <a href="<?= $login_url ?>" class="profile-btn">
        <span class="user-name">Iniciar Sesión</span>
    </a>
<?php }?>