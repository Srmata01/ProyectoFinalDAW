<?php
session_start();
require_once '../config/database.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['id'])) {
    // Si no ha iniciado sesión, redirigir al login
    header('Location: ../login.php');
    exit();
}

// Determinar la ruta base según el directorio
$base_path = '../';

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
            $cuerpo_incidencia = $_POST['cuerpo_incidencia'];

            // Construir detalles adicionales
            $detalles = '';
            if (!empty($_POST['tipo_incidencia'])) {
                if ($_POST['tipo_incidencia'] === 'autonomo' && !empty($_POST['id_autonomo'])) {
                    $id_autonomo = $_POST['id_autonomo'];
                    // Buscar detalles del autónomo
                    $stmt = $pdo->prepare("SELECT CONCAT(nombre, ' ', apellido) as nombre_completo FROM usuarios WHERE id_usuario = ?");
                    $stmt->execute([$id_autonomo]);
                    $autonomo = $stmt->fetch();
                    if ($autonomo) {
                        $detalles .= "Incidencia sobre Autónomo: {$autonomo['nombre_completo']} (ID: {$id_autonomo})\n";
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
                        $detalles .= "Incidencia sobre Servicio: {$servicio['nombre']} de {$servicio['autonomo']} (ID: {$id_servicio})\n";
                    }
                }
            }

            // Añadir los detalles al cuerpo de la incidencia
            if (!empty($detalles)) {
                $cuerpo_incidencia = $detalles . "\n\n" . $cuerpo_incidencia;
            }

            // Procesar imagen si se ha subido
            $imagen_incidencia = null;
            if (isset($_FILES['imagen_incidencia']) && $_FILES['imagen_incidencia']['error'] === UPLOAD_ERR_OK) {
                $imagen_incidencia = file_get_contents($_FILES['imagen_incidencia']['tmp_name']);
            }

            // Insertar la incidencia en la base de datos
            $stmt = $pdo->prepare("
                INSERT INTO incidencias (persona_incidencia, mail_contacto, titulo_incidencia, cuerpo_incidencia, imagen_incidencia)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$persona_incidencia, $mail_contacto, $titulo_incidencia, $cuerpo_incidencia, $imagen_incidencia]);

            // Redirigir al main.php después de registrar la incidencia
            header('Location: ../main.php?mensaje=incidencia_registrada');
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
    <style>
    body {
        background: linear-gradient(-45deg, rgba(255, 180, 110, 0.7), rgba(255, 220, 150, 0.7), rgba(255, 148, 91, 0.7), rgba(255, 255, 255, 0.7));
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
    }

    .incidencias-container {
        max-width: 800px;
        margin: 150px auto 30px;
        padding: 30px;
        background-color: #fff;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        text-align: center;
    }

    .incidencias-title {
        text-align: center;
        margin-bottom: 30px;
        color: #333;
        font-size: 28px;
        position: relative;
        padding-bottom: 15px;
    }

    .incidencias-title:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 3px;
        background: linear-gradient(to right, #FF8C42, #FFB347);
    }

    .incidencias-form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
        text-align: left;
        margin-bottom: 15px;
    }

    .form-group label {
        font-weight: 600;
        color: #555;
    }

    .form-radio-group {
        display: flex;
        gap: 25px;
        margin: 20px 0;
        justify-content: center;
    }

    .form-radio-label {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        font-weight: 500;
    }

    input[type="radio"] {
        accent-color: #FF8C42;
    }

    .select-container {
        margin-top: 15px;
        display: none;
        animation: fadeIn 0.3s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .mensaje-box {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
        text-align: center;
    }

    .mensaje-error {
        background-color: #ffdddd;
        color: #ff0000;
    }

    .mensaje-success {
        background-color: #ddffdd;
        color: #009900;
    }

    .form-submit {
        width: 100%;
        padding: 14px;
        background: linear-gradient(to right, #FF8C42, #FFB347);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        margin-top: 15px;
    }

    .form-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 140, 66, 0.4);
    }

    select,
    input[type="text"],
    textarea {
        padding: 12px;
        border: 2px solid #eee;
        border-radius: 8px;
        font-size: 15px;
        width: 100%;
        transition: border 0.3s;
    }

    select:focus,
    input[type="text"]:focus,
    textarea:focus {
        border-color: #FF8C42;
        outline: none;
    }

    textarea {
        min-height: 150px;
        resize: vertical;
    }
</style>
</head>

<body>
    <header>
        <div class="header-container">
            <div class="logo-container">
                <a href="../main.php">
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
        </form>
    </div>

    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h4>Información Personal</h4>
                <ul>
                    <li><a href="../politicaprivacidad.php">Política de privacidad</a></li>
                    <li><a href="../politicacookiesdatos.php">Política de Cookies y protección de datos</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>Contacto</h4>
                <ul>
                    <li><a href="mailto:fixitnow@gmail.com">fixitnow@gmail.com</a></li>
                    <li><a href="tel:+34690096690">+34 690 096 690</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>¿Eres miembro?</h4>
                <ul>
                    <li><a href="../create_users/index.php">Únete a Nosotros</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>¿Tienes algún problema?</h4>
                <ul>
                    <li><a href="../incidencias/crear.php">Reportar incidencia</a></li>
                </ul>
            </div>

            <div class="footer-section social-media">
                <div class="social-icons">
                    <a href="#"><img src="../media/twitter-icon.png" alt="Twitter"></a>
                    <a href="#"><img src="../media/instagram-icon.png" alt="Instagram"></a>
                    <a href="#"><img src="../media/facebook-icon.png" alt="Facebook"></a>
                    <a href="#"><img src="../media/tiktok-icon.png" alt="TikTok"></a>
                </div>
            </div>

            <div class="footer-logo">
                <img src="../media/logo.png" alt="FixItNow Logo">
            </div>
        </div>
    </footer>

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