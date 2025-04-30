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
                                    header("Location: main.php");
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

    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .option-description {
            margin-top: 20px;
            text-align: center;
        }

        .register-link {
            color: orange;
            text-decoration: underline;
        }

        .no-account-link {
            color: black;
            text-decoration: none;
        }

        .form-container {
            background-color: #f0f0f0;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .form-title {
            font-size: 24px;
            text-align: center;
            margin-bottom: 20px;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .submit-btn {
            background-color: orange;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
            font-size: 16px;
        }

        .submit-btn:hover {
            background-color:rgb(255, 188, 101);
        }

    </style>
</head>

<body>
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
</body>

</html>
