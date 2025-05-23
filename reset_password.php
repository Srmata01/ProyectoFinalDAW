<?php
session_start();
require_once './config/database.php';

$mensaje = '';
$error = '';
$token_valido = false;
$token = '';

// Verificar si se proporcionó un token
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        // Verificar si el token es válido y no ha expirado
        $stmt = $pdo->prepare("SELECT rt.id, rt.id_usuario, rt.usado, u.nombre, u.email
                              FROM reset_tokens rt
                              JOIN usuarios u ON rt.id_usuario = u.id_usuario
                              WHERE rt.token = ? AND rt.usado = 0 AND rt.fecha_expiracion > NOW()");
        $stmt->execute([$token]);
        $token_info = $stmt->fetch();
        
        if ($token_info) {
            $token_valido = true;
            $id_usuario = $token_info['id_usuario'];
            $nombre_usuario = $token_info['nombre'];
            $email_usuario = $token_info['email'];
        } else {
            $error = "El enlace de restablecimiento no es válido o ha expirado.";
        }
    } catch (PDOException $e) {
        $error = "Ha ocurrido un error. Por favor, inténtalo más tarde.";
    }
} else {
    $error = "No se proporcionó un token de restablecimiento.";
}

// Procesar el formulario de restablecimiento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valido) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
      // Validar contraseñas
    if (strlen($password) < 8) {
        $error = "La contraseña debe tener al menos 8 caracteres.";
    } elseif ($password !== $confirm_password) {
        $error = "Las contraseñas no coinciden.";
    } else {
        try {            // Primero, obtener la contraseña actual para verificar que no sea la misma
            $stmt = $pdo->prepare("SELECT contraseña FROM usuarios WHERE id_usuario = ?");
            $stmt->execute([$id_usuario]);
            $usuario = $stmt->fetch();
            
            // Generar el hash de la nueva contraseña
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Actualizar la contraseña del usuario
            $stmt = $pdo->prepare("UPDATE usuarios SET contraseña = ? WHERE id_usuario = ?");
            $stmt->execute([$hashed_password, $id_usuario]);
            
            // Marcar el token como usado
            $stmt = $pdo->prepare("UPDATE reset_tokens SET usado = 1 WHERE token = ?");
            $stmt->execute([$token]);
            
            $mensaje = "Tu contraseña ha sido actualizada correctamente. Ahora puedes iniciar sesión con tu nueva contraseña.";
            
            // Redirigir al login después de 5 segundos
            header("refresh:5;url=login.php");        } catch (PDOException $e) {
            $error = "Ha ocurrido un error al actualizar la contraseña. Por favor, inténtalo más tarde.";
            // Para desarrollo, mostrar el error específico
            $error .= "<br>Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - FixItNow</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="main.css">
    <link rel="icon" type="image/png" href="media/logo.png">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo-container">
                <a href="index.php">
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
            <h2 class="form-title">Restablecer Contraseña</h2>
              <?php if (!empty($mensaje)): ?>
                <div class="success-message">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php elseif (!empty($error)): ?>
                <div class="error-message">
                    <?= htmlspecialchars($error) ?>
                </div>
                <p class="option-description" style="margin-top: 20px; text-align: center;">
                    <a href="recuperar_password.php" class="register-link">Volver a intentar</a>
                </p>
            <?php elseif ($token_valido): ?>
                <p>Hola <?= htmlspecialchars($nombre_usuario) ?>, establece tu nueva contraseña para la cuenta con correo <?= htmlspecialchars($email_usuario) ?>.</p>
                
                <form method="POST" action="reset_password.php?token=<?= htmlspecialchars($token) ?>">
                    <label for="password">Nueva contraseña:</label>
                    <input type="password" name="password" id="password" required minlength="8">
                    
                    <label for="confirm_password">Confirmar contraseña:</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                    
                    <button type="submit" class="submit-btn">Cambiar contraseña</button>
                </form>
            <?php endif; ?>

            <p class="option-description" style="margin-top: 20px; text-align: center;">
                <a href="login.php" class="register-link">Volver al inicio de sesión</a>
            </p>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
