<?php
require '../config/conexion.php';

$tipo_usuario = 2; // Cliente

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
        echo "Cliente creado exitosamente.";
    } else {
        echo "Error al crear cliente.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cliente</title>
</head>
<body>
<h2>Crear Cliente</h2>
<form method="POST" action="">
    Nombre: <input type="text" name="nombre" required><br>
    Apellido: <input type="text" name="apellido" required><br>
    Email: <input type="email" name="email" required><br>
    Contraseña: <input type="password" name="contraseña" required><br>
    Teléfono: <input type="text" name="telefono"><br>
    Dirección: <textarea name="direccion"></textarea><br>
    <button type="submit">Crear Cliente</button>
    <button onclick="window.location.href='seleccionar_tipo.php'">Seleccionar otro tipo de usuario</button> 
</form>
</body>
</html>
