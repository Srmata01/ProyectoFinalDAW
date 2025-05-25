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
    header('Location: ../index.php');
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
    $autonomo = $stmt->fetch();    if (!$autonomo) {
        header('Location: ../index.php');
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
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../includes/responsive-header.css">
    <link rel="stylesheet" href="../includes/footer.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <link rel="icon" type="image/png" href="../media/logo.png">
</head>
<body>    <?php 
        $base_path = '../';
        include '../includes/header_template.php'; 
    ?>

    <div class="responsive-container">
        <div class="profile-columns-container">
            <div class="profile-column">
                <div class="perfil-autonomo">
                    <div class="info-principal">
                        <?php if (!empty($autonomo['foto_perfil'])): ?>
                            <img src="data:image/jpeg;base64,<?= base64_encode($autonomo['foto_perfil']) ?>" 
                                 alt="Foto de perfil" class="foto-perfil">
                        <?php endif; ?>                        <div>                            <h1 class="detail-title">
                                <?= htmlspecialchars($autonomo['nombre'] . ' ' . $autonomo['apellido']) ?>
                            </h1>
                            <?php if (isset($_GET['mostrar_contacto']) && $_GET['mostrar_contacto'] == 1): ?>
                                <div class="detail-section">
                                    <p><strong>Teléfono:</strong> <?= htmlspecialchars($autonomo['telefono']) ?></p>
                                    <p><strong>Email:</strong> <?= htmlspecialchars($autonomo['email']) ?></p>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($autonomo['descripcion'])): ?>
                                <div class="detail-section">
                                    <h3>Sobre mí</h3>
                                    <p class="sobre-mi"><?= nl2br(htmlspecialchars($autonomo['descripcion'])) ?></p>
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
                    }                    ?>                    <h2 class="detail-title servicios-heading">Servicios ofrecidos (<?= $autonomo['total_servicios'] ?>)</h2>
                    <?php if (!empty($servicios)): ?>
                        <div class="servicios-grid">                            <?php foreach ($servicios as $servicio): ?>
                                <a href="../services/ver_servicio.php?id=<?= $servicio['id_servicio'] ?>" 
                                   class="servicio-card servicio-link">
                                    <h3><?= htmlspecialchars($servicio['nombre']) ?></h3>
                                    <p><?= htmlspecialchars($servicio['descripcion']) ?></p>
                                    <div class="servicio-card-footer">
                                        <strong class="servicio-precio">
                                            <?= number_format($servicio['precio'], 2) ?> €
                                        </strong>
                                        <span class="servicio-duracion">
                                            <?= $servicio['duracion'] ?> min
                                        </span>
                                    </div>
                                </a>
                            <?php endforeach; ?>                        </div>
                    <?php else: ?>
                        <p class="detail-section servicio-empty">Este profesional no tiene servicios activos actualmente.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>    </div>

    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        // Script para asegurar que el contenido tiene altura mínima para evitar que el footer suba demasiado
        document.addEventListener('DOMContentLoaded', function() {
            var contentHeight = document.querySelector('.responsive-container').offsetHeight;
            var windowHeight = window.innerHeight;
            var headerHeight = document.querySelector('header').offsetHeight;
            var footerHeight = document.querySelector('footer').offsetHeight;
            
            if (contentHeight < windowHeight - headerHeight - footerHeight) {
                document.querySelector('.responsive-container').style.minHeight = 
                    (windowHeight - headerHeight - footerHeight) + 'px';
            }
        });
    </script>
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