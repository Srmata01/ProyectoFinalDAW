<?php
if (isset($_SESSION['usuario'])) {
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
    ?>
    <div class="profile-container">
        <a href="<?= $perfil_url ?>" class="profile-btn" style="text-decoration: none;">
            <div class="user-avatar"><?= strtoupper(substr($_SESSION['usuario']['nombre'], 0, 1)) ?></div>
            <span class="user-name"><?= htmlspecialchars($_SESSION['usuario']['nombre'] . ' ' . $_SESSION['usuario']['apellido']) ?></span>
        </a>
    </div>
<?php } else { ?>
    <a href="login.php" class="profile-btn">
        <span class="user-name">Iniciar Sesión</span>
    </a>
<?php } ?>