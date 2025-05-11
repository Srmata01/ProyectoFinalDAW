<?php
// Incluimos la configuración de la base de datos
require_once '../../config/database.php';

// Obtenemos los parámetros de la solicitud
$busqueda = $_POST['q'] ?? '';
$tipo = $_POST['tipo'] ?? 'general';

// Si la búsqueda está vacía, mostrar mensaje
if (empty($busqueda)) {
    echo '<div class="mensaje-busqueda">Introduce términos de búsqueda para encontrar resultados.</div>';
    exit;
}

// Función para generar el HTML de los resultados según el tipo
function generarHTML($resultados, $tipo) {
    $html = '';
    
    if (empty($resultados)) {
        return '<div class="mensaje-busqueda">No se encontraron resultados para tu búsqueda.</div>';
    }
    
    switch ($tipo) {
        case 'servicios':
            $html .= '<div class="resultados-grid">';
            foreach ($resultados as $servicio) {
                $html .= '<div class="servicio-card">';
                if (!empty($servicio['imagen'])) {
                    $html .= '<div class="servicio-img"><img src="data:image/jpeg;base64,'.base64_encode($servicio['imagen']).'" alt="'.$servicio['nombre'].'"></div>';
                }
                $html .= '<div class="servicio-info">';
                $html .= '<h3><a href="/services/ver_servicio.php?id='.$servicio['id_servicio'].'">'.$servicio['nombre'].'</a></h3>';
                $html .= '<p class="precio">€'.$servicio['precio'].'</p>';
                $html .= '<p class="localidad">'.$servicio['localidad'].'</p>';
                $html .= '<p class="descripcion">'.substr($servicio['descripcion'], 0, 100).'...</p>';
                $html .= '</div>';
                $html .= '</div>';
            }
            $html .= '</div>';
            break;
            
        case 'usuarios':
        case 'autonomos':
            $html .= '<div class="usuarios-grid">';
            foreach ($resultados as $usuario) {
                $html .= '<div class="usuario-card">';
                if (!empty($usuario['foto_perfil'])) {
                    $html .= '<div class="usuario-img"><img src="data:image/jpeg;base64,'.base64_encode($usuario['foto_perfil']).'" alt="'.$usuario['nombre'].'"></div>';
                } else {
                    $html .= '<div class="usuario-img usuario-placeholder">'.strtoupper(substr($usuario['nombre'], 0, 1)).'</div>';
                }
                $html .= '<div class="usuario-info">';
                $html .= '<h3><a href="/vistas_usuarios/ver_autonomo.php?id='.$usuario['id_usuario'].'">'.$usuario['nombre'].' '.$usuario['apellido'].'</a></h3>';
                if (isset($usuario['profesion'])) {
                    $html .= '<p class="profesion">'.$usuario['profesion'].'</p>';
                }
                $html .= '</div>';
                $html .= '</div>';
            }
            $html .= '</div>';
            break;
            
        case 'general':
        default:
            // Para búsqueda general, mostramos secciones separadas
            if (isset($resultados['servicios']) && !empty($resultados['servicios'])) {
                $html .= '<h2 class="seccion-titulo">Servicios</h2>';
                $html .= generarHTML($resultados['servicios'], 'servicios');
            }
            
            if (isset($resultados['usuarios']) && !empty($resultados['usuarios'])) {
                $html .= '<h2 class="seccion-titulo">Profesionales</h2>';
                $html .= generarHTML($resultados['usuarios'], 'usuarios');
            }
            
            if (empty($html)) {
                $html = '<div class="mensaje-busqueda">No se encontraron resultados para tu búsqueda.</div>';
            }
            break;
    }
    
    return $html;
}

// Realizar la búsqueda según el tipo
try {
    switch ($tipo) {
        case 'servicios':
            // Definimos parámetros adicionales de filtrado
            $localidad = $_POST['localidad'] ?? '';
            $precio = $_POST['precio'] ?? '';
            $duracion = $_POST['duracion'] ?? '';
            $orden = $_POST['orden'] ?? '';
            
            // Construimos la consulta SQL
            $sql = "SELECT * FROM servicios WHERE nombre LIKE :busqueda";
            $params = [':busqueda' => "%$busqueda%"];
            
            // Añadimos filtros adicionales si están presentes
            if (!empty($localidad)) {
                $sql .= " AND localidad LIKE :localidad";
                $params[':localidad'] = "%$localidad%";
            }
            
            if (!empty($precio)) {
                if ($precio === 'bajo') {
                    $sql .= " AND precio < 50";
                } elseif ($precio === 'medio') {
                    $sql .= " AND precio >= 50 AND precio < 100";
                } elseif ($precio === 'alto') {
                    $sql .= " AND precio >= 100";
                }
            }
            
            if (!empty($duracion)) {
                if ($duracion === 'corta') {
                    $sql .= " AND duracion < 2";
                } elseif ($duracion === 'media') {
                    $sql .= " AND duracion >= 2 AND duracion < 5";
                } elseif ($duracion === 'larga') {
                    $sql .= " AND duracion >= 5";
                }
            }
            
            // Ordenación
            if (!empty($orden)) {
                if ($orden === 'asc') {
                    $sql .= " ORDER BY precio ASC";
                } elseif ($orden === 'desc') {
                    $sql .= " ORDER BY precio DESC";
                } elseif ($orden === 'reciente') {
                    $sql .= " ORDER BY fecha_creacion DESC";
                }
            } else {
                $sql .= " ORDER BY nombre ASC";
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo generarHTML($resultados, $tipo);
            break;
            
        case 'usuarios':
            $sql = "SELECT * FROM usuarios WHERE (nombre LIKE :busqueda OR apellido LIKE :busqueda) ORDER BY nombre ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':busqueda' => "%$busqueda%"]);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo generarHTML($resultados, $tipo);
            break;
            
        case 'autonomos':
            $sql = "SELECT u.*, p.profesion FROM usuarios u
                    LEFT JOIN perfil_autonomo p ON u.id_usuario = p.id_usuario
                    WHERE (u.nombre LIKE :busqueda OR u.apellido LIKE :busqueda) AND u.tipo = 3
                    ORDER BY u.nombre ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':busqueda' => "%$busqueda%"]);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo generarHTML($resultados, $tipo);
            break;
            
        case 'general':
        default:
            // Para búsqueda general, hacemos varias consultas
            $resultadosCombinados = [];
            
            // Buscamos servicios
            $sql = "SELECT * FROM servicios WHERE nombre LIKE :busqueda ORDER BY nombre ASC LIMIT 5";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':busqueda' => "%$busqueda%"]);
            $resultadosCombinados['servicios'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Buscamos profesionales (autónomos)
            $sql = "SELECT u.*, p.profesion FROM usuarios u
                    LEFT JOIN perfil_autonomo p ON u.id_usuario = p.id_usuario
                    WHERE (u.nombre LIKE :busqueda OR u.apellido LIKE :busqueda) AND u.tipo = 3
                    ORDER BY u.nombre ASC LIMIT 5";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':busqueda' => "%$busqueda%"]);
            $resultadosCombinados['usuarios'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo generarHTML($resultadosCombinados, $tipo);
            break;
    }
} catch (PDOException $e) {
    // En caso de error, mostramos un mensaje
    error_log("Error en la búsqueda: " . $e->getMessage());
    echo '<div class="error-message">Ha ocurrido un error al procesar tu búsqueda. Inténtalo de nuevo más tarde.</div>';
}
?>
