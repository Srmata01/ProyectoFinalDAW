<?php
require_once 'database.php';

session_start();

function estaLogueado() {
    return isset($_SESSION['user_id']);
}

function obtenerNombreUsuario($pdo) {
    if (estaLogueado()) {
        $stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id_usuario = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $usuario = $stmt->fetch();
        return $usuario['nombre'] ?? '';
    }
    return '';
}

function esAdmin($pdo) {
    if (estaLogueado()) {
        $stmt = $pdo->prepare("SELECT id_tipo_usuario FROM usuarios WHERE id_usuario = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $usuario = $stmt->fetch();
        return ($usuario['id_tipo_usuario'] == 3); // Asumiendo que 3 es el ID para administradores
    }
    return false;
}
?>