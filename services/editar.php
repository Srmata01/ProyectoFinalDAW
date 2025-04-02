<?php
require_once '../config/database.php';

$id_servicio = $_GET['id'] ?? 0;

// Obtener el servicio a editar
$servicio = $pdo->query("SELECT * FROM servicios WHERE id_servicio = $id_servicio")->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $duracion = $_POST['duracion'];
    $estado = $_POST['estado'];
    
    $pdo->query("UPDATE servicios SET 
                nombre = '$nombre', 
                descripcion = '$descripcion', 
                precio = $precio, 
                duracion = $duracion, 
                estado = '$estado' 
                WHERE id_servicio = $id_servicio");
    
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
        <p>
            <label>Nombre:<br>
                <input type="text" name="nombre" value="<?= $servicio['nombre'] ?>" required>
            </label>
        </p>
        
        <p>
            <label>Descripción:<br>
                <textarea name="descripcion" required><?= $servicio['descripcion'] ?></textarea>
            </label>
        </p>
        
        <p>
            <label>Precio (€):<br>
                <input type="number" step="0.01" name="precio" value="<?= $servicio['precio'] ?>" required>
            </label>
        </p>
        
        <p>
            <label>Duración (minutos):<br>
                <input type="number" name="duracion" value="<?= $servicio['duracion'] ?>" required>
            </label>
        </p>
        
        <p>
            <label>Estado:<br>
                <select name="estado">
                    <option value="activo" <?= $servicio['estado'] == 'activo' ? 'selected' : '' ?>>Activo</option>
                    <option value="inactivo" <?= $servicio['estado'] == 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </label>
        </p>
        
        <button type="submit">Guardar Cambios</button>
        <a href="index.php">Cancelar</a>
    </form>
</body>
</html>