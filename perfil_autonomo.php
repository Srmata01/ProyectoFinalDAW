<?php
require_once 'config/database.php';
session_start();

// Validación de sesión y tipo de usuario
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_tipo_usuario'] != 3) {
    header('Location: login.php');
    exit();
}

$id_autonomo = $_SESSION['usuario']['id_usuario'];

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
    <title>Perfil de Autónomo</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h2>Bienvenido, <?= htmlspecialchars($autonomo['nombre']) ?></h2>
    </header>

    <section>
        <h3>Mi Perfil</h3>
        <form action="actualizar_perfil_autonomo.php" method="POST">
            <input type="hidden" name="id_usuario" value="<?= $autonomo['id_usuario'] ?>">
            <label>Nombre: <input type="text" name="nombre" value="<?= htmlspecialchars($autonomo['nombre']) ?>"></label>
            <label>Apellido: <input type="text" name="apellido" value="<?= htmlspecialchars($autonomo['apellido']) ?>"></label>
            <label>Email: <input type="email" name="email" value="<?= htmlspecialchars($autonomo['email']) ?>"></label>
            <label>Teléfono: <input type="tel" name="telefono" value="<?= htmlspecialchars($autonomo['telefono']) ?>"></label>
            <label>Dirección: <input type="text" name="direccion" value="<?= htmlspecialchars($autonomo['direccion']) ?>"></label>
            <label>CIF: <input type="text" name="CIF" value="<?= htmlspecialchars($autonomo['CIF']) ?>"></label>
            <button type="submit">Guardar Cambios</button>
        </form>
    </section>

    <section>
        <h3>Mis Servicios</h3>
        <a href="nuevo_servicio.php">Añadir nuevo servicio</a>
        <?php if ($servicios): ?>
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
                            <td>
                                <a href="editar_servicio.php?id=<?= $servicio['id_servicio'] ?>">Editar</a> |
                                <a href="eliminar_servicio.php?id=<?= $servicio['id_servicio'] ?>" onclick="return confirm('¿Eliminar este servicio?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No tienes servicios registrados.</p>
        <?php endif; ?>
    </section>

    <section>
        <h3>Reservas de Clientes</h3>
        <?php if ($reservas): ?>
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
                            <td>
                                <?php if ($reserva['estado'] == 'pendiente'): ?>
                                    <a href="aceptar_reserva.php?id=<?= $reserva['id_reserva'] ?>">Aceptar</a> |
                                    <a href="rechazar_reserva.php?id=<?= $reserva['id_reserva'] ?>">Rechazar</a>
                                <?php elseif ($reserva['estado'] == 'aceptada'): ?>
                                    <a href="completar_reserva.php?id=<?= $reserva['id_reserva'] ?>">Completar</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No tienes reservas activas.</p>
        <?php endif; ?>
    </section>

    <footer>
        <p>&copy; <?= date("Y") ?> Tu Empresa</p>
    </footer>
</body>
</html>
