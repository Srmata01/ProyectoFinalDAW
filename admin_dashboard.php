<?php
require_once 'config/database.php';
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] != 1) {
    header('Location: login.php');
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
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo-container">
                <img src="media/logo.png" alt="Logo" class="logo">
            </div>
            <div class="user-container">
                <div class="profile-container">
                    <a href="admin_dashboard.php" class="profile-btn" style="text-decoration: none;">
                        <div class="user-avatar"><?= strtoupper(substr($_SESSION['usuario']['nombre'], 0, 1)) ?></div>
                        <span class="user-name"><?= htmlspecialchars($_SESSION['usuario']['nombre']) ?></span>
                    </a>
                    <a href="includes/logout.php" class="submit-btn" style="margin-left: 10px;">Cerrar sesión</a>
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
                <h4 class="footer-title">Contacto</h4>
                <ul class="footer-list">
                    <li><a href="#" class="footer-link">admin@empresa.com</a></li>
                    <li><a href="#" class="footer-link">Tel: 123 456 789</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4 class="footer-title">Legal</h4>
                <ul class="footer-list">
                    <li><a href="#" class="footer-link">Términos y condiciones</a></li>
                    <li><a href="#" class="footer-link">Política de privacidad</a></li>
                </ul>
            </div>
        </div>
    </footer>
</body>
</html>
