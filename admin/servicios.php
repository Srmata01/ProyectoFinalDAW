<?php
require_once '../config/database.php';
session_start();

// Verificar que el usuario es un administrador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] != 1) {
    header('Location: ../login.php');
    exit();
}

// Actualizar el estado de un servicio si se solicita
if (isset($_GET['accion']) && isset($_GET['id_servicio']) && is_numeric($_GET['id_servicio'])) {
    try {
        $id_servicio = $_GET['id_servicio'];
        $accion = $_GET['accion'];
        
        if ($accion === 'cambiar_estado') {
            // Obtener el estado actual del servicio
            $stmt = $pdo->prepare("
                SELECT estado 
                FROM servicios
                WHERE id_servicio = ?
            ");
            $stmt->execute([$id_servicio]);
            $servicio_estado = $stmt->fetch();
            
            if ($servicio_estado) {
                // Determinar el nuevo estado (alternando entre activo e inactivo)
                $nuevo_estado = ($servicio_estado['estado'] == 'activo') ? 'inactivo' : 'activo';
                
                // Actualizar el estado del servicio
                $stmt = $pdo->prepare("
                    UPDATE servicios 
                    SET estado = ? 
                    WHERE id_servicio = ?
                ");
                $stmt->execute([$nuevo_estado, $id_servicio]);
                
                $mensaje = "Estado del servicio actualizado correctamente.";
                $tipo_mensaje = "success";
            }
        } elseif ($accion === 'eliminar') {
            // Eliminar el servicio
            $stmt = $pdo->prepare("DELETE FROM servicios WHERE id_servicio = ?");
            $stmt->execute([$id_servicio]);
            
            if ($stmt->rowCount() > 0) {
                $mensaje = "Servicio eliminado correctamente.";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "No se pudo eliminar el servicio.";
                $tipo_mensaje = "danger";
            }
        }
    } catch (PDOException $e) {
        $mensaje = "Error al procesar la solicitud: " . $e->getMessage();
        $tipo_mensaje = "danger";
    }
}

// Obtener todos los servicios con información del autónomo
try {
    $stmt = $pdo->prepare("
        SELECT s.*, 
               u.nombre as autonomo_nombre, 
               u.apellido as autonomo_apellido,
               u.email as autonomo_email,
               eu.estado as estado_usuario
        FROM servicios s
        JOIN usuarios u ON s.id_autonomo = u.id_usuario
        JOIN estados_usuarios eu ON u.id_estado_usuario = eu.id_estado_usuario
        ORDER BY s.id_servicio DESC
    ");
    $stmt->execute();
    $servicios = $stmt->fetchAll();
} catch (PDOException $e) {
    $mensaje = "Error al obtener los servicios: " . $e->getMessage();
    $tipo_mensaje = "danger";
    $servicios = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Servicios - FixItNow</title>
    <link rel="stylesheet" href="../vistas_usuarios/vistas.css">
    <style>
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .admin-table th, .admin-table td {
            border: 1px solid #ddd;
            padding: 12px;
        }
        .admin-table th {
            background-color: #f2f2f2;
            text-align: left;
        }
        .admin-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .admin-table tr:hover {
            background-color: #f1f1f1;
        }
        .estado-activo {
            color: green;
            font-weight: bold;
        }
        .estado-inactivo {
            color: red;
            font-weight: bold;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }
        .alert-danger {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }
        .descripcion-cell {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .descripcion-cell:hover {
            white-space: normal;
            overflow: visible;
        }
    </style>
</head>
<body>    <header>
        <div class="header-container">
            <div class="logo-container">
                <a href="../index.php">
                    <img src="../media/logo.png" alt="Logo" class="logo">
                </a>
            </div>
            <div class="user-container">
                <div class="profile-container">
                    <?php include '../includes/profile_header.php'; ?>
                </div>
            </div>
        </div>
    </header>
    
    <div class="container1">
        <div class="document-container">
            <h1 class="document-title">Administración de Servicios</h1>
            
            <?php if (isset($mensaje)): ?>
                <div class="alert alert-<?= $tipo_mensaje ?>">
                    <?= $mensaje ?>
                </div>
            <?php endif; ?>
            
            <div class="admin-section">
                <h2>Listado de Servicios</h2>
                
                <?php if (empty($servicios)): ?>
                    <p>No hay servicios registrados.</p>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Autónomo</th>
                                <th>Descripción</th>
                                <th>Precio</th>
                                <th>Localidad</th>
                                <th>Estado</th>
                                <th>Estado Usuario</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($servicios as $servicio): ?>
                                <tr>
                                    <td><?= $servicio['id_servicio'] ?></td>
                                    <td><?= htmlspecialchars($servicio['nombre']) ?></td>
                                    <td><?= htmlspecialchars($servicio['autonomo_nombre'] . ' ' . $servicio['autonomo_apellido']) ?></td>
                                    <td class="descripcion-cell"><?= htmlspecialchars($servicio['descripcion']) ?></td>
                                    <td><?= htmlspecialchars($servicio['precio']) ?> €</td>
                                    <td><?= htmlspecialchars($servicio['localidad']) ?></td>
                                    <td class="estado-<?= strtolower($servicio['estado']) ?>">
                                        <?= htmlspecialchars(ucfirst($servicio['estado'])) ?>
                                    </td>
                                    <td class="estado-<?= strtolower($servicio['estado_usuario']) ?>">
                                        <?= htmlspecialchars($servicio['estado_usuario']) ?>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 10px;">
                                            <a href="?accion=cambiar_estado&id_servicio=<?= $servicio['id_servicio'] ?>" 
                                               onclick="return confirm('¿Estás seguro de cambiar el estado de este servicio?');"
                                               class="btn-editar" style="padding: 5px 10px; background-color: #FF9B00; color: white; text-decoration: none; border-radius: 4px;">
                                                <?= $servicio['estado'] == 'activo' ? 'Desactivar' : 'Activar' ?>
                                            </a>
                                            <a href="?accion=eliminar&id_servicio=<?= $servicio['id_servicio'] ?>" 
                                               onclick="return confirm('¿Estás seguro de ELIMINAR este servicio? Esta acción no se puede deshacer.');"
                                               class="btn-eliminar" style="padding: 5px 10px; background-color: #dc3545; color: white; text-decoration: none; border-radius: 4px;">
                                                Eliminar
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <div class="admin-actions">
                <a href="../vistas_usuarios/perfil_admin.php" class="submit-btn" style="background-color: #6c757d;">Volver al Panel de Administración</a>
            </div>
        </div>
    </div>
    
    <?php 
    // Definir la ruta base para el footer
    $base_path = '../';
    include '../includes/footer.php'; 
    ?>
</body>
</html>
