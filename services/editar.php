<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] != 3) {
    header('Location: ../login.php');
    exit();
}

$id_autonomo = $_SESSION['usuario']['id'];
$id_servicio = $_GET['id'] ?? null;

if (!$id_servicio) {
    header('Location: ../vistas_usuarios/perfil_autonomo.php');
    exit();
}

try {
    // Verificar primero si el usuario está activo
    $stmt = $pdo->prepare("
        SELECT eu.estado 
        FROM usuarios u
        JOIN estados_usuarios eu ON u.id_estado_usuario = eu.id_estado_usuario
        WHERE u.id_usuario = ?
    ");
    $stmt->execute([$id_autonomo]);
    $usuario = $stmt->fetch();
    
    if (strtolower($usuario['estado']) != 'activo') {
        $error = "No puedes editar servicios porque tu cuenta está inactiva. Contacta con el administrador.";
    } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = $pdo->prepare("
            UPDATE servicios 
            SET nombre = ?, descripcion = ?, precio = ?, duracion = ?, estado = ?, localidad = ?
            WHERE id_servicio = ? AND id_autonomo = ?
        ");
        
        $stmt->execute([
            $_POST['nombre'],
            $_POST['descripcion'],
            $_POST['precio'],
            $_POST['duracion'],
            $_POST['estado'],
            $_POST['localidad'],
            $id_servicio,
            $id_autonomo
        ]);
        
        header('Location: ../vistas_usuarios/perfil_autonomo.php');
        exit();
    }

    // Obtener datos del servicio
    $stmt = $pdo->prepare("
        SELECT * FROM servicios 
        WHERE id_servicio = ? AND id_autonomo = ?
    ");
    $stmt->execute([$id_servicio, $id_autonomo]);
    $servicio = $stmt->fetch();

    if (!$servicio) {
        header('Location: ../vistas_usuarios/perfil_autonomo.php');
        exit();
    }

} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Servicio</title>
    <link rel="stylesheet" href="../vistas_usuarios/vistas.css">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo-container">
                <a href="../main.php">
                    <img src="../media/logo.png" alt="Logo FixItNow" class="logo">
                </a>
            </div>
            <div class="user-container">
                <?php include '../includes/profile_header.php'; ?>
            </div>
        </div>
    </header>

    <div class="container1">
        <div class="profile-columns-container">
            <div class="profile-column">
                <h2 class="document-title">Editar Servicio</h2>
                
                <?php if (isset($error)): ?>
                    <div class="error-message"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="post" class="form-grid">
                    <div class="form-row">
                        <label>
                            <span>Nombre del servicio:</span>
                            <input type="text" name="nombre" required 
                                   value="<?= htmlspecialchars($servicio['nombre']) ?>">
                        </label>
                    </div>
                    
                    <div class="form-row">
                        <label>
                            <span>Descripción:</span>
                            <textarea name="descripcion" required rows="4"><?= htmlspecialchars($servicio['descripcion']) ?></textarea>
                        </label>
                    </div>
                    
                    <div class="form-row">
                        <label>
                            <span>Precio (€):</span>
                            <input type="number" step="0.01" name="precio" required 
                                   value="<?= htmlspecialchars($servicio['precio']) ?>">
                        </label>
                        
                        <label>
                            <span>Duración (minutos):</span>
                            <input type="number" name="duracion" required 
                                   value="<?= htmlspecialchars($servicio['duracion']) ?>">
                        </label>
                    </div>

                    <div class="form-row">
                        <label>
                            <span>Estado:</span>
                            <select name="estado" required>
                                <option value="activo" <?= $servicio['estado'] === 'activo' ? 'selected' : '' ?>>Activo</option>
                                <option value="inactivo" <?= $servicio['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                        </label>
                    </div>

                    <div class="form-row">
                        <label>
                            <span>Localidad:</span>
                            <input type="text" name="localidad" required 
                                   value="<?= htmlspecialchars($servicio['localidad']) ?>">
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="submit-btn">Guardar Cambios</button>
                        <a href="../vistas_usuarios/perfil_autonomo.php" class="submit-btn" style="background-color: #6c757d;">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>    </div>

    <?php 
    // Definir la ruta base para el footer
    $base_path = '../';
    include '../includes/footer.php'; 
    ?>
</body>
</html>