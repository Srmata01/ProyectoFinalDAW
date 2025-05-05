<?php
session_start();
require_once 'config/database.php';

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
                <div class="search-box">
                    <input type="text" id="busqueda" placeholder="Buscar lampistas, fontaneros, paletas..." class="search-input">
                    <img src="media/lupa.png" alt="Buscar">
                    <div id="resultados" class="autocomplete-results"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros de búsqueda -->
    <div class="filtros-container">
        <select id="filtro_localidad">
            <option value="">Selecciona localidad</option>
            <option value="Madrid">Madrid</option>
            <option value="Barcelona">Barcelona</option>
            <option value="Valencia">Valencia</option>
        </select>

        <select id="filtro_precio">
            <option value="">Selecciona precio</option>
            <option value="50">Hasta 50€</option>
            <option value="100">Hasta 100€</option>
            <option value="200">Hasta 200€</option>
        </select>

        <select id="filtro_duracion">
            <option value="">Selecciona duración</option>
            <option value="30">Hasta 30 min</option>
            <option value="60">Hasta 60 min</option>
            <option value="120">Hasta 120 min</option>
        </select>

        <div class="orden-container">
            <button id="orden_asc">Ordenar por precio ascendente</button>
            <button id="orden_desc">Ordenar por precio descendente</button>
        </div>
    </div>

    <!-- Sección de servicios destacados -->
    <div class="servicios-section">
        <div class="servicios-destacados">
            <h2>Servicios Destacados</h2>
            <div class="servicios-grid" id="resultados">
                <?php foreach ($servicios as $servicio): ?>
                    <a href="services/ver_servicio.php?id=<?php echo htmlspecialchars($servicio['id_servicio']); ?>" class="servicio-link">
                        <div class="servicio-card">
                            <div class="autonomo-info">
                                <?php if (!empty($servicio['imagen_autonomo'])): ?>
                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($servicio['imagen_autonomo']); ?>" alt="Foto de perfil" class="autonomo-imagen">
                                <?php else: ?>
                                    <img src="media/autonomo.jpg" alt="Foto de perfil por defecto" class="autonomo-imagen">
                                <?php endif; ?>
                                <span class="autonomo-nombre"><?php echo htmlspecialchars($servicio['nombre_autonomo']); ?></span>
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
        </div>
    </div>

    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h4>Información Personal</h4>
                <ul>
                    <li><a href="politicaprivacidad.html">Política de privacidad</a></li>
                    <li><a href="politicacookiesdatos.html">Política de Cookies y protección de datos</a></li>
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
    </footer>

    <script src="buscador.js"></script>
</body>

</html>
