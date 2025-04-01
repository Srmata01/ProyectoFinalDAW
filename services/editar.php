<?php
require_once '../config/database.php';

// Obtener servicio a editar
$id = $_GET['id'] ?? 0;
$servicio = $pdo->query("SELECT * FROM servicios WHERE id_servicio = $id")->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("
        UPDATE servicios SET
        nombre = ?, descripcion = ?, precio = ?, duracion = ?, estado = ?
        WHERE id_servicio = ?
    ");
    $stmt->execute([
        $_POST['nombre'],
        $_POST['descripcion'],
        $_POST['precio'],
        $_POST['duracion'],
        $_POST['estado'],
        $id
    ]);
    
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Servicio</title>
</head>
<body>
    <h1>Editar Servicio</h1>
    
    <form method="post">
        <p>Nombre: <input type="text" name="nombre" value="<?= $servicio['nombre'] ?>" required></p>
        <p>Descripción: <textarea name="descripcion" required><?= $servicio['descripcion'] ?></textarea></p>
        <p>Precio (€): <input type="number" step="0.01" name="precio" value="<?= $servicio['precio'] ?>" required></p>
        <p>Duración (min): <input type="number" name="duracion" value="<?= $servicio['duracion'] ?>" required></p>
        <p>Estado: 
            <select name="estado">
                <option value="activo" <?= $servicio['estado'] === 'activo' ? 'selected' : '' ?>>Activo</option>
                <option value="inactivo" <?= $servicio['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
            </select>
        </p>
        
        <button type="submit">Guardar Cambios</button>
    </form>
    
    <p><a href="index.php">Volver al listado</a></p>
</body>
</html>