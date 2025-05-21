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
        
        .incidencias-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .incidencias-table th, 
        .incidencias-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .incidencias-table th {
            background-color: #f8f8f8;
            font-weight: bold;
        }
        
        .incidencias-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .imagen-incidencia {
            max-width: 100px;
            max-height: 100px;
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
        }
        
        .truncate {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .volver-btn {
            display: block;
            width: 200px;
            margin: 20px auto 0;
            padding: 10px;
            background-color: #6c757d;
            color: white;
            text-align: center;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .volver-btn:hover {
            background-color: #5a6268;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 700px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: black;
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
            <h1 class="document-title">Gestión de Incidencias</h1>
        
            <?php if (!empty($mensaje)): ?>
                <div class="mensaje-box mensaje-<?= $tipo_mensaje ?>">
                    <?= htmlspecialchars($mensaje) ?>
                </div>            <?php endif; ?>
        
            <?php if (empty($incidencias)): ?>
                <p>No hay incidencias registradas en el sistema.</p>
            <?php else: ?>
                <table class="incidencias-table">
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
                            <td class="truncate">
                                <?= htmlspecialchars(substr($incidencia['cuerpo_incidencia'], 0, 100) . (strlen($incidencia['cuerpo_incidencia']) > 100 ? '...' : '')) ?>
                                <button class="ver-mas" onclick="verDetalle(<?= $incidencia['id_incidencia'] ?>, '<?= htmlspecialchars(addslashes($incidencia['titulo_incidencia'])) ?>', '<?= htmlspecialchars(addslashes($incidencia['cuerpo_incidencia'])) ?>')">Ver más</button>
                            </td>
                            <td>
                                <?php if ($incidencia['imagen_incidencia']): ?>
                                    <img src="data:image/jpeg;base64,<?= base64_encode($incidencia['imagen_incidencia']) ?>" 
                                         alt="Imagen de incidencia" class="imagen-incidencia"
                                         onclick="abrirImagen('data:image/jpeg;base64,<?= base64_encode($incidencia['imagen_incidencia']) ?>')">
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
        <?php endif; ?>
        
        <a href="../vistas_usuarios/perfil_admin.php" class="volver-btn">Volver al Panel de Administración</a>
    </div>
    
    <!-- Modal para ver detalle completo -->
    <div id="detalleModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h2 id="modalTitulo"></h2>
            <p id="modalCuerpo" style="white-space: pre-wrap;"></p>
        </div>
    </div>
    
    <!-- Modal para ver imagen ampliada -->
    <div id="imagenModal" class="modal">
        <div class="modal-content" style="text-align: center;">
            <span class="close" onclick="cerrarImagenModal()">&times;</span>
            <img id="imagenAmpliada" style="max-width: 100%; max-height: 80vh;">
        </div>
    </div>
    
    <script>
        function verDetalle(id, titulo, cuerpo) {
            document.getElementById('modalTitulo').textContent = titulo;
            document.getElementById('modalCuerpo').textContent = cuerpo;
            document.getElementById('detalleModal').style.display = 'block';
        }
        
        function cerrarModal() {
            document.getElementById('detalleModal').style.display = 'none';
        }
        
        function abrirImagen(src) {
            document.getElementById('imagenAmpliada').src = src;
            document.getElementById('imagenModal').style.display = 'block';
        }
        
        function cerrarImagenModal() {
            document.getElementById('imagenModal').style.display = 'none';
        }
        
        // Cerrar modales si se hace clic fuera de ellos
        window.onclick = function(event) {
            if (event.target == document.getElementById('detalleModal')) {
                cerrarModal();
            }
            if (event.target == document.getElementById('imagenModal')) {
                cerrarImagenModal();
            }
        }
    </script>
    
    <?php
    // Definir ruta base para el footer
    $base_path = '../';
    include '../includes/footer.php';
    ?>
</body>
</html>
