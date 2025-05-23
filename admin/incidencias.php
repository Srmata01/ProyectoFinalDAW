<?php
session_start();
require_once '../config/database.php';

// Verificar si el usuario ha iniciado sesión como administrador
if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['id']) || $_SESSION['usuario']['tipo'] != 1) {
    // Si no es un administrador, redirigir
    header('Location: ../login.php');
    exit();
}

// Variables para mensajes
$mensaje = '';
$tipo_mensaje = '';

// Procesar cambios de estado si se solicita
if (isset($_POST['id_incidencia']) && isset($_POST['accion'])) {
    $id_incidencia = $_POST['id_incidencia'];
    $accion = $_POST['accion'];
    
    if ($accion === 'resolver') {
        // Aquí se implementaría la lógica para marcar como resuelta
        // Por ahora, simplemente eliminaremos la incidencia para simular resolución
        try {
            $stmt = $pdo->prepare("DELETE FROM incidencias WHERE id_incidencia = ?");
            $stmt->execute([$id_incidencia]);
            $mensaje = 'Incidencia marcada como resuelta y eliminada con éxito.';
            $tipo_mensaje = 'success';
        } catch (PDOException $e) {
            $mensaje = 'Error al procesar la incidencia: ' . $e->getMessage();
            $tipo_mensaje = 'error';
        }
    }
}

// Obtener todas las incidencias
try {
    $stmt = $pdo->prepare("
        SELECT *
        FROM incidencias
        ORDER BY id_incidencia DESC
    ");
    $stmt->execute();
    $incidencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensaje = 'Error al cargar las incidencias: ' . $e->getMessage();
    $tipo_mensaje = 'error';
    $incidencias = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Incidencias - FixItNow</title>
    <link rel="stylesheet" href="../vistas_usuarios/vistas.css">    <style>        .admin-table {
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
        
        .imagen-incidencia {
            max-width: 150px;
            max-height: 150px;
            border-radius: 4px;
        }
        
        .incidencia-acciones {
            display: flex;
            gap: 8px;
            justify-content: center;
        }
        
        .incidencia-acciones button {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-resolver {
            background-color: #4CAF50;
            color: white;
        }
        
        .btn-resolver:hover {
            background-color: #45a049;
        }        .descripcion-completa {
            max-width: 350px;
            white-space: pre-line;
            overflow-wrap: break-word;
            text-indent: 0;
            padding: 0;
            margin: 0;
        }
        
        .mensaje-box {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        
        .mensaje-error {
            background-color: #ffdddd;
            color: #ff0000;
        }
        
        .mensaje-success {
            background-color: #ddffdd;
            color: #009900;
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
    </header>    <div class="container1">
        <div class="document-container">
            <h1 class="document-title">Gestión de Incidencias</h1>
        
            <?php if (!empty($mensaje)): ?>
                <div class="mensaje-box mensaje-<?= $tipo_mensaje ?>">
                    <?= htmlspecialchars($mensaje) ?>
                </div>            <?php endif; ?>
            
            <div class="admin-section">                <h2>Listado de Incidencias</h2>
        
                <?php if (empty($incidencias)): ?>
                    <p>No hay incidencias registradas en el sistema.</p>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Persona</th>
                                <th>Email</th>
                                <th>Título</th>
                                <th>Descripción</th>
                                <th>Imagen</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                    <tbody>
                    <?php foreach ($incidencias as $incidencia): ?>
                        <tr>
                            <td><?= $incidencia['id_incidencia'] ?></td>
                            <td><?= htmlspecialchars($incidencia['persona_incidencia']) ?></td>
                            <td><?= htmlspecialchars($incidencia['mail_contacto']) ?></td>
                            <td><?= htmlspecialchars($incidencia['titulo_incidencia']) ?></td>                            <td>
                                <div class="descripcion-completa">
                                    <?= htmlspecialchars(trim($incidencia['cuerpo_incidencia'])) ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($incidencia['imagen_incidencia']): ?>
                                    <img src="data:image/jpeg;base64,<?= base64_encode($incidencia['imagen_incidencia']) ?>" 
                                         alt="Imagen de incidencia" class="imagen-incidencia">
                                <?php else: ?>
                                    <em>Sin imagen</em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="incidencia-acciones">
                                    <form method="post" onsubmit="return confirm('¿Está seguro de marcar esta incidencia como resuelta? Esta acción eliminará la incidencia del sistema.');">
                                        <input type="hidden" name="id_incidencia" value="<?= $incidencia['id_incidencia'] ?>">
                                        <input type="hidden" name="accion" value="resolver">
                                        <button type="submit" class="btn-resolver">Resolver</button>
                                    </form>
                                </div>
                            </td>
                        </tr>                    <?php endforeach; ?>                </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <div class="admin-actions">
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
