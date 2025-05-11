<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] != 1) {
    header('Location: ../login.php');
    exit();
}

try {
    // Obtener usuarios
    $stmt = $pdo->prepare("
        SELECT u.*, tu.tipo as tipo_usuario, eu.estado as estado_usuario
        FROM usuarios u
        JOIN tipos_usuarios tu ON u.id_tipo_usuario = tu.id_tipo_usuario
        JOIN estados_usuarios eu ON u.id_estado_usuario = eu.id_estado_usuario
    ");
    $stmt->execute();
    $usuarios = $stmt->fetchAll();

    // Obtener incidencias
    $stmt = $pdo->prepare("
        SELECT i.*
        FROM incidencias i
        ORDER BY i.titulo_incidencia DESC
    ");
    $stmt->execute();
    $incidencias = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Error al obtener datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Administrador</title>
    <link rel="stylesheet" href="vistas.css">
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
                    <a href="../includes/logout.php" class="submit-btn" style="margin-left: 10px;">Cerrar sesión</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container1">
        <div class="document-container">
            <h1 class="document-title">Listado de Usuarios</h1>
            <div class="form-grid">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?= htmlspecialchars($usuario['nombre']) . ' ' . htmlspecialchars($usuario['apellido']) ?></td>
                                <td><?= htmlspecialchars($usuario['email']) ?></td>
                                <td><?= htmlspecialchars($usuario['telefono']) ?></td>
                                <td><?= htmlspecialchars($usuario['tipo_usuario']) ?></td>
                                <td><?= htmlspecialchars($usuario['estado_usuario']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="document-container">
            <h2 class="document-title">Incidencias Reportadas</h2>
            <?php if (count($incidencias) > 0): ?>
                <div class="form-grid">
                    <table>
                        <thead>
                            <tr>
                                <th>Persona</th>
                                <th>Email</th>
                                <th>Título</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($incidencias as $incidencia): ?>
                                <tr>
                                    <td><?= htmlspecialchars($incidencia['persona_incidencia']) ?></td>
                                    <td><?= htmlspecialchars($incidencia['mail_contacto']) ?></td>
                                    <td><?= htmlspecialchars($incidencia['titulo_incidencia']) ?></td>
                                    <td><?= htmlspecialchars($incidencia['cuerpo_incidencia']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="document-text">No hay incidencias registradas.</p>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <div class="footer-container">
            <div class="footer-section">
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
