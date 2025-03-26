<?php
require '../config/conexion.php';

// Obtener todos los servicios existentes
$query = $pdo->query("SELECT * FROM servicios");
$servicios = $query->fetchAll(PDO::FETCH_ASSOC);

// Si se envía el formulario para actualizar un servicio
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_servicio']) && isset($_POST['actualizar'])) {
    $id_servicio = $_POST['id_servicio'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $duracion = $_POST['duracion'];
    $estado = $_POST['estado'];

    $updateQuery = $pdo->prepare("UPDATE servicios SET nombre = :nombre, descripcion = :descripcion, precio = :precio, duracion = :duracion, estado = :estado WHERE id_servicio = :id_servicio");
    $updateQuery->bindParam(':nombre', $nombre);
    $updateQuery->bindParam(':descripcion', $descripcion);
    $updateQuery->bindParam(':precio', $precio);
    $updateQuery->bindParam(':duracion', $duracion);
    $updateQuery->bindParam(':estado', $estado);
    $updateQuery->bindParam(':id_servicio', $id_servicio);

    if ($updateQuery->execute()) {
        echo "Servicio actualizado exitosamente.";
    } else {
        echo "Error al actualizar el servicio.";
    }
}

// Si se envía la solicitud para eliminar un servicio
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_servicio']) && isset($_POST['eliminar'])) {
    $id_servicio = $_POST['id_servicio'];

    $deleteQuery = $pdo->prepare("DELETE FROM servicios WHERE id_servicio = :id_servicio");
    $deleteQuery->bindParam(':id_servicio', $id_servicio);

    if ($deleteQuery->execute()) {
        echo "Servicio eliminado exitosamente.";
    } else {
        echo "Error al eliminar el servicio.";
    }
}

// Obtener datos del servicio seleccionado
$servicio_seleccionado = null;
if (isset($_GET['id_servicio'])) {
    $id_servicio = $_GET['id_servicio'];
    $query = $pdo->prepare("SELECT * FROM servicios WHERE id_servicio = :id_servicio");
    $query->bindParam(':id_servicio', $id_servicio);
    $query->execute();
    $servicio_seleccionado = $query->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Servicio</title>
</head>
<body>
<h2>Editar Servicio</h2>

<!-- Lista de servicios para seleccionar -->
<form method="GET" action="">
    <label for="id_servicio">Selecciona un servicio para editar:</label>
    <select name="id_servicio" id="id_servicio" onchange="this.form.submit()">
        <option value="">-- Seleccionar --</option>
        <?php foreach ($servicios as $servicio): ?>
            <option value="<?= $servicio['id_servicio'] ?>" <?= isset($id_servicio) && $id_servicio == $servicio['id_servicio'] ? 'selected' : '' ?>>
                <?= $servicio['nombre'] ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<!-- Formulario para editar o eliminar el servicio -->
<?php if ($servicio_seleccionado): ?>
    <form method="POST" action="">
        <input type="hidden" name="id_servicio" value="<?= $servicio_seleccionado['id_servicio'] ?>">
        Nombre del Servicio: <input type="text" name="nombre" value="<?= $servicio_seleccionado['nombre'] ?>" required><br>
        Descripción: <textarea name="descripcion" required><?= $servicio_seleccionado['descripcion'] ?></textarea><br>
        Precio: <input type="number" step="0.01" name="precio" value="<?= $servicio_seleccionado['precio'] ?>" required><br>
        Duración (en minutos): <input type="number" name="duracion" value="<?= $servicio_seleccionado['duracion'] ?>" required><br>
        Estado: 
        <select name="estado">
            <option value="activo" <?= $servicio_seleccionado['estado'] == 'activo' ? 'selected' : '' ?>>Activo</option>
            <option value="inactivo" <?= $servicio_seleccionado['estado'] == 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
        </select><br>

        <!-- Botón para actualizar -->
        <button type="submit" name="actualizar">Actualizar Servicio</button>

        <!-- Botón para eliminar -->
        <button type="submit" name="eliminar" onclick="return confirm('¿Estás seguro de que quieres eliminar este servicio? Esta acción no se puede deshacer.')">Eliminar Servicio</button>
    </form>
<?php endif; ?>

<!-- Botón para volver a la creación de servicios -->
<button onclick="window.location.href='crear_servicio.php'">Crear un nuevo servicio</button>

</body>
</html>
