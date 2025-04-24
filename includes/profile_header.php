<?php
if (isset($_SESSION['usuario'])) {
    $in_profile_page = strpos($_SERVER['PHP_SELF'], 'perfil_') !== false;
    
    if (!$in_profile_page) {
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
                        <img src="data:image/jpeg;base64,<?= base64_encode($foto_perfil) ?>" alt="Foto de perfil" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    </div>
                <?php else: ?>
                    <div class="user-avatar"><?= strtoupper(substr($_SESSION['usuario']['nombre'], 0, 1)) ?></div>
                <?php endif; ?>
                <span class="user-name"><?= htmlspecialchars($_SESSION['usuario']['nombre'] . ' ' . $_SESSION['usuario']['apellido']) ?></span>
            </a>
        </div>
    <?php
    }
} else { ?>
    <a href="login.php" class="profile-btn">
        <span class="user-name">Iniciar Sesión</span>
    </a>
<?php } ?>