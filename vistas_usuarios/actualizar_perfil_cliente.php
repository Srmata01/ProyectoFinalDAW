<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] != 2) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_usuario = $_SESSION['usuario']['id'];
    $foto_perfil = null;
    $update_foto = false;

    // Procesar la foto si se subió una nueva
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $foto_temp = $_FILES['foto_perfil']['tmp_name'];
        $foto_tipo = $_FILES['foto_perfil']['type'];
        
        if (strpos($foto_tipo, 'image/') === 0) {
            $foto_perfil = file_get_contents($foto_temp);
            $update_foto = true;
        }
    }

    try {
        if ($update_foto) {
            $stmt = $pdo->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id_usuario = ?");
            $stmt->execute([$foto_perfil, $id_usuario]);
        }

        // Actualizar la sesión si es necesario
        $_SESSION['mensaje'] = "Perfil actualizado correctamente";
        
        header('Location: perfil_cliente.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error al actualizar el perfil: " . $e->getMessage();
        header('Location: perfil_cliente.php');
        exit();
    }
}

header('Location: perfil_cliente.php');
exit();
?>