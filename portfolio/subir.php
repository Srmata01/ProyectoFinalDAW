<?php
require_once '../config/database.php';
session_start();

$base_path = '../'; // Definimos base_path ya que estamos en un subdirectorio

// Validación de sesión y tipo de usuario
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] != 3) {
    header('Location: ../login.php');
    exit();
}

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_FILES['imagenes']) && is_array($_FILES['imagenes']['name'])) {
            $descripcion = $_POST['descripcion'] ?? '';
            
            for ($i = 0; $i < count($_FILES['imagenes']['name']); $i++) {
                if ($_FILES['imagenes']['error'][$i] === UPLOAD_ERR_OK) {
                    $tipo = $_FILES['imagenes']['type'][$i];
                    
                    // Verificar que sea una imagen
                    if (strpos($tipo, 'image/') === 0) {
                        $imagen = file_get_contents($_FILES['imagenes']['tmp_name'][$i]);
                        
                        $stmt = $pdo->prepare("INSERT INTO portfolios (id_usuario, imagen, descripcion) VALUES (?, ?, ?)");
                        $stmt->execute([$_SESSION['usuario']['id'], $imagen, $descripcion]);
                        
                        $mensaje = "Las imágenes se han subido correctamente.";
                    } else {
                        $error .= "El archivo " . htmlspecialchars($_FILES['imagenes']['name'][$i]) . " no es una imagen válida.<br>";
                    }
                } else {
                    $error .= "Error al subir " . htmlspecialchars($_FILES['imagenes']['name'][$i]) . ".<br>";
                }
            }
        }
    } catch (PDOException $e) {
        $error = "Error en la base de datos: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Imágenes al Portfolio</title>
    <link rel="stylesheet" href="../includes/responsive-header.css">
    <link rel="stylesheet" href="../includes/compact-forms.css">
    <link rel="stylesheet" href="../vistas_usuarios/vistas.css">
    <link rel="stylesheet" href="../includes/footer.css">
    <link rel="icon" type="image/png" href="../media/logo.png">
</head>
<body>
    <?php include '../includes/header_template.php'; ?>

    <div class="gallery-container">
        <h1>Subir Imágenes al Portfolio</h1>
        
        <?php if ($mensaje): ?>
            <div class="success-message"><?= $mensaje ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>

        <form action="subir.php" method="POST" enctype="multipart/form-data" class="upload-form">
            <div class="form-group">
                <label for="imagenes">Seleccionar Imágenes:</label>
                <input type="file" id="imagenes" name="imagenes[]" accept="image/*" multiple required>
            </div>
            
            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion" rows="4" placeholder="Describe brevemente los trabajos realizados"></textarea>
            </div>
            
            <div class="button-group">
                <button type="submit" class="submit-btn">Subir Imágenes</button>
                <a href="index.php" class="cancel-btn">Volver al Portfolio</a>
            </div>
        </form>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>