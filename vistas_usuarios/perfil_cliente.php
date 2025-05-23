<?php
session_start();
require_once '../config/database.php';

$base_path = '../'; // Definimos base_path ya que estamos en un subdirectorio
require_once $base_path . 'includes/header_template.php';

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
    $cliente = $stmt->fetch();    if (!$cliente) {
        throw new Exception("No se pudo cargar tu perfil de cliente");
    }
    
    // Consulta actualizada para incluir el estado_confirmacion
    $stmt = $pdo->prepare("
        SELECT r.*, s.nombre as servicio, s.precio, s.duracion,
               CONCAT(a.nombre, ' ', a.apellido) as autonomo,
               a.id_usuario as id_autonomo,
               a.telefono as telefono_autonomo,
               DATE_FORMAT(r.fecha_hora, '%d/%m/%Y') as fecha_formateada,
               TIME_FORMAT(r.fecha_hora, '%H:%i') as hora_inicio_formateada,
               ADDTIME(TIME_FORMAT(r.fecha_hora, '%H:%i'), SEC_TO_TIME(s.duracion * 60)) as hora_fin_formateada,
               r.estado_confirmacion
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
    <link rel="stylesheet" href="../includes/responsive-header.css">
    <link rel="stylesheet" href="../includes/footer.css">
</head>
<body>    <div class="container1">
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 15px; margin: 15px; border-radius: 5px; text-align: center;">
                <?= htmlspecialchars($_SESSION['mensaje']) ?>
            </div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 15px; margin: 15px; border-radius: 5px; text-align: center;">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
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
                        <table>                            <thead>
                                <tr>
                                    <th>Servicio</th>
                                    <th>Autónomo</th>
                                    <th>Fecha</th>
                                    <th>Hora Inicio</th>
                                    <th>Hora Fin</th>
                                    <th>Precio</th>
                                    <th>Estado</th>
                                    <th>Confirmación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reservas as $reserva): ?>
                                    <?php
                                    // Estilo para la fila según el estado de confirmación
                                    $estilo_fila = '';
                                    $estado_texto = '';
                                    
                                    if ($reserva['estado_confirmacion'] == 'pendiente') {
                                        $estilo_fila = 'background-color: #fff3cd;'; // Amarillo claro
                                        $estado_texto = '<span style="color: #856404; font-weight: bold;">Pendiente</span>';
                                    } elseif ($reserva['estado_confirmacion'] == 'aceptada') {
                                        $estado_texto = '<span style="color: #155724; font-weight: bold;">Aceptada</span>';
                                    } elseif ($reserva['estado_confirmacion'] == 'rechazada') {
                                        $estilo_fila = 'background-color: #f8d7da;'; // Rojo claro
                                        $estado_texto = '<span style="color: #721c24; font-weight: bold;">Rechazada</span>';
                                    }
                                    ?>                                    <tr style="<?= $estilo_fila ?>">
                                        <td><?= htmlspecialchars($reserva['servicio'] ?? '') ?></td>
                                        <td>
                                            <a href="../vistas_usuarios/ver_autonomo.php?id=<?= $reserva['id_autonomo'] ?? '' ?>" 
                                               title="Ver perfil del profesional" 
                                               style="color: var(--color-primary); text-decoration: underline;">
                                                <?= htmlspecialchars($reserva['autonomo'] ?? '') ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($reserva['fecha_formateada'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($reserva['hora_inicio_formateada'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($reserva['hora_fin_formateada'] ?? '') ?></td>
                                        <td><?= isset($reserva['precio']) ? number_format($reserva['precio'], 2) . ' €' : '' ?></td>
                                        <td><?= isset($reserva['estado']) ? ucfirst(htmlspecialchars($reserva['estado'])) : '' ?></td>                                        <td><?= $estado_texto ?></td>
                                        <td class="form-actions">
                                            <?php if (($reserva['estado'] ?? '') == 'pendiente' && $reserva['estado_confirmacion'] != 'rechazada'): ?>
                                                <a href="../reservas/cancelar.php?id=<?= $reserva['id_reserva'] ?? '' ?>" class="submit-btn" style="padding: 8px 12px; font-size: 14px;">Cancelar</a>
                                            <?php endif; ?>
                                            <?php if ($reserva['estado_confirmacion'] == 'aceptada'): ?>
                                                <a href="contactar.php?id=<?= $reserva['id_servicio'] ?? '' ?>" class="submit-btn" style="padding: 8px 12px; font-size: 14px; background-color: var(--color-text-light);">Contactar</a>
                                            <?php endif; ?>
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
                    <a href="../services/buscarservicio.php" class="submit-btn">Buscar nuevos servicios</a>                </div>
            </div>
        </div>
    </div>
    <?php include $base_path . 'includes/footer.php'; ?>
</body>
</html>
