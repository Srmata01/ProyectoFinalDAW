<?php
session_start();
require_once '../config/database.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['id'])) {
    header('Location: ../login.php');
    exit();
}

// Variables para mensajes
$mensaje = '';
$tipo_mensaje = '';

// Si se recibe un ID de usuario para valorar
$id_receptor = filter_var($_GET['id_usuario'] ?? 0, FILTER_SANITIZE_NUMBER_INT);

if ($id_receptor <= 0) {
    $mensaje = 'ID de usuario no válido';
    $tipo_mensaje = 'error';
}

// No permitir valorarse a sí mismo
if ($id_receptor == $_SESSION['usuario']['id']) {
    $mensaje = 'No puedes valorarte a ti mismo';
    $tipo_mensaje = 'error';
}

// Obtener información del usuario a valorar
if ($id_receptor > 0) {
    $stmt = $pdo->prepare("
        SELECT u.id_usuario, u.nombre, u.apellido, u.foto_perfil, tu.tipo as tipo_usuario 
        FROM usuarios u
        JOIN tipos_usuarios tu ON u.id_tipo_usuario = tu.id_tipo_usuario
        WHERE u.id_usuario = ?
    ");
    $stmt->execute([$id_receptor]);
    $usuario_receptor = $stmt->fetch();

    if (!$usuario_receptor) {
        $mensaje = 'Usuario no encontrado';
        $tipo_mensaje = 'error';
    }

    // Verificar si ya existe una valoración previa
    $stmt = $pdo->prepare("
        SELECT * FROM valoraciones_usuarios 
        WHERE id_emisor = ? AND id_receptor = ?
    ");
    $stmt->execute([$_SESSION['usuario']['id'], $id_receptor]);
    $valoracion_existente = $stmt->fetch();
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar campos obligatorios
    if (empty($_POST['puntuacion'])) {
        $mensaje = 'Por favor, selecciona una puntuación.';
        $tipo_mensaje = 'error';
    } else {
        try {
            $id_emisor = $_SESSION['usuario']['id'];
            $puntuacion = filter_var($_POST['puntuacion'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $comentario = htmlspecialchars(trim($_POST['comentario'] ?? ''), ENT_QUOTES, 'UTF-8');
            
            // Validar puntuación
            if ($puntuacion < 1 || $puntuacion > 5) {
                $mensaje = 'La puntuación debe estar entre 1 y 5.';
                $tipo_mensaje = 'error';
            } else {
                if ($valoracion_existente) {
                    // Actualizar valoración existente
                    $stmt = $pdo->prepare("
                        UPDATE valoraciones_usuarios 
                        SET puntuacion = ?, comentario = ?, fecha_creacion = CURRENT_TIMESTAMP 
                        WHERE id_emisor = ? AND id_receptor = ?
                    ");
                    $stmt->execute([$puntuacion, $comentario, $id_emisor, $id_receptor]);
                    
                    $mensaje = 'Valoración actualizada correctamente.';
                    $tipo_mensaje = 'success';
                } else {
                    // Insertar nueva valoración
                    $stmt = $pdo->prepare("
                        INSERT INTO valoraciones_usuarios (id_emisor, id_receptor, puntuacion, comentario) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$id_emisor, $id_receptor, $puntuacion, $comentario]);
                    
                    $mensaje = 'Valoración guardada correctamente.';
                    $tipo_mensaje = 'success';
                }

                // Redirección a la página del usuario valorado
                $tipo_usuario = $usuario_receptor['tipo_usuario'] === 'Autónomo' ? 'autonomo' : 'cliente';
                header("Location: ../vistas_usuarios/ver_{$tipo_usuario}.php?id={$id_receptor}&mensaje=valoracion_guardada");
                exit();
            }
        } catch (PDOException $e) {
            $mensaje = 'Error al guardar la valoración: ' . $e->getMessage();
            $tipo_mensaje = 'error';
        }
    }
}

// Determinar el tipo de usuario para mostrar la vista correcta
$nombre_usuario = $usuario_receptor ? "{$usuario_receptor['nombre']} {$usuario_receptor['apellido']}" : "";
$tipo_usuario_texto = $usuario_receptor ? $usuario_receptor['tipo_usuario'] : "";
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Valorar Usuario - FixItNow</title>
    <link rel="stylesheet" href="../main.css">
    <style>
        body {
            background: linear-gradient(-45deg, rgba(255, 180, 110, 0.7), rgba(255, 220, 150, 0.7), rgba(255, 148, 91, 0.7), rgba(255, 255, 255, 0.7));
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        .valoraciones-container {
            max-width: 800px;
            margin: 150px auto 30px;
            padding: 30px;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
        }

        .valoraciones-title {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
            font-size: 28px;
            position: relative;
            padding-bottom: 15px;
        }

        .valoraciones-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(to right, #FF8C42, #FFB347);
        }

        .mensaje-box {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .mensaje-error {
            background-color: #ffdddd;
            color: #ff0000;
        }

        .mensaje-success {
            background-color: #ddffdd;
            color: #009900;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .star-rating {
            display: inline-flex;
            flex-direction: row-reverse;
            font-size: 32px;
            justify-content: center;
            width: 100%;
            margin-bottom: 20px;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            color: #ddd;
            cursor: pointer;
            padding: 0 5px;
            transition: color 0.2s;
        }

        .star-rating :checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #FFD700;
        }

        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #eee;
            border-radius: 8px;
            resize: vertical;
            min-height: 150px;
            font-family: inherit;
            font-size: 16px;
        }

        textarea:focus {
            border-color: #FF8C42;
            outline: none;
        }

        .form-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(to right, #FF8C42, #FFB347);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 15px;
        }

        .form-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 140, 66, 0.4);
        }

        .usuario-info {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
        }

        .usuario-foto {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 20px;
            background-color: #f1f1f1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            font-weight: bold;
            color: #555;
        }

        .usuario-nombre {
            font-size: 20px;
            font-weight: bold;
        }

        .usuario-tipo {
            font-size: 16px;
            color: #666;
        }

        .valoracion-actual {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #FF8C42;
        }

        .valoracion-actual h3 {
            margin-top: 0;
            color: #444;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn-secundario {
            padding: 14px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            flex: 1;
        }

        .btn-secundario:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }

        .btn-eliminar {
            background-color: #dc3545;
        }

        .btn-eliminar:hover {
            background-color: #c82333;
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

    <div class="valoraciones-container">
        <h1 class="valoraciones-title">Valorar <?php echo $tipo_usuario_texto; ?></h1>

        <?php if (!empty($mensaje)): ?>
            <div class="mensaje-box mensaje-<?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <?php if ($id_receptor > 0 && $usuario_receptor): ?>
            <div class="usuario-info">
                <?php if (!empty($usuario_receptor['foto_perfil'])): ?>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($usuario_receptor['foto_perfil']); ?>" 
                         alt="Foto de perfil" class="usuario-foto">
                <?php else: ?>
                    <div class="usuario-foto">
                        <?php echo strtoupper(substr($usuario_receptor['nombre'], 0, 1) . substr($usuario_receptor['apellido'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
                <div>
                    <div class="usuario-nombre"><?php echo htmlspecialchars($nombre_usuario); ?></div>
                    <div class="usuario-tipo"><?php echo htmlspecialchars($tipo_usuario_texto); ?></div>
                </div>
            </div>

            <?php if ($valoracion_existente): ?>
                <div class="valoracion-actual">
                    <h3>Tu valoración actual</h3>
                    <div class="estrellas">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php if ($i <= $valoracion_existente['puntuacion']): ?>
                                <span style="color: #FFD700; font-size: 24px;">★</span>
                            <?php else: ?>
                                <span style="color: #ddd; font-size: 24px;">☆</span>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <?php if (!empty($valoracion_existente['comentario'])): ?>
                        <p><?php echo nl2br($valoracion_existente['comentario']); ?></p>
                    <?php else: ?>
                        <p><em>Sin comentario</em></p>
                    <?php endif; ?>
                    <p><small>Última actualización: <?php echo date('d/m/Y H:i', strtotime($valoracion_existente['fecha_creacion'])); ?></small></p>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label>Puntuación:</label>
                    <div class="star-rating">
                        <input type="radio" id="star5" name="puntuacion" value="5" <?php echo ($valoracion_existente && $valoracion_existente['puntuacion'] == 5) ? 'checked' : ''; ?>>
                        <label for="star5" title="5 estrellas">★</label>
                        <input type="radio" id="star4" name="puntuacion" value="4" <?php echo ($valoracion_existente && $valoracion_existente['puntuacion'] == 4) ? 'checked' : ''; ?>>
                        <label for="star4" title="4 estrellas">★</label>
                        <input type="radio" id="star3" name="puntuacion" value="3" <?php echo ($valoracion_existente && $valoracion_existente['puntuacion'] == 3) ? 'checked' : ''; ?>>
                        <label for="star3" title="3 estrellas">★</label>
                        <input type="radio" id="star2" name="puntuacion" value="2" <?php echo ($valoracion_existente && $valoracion_existente['puntuacion'] == 2) ? 'checked' : ''; ?>>
                        <label for="star2" title="2 estrellas">★</label>
                        <input type="radio" id="star1" name="puntuacion" value="1" <?php echo ($valoracion_existente && $valoracion_existente['puntuacion'] == 1) ? 'checked' : ''; ?>>
                        <label for="star1" title="1 estrella">★</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="comentario">Comentario (opcional):</label>
                    <textarea id="comentario" name="comentario" placeholder="Escribe tu opinión sobre este usuario..."><?php echo $valoracion_existente ? htmlspecialchars($valoracion_existente['comentario']) : ''; ?></textarea>
                </div>                <div class="form-actions">
                    <?php if ($valoracion_existente): ?>
                        <?php 
                        $tipo_usuario = $usuario_receptor['tipo_usuario'] === 'Autónomo' ? 'autonomo' : 'cliente';
                        ?>
                        <a href="eliminar.php?id_receptor=<?php echo $id_receptor; ?>&confirm=1&redirect=<?php echo $tipo_usuario; ?>" 
                           class="btn-secundario btn-eliminar" 
                           onclick="return confirm('¿Estás seguro de eliminar tu valoración?')">
                            Eliminar Valoración
                        </a>                    <?php endif; ?>
                    
                    <a href="../vistas_usuarios/ver_<?php echo $tipo_usuario; ?>.php?id=<?php echo $id_receptor; ?>" class="btn-secundario">
                        Cancelar
                    </a>
                    
                    <button type="submit" class="form-submit">
                        <?php echo $valoracion_existente ? 'Actualizar Valoración' : 'Enviar Valoración'; ?>
                    </button>
                </div>
            </form>
        <?php else: ?>
            <p>No se ha proporcionado un ID de usuario válido para valorar.</p>
            <a href="../main.php" class="form-submit" style="display: inline-block; width: auto; min-width: 200px; margin-top: 20px;">
                Volver al Inicio
            </a>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>
