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
    <link rel="stylesheet" href="../main.css">
    <style>
        .admin-incidencias-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .admin-incidencias-title {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
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
            background-color: #f2f2f2;
            font-weight: bold;
            color: #444;
        }
        
        .incidencias-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .incidencia-cuerpo {
            max-height: 100px;
            overflow-y: auto;
            white-space: pre-wrap;
        }
        
        .accion-btn {
            padding: 8px 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-right: 5px;
        }
        
        .accion-btn:hover {
            background-color: #45a049;
        }
        
        .incidencia-imagen-container {
            max-width: 100px;
            max-height: 100px;
            overflow: hidden;
        }
        
        .incidencia-imagen {
            width: 100%;
            height: auto;
            cursor: pointer;
        }
        
        /* Modal para imagen ampliada */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            max-width: 90%;
            max-height: 90%;
        }
        
        .close {
            position: absolute;
            top: 15px;
            right: 25px;
            color: white;
            font-size: 35px;
            cursor: pointer;
        }
        
        .no-incidencias {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo-container">
                <a href="../main.php">
                    <img src="../media/logo.png" alt="Logo FixItNow" class="logo">
                </a>
            </div>
            <div class="login-profile-box">
                <?php include '../includes/profile_header.php'; ?>
            </div>
        </div>
    </header>

    <div class="admin-incidencias-container">
        <h1 class="admin-incidencias-title">Gestión de Incidencias</h1>
        
        <?php if (!empty($mensaje)): ?>
            <div class="mensaje-box mensaje-<?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($incidencias)): ?>
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
                        <td><?php echo htmlspecialchars($incidencia['id_incidencia']); ?></td>
                        <td><?php echo htmlspecialchars($incidencia['persona_incidencia']); ?></td>
                        <td><?php echo htmlspecialchars($incidencia['mail_contacto']); ?></td>
                        <td><?php echo htmlspecialchars($incidencia['titulo_incidencia']); ?></td>
                        <td>
                            <div class="incidencia-cuerpo">
                                <?php echo nl2br(htmlspecialchars($incidencia['cuerpo_incidencia'])); ?>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($incidencia['imagen_incidencia'])): ?>
                            <div class="incidencia-imagen-container">
                                <img 
                                    src="data:image/jpeg;base64,<?php echo base64_encode($incidencia['imagen_incidencia']); ?>" 
                                    alt="Imagen de la incidencia" 
                                    class="incidencia-imagen" 
                                    onclick="mostrarImagen(this.src)"
                                >
                            </div>
                            <?php else: ?>
                                <span>Sin imagen</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="post" onsubmit="return confirm('¿Estás seguro de querer resolver esta incidencia? Esta acción la eliminará de la lista.');">
                                <input type="hidden" name="id_incidencia" value="<?php echo $incidencia['id_incidencia']; ?>">
                                <input type="hidden" name="accion" value="resolver">
                                <button type="submit" class="accion-btn">Resolver</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-incidencias">No hay incidencias pendientes.</p>
        <?php endif; ?>    </div>
    
    <!-- Modal para mostrar imagen ampliada -->
    <div id="imagenModal" class="modal" onclick="this.style.display='none'">
        <span class="close">&times;</span>
        <img class="modal-content" id="imagenAmpliada">
    </div>

    <?php 
    // Definir la ruta base para el footer
    $base_path = '../';
    include '../includes/footer.php'; 
    ?>
                <h4>Información Personal</h4>
                <ul>
                    <li><a href="../politicaprivacidad.php">Política de privacidad</a></li>
                    <li><a href="../politicacookiesdatos.php">Política de Cookies y protección de datos</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Contacto</h4>
                <ul>
                    <li><a href="mailto:fixitnow@gmail.com">fixitnow@gmail.com</a></li>
                    <li><a href="tel:+34690096690">+34 690 096 690</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>¿Eres miembro?</h4>
                <ul>
                    <li><a href="../create_users/index.php">Únete a Nosotros</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>¿Tienes algún problema?</h4>
                <ul>
                    <li><a href="../incidencias/crear.php">Reportar incidencia</a></li>
                </ul>
            </div>
            
            <div class="footer-section social-media">
                <div class="social-icons">
                    <a href="#"><img src="../media/twitter-icon.png" alt="Twitter"></a>
                    <a href="#"><img src="../media/instagram-icon.png" alt="Instagram"></a>
                    <a href="#"><img src="../media/facebook-icon.png" alt="Facebook"></a>
                    <a href="#"><img src="../media/tiktok-icon.png" alt="TikTok"></a>
                </div>
            </div>
            
            <div class="footer-logo">
                <img src="../media/logo.png" alt="FixItNow Logo">
            </div>
        </div>
    </footer>

    <script>
        // Función para mostrar imagen ampliada
        function mostrarImagen(src) {
            const modal = document.getElementById('imagenModal');
            const imagenAmpliada = document.getElementById('imagenAmpliada');
            
            modal.style.display = 'flex';
            imagenAmpliada.src = src;
        }
        
        // Cerrar modal al hacer clic en la X
        document.querySelector('.close').addEventListener('click', function(e) {
            e.stopPropagation();
            document.getElementById('imagenModal').style.display = 'none';
        });
    </script>
</body>
</html>
