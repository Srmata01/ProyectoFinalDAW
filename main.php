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

$busqueda = $_GET['q'] ?? '';
$localidad = $_GET['localidad'] ?? '';
$precio = $_GET['precio'] ?? '';
$duracion = $_GET['duracion'] ?? '';
$orden = $_GET['orden'] ?? '';

$sql = "SELECT s.id_servicio, s.nombre, s.descripcion, s.precio, s.duracion, s.localidad, u.nombre AS nombre_autonomo, u.foto_perfil AS imagen_autonomo
        FROM servicios s
        JOIN usuarios u ON s.id_autonomo = u.id_usuario
        WHERE s.nombre LIKE :busqueda";

$params = [':busqueda' => "%$busqueda%"];

if ($localidad) {
    $sql .= " AND s.localidad LIKE :localidad";
    $params[':localidad'] = "%$localidad%";
}
if ($precio) {
    $sql .= " AND s.precio <= :precio";
    $params[':precio'] = $precio;
}
if ($duracion) {
    $sql .= " AND s.duracion <= :duracion";
    $params[':duracion'] = $duracion;
}
if ($orden == 'asc') {
    $sql .= " ORDER BY s.precio ASC";
} elseif ($orden == 'desc') {
    $sql .= " ORDER BY s.precio DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar que cada servicio tiene todos los campos necesarios
foreach ($servicios as &$servicio) {
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
            height: 100%;
            width: 100%;
            display: flex;
            flex-direction: column;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            padding: 15px;
            background-color: #fff;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
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
                <a href="main.php">
                    <img src="media/logo.png" alt="Logo FixItNow" class="logo">
                </a>
            </div>
            <div class="login-profile-box">
                <?php include 'includes/profile_header.php'; ?>
            </div>
        </div>
    </header>

    <!-- Sección de video y búsqueda -->
    <div class="video-background">
        <video autoplay muted loop>
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
                <a href="services/index.php" class="ver-todos-btn">Ver Todos los Servicios</a>
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
            background-color: #FF9B00;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .ver-todos-btn:hover {
            background-color:rgb(180, 108, 0);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
    </style>

    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h4>Información Personal</h4>
                <ul>
                    <li><a href="politicaprivacidad.php">Política de privacidad</a></li>
                    <li><a href="politicacookiesdatos.php">Política de Cookies y protección de datos</a></li>
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
                <h4>Eres miembro?</h4>
                <ul>
                    <li><a href="create_users/index.php">Únete a Nosotros</a></li>
                </ul>
            </div>

            <div class="footer-section social-media">
                <div class="social-icons">
                    <a href="#"><img src="media/twitter-icon.png" alt="Twitter"></a>
                    <a href="#"><img src="media/instagram-icon.png" alt="Instagram"></a>
                    <a href="#"><img src="media/facebook-icon.png" alt="Facebook"></a>
                    <a href="#"><img src="media/tiktok-icon.png" alt="TikTok"></a>
                </div>
            </div>

            <div class="footer-logo">
                <img src="media/logo.png" alt="FixItNow Logo">
            </div>
        </div>
    </footer>    <!-- Agregar referencia al script del buscador reutilizable -->
    <script src="services/js/buscador.js" defer></script>

    <script>
        // Inicializar los eventos para los filtros
        document.addEventListener('DOMContentLoaded', function() {
            const filtroLocalidad = document.getElementById('filtro_localidad');
            const filtroPrecio = document.getElementById('filtro_precio');
            const filtroDuracion = document.getElementById('filtro_duracion');
            const ordenAsc = document.getElementById('orden_asc');
            const ordenDesc = document.getElementById('orden_desc');
            
            // Función para aplicar filtros
            function aplicarFiltros() {
                const localidad = filtroLocalidad.value;
                const precio = filtroPrecio.value;
                const duracion = filtroDuracion.value;
                const orden = window.ordenActual || '';
                
                window.location.href = `services/buscarservicio.php?localidad=${encodeURIComponent(localidad)}&precio=${encodeURIComponent(precio)}&duracion=${encodeURIComponent(duracion)}&orden=${orden}`;
            }
            
            // Asignar eventos a los filtros
            filtroLocalidad.addEventListener('change', aplicarFiltros);
            filtroPrecio.addEventListener('change', aplicarFiltros);
            filtroDuracion.addEventListener('change', aplicarFiltros);
            
            // Eventos para los botones de orden
            ordenAsc.addEventListener('click', function() {
                window.ordenActual = 'asc';
                aplicarFiltros();
            });
            
            ordenDesc.addEventListener('click', function() {
                window.ordenActual = 'desc';
                aplicarFiltros();
            });
        });
    </script>
</body>

</html>
