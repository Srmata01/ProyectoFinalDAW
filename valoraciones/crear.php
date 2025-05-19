<?php
/**
 * Archivo para crear valoraciones de usuarios
 */
// Asegurar que se muestre correctamente los caracteres especiales
header('Content-Type: text/html; charset=utf-8');

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

// Obtener información del usuario a valorar y verificaciones
$usuario_receptor = null;
if ($id_receptor > 0) {
    try {
        // Información del usuario
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
        } else {
            // Verificar si ya existe una valoración
            $stmt = $pdo->prepare("
                SELECT id_valoracion FROM valoraciones_usuarios 
                WHERE id_emisor = ? AND id_receptor = ?
            ");
            $stmt->execute([$_SESSION['usuario']['id'], $id_receptor]);
            
            if ($stmt->fetch()) {
                $tipo_usuario = $usuario_receptor['tipo_usuario'] === 'Autónomo' ? 'autonomo' : 'cliente';
                $_SESSION['mensaje'] = 'Ya has valorado a este usuario. No puedes añadir otra valoración.';
                header("Location: ../vistas_usuarios/ver_{$tipo_usuario}.php?id={$id_receptor}");
                exit();
            }
        }
    } catch (PDOException $e) {
        $mensaje = 'Error al obtener datos: ' . $e->getMessage();
        $tipo_mensaje = 'error';
    }
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $usuario_receptor) {
    // Validar campos obligatorios
    if (empty($_POST['puntuacion'])) {
        $mensaje = 'Por favor, selecciona una puntuación.';
        $tipo_mensaje = 'error';
    } else {        try {
            $puntuacion = filter_var($_POST['puntuacion'], FILTER_VALIDATE_INT);
            $comentario = trim($_POST['comentario'] ?? '');
            // Validar puntuación
            if ($puntuacion < 1 || $puntuacion > 5) {
                $mensaje = 'La puntuación debe estar entre 1 y 5.';
                $tipo_mensaje = 'error';
            } else {
                // Insertar nueva valoración
                $stmt = $pdo->prepare("
                    INSERT INTO valoraciones_usuarios (id_emisor, id_receptor, puntuacion, comentario) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$_SESSION['usuario']['id'], $id_receptor, $puntuacion, $comentario]);
                
                // Redirección
                $tipo_usuario = $usuario_receptor['tipo_usuario'] === 'Autónomo' ? 'autonomo' : 'cliente';
                $_SESSION['mensaje'] = 'Valoración guardada correctamente.';
                header("Location: ../vistas_usuarios/ver_{$tipo_usuario}.php?id={$id_receptor}");
                exit();
            }
        } catch (PDOException $e) {
            $mensaje = 'Error al guardar la valoración: ' . $e->getMessage();
            $tipo_mensaje = 'error';
        }
    }
}

// Variables simplificadas
$nombre_usuario = $usuario_receptor ? "{$usuario_receptor['nombre']} {$usuario_receptor['apellido']}" : "";
$tipo_usuario_texto = $usuario_receptor ? $usuario_receptor['tipo_usuario'] : "";
$tipo_usuario = $usuario_receptor ? ($usuario_receptor['tipo_usuario'] === 'Autónomo' ? 'autonomo' : 'cliente') : "";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Valorar Usuario - FixItNow</title>
    <link rel="stylesheet" href="../main.css">
    <style>
        body { background: linear-gradient(-45deg, rgba(255, 180, 110, 0.7), rgba(255, 220, 150, 0.7), rgba(255, 148, 91, 0.7), rgba(255, 255, 255, 0.7)); margin: 0; padding: 0; font-family: Arial, sans-serif; }
        .valoraciones-container { max-width: 800px; margin: 150px auto 30px; padding: 30px; background-color: #fff; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; }
        .valoraciones-title { text-align: center; margin-bottom: 30px; color: #333; font-size: 28px; position: relative; padding-bottom: 15px; }
        .valoraciones-title:after { content: ''; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 80px; height: 3px; background: linear-gradient(to right, #FF8C42, #FFB347); }
        .mensaje-box { padding: 15px; margin-bottom: 20px; border-radius: 5px; text-align: center; }
        .mensaje-error { background-color: #ffdddd; color: #ff0000; }
        .mensaje-success { background-color: #ddffdd; color: #009900; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; }
        .star-rating { display: inline-flex; flex-direction: row-reverse; font-size: 32px; justify-content: center; width: 100%; margin-bottom: 20px; }
        .star-rating input { display: none; }
        .star-rating label { color: #ddd; cursor: pointer; padding: 0 5px; transition: color 0.2s; }
        .star-rating :checked ~ label, .star-rating label:hover, .star-rating label:hover ~ label { color: #FFD700; }
        textarea { width: 100%; padding: 12px; border: 2px solid #eee; border-radius: 8px; resize: vertical; min-height: 150px; font-family: inherit; font-size: 16px; }
        textarea:focus { border-color: #FF8C42; outline: none; }
        .form-submit { width: 100%; padding: 14px; background: linear-gradient(to right, #FF8C42, #FFB347); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s; margin-top: 15px; }
        .form-submit:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255, 140, 66, 0.4); }
        .usuario-info { display: flex; align-items: center; justify-content: center; margin-bottom: 30px; }
        .usuario-foto { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin-right: 20px; background-color: #f1f1f1; display: flex; align-items: center; justify-content: center; font-size: 30px; font-weight: bold; color: #555; }
        .usuario-nombre { font-size: 20px; font-weight: bold; }
        .usuario-tipo { font-size: 16px; color: #666; }
        .form-actions { display: flex; gap: 10px; justify-content: center; }
        .btn-secundario { padding: 14px; background-color: #6c757d; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; display: inline-block; text-align: center; flex: 1; }
        .btn-secundario:hover { background-color: #5a6268; transform: translateY(-2px); }
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
    </header>    <div class="valoraciones-container">
        <h1 class="valoraciones-title">Valorar <?= $tipo_usuario_texto ?></h1>

        <?php if (!empty($mensaje)): ?>
            <div class="mensaje-box mensaje-<?= $tipo_mensaje ?>">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <?php if ($id_receptor > 0 && $usuario_receptor): ?>
            <div class="usuario-info">
                <?php if (!empty($usuario_receptor['foto_perfil'])): ?>
                    <img src="data:image/jpeg;base64,<?= base64_encode($usuario_receptor['foto_perfil']) ?>" 
                         alt="Foto de perfil" class="usuario-foto">
                <?php else: ?>
                    <div class="usuario-foto">
                        <?= strtoupper(substr($usuario_receptor['nombre'], 0, 1) . substr($usuario_receptor['apellido'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <div>
                    <div class="usuario-nombre"><?= htmlspecialchars($nombre_usuario) ?></div>
                    <div class="usuario-tipo"><?= htmlspecialchars($tipo_usuario_texto) ?></div>
                </div>
            </div>            <form action="" method="POST" accept-charset="UTF-8">
                <div class="form-group">
                    <label>Puntuación:</label>
                    <div class="star-rating">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" id="star<?= $i ?>" name="puntuacion" value="<?= $i ?>">
                            <label for="star<?= $i ?>" title="<?= $i ?> estrellas">★</label>
                        <?php endfor; ?>
                    </div>
                </div>                <div class="form-group">
                    <label for="comentario">Comentario (opcional):</label>
                    <textarea id="comentario" name="comentario" placeholder="Escribe tu opinión sobre este usuario..." rows="5"></textarea>
                    <small>Puedes usar acentos y caracteres especiales</small>
                </div>
                
                <div class="form-actions">
                    <a href="../vistas_usuarios/ver_<?= $tipo_usuario ?>.php?id=<?= $id_receptor ?>" class="btn-secundario">
                        Cancelar
                    </a>
                    <button type="submit" class="form-submit">Enviar Valoración</button>
                </div>
            </form>
        <?php else: ?>
            <p>No se ha proporcionado un ID de usuario válido para valorar.</p>
            <a href="../main.php" class="form-submit" style="display: inline-block; width: auto; min-width: 200px; margin-top: 20px;">
                Volver al Inicio
            </a>
        <?php endif; ?>    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
