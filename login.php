<?php
session_start();
require_once __DIR__ . '/config/database.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? '');
    $password = $_POST["password"] ?? '';

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT id_usuario, nombre, contraseña, id_estado_usuario, id_tipo_usuario FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['contraseña'])) {
            if ($usuario['id_estado_usuario'] == 1) {
                $_SESSION["usuario_id"] = $usuario["id_usuario"];
                $_SESSION["usuario_nombre"] = $usuario["nombre"];
                $_SESSION["usuario_tipo"] = $usuario["id_tipo_usuario"];
                header("Location: main.html"); // Redirige a tu página principal
                exit();
            } else {
                $error = "Tu cuenta no está activa.";
            }
        } else {
            $error = "Credenciales incorrectas.";
        }
    } else {
        $error = "Por favor, rellena todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="styles.css"> <!-- Asegúrate que esta ruta es correcta -->
</head>
<body>
    <div class="form-container">
        <h2 class="form-title">Iniciar Sesión</h2>

        <?php if (!empty($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="email">Correo electrónico:</label>
            <input type="text" name="email" id="email" required>

            <label for="password">Contraseña:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit" class="submit-btn">Entrar</button>
        </form>
        <br>
        <p class="option-description"><a href="create_users/index.php">No tienes cuenta? Registrate!</a></p>
    </div>
</body>
</html>
