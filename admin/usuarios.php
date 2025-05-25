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
    <link rel="stylesheet" href="../includes/responsive-header.css">
    <link rel="stylesheet" href="../includes/compact-forms.css">
    <link rel="stylesheet" href="../includes/footer.css">
    <link rel="icon" type="image/png" href="../media/logo.png">
    <script src="../services/js/buscador.js" defer></script>
    <style>
        body {
            background-color: #f5f5f5;
        }        .container1 {
            padding: 1rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        .document-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1.5rem;
        }
        .document-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #333;
        }.admin-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0.15rem 0;
            font-size: 0.7rem;
            table-layout: fixed;
        }        .admin-table th, .admin-table td {
            border: 1px solid #eee;
            padding: 0.15rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .admin-table th {
            background-color: #f8f9fa;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.35rem;
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
            font-size: 0.85rem;
        }
        .estado-inactivo {
            color: red;
            font-weight: bold;
            font-size: 0.85rem;
        }
        .alert {
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 4px;
            font-size: 0.9rem;
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
        .btn-editar, .btn-eliminar {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.8rem !important;
        }        .admin-section {
            margin-bottom: 0.75rem;
        }        .admin-actions {
            margin-top: 0.5rem;
            text-align: center;
        }        .submit-btn {
            font-size: 0.6rem;
            padding: 0.1rem 0.2rem;
            text-decoration: none;
            border-radius: 2px;
            display: inline-block;
            min-width: 60px;
            text-align: center;
            color: white;
        }
          .hide-mobile {
            display: table-cell;
        }        .user-card {
            background: white;
            border: 1px solid #eee;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .user-card-field {
            margin-bottom: 0.5rem;
        }
        .user-card-label {
            font-weight: 600;
            color: #666;
            font-size: 0.75rem;
        }
        .user-card-value {
            color: #333;
            font-size: 0.85rem;
        }
        .user-card-actions {
            margin-top: 0.75rem;
            display: flex;
            gap: 0.5rem;
        }
        
        .user-img {
            width: 150px;
            height: 150px;
            border-radius: 4px;
            object-fit: cover;
            display: block;
            margin: 0.5rem 0;
        }
        
        /* Responsive styles */
        @media (max-width: 768px) {
            .container1 {
                padding: 0.25rem;
                max-width: 430px;
            }
            .document-container {
                padding: 0.5rem;
                border-radius: 0;
            }
            .admin-table {
                display: none;
            }
            .user-card {
                display: block;
            }
            .document-title {
                font-size: 1rem;
                margin-bottom: 0.75rem;
            }
            .admin-section h2 {
                font-size: 0.9rem;
                margin: 0.5rem 0;
            }            .btn-editar, .btn-eliminar {
                padding: 0.25rem 0.5rem !important;
                font-size: 0.75rem !important;
                min-width: 40px;
            }            .submit-btn {
                font-size: 0.55rem;
                padding: 0.05rem 0.15rem;
                min-width: 50px;
            }
            
            .user-img {
                width: 120px;
                height: 120px;
                margin: 0.5rem auto;
            }
        }
    </style>
</head>
<body>
<?php
$base_path = '../';
include '../includes/header_template.php';
?>
    
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
                <?php else: ?>                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th class="hide-mobile">Teléfono</th>
                                <th class="hide-mobile">Tipo</th>
                                <th class="hide-mobile">Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?>
                                    </td>                                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                                    <td class="hide-mobile"><?= htmlspecialchars($usuario['telefono']) ?></td>
                                    <td class="hide-mobile"><?= htmlspecialchars($usuario['tipo_usuario']) ?></td>
                                    <td class="hide-mobile estado-<?= strtolower($usuario['estado_usuario']) ?>">
                                        <?= htmlspecialchars($usuario['estado_usuario']) ?>
                                    </td><td>
                                        <?php if ($usuario['id_usuario'] != $_SESSION['usuario']['id']): ?>                                            <div class="admin-action-buttons">
                                                <a href="?accion=cambiar_estado&id_usuario=<?= $usuario['id_usuario'] ?>" 
                                                   onclick="return confirm('¿Estás seguro de cambiar el estado de este usuario?');"
                                                   class="btn-editar">
                                                    <?= strtolower($usuario['estado_usuario']) == 'activo' ? 'Des.' : 'Act.' ?>
                                                </a>
                                                <a href="?accion=eliminar&id_usuario=<?= $usuario['id_usuario'] ?>" 
                                                   onclick="return confirm('¿Estás seguro de ELIMINAR este usuario? Esta acción no se puede deshacer.');"
                                                   class="btn-eliminar">
                                                    Elim.
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <span>Usuario actual</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>                    </table>

                    <?php foreach ($usuarios as $usuario): ?>
                        <div class="user-card">
                            <div class="user-card-field">
                                <div class="user-card-label">Nombre:</div>
                                <div class="user-card-value"><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></div>
                            </div>
                            <div class="user-card-field">
                                <div class="user-card-label">Email:</div>
                                <div class="user-card-value"><?= htmlspecialchars($usuario['email']) ?></div>
                            </div>
                            <div class="user-card-actions">
                                <?php if ($usuario['id_usuario'] != $_SESSION['usuario']['id']): ?>
                                    <a href="?accion=cambiar_estado&id_usuario=<?= $usuario['id_usuario'] ?>" 
                                       onclick="return confirm('¿Estás seguro de cambiar el estado de este usuario?');"
                                       class="btn-editar">
                                        <?= strtolower($usuario['estado_usuario']) == 'activo' ? 'Desactivar' : 'Activar' ?>
                                    </a>
                                    <a href="?accion=eliminar&id_usuario=<?= $usuario['id_usuario'] ?>" 
                                       onclick="return confirm('¿Estás seguro de ELIMINAR este usuario? Esta acción no se puede deshacer.');"
                                       class="btn-eliminar">
                                        Eliminar
                                    </a>
                                <?php else: ?>
                                    <span>Usuario actual</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>              <div class="admin-actions">
                <a href="../vistas_usuarios/perfil_admin.php" class="submit-btn" style="background-color: #6c757d;">Volver</a>
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
