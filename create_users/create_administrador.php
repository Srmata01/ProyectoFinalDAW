<?php
require '../config/conexion.php';

$id_estado_usuario = $_POST['id_estado_usuario'] ?? null; // Asegurar que el dato existe
if (!$id_estado_usuario) {
    die("Error: No se ha recibido el ID del estado del usuario.");
}

// Comprobar si el estado existe en la base de datos
$checkQuery = $pdo->prepare("SELECT COUNT(*) FROM estados_usuarios WHERE id_estado_usuario = :id_estado_usuario");
$checkQuery->bindParam(':id_estado_usuario', $id_estado_usuario, PDO::PARAM_INT);
$checkQuery->execute();
$estadoExiste = $checkQuery->fetchColumn();

if ($estadoExiste == 0) {
    die("Error: El estado del usuario seleccionado no existe.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $email = $_POST['email'];
    $clave = password_hash($_POST['contraseña'], PASSWORD_DEFAULT);
    $telefono = $_POST['telefono'] ?? NULL;
    $direccion = $_POST['direccion'] ?? NULL;
    $estado_usuario = 1; // Activo por defecto

    $query = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, contraseña, telefono, direccion, id_tipo_usuario, id_estado_usuario) VALUES (:nombre, :apellido, :email, :clave, :telefono, :direccion, :tipo, :estado)");
    $query->bindParam(':nombre', $nombre);
    $query->bindParam(':apellido', $apellido);
    $query->bindParam(':email', $email);
    $query->bindParam(':clave', $clave);
    $query->bindParam(':telefono', $telefono);
    $query->bindParam(':direccion', $direccion);
    $query->bindParam(':tipo', $tipo_usuario);
    $query->bindParam(':estado', $estado_usuario);

    if ($query->execute()) {
        echo "Administrador creado exitosamente.";
    } else {
        echo "Error al crear administrador.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Administrador</title>
</head>
<body>
<h2>Crear Administrador</h2>
<form method="POST" action="">
    Nombre: <input type="text" name="nombre" required><br>
    Apellido: <input type="text" name="apellido" required><br>
    Email: <input type="email" name="email" required><br>
    Contraseña: <input type="password" name="contraseña" required><br>
    Teléfono: <input type="text" name="telefono"><br>
    Dirección: <textarea name="direccion"></textarea><br>
    <button type="submit">Crear Administrador</button>
    <button onclick="window.location.href='index.php'">Seleccionar otro tipo de usuario</button>
</form>
</body>
</html>
