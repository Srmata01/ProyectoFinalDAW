<?php
session_start();
require_once './config/database.php';

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (!empty($email)) {
        try {
            $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                // Aquí iría la lógica para enviar el email de recuperación
                $mensaje = "Si el correo existe en nuestra base de datos, recibirás un email con las instrucciones para recuperar tu contraseña.";
            } else {
                $mensaje = "Si el correo existe en nuestra base de datos, recibirás un email con las instrucciones para recuperar tu contraseña.";
            }
        } catch (PDOException $e) {
            $error = "Ha ocurrido un error. Por favor, inténtalo más tarde.";
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