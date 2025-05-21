<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] != 1) {
    header('Location: ../login.php');
    exit();
}

try {
    // Obtener usuarios (limitado a 5)
    $stmt = $pdo->prepare("
        SELECT u.*, tu.tipo as tipo_usuario, eu.estado as estado_usuario
        FROM usuarios u
        JOIN tipos_usuarios tu ON u.id_tipo_usuario = tu.id_tipo_usuario
        JOIN estados_usuarios eu ON u.id_estado_usuario = eu.id_estado_usuario
        LIMIT 5
    ");
    $stmt->execute();
    $usuarios = $stmt->fetchAll();

    // Obtener servicios (limitado a 5)
    $stmt = $pdo->prepare("
        SELECT s.*, u.nombre as autonomo_nombre, u.apellido as autonomo_apellido
        FROM servicios s
        JOIN usuarios u ON s.id_autonomo = u.id_usuario
        ORDER BY s.id_servicio DESC
        LIMIT 5
    ");
    $stmt->execute();
    $servicios = $stmt->fetchAll();

    // Obtener valoraciones (limitado a 5)
    $stmt = $pdo->prepare("
        SELECT v.*, 
               e.nombre AS emisor_nombre, e.apellido AS emisor_apellido,
               r.nombre AS receptor_nombre, r.apellido AS receptor_apellido
        FROM valoraciones_usuarios v
        JOIN usuarios e ON v.id_emisor = e.id_usuario
        JOIN usuarios r ON v.id_receptor = r.id_usuario
        ORDER BY v.fecha_creacion DESC
        LIMIT 5
    ");
    $stmt->execute();
    $valoraciones = $stmt->fetchAll();

    // Obtener incidencias (limitado a 5)
    $stmt = $pdo->prepare("
        SELECT i.*
        FROM incidencias i
        ORDER BY i.id_incidencia DESC
        LIMIT 5
    ");
    $stmt->execute();
    $incidencias = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Error al obtener datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Administrador</title>
    <link rel="stylesheet" href="vistas.css">    <style>
        .admin-dashboard {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: space-between;
            padding: 20px 0;
        }
        
        .admin-card {
            flex: 1 1 calc(50% - 20px);
            min-width: 300px;
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
          .admin-card h2 {
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #FF9B00;
            color: #333;
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .admin-table th, .admin-table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .admin-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        
        .admin-table tr:hover {
            background-color: #f1f1f1;
        }
        
        .view-more-btn {
            display: block;
            text-align: center;
            background-color: #FF9B00;
            color: white;
            padding: 10px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }
          .view-more-btn:hover {
            background-color: #e38a00;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo-container">
                <a href="../main.php">
                    <img src="../media/logo.png" alt="Logo" class="logo">
                </a>
            </div>
            <div class="user-container">
                <div class="profile-container">
                    <?php include '../includes/profile_header.php'; ?>
                    <a href="../includes/logout.php" class="submit-btn" style="margin-left: 10px;">Cerrar sesión</a>
                </div>
            </div>
        </div>
    </header>    <div class="container1">
        <h1 class="document-title">Panel de Administración</h1>
        
        <div class="admin-dashboard">            <!-- Card de Usuarios -->
            <div class="admin-card">
                <h2>Usuarios</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($usuarios) > 0): ?>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></td>
                                    <td><?= htmlspecialchars($usuario['tipo_usuario']) ?></td>
                                    <td><?= htmlspecialchars($usuario['estado_usuario']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">No hay usuarios registrados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <a href="../admin/usuarios.php" class="view-more-btn">Gestionar Usuarios</a>
            </div>
              <!-- Card de Servicios -->
            <div class="admin-card">
                <h2>Servicios</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Autónomo</th>
                            <th>Precio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($servicios) > 0): ?>
                            <?php foreach ($servicios as $servicio): ?>
                                <tr>
                                    <td><?= htmlspecialchars($servicio['nombre']) ?></td>
                                    <td><?= htmlspecialchars($servicio['autonomo_nombre'] . ' ' . $servicio['autonomo_apellido']) ?></td>
                                    <td><?= htmlspecialchars($servicio['precio']) ?> €</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">No hay servicios registrados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <a href="../admin/servicios.php" class="view-more-btn">Gestionar Servicios</a>
            </div>
              <!-- Card de Valoraciones -->
            <div class="admin-card">
                <h2>Valoraciones</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Emisor</th>
                            <th>Receptor</th>
                            <th>Puntuación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($valoraciones) > 0): ?>
                            <?php foreach ($valoraciones as $valoracion): ?>
                                <tr>
                                    <td><?= htmlspecialchars($valoracion['emisor_nombre'] . ' ' . $valoracion['emisor_apellido']) ?></td>
                                    <td><?= htmlspecialchars($valoracion['receptor_nombre'] . ' ' . $valoracion['receptor_apellido']) ?></td>
                                    <td><?= str_repeat('★', (int)$valoracion['puntuacion']) . str_repeat('☆', 5 - (int)$valoracion['puntuacion']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">No hay valoraciones registradas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <a href="../admin/valoraciones.php" class="view-more-btn">Gestionar Valoraciones</a>
            </div>
              <!-- Card de Incidencias -->
            <div class="admin-card">
                <h2>Incidencias</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Persona</th>
                            <th>Título</th>
                            <th>ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($incidencias) > 0): ?>
                            <?php foreach ($incidencias as $incidencia): ?>
                                <tr>
                                    <td><?= htmlspecialchars($incidencia['persona_incidencia']) ?></td>
                                    <td><?= htmlspecialchars($incidencia['titulo_incidencia']) ?></td>
                                    <td><?= htmlspecialchars($incidencia['id_incidencia']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">No hay incidencias registradas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <a href="../admin/incidencias.php" class="view-more-btn">Gestionar Incidencias</a>
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
