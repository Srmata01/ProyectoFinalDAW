<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['usuario'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ../login.php');
    exit();
}

// Incluir el componente de valoraciones
$valoraciones_file = '../valoraciones/valoraciones_simple.php';
if (file_exists($valoraciones_file)) {
    require_once $valoraciones_file;
} else {
    die("Error: No se pudo encontrar el archivo de valoraciones ($valoraciones_file)");
}

// Verificar que la función existe
if (!function_exists('mostrarValoraciones')) {
    die("Error: La función mostrarValoraciones no está disponible");
}

if (!isset($_GET['id'])) {
    header('Location: ../main.php');
    exit();
}

$id_autonomo = $_GET['id'];

try {
    // Obtener información del autónomo
    $stmt = $pdo->prepare("
        SELECT u.*, COUNT(s.id_servicio) as total_servicios
        FROM usuarios u
        LEFT JOIN servicios s ON u.id_usuario = s.id_autonomo
        WHERE u.id_usuario = ? AND u.id_tipo_usuario = 3
        GROUP BY u.id_usuario
    ");
    $stmt->execute([$id_autonomo]);
    $autonomo = $stmt->fetch();

    if (!$autonomo) {
        header('Location: ../main.php');
        exit();
    }

    // Obtener servicios del autónomo
    $stmt = $pdo->prepare("
        SELECT *
        FROM servicios
        WHERE id_autonomo = ? AND estado = 'activo'
        ORDER BY nombre ASC
    ");
    $stmt->execute([$id_autonomo]);
    $servicios = $stmt->fetchAll();

    // Obtener imágenes del portafolio
    $stmt = $pdo->prepare("
        SELECT *
        FROM portfolios
        WHERE id_usuario = ?
        ORDER BY id_imagen DESC
    ");
    $stmt->execute([$id_autonomo]);
    $imagenes = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil de <?= htmlspecialchars($autonomo['nombre']) ?> - FixItNow</title>
    <link rel="stylesheet" href="vistas.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <style>
        .perfil-autonomo {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        .info-principal {
            display: flex;
            align-items: start;
            gap: 30px;
            margin-bottom: 30px;
        }
        .foto-perfil {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
        }
        .servicios-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .servicio-card {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            transition: transform 0.3s ease;
        }
        .servicio-card:hover {
            transform: translateY(-5px);
        }
        /* Estilos para el slider */
        .swiper {
            width: 100%;
            max-width: 800px; /* Limitamos el ancho máximo */
            padding-top: 20px;
            padding-bottom: 20px;
            margin: 0 auto;
        }
        .swiper-slide {
            background-position: center;
            background-size: cover;
            width: 200px; /* Reducimos el tamaño de las imágenes */
            height: 200px;
        }
        .swiper-slide img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .portfolio-section {
            margin-top: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
        }
        .portfolio-section h2 {
            margin-bottom: 20px;
            color: #FF9B00;
            text-align: center;
        }
        /* Personalización de los controles del slider */
        .swiper-button-next,
        .swiper-button-prev {
            color: #FF9B00;
            transform: scale(0.7); /* Hacemos las flechas más pequeñas */
        }
        .swiper-pagination-bullet-active {
            background: #FF9B00;
        }
    </style>
</head>
<body>
    <header>
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
        <div class="profile-columns-container">
            <div class="profile-column">
                <div class="perfil-autonomo">
                    <div class="info-principal">
                        <?php if (!empty($autonomo['foto_perfil'])): ?>
                            <img src="data:image/jpeg;base64,<?= base64_encode($autonomo['foto_perfil']) ?>" 
                                 alt="Foto de perfil" class="foto-perfil">
                        <?php endif; ?>
                        <div>                            <h1 class="document-title">
                                <?= htmlspecialchars($autonomo['nombre'] . ' ' . $autonomo['apellido']) ?>
                            </h1>
                            <?php if (isset($_GET['mostrar_contacto']) && $_GET['mostrar_contacto'] == 1): ?>
                                <p><strong>Teléfono:</strong> <?= htmlspecialchars($autonomo['telefono']) ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($autonomo['email']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($autonomo['descripcion'])): ?>
                                <div style="margin-top: 20px;">
                                    <h3>Sobre mí</h3>
                                    <p><?= nl2br(htmlspecialchars($autonomo['descripcion'])) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($imagenes)): ?>
                        <div class="portfolio-section">
                            <h2>Portafolio de Trabajos</h2>
                            <div class="swiper mySwiper">
                                <div class="swiper-wrapper">
                                    <?php foreach ($imagenes as $imagen): ?>
                                        <div class="swiper-slide">
                                            <img src="data:image/jpeg;base64,<?= base64_encode($imagen['imagen']) ?>" 
                                                 alt="Trabajo realizado"
                                                 title="<?= htmlspecialchars($imagen['descripcion'] ?? '') ?>">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="swiper-pagination"></div>
                                <div class="swiper-button-next"></div>
                                <div class="swiper-button-prev"></div>
                            </div>
                        </div>                    <?php endif; ?>                    <?php 
                    // Mostrar componente de valoraciones después del portafolio
                    if (isset($autonomo['id_usuario']) && is_numeric($autonomo['id_usuario'])) {
                        mostrarValoraciones($autonomo['id_usuario']);
                    } else {
                        echo '<div class="error">Error: No se pudo obtener el ID del usuario para mostrar valoraciones</div>';
                    }
                    ?>

                    <h2>Servicios ofrecidos (<?= $autonomo['total_servicios'] ?>)</h2>
                    <?php if (!empty($servicios)): ?>
                        <div class="servicios-grid">
                            <?php foreach ($servicios as $servicio): ?>
                                <a href="../services/ver_servicio.php?id=<?= $servicio['id_servicio'] ?>" 
                                   class="servicio-card" style="text-decoration: none; color: inherit;">
                                    <h3 style="color: #FF9B00;"><?= htmlspecialchars($servicio['nombre']) ?></h3>
                                    <p><?= htmlspecialchars($servicio['descripcion']) ?></p>
                                    <div style="margin-top: 15px;">
                                        <strong style="color: #FF9B00; font-size: 1.2em;">
                                            <?= number_format($servicio['precio'], 2) ?> €
                                        </strong>
                                        <span style="color: #666; margin-left: 10px;">
                                            <?= $servicio['duracion'] ?> min
                                        </span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>Este profesional no tiene servicios activos actualmente.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        var swiper = new Swiper(".mySwiper", {
            effect: "coverflow",
            grabCursor: true,
            centeredSlides: true,
            slidesPerView: 3, // Mostramos 3 slides a la vez
            spaceBetween: 20, // Espacio entre slides
            coverflowEffect: {
                rotate: 30, // Reducimos la rotación
                stretch: 0,
                depth: 50, // Reducimos la profundidad
                modifier: 1,
                slideShadows: true,
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            breakpoints: {
                // Cuando la pantalla es menor a 640px
                640: {
                    slidesPerView: 1,
                    spaceBetween: 10,
                },
                // Cuando la pantalla es menor a 768px
                768: {
                    slidesPerView: 2,
                    spaceBetween: 15,
                },
                // Cuando la pantalla es mayor a 768px
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 20,
                }
            }
        });
    </script>
</body>
</html>