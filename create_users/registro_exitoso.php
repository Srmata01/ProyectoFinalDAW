<?php
$tipo = isset($_GET['tipo']) ? htmlspecialchars($_GET['tipo']) : 'usuario';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Registro Exitoso</title>
    <meta charset="UTF-8">
</head>
<body>
    <h1>¡Registro completado con éxito!</h1>
    <p>Tu cuenta como <?= ucfirst($tipo) ?> ha sido creada y está <strong>activa</strong>.</p>
    <p><a href="../login.php">Volver al inicio</a></p>
</body>
</html> 