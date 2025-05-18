<?php
require_once '../config/database.php';
session_start();

// Incluir el componente de valoraciones simplificado
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
    header('Location: ../main.php');
    exit();
}

// Verificar que se proporcionó un ID de cliente
if (!isset($_GET['id'])) {
    header('Location: ../main.php');
    exit();
}

$id_cliente = $_GET['id'];
$id_autonomo = $_SESSION['usuario']['id'];

try {
    // Verificar que es un cliente que ha hecho alguna reserva con este autónomo
    $stmt = $pdo->prepare("
        SELECT DISTINCT c.id_usuario, c.nombre, c.apellido, c.email, c.telefono,
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Cliente - FixItNow</title>
    <link rel="stylesheet" href="vistas.css">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo-container">
                <a href="../main.php">
                    <img src="../media/logo.png" alt="Logo FixItNow" class="logo">
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
                <h2 class="document-title">Información del cliente</h2>
                
                <div class="feature-coming-soon" style="text-align: center; padding: 40px; background-color: #f8f9fa; border-radius: 10px; margin-bottom: 30px;">
                    <h3>Funcionalidad en desarrollo</h3>
                    <p>La visualización detallada de perfiles de clientes estará disponible próximamente.</p>
                    <p>Por ahora, puedes contactar con <?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']) ?> a través de:</p>
                    <p><strong>Teléfono:</strong> <?= htmlspecialchars($cliente['telefono']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($cliente['email']) ?></p>
                    <p style="margin-top: 20px;"><small>Este cliente ha realizado <?= $cliente['total_reservas'] ?> reserva(s) de tus servicios.</small></p>                </div>
                
                <?php 
                // Mostrar componente de valoraciones simplificado
                mostrarValoraciones($cliente['id_usuario']);
                ?>
                
                <div class="form-actions">
                    <a href="perfil_autonomo.php" class="submit-btn">Volver a mi perfil</a>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
