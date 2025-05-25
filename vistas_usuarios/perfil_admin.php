<?php
session_start();
require_once '../config/database.php';

$base_path = '../'; // Definimos base_path ya que estamos en un subdirectorio
require_once $base_path . 'includes/header_template.php';

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
               e.nombre as emisor_nombre, e.apellido as emisor_apellido,
               r.nombre as receptor_nombre, r.apellido as receptor_apellido
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador - FixItNow</title>
    <link rel="stylesheet" href="../main.css">
    <link rel="stylesheet" href="../includes/responsive-header.css">
    <link rel="stylesheet" href="../includes/footer.css">
    <link rel="stylesheet" href="vistas.css">
    <link rel="icon" type="image/png" href="../media/logo.png">
    <style>
        .container1 {
            margin-top: 100px !important;
        }
    </style>
</head>
<body class="app">
    <div class="app-main">
        <div class="container1">
            <?php if (isset($_SESSION['mensaje'])): ?>
                <div class="alert alert-<?php echo $_SESSION['mensaje_tipo']; ?>">
                    <?php 
                    echo $_SESSION['mensaje'];
                    unset($_SESSION['mensaje']);
                    unset($_SESSION['mensaje_tipo']);
                    ?>
                </div>
            <?php endif; ?>

            <h1 class="document-title">Panel de Administración</h1>
            
            <div class="admin-dashboard">
                <div class="admin-card">
                    <h2>Últimos Usuarios</h2>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                            </tr>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['tipo_usuario']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['estado_usuario']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php for ($i = count($usuarios); $i < 5; $i++): ?>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                            <?php endfor; ?>
                        </table>
                    </div>
                    <a href="../admin/usuarios.php" class="view-more-btn">Ver todos</a>
                </div>

                <div class="admin-card">
                    <h2>Últimos Servicios</h2>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <tr>
                                <th>Servicio</th>
                                <th>Autónomo</th>
                                <th>Precio</th>
                            </tr>
                            <?php foreach ($servicios as $servicio): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($servicio['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($servicio['autonomo_nombre'] . ' ' . $servicio['autonomo_apellido']); ?></td>
                                    <td><?php echo htmlspecialchars($servicio['precio']); ?>€</td>
                                </tr>
                            <?php endforeach; ?>
                            <?php for ($i = count($servicios); $i < 5; $i++): ?>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                            <?php endfor; ?>
                        </table>
                    </div>
                    <a href="../admin/servicios.php" class="view-more-btn">Ver todos</a>
                </div>

                <div class="admin-card">
                    <h2>Últimas Valoraciones</h2>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <tr>
                                <th>De</th>
                                <th>Para</th>
                                <th>Puntuación</th>
                            </tr>
                            <?php foreach ($valoraciones as $valoracion): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($valoracion['emisor_nombre'] . ' ' . $valoracion['emisor_apellido']); ?></td>
                                    <td><?php echo htmlspecialchars($valoracion['receptor_nombre'] . ' ' . $valoracion['receptor_apellido']); ?></td>
                                    <td>
                                        <div class="valoracion-estrellas">
                                            <?= str_repeat('★', (int)$valoracion['puntuacion']) . str_repeat('☆', 5 - (int)$valoracion['puntuacion']) ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php for ($i = count($valoraciones); $i < 5; $i++): ?>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                            <?php endfor; ?>
                        </table>
                    </div>
                    <a href="../admin/valoraciones.php" class="view-more-btn">Ver todas</a>
                </div>

                <div class="admin-card">
                    <h2>Últimas Incidencias</h2>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <tr>
                                <th>Persona</th>
                                <th>Email</th>
                                <th>Título</th>
                            </tr>
                            <?php foreach ($incidencias as $incidencia): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($incidencia['persona_incidencia']); ?></td>
                                    <td><?php echo htmlspecialchars($incidencia['mail_contacto']); ?></td>
                                    <td><?php echo htmlspecialchars($incidencia['titulo_incidencia']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php for ($i = count($incidencias); $i < 5; $i++): ?>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                            <?php endfor; ?>
                        </table>
                    </div>
                    <a href="../admin/incidencias.php" class="view-more-btn">Ver todas</a>
                </div>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
