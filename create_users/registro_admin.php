<?php 
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $error = '';
    
    // Validar nombre
    $nombre = trim($_POST['nombre']);
    if (empty($nombre)) {
        $error = "El nombre es obligatorio";
    } elseif (!preg_match('/^[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+$/', $nombre)) {
        $error = "El nombre solo debe contener letras y espacios";
    }
    
    // Validar apellido
    $apellido = trim($_POST['apellido']);
    if (!$error && empty($apellido)) {
        $error = "El apellido es obligatorio";
    } elseif (!$error && !preg_match('/^[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+$/', $apellido)) {
        $error = "El apellido solo debe contener letras y espacios";
    }
    
    // Validar email
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    if (!$error && empty($email)) {
        $error = "El email es obligatorio";
    } elseif (!$error && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El formato del email no es válido";
    }
    
    // Validar contraseña
    $password = $_POST['password'];
    if (!$error && (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || 
        !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password))) {
        $error = "La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número";    }    // Validar teléfono(opcional)
    $telefono = trim($_POST['telefono'] ?? '');
    if (!$error && !empty($telefono)) {
        $telefono = str_replace([' ', '-', '+'], '', $telefono);
        if (!preg_match('/^[0-9]{9}$/', $telefono)) {
            $error = "El teléfono debe tener 9 números";
        }
    }

    // Validar dirección
    $direccion = trim($_POST['direccion'] ?? '');

    // Validar código de administrador
    $codigo_admin = $_POST['codigo_admin'] ?? '';
    if (!$error && empty($codigo_admin)) {
        $error = "El código de administrador es obligatorio";
    } elseif (!$error && $codigo_admin !== ADMIN_CODE) {
        $error = "Código de administrador incorrecto";
    }
    
    // Si no hay errores, continuar con el registro
    if (!$error) {
        try {
            $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $error = "Este email ya está registrado";
            } else {                $stmt = $pdo->prepare("INSERT INTO usuarios 
                      (nombre, apellido, email, contraseña, telefono, direccion, id_tipo_usuario, id_estado_usuario) 
                      VALUES (?, ?, ?, ?, ?, ?, 1, 1)");
                $stmt->execute([
                    $nombre,
                    $apellido,
                    $email,
                    password_hash($password, PASSWORD_DEFAULT),
                    $telefono,
                    $direccion
                ]);
                
                header("Location: registro_exitoso.php?tipo=administrador");
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
    <title>Registro de Administrador - FixItNow</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../includes/responsive-header.css">
    <link rel="stylesheet" href="../includes/footer.css">
    <link rel="icon" type="image/png" href="../media/logo.png">
</head>
<body class="app">
    <header>
        <div class="header-container">
            <div class="logo-container">
                <a href="../index.php">
                    <img src="../media/logo.png" alt="Logo FixItNow" class="logo" style="height: 45px;">
                </a>
            </div>
            <div class="login-profile-box">
                <?php include '../includes/profile_header.php'; ?>
            </div>
        </div>
    </header><!-- ✅ INICIO ZONA CON GRADIENTE ANIMADO -->
   <div class="app-main">
        <div class="registro-container">
            <div class="registro-form">                <h1 class="registro-title">Regístrate como Administrador</h1>

                <form method="post">
                    <?php if (isset($error)): ?>
                        <div class="registro-error"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <div class="registro-grid">
                        <div class="registro-field">
                            <label for="nombre">Nombre</label>
                            <input type="text" id="nombre" name="nombre" class="registro-input" required>
                        </div>

                        <div class="registro-field">
                            <label for="apellido">Apellido</label>
                            <input type="text" id="apellido" name="apellido" class="registro-input" required>
                        </div>

                        <div class="registro-field">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="registro-input" required>
                        </div>                        <div class="registro-field">
                            <label for="password">Contraseña</label>
                            <input type="password" id="password" name="password" class="registro-input" required
                                   minlength="8" placeholder="Mínimo 8 caracteres">
                        </div>

                        <div class="registro-field">
                            <label for="telefono">Teléfono</label>
                            <input type="tel" id="telefono" name="telefono" class="registro-input">
                        </div>

                        <div class="registro-field">
                        <label for="codigo_admin">Código de Administrador</label>
                        <input type="password" id="codigo_admin" name="codigo_admin" class="registro-input" required>
                    </div>
                    </div>

                    <div class="registro-field">
                        <label for="direccion">Dirección</label>
                        <textarea id="direccion" name="direccion" class="registro-textarea" rows="2"></textarea>
                    </div>

                    <button type="submit" class="registro-submit">Registrarse</button>
                </form>
            </div>
        </div>
        </div>
    </div>
    <!-- ✅ FIN ZONA CON GRADIENTE ANIMADO -->

    <?php 
    // Definir la ruta base para el footer
    $base_path = '../';
    include '../includes/footer.php'; 
    ?>
</body>

</html>
