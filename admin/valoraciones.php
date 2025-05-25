<?php
session_start();
require_once '../config/database.php';

// Verificar que el usuario es un administrador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] != 1) {
    header('Location: ../login.php');
    exit();
}

// Eliminar una valoración si se solicita
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM valoraciones_usuarios WHERE id_valoracion = ?");
        $stmt->execute([$_GET['eliminar']]);
        
        if ($stmt->rowCount() > 0) {
            $mensaje = "Valoración eliminada correctamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "No se encontró la valoración a eliminar.";
            $tipo_mensaje = "danger";
        }
    } catch (PDOException $e) {
        $mensaje = "Error al eliminar la valoración: " . $e->getMessage();
        $tipo_mensaje = "danger";
    }
}

// Obtener todas las valoraciones (con paginación)
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 10;
$offset = ($pagina - 1) * $por_pagina;

try {
    // Contar total de valoraciones
    $stmt = $pdo->query("SELECT COUNT(*) FROM valoraciones_usuarios");
    $total_valoraciones = $stmt->fetchColumn();
    $total_paginas = ceil($total_valoraciones / $por_pagina);
    
    // Obtener valoraciones para la página actual
    $stmt = $pdo->prepare("
        SELECT v.*, 
               e.nombre AS emisor_nombre, e.apellido AS emisor_apellido,
               r.nombre AS receptor_nombre, r.apellido AS receptor_apellido
        FROM valoraciones_usuarios v
        JOIN usuarios e ON v.id_emisor = e.id_usuario
        JOIN usuarios r ON v.id_receptor = r.id_usuario
        ORDER BY v.fecha_creacion DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bindParam(1, $por_pagina, PDO::PARAM_INT);
    $stmt->bindParam(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $valoraciones = $stmt->fetchAll();
} catch (PDOException $e) {
    $mensaje = "Error al obtener las valoraciones: " . $e->getMessage();
    $tipo_mensaje = "danger";
    $valoraciones = [];
    $total_paginas = 0;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Valoraciones - FixItNow</title>
    <link rel="stylesheet" href="../vistas_usuarios/vistas.css">
    <link rel="stylesheet" href="../includes/responsive-header.css">
    <link rel="stylesheet" href="../includes/footer.css">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container1 {
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
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0.15rem 0;
            font-size: 0.7rem;
            table-layout: fixed;
        }
        .admin-table th, .admin-table td {
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
        .pagination {
            margin: 1rem 0;
            text-align: center;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.25rem;
        }
        .pagination a, .pagination span {
            padding: 4px 8px;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #333;
            font-size: 0.85em;
            border-radius: 4px;
            min-width: 1.5rem;
            text-align: center;
        }
        .pagination a:hover {
            background-color: #f8f9fa;
        }
        .pagination .active {
            background-color: #0d6efd;
            color: white;
            border: 1px solid #0d6efd;
        }
        .alert {
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: none;
            border-radius: 8px;
        }
        .alert-success {
            color: #0f5132;
            background-color: #d1e7dd;
        }
        .alert-danger {
            color: #842029;
            background-color: #f8d7da;
        }
        .btn-eliminar {
            padding: 3px 6px !important;
            border-radius: 3px !important;
            font-weight: 500 !important;
            text-decoration: none !important;
            transition: all 0.2s ease !important;
            border: none !important;
            cursor: pointer !important;
            font-size: 0.75rem !important;
            background-color: #dc3545 !important;
            color: white !important;
            display: inline-block !important;
            text-align: center !important;
            line-height: 1.2 !important;
            min-width: 50px !important;
        }
        .btn-eliminar:hover {
            background-color: #bb2d3b !important;
        }
        .valoracion-estrellas {
            color: #ffc107;
            font-size: 0.9em;
            line-height: 1;
        }
        .mobile-cards {
            display: none;
        }
        .rating-card {
            background: white;
            border: 1px solid #eee;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .rating-card-field {
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }
        .rating-card-field:last-child {
            margin-bottom: 0;
        }
        .rating-card-label {
            font-weight: 600;
            color: #666;
            font-size: 0.75rem;
        }
        .rating-card-value {
            color: #333;
            font-size: 0.85rem;
        }
        .rating-card-value a {
            color: #0066cc;
            text-decoration: none;
        }
        .rating-card-value a:hover {
            text-decoration: underline;
        }
        .rating-card .valoracion-estrellas {
            font-size: 1rem;
        }
        .rating-card-actions {
            margin-top: 0.75rem;
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
            padding-top: 0.5rem;
            border-top: 1px solid #eee;
        }
        .rating-img {
            width: 150px;
            height: 150px;
            border-radius: 4px;
            object-fit: cover;
            display: block;
            margin: 0.5rem 0;
        }
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
            
            .mobile-cards {
                display: block;
            }
            
            .document-title {
                font-size: 1.1rem;
                margin-bottom: 1rem;
            }
            
            .admin-section h2 {
                font-size: 1rem;
                margin: 0.75rem 0;
                color: #444;
            }
            
            .btn-eliminar {
                padding: 4px 8px !important;
                font-size: 0.8rem !important;
                min-width: 70px;
            }
            
            .submit-btn {
                font-size: 0.8rem;
                padding: 0.35rem 0.75rem;
                min-width: 80px;
            }
            
            .rating-card {
                margin: 0.75rem 0;
            }
            
            .rating-card-value {
                font-size: 0.9rem;
            }
        }
        
        .submit-btn[style*="background-color: #6c757d"] {
            font-size: 0.7rem !important;
            padding: 0.25rem 0.5rem !important;
            min-width: 40px !important;
            border-radius: 3px !important;
            opacity: 0.9;
        }

        .submit-btn[style*="background-color: #6c757d"]:hover {
            opacity: 1;
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
            <h1 class="document-title">Administración de Valoraciones</h1>
            
            <?php if (isset($mensaje)): ?>
                <div class="alert alert-<?= $tipo_mensaje ?>">
                    <?= $mensaje ?>
                </div>
            <?php endif; ?>
            
            <div class="admin-section">
                <h2>Listado de Valoraciones</h2>                <?php if (empty($valoraciones)): ?>
                    <p>No hay valoraciones registradas.</p>
                <?php else: ?>
                    <!-- Vista de tabla para escritorio -->
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Emisor</th>
                                <th>Receptor</th>
                                <th>Puntuación</th>
                                <th>Comentario</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($valoraciones as $valoracion): ?>
                                <tr>
                                    <td><?= $valoracion['id_valoracion'] ?></td>
                                    <td>
                                        <a href="../vistas_usuarios/ver_<?= $valoracion['id_emisor'] == $_SESSION['usuario']['id'] ? 'autonomo' : 'cliente' ?>.php?id=<?= $valoracion['id_emisor'] ?>">
                                            <?= htmlspecialchars($valoracion['emisor_nombre'] . ' ' . $valoracion['emisor_apellido']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="../vistas_usuarios/ver_<?= $valoracion['id_receptor'] == $_SESSION['usuario']['id'] ? 'autonomo' : 'cliente' ?>.php?id=<?= $valoracion['id_receptor'] ?>">
                                            <?= htmlspecialchars($valoracion['receptor_nombre'] . ' ' . $valoracion['receptor_apellido']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="valoracion-estrellas"><?= str_repeat('★', (int)$valoracion['puntuacion']) . str_repeat('☆', 5 - (int)$valoracion['puntuacion']) ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($valoracion['comentario'] ?? 'Sin comentario') ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($valoracion['fecha_creacion'])) ?></td>
                                    <td>
                                        <a href="?eliminar=<?= $valoracion['id_valoracion'] ?>" 
                                           onclick="return confirm('¿Estás seguro de eliminar esta valoración?');"
                                           class="btn-eliminar">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Vista de tarjetas para móvil -->
                    <div class="mobile-cards">
                        <?php foreach ($valoraciones as $valoracion): ?>
                            <div class="rating-card">
                                <div class="rating-card-field">
                                    <div class="rating-card-label">De:</div>
                                    <div class="rating-card-value">
                                        <a href="../vistas_usuarios/ver_<?= $valoracion['id_emisor'] == $_SESSION['usuario']['id'] ? 'autonomo' : 'cliente' ?>.php?id=<?= $valoracion['id_emisor'] ?>">
                                            <?= htmlspecialchars($valoracion['emisor_nombre'] . ' ' . $valoracion['emisor_apellido']) ?>
                                        </a>
                                    </div>
                                </div>
                                <div class="rating-card-field">
                                    <div class="rating-card-label">Para:</div>
                                    <div class="rating-card-value">
                                        <a href="../vistas_usuarios/ver_<?= $valoracion['id_receptor'] == $_SESSION['usuario']['id'] ? 'autonomo' : 'cliente' ?>.php?id=<?= $valoracion['id_receptor'] ?>">
                                            <?= htmlspecialchars($valoracion['receptor_nombre'] . ' ' . $valoracion['receptor_apellido']) ?>
                                        </a>
                                    </div>
                                </div>
                                <div class="rating-card-field">
                                    <div class="rating-card-label">Puntuación:</div>
                                    <div class="rating-card-value">
                                        <div class="valoracion-estrellas">
                                            <?= str_repeat('★', (int)$valoracion['puntuacion']) . str_repeat('☆', 5 - (int)$valoracion['puntuacion']) ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="rating-card-field">
                                    <div class="rating-card-label">Fecha:</div>
                                    <div class="rating-card-value">
                                        <?= date('d/m/Y', strtotime($valoracion['fecha_creacion'])) ?>
                                    </div>
                                </div>
                                <div class="rating-card-field">
                                    <div class="rating-card-label">Comentario:</div>
                                    <div class="rating-card-value">
                                        <?= htmlspecialchars($valoracion['comentario'] ?? 'Sin comentario') ?>
                                    </div>
                                </div>
                                <div class="rating-card-actions">
                                    <a href="?eliminar=<?= $valoracion['id_valoracion'] ?>" 
                                       onclick="return confirm('¿Estás seguro de eliminar esta valoración?');"
                                       class="btn-eliminar">Eliminar</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Paginación -->
                    <?php if ($total_paginas > 1): ?>
                        <div class="pagination">
                            <?php if ($pagina > 1): ?>
                                <a href="?pagina=1">&laquo; Primera</a>
                                <a href="?pagina=<?= $pagina - 1 ?>">Anterior</a>
                            <?php endif; ?>
                            
                            <?php
                            $rango = 2;
                            for ($i = max(1, $pagina - $rango); $i <= min($total_paginas, $pagina + $rango); $i++): ?>
                                <?php if ($i == $pagina): ?>
                                    <span class="active"><?= $i ?></span>
                                <?php else: ?>
                                    <a href="?pagina=<?= $i ?>"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($pagina < $total_paginas): ?>
                                <a href="?pagina=<?= $pagina + 1 ?>">Siguiente</a>
                                <a href="?pagina=<?= $total_paginas ?>">Última &raquo;</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?></div>              <div class="admin-actions">                <a href="../vistas_usuarios/perfil_admin.php" class="submit-btn" style="background-color: #6c757d;">Volver</a>
            </div>
        </div>
    </div>
    
    <?php
    // Definir la ruta base para las rutas relativas en el footer
    $base_path = '../';
    include '../includes/footer.php'; 
    ?>
</body>
</html>
