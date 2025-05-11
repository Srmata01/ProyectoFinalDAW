<?php
// Incluimos la configuración de la base de datos
require_once '../../config/database.php';

// Obtenemos los parámetros de la solicitud
$busqueda = $_POST['q'] ?? '';
$tipo = $_POST['tipo'] ?? 'general';

// Array para almacenar los resultados
$resultados = [];

// Verificamos que tengamos al menos un carácter para buscar
if (strlen($busqueda) > 0) {
    // Configuración de búsqueda según el tipo
    switch ($tipo) {
        case 'servicios':
            // Búsqueda en servicios
            $sql = "SELECT id_servicio as id, nombre as text FROM servicios 
                   WHERE nombre LIKE :busqueda 
                   ORDER BY nombre ASC 
                   LIMIT 8";
            $params = [':busqueda' => "%$busqueda%"];
            break;
            
        case 'usuarios':
            // Búsqueda en usuarios
            $sql = "SELECT id_usuario as id, CONCAT(nombre, ' ', apellido) as text FROM usuarios 
                   WHERE nombre LIKE :busqueda OR apellido LIKE :busqueda 
                   ORDER BY nombre ASC 
                   LIMIT 8";
            $params = [':busqueda' => "%$busqueda%"];
            break;
            
        case 'autonomos':
            // Búsqueda específica de autónomos
            $sql = "SELECT u.id_usuario as id, CONCAT(u.nombre, ' ', u.apellido) as text 
                   FROM usuarios u
                   WHERE (u.nombre LIKE :busqueda OR u.apellido LIKE :busqueda)
                   AND u.tipo = 3
                   ORDER BY u.nombre ASC 
                   LIMIT 8";
            $params = [':busqueda' => "%$busqueda%"];
            break;
            
        case 'general':
        default:
            // Búsqueda general (combina servicios y usuarios)
            $sql = "(SELECT id_servicio as id, nombre as text, 'servicio' as tipo FROM servicios 
                    WHERE nombre LIKE :busqueda)
                   UNION
                   (SELECT id_usuario as id, CONCAT(nombre, ' ', apellido) as text, 'usuario' as tipo FROM usuarios 
                    WHERE nombre LIKE :busqueda OR apellido LIKE :busqueda)
                   ORDER BY text ASC
                   LIMIT 10";
            $params = [':busqueda' => "%$busqueda%"];
            break;
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // En caso de error, devolver un array vacío
        error_log("Error en autocompletado: " . $e->getMessage());
    }
}

// Devolver resultados en formato JSON
header('Content-Type: application/json');
echo json_encode($resultados);
?>
