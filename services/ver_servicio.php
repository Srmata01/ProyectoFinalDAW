<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['usuario'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ../login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id_servicio = $_GET['id'];

try {    $stmt = $pdo->prepare("
        SELECT s.*, 
               u.id_usuario as autonomo_id,
               u.nombre as autonomo_nombre, 
               u.apellido as autonomo_apellido,
               u.foto_perfil as autonomo_foto,
               eu.estado as estado_usuario
        FROM servicios s
        JOIN usuarios u ON s.id_autonomo = u.id_usuario
        JOIN estados_usuarios eu ON u.id_estado_usuario = eu.id_estado_usuario
        WHERE s.id_servicio = ? AND s.estado = 'activo' AND eu.estado = 'Activo'
    ");
    $stmt->execute([$id_servicio]);
    $servicio = $stmt->fetch();

    if (!$servicio) {
        header('Location: index.php');
        exit();
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($servicio['nombre']) ?> - FixItNow</title>
    <link rel="stylesheet" href="../vistas_usuarios/vistas.css">
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../includes/responsive-header.css">    <style>
        /* Estilos específicos para mejorar el centrado */        
        .profile-columns-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .profile-column {
            width: 100%;
        }
        
        @media (max-width: 480px) {
            .user-name {
                display: inline-block !important;
            }
            
            .responsive-container {
                padding: 15px;
                margin-top: 70px;
            }

            .login-profile-box .submit-btn {
                padding: 1px 4px;
                font-size: calc(var(--font-size-xs) - 1px);
                min-height: 20px;
            }
        }
        
        @media (max-width: 768px) {
            .responsive-detail {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">            <div class="logo-container">
                <a href="../index.php">
                    <img src="../media/logo.png" alt="Logo" class="logo">
                </a>
            </div>
            <div class="user-container">
                <div class="profile-container">
                    <div class="login-profile-box">
                        <?php include '../includes/profile_header.php'; ?>
                    </div>
                </div>
            </div>
        </div>
    </header><div class="responsive-container">
        <div class="profile-columns-container">
            <div class="profile-column">
                <div class="responsive-detail">
                    <h1 class="detail-title"><?= htmlspecialchars($servicio['nombre']) ?></h1>
                    
                    <div class="user-module">
                        <?php if (!empty($servicio['autonomo_foto'])): ?>
                            <img src="data:image/jpeg;base64,<?= base64_encode($servicio['autonomo_foto']) ?>" 
                                 alt="Foto del profesional" class="user-photo">
                        <?php endif; ?>
                        <div class="user-info">
                            <h3>Profesional: 
                                <a href="../vistas_usuarios/ver_autonomo.php?id=<?= $servicio['autonomo_id'] ?>">
                                    <?= htmlspecialchars($servicio['autonomo_nombre'] . ' ' . $servicio['autonomo_apellido']) ?>
                                </a>
                            </h3>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3>Descripción del servicio</h3>
                        <p><?= htmlspecialchars($servicio['descripcion']) ?></p>
                        
                        <h3>Duración estimada</h3>
                        <p><?= $servicio['duracion'] ?> minutos</p>
                        
                        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
                            <div class="price-section"><?= number_format($servicio['precio'], 2) ?> €</div>
                            <div class="button-group">
                                <a href="../vistas_usuarios/ver_autonomo.php?id=<?= $servicio['autonomo_id'] ?>" 
                                   class="compact-btn secondary">Ver perfil del profesional</a>
                                <?php if (isset($_SESSION['usuario']) && $_SESSION['usuario']['tipo'] == 2): ?>
                                    <a href="../reservas/crear.php?servicio=<?= $servicio['id_servicio'] ?>" 
                                       class="compact-btn">Reservar este servicio</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><?php 
    $base_path = '../';
    include '../includes/footer.php'; 
    ?>
  
</body>
</html>