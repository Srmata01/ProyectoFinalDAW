<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proyecto Login</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
</head>
<body>

<form action="">
    <h1>Inicio de sesión</h1>
    <label for="usuario">Usuario</label>
    <input type="text" name="usuario" id="usuario" placeholder="Usuario">
    <label for="password">Contraseña</label>
    <input type="password" name="password" id="password" placeholder="Contraseña">
    <input type="submit" value="Iniciar sesión">
    <a href="registro.php">Registrarse</a>
    <a href="recuperar.php">Recuperar contraseña</a>
</form>
    
</body>
</html>

<?php

    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    $consulta = "SELECT * FROM usuarios WHERE usuario = '$usuario' AND password = '$password'";
    $resultado = mysqli_query($conexion, $consulta);

    $filas = mysqli_num_rows($resultado);

    if($filas > 0){
        header("location:inicio.php");
    }else{
        echo "Error en la autenticación";
    }

    mysqli_free_result($resultado);
    mysqli_close($conexion);

?>