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
    $stmt = $pdo->prepare("SELECT * FROM servicios WHERE id_autonomo = ? ORDER BY nombre ASC");
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

// Mensajes de estado
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Autónomo</title>
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
        <?php if (isset($mensaje)): ?>
            <div class="success-message"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="profile-columns-container">
            <!-- Columna izquierda: Datos del autónomo -->
            <div class="profile-column">
                <h2 class="document-title">Mis Datos</h2>
                <div class="profile-photo-container">
                    <?php if (!empty($autonomo['foto_perfil'])): ?>
                        <img src="data:image/jpeg;base64,<?= base64_encode($autonomo['foto_perfil']) ?>" 
                             alt="Foto de perfil" 
                             style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover;">
                    <?php else: ?>
                        <div class="user-avatar" style="width: 150px; height: 150px; margin: 0 auto; font-size: 3em;">
                            <?= strtoupper(substr($autonomo['nombre'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <form action="actualizar_perfil_autonomo.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_usuario" value="<?= $_SESSION['usuario']['id'] ?>">
                    <div class="form-row">
                        <label>Nombre: <input type="text" name="nombre" value="<?= htmlspecialchars($_SESSION['usuario']['nombre']) ?>"></label>
                        <label>Apellido: <input type="text" name="apellido" value="<?= htmlspecialchars($_SESSION['usuario']['apellido']) ?>"></label>
                        <label>Email: <input type="email" name="email" value="<?= htmlspecialchars($_SESSION['usuario']['email']) ?>"></label>
                        <label>Teléfono: <input type="tel" name="telefono" value="<?= htmlspecialchars($_SESSION['usuario']['telefono']) ?>"></label>
                        <label>Dirección: <input type="text" name="direccion" value="<?= htmlspecialchars($_SESSION['usuario']['direccion']) ?>"></label>
                        <label>DNI/NIF: <input type="text" name="DNI" value="<?= htmlspecialchars($autonomo['DNI'] ?? '') ?>"></label>
                        <label>Foto de perfil: <input type="file" name="foto_perfil" accept="image/*"></label>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="submit-btn">Guardar Cambios</button>
                    </div>
                </form>
                
                <!-- Servicios del autónomo -->
                <h2 class="document-title" style="margin-top: 30px;">Mis Servicios</h2>
                <div class="form-actions" style="margin-bottom: 20px;">
                    <a href="../services/crear.php" class="submit-btn">Añadir nuevo servicio</a>
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
                                    <tr id="servicio-<?= $servicio['id_servicio'] ?>">
                                        <td><?= htmlspecialchars($servicio['nombre']) ?></td>
                                        <td><?= htmlspecialchars($servicio['descripcion']) ?></td>
                                        <td><?= number_format($servicio['precio'], 2) ?></td>
                                        <td><?= $servicio['duracion'] ?></td>
                                        <td><?= ucfirst($servicio['estado']) ?></td>
                                        <td class="form-actions">
                                            <a href="../services/editar.php?id=<?= $servicio['id_servicio'] ?>" class="submit-btn">Editar</a>
                                            <button onclick="eliminarServicio(<?= $servicio['id_servicio'] ?>)" 
                                                    class="submit-btn" style="background-color: #dc3545;">Eliminar</button>
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

            <!-- Columna derecha: Reservas de clientes -->
            <div class="profile-column">
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

    <script>
    function eliminarServicio(id) {
        if (confirm('¿Estás seguro de que deseas eliminar este servicio?')) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '../services/eliminar_servicio.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (this.status === 200) {
                    const fila = document.getElementById('servicio-' + id);
                    if (fila) {
                        fila.remove();
                    }
                    alert('Servicio eliminado correctamente');
                } else {
                    const mensaje = this.responseText || 'Error al eliminar el servicio';
                    alert(mensaje);
                }
            };
            
            xhr.send('id=' + id);
        }
    }
    </script>
</body>
</html>
