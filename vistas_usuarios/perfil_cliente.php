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

            <div class="search-container">
                <div class="search-box">
                    <input type="text" placeholder="Buscar proyectos, materiales..." class="search-input">
                    <img src="../media/lupa.png" alt="Buscar" class="search-icon">
                </div>
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
        <div class="profile-columns-container">
            <!-- Columna izquierda: Datos del cliente -->
            <div class="profile-column">
                <h2 class="document-title">Mis Datos</h2>
                <div class="profile-photo-container">
                    <?php if (!empty($cliente['foto_perfil'])): ?>
                        <img src="data:image/jpeg;base64,<?= base64_encode($cliente['foto_perfil']) ?>" 
                             alt="Foto de perfil" 
                             style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover;">
                    <?php else: ?>
                        <div class="user-avatar" style="width: 150px; height: 150px; margin: 0 auto; font-size: 3em;">
                            <?= strtoupper(substr($cliente['nombre'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <form action="actualizar_perfil_cliente.php" method="POST" enctype="multipart/form-data">
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
                        <label>
                            <span>Foto de perfil:</span>
                            <input type="file" name="foto_perfil" accept="image/*">
                        </label>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="submit-btn">Guardar cambios</button>
                    </div>
                </form>
            </div>

            <!-- Columna derecha: Reservas -->
            <div class="profile-column">
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
                <div class="form-actions">
                    <a href="../services/buscarservicio.php" class="submit-btn">Buscar nuevos servicios</a>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h4>Información Personal</h4>
                <ul>
                    <li><a href="../politicaprivacidad.html">Política de privacidad</a></li>
                    <li><a href="../politicacookiesdatos.html">Política de Cookies y protección de datos</a></li>
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
                    <li><a href="../create_users/index.php">Únete a Nosotros</a></li>
                </ul>
            </div>
            
            <div class="footer-section social-media">
                <div class="social-icons">
                    <a href="#"><img src="../media/twitter-icon.png" alt="Twitter"></a>
                    <a href="#"><img src="../media/instagram-icon.png" alt="Instagram"></a>
                    <a href="#"><img src="../media/facebook-icon.png" alt="Facebook"></a>
                    <a href="#"><img src="../media/tiktok-icon.png" alt="TikTok"></a>
                </div>
            </div>
            
            <div class="footer-logo">
                <img src="../media/logo.png" alt="FixItNow Logo">
            </div>
        </div>
    </footer>
</body>
</html>
