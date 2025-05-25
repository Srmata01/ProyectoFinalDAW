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
    <link rel="stylesheet" href="../includes/responsive-header.css">
    <link rel="stylesheet" href="../includes/footer.css">
    <link rel="stylesheet" href="../vistas_usuarios/vistas.css">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }        .admin-table th, .admin-table td {
            padding: 2px 4px;
            border: none;
            border-bottom: 1px solid #eee;
            font-size: 0.8em;
            vertical-align: middle;
            line-height: 1.1;
        }
        .admin-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.7em;
            color: #666;
            white-space: nowrap;
            height: 24px;
        }
        .admin-table tr:last-child td {
            border-bottom: none;
        }
        .admin-table tr:hover {
            background-color: #f8f9fa;
        }
        .estado-activo {
            color: #198754;
            font-weight: 600;
            background-color: #d1e7dd;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.8em;
            display: inline-block;
        }
        .estado-inactivo {
            color: #842029;
            font-weight: 600;
            background-color: #f8d7da;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.8em;
            display: inline-block;
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border: none;
            border-radius: 8px;
            display: flex;
            align-items: center;
        }
        .alert-success {
            color: #0f5132;
            background-color: #d1e7dd;
        }
        .alert-danger {
            color: #842029;
            background-color: #f8d7da;
        }        .descripcion-cell {
            max-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            position: relative;
        }
        .descripcion-cell:hover {
            white-space: normal;
            overflow: visible;
            background-color: #fff;
            position: absolute;
            z-index: 1;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 4px;
            padding: 4px;
            margin: -4px;
        }
        .admin-actions {
            margin-top: 2rem;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }        .btn-editar, .btn-eliminar {
            padding: 3px 6px !important;
            border-radius: 3px !important;
            font-weight: 500 !important;
            text-decoration: none !important;
            transition: all 0.2s ease !important;
            border: none !important;
            cursor: pointer !important;
            font-size: 0.75rem !important;
            white-space: nowrap !important;
            display: inline-block !important;
            text-align: center !important;
            line-height: 1.2 !important;
        }
        .btn-editar {
            background-color: #FF9B00 !important;
            color: white !important;
        }
        .btn-editar:hover {
            background-color: #e68a00 !important;
        }
        .btn-eliminar {
            background-color: #dc3545 !important;
            color: white !important;
        }
        .btn-eliminar:hover {
            background-color: #bb2d3b !important;
        }
        .service-card {
            display: none;
            background: white;
            border: 1px solid #eee;
            padding: 0.5rem;
            margin-bottom: 0.5rem;
            border-radius: 4px;
        }
        .service-card-field {
            margin-bottom: 0.25rem;
        }
        .service-card-label {
            font-weight: 600;
            color: #666;
            font-size: 0.75rem;
        }
        .service-card-value {
            color: #333;
            font-size: 0.8rem;
        }
        .service-card-actions {
            margin-top: 0.5rem;
            display: flex;
            gap: 0.25rem;
        }
        .submit-btn {
            font-size: 0.6rem;
            padding: 0.1rem 0.2rem;
            text-decoration: none;
            border-radius: 2px;
            display: inline-block;
            min-width: 60px;
            text-align: center;
            color: white;
        }
        @media (max-width: 992px) {
            .admin-table {
                font-size: 0.85em;
            }
            .btn-editar, .btn-eliminar {
                padding: 3px 6px !important;
                font-size: 0.75rem !important;
                min-width: 50px !important;
            }
        }        @media (max-width: 768px) {
            .container1 {
                padding: 0.25rem;
            }
            .document-container {
                padding: 0.5rem;
                border-radius: 0;
            }
            .admin-table {
                display: none;
            }
            .service-card {
                display: block;
            }
            .document-title {
                font-size: 1rem;
                margin-bottom: 0.75rem;
            }
            .admin-section h2 {
                font-size: 0.9rem;
                margin: 0.5rem 0;
            }
            .btn-editar, .btn-eliminar {
                padding: 2px 4px !important;
                font-size: 0.7rem !important;
                min-width: 40px;
            }
            .submit-btn {
                font-size: 0.55rem;
                padding: 0.05rem 0.15rem;
                min-width: 50px;
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
                <?php else: ?>                    <table class="admin-table">
                        <thead>
                            <tr>
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
                            <?php foreach ($servicios as $servicio): ?>                                <tr>
                                    <td data-label="Nombre"><?= htmlspecialchars($servicio['nombre']) ?></td>
                                    <td data-label="Autónomo"><?= htmlspecialchars($servicio['autonomo_nombre'] . ' ' . $servicio['autonomo_apellido']) ?></td>
                                    <td class="descripcion-cell" data-label="Descripción"><?= htmlspecialchars($servicio['descripcion']) ?></td>
                                    <td data-label="Precio"><?= htmlspecialchars($servicio['precio']) ?> €</td>
                                    <td data-label="Localidad"><?= htmlspecialchars($servicio['localidad']) ?></td>
                                    <td data-label="Estado">
                                        <span class="estado-<?= strtolower($servicio['estado']) ?>">
                                            <?= htmlspecialchars(ucfirst($servicio['estado'])) ?>
                                        </span>
                                    </td>
                                    <td data-label="Estado Usuario">
                                        <span class="estado-<?= strtolower($servicio['estado_usuario']) ?>">
                                            <?= htmlspecialchars($servicio['estado_usuario']) ?>
                                        </span>
                                    </td>                                    <td style="white-space: nowrap;">
                                        <div style="display: flex; gap: 2px;">
                                            <a href="?accion=cambiar_estado&id_servicio=<?= $servicio['id_servicio'] ?>" 
                                               onclick="return confirm('¿Estás seguro de cambiar el estado de este servicio?');"
                                               class="btn-editar" style="width: auto; min-width: 60px;">
                                                <?= $servicio['estado'] == 'activo' ? 'Desactivar' : 'Activar' ?>
                                            </a>
                                            <a href="?accion=eliminar&id_servicio=<?= $servicio['id_servicio'] ?>" 
                                               onclick="return confirm('¿Estás seguro de ELIMINAR este servicio? Esta acción no se puede deshacer.');"
                                               class="btn-eliminar" style="width: auto; min-width: 50px;">
                                                Eliminar
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>                    </table>

                    <?php foreach ($servicios as $servicio): ?>
                        <div class="service-card">
                            <div class="service-card-field">
                                <div class="service-card-label">Nombre:</div>
                                <div class="service-card-value"><?= htmlspecialchars($servicio['nombre']) ?></div>
                            </div>
                            <div class="service-card-field">
                                <div class="service-card-label">Autónomo:</div>
                                <div class="service-card-value"><?= htmlspecialchars($servicio['autonomo_nombre'] . ' ' . $servicio['autonomo_apellido']) ?></div>
                            </div>
                            <div class="service-card-field">
                                <div class="service-card-label">Precio:</div>
                                <div class="service-card-value"><?= htmlspecialchars($servicio['precio']) ?> €</div>
                            </div>
                            <div class="service-card-actions">
                                <a href="?accion=cambiar_estado&id_servicio=<?= $servicio['id_servicio'] ?>" 
                                   onclick="return confirm('¿Estás seguro de cambiar el estado de este servicio?');"
                                   class="btn-editar" style="background-color: #FF9B00; color: white; text-decoration: none; border-radius: 3px;">
                                    <?= $servicio['estado'] == 'activo' ? 'Des.' : 'Act.' ?>
                                </a>
                                <a href="?accion=eliminar&id_servicio=<?= $servicio['id_servicio'] ?>" 
                                   onclick="return confirm('¿Estás seguro de ELIMINAR este servicio? Esta acción no se puede deshacer.');"
                                   class="btn-eliminar" style="background-color: #dc3545; color: white; text-decoration: none; border-radius: 3px;">
                                    Elim.
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="admin-actions" style="text-align: center;">
                <a href="../vistas_usuarios/perfil_admin.php" class="submit-btn" style="background-color: #6c757d;">Volver al Panel</a>
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
