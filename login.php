<?php
session_start();
require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        // Buscar usuario en la base de datos
        $stmt = $pdo->prepare("SELECT * FROM usuarios 
                             INNER JOIN tipos_usuarios ON usuarios.id_tipo_usuario = tipos_usuarios.id_tipo_usuario
                             INNER JOIN estados_usuarios ON usuarios.id_estado_usuario = estados_usuarios.id_estado_usuario
                             WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['contraseña'])) {
            // Verificar si el usuario está activo
            if ($user['id_estado_usuario'] == 1) { // 1 = Activo
                $_SESSION['user'] = [
                    'id' => $user['id_usuario'],
                    'nombre' => $user['nombre'],
                    'email' => $user['email'],
                    'tipo' => $user['tipo'],
                    'id_tipo' => $user['id_tipo_usuario']
                ];

                // Redirigir según tipo de usuario
                switch ($user['id_tipo_usuario']) {
                    case 1: // Moderador
                        header('Location: admin/dashboard.php');
                        break;
                    case 2: // Cliente
                        header('Location: cliente/dashboard.php');
                        break;
                    case 3: // Autónomo
                        header('Location: autonomo/dashboard.php');
                        break;
                    default:
                        header('Location: perfil.php');
                }
                exit();
            } else {
                $error = "Tu cuenta no está activa";
            }
        } else {
            $error = "Email o contraseña incorrectos";
        }
    } catch (PDOException $e) {
        $error = "Error al iniciar sesión: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
</head>
<body>
    <div class="login-container">
        <h1>Iniciar Sesión</h1>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit">Ingresar</button>
        </form>

        <div class="register-link">
            <p>¿No tienes cuenta? <a href="create_users/index.php">Regístrate aquí</a></p>
        </div>
    </div>
</body>
</html>