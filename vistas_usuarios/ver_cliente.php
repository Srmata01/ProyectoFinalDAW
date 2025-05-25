<?php
require_once '../config/database.php';
session_start();

// Incluir el componente de valorac    
require_once '../valoraciones/valoraciones_simple.php';

// Verificar que hay un usuario autenticado
if (!isset($_SESSION['usuario'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ../login.php');
    exit();
}

// Verificar que el usuario es un autónomo para ver perfiles de clientes
if ($_SESSION['usuario']['tipo'] != 3) {
    $_SESSION['error'] = "No tienes permisos para acceder a esta página.";
    header('Location: ../index.php');
    exit();
}

// Verificar que se proporcionó un ID de cliente
if (!isset($_GET['id'])) {
    header('Location: ../index.php');
    exit();
}

$id_cliente = $_GET['id'];
$id_autonomo = $_SESSION['usuario']['id'];

try {
    // Verificar que es un cliente que ha hecho alguna reserva con este autónomo
    $stmt = $pdo->prepare("
        SELECT DISTINCT c.id_usuario, c.nombre, c.apellido, c.email, c.telefono, c.foto_perfil,
               COUNT(r.id_reserva) as total_reservas
        FROM usuarios c
        JOIN reservas r ON c.id_usuario = r.id_cliente
        JOIN servicios s ON r.id_servicio = s.id_servicio
        WHERE c.id_usuario = ? AND s.id_autonomo = ? AND c.id_tipo_usuario = 2
        GROUP BY c.id_usuario
    ");
    $stmt->execute([$id_cliente, $id_autonomo]);
    $cliente = $stmt->fetch();

    if (!$cliente) {
        $_SESSION['error'] = "No se encontró el cliente solicitado o no tienes permiso para ver su información.";
        header('Location: perfil_autonomo.php');
        exit();
    }

} catch (PDOException $e) {
    die("Error de base de datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil de <?= htmlspecialchars($cliente['nombre']) ?> - FixItNow</title>
    <link rel="stylesheet" href="vistas.css">
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../includes/responsive-header.css">
    <link rel="stylesheet" href="../includes/footer.css">
    <link rel="icon" type="image/png" href="../media/logo.png">
</head>
<body>    <?php 
        $base_path = '../';
        include '../includes/header_template.php'; 
    ?>    <div class="responsive-container">
        <div class="profile-columns-container">
            <div class="profile-column">
                <div class="perfil-cliente">
                    <div class="info-principal">
                        <?php if (!empty($cliente['foto_perfil'])): ?>
                            <img src="data:image/jpeg;base64,<?= base64_encode($cliente['foto_perfil']) ?>" 
                                 alt="Foto de perfil" class="foto-perfil">
                        <?php else: ?>
                            <div class="user-avatar" style="width: 100px; height: 100px; margin: 0 auto; font-size: 2.5em; display: flex; align-items: center; justify-content: center; background-color: #FF9B00; color: white; border-radius: 50%;">
                                <?= strtoupper(substr($cliente['nombre'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <div>
                            <h1 class="detail-title">
                                <?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']) ?>
                            </h1>
                            <div class="detail-section">
                                <p><strong>Teléfono:</strong> <?= htmlspecialchars($cliente['telefono']) ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($cliente['email']) ?></p>
                                <p class="cliente-reservas-info"><small>Este cliente ha realizado <?= $cliente['total_reservas'] ?> reserva(s) de tus servicios.</small></p>
                            </div>
                        </div>
                    </div>
                    
                    <?php 
                    // Mostrar componente de valoraciones simplificado
                    mostrarValoraciones($cliente['id_usuario']);
                    ?>
                    
                    <div class="button-group" style="margin-top: 20px;">
                        <a href="perfil_autonomo.php" class="compact-btn">Volver a mi perfil</a>
                    </div>                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
      <script>
        // Script para asegurar que el contenido tiene altura mínima para evitar que el footer suba demasiado
        document.addEventListener('DOMContentLoaded', function() {
            var contentHeight = document.querySelector('.responsive-container').offsetHeight;
            var windowHeight = window.innerHeight;
            var headerHeight = document.querySelector('header').offsetHeight;
            var footerHeight = document.querySelector('footer').offsetHeight;
            
            if (contentHeight < windowHeight - headerHeight - footerHeight) {
                document.querySelector('.responsive-container').style.minHeight = 
                    (windowHeight - headerHeight - footerHeight) + 'px';
            }
        });
    </script>
</body>
</html>
