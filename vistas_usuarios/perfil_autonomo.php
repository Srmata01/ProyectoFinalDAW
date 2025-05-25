<?php
session_start();
require_once '../config/database.php';

$base_path = '../'; // Definimos base_path ya que estamos en un subdirectorio
require_once $base_path . 'includes/header_template.php';

// Validación de sesión y tipo de usuario
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] != 3) {
    header('Location: ../login.php');
    exit();
}

$id_autonomo = $_SESSION['usuario']['id'];

try {
    // Obtener info del autónomo
    $stmt = $pdo->prepare("SELECT * FROM usuarios u WHERE u.id_usuario = ? AND u.id_tipo_usuario = 3");
    $stmt->execute([$id_autonomo]);
    $autonomo = $stmt->fetch();    // Servicios ofrecidos
    $stmt = $pdo->prepare("SELECT * FROM servicios WHERE id_autonomo = ? ORDER BY nombre ASC");
    $stmt->execute([$id_autonomo]);
    $servicios = $stmt->fetchAll();
    
    // Reservas adaptadas a la nueva estructura con estado_confirmacion
    $stmt = $pdo->prepare("
        SELECT r.*, s.nombre as servicio, 
               CONCAT(c.nombre, ' ', c.apellido) as cliente,
               c.id_usuario as id_cliente,
               c.telefono as telefono_cliente,
               DATE_FORMAT(r.fecha_hora, '%d/%m/%Y') as fecha_formateada,
               TIME_FORMAT(r.fecha_hora, '%H:%i') as hora_inicio_formateada,
               ADDTIME(TIME_FORMAT(r.fecha_hora, '%H:%i'), SEC_TO_TIME(s.duracion * 60)) as hora_fin_formateada,
               r.estado_confirmacion
        FROM reservas r
        JOIN servicios s ON r.id_servicio = s.id_servicio
        JOIN usuarios c ON r.id_cliente = c.id_usuario
        WHERE s.id_autonomo = ?
        ORDER BY r.estado_confirmacion = 'pendiente' DESC, r.fecha_hora ASC
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
    <link rel="stylesheet" href="../includes/responsive-header.css">
    <link rel="stylesheet" href="../includes/footer.css">
</head>
<body class="app">
    <div class="app-main">
        <div class="container1">
            <?php if (isset($mensaje)): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

        <div class="profile-columns-container">
            <!-- Columna izquierda: Datos del autónomo -->
            <div class="profile-column">
                <h2 class="document-title">Mis Datos</h2>
                <div class="profile-photo-container">                    <?php if (!empty($autonomo['foto_perfil'])): ?>
                        <img src="data:image/jpeg;base64,<?= base64_encode($autonomo['foto_perfil']) ?>" 
                             alt="Foto de perfil">
                    <?php else: ?>
                        <div class="user-avatar">
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
                    </div>                    <div class="form-actions">
                        <button type="submit" class="submit-btn">Guardar Cambios</button>
                        <button type="button" class="submit-btn" onclick="window.location.href='../portfolio/index.php'">Galería</button>
                        <button type="button" class="submit-btn" onclick="window.location.href='../reservas/horarios_autonomo.php'">Horarios</button>
                    </div>
                </form>
                
                <!-- Servicios del autónomo -->                <h2 class="document-title">Mis Servicios</h2>                <div class="form-actions">
                    <button type="button" class="submit-btn" onclick="window.location.href='../services/crear.php'">Añadir Servicio</button>
                </div>
                <?php if ($servicios): ?>
                    <div class="form-grid">
                        <table>
                            <thead>                                <tr>
                                    <th>Nombre</th>
                                    <th>Precio</th>
                                    <th>Duración</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($servicios as $servicio): ?>
                                    <tr id="servicio-<?= $servicio['id_servicio'] ?>">                                        <td><?= htmlspecialchars($servicio['nombre']) ?></td>
                                        <td><?= number_format($servicio['precio'], 2) ?></td>
                                        <td><?= $servicio['duracion'] ?></td>
                                        <td><?= ucfirst($servicio['estado']) ?></td>                                        <td class="form-actions">
                                            <button type="button" class="submit-btn" onclick="window.location.href='../services/editar.php?id=<?= $servicio['id_servicio'] ?>'">Editar</button>
                                            <button type="button" onclick="eliminarServicio(<?= $servicio['id_servicio'] ?>)" 
                                                    class="delete-btn">Eliminar</button>
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
                <h2 class="document-title">Reservas de Clientes</h2>                <?php if ($reservas): ?>
                    <div class="reservas-table-container responsive-cards">
                        <table class="reservas-table">                            
                            <thead>
                                <tr>                                    
                                    <th>Servicio</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Hora Inicio</th>
                                    <th>Hora Fin</th>
                                    <th>Estado</th>
                                    <th>Confirmación</th>
                                    <th class="actions-column">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reservas as $reserva): ?>
                                    <?php 
                                    // Definir clases de estado
                                    $estado_texto = '';
                                    
                                    if ($reserva['estado_confirmacion'] == 'pendiente') {
                                        $estado_texto = '<span class="reserva-estado reserva-pendiente">Pendiente</span>';
                                    } elseif ($reserva['estado_confirmacion'] == 'aceptada') {
                                        $estado_texto = '<span class="reserva-estado reserva-aceptada">Aceptada</span>';
                                    } elseif ($reserva['estado_confirmacion'] == 'rechazada') {
                                        $estado_texto = '<span class="reserva-estado reserva-rechazada">Rechazada</span>';
                                    }
                                    ?>                                    <tr>
                                        <td><?= htmlspecialchars($reserva['servicio']) ?></td>                                        <td>
                                            <?php if (isset($reserva['id_cliente'])): ?>
                                                <a href="ver_cliente.php?id=<?= $reserva['id_cliente'] ?>" 
                                                   title="Ver detalles del cliente" 
                                                   class="document-link">
                                                    <?= htmlspecialchars($reserva['cliente']) ?>
                                                </a>
                                            <?php else: ?>
                                                <?= htmlspecialchars($reserva['cliente']) ?>
                                            <?php endif; ?>                                        </td>
                                        <td><?= htmlspecialchars($reserva['fecha_formateada']) ?></td>
                                        <td><?= htmlspecialchars($reserva['hora_inicio_formateada']) ?></td>
                                        <td><?= htmlspecialchars($reserva['hora_fin_formateada']) ?></td>
                                        <td><?= ucfirst($reserva['estado']) ?></td>                                        <td><?= $estado_texto ?></td>
                                        <td class="actions-column"><?php if ($reserva['estado'] == 'pendiente' && $reserva['estado_confirmacion'] == 'pendiente'): ?>                                                <div class="button-group">
                                                    <button type="button" class="submit-btn" onclick="window.location.href='../reservas/aceptar_reserva.php?id=<?= $reserva['id_reserva'] ?>'">Aceptar</button>
                                                    <button type="button" class="delete-btn" onclick="window.location.href='../reservas/rechazar_reserva.php?id=<?= $reserva['id_reserva'] ?>'">Rechazar</button>
                                                </div>
                                            <?php elseif ($reserva['estado'] == 'pendiente' && $reserva['estado_confirmacion'] == 'aceptada'): ?>
                                                <button type="button" class="submit-btn" onclick="window.location.href='../reservas/completar_reserva.php?id=<?= $reserva['id_reserva'] ?>'">✔</button>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>                    </div>

                    <!-- Versión móvil con tarjetas -->
                    <div class="reservas-card-container responsive-cards">
                        <?php foreach ($reservas as $reserva): ?>
                            <?php
                            // Definir clases de estado
                            if ($reserva['estado_confirmacion'] == 'pendiente') {
                                $estado_texto = '<span class="reserva-estado reserva-pendiente">Pendiente</span>';
                            } elseif ($reserva['estado_confirmacion'] == 'aceptada') {
                                $estado_texto = '<span class="reserva-estado reserva-aceptada">Aceptada</span>';
                            } elseif ($reserva['estado_confirmacion'] == 'rechazada') {
                                $estado_texto = '<span class="reserva-estado reserva-rechazada">Rechazada</span>';
                            }
                            ?>
                            <div class="reservas-card">
                                <div class="reservas-card-header">
                                    <div class="reservas-card-title"><?= htmlspecialchars($reserva['servicio']) ?></div>
                                    <?= $estado_texto ?>
                                </div>
                                <div class="reservas-card-body">
                                    <p><strong>Cliente:</strong> <?= htmlspecialchars($reserva['cliente']) ?></p>
                                    <p><strong>Fecha:</strong> <?= htmlspecialchars($reserva['fecha_formateada']) ?></p>
                                    <p><strong>Horario:</strong> <?= htmlspecialchars($reserva['hora_inicio_formateada']) ?> - <?= htmlspecialchars($reserva['hora_fin_formateada']) ?></p>
                                </div>
                                <div class="reservas-card-footer">
                                    <div class="reservas-card-actions">
                                        <?php if ($reserva['estado'] == 'pendiente' && $reserva['estado_confirmacion'] == 'pendiente'): ?>
                                            <button type="button" class="submit-btn" onclick="window.location.href='../reservas/aceptar_reserva.php?id=<?= $reserva['id_reserva'] ?>'">Aceptar</button>
                                            <button type="button" class="delete-btn" onclick="window.location.href='../reservas/rechazar_reserva.php?id=<?= $reserva['id_reserva'] ?>'">Rechazar</button>
                                        <?php elseif ($reserva['estado'] == 'pendiente' && $reserva['estado_confirmacion'] == 'aceptada'): ?>
                                            <button type="button" class="submit-btn" onclick="window.location.href='../reservas/completar_reserva.php?id=<?= $reserva['id_reserva'] ?>'">Completar ✓</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="document-text">No tienes reservas activas.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>    <script>
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

    <?php include $base_path . 'includes/footer.php'; ?>
</body>
</html>
