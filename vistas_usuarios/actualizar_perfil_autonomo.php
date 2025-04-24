<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] != 3) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_usuario = $_SESSION['usuario']['id'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    $dni = $_POST['DNI'];  // Cambiado de CIF a DNI
    $update_foto = false;
    $foto_perfil = null;

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
        // Preparar la consulta base
        $sql = "UPDATE usuarios SET 
                nombre = ?, 
                apellido = ?, 
                email = ?, 
                telefono = ?, 
                direccion = ?, 
                DNI = ?";
        $params = [$nombre, $apellido, $email, $telefono, $direccion, $dni];

        // Añadir la foto si se subió una nueva 
        if ($update_foto) {
            $sql .= ", foto_perfil = ?";
            $params[] = $foto_perfil;
        }

        $sql .= " WHERE id_usuario = ?";
        $params[] = $id_usuario;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Actualizar la sesión
        $_SESSION['usuario']['nombre'] = $nombre;
        $_SESSION['usuario']['apellido'] = $apellido;
        $_SESSION['usuario']['email'] = $email;
        $_SESSION['usuario']['telefono'] = $telefono;
        $_SESSION['usuario']['direccion'] = $direccion;

        $_SESSION['mensaje'] = "Perfil actualizado correctamente";
        
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error al actualizar el perfil: " . $e->getMessage();
    }
}

header('Location: perfil_autonomo.php');
exit();
?>