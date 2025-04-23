<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $telefono = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? '';

    // Validación básica
    if (empty($nombre) || empty($apellido) || empty($email) || empty($password)) {
        $error = "Todos los campos obligatorios deben ser completados";
    } else {
        try {
            // Verificar si el email ya existe
            $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                $error = "Este email ya está registrado";
            } else {
                // Insertar nuevo cliente
                $stmt = $pdo->prepare("INSERT INTO usuarios 
                      (nombre, apellido, email, contraseña, telefono, direccion, id_tipo_usuario, id_estado_usuario) 
                      VALUES (?, ?, ?, ?, ?, ?, 2, 1)");
                $stmt->execute([
                    $nombre,
                    $apellido,
                    $email,
                    password_hash($password, PASSWORD_DEFAULT),
                    $telefono,
                    $direccion
                ]);

                header("Location: registro_exitoso.php?tipo=" . urlencode($tipo_usuario));
                exit();
            }
        } catch (PDOException $e) {
            $error = "Error al registrar: " . $e->getMessage();
        }
    }
}
?>



<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FixItNow</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="icon" type="image/png" href="../media/logo.png">
</head>

<body>
    <header>
        <div class="header-container">
            <!-- Logo a la izquierda -->
            <div class="logo-container">
                <a href="../main.html" class="logo-link">
                    <img src="../media/logo.png" alt="Logo FixItNow" class="logo">
                </a>
            </div>

            <!-- Buscador centrado -->
            <div class="search-container">
                <div class="search-box">
                    <input type="text" placeholder="Buscar proyectos, materiales..." class="search-input">
                    <img src="../media/lupa.png" alt="" onclick="">
                </div>
            </div>

            <!-- Perfil de usuario a la derecha -->
            <div class="user-container">
                <div class="profile-container">
                    <button class="profile-btn">
                        <div class="user-avatar">JT</div>
                        <span class="user-name">Jordi Torrella</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <style>
        /* Estilos para el contenedor del video */
        .video-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        /* Ajustar el video para cubrir toda la pantalla */
        .video-background video {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            object-fit: cover;
        }

        /* Contenido encima del video */
        .content {
            position: relative;
            z-index: 1;
            color: white;
            text-align: center;
            font-size: 2rem;
            padding: 20px;
        }

        
    </style>
    </head>

    <body>


        <div class="container1">
            <div class="content">
                <h1>Registrate como cliente</h1>
            </div>

            <form method="post" class="form-grid">
                <div class="form-row">
                    <label>Nombre:
                        <input type="text" name="nombre" required>
                    </label>
                    <label>Apellido:
                        <input type="text" name="apellido" required>
                    </label>
                    <label>Email:
                        <input type="email" name="email" required>
                    </label>
                </div>
                <div class="form-row">
                    <label>Contraseña:
                        <input type="password" name="password" required>
                    </label>
                    <label>Teléfono:
                        <input type="tel" name="telefono">
                    </label>
                    <label>Dirección:
                        <textarea name="direccion" rows="1"></textarea>
                    </label>
                </div>
                <div class="form-actions">
                    <button type="submit">Registrarse</button>
                </div>
            </form>

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
                    <h4>Eres miembro?</h4>
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