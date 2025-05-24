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
    <link rel="stylesheet" href="../includes/responsive-header.css">
    <link rel="stylesheet" href="../includes/footer.css">
    <link rel="stylesheet" href="../includes/compact-forms.css">
    
</head>

<body>
    <header>
        <div class="header-container">
            <div class="logo-container">
                <a href="../index.php">
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
            <a href="../index.php" class="form-submit" style="display: inline-block; width: auto; min-width: 200px; margin-top: 20px;">
                Volver al Inicio
            </a>
        <?php endif; ?>    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
