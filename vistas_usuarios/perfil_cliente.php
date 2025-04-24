<?php
require_once '../config/database.php';
session_start();

// Corregido: usamos 'tipo' en vez de 'id_tipo_usuario'
if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['id']) || $_SESSION['usuario']['tipo'] != 2) {
    header('Location: ../login.php');
    exit();
}

$id_cliente = $_SESSION['usuario']['id'];

try {
    $stmt = $pdo->prepare("
        SELECT u.*, eu.estado as estado_usuario 
        FROM usuarios u
        JOIN estados_usuarios eu ON u.id_estado_usuario = eu.id_estado_usuario
        WHERE u.id_usuario = ?
    ");
    $stmt->execute([$id_cliente]);
    $cliente = $stmt->fetch();

    if (!$cliente) {
        throw new Exception("No se pudo cargar tu perfil de cliente");
    }

    $stmt = $pdo->prepare("
        SELECT r.*, s.nombre as servicio, s.precio, s.duracion,
               CONCAT(a.nombre, ' ', a.apellido) as autonomo,
               a.telefono as telefono_autonomo
        FROM reservas r
        JOIN servicios s ON r.id_servicio = s.id_servicio
        JOIN usuarios a ON s.id_autonomo = a.id_usuario
        WHERE r.id_cliente = ?
        ORDER BY r.fecha_hora DESC
    ");
    $stmt->execute([$id_cliente]);
    $reservas = $stmt->fetchAll() ?: [];

} catch (PDOException $e) {
    die("Error de base de datos: " . $e->getMessage());
} catch (Exception $e) {
    die($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Cliente</title>
    <link rel="stylesheet" href="../styles.css">
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
                    <a href="../includes/logout.php" class="submit-btn" style="margin-left: 10px;">Cerrar sesión</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container1">
        <div class="document-container">
            <h1 class="document-title">Mi Perfil</h1>
            <div class="form-grid">
                <div class="form-row">
                    <label>
                        <span>Nombre:</span>
                        <input type="text" value="<?= htmlspecialchars($cliente['nombre'] ?? '') ?>" readonly>
                    </label>
                    <label>
                        <span>Apellido:</span>
                        <input type="text" value="<?= htmlspecialchars($cliente['apellido'] ?? '') ?>" readonly>
                    </label>
                    <label>
                        <span>Email:</span>
                        <input type="email" value="<?= htmlspecialchars($cliente['email'] ?? '') ?>" readonly>
                    </label>
                    <label>
                        <span>Teléfono:</span>
                        <input type="tel" value="<?= htmlspecialchars($cliente['telefono'] ?? '') ?>" readonly>
                    </label>
                    <label>
                        <span>Dirección:</span>
                        <input type="text" value="<?= htmlspecialchars($cliente['direccion'] ?? '') ?>" readonly>
                    </label>
                    <label>
                        <span>Estado:</span>
                        <input type="text" value="<?= htmlspecialchars($cliente['estado_usuario'] ?? '') ?>" readonly>
                    </label>
                </div>
                <div class="form-actions">
                    <a href="editar_perfil.php" class="submit-btn">Editar perfil</a>
                </div>
            </div>
        </div>

        <div class="document-container">
            <h2 class="document-title">Mis Reservas</h2>
            <?php if (!empty($reservas)): ?>
                <div class="form-grid">
                    <table>
                        <thead>
                            <tr>
                                <th>Servicio</th>
                                <th>Autónomo</th>
                                <th>Fecha y Hora</th>
                                <th>Precio</th>
                                <th>Duración</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservas as $reserva): ?>
                                <tr>
                                    <td><?= htmlspecialchars($reserva['servicio'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($reserva['autonomo'] ?? '') ?></td>
                                    <td><?= isset($reserva['fecha_hora']) ? date('d/m/Y H:i', strtotime($reserva['fecha_hora'])) : '' ?></td>
                                    <td><?= isset($reserva['precio']) ? number_format($reserva['precio'], 2) . ' €' : '' ?></td>
                                    <td><?= isset($reserva['duracion']) ? $reserva['duracion'] . ' min' : '' ?></td>
                                    <td><?= isset($reserva['estado']) ? ucfirst(htmlspecialchars($reserva['estado'])) : '' ?></td>
                                    <td class="form-actions">
                                        <?php if (($reserva['estado'] ?? '') == 'pendiente'): ?>
                                            <a href="cancelar_reserva.php?id=<?= $reserva['id_reserva'] ?? '' ?>" class="submit-btn" style="padding: 8px 12px; font-size: 14px;">Cancelar</a>
                                        <?php endif; ?>
                                        <a href="contactar.php?id=<?= $reserva['id_servicio'] ?? '' ?>" class="submit-btn" style="padding: 8px 12px; font-size: 14px; background-color: var(--color-text-light);">Contactar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="document-text">No tienes reservas actualmente.</p>
            <?php endif; ?>
            <div class="form-actions" style="margin-top: 20px;">
                <a href="buscar_servicios.php" class="submit-btn">Buscar nuevos servicios</a>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h4 class="footer-title">Contacto</h4>
                <ul class="footer-list">
                    <li><a href="#" class="footer-link">info@empresa.com</a></li>
                    <li><a href="#" class="footer-link">Tel: 123 456 789</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4 class="footer-title">Legal</h4>
                <ul class="footer-list">
                    <li><a href="#" class="footer-link">Términos y condiciones</a></li>
                    <li><a href="#" class="footer-link">Política de privacidad</a></li>
                </ul>
            </div>
        </div>
    </footer>
</body>
</html>
