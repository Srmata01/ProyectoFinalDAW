<?php
session_start();
require_once 'config/database.php';

// Consulta para obtener los servicios más recientes (máximo 4)
$stmt_recientes = $pdo->prepare("
    SELECT s.id_servicio, s.nombre, s.descripcion, s.precio, s.duracion, s.localidad, 
           u.nombre AS nombre_autonomo, u.foto_perfil AS imagen_autonomo
    FROM servicios s
    JOIN usuarios u ON s.id_autonomo = u.id_usuario
    WHERE s.estado = 'activo'
    ORDER BY s.id_servicio DESC
    LIMIT 4
");
$stmt_recientes->execute();
$servicios_recientes = $stmt_recientes->fetchAll(PDO::FETCH_ASSOC);

// Verificar que cada servicio tiene todos los campos necesarios
foreach ($servicios_recientes as &$servicio) {
    if (!isset($servicio['id_servicio'])) {
        $servicio['id_servicio'] = '';
    }
    if (!isset($servicio['imagen_autonomo'])) {
        $servicio['imagen_autonomo'] = 'media/autonomo.jpg'; // imagen por defecto
    }
    if (!isset($servicio['nombre_autonomo'])) {
        $servicio['nombre_autonomo'] = 'Autónomo';
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FixItNow</title>
    <link rel="stylesheet" href="main.css">
    <link rel="icon" type="image/png" href="media/logo.png">
    <style>
        /* Grid de servicios - 4 en línea con centrado */
        .servicios-grid {
            display: flex;
            justify-content: center;
            flex-wrap: nowrap;
            gap: 20px;
            margin: 30px auto;
            max-width: 1200px;
        }
        
        .servicio-link {
            flex: 0 0 calc(25% - 15px); /* Cada tarjeta ocupa 1/4 del espacio menos el gap */
            max-width: 280px;
            text-decoration: none;
            color: inherit;
        }
        
        .servicio-card {
            height: 300px; /* Altura fija */
            width: 100%;
            display: flex;
            flex-direction: column;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            padding: 15px;
            background-color: #fff;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden; /* Para que el contenido no desborde */
        }
        
        .servicio-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        }
        
        .servicios-destacados {
            text-align: center;
            padding: 20px;
        }
        
        .no-servicios {
            text-align: center;
            padding: 30px;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin: 20px auto;
            max-width: 600px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        /* Estilos responsive */
        @media (max-width: 992px) {
            .servicios-grid {
                flex-wrap: wrap;
            }
            .servicio-link {
                flex: 0 0 calc(50% - 10px);
                max-width: none;
            }
        }
        
        @media (max-width: 576px) {
            .servicio-link {
                flex: 0 0 100%;
                max-width: 350px;
            }
        }
    </style>
</head>

<body>
    <header>
        <div class="header-container">
            <div class="logo-container">
                <a href="index.php">
                    <img src="media/logo.png" alt="Logo FixItNow" class="logo">
                </a>
            </div>            <div class="login-profile-box">
                <?php include 'includes/profile_header.php'; ?>
            </div>        </div>    </header>      
    
    <!-- Sección de video y búsqueda -->
    <div class="video-background">
        <video autoplay muted loop playsinline>
            <source src="media/videocorp1.mp4" type="video/mp4">
            Tu navegador no soporta videos en HTML5.
        </video>
        <div class="content">
            <h1>Encuentra a tu profesional cerca de ti</h1>
            <p class="subtitulo1">Soluciona tus obras de la forma más rápida</p>
            <div class="search-container">
                <?php include 'includes/search_form.php'; ?>
            </div>
        </div>
    </div>

    <!-- Sección de servicios recientes -->
    <div class="servicios-section">
        <div class="servicios-destacados">
            <h2>Servicios Recientes</h2>
            <?php if (!empty($servicios_recientes)): ?>
                <div class="servicios-grid servicios-recientes-grid">
                    <?php foreach ($servicios_recientes as $servicio): ?>
                        <a href="services/ver_servicio.php?id=<?php echo htmlspecialchars($servicio['id_servicio']); ?>" class="servicio-link">
                            <div class="servicio-card">
                                <div class="autonomo-info">
                                    <?php if (!empty($servicio['imagen_autonomo'])): ?>
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($servicio['imagen_autonomo']); ?>" alt="Foto de perfil" class="autonomo-imagen">
                                    <?php else: ?>
                                        <img src="media/autonomo.jpg" alt="Foto de perfil por defecto" class="autonomo-imagen">
                                    <?php endif; ?>
                                    <span class="autonomo-nombre"><?php echo htmlspecialchars($servicio['nombre_autonomo'] ?? 'Autónomo'); ?></span>
                                </div>
                                <h3 class="servicio-titulo"><?php echo htmlspecialchars($servicio['nombre']); ?></h3>
                                <p class="servicio-descripcion"><?php echo htmlspecialchars($servicio['descripcion']); ?></p>
                                <p class="servicio-precio"><?php echo number_format($servicio['precio'], 2); ?>€</p>
                                <p class="servicio-duracion"><?php echo htmlspecialchars($servicio['duracion']); ?> min</p>
                                <p class="servicio-localidad"><?php echo htmlspecialchars($servicio['localidad']); ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-servicios">No hay servicios disponibles en este momento.</p>
            <?php endif; ?>
            
            <!-- Botón para ver todos los servicios -->
            <div class="ver-todos-container">
                <a href="services/buscarservicio.php" class="ver-todos-btn">Ver Todos los Servicios</a>
            </div>
        </div>
    </div>

    <style>
        .ver-todos-container {
            text-align: center;
            margin: 30px auto;
        }
        
        .ver-todos-btn {
            display: inline-block;
            padding: 12px 30px;
            background-color:rgb(255, 158, 31);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .ver-todos-btn:hover {
            background-color:rgb(203, 115, 0);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);        }
    </style>    <?php 
    // Incluir el footer compartido (archivo en raíz, así que no necesita $base_path)
    include 'includes/footer.php';    ?>      <!-- Agregar referencia al script del buscador reutilizable -->
    <script src="services/js/buscador.js" defer></script>
    
    <!-- Script simplificado para el video de fondo -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var video = document.querySelector('.video-background video');
            
            // Si hay error al reproducir el video, mostrar imagen de fondo
            video.addEventListener('error', function() {
                document.querySelector('.video-background').style.backgroundImage = "url('media/wpfijo1.jpg')";
                document.querySelector('.video-background').style.backgroundSize = "cover";
            });
            
            // Intentar reproducir el video
            video.play().catch(function() {
                // Si no se puede reproducir automáticamente, mostrar imagen de fondo
                document.querySelector('.video-background').style.backgroundImage = "url('media/wpfijo1.jpg')";
                document.querySelector('.video-background').style.backgroundSize = "cover";
            });
        });
    </script>
</body>

</html>
