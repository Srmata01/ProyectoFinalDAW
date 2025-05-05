<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] != 3) {
    header('Location: ../login.php');
    exit();
}

$id_autonomo = $_SESSION['usuario']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO servicios 
            (id_autonomo, nombre, descripcion, precio, duracion, estado, localidad) 
            VALUES (?, ?, ?, ?, ?, 'activo', ?)
        ");
        
        $stmt->execute([
            $id_autonomo,
            $_POST['nombre'],
            $_POST['descripcion'],
            $_POST['precio'],
            $_POST['duracion'],
            $_POST['localidad']
        ]);
        
        header('Location: ../vistas_usuarios/perfil_autonomo.php');
        exit;
    } catch (PDOException $e) {
        $error = "Error al crear el servicio: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Servicio</title>
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

            <div class="search-container">
                <div class="search-box">
                    <input type="text" placeholder="Buscar proyectos, materiales..." class="search-input">
                    <img src="../media/lupa.png" alt="Buscar" class="search-icon">
                </div>
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
        <div class="profile-columns-container">
            <div class="profile-column">
                <h2 class="document-title">Crear Nuevo Servicio</h2>
                
                <?php if (isset($error)): ?>
                    <div class="error-message"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <form method="post" class="form-grid">
                    <div class="form-row">
                        <label>
                            <span>Nombre del servicio:</span>
                            <input type="text" name="nombre" required>
                        </label>
                    </div>
                    
                    <div class="form-row">
                        <label>
                            <span>Descripción:</span>
                            <textarea name="descripcion" required rows="4"></textarea>
                        </label>
                    </div>
                    
                    <div class="form-row">
                        <label>
                            <span>Precio (€):</span>
                            <input type="number" step="0.01" name="precio" required>
                        </label>
                        
                        <label>
                            <span>Duración (minutos):</span>
                            <input type="number" name="duracion" required>
                        </label>
                    </div>

                    <div class="form-row">
                        <label>
                            <span>Localidad:</span>
                            <input type="text" name="localidad" required>
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="submit-btn">Crear Servicio</button>
                        <a href="../vistas_usuarios/perfil_autonomo.php" class="submit-btn" style="background-color: #6c757d;">Cancelar</a>
                    </div>
                </form>
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