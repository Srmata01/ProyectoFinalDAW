<?php
/**
 * Componente para mostrar valoraciones de usuarios
 */
// Asegurarnos de que los caracteres especiales se muestren correctamente
header('Content-Type: text/html; charset=utf-8');

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir la conexión a la base de datos si no está incluida
if (!function_exists('conectarBaseDatos') && !isset($pdo)) {
    require_once __DIR__ . '/../config/database.php';
}

/**
 * Muestra las valoraciones recibidas por un usuario
 * 
 * @param int $id_usuario ID del usuario del que se quieren mostrar las valoraciones
 */
function mostrarValoraciones($id_usuario) {
    global $pdo;
    
    // Añadir log para debugging
    error_log("mostrarValoraciones() llamada con ID: " . $id_usuario);
    
    if (!is_numeric($id_usuario) || $id_usuario <= 0) {
        echo '<p class="error">ID de usuario no válido</p>';
        return;
    }
    
    // Verificar que $pdo está disponible
    if (!isset($pdo)) {
        echo '<p class="error">Error: Conexión a base de datos no disponible</p>';
        return;
    }

    try {
        // Obtener todas las valoraciones recibidas por el usuario
        $stmt = $pdo->prepare("
            SELECT v.*, u.nombre, u.apellido, u.foto_perfil 
            FROM valoraciones_usuarios v
            JOIN usuarios u ON v.id_emisor = u.id_usuario
            WHERE v.id_receptor = ?
            ORDER BY v.fecha_creacion DESC
        ");
        $stmt->execute([$id_usuario]);
        $valoraciones = $stmt->fetchAll();
        
        // Calcular la media de puntuación
        $stmt = $pdo->prepare("
            SELECT AVG(puntuacion) as media, COUNT(*) as total
            FROM valoraciones_usuarios 
            WHERE id_receptor = ?
        ");
        $stmt->execute([$id_usuario]);
        $stats = $stmt->fetch();
        
        $media = round(floatval($stats['media'] ?? 0), 1);
        $total = intval($stats['total'] ?? 0);
        
        // Verificar si el usuario actual ha valorado a este usuario
        $ha_valorado = false;
        if (isset($_SESSION['usuario']) && isset($_SESSION['usuario']['id'])) {
            $id_emisor = $_SESSION['usuario']['id'];
            if ($id_emisor != $id_usuario) {  // No se puede valorar a uno mismo
                $stmt = $pdo->prepare("
                    SELECT id_valoracion FROM valoraciones_usuarios 
                    WHERE id_emisor = ? AND id_receptor = ?
                ");
                $stmt->execute([$id_emisor, $id_usuario]);
                $ha_valorado = $stmt->fetch() ? true : false;
            }
        }
        
        // Determinar tipo de usuario para los enlaces
        $tipo_usuario = '';
        $stmt = $pdo->prepare("SELECT tu.tipo FROM usuarios u JOIN tipos_usuarios tu ON u.id_tipo_usuario = tu.id_tipo_usuario WHERE u.id_usuario = ?");
        $stmt->execute([$id_usuario]);
        $res = $stmt->fetch();
        if ($res) {
            $tipo_usuario = strtolower($res['tipo']) === 'autónomo' || strtolower($res['tipo']) === 'autonomo' ? 'autonomo' : 'cliente';
        }
        ?>
        
        <div class="valoraciones-section">
            <div class="valoraciones-header">
                <h2>Valoraciones</h2>
                
                <?php if (isset($_SESSION['usuario']) && $_SESSION['usuario']['id'] != $id_usuario && !$ha_valorado) : ?>
                    <div class="valoracion-actions">
                        <a href="../valoraciones/crear.php?id_usuario=<?= $id_usuario ?>" class="btn-valoracion">
                            Añadir valoración
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="valoracion-stats">
                <div class="media-puntuacion">
                    <div class="stars">
                        <?php for ($i = 1; $i <= 5; $i++) : ?>
                            <?php if ($i <= floor($media)) : ?>
                                <span class="star">★</span>
                            <?php elseif ($i - 0.5 <= $media) : ?>
                                <span class="star half">★</span>
                            <?php else : ?>
                                <span class="star empty">☆</span>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <span class="rating-number"><?= $media ?></span>
                    <span class="rating-count">(<?= $total ?> valoraciones)</span>
                </div>
            </div>

            <div class="valoraciones-lista">
                <?php if (count($valoraciones) > 0) : ?>                    <?php foreach ($valoraciones as $valoracion) : ?>
                        <div class="valoracion-item">
                            <?php if (!empty($valoracion['foto_perfil'])) : ?>
                                <img src="data:image/jpeg;base64,<?= base64_encode($valoracion['foto_perfil']) ?>" 
                                     alt="Foto de <?= htmlspecialchars($valoracion['nombre']) ?>" 
                                     class="valoracion-usuario-img">
                            <?php else : ?>
                                <div class="valoracion-usuario-img default">
                                    <?= htmlspecialchars(strtoupper(substr($valoracion['nombre'], 0, 1) . substr($valoracion['apellido'], 0, 1))) ?>
                                </div>
                            <?php endif; ?>
                            <div class="valoracion-content">
                                <div class="valoracion-header">
                                    <div class="valoracion-nombre">
                                        <?= htmlspecialchars($valoracion['nombre'] . ' ' . $valoracion['apellido']) ?>
                                    </div>
                                    <div class="valoracion-fecha">
                                        <?= date('d/m/Y', strtotime($valoracion['fecha_creacion'])) ?>
                                    </div>
                                </div>
                                <div class="valoracion-estrellas">
                                    <?php for ($i = 1; $i <= 5; $i++) : ?>
                                        <?php if ($i <= $valoracion['puntuacion']) : ?>
                                            <span class="star">★</span>
                                        <?php else : ?>
                                            <span class="star empty">☆</span>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <?php if (!empty($valoracion['comentario'])) : ?>
                                    <div class="valoracion-comentario">
                                        <?= nl2br($valoracion['comentario']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p class="mensaje-sin-valoraciones">No hay valoraciones todavía. ¡Sé el primero en valorar!</p>
                <?php endif; ?>
            </div>
        </div>

        <style>
        /* Estilos para el sistema de valoraciones */
        .valoraciones-section {
            margin: 20px 0;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .valoraciones-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .valoracion-stats {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .media-puntuacion {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .stars {
            display: flex;
            color: #FFD700;
            font-size: 24px;
        }
        
        .star.empty {
            color: #ddd;
        }
        
        .rating-number {
            font-size: 20px;
            font-weight: bold;
        }
        
        .rating-count {
            color: #666;
            font-size: 0.9em;
        }
        
        .btn-valoracion {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        .btn-valoracion:hover {
            background-color: #45a049;
        }
        
        .valoracion-item {
            display: flex;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .valoracion-item:last-child {
            border-bottom: none;
        }
        
        .valoracion-usuario-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }
        
        .valoracion-usuario-img.default {
            background-color: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #888;
            font-weight: bold;
        }
        
        .valoracion-content {
            flex-grow: 1;
        }
        
        .valoracion-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .valoracion-nombre {
            font-weight: bold;
            color: #333;
        }
        
        .valoracion-fecha {
            color: #999;
            font-size: 0.8em;
        }
        
        .valoracion-estrellas {
            color: #FFD700;
            margin-bottom: 5px;
        }
        
        .valoracion-comentario {
            color: #555;
            line-height: 1.4;
        }
        
        .mensaje-sin-valoraciones {
            color: #666;
            font-style: italic;
            text-align: center;
            padding: 20px;
        }
        </style>
        <?php
    } catch (PDOException $e) {
        echo '<div class="error">Error al cargar las valoraciones: ' . $e->getMessage() . '</div>';
    }
}
?>
