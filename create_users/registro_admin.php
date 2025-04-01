<?php 
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $codigo_admin = $_POST['codigo_admin'] ?? '';

    if (empty($nombre) || empty($apellido) || empty($email) || empty($password)) {
        $error = "Todos los campos obligatorios deben ser completados";
    } elseif ($codigo_admin !== ADMIN_CODE) {
        $error = "Código de administrador incorrecto";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $error = "Este email ya está registrado";
            } else {
                $stmt = $pdo->prepare("INSERT INTO usuarios 
                      (nombre, apellido, email, contraseña, id_tipo_usuario, id_estado_usuario) 
                      VALUES (?, ?, ?, ?, 1, 1)");
                $stmt->execute([
                    $nombre,
                    $apellido,
                    $email,
                    password_hash($password, PASSWORD_DEFAULT)
                ]);
                
                header("Location: registro_exitoso.php?tipo=" . urlencode($tipo_usuario));
                exit();
            }
        } catch (PDOException $e) {
            $error = "Error al registrar: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registro de Administrador</title>
    <meta charset="UTF-8">
</head>
<body>
    <h1>Registro de Administrador</h1>
    
    <?php if (!empty($error)): ?>
        <p style="color:red;"><?= $error ?></p>
    <?php endif; ?>
    
    <form method="post">
        <p>
            <label>Nombre: <input type="text" name="nombre" required></label>
        </p>
        <p>
            <label>Apellido: <input type="text" name="apellido" required></label>
        </p>
        <p>
            <label>Email: <input type="email" name="email" required></label>
        </p>
        <p>
            <label>Contraseña: <input type="password" name="password" required></label>
        </p>
        <p>
            <label>Código de Administrador: <input type="password" name="codigo_admin" required></label>
        </p>
        <p>
            <button type="submit">Registrarse</button>
        </p>
    </form>
</body>
</html>