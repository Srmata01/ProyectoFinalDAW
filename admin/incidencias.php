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
<head>    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Incidencias - FixItNow</title>
    <link rel="stylesheet" href="../vistas_usuarios/vistas.css">
    <link rel="stylesheet" href="../includes/responsive-header.css">
    <link rel="stylesheet" href="../includes/compact-forms.css">
    <link rel="stylesheet" href="../includes/footer.css">
    <link rel="icon" type="image/png" href="../media/logo.png">
    <script src="../services/js/buscador.js" defer></script>    <style>
        /* Estilos generales */
        body {
            background-color: #f5f5f5;
        }

        /* Vista móvil por defecto oculta */
        .mobile-cards {
            display: none;
        }
        
        /* Contenedores principales */
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
        
        /* Tabla de escritorio */
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
        
        /* Estilos de imagen */
        .imagen-incidencia {
            width: 150px;
            height: 150px;
            border-radius: 4px;
            object-fit: cover;
            display: block;
            margin: 0.5rem auto;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        /* Tarjetas móviles */
        .incident-card {
            background: white;
            border: 1px solid #eee;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .incident-card-field {
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }
        
        .incident-card-field:last-child {
            margin-bottom: 0;
        }
        
        .incident-card-label {
            font-weight: 600;
            color: #666;
            font-size: 0.75rem;
            margin-bottom: 0.2rem;
        }
        
        .incident-card-value {
            color: #333;
            font-size: 0.85rem;
        }
        
        .incident-card-actions {
            margin-top: 0.75rem;
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
            padding-top: 0.5rem;
            border-top: 1px solid #eee;
        }
        
        /* Botón resolver con estilo verde */
        .btn-resolver {
            background-color: #2ecc71;
            color: white;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            min-width: 60px;
            line-height: 1.2;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .btn-resolver:hover {
            background-color: #27ae60;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
            
            /* Ocultar tabla en móvil */
            .admin-table {
                display: none;
            }
            
            /* Mostrar cards en móvil */
            .mobile-cards {
                display: block;
            }
            
            /* Ajustes de tamaño para botones en móvil */
            .btn-resolver {
                padding: 6px 12px;
                font-size: 0.8rem;
                min-width: 80px;
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
?><div class="container1">
        <div class="document-container">
            <h1 class="document-title">Gestión de Incidencias</h1>
        
            <?php if (!empty($mensaje)): ?>
                <div class="mensaje-box mensaje-<?= $tipo_mensaje ?>">
                    <?= htmlspecialchars($mensaje) ?>
                </div>            <?php endif; ?>
            
            <div class="admin-section">                <h2>Listado de Incidencias</h2>                <?php if (empty($incidencias)): ?>
                    <p>No hay incidencias registradas en el sistema.</p>
                <?php else: ?>
                    <!-- Vista de tabla para escritorio -->
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
                                    <td><?= htmlspecialchars($incidencia['titulo_incidencia']) ?></td>
                                    <td>
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
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Vista de tarjetas para móvil -->
                    <div class="mobile-cards">
                        <?php foreach ($incidencias as $incidencia): ?>
                            <div class="incident-card">
                                <div class="incident-card-field">
                                    <div class="incident-card-label">Persona:</div>
                                    <div class="incident-card-value"><?= htmlspecialchars($incidencia['persona_incidencia']) ?></div>
                                </div>
                                <div class="incident-card-field">
                                    <div class="incident-card-label">Email:</div>
                                    <div class="incident-card-value"><?= htmlspecialchars($incidencia['mail_contacto']) ?></div>
                                </div>
                                <div class="incident-card-field">
                                    <div class="incident-card-label">Título:</div>
                                    <div class="incident-card-value"><?= htmlspecialchars($incidencia['titulo_incidencia']) ?></div>
                                </div>
                                <div class="incident-card-field">
                                    <div class="incident-card-label">Descripción:</div>
                                    <div class="incident-card-value"><?= htmlspecialchars(trim($incidencia['cuerpo_incidencia'])) ?></div>
                                </div>
                                <?php if ($incidencia['imagen_incidencia']): ?>
                                    <div class="incident-card-field">
                                        <div class="incident-card-label">Imagen:</div>
                                        <img src="data:image/jpeg;base64,<?= base64_encode($incidencia['imagen_incidencia']) ?>" 
                                             alt="Imagen de incidencia" class="imagen-incidencia">
                                    </div>
                                <?php endif; ?>
                                <div class="incident-card-actions">
                                    <form method="post" onsubmit="return confirm('¿Está seguro de marcar esta incidencia como resuelta? Esta acción eliminará la incidencia del sistema.');">
                                        <input type="hidden" name="id_incidencia" value="<?= $incidencia['id_incidencia'] ?>">
                                        <input type="hidden" name="accion" value="resolver">
                                        <button type="submit" class="btn-resolver">Resolver</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
              <div class="admin-actions">
                <a href="../vistas_usuarios/perfil_admin.php" class="submit-btn" style="background-color: #6c757d;">Volver</a>
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
