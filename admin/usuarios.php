<?php
require_once '../config/database.php';
session_start();

// Verificar que el usuario es un administrador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] != 1) {
    header('Location: ../login.php');
    exit();
}

// Cambiar el estado de un usuario o eliminarlo si se solicita
if (isset($_GET['accion']) && isset($_GET['id_usuario']) && is_numeric($_GET['id_usuario'])) {
    try {
        $id_usuario = $_GET['id_usuario'];
        $accion = $_GET['accion'];
        
        if ($accion === 'cambiar_estado') {
            // Obtener el estado actual del usuario
            $stmt = $pdo->prepare("
                SELECT u.id_estado_usuario, eu.estado 
                FROM usuarios u
                JOIN estados_usuarios eu ON u.id_estado_usuario = eu.id_estado_usuario
                WHERE u.id_usuario = ?
            ");
            $stmt->execute([$id_usuario]);
            $usuario_estado = $stmt->fetch();              if ($usuario_estado) {
                // Determinar el nuevo estado (alternando entre Activo e Inactivo)
                $nuevo_estado_id = (strtolower($usuario_estado['estado']) == 'activo') ? 2 : 1; // 1=Activo, 2=Inactivo
                
                // Actualizar el estado del usuario
                $stmt = $pdo->prepare("
                    UPDATE usuarios 
                    SET id_estado_usuario = ? 
                    WHERE id_usuario = ?
                ");
                $stmt->execute([$nuevo_estado_id, $id_usuario]);
                
                // Si el usuario es autónomo, actualizar también el estado de sus servicios
                if ($nuevo_estado_id == 2) { // Si se está desactivando al usuario
                    // Consultar si el usuario tiene servicios
                    $stmt = $pdo->prepare("
                        SELECT COUNT(*) as total_servicios
                        FROM servicios
                        WHERE id_autonomo = ?
                    ");
                    $stmt->execute([$id_usuario]);
                    $servicios_count = $stmt->fetch();
                    
                    if ($servicios_count['total_servicios'] > 0) {
                        // Desactivar todos los servicios del usuario
                        $stmt = $pdo->prepare("
                            UPDATE servicios
                            SET estado = 'inactivo'
                            WHERE id_autonomo = ?
                        ");
                        $stmt->execute([$id_usuario]);
                        $mensaje = "Estado de usuario y sus servicios actualizados correctamente.";
                    } else {
                        $mensaje = "Estado de usuario actualizado correctamente.";
                    }
                } else if ($nuevo_estado_id == 1) { // Si se está activando al usuario
                    // Consultar si el usuario tiene servicios
                    $stmt = $pdo->prepare("
                        SELECT COUNT(*) as total_servicios
                        FROM servicios
                        WHERE id_autonomo = ?
                    ");
                    $stmt->execute([$id_usuario]);
                    $servicios_count = $stmt->fetch();
                    
                    if ($servicios_count['total_servicios'] > 0) {
                        // Activar todos los servicios del usuario
                        $stmt = $pdo->prepare("
                            UPDATE servicios
                            SET estado = 'activo'
                            WHERE id_autonomo = ?
                        ");
                        $stmt->execute([$id_usuario]);
                        $mensaje = "Estado de usuario y sus servicios actualizados correctamente.";
                    } else {
                        $mensaje = "Estado de usuario actualizado correctamente.";
                    }
                }
                
                $tipo_mensaje = "success";
            }
        } elseif ($accion === 'eliminar') {
            // Verificar si es el usuario actual
            if ($id_usuario == $_SESSION['usuario']['id']) {
                $mensaje = "No puedes eliminar tu propio usuario.";
                $tipo_mensaje = "danger";
            } else {
                // Eliminar el usuario
                $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
                $stmt->execute([$id_usuario]);
                
                if ($stmt->rowCount() > 0) {
                    $mensaje = "Usuario eliminado correctamente.";
                    $tipo_mensaje = "success";
                } else {
                    $mensaje = "No se pudo eliminar el usuario.";
                    $tipo_mensaje = "danger";
                }
            }
        }
    } catch (PDOException $e) {
        $mensaje = "Error al procesar la solicitud: " . $e->getMessage();
        $tipo_mensaje = "danger";
    }
}

// Obtener todos los usuarios
try {
    $stmt = $pdo->prepare("
        SELECT u.*, tu.tipo as tipo_usuario, eu.estado as estado_usuario
        FROM usuarios u
        JOIN tipos_usuarios tu ON u.id_tipo_usuario = tu.id_tipo_usuario
        JOIN estados_usuarios eu ON u.id_estado_usuario = eu.id_estado_usuario
        ORDER BY u.nombre ASC
    ");
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
} catch (PDOException $e) {
    $mensaje = "Error al obtener los usuarios: " . $e->getMessage();
    $tipo_mensaje = "danger";
    $usuarios = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Usuarios - FixItNow</title>
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
    </style>
</head>
<body>    <header>
        <div class="header-container">
            <div class="logo-container">
                <a href="../main.php">
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
            <h1 class="document-title">Administración de Usuarios</h1>
            
            <?php if (isset($mensaje)): ?>
                <div class="alert alert-<?= $tipo_mensaje ?>">
                    <?= $mensaje ?>
                </div>
            <?php endif; ?>
            
            <div class="admin-section">
                <h2>Listado de Usuarios</h2>
                
                <?php if (empty($usuarios)): ?>
                    <p>No hay usuarios registrados.</p>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td><?= $usuario['id_usuario'] ?></td>
                                    <td>
                                        <?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?>
                                    </td>
                                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                                    <td><?= htmlspecialchars($usuario['telefono']) ?></td>
                                    <td><?= htmlspecialchars($usuario['tipo_usuario']) ?></td>
                                    <td class="estado-<?= strtolower($usuario['estado_usuario']) ?>">
                                        <?= htmlspecialchars($usuario['estado_usuario']) ?>
                                    </td>                                    <td>
                                        <?php if ($usuario['id_usuario'] != $_SESSION['usuario']['id']): ?>
                                            <div style="display: flex; gap: 10px;">                                                <a href="?accion=cambiar_estado&id_usuario=<?= $usuario['id_usuario'] ?>" 
                                                   onclick="return confirm('¿Estás seguro de cambiar el estado de este usuario?');"
                                                   class="btn-editar" style="padding: 5px 10px; background-color: #FF9B00; color: white; text-decoration: none; border-radius: 4px;">
                                                    <?= strtolower($usuario['estado_usuario']) == 'activo' ? 'Desactivar' : 'Activar' ?>
                                                </a>
                                                <a href="?accion=eliminar&id_usuario=<?= $usuario['id_usuario'] ?>" 
                                                   onclick="return confirm('¿Estás seguro de ELIMINAR este usuario? Esta acción no se puede deshacer.');"
                                                   class="btn-eliminar" style="padding: 5px 10px; background-color: #dc3545; color: white; text-decoration: none; border-radius: 4px;">
                                                    Eliminar
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <span>Usuario actual</span>
                                        <?php endif; ?>
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
