<?php
require_once '../config/database.php';

// Obtener el ID del usuario autónomo actual (deberías obtenerlo de la sesión)
session_start();
$id_autonomo = $_SESSION['user']['id'] ?? null;

if (!$id_autonomo) {
    die("Error: No se ha identificado al usuario autónomo");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO servicios 
            (id_usuario, nombre, descripcion, precio, duracion, estado) 
            VALUES (?, ?, ?, ?, ?, 'activo')
        ");
        
        $stmt->execute([
            $id_autonomo, // Usamos el ID del autónomo de la sesión
            $_POST['nombre'],
            $_POST['descripcion'],
            $_POST['precio'],
            $_POST['duracion']
        ]);
        
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        die("Error al crear el servicio: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Servicio</title>
</head>
<body>
    <h1>Crear Nuevo Servicio</h1>
    
    <?php if (isset($error)): ?>
        <p style="color: red;"><?= $error ?></p>
    <?php endif; ?>
    
    <form method="post">
        <p>Nombre: <input type="text" name="nombre" required></p>
        <p>Descripción: <textarea name="descripcion" required></textarea></p>
        <p>Precio (€): <input type="number" step="0.01" name="precio" required></p>
        <p>Duración (min): <input type="number" name="duracion" required></p>
        
        <button type="submit">Crear Servicio</button>
    </form>
    
    <p><a href="index.php">Volver al listado</a></p>
</body>
</html>