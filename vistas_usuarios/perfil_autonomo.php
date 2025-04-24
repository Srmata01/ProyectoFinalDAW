<?php
require_once '../config/database.php';
session_start();

// Validación de sesión y tipo de usuario
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] != 3) {
    header('Location: ../login.php');
    exit();
}

$id_autonomo = $_SESSION['usuario']['id'];

try {
    // Obtener info del autónomo
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$id_autonomo]);
    $autonomo = $stmt->fetch();

    // Servicios ofrecidos
    $stmt = $pdo->prepare("SELECT * FROM servicios WHERE id_autonomo = ?");
    $stmt->execute([$id_autonomo]);
    $servicios = $stmt->fetchAll();

    // Reservas
    $stmt = $pdo->prepare("
        SELECT r.*, s.nombre as servicio, 
               CONCAT(c.nombre, ' ', c.apellido) as cliente,
               c.telefono as telefono_cliente
        FROM reservas r
        JOIN servicios s ON r.id_servicio = s.id_servicio
        JOIN usuarios c ON r.id_cliente = c.id_usuario
        WHERE s.id_autonomo = ?
        ORDER BY r.fecha_hora DESC
    ");
    $stmt->execute([$id_autonomo]);
    $reservas = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Error al obtener datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Autónomo</title>
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
            <h2 class="document-title">Mi Perfil</h2>
            <div class="form-grid">
                <form action="actualizar_perfil_autonomo.php" method="POST">
                    <input type="hidden" name="id_usuario" value="<?= $_SESSION['usuario']['id'] ?>">
                    <label>Nombre: <input type="text" name="nombre" value="<?= htmlspecialchars($_SESSION['usuario']['nombre']) ?>"></label>
                    <label>Apellido: <input type="text" name="apellido" value="<?= htmlspecialchars($_SESSION['usuario']['apellido']) ?>"></label>
                    <label>Email: <input type="email" name="email" value="<?= htmlspecialchars($_SESSION['usuario']['email']) ?>"></label>
                    <label>Teléfono: <input type="tel" name="telefono" value="<?= htmlspecialchars($_SESSION['usuario']['telefono']) ?>"></label>
                    <label>Dirección: <input type="text" name="direccion" value="<?= htmlspecialchars($_SESSION['usuario']['direccion']) ?>"></label>
                    <label>CIF: <input type="text" name="CIF" value="<?= htmlspecialchars($autonomo['CIF'] ?? '') ?>"></label>
                    <button type="submit" class="submit-btn">Guardar Cambios</button>
                </form>
            </div>
        </div>

        <div class="document-container">
            <h2 class="document-title">Mis Servicios</h2>
            <div class="form-actions">
                <a href="nuevo_servicio.php" class="submit-btn">Añadir nuevo servicio</a>
            </div>
            <?php if ($servicios): ?>
                <div class="form-grid">
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Precio (€)</th>
                                <th>Duración (min)</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($servicios as $servicio): ?>
                                <tr>
                                    <td><?= htmlspecialchars($servicio['nombre']) ?></td>
                                    <td><?= htmlspecialchars($servicio['descripcion']) ?></td>
                                    <td><?= number_format($servicio['precio'], 2) ?></td>
                                    <td><?= $servicio['duracion'] ?></td>
                                    <td><?= ucfirst($servicio['estado']) ?></td>
                                    <td class="form-actions">
                                        <a href="editar_servicio.php?id=<?= $servicio['id_servicio'] ?>" class="submit-btn">Editar</a>
                                        <a href="eliminar_servicio.php?id=<?= $servicio['id_servicio'] ?>" onclick="return confirm('¿Eliminar este servicio?')" class="submit-btn" style="background-color: #dc3545;">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="document-text">No tienes servicios registrados.</p>
            <?php endif; ?>
        </div>

        <div class="document-container">
            <h2 class="document-title">Reservas de Clientes</h2>
            <?php if ($reservas): ?>
                <div class="form-grid">
                    <table>
                        <thead>
                            <tr>
                                <th>Servicio</th>
                                <th>Cliente</th>
                                <th>Teléfono</th>
                                <th>Fecha y Hora</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservas as $reserva): ?>
                                <tr>
                                    <td><?= htmlspecialchars($reserva['servicio']) ?></td>
                                    <td><?= htmlspecialchars($reserva['cliente']) ?></td>
                                    <td><?= htmlspecialchars($reserva['telefono_cliente']) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($reserva['fecha_hora'])) ?></td>
                                    <td><?= ucfirst($reserva['estado']) ?></td>
                                    <td class="form-actions">
                                        <?php if ($reserva['estado'] == 'pendiente'): ?>
                                            <a href="aceptar_reserva.php?id=<?= $reserva['id_reserva'] ?>" class="submit-btn">Aceptar</a>
                                            <a href="rechazar_reserva.php?id=<?= $reserva['id_reserva'] ?>" class="submit-btn" style="background-color: #dc3545;">Rechazar</a>
                                        <?php elseif ($reserva['estado'] == 'aceptada'): ?>
                                            <a href="completar_reserva.php?id=<?= $reserva['id_reserva'] ?>" class="submit-btn">Completar</a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="document-text">No tienes reservas activas.</p>
            <?php endif; ?>
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
