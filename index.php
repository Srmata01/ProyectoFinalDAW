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
    <link rel="stylesheet" href="includes/footer.css">
    <link rel="icon" type="image/png" href="media/logo.png">    <style>
        /* Grid de servicios - 4 en línea con centrado pero más compacto */        .servicios-grid {
            display: flex;
            justify-content: center;
            flex-wrap: nowrap;
            gap: var(--space-sm); /* Reducido de var(--space-md) */
            margin: var(--space-sm) auto; /* Reducido de var(--space-md) */
            max-width: 1000px; /* Reducido de 1100px */
        }
          .servicio-link {
            flex: 0 0 calc(25% - 12px); /* Cada tarjeta ocupa 1/4 del espacio menos el gap */
            max-width: 220px; /* Reducido de 240px */
            text-decoration: none;
            color: inherit;
        }
        
        .servicio-card {
            height: 175px; /* Reducido de 260px */
            width: 100%;
            display: flex;
            flex-direction: column;
            border-radius: var(--radius-sm);
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            padding: var(--space-xs); /* Reducido de var(--space-sm) */
            background-color: #fff;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden; /* Para que el contenido no desborde */
        }
        
        .servicio-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.12);
        }
        
        .servicios-destacados {
            text-align: center;
            padding: var(--space-sm);
        }
        
        .no-servicios {
            text-align: center;
            padding: var(--space-md);
            background-color: #f8f9fa;
            border-radius: var(--radius-sm);
            margin: var(--space-sm) auto;
            max-width: 500px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            font-size: var(--font-size-sm);
        }
        
        /* Estilos responsivos mejorados */
        @media (max-width: 992px) {
            .servicios-grid {
                flex-wrap: wrap;
                gap: var(--space-sm);
            }
            .servicio-link {
                flex: 0 0 calc(50% - 8px);
                max-width: none;
            }
            .content h1 {
                font-size: 2.2rem;
            }
            .content .subtitulo1 {
                font-size: 1.15rem;
            }
        }
        
        @media (max-width: 768px) {
            .content h1 {
                font-size: 1.75rem;
            }
            .content .subtitulo1 {
                font-size: 0.9rem;
            }
            .servicios-grid {
                padding: var(--space-xs);
            }
        }
        
        @media (max-width: 576px) {
            .servicio-link {
                flex: 0 0 100%;
                max-width: 280px;
            }
            .content h1 {
                font-size: 1.5rem;
                margin-bottom: var(--space-xs);
            }
            .content .subtitulo1 {
                margin-bottom: var(--space-md);
            }
            .search-input {
                padding: 6px 12px 6px 28px;
                height: 50px;
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
            </div>        </div>    </header>        <!-- Sección de video y búsqueda -->    <div class="video-background">
        <video autoplay muted loop playsinline>
            <source src="media/videocorp1.webm" type="video/webm">
        </video>
        <div class="content">
            <h1>Encuentra a tu profesional cerca de ti</h1>
            <p class="subtitulo1">Soluciona tus obras de la forma más rápida</p>
            <div class="search-container">
                <?php include 'includes/search_form.php'; ?>
            </div>
        </div>
    </div>

    <!-- Sección de servicios recientes -->    <div class="servicios-section compact-section">
        <div class="servicios-destacados">
            <h2 class="compact-heading">Servicios Recientes</h2>
            <?php if (!empty($servicios_recientes)): ?>
                <div class="servicios-grid servicios-recientes-grid">
                    <?php foreach ($servicios_recientes as $servicio): ?>
                        <a href="services/ver_servicio.php?id=<?php echo htmlspecialchars($servicio['id_servicio']); ?>" class="servicio-link">
                            <div class="servicio-card">                                <div class="autonomo-info compact-autonomo">
                                    <?php if (!empty($servicio['imagen_autonomo'])): ?>
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($servicio['imagen_autonomo']); ?>" alt="Foto de perfil" class="autonomo-imagen">
                                    <?php else: ?>
                                        <img src="media/autonomo.jpg" alt="Foto de perfil por defecto" class="autonomo-imagen">
                                    <?php endif; ?>
                                    <span class="autonomo-nombre"><?php echo htmlspecialchars($servicio['nombre_autonomo'] ?? 'Autónomo'); ?></span>
                                </div>
                                <h3 class="servicio-titulo compact-titulo"><?php echo htmlspecialchars($servicio['nombre']); ?></h3>
                                <p class="servicio-descripcion compact-descripcion"><?php 
                                    $desc = htmlspecialchars($servicio['descripcion']);
                                    echo (strlen($desc) > 60) ? substr($desc, 0, 60).'...' : $desc; 
                                ?></p>
                                <div class="servicio-detalles">
                                    <span class="servicio-precio"><?php echo number_format($servicio['precio'], 2); ?>€</span>
                                    <span class="servicio-duracion"><?php echo htmlspecialchars($servicio['duracion']); ?> min</span>
                                </div>
                                <p class="servicio-localidad compact-localidad"><?php echo htmlspecialchars($servicio['localidad']); ?></p>
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
    </div>    <style>        .ver-todos-container {
            text-align: center;
            margin: var(--space-sm) auto; /* Reducido de var(--space-md) */
        }
        
        .ver-todos-btn {
            display: inline-block;
            padding: 6px 16px; /* Reducido de 8px 20px */
            background-color: var(--color-primary);
            color: white;
            text-decoration: none;
            border-radius: var(--radius-sm);
            font-weight: bold;
            font-size: var(--font-size-xs); /* Reducido de var(--font-size-sm) */
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        }
        
        .ver-todos-btn:hover {
            background-color: var(--color-primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.12);
        }
        
        /* Estilos adicionales para elementos */        .compact-heading {
            font-size: var(--font-size-sg); /* Reducido de var(--font-size-xl) */
            margin-bottom: var(--space-sm); /* Reducido de var(--space-md) */
        }
        
        .compact-autonomo {
            margin-bottom: var(--space-xs);
            padding-bottom: var(--space-xs);
        }
          .autonomo-imagen {
            width: 32px; /* Reducido de 40px */
            height: 32px; /* Reducido de 40px */
            margin-right: var(--space-xs); /* Reducido de var(--space-sm) */
        }
        
        .compact-titulo {
            font-size: var(--font-size-sm); /* Reducido de var(--font-size-md) */
            margin-bottom: var(--space-xs);
        }
        
        .compact-descripcion {
            font-size: var(--font-size-xs); /* Reducido de var(--font-size-sm) */
            margin-bottom: var(--space-xs);
            line-height: 1.3; /* Reducido de 1.4 */
        }
          .servicio-detalles {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-xs);
        }
        
        .servicio-precio {
            font-size: var(--font-size-sm); /* Reducido de var(--font-size-md) */
            font-weight: bold;
        }
        
        .servicio-duracion {
            font-size: var(--font-size-xs);
            color: var(--color-text-lighter);
        }
        
        .compact-localidad {
            font-size: var(--font-size-xs);
            color: var(--color-text-light);
            margin-top: auto; /* Empuja al fondo de la tarjeta */
        }
          .compact-section {
            padding: var(--space-md) 0;
        }
        
        @media (max-width: 768px) {
            .compact-heading {
                font-size: var(--font-size-lg);
            }
              .ver-todos-btn {
                padding: 6px 16px;
                font-size: var(--font-size-xs);
            }
        }
        
        @media (max-width: 576px) {            .ver-todos-container {
                margin: var(--space-sm) auto;
            }
        }
    </style><?php 
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
