<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$busqueda = $_GET['q'] ?? '';
$localidad = $_GET['localidad'] ?? '';
$precio = $_GET['precio'] ?? '';
$duracion = $_GET['duracion'] ?? '';
$orden = $_GET['orden'] ?? '';

$sql = "SELECT s.id_servicio, s.nombre, s.descripcion, s.precio, s.duracion, s.localidad, u.nombre AS nombre_autonomo, u.apellido AS apellido_autonomo, u.foto_perfil AS imagen_autonomo
        FROM servicios s
        JOIN usuarios u ON s.id_autonomo = u.id_usuario
        WHERE 1=1"; // Siempre verdadero, para añadir condiciones con AND

$params = [];

// Añadir condiciones si hay parámetros
if (!empty($busqueda)) {
    // Buscar en nombre, descripción del servicio, localidad o profesión del autónomo
    $sql .= " AND (s.nombre LIKE :busqueda OR s.descripcion LIKE :busqueda OR s.localidad LIKE :busqueda)";
    $params[':busqueda'] = "%$busqueda%";
}

// Si la localidad está presente como parámetro específico, usarla para filtrar
// Esto permite buscar por palabra clave general y también por localidad
if (!empty($localidad)) {
    $sql .= " AND s.localidad LIKE :localidad";
    $params[':localidad'] = "%$localidad%";
}

if (!empty($precio)) {
    $sql .= " AND s.precio <= :precio";
    $params[':precio'] = $precio;
}

if (!empty($duracion)) {
    $sql .= " AND s.duracion <= :duracion";
    $params[':duracion'] = $duracion;
}

// Determinar orden
if ($orden == 'asc') {
    $sql .= " ORDER BY s.precio ASC";
} elseif ($orden == 'desc') {
    $sql .= " ORDER BY s.precio DESC";
} else {
    $sql .= " ORDER BY s.id_servicio DESC"; // Por defecto, los más recientes primero
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Servicios - FixItNow</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="icon" type="image/png" href="../media/logo.png">
    <!-- Agregar referencia al script del buscador -->
    <script src="../services/js/buscador.js" defer></script>
    <style>
        .resultados-titulo {
            text-align: center;
            margin: 20px 0;
            color: var(--color-text);
        }
        
        .filtros-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
            margin: 20px 0;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: var(--radius-md);
        }
        
        .filtros-container select, 
        .filtros-container button {
            padding: 10px 15px;
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            font-size: 14px;
            background-color: white;
            cursor: pointer;
        }
        
        .servicios-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin: 30px auto;
            max-width: 1200px;
            padding: 0 20px;
        }
        
        .servicio-card {
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            padding: 20px;
            background-color: white;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
        }
        
        .servicio-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .servicio-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            align-items: center;
        }
        
        .servicio-titulo {
            font-size: 18px;
            font-weight: bold;
            color: var(--color-text);
            margin: 0;
        }
        
        .servicio-precio {
            font-size: 16px;
            font-weight: bold;
            color: var(--color-primary-dark);
        }
        
        .servicio-descripcion {
            margin: 10px 0;
            font-size: 14px;
            color: var(--color-text-light);
            line-height: 1.5;
        }
        
        .servicio-footer {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            font-size: 13px;
            color: var(--color-text-lighter);
        }
        
        .servicio-localidad, 
        .servicio-duracion {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .servicio-autonomo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
        }
        
        .autonomo-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .autonomo-info {
            display: flex;
            flex-direction: column;
        }
        
        .autonomo-nombre {
            font-size: 14px;
            font-weight: 500;
            color: var(--color-text);
        }
        
        .no-resultados {
            text-align: center;
            padding: 40px;
            font-size: 16px;
            color: var(--color-text-light);
            background-color: #f9f9f9;
            border-radius: var(--radius-md);
            margin: 30px auto;
            max-width: 600px;
        }
    </style>
</head>

<body class="app">
    <header class="app-header">
        <div class="header-container">
            <div class="logo-container">
                <a href="../main.php" class="logo-link">
                    <img src="../media/logo.png" alt="Logo FixItNow" class="logo">
                </a>
            </div>            <div class="search-container">
                <div class="search-box">                    <input type="text" 
                           id="buscador-principal" 
                           placeholder="Buscar por servicio o localidad..." 
                           class="search-input"
                           value="<?php echo htmlspecialchars($busqueda); ?>">
                    <img src="../media/lupa.png" alt="Buscar" class="search-icon" id="btn-buscar">
                </div>
            </div>

            <div class="user-container">
                <?php 
                if (isset($_SESSION['usuario'])) {
                    // Determinar perfil URL
                    $perfil_url = '';
                    switch ($_SESSION['usuario']['tipo']) {
                        case 1:
                            $perfil_url = '../vistas_usuarios/perfil_admin.php';
                            break;
                        case 2:
                            $perfil_url = '../vistas_usuarios/perfil_cliente.php';
                            break;
                        case 3:
                            $perfil_url = '../vistas_usuarios/perfil_autonomo.php';
                            break;
                    }
                    
                    // Obtener la foto de perfil del usuario
                    $stmt = $pdo->prepare("SELECT foto_perfil FROM usuarios WHERE id_usuario = ?");
                    $stmt->execute([$_SESSION['usuario']['id']]);
                    $usuario = $stmt->fetch();
                    $foto_perfil = $usuario['foto_perfil'];
                    ?>
                    <div class="profile-container">
                        <a href="<?= $perfil_url ?>" class="profile-btn" style="text-decoration: none;">
                            <?php if ($foto_perfil): ?>
                                <div class="user-avatar">
                                    <img src="data:image/jpeg;base64,<?= base64_encode($foto_perfil) ?>" alt="Foto de perfil">
                                </div>
                            <?php else: ?>
                                <div class="user-avatar"><?= strtoupper(substr($_SESSION['usuario']['nombre'], 0, 1)) ?></div>
                            <?php endif; ?>
                            <span class="user-name"><?= htmlspecialchars($_SESSION['usuario']['nombre'] . ' ' . $_SESSION['usuario']['apellido']) ?></span>
                        </a>
                    </div>
                <?php } else { ?>
                    <a href="../login.php" class="profile-btn">
                        <span class="user-name">Iniciar Sesión</span>
                    </a>
                <?php } ?>
            </div>
        </div>
    </header>

    <main class="app-main">
        <h1 class="resultados-titulo">
            <?php if (!empty($busqueda)): ?>
                Resultados de búsqueda para: "<?php echo htmlspecialchars($busqueda); ?>"
            <?php else: ?>
                Servicios disponibles
            <?php endif; ?>
        </h1>

        <!-- Filtros de búsqueda -->
        <div class="filtros-container">
            <form action="buscarservicio.php" method="GET" id="filtros-form">
                <!-- Mantener el parámetro de búsqueda actual -->
                <input type="hidden" name="q" value="<?php echo htmlspecialchars($busqueda); ?>">
                
                <select name="localidad" id="filtro_localidad">
                    <option value="">Todas las localidades</option>
                    <option value="Madrid" <?php echo ($localidad == 'Madrid') ? 'selected' : ''; ?>>Madrid</option>
                    <option value="Barcelona" <?php echo ($localidad == 'Barcelona') ? 'selected' : ''; ?>>Barcelona</option>
                    <option value="Valencia" <?php echo ($localidad == 'Valencia') ? 'selected' : ''; ?>>Valencia</option>
                </select>
                
                <select name="precio" id="filtro_precio">
                    <option value="">Cualquier precio</option>
                    <option value="50" <?php echo ($precio == '50') ? 'selected' : ''; ?>>Hasta 50€</option>
                    <option value="100" <?php echo ($precio == '100') ? 'selected' : ''; ?>>Hasta 100€</option>
                    <option value="200" <?php echo ($precio == '200') ? 'selected' : ''; ?>>Hasta 200€</option>
                </select>
                
                <select name="duracion" id="filtro_duracion">
                    <option value="">Cualquier duración</option>
                    <option value="30" <?php echo ($duracion == '30') ? 'selected' : ''; ?>>Hasta 30 min</option>
                    <option value="60" <?php echo ($duracion == '60') ? 'selected' : ''; ?>>Hasta 60 min</option>
                    <option value="120" <?php echo ($duracion == '120') ? 'selected' : ''; ?>>Hasta 120 min</option>
                </select>
                
                <button type="submit">Aplicar filtros</button>
            </form>
            
            <div class="orden-container">
                <a href="?q=<?php echo urlencode($busqueda); ?>&localidad=<?php echo urlencode($localidad); ?>&precio=<?php echo urlencode($precio); ?>&duracion=<?php echo urlencode($duracion); ?>&orden=asc" class="btn-orden <?php echo ($orden == 'asc') ? 'active' : ''; ?>">
                    Menor precio
                </a>
                <a href="?q=<?php echo urlencode($busqueda); ?>&localidad=<?php echo urlencode($localidad); ?>&precio=<?php echo urlencode($precio); ?>&duracion=<?php echo urlencode($duracion); ?>&orden=desc" class="btn-orden <?php echo ($orden == 'desc') ? 'active' : ''; ?>">
                    Mayor precio
                </a>
            </div>
        </div>
        
        <?php if (!empty($servicios)): ?>
            <div class="servicios-grid">
                <?php foreach ($servicios as $servicio): ?>
                    <a href="ver_servicio.php?id=<?php echo htmlspecialchars($servicio['id_servicio']); ?>" class="servicio-card-link">
                        <div class="servicio-card">
                            <div class="servicio-header">
                                <h3 class="servicio-titulo"><?php echo htmlspecialchars($servicio['nombre']); ?></h3>
                                <div class="servicio-precio"><?php echo number_format($servicio['precio'], 2); ?>€</div>
                            </div>
                            
                            <p class="servicio-descripcion"><?php echo htmlspecialchars(substr($servicio['descripcion'], 0, 120)); ?>...</p>
                            
                            <div class="servicio-footer">
                                <div class="servicio-localidad">
                                    <span>📍</span> <?php echo htmlspecialchars($servicio['localidad']); ?>
                                </div>
                                <div class="servicio-duracion">
                                    <span>⏱️</span> <?php echo htmlspecialchars($servicio['duracion']); ?> min
                                </div>
                            </div>
                            
                            <div class="servicio-autonomo">
                                <?php if (!empty($servicio['imagen_autonomo'])): ?>
                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($servicio['imagen_autonomo']); ?>" alt="Foto de perfil" class="autonomo-img">
                                <?php else: ?>
                                    <img src="../media/autonomo.jpg" alt="Foto de perfil por defecto" class="autonomo-img">
                                <?php endif; ?>
                                
                                <div class="autonomo-info">
                                    <span class="autonomo-nombre"><?php echo htmlspecialchars($servicio['nombre_autonomo'] . ' ' . $servicio['apellido_autonomo']); ?></span>
                                    <span class="autonomo-tipo">Profesional</span>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-resultados">
                <p>No se encontraron servicios que coincidan con tu búsqueda.</p>
                <p>Intenta con otros términos o filtros de búsqueda.</p>
            </div>
        <?php endif; ?>
    </main>

    <footer class="app-footer">
        <div class="footer-container">
            <div class="footer-section">
                <h4 class="footer-title">Información Personal</h4>
                <ul class="footer-list">
                    <li><a href="../politicaprivacidad.php" class="footer-link">Política de privacidad</a></li>
                    <li><a href="../politicacookiesdatos.php" class="footer-link">Política de Cookies y protección de
                            datos</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4 class="footer-title">Contacto</h4>
                <ul class="footer-list">
                    <li><a href="mailto:fixitnow@gmail.com" class="footer-link">fixitnow@gmail.com</a></li>
                    <li><a href="tel:+34690096690" class="footer-link">+34 690 096 690</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4 class="footer-title">¿Eres miembro?</h4>
                <ul class="footer-list">
                    <li><a href="../create_users/index.php" class="footer-link">Únete a Nosotros</a></li>
                </ul>
            </div>

            <div class="footer-section social-media">
                <div class="social-icons">
                    <a href="#" class="social-link"><img src="../media/twitter-icon.png" alt="Twitter"
                            class="social-icon"></a>
                    <a href="#" class="social-link"><img src="../media/instagram-icon.png" alt="Instagram"
                            class="social-icon"></a>
                    <a href="#" class="social-link"><img src="../media/facebook-icon.png" alt="Facebook"
                            class="social-icon"></a>
                    <a href="#" class="social-link"><img src="../media/tiktok-icon.png" alt="TikTok"
                            class="social-icon"></a>
                </div>
            </div>

            <div class="footer-logo">
                <img src="../media/logo.png" alt="FixItNow Logo" class="footer-logo-img">
            </div>
        </div>
    </footer>

    <script>
        // Añadir funcionalidad para aplicar filtros automáticamente cuando cambian
        document.addEventListener('DOMContentLoaded', function() {
            // Obtener los elementos de filtro
            const filtros = document.querySelectorAll('#filtro_localidad, #filtro_precio, #filtro_duracion');
            
            // Añadir eventos de cambio
            filtros.forEach(filtro => {
                filtro.addEventListener('change', function() {
                    document.getElementById('filtros-form').submit();
                });
            });
        });
    </script>
</body>

</html>
