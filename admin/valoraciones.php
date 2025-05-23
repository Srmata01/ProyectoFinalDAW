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
        .pagination {
            margin: 20px 0;
            text-align: center;
        }
        .pagination a, .pagination span {
            padding: 8px 16px;
            margin: 0 4px;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #333;
        }
        .pagination a:hover {
            background-color: #ddd;
        }
        .pagination .active {
            background-color: #4CAF50;
            color: white;
            border: 1px solid #4CAF50;
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
            <h1 class="document-title">Administración de Valoraciones</h1>
            
            <?php if (isset($mensaje)): ?>
                <div class="alert alert-<?= $tipo_mensaje ?>">
                    <?= $mensaje ?>
                </div>
            <?php endif; ?>
            
            <div class="admin-section">
                <h2>Listado de Valoraciones</h2>
                
                <?php if (empty($valoraciones)): ?>
                    <p>No hay valoraciones registradas.</p>
                <?php else: ?>
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
                <?php endif; ?>            </div>              <div class="admin-actions">
                <a href="../vistas_usuarios/perfil_admin.php" class="submit-btn" style="background-color: #6c757d;">Volver al Panel</a>
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
