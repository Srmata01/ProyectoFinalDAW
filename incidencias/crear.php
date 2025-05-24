<?php
session_start();
require_once '../config/database.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['id'])) {
    // Si no ha iniciado sesión, redirigir al login
    header('Location: ../login.php');
    exit();
}

// Variables para mensajes
$mensaje = '';
$tipo_mensaje = '';

// Obtener la lista de autónomos activos
$stmt_autonomos = $pdo->prepare("
    SELECT u.id_usuario, CONCAT(u.nombre, ' ', u.apellido) as nombre_completo, u.email
    FROM usuarios u
    JOIN estados_usuarios eu ON u.id_estado_usuario = eu.id_estado_usuario
    JOIN tipos_usuarios tu ON u.id_tipo_usuario = tu.id_tipo_usuario
    WHERE tu.id_tipo_usuario = 3 AND eu.estado = 'activo'
    ORDER BY u.nombre ASC
");
$stmt_autonomos->execute();
$autonomos = $stmt_autonomos->fetchAll(PDO::FETCH_ASSOC);

// Obtener la lista de servicios activos
$stmt_servicios = $pdo->prepare("
    SELECT s.id_servicio, s.nombre, s.localidad, u.nombre as nombre_autonomo, u.apellido as apellido_autonomo
    FROM servicios s
    JOIN usuarios u ON s.id_autonomo = u.id_usuario
    WHERE s.estado = 'activo'
    ORDER BY s.nombre ASC
");
$stmt_servicios->execute();
$servicios = $stmt_servicios->fetchAll(PDO::FETCH_ASSOC);

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar campos obligatorios
    if (empty($_POST['titulo_incidencia']) || empty($_POST['cuerpo_incidencia'])) {
        $mensaje = 'Por favor, complete todos los campos obligatorios.';
        $tipo_mensaje = 'error';
    } else {
        try {
            // Preparar datos para insertar
            $persona_incidencia = $_SESSION['usuario']['nombre'] . ' ' . $_SESSION['usuario']['apellido'];
            $mail_contacto = $_SESSION['usuario']['email'] ?? '';
            $titulo_incidencia = $_POST['titulo_incidencia'];
            $cuerpo_incidencia = $_POST['cuerpo_incidencia'];            // Construir detalles adicionales
            $detalles = '';
            if (!empty($_POST['tipo_incidencia'])) {
                if ($_POST['tipo_incidencia'] === 'autonomo' && !empty($_POST['id_autonomo'])) {
                    $id_autonomo = $_POST['id_autonomo'];
                    // Buscar detalles del autónomo
                    $stmt = $pdo->prepare("SELECT CONCAT(nombre, ' ', apellido) as nombre_completo FROM usuarios WHERE id_usuario = ?");
                    $stmt->execute([$id_autonomo]);
                    $autonomo = $stmt->fetch();
                    if ($autonomo) {
                        $detalles .= "Incidencia sobre Autónomo: {$autonomo['nombre_completo']}";
                    }
                } elseif ($_POST['tipo_incidencia'] === 'servicio' && !empty($_POST['id_servicio'])) {
                    $id_servicio = $_POST['id_servicio'];
                    // Buscar detalles del servicio
                    $stmt = $pdo->prepare("
                        SELECT s.nombre, CONCAT(u.nombre, ' ', u.apellido) as autonomo 
                        FROM servicios s
                        JOIN usuarios u ON s.id_autonomo = u.id_usuario
                        WHERE s.id_servicio = ?
                    ");
                    $stmt->execute([$id_servicio]);
                    $servicio = $stmt->fetch();
                    if ($servicio) {
                        $detalles .= "Incidencia sobre Servicio: {$servicio['nombre']} de {$servicio['autonomo']}";
                    }
                }            }            // Añadir los detalles al cuerpo de la incidencia
            if (!empty($detalles)) {
                $cuerpo_incidencia = $detalles . "\n\n" . $cuerpo_incidencia;
            }

            // Procesar imagen si se ha subido
            $imagen_incidencia = null;
            if (isset($_FILES['imagen_incidencia']) && $_FILES['imagen_incidencia']['error'] === UPLOAD_ERR_OK) {
                $imagen_incidencia = file_get_contents($_FILES['imagen_incidencia']['tmp_name']);
            }            // Insertar la incidencia en la base de datos
            $stmt = $pdo->prepare("
                INSERT INTO incidencias (persona_incidencia, mail_contacto, titulo_incidencia, cuerpo_incidencia, imagen_incidencia)
                VALUES (?, ?, ?, ?, ?)
            ");            $stmt->execute([$persona_incidencia, $mail_contacto, $titulo_incidencia, $cuerpo_incidencia, $imagen_incidencia]);            // Redirigir al index.php después de registrar la incidencia
            header('Location: ../index.php?mensaje=incidencia_registrada');
            exit();
        } catch (PDOException $e) {
            $mensaje = 'Error al registrar la incidencia: ' . $e->getMessage();
            $tipo_mensaje = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportar Incidencia - FixItNow</title>
    <link rel="stylesheet" href="../main.css">
    <link rel="stylesheet" href="../includes/responsive-header.css">
    <link rel="stylesheet" href="../includes/footer.css">
    <link rel="stylesheet" href="../includes/compact-forms.css">
</head>

<body>
    <header>
        <div class="header-container">
            <div class="logo-container">
                <a href="../index.php">
                    <img src="../media/logo.png" alt="Logo FixItNow" class="logo">
                </a>
            </div>
            <div class="login-profile-box">
                <?php include '../includes/profile_header.php'; ?>
            </div>
        </div>
    </header>

    <div class="incidencias-container">
        <h1 class="incidencias-title">Reportar Incidencia</h1>

        <?php if (!empty($mensaje)): ?>
            <div class="mensaje-box mensaje-<?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <form class="incidencias-form" action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>¿Sobre qué quieres reportar la incidencia?</label>
                <div class="form-radio-group">
                    <label class="form-radio-label">
                        <input type="radio" name="tipo_incidencia" value="autonomo"> Autónomo
                    </label>
                    <label class="form-radio-label">
                        <input type="radio" name="tipo_incidencia" value="servicio"> Servicio
                    </label>
                    <label class="form-radio-label">
                        <input type="radio" name="tipo_incidencia" value="otro" checked> Otro
                    </label>
                </div>

                <div id="autonomo-select" class="select-container">
                    <label for="id_autonomo">Seleccione un autónomo:</label>
                    <select id="id_autonomo" name="id_autonomo">
                        <option value="">Seleccione un autónomo</option>
                        <?php foreach ($autonomos as $autonomo): ?>
                            <option value="<?php echo htmlspecialchars($autonomo['id_usuario']); ?>">
                                <?php echo htmlspecialchars($autonomo['nombre_completo']); ?> (<?php echo htmlspecialchars($autonomo['email']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="servicio-select" class="select-container">
                    <label for="id_servicio">Seleccione un servicio:</label>
                    <select id="id_servicio" name="id_servicio">
                        <option value="">Seleccione un servicio</option>
                        <?php foreach ($servicios as $servicio): ?>
                            <option value="<?php echo htmlspecialchars($servicio['id_servicio']); ?>">
                                <?php echo htmlspecialchars($servicio['nombre']); ?> en <?php echo htmlspecialchars($servicio['localidad']); ?>
                                (<?php echo htmlspecialchars($servicio['nombre_autonomo'] . ' ' . $servicio['apellido_autonomo']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="titulo_incidencia">Título de la incidencia: *</label>
                <input type="text" id="titulo_incidencia" name="titulo_incidencia" required>
            </div>

            <div class="form-group">
                <label for="cuerpo_incidencia">Descripción de la incidencia: *</label>
                <textarea id="cuerpo_incidencia" name="cuerpo_incidencia" required></textarea>
            </div>

            <div class="form-group">
                <label for="imagen_incidencia">Adjuntar imagen (opcional):</label>
                <input type="file" id="imagen_incidencia" name="imagen_incidencia" accept="image/*">
            </div>

            <button type="submit" class="form-submit">Enviar Incidencia</button>
        </form>    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Script para mostrar/ocultar los selectores según la opción elegida
        document.addEventListener('DOMContentLoaded', function() {
            const tipoRadios = document.querySelectorAll('input[name="tipo_incidencia"]');
            const autonomoSelect = document.getElementById('autonomo-select');
            const servicioSelect = document.getElementById('servicio-select');

            function actualizarVisibilidad() {
                const seleccionado = document.querySelector('input[name="tipo_incidencia"]:checked').value;

                autonomoSelect.style.display = (seleccionado === 'autonomo') ? 'block' : 'none';
                servicioSelect.style.display = (seleccionado === 'servicio') ? 'block' : 'none';

                // Reset values when changing selection
                if (seleccionado !== 'autonomo') {
                    document.getElementById('id_autonomo').value = '';
                }
                if (seleccionado !== 'servicio') {
                    document.getElementById('id_servicio').value = '';
                }
            }

            tipoRadios.forEach(radio => {
                radio.addEventListener('change', actualizarVisibilidad);
            });

            // Inicializar estado
            actualizarVisibilidad();
        });
    </script>
</body>

</html>