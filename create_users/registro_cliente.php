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
        $error = "La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número";
    }
    
    // Validar DNI/NIF
    $nif = strtoupper(trim($_POST['nif']));
    if (!$error && empty($nif)) {
        $error = "El DNI/NIF es obligatorio";
    } elseif (!$error && !preg_match('/^[0-9]{8}[A-Z]$/', $nif)) {
        $error = "El formato del DNI no es válido";
    } else {
        $letras = "TRWAGMYFPDXBNJZSQVHLCKE";
        if ($letras[((int)substr($nif, 0, 8)) % 23] !== $nif[8]) {
            $error = "El DNI no es válido (letra incorrecta)";
        }
    }
    
    // Validar teléfono (opcional)
    $telefono = trim($_POST['telefono'] ?? '');
    if (!$error && !empty($telefono)) {
        $telefono = str_replace([' ', '-'], '', $telefono);
        if (!preg_match('/^[679][0-9]{8}$/', $telefono)) {
            $error = "El formato del teléfono no es válido (debe ser un número español)";
        }
    }
    
    // Validar dirección
    $direccion = trim($_POST['direccion'] ?? '');

    // Procesar foto de perfil
    if (!$error && isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $foto_temp = $_FILES['foto_perfil']['tmp_name'];
        $foto_tipo = $_FILES['foto_perfil']['type'];

        if (strpos($foto_tipo, 'image/') === 0) {
            $foto_perfil = file_get_contents($foto_temp);
        } else {
            $error = "El archivo debe ser una imagen válida";
        }
    }

    // Si no hay errores, continuar con el registro
    if (!$error) {
        try {
            $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                $error = "Este email ya está registrado";
            } else {
                $stmt = $pdo->prepare("INSERT INTO usuarios 
                      (nombre, apellido, email, contraseña, telefono, direccion, DNI, id_tipo_usuario, id_estado_usuario, foto_perfil) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, 2, 1, ?)");
                $stmt->execute([
                    $nombre,
                    $apellido,
                    $email,
                    password_hash($password, PASSWORD_DEFAULT),
                    $telefono,
                    $direccion,
                    $nif,
                    $foto_perfil
                ]);

                header("Location: registro_exitoso.php?tipo=cliente");
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
    <title>Registro de Cliente - FixItNow</title>
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
    </header>
   <!-- ✅ INICIO ZONA CON GRADIENTE ANIMADO -->
   <div class="app-main">
        <div class="registro-container">
            <div class="registro-form">
                <h1 class="registro-title">Regístrate como Cliente</h1>

                <form method="post" enctype="multipart/form-data">
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
                            <label for="nif">DNI/NIF</label>
                            <input type="text" id="nif" name="nif" class="registro-input" required 
                                   placeholder="DNI o NIF">
                        </div>

                        <div class="registro-field">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="registro-input" required>
                        </div>

                        <div class="registro-field">
                            <label for="password">Contraseña</label>
                            <input type="password" id="password" name="password" class="registro-input" required 
                                   minlength="8" placeholder="Mínimo 8 caracteres">
                        </div>

                        <div class="registro-field">
                            <label for="telefono">Teléfono</label>
                            <input type="tel" id="telefono" name="telefono" class="registro-input">
                        </div>
                    </div>

                    <div class="registro-field">
                        <label for="direccion">Dirección</label>
                        <textarea id="direccion" name="direccion" class="registro-textarea" rows="2"></textarea>
                    </div>

                    <div class="registro-field">
                        <label for="foto_perfil">Foto de perfil</label>
                        <input type="file" id="foto_perfil" name="foto_perfil" class="registro-input" accept="image/*">
                    </div>

                    <button type="submit" class="registro-submit">Registrarse</button>
                </form>
            </div>
        </div>
   </div>
   <!-- ✅ FIN ZONA CON GRADIENTE ANIMADO -->

    <?php 
    $base_path = '../';
    include $base_path . 'includes/footer.php'; 
    ?>
</body>
</html>
