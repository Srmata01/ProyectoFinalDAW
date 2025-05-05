<?php
require_once 'config/database.php';

$busqueda = $_GET['q'] ?? '';

$sql = "SELECT nombre FROM servicios WHERE nombre LIKE :busqueda LIMIT 5";
$stmt = $pdo->prepare($sql);
$stmt->execute([':busqueda' => "%$busqueda%"]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $result) {
    echo "<div>" . htmlspecialchars($result['nombre']) . "</div>";
}
?>
