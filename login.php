<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Configuración para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = filter_var(trim($_POST["email"] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST["password"] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Por favor, rellena todos los campos.";
    } else {
        try {
            $stmt = $pdo->prepare("
                SELECT id_usuario, nombre, apellido, email, contraseña, 
                       id_estado_usuario, id_tipo_usuario, telefono, direccion 
                FROM usuarios 
                WHERE email = ?
            ");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            if ($usuario) {
                if (password_verify($password, $usuario['contraseña'])) {
                    if ($usuario['id_estado_usuario'] == 1) { // 1 = Activo
                        // Limpiamos cualquier sesión anterior
                        session_regenerate_id(true);

                        // Configuración de la sesión
                        $_SESSION['usuario'] = [
                            'id' => $usuario['id_usuario'],
                            'nombre' => $usuario['nombre'],
                            'apellido' => $usuario['apellido'],
                            'email' => $usuario['email'],
                            'tipo' => $usuario['id_tipo_usuario'],
                            'estado' => $usuario['id_estado_usuario'],
                            'telefono' => $usuario['telefono'],
                            'direccion' => $usuario['direccion']
                        ];

                        // Redirección basada en redirección guardada o tipo de usuario
                        if (isset($_SESSION['redirect_after_login'])) {
                            $redirect = $_SESSION['redirect_after_login'];
                            unset($_SESSION['redirect_after_login']);
                            header("Location: " . $redirect);
                        } else {
                            switch ((int)$_SESSION['usuario']['tipo']) {
                                case 1:
                                    header("Location: vistas_usuarios/perfil_admin.php");
                                    break;
                                case 2:
                                    header("Location: vistas_usuarios/perfil_cliente.php");
                                    break;
                                case 3:
                                    header("Location: vistas_usuarios/perfil_autonomo.php");
                                    break;
                                default:
                                    header("Location: index.php");
                                    break;
                            }
                        }
                        exit();
                    } else {
                        $error = "Tu cuenta no está activa.";
                    }
                } else {
                    $error = "Credenciales incorrectas.";
                    usleep(rand(500000, 1000000));
                }
            } else {
                $error = "Credenciales incorrectas.";
                usleep(rand(500000, 1000000));
            }
        } catch (PDOException $e) {
            $error = "Error del sistema. Por favor, inténtalo más tarde.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="main.css">
    <link rel="icon" type="image/png" href="media/logo.png">
    <link rel="stylesheet" href="includes/responsive-header.css">
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
        </div>    </header>    <div class="responsive-container" style="margin-top: 3rem; min-height: calc(100vh - 200px); display: flex; align-items: center;">
        <div class="responsive-form-container" style="max-width: 400px; margin: 0 auto; padding: 1.5rem;">
            <h2 class="form-title">Iniciar Sesión</h2>

            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">            <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="email" style="margin-bottom: 0.3rem;">Correo electrónico:</label>
                    <input type="email" name="email" id="email" required
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                        placeholder="tucorreo@ejemplo.com"
                        style="padding: 0.5rem; border-radius: 4px;">
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="password" style="margin-bottom: 0.3rem;">Contraseña:</label>
                    <input type="password" name="password" id="password" required minlength="8" 
                        placeholder="Tu contraseña"
                        style="padding: 0.5rem; border-radius: 4px;">
                </div>

                <button type="submit" class="submit-btn" style="margin: 0.5rem 0;">Entrar</button>
            </form>            <div class="option-links" style="margin-top: 1rem; text-align: center;">
                <p class="option-description" style="margin-bottom: 0.5rem;">
                    <a>¿No tienes cuenta?</a>
                    <a href="create_users/index.php" class="register-link">Regístrate</a>
                </p>
                <p class="option-description">
                    <a href="recuperar_password.php" class="recover-link">¿Olvidaste tu contraseña?</a>
                </p>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
