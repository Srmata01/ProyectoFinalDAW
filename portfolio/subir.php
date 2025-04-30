<?php
require_once '../config/database.php';
session_start();

// Validación de sesión y tipo de usuario
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] != 3) {
    header('Location: ../login.php');
    exit();
}

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_FILES['imagenes']) && is_array($_FILES['imagenes']['name'])) {
            $descripcion = $_POST['descripcion'] ?? '';
            
            for ($i = 0; $i < count($_FILES['imagenes']['name']); $i++) {
                if ($_FILES['imagenes']['error'][$i] === UPLOAD_ERR_OK) {
                    $tipo = $_FILES['imagenes']['type'][$i];
                    
                    // Verificar que sea una imagen
                    if (strpos($tipo, 'image/') === 0) {
                        $imagen = file_get_contents($_FILES['imagenes']['tmp_name'][$i]);
                        
                        $stmt = $pdo->prepare("INSERT INTO portfolios (id_usuario, imagen, descripcion) VALUES (?, ?, ?)");
                        $stmt->execute([$_SESSION['usuario']['id'], $imagen, $descripcion]);
                    } else {
                        $error .= "El archivo " . htmlspecialchars($_FILES['imagenes']['name'][$i]) . " no es una imagen válida.<br>";
                    }
                }
            }
            
            if (empty($error)) {
                $mensaje = "Imágenes subidas correctamente";
            }
        }
    } catch (PDOException $e) {
        $error = "Error al subir las imágenes: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Imágenes</title>
    <link rel="stylesheet" href="../vistas_usuarios/vistas.css">
    <style>
        .upload-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }
        .upload-form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-height: 100px;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
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
                <div class="profile-container">
                    <?php include '../includes/profile_header.php'; ?>
                </div>
            </div>
        </div>
    </header>

    <div class="upload-container">
        <h1>Subir Imágenes a la Galería</h1>
        
        <?php if ($mensaje): ?>
            <div class="message success">
                <?= $mensaje ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <div class="upload-form">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="imagenes">Seleccionar Imágenes:</label>
                    <input type="file" name="imagenes[]" id="imagenes" multiple accept="image/*" required>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción (opcional):</label>
                    <textarea name="descripcion" id="descripcion" placeholder="Añade una descripción para tus imágenes..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="submit-btn">Subir Imágenes</button>
                    <a href="index.php" class="submit-btn" style="background-color: #6c757d;">Volver a la Galería</a>
                </div>
            </form>
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