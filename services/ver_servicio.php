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

try {
    $stmt = $pdo->prepare("
        SELECT s.*, 
               u.id_usuario as autonomo_id,
               u.nombre as autonomo_nombre, 
               u.apellido as autonomo_apellido,
               u.telefono as autonomo_telefono,
               u.foto_perfil as autonomo_foto
        FROM servicios s
        JOIN usuarios u ON s.id_autonomo = u.id_usuario
        WHERE s.id_servicio = ? AND s.estado = 'activo'
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
    <title><?= htmlspecialchars($servicio['nombre']) ?> - FixItNow</title>
    <link rel="stylesheet" href="../vistas_usuarios/vistas.css">
    <style>
        .servicio-detalle {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        .autonomo-info {
            display: flex;
            align-items: center;
            margin: 20px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
        }
        .autonomo-foto {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 20px;
        }
        .precio-reserva {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .precio {
            font-size: 24px;
            color: #FF9B00;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo-container">
                <a href="../main.php">
                    <img src="../media/logo.png" alt="Logo" class="logo">
                </a>
            </div>
            <div class="user-container">
                <div class="profile-container">
                    <?php include '../includes/profile_header.php'; ?>
                </div>
            </div>
        </div>
    </header>

    <div class="container1">
        <div class="profile-columns-container">
            <div class="profile-column">
                <div class="servicio-detalle">
                    <h1 class="document-title"><?= htmlspecialchars($servicio['nombre']) ?></h1>
                    
                    <div class="autonomo-info">
                        <?php if (!empty($servicio['autonomo_foto'])): ?>
                            <img src="data:image/jpeg;base64,<?= base64_encode($servicio['autonomo_foto']) ?>" 
                                 alt="Foto del profesional" class="autonomo-foto">
                        <?php endif; ?>
                        <div>
                            <h3>Profesional: 
                                <a href="../vistas_usuarios/ver_autonomo.php?id=<?= $servicio['autonomo_id'] ?>">
                                    <?= htmlspecialchars($servicio['autonomo_nombre'] . ' ' . $servicio['autonomo_apellido']) ?>
                                </a>
                            </h3>
                            <p>Teléfono: <?= htmlspecialchars($servicio['autonomo_telefono']) ?></p>
                        </div>
                    </div>

                    <div class="servicio-contenido">
                        <h3>Descripción del servicio</h3>
                        <p><?= htmlspecialchars($servicio['descripcion']) ?></p>
                        
                        <h3>Duración estimada</h3>
                        <p><?= $servicio['duracion'] ?> minutos</p>

                        <div class="precio-reserva">
                            <div class="precio"><?= number_format($servicio['precio'], 2) ?> €</div>
                            <?php if (isset($_SESSION['usuario']) && $_SESSION['usuario']['tipo'] == 2): ?>
                                <a href="../reservas/crear.php?servicio=<?= $servicio['id_servicio'] ?>" 
                                   class="submit-btn">Reservar este servicio</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php 
    $base_path = '../';
    include '../includes/footer.php'; 
    ?>
</body>
</html>