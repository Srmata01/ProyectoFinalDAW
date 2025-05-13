<?php
// Incluimos la configuración de la base de datos
require_once '../../config/database.php';

// Obtener parámetros de búsqueda
$busqueda = isset($_GET['q']) ? $_GET['q'] : '';
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'general';

// Si no hay término de búsqueda, no mostramos resultados
if (empty($busqueda)) {
    echo '<div class="mensaje-busqueda">Introduce términos de búsqueda para encontrar resultados.</div>';
    exit;
}

// Variable para almacenar los resultados
$resultados = array();

// Realizar búsqueda según el tipo
try {
    switch ($tipo) {
        case 'servicios':
            // Búsqueda de servicios
            $sql = "SELECT id_servicio, nombre, precio, localidad, descripcion FROM servicios 
                   WHERE nombre LIKE :busqueda OR descripcion LIKE :busqueda 
                   ORDER BY nombre
                   LIMIT 10";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':busqueda' => "%$busqueda%"]);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        case 'usuarios':
            // Búsqueda de usuarios
            $sql = "SELECT id_usuario, nombre, apellido, tipo FROM usuarios 
                   WHERE nombre LIKE :busqueda OR apellido LIKE :busqueda 
                   ORDER BY nombre
                   LIMIT 10";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':busqueda' => "%$busqueda%"]);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        case 'autonomos':
            // Búsqueda específica de autónomos
            $sql = "SELECT u.id_usuario, u.nombre, u.apellido 
                   FROM usuarios u
                   WHERE (u.nombre LIKE :busqueda OR u.apellido LIKE :busqueda) AND u.tipo = 3
                   ORDER BY u.nombre
                   LIMIT 10";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':busqueda' => "%$busqueda%"]);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        case 'general':
        default:
            // Búsqueda general combinada
            // Primero servicios
            $sql = "SELECT id_servicio, nombre, 'servicio' as tipo FROM servicios 
                   WHERE nombre LIKE :busqueda
                   ORDER BY nombre
                   LIMIT 5";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':busqueda' => "%$busqueda%"]);
            $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Luego usuarios (autónomos)
            $sql = "SELECT id_usuario, CONCAT(nombre, ' ', apellido) as nombre, 'autonomo' as tipo 
                   FROM usuarios 
                   WHERE (nombre LIKE :busqueda OR apellido LIKE :busqueda) AND tipo = 3
                   ORDER BY nombre
                   LIMIT 5";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':busqueda' => "%$busqueda%"]);
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Combinar resultados
            $resultados = array_merge($servicios, $usuarios);
            break;
    }
    
    // Si no hay resultados, mostrar mensaje
    if (empty($resultados)) {
        echo '<div class="mensaje-busqueda">No se encontraron resultados para "<strong>' . htmlspecialchars($busqueda) . '</strong>".</div>';
        exit;
    }
    
    // Generar HTML para los resultados según el tipo
    echo '<div class="resultados-lista">';
    
    if ($tipo === 'general') {
        // Para búsquedas generales, agrupamos por tipo
        $porTipo = [];
        foreach ($resultados as $item) {
            $porTipo[$item['tipo']][] = $item;
        }
        
        // Mostrar servicios primero si existen
        if (isset($porTipo['servicio'])) {
            echo '<div class="seccion-resultados">';
            echo '<h3 class="titulo-seccion">Servicios</h3>';
            foreach ($porTipo['servicio'] as $servicio) {
                echo '<div class="item-resultado">';
                echo '<a href="/services/ver_servicio.php?id=' . $servicio['id_servicio'] . '">';
                echo htmlspecialchars($servicio['nombre']);
                echo '</a>';
                echo '</div>';
            }
            echo '</div>';
        }
        
        // Luego mostrar autónomos si existen
        if (isset($porTipo['autonomo'])) {
            echo '<div class="seccion-resultados">';
            echo '<h3 class="titulo-seccion">Profesionales</h3>';
            foreach ($porTipo['autonomo'] as $autonomo) {
                echo '<div class="item-resultado">';
                echo '<a href="/vistas_usuarios/ver_autonomo.php?id=' . $autonomo['id_usuario'] . '">';
                echo htmlspecialchars($autonomo['nombre']);
                echo '</a>';
                echo '</div>';
            }
            echo '</div>';
        }
    } else if ($tipo === 'servicios') {
        // Mostrar resultados de servicios
        foreach ($resultados as $servicio) {
            echo '<div class="item-resultado">';
            echo '<a href="/services/ver_servicio.php?id=' . $servicio['id_servicio'] . '">';
            echo '<div class="resultado-nombre">' . htmlspecialchars($servicio['nombre']) . '</div>';
            echo '<div class="resultado-info">';
            echo '<span class="precio">€' . htmlspecialchars($servicio['precio']) . '</span>';
            echo '<span class="localidad">' . htmlspecialchars($servicio['localidad']) . '</span>';
            echo '</div>';
            echo '</a>';
            echo '</div>';
        }
    } else {
        // Mostrar resultados de usuarios/autónomos
        foreach ($resultados as $usuario) {
            echo '<div class="item-resultado">';
            echo '<a href="/vistas_usuarios/ver_autonomo.php?id=' . $usuario['id_usuario'] . '">';
            echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']);
            echo '</a>';
            echo '</div>';
        }
    }
    
    echo '</div>';
    
} catch (PDOException $e) {
    // En caso de error, mostrar mensaje
    error_log("Error en la búsqueda: " . $e->getMessage());
    echo '<div class="error-message">Ha ocurrido un error al procesar tu búsqueda.</div>';
}
?>
