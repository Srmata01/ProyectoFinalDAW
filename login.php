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

    error_log("Intento de login - Email: " . $email);

    if (empty($email) || empty($password)) {
        $error = "Por favor, rellena todos los campos.";
    } else {
        try {
            // Consulta mejorada con más campos necesarios
            $stmt = $pdo->prepare("
                SELECT id_usuario, nombre, apellido, email, contraseña, 
                       id_estado_usuario, id_tipo_usuario, telefono, direccion 
                FROM usuarios 
                WHERE email = ?
            ");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            error_log("Resultado de búsqueda de usuario: " . ($usuario ? "Usuario encontrado" : "Usuario no encontrado"));

            if ($usuario) {
                error_log("Tipo de usuario: " . $usuario['id_tipo_usuario']);
                error_log("Estado de usuario: " . $usuario['id_estado_usuario']);

                if (password_verify($password, $usuario['contraseña'])) {
                    if ($usuario['id_estado_usuario'] == 1) { // 1 = Activo
                        // Limpiamos cualquier sesión anterior
                        session_unset();
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

                        error_log("Sesión creada exitosamente: " . print_r($_SESSION['usuario'], true));

                        // Redirección basada en tipo de usuario
                        switch ((int)$_SESSION['usuario']['tipo']) {
                            case 1:
                                error_log("Redirigiendo a vistas_usuarios/perfil_admin.php");
                                header("Location: vistas_usuarios/perfil_admin.php");
                                break;
                            case 2:
                                error_log("Redirigiendo a vistas_usuarios/perfil_cliente.php");
                                header("Location: vistas_usuarios/perfil_cliente.php");
                                break;
                            case 3:
                                error_log("Redirigiendo a vistas_usuarios/perfil_autonomo.php");
                                header("Location: vistas_usuarios/perfil_autonomo.php");
                                break;
                            default:
                                error_log("Tipo de usuario desconocido, redirigiendo a index.php");
                                header("Location: index.php");
                                break;
                        }
                        exit();
                    } else {
                        $error = "Tu cuenta no está activa.";
                        error_log("Intento de login con cuenta inactiva");
                    }
                } else {
                    $error = "Credenciales incorrectas.";
                    error_log("Contraseña incorrecta para el usuario: " . $email);
                    usleep(rand(500000, 1000000));
                }
            } else {
                $error = "Credenciales incorrectas.";
                error_log("Usuario no encontrado: " . $email);
                usleep(rand(500000, 1000000));
            }
        } catch (PDOException $e) {
            error_log("Error de base de datos: " . $e->getMessage());
            $error = "Error del sistema. Por favor, inténtalo más tarde.";
        }
    }
}

// Depuración: Verifica si llegamos aquí cuando no deberíamos
error_log("Flujo inesperado - ¿Redirección falló?");
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
            <a href="create_users/index.php">¿No tienes cuenta? Regístrate!</a><br>
            <a href="recuperar_password.php">¿Olvidaste tu contraseña?</a>
        </p>
    </div>
</body>

</html>