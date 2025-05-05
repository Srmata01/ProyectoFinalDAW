<?php
require_once 'config/database.php';

$busqueda = $_GET['q'] ?? '';
$localidad = $_GET['localidad'] ?? '';
$precio = $_GET['precio'] ?? '';
$duracion = $_GET['duracion'] ?? '';
$orden = $_GET['orden'] ?? '';

$sql = "SELECT s.id_servicio, s.nombre, s.descripcion, s.precio, s.duracion, s.localidad, u.nombre AS nombre_autonomo, u.foto_perfil AS imagen_autonomo
        FROM servicios s
        JOIN usuarios u ON s.id_autonomo = u.id_usuario
        WHERE s.nombre LIKE :busqueda";

$params = [':busqueda' => "%$busqueda%"];

if ($localidad) {
    $sql .= " AND s.localidad LIKE :localidad";
    $params[':localidad'] = "%$localidad%";
}
if ($precio) {
    $sql .= " AND s.precio <= :precio";
    $params[':precio'] = $precio;
}
if ($duracion) {
    $sql .= " AND s.duracion <= :duracion";
    $params[':duracion'] = $duracion;
}
if ($orden == 'asc') {
    $sql .= " ORDER BY s.precio ASC";
} elseif ($orden == 'desc') {
    $sql .= " ORDER BY s.precio DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($servicios as $servicio) {
    echo "<div class='servicio-card'>";
    echo "<h3>" . htmlspecialchars($servicio['nombre']) . "</h3>";
    echo "<p>" . htmlspecialchars($servicio['descripcion']) . "</p>";
    echo "<p>Precio: " . htmlspecialchars($servicio['precio']) . "€</p>";
    echo "<p>Duración: " . htmlspecialchars($servicio['duracion']) . " min</p>";
    echo "<p>Localidad: " . htmlspecialchars($servicio['localidad']) . "</p>";
    echo "<p>Autónomo: " . htmlspecialchars($servicio['nombre_autonomo']) . "</p>";
    echo "</div>";
}
?>
