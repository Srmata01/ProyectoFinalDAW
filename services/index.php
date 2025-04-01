<?php
require_once '../config/database.php';

// Obtener todos los servicios activos
$servicios = $pdo->query("
    SELECT s.*, u.nombre AS autonomo 
    FROM servicios s
    JOIN usuarios u ON s.id_usuario = u.id_usuario
    WHERE s.estado = 'activo'
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Servicios Disponibles</title>
</head>
<body>
    <h1>Servicios Disponibles</h1>
    
    <table border="1">
        <tr>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Precio</th>
            <th>Duración</th>
            <th>Autónomo</th>
        </tr>
        <?php foreach ($servicios as $servicio): ?>
        <tr>
            <td><?= $servicio['nombre'] ?></td>
            <td><?= $servicio['descripcion'] ?></td>
            <td><?= $servicio['precio'] ?> €</td>
            <td><?= $servicio['duracion'] ?> min</td>
            <td><?= $servicio['autonomo'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <p><a href="crear.php">Crear nuevo servicio</a></p>
</body>
</html>