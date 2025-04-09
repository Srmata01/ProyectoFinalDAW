<?php
require_once '../config/session.php';

function iniciarSesion($pdo, $email, $password) {
    $stmt = $pdo->prepare("SELECT id_usuario, nombre, contraseña FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($password, $usuario['contraseña'])) {
        $_SESSION['user_id'] = $usuario['id_usuario'];
        $_SESSION['user_name'] = $usuario['nombre'];
        return true;
    }
    return false;
}

function registrarUsuario($pdo, $datosUsuario) {
    // Validar datos primero
    $hashedPassword = password_hash($datosUsuario['contraseña'], PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, contraseña, telefono, direccion, id_tipo_usuario) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    return $stmt->execute([
        $datosUsuario['nombre'],
        $datosUsuario['apellido'],
        $datosUsuario['email'],
        $hashedPassword,
        $datosUsuario['telefono'],
        $datosUsuario['direccion'],
        $datosUsuario['tipo_usuario']
    ]);
}
?>