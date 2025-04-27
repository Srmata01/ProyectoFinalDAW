<?php
require_once '../config/database.php';
session_start();

// Obtener todos los servicios con información del autónomo
$stmt = $pdo->query("
    SELECT s.*, 
           u.nombre as autonomo_nombre, 
           u.apellido as autonomo_apellido,
           u.telefono as autonomo_telefono
    FROM servicios s
    JOIN usuarios u ON s.id_autonomo = u.id_usuario
    WHERE s.estado = 'activo'
    ORDER BY s.nombre ASC
");
$servicios = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Servicios Disponibles - FixItNow</title>
    <link rel="stylesheet" href="../vistas_usuarios/vistas.css">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo-container">
                <a href="../main.php">
                    <img src="../media/logo.png" alt="Logo FixItNow" class="logo">
                </a>
            </div>
            <div class="user-container">
                <?php include '../includes/profile_header.php'; ?>
            </div>
        </div>
    </header>

    <div class="container1">
        <div class="profile-columns-container">
            <div class="profile-column">
                <h2 class="document-title">Servicios Disponibles</h2>
                
                <?php if (!empty($servicios)): ?>
                    <div class="form-grid">
                        <table>
                            <thead>
                                <tr>
                                    <th>Servicio</th>
                                    <th>Profesional</th>
                                    <th>Descripción</th>
                                    <th>Precio</th>
                                    <th>Duración</th>
                                    <th>Contacto</th>
                                    <?php if (isset($_SESSION['usuario']) && $_SESSION['usuario']['tipo'] == 2): ?>
                                        <th>Acciones</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($servicios as $servicio): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($servicio['nombre']) ?></td>
                                        <td><?= htmlspecialchars($servicio['autonomo_nombre'] . ' ' . $servicio['autonomo_apellido']) ?></td>
                                        <td><?= htmlspecialchars($servicio['descripcion']) ?></td>
                                        <td><?= number_format($servicio['precio'], 2) ?> €</td>
                                        <td><?= $servicio['duracion'] ?> min</td>
                                        <td><?= htmlspecialchars($servicio['autonomo_telefono']) ?></td>
                                        <?php if (isset($_SESSION['usuario']) && $_SESSION['usuario']['tipo'] == 2): ?>
                                            <td class="form-actions">
                                                <a href="../reservas/crear.php?servicio=<?= $servicio['id_servicio'] ?>" 
                                                   class="submit-btn">Reservar</a>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="document-text">No hay servicios disponibles en este momento.</p>
                <?php endif; ?>

                <?php if (!isset($_SESSION['usuario'])): ?>
                    <div class="form-actions" style="text-align: center; margin-top: 20px;">
                        <p>Para reservar servicios necesitas iniciar sesión</p>
                        <a href="../login.php" class="submit-btn">Iniciar Sesión</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h4>Información Personal</h4>
                <ul>
                    <li><a href="../politicaprivacidad.html">Política de privacidad</a></li>
                    <li><a href="../politicacookiesdatos.html">Política de Cookies y protección de datos</a></li>
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
</body>
</html>