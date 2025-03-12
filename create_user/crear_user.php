<?php
require '../config/conexion.php';

if (!isset($_GET['tipo']) || !in_array($_GET['tipo'], [2, 3])) {
    die("Tipo de usuario no válido.");
}
$tipo_usuario = $_GET['tipo'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $clave = password_hash($_POST['clave'], PASSWORD_DEFAULT);
    
    $query = $pdo->prepare("INSERT INTO usuarios (usuario, clave, id_tipo_usuario) VALUES (:usuario, :clave, :tipo)");
    $query->bindParam(':usuario', $usuario);
    $query->bindParam(':clave', $clave);
    $query->bindParam(':tipo', $tipo_usuario);
    
    if ($query->execute()) {
        echo "Usuario creado exitosamente.";
    } else {
        echo "Error al crear usuario.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario</title>
</head>
<body>
<h2>Crear Usuario</h2>
<form method="POST" action="">
    Usuario: <input type="text" name="usuario" required><br>
    Clave: <input type="password" name="clave" required><br>
    <button type="submit">Crear Usuario</button>
</form>
</body>
</html>
