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
            // Consulta mejorada con más campos necesarios
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
                        // Configuración completa de la sesión
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

                        // Depuración: Verifica los datos antes de redirigir
                        error_log("Login exitoso para: " . $email);
                        error_log("Datos de sesión: " . print_r($_SESSION['usuario'], true));

                        // Redirección basada en tipo de usuario
                        switch ($_SESSION['usuario']['tipo']) {
                            case 1: header("Location: admin_dashboard.php"); exit();
                            case 2: header("Location: perfil_cliente.php"); exit();
                            case 3: header("Location: perfil_autonomo.php"); exit();
                            default: header("Location: index.php"); exit();
                        }
                        exit();
                    } else {
                        $error = "Tu cuenta no está activa.";
                    }
                } else {
                    $error = "Credenciales incorrectas.";
                    usleep(rand(500000, 1000000)); // Retraso para seguridad
                }
            } else {
                $error = "Credenciales incorrectas.";
                usleep(rand(500000, 1000000)); // Retraso para seguridad
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