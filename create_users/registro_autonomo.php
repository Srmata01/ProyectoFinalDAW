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
    $foto_perfil = null;

    // Procesamiento de la foto de perfil
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $foto_temp = $_FILES['foto_perfil']['tmp_name'];
        $foto_tipo = $_FILES['foto_perfil']['type'];
        
        // Verificar que sea una imagen
        if (strpos($foto_tipo, 'image/') === 0) {
            $foto_perfil = file_get_contents($foto_temp);
        }
    }

    if (empty($nombre) || empty($apellido) || empty($email) || empty($password) || empty($nif)) {
        $error = "Todos los campos obligatorios deben ser completados";
    } elseif (strlen($password) < 8) {
        $error = "La contraseña debe tener al menos 8 caracteres";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $error = "Este email ya está registrado";
            } else {
                $stmt = $pdo->prepare("INSERT INTO usuarios 
                      (nombre, apellido, email, contraseña, telefono, direccion, DNI, id_tipo_usuario, id_estado_usuario, foto_perfil) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, 3, 1, ?)");
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
    <link rel="icon" type="image/png" href="../media/logo.png">
    <style>
        .video-background {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            overflow: hidden;
            z-index: -1;
        }
        .video-background video {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            min-width: 100%; min-height: 100%;
            object-fit: cover;
        }
        .content {
            position: relative;
            z-index: 1;
            color: orange;
            text-align: center;
            font-size: 2rem;
            padding: 20px;
        } 
        .form-grid {
            background-color: #8585855c;
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            color: white;
            width: 80%;
            margin: auto;
            margin-top: -5px;
        }
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .form-row label {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        input, textarea {
            padding: 0.5rem;
            border: none;
            border-radius: 8px;
            background-color: rgba(255,255,255,0.8);
        }
        .submit-btn {
            background-color: #ff5e00;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
        }
        .submit-btn:hover {
            background-color: #e04e00;
        }
        .error-message {
            background-color: rgba(255, 0, 0, 0.8);
            color: white;
            padding: 0.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }
        .container1 {
            background-color: transparent !important;
        }
    </style>
</head>
<body>


    <header>
        <div class="header-container">
            <div class="logo-container">
                <a href="../main.php">
                    <img src="../media/logo.png" alt="Logo FixItNow" class="logo">
                </a>
            </div>
            <div class="login-profile-box">
                <?php include '../includes/profile_header.php'; ?>
            </div>
        </div>
    </header>

   <!-- ✅ INICIO ZONA CON GRADIENTE ANIMADO -->
   <div class="app-main">
        <div class="content-wrapper">
                <div class="content">
                    <h1>Regístrate como Autonomo</h1>
                </div>

                <form method="post" class="form-grid" enctype="multipart/form-data">
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
                            <input type="tel" name="telefono">
                        </label>
                    </div>
                    <div class="form-row">
                        <label>Dirección:
                            <textarea name="direccion" rows="1"></textarea>
                        </label>
                        <label>Foto de perfil:
                            <input type="file" name="foto_perfil" accept="image/*">
                        </label>                    </div>
                    <div class="form-actions">
                        <button type="submit" class="submit-btn">Registrarse</button>
                    </div>
                </form>
        </div>
    </div>

    <?php 
    // Definir la ruta base para el footer
    $base_path = '../';
    include '../includes/footer.php'; 
    ?>
</body>
</html>
