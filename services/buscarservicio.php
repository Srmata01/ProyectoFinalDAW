    <?php
    session_start();
    require_once __DIR__ . '/../config/database.php';

    $busqueda = $_GET['q'] ?? '';
    $localidad = $_GET['localidad'] ?? '';
    $precio = $_GET['precio'] ?? '';
    $duracion = $_GET['duracion'] ?? '';
    $orden = $_GET['orden'] ?? '';

    $sql = "SELECT s.id_servicio, s.nombre, s.descripcion, s.precio, s.duracion, s.localidad, u.nombre AS nombre_autonomo, u.apellido AS apellido_autonomo, u.foto_perfil AS imagen_autonomo
            FROM servicios s
            JOIN usuarios u ON s.id_autonomo = u.id_usuario
            JOIN estados_usuarios eu ON u.id_estado_usuario = eu.id_estado_usuario
            WHERE s.estado = 'activo' AND eu.estado = 'Activo'"; // Solo servicios activos de usuarios activos

    $params = [];

    // Añadir condiciones si hay parámetros
    if (!empty($busqueda)) {
        // Buscar en nombre, descripción del servicio, localidad o profesión del autónomo
        $sql .= " AND (s.nombre LIKE :busqueda OR s.descripcion LIKE :busqueda OR s.localidad LIKE :busqueda)";
        $params[':busqueda'] = "%$busqueda%";
    }

    // Si la localidad está presente como parámetro específico, usarla para filtrar
    // Esto permite buscar por palabra clave general y también por localidad
    if (!empty($localidad)) {
        $sql .= " AND s.localidad LIKE :localidad";
        $params[':localidad'] = "%$localidad%";
    }

    if (!empty($precio)) {
        $sql .= " AND s.precio <= :precio";
        $params[':precio'] = $precio;
    }

    if (!empty($duracion)) {
        $sql .= " AND s.duracion <= :duracion";
        $params[':duracion'] = $duracion;
    }

    // Determinar orden
    if ($orden == 'asc') {
        $sql .= " ORDER BY s.precio ASC";
    } elseif ($orden == 'desc') {
        $sql .= " ORDER BY s.precio DESC";
    } else {
        $sql .= " ORDER BY s.id_servicio DESC"; // Por defecto, los más recientes primero
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <!DOCTYPE html>
    <html lang="es">    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Buscar Servicios - FixItNow</title>
        <link rel="stylesheet" href="../styles.css">
        <link rel="stylesheet" href="../includes/responsive-header.css">
        <link rel="stylesheet" href="../includes/footer.css">
        <link rel="icon" type="image/png" href="../media/logo.png">
        <script src="../services/js/buscador.js" defer></script>
    </head>

    <body class="app">
    <?php 
        $base_path = '../';
        include '../includes/header_template.php'; 
    ?>

        <main class="app-main">
            <h1 class="resultados-titulo">
                <?php if (!empty($busqueda)): ?>
                    Resultados de búsqueda para: "<?php echo htmlspecialchars($busqueda); ?>"
                <?php else: ?>
                    Servicios disponibles
                <?php endif; ?>
            </h1>            <!-- Filtros de búsqueda -->
            <div class="filtros-container">
                <form action="buscarservicio.php" method="GET" id="filtros-form">
                    <!-- Mantener el parámetro de búsqueda actual -->
                    <input type="hidden" name="q" value="<?php echo htmlspecialchars($busqueda); ?>">                    
                    <select name="localidad" id="filtro_localidad" onchange="this.form.submit()">
                        <option value="">Todas las localidades</option>
                        <?php
                        // Obtener todas las localidades disponibles en la base de datos
                        $stmt_loc = $pdo->query("SELECT DISTINCT localidad FROM servicios ORDER BY localidad");
                        $localidades = $stmt_loc->fetchAll(PDO::FETCH_COLUMN);
                        
                        foreach ($localidades as $loc) {
                            $selected = ($localidad == $loc) ? 'selected' : '';
                            echo "<option value=\"" . htmlspecialchars($loc) . "\" $selected>" . htmlspecialchars($loc) . "</option>";
                        }
                        ?>
                    </select>

                    <select name="precio" id="filtro_precio" onchange="this.form.submit()">
                        <option value="">Cualquier precio</option>
                        <option value="50" <?php echo ($precio == '50') ? 'selected' : ''; ?>>Hasta 50€</option>
                        <option value="100" <?php echo ($precio == '100') ? 'selected' : ''; ?>>Hasta 100€</option>
                        <option value="200" <?php echo ($precio == '200') ? 'selected' : ''; ?>>Hasta 200€</option>
                    </select>

                    <select name="duracion" id="filtro_duracion" onchange="this.form.submit()">
                        <option value="">Cualquier duración</option>
                        <option value="30" <?php echo ($duracion == '30') ? 'selected' : ''; ?>>Hasta 30 min</option>
                        <option value="60" <?php echo ($duracion == '60') ? 'selected' : ''; ?>>Hasta 60 min</option>
                        <option value="120" <?php echo ($duracion == '120') ? 'selected' : ''; ?>>Hasta 120 min</option>
                    </select>
                </form>

                <div class="orden-container">
                    <a href="?q=<?php echo urlencode($busqueda); ?>&localidad=<?php echo urlencode($localidad); ?>&precio=<?php echo urlencode($precio); ?>&duracion=<?php echo urlencode($duracion); ?>&orden=asc" class="btn-orden <?php echo ($orden == 'asc') ? 'active' : ''; ?>">
                        Menor precio
                    </a>                    <a href="?q=<?php echo urlencode($busqueda); ?>&localidad=<?php echo urlencode($localidad); ?>&precio=<?php echo urlencode($precio); ?>&duracion=<?php echo urlencode($duracion); ?>&orden=desc" class="btn-orden <?php echo ($orden == 'desc') ? 'active' : ''; ?>">
                        Mayor precio
                    </a>
                    <a href="buscarservicio.php" class="btn-limpiar">Limpiar filtros</a>
                </div>
            </div>

            <?php if (!empty($servicios)): ?>
                <div class="servicios-grid">
                    <?php foreach ($servicios as $servicio): ?>
                        <a href="ver_servicio.php?id=<?php echo htmlspecialchars($servicio['id_servicio']); ?>" class="servicio-card-link">
                            <div class="servicio-card">
                                <div class="servicio-header">
                                    <h3 class="servicio-titulo"><?php echo htmlspecialchars($servicio['nombre']); ?></h3>
                                    <div class="servicio-precio"><?php echo number_format($servicio['precio'], 2); ?>€</div>
                                </div>

                                <p class="servicio-descripcion"><?php echo htmlspecialchars(substr($servicio['descripcion'], 0, 120)); ?>...</p>

                                <div class="servicio-footer">
                                    <div class="servicio-localidad">
                                        <span>📍</span> <?php echo htmlspecialchars($servicio['localidad']); ?>
                                    </div>
                                    <div class="servicio-duracion">
                                        <span>⏱️</span> <?php echo htmlspecialchars($servicio['duracion']); ?> min
                                    </div>
                                </div>

                                <div class="servicio-autonomo">
                                    <?php if (!empty($servicio['imagen_autonomo'])): ?>
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($servicio['imagen_autonomo']); ?>" alt="Foto de perfil" class="autonomo-img">
                                    <?php else: ?>
                                        <img src="../media/autonomo.jpg" alt="Foto de perfil por defecto" class="autonomo-img">
                                    <?php endif; ?>

                                    <div class="autonomo-info">
                                        <span class="autonomo-nombre"><?php echo htmlspecialchars($servicio['nombre_autonomo'] . ' ' . $servicio['apellido_autonomo']); ?></span>
                                        <span class="autonomo-tipo">Profesional</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-resultados">
                    <p>No se encontraron servicios que coincidan con tu búsqueda.</p>
                    <p>Intenta con otros términos o filtros de búsqueda.</p>                </div>            <?php endif; ?>        </main>

        <?php 
        // Definir la ruta base para el footer
        $base_path = '../';
        include '../includes/footer.php'; 
        ?>
    </body>
</html>