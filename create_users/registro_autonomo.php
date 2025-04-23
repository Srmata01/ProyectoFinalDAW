<?php 
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $telefono = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $nif = $_POST['nif'] ?? '';

    if (empty($nombre) || empty($apellido) || empty($email) || empty($password) || empty($nif)) {
        $error = "Todos los campos obligatorios deben ser completados";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $error = "Este email ya está registrado";
            } else {
                $stmt = $pdo->prepare("INSERT INTO usuarios 
                      (nombre, apellido, email, contraseña, telefono, direccion, CIF, id_tipo_usuario, id_estado_usuario) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, 3, 1)");
                $stmt->execute([
                    $nombre,
                    $apellido,
                    $email,
                    password_hash($password, PASSWORD_DEFAULT),
                    $telefono,
                    $direccion,
                    $nif
                ]);
                
                header("Location: registro_exitoso.php?tipo=autonomo");
                exit();
            }
        } catch (PDOException $e) {
            $error = "Error al registrar: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Autónomo - FixItNow</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo-container">
                <a href="../main.html.php">
                    <img src="../media/logo.png" alt="Logo FixItNow" class="logo">
                </a>
            </div>
        </div>
    </header>

    <div class="container1">
        <div class="content">
            <h1>Registro de Autónomo</h1>
        </div>
        
        <form method="post" class="form-grid">
            <?php if (isset($error)): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <div class="form-row">
                <label>Nombre:
                    <input type="text" name="nombre" required>
                </label>
                <label>Apellido:
                    <input type="text" name="apellido" required>
                </label>
                <label>NIF/CIF:
                    <input type="text" name="nif" required placeholder="NIF personal o CIF de empresa">
                </label>
            </div>
            <div class="form-row">
                <label>Email:
                    <input type="email" name="email" required>
                </label>
                <label>Contraseña:
                    <input type="password" name="password" required>
                </label>
                <label>Teléfono:
                    <input type="tel" name="telefono" required>
                </label>
            </div>
            <div class="form-row">
                <label>Dirección:
                    <textarea name="direccion" rows="1" required></textarea>
                </label>
            </div>
            <div class="form-actions">
                <button type="submit" class="submit-btn">Registrarse</button>
            </div>
        </form>
    </div>

    <footer>
        // ...existing code...
    </footer>
</body>
</html>