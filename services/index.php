<?php
require_once '../config/database.php';

// Obtener todos los servicios
$servicios = $pdo->query("SELECT * FROM servicios")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Servicios</title>
</head>
<body>
    <h1>Servicios Disponibles</h1>
    
    <a href="crear.php" class="btn btn-new">Crear Nuevo Servicio</a>
    
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Precio</th>
                <th>Duración</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($servicios as $servicio): ?>
            <tr id="servicio-<?= $servicio['id_servicio'] ?>">
                <td><?= htmlspecialchars($servicio['nombre']) ?></td>
                <td><?= htmlspecialchars($servicio['descripcion']) ?></td>
                <td><?= number_format($servicio['precio'], 2) ?> €</td>
                <td><?= $servicio['duracion'] ?> min</td>
                <td><?= ucfirst($servicio['estado']) ?></td>
                <td>
                    <a href="editar.php?id=<?= $servicio['id_servicio'] ?>" class="btn btn-edit">Editar</a>
                    <button onclick="eliminarServicio(<?= $servicio['id_servicio'] ?>)" class="btn btn-delete">Eliminar</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
    function eliminarServicio(id) {
        if (confirm('¿Estás seguro de que deseas eliminar este servicio?')) {
            // Crear una solicitud AJAX
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'eliminar_servicio.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (this.status === 200) {
                    // Eliminar la fila de la tabla si la respuesta es exitosa
                    const fila = document.getElementById('servicio-' + id);
                    if (fila) {
                        fila.remove();
                    }
                    alert('Servicio eliminado correctamente');
                } else {
                    alert('Error al eliminar el servicio');
                }
            };
            
            xhr.send('id=' + id);
        }
    }
    </script>
</body>
</html>