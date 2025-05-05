<?php
require_once 'config/database.php';

$busqueda = $_GET['q'] ?? '';
$localidad = $_GET['localidad'] ?? '';
$precio = $_GET['precio'] ?? '';
$duracion = $_GET['duracion'] ?? '';

$sql = "SELECT nombre FROM servicios WHERE nombre LIKE :busqueda";

$params = [':busqueda' => "%$busqueda%"];

if ($localidad) {
    $sql .= " AND localidad LIKE :localidad";
    $params[':localidad'] = "%$localidad%";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $result) {
    echo "<div>" . htmlspecialchars($result['nombre']) . "</div>";
}
?>
