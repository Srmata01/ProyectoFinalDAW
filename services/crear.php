<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] != 3) {
    header('Location: ../login.php');
    exit();
}

$id_autonomo = $_SESSION['usuario']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            $error = "No puedes crear servicios porque tu cuenta está inactiva. Contacta con el administrador.";
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO servicios 
                (id_autonomo, nombre, descripcion, precio, duracion, estado, localidad) 
                VALUES (?, ?, ?, ?, ?, 'activo', ?)
            ");
            
            $stmt->execute([
                $id_autonomo,
                $_POST['nombre'],
                $_POST['descripcion'],
                $_POST['precio'],
                $_POST['duracion'],
                $_POST['localidad']
            ]);
            
            header('Location: ../vistas_usuarios/perfil_autonomo.php');
            exit;
        }
    } catch (PDOException $e) {
        $error = "Error al crear el servicio: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Servicio</title>
    <link rel="stylesheet" href="../vistas_usuarios/vistas.css">
    <link rel="icon" type="image/png" href="../media/logo.png">
</head>
<body>
<header>
        <div class="header-container">
            <div class="logo-container">
                <a href="../index.php">
                    <img src="../media/logo.png" alt="Logo FixItNow" class="logo">
                </a>
            </div>

            <div class="search-container">
                <div class="search-box">                    <input type="text" placeholder="Buscar por servicio o localidad..." class="search-input">
                    <img src="../media/lupa.png" alt="Buscar" class="search-icon">
                </div>
            </div>

            <div class="user-container">
                <div class="profile-container">
                    <?php include '../includes/profile_header.php'; ?>
                    <a href="../includes/logout.php" class="submit-btn" style="margin-left: 10px;">Cerrar sesión</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container1">
        <div class="profile-columns-container">
            <div class="profile-column">
                <h2 class="document-title">Crear Nuevo Servicio</h2>
                
                <?php if (isset($error)): ?>
                    <div class="error-message"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <form method="post" class="form-grid">
                    <div class="form-row">
                        <label>
                            <span>Nombre del servicio:</span>
                            <input type="text" name="nombre" required>
                        </label>
                    </div>
                    
                    <div class="form-row">
                        <label>
                            <span>Descripción:</span>
                            <textarea name="descripcion" required rows="4"></textarea>
                        </label>
                    </div>
                    
                    <div class="form-row">
                        <label>
                            <span>Precio (€):</span>
                            <input type="number" step="0.01" name="precio" required>
                        </label>
                        
                        <label>
                            <span>Duración (minutos):</span>
                            <input type="number" name="duracion" required>
                        </label>
                    </div>

                    <div class="form-row">
                        <label>
                            <span>Localidad:</span>
                            <input type="text" name="localidad" required>
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="submit-btn">Crear Servicio</button>
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