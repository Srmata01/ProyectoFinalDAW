<?php
require '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_usuario = $_POST['id_usuario']; // Quién ofrece el servicio
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $duracion = $_POST['duracion'];
    $estado = $_POST['estado']; // 'activo' o 'inactivo'

    $query = $pdo->prepare("INSERT INTO servicios (id_usuario, nombre, descripcion, precio, duracion, estado) VALUES (:id_usuario, :nombre, :descripcion, :precio, :duracion, :estado)");
    $query->bindParam(':id_usuario', $id_usuario);
    $query->bindParam(':nombre', $nombre);
    $query->bindParam(':descripcion', $descripcion);
    $query->bindParam(':precio', $precio);
    $query->bindParam(':duracion', $duracion);
    $query->bindParam(':estado', $estado);

    if ($query->execute()) {
        echo "Servicio creado exitosamente.";
    } else {
        echo "Error al crear el servicio.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Servicio</title>
</head>
<body>
<h2>Crear Servicio</h2>
<form method="POST" action="">
    ID Usuario (quién lo ofrece): <input type="number" name="id_usuario" required><br>
    Nombre del Servicio: <input type="text" name="nombre" required><br>
    Descripción: <textarea name="descripcion" required></textarea><br>
    Precio: <input type="number" step="0.01" name="precio" required><br>
    Duración (en minutos): <input type="number" name="duracion" required><br>
    Estado: 
    <select name="estado">
        <option value="activo">Activo</option>
        <option value="inactivo">Inactivo</option>
    </select><br>
    <button type="submit">Crear Servicio</button>
</form>

<!-- Botón para ir a la edición de servicios -->
<button onclick="window.location.href='editar_servicio.php'">Editar Servicios</button>

</body>
</html>
