<?php
session_start();
require_once './config/database.php';

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (!empty($email)) {
        try {
            // Verificar si el email existe en la base de datos
            $stmt = $pdo->prepare("SELECT id_usuario, nombre FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();
            
            if ($usuario) {
                // Generar un token aleatorio
                $token = bin2hex(random_bytes(32)); // 64 caracteres hexadecimales
                $id_usuario = $usuario['id_usuario'];
                
                // Establecer fecha de expiración (24 horas)
                $fecha_expiracion = date('Y-m-d H:i:s', strtotime('+24 hours'));
                
                // Eliminar tokens antiguos del usuario
                $stmt = $pdo->prepare("DELETE FROM reset_tokens WHERE id_usuario = ?");
                $stmt->execute([$id_usuario]);
                
                // Guardar el token en la base de datos
                $stmt = $pdo->prepare("INSERT INTO reset_tokens (id_usuario, token, fecha_expiracion) VALUES (?, ?, ?)");
                $stmt->execute([$id_usuario, $token, $fecha_expiracion]);
                
                // Redirigir directamente a la página de restablecimiento
                header("Location: reset_password.php?token=" . $token);
                exit;
            } else {
                // Si el email no existe, mostrar un mensaje de error genérico
                $error = "No se ha encontrado ninguna cuenta con ese correo electrónico.";
            }
        } catch (PDOException $e) {
            $error = "Ha ocurrido un error. Por favor, inténtalo más tarde.";
            // Para desarrollo, puedes descomentar la siguiente línea
            // $error .= "<br>Error: " . $e->getMessage();
        }
    } else {
        $error = "Por favor, introduce un email válido.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - FixItNow</title>
    <link rel="stylesheet" href="styles.css">
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

    <div class="container1">
        <div class="form-container">
            <h2 class="form-title">Recuperar Contraseña</h2>
            
            <?php if (!empty($mensaje)): ?>
                <div class="success-message">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <label for="email">Correo electrónico:</label>
                <input type="email" name="email" id="email" required>
                
                <button type="submit" class="submit-btn">Enviar instrucciones</button>
            </form>

            <p class="option-description" style="margin-top: 20px; text-align: center;">
                <a href="login.php" class="register-link">Volver al inicio de sesión</a>
            </p>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>