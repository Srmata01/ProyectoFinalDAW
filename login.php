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
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="main.css">
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
            <h2 class="form-title">Iniciar Sesión</h2>

            <?php if (!empty($error)): ?>
                <div class="error-message" style="color:red; margin-bottom:15px;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <label for="email">Correo electrónico:</label>
                <input type="email" name="email" id="email" required
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

                <label for="password">Contraseña:</label>
                <input type="password" name="password" id="password" required minlength="8">

                <button type="submit" class="submit-btn">Entrar</button>
            </form>

            <p class="option-description">
                <a href="" class="no-account-link">¿No tienes cuenta?</a>
                <a href="create_users/index.php" class="register-link">Regístrate!</a><br>
                <br>
                <a href="recuperar_password.php" class="register-link">¿Olvidaste tu contraseña?</a>
            </p>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
