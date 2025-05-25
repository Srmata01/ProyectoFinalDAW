<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] != 3) {
    header('Location: ../login.php');
    exit();
}

$id_autonomo = $_SESSION['usuario']['id'];
$mensaje = $error = '';

// Procesar el formulario para añadir/actualizar horarios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['accion'])) {
            // Añadir un nuevo horario
            if ($_POST['accion'] === 'agregar') {
                $stmt = $pdo->prepare("
                    INSERT INTO horarios_autonomo (id_autonomo, dia_semana, hora_inicio, hora_fin, activo) 
                    VALUES (?, ?, ?, ?, 1)
                ");
                $stmt->execute([
                    $id_autonomo,
                    $_POST['dia_semana'],
                    $_POST['hora_inicio'],
                    $_POST['hora_fin']
                ]);
                $mensaje = "Horario añadido correctamente.";
            } 
            // Eliminar un horario existente
            elseif ($_POST['accion'] === 'eliminar' && isset($_POST['id_horario'])) {
                $stmt = $pdo->prepare("
                    DELETE FROM horarios_autonomo 
                    WHERE id_horario = ? AND id_autonomo = ?
                ");
                $stmt->execute([$_POST['id_horario'], $id_autonomo]);
                $mensaje = "Horario eliminado correctamente.";
            }
            // Actualizar disponibilidad de un horario
            elseif ($_POST['accion'] === 'actualizar' && isset($_POST['id_horario'])) {
                $activo = isset($_POST['activo']) ? 1 : 0;
                $stmt = $pdo->prepare("
                    UPDATE horarios_autonomo 
                    SET activo = ? 
                    WHERE id_horario = ? AND id_autonomo = ?
                ");
                $stmt->execute([$activo, $_POST['id_horario'], $id_autonomo]);
                $mensaje = "Disponibilidad actualizada correctamente.";
            }
        }
    } catch (PDOException $e) {
        $error = "Error al procesar la solicitud: " . $e->getMessage();
    }
}

// Obtener los horarios actuales del autónomo
try {
    $stmt = $pdo->prepare("
        SELECT * FROM horarios_autonomo 
        WHERE id_autonomo = ? 
        ORDER BY dia_semana, hora_inicio ASC
    ");
    $stmt->execute([$id_autonomo]);
    $horarios = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error al obtener los horarios: " . $e->getMessage();
}

// Array para mostrar los días en español
$dias_semana = [
    1 => 'Lunes',
    2 => 'Martes',
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado',
    7 => 'Domingo'
];
?>

<!DOCTYPE html>
<html lang="es">
<head>    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Horarios - FixItNow</title>
    <link rel="stylesheet" href="../includes/responsive-header.css">
    <link rel="stylesheet" href="../includes/compact-forms.css">
    <link rel="stylesheet" href="../vistas_usuarios/vistas.css">
    <link rel="stylesheet" href="../includes/footer.css">
    <script src="../services/js/buscador.js" defer></script></head>
<body>    <?php
    $base_path = '../';
    include '../includes/header_template.php';
    ?>

    <div class="container1">
        <div class="profile-columns-container">
            <div class="profile-column">
                <h2 class="document-title">Gestión de Horarios</h2>
                
                <?php if ($mensaje): ?>
                    <div class="success-message"><?= htmlspecialchars($mensaje) ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="error-message"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <!-- Formulario para añadir nuevo horario -->
                <div class="form-container">
                    <h3>Añadir nuevo horario</h3>
                    <form method="post" class="form-grid">
                        <input type="hidden" name="accion" value="agregar">
                        
                        <div class="form-row">
                            <label>
                                <span>Día de la semana:</span>
                                <select name="dia_semana" required>
                                    <?php foreach ($dias_semana as $valor => $etiqueta): ?>
                                        <option value="<?= $valor ?>"><?= $etiqueta ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </div>
                        
                        <div class="form-row">
                            <div class="time-group">
                                <label>
                                    <span>Hora de inicio:</span>
                                    <input type="time" name="hora_inicio" required>
                                </label>
                                
                                <label>
                                    <span>Hora de fin:</span>
                                    <input type="time" name="hora_fin" required>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="submit-btn">Añadir Horario</button>
                        </div>
                    </form>
                </div>
                
                <!-- Lista de horarios actuales -->
                <div class="form-container">
                    <h3>Mis Horarios</h3>
                    
                    <?php if (!empty($horarios)): ?>
                        <div class="horarios-grid">
                            <?php foreach ($horarios as $horario): ?>
                                <div class="horario-card">
                                    <div class="horario-info">
                                        <strong><?= $dias_semana[$horario['dia_semana']] ?? 'Día '.$horario['dia_semana'] ?>:</strong>
                                        <?= substr($horario['hora_inicio'], 0, 5) ?> - <?= substr($horario['hora_fin'], 0, 5) ?>
                                    </div>
                                    <div class="horario-acciones">                        <form method="post" class="horario-form">
                                            <input type="hidden" name="accion" value="actualizar">
                                            <input type="hidden" name="id_horario" value="<?= $horario['id_horario'] ?>">
                                            
                                            <label class="switch">
                                                <input type="checkbox" name="activo" <?= $horario['activo'] ? 'checked' : '' ?> onchange="this.form.submit()">
                                                <span class="slider"></span>
                                            </label>
                                        </form>
                                        
                                        <form method="post" class="horario-form" onsubmit="return confirm('¿Estás seguro de eliminar este horario?');">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="id_horario" value="<?= $horario['id_horario'] ?>">
                                            <button type="submit" class="submit-btn btn-danger">Eliminar</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="document-text">No tienes horarios configurados. Añade tus horarios disponibles para recibir reservas.</p>
                    <?php endif; ?>
                </div>
                  <div class="form-actions">
                    <a href="../vistas_usuarios/perfil_autonomo.php" class="submit-btn btn-secondary">Volver a mi perfil</a>
                </div>
            </div>        </div>
    </div>

    <?php 
    // Definir la ruta base para el footer
    $base_path = '../';
    include '../includes/footer.php'; 
    ?>
</body>
</html>