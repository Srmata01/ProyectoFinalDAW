<?php
require '../config/conexion.php';

// Obtener todos los servicios
$query = $pdo->query("SELECT * FROM servicios");
$servicios = $query->fetchAll(PDO::FETCH_ASSOC);

// Si se envía la solicitud para eliminar un servicio
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['eliminar'])) {
    $id_servicio = $_POST['id_servicio'];

    $deleteQuery = $pdo->prepare("DELETE FROM servicios WHERE id_servicio = :id_servicio");
    $deleteQuery->bindParam(':id_servicio', $id_servicio);

    if ($deleteQuery->execute()) {
        echo "<script>alert('Servicio eliminado exitosamente.'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Error al eliminar el servicio.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Servicios</title>
</head>
<body>
<h2>Listado de Servicios</h2>

<!-- Botón para añadir nuevos servicios -->
<button onclick="window.location.href='crear_servicio.php'">Añadir Servicio</button>

<table border="1">
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Descripción</th>
        <th>Precio</th>
        <th>Duración</th>
        <th>Estado</th>
        <th>Acciones</th>
    </tr>
    <?php foreach ($servicios as $servicio): ?>
        <tr>
            <td><?= $servicio['id_servicio'] ?></td>
            <td><?= htmlspecialchars($servicio['nombre']) ?></td>
            <td><?= htmlspecialchars($servicio['descripcion']) ?></td>
            <td><?= number_format($servicio['precio'], 2) ?> €</td>
            <td><?= $servicio['duracion'] ?> min</td>
            <td><?= $servicio['estado'] ?></td>
            <td>
                <a href="editar_servicio.php?id_servicio=<?= $servicio['id_servicio'] ?>">Editar</a>
                
                <!-- Botón de eliminación con confirmación -->
                <form method="POST" action="" style="display:inline;">
                    <input type="hidden" name="id_servicio" value="<?= $servicio['id_servicio'] ?>">
                    <button type="submit" name="eliminar" onclick="return confirm('¿Estás seguro de que quieres eliminar este servicio?')">Eliminar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
