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
<head>
    <meta charset="UTF-8">
    <title>Gestión de Horarios - FixItNow</title>
    <link rel="stylesheet" href="../vistas_usuarios/vistas.css">
    <style>
        .horarios-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
            margin-top: 20px;
        }
        .horario-card {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .horario-info {
            flex: 1;
        }
        .horario-acciones {
            display: flex;
            gap: 10px;
        }
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #FF9B00;
        }
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        .time-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo-container">
                <a href="../index.php">
                    <img src="../media/logo.png" alt="Logo FixItNow" class="logo">
                </a>
            </div>
            <div class="user-container">
                <?php include '../includes/profile_header.php'; ?>
            </div>
        </div>
    </header>

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
                                    <div class="horario-acciones">
                                        <form method="post" style="display: inline-block;">
                                            <input type="hidden" name="accion" value="actualizar">
                                            <input type="hidden" name="id_horario" value="<?= $horario['id_horario'] ?>">
                                            
                                            <label class="switch">
                                                <input type="checkbox" name="activo" <?= $horario['activo'] ? 'checked' : '' ?> onchange="this.form.submit()">
                                                <span class="slider"></span>
                                            </label>
                                        </form>
                                        
                                        <form method="post" style="display: inline-block;" onsubmit="return confirm('¿Estás seguro de eliminar este horario?');">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="id_horario" value="<?= $horario['id_horario'] ?>">
                                            <button type="submit" class="submit-btn" style="background-color: #dc3545;">Eliminar</button>
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
                    <a href="../vistas_usuarios/perfil_autonomo.php" class="submit-btn" style="background-color: #6c757d;">Volver a mi perfil</a>
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