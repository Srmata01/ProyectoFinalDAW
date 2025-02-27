<?php
session_start();
require '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    $query = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = :usuario AND password = :password");
    $query->bindParam(':usuario', $usuario);
    $query->bindParam(':password', $password);
    $query->execute();

    if ($query->rowCount() > 0) {
        $_SESSION['usuario'] = $usuario;
        header("Location: ../index.php");
        exit;
    } else {
        echo "<script>alert('Usuario o contraseña incorrectos');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
<h2>Login</h2>
<form method="POST" action="">
    Usuario: <input type="text" name="usuario" required><br>
    Contraseña: <input type="password" name="password" required><br>
    <button type="submit">Iniciar Sesión</button>
</form>
</body>
</html>