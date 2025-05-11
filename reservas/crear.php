<?php
require_once '../config/database.php';
session_start();

// Verificar que el usuario es un cliente
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] != 2) {
    header('Location: ../login.php');
    exit();
}

$id_cliente = $_SESSION['usuario']['id'];
$id_servicio = isset($_GET['servicio']) ? (int)$_GET['servicio'] : 0;
$mensaje = $error = '';

// Verificar que el servicio existe y obtener sus datos
try {
    $stmt = $pdo->prepare("
        SELECT s.*, 
               u.id_usuario as autonomo_id,
               u.nombre as autonomo_nombre, 
               u.apellido as autonomo_apellido
        FROM servicios s
        JOIN usuarios u ON s.id_autonomo = u.id_usuario
        WHERE s.id_servicio = ? AND s.estado = 'activo'
    ");
    $stmt->execute([$id_servicio]);
    $servicio = $stmt->fetch();

    if (!$servicio) {
        header('Location: ../services/index.php');
        exit();
    }

    $id_autonomo = $servicio['autonomo_id'];

    // Si se ha enviado el formulario, procesar la reserva
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fecha'], $_POST['id_horario'])) {
        $fecha = $_POST['fecha'];
        $id_horario = (int)$_POST['id_horario'];
        $hora_inicio = isset($_POST['hora_inicio']) ? $_POST['hora_inicio'] : null;
        
        // Comprobar que el id_horario corresponde a un horario válido del autónomo
        $stmt = $pdo->prepare("SELECT * FROM horarios_autonomo WHERE id_horario = ? AND id_autonomo = ? AND activo = 1");
        $stmt->execute([$id_horario, $id_autonomo]);
        $horario = $stmt->fetch();
        
        if ($horario) {
            // La fecha y hora de inicio de la reserva
            $fecha_hora_inicio = $fecha . ' ' . $hora_inicio;
            
            // Obtener la duración del servicio para calcular la hora de fin
            $duracion_servicio = $servicio['duracion'];
            
            // Verificar que no hay conflictos con otras reservas
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as conflictos
                FROM reservas r
                JOIN servicios s ON r.id_servicio = s.id_servicio
                WHERE s.id_autonomo = ? 
                AND DATE(r.fecha_hora) = ?
                AND r.estado IN ('pendiente', 'aceptada')
                AND (
                    (r.fecha_hora <= ? AND ADDTIME(r.fecha_hora, SEC_TO_TIME(s.duracion * 60)) > ?)
                    OR (? <= r.fecha_hora AND ? >= ADDTIME(r.fecha_hora, SEC_TO_TIME(s.duracion * 60)))
                    OR (r.fecha_hora <= ? AND ADDTIME(r.fecha_hora, SEC_TO_TIME(s.duracion * 60)) >= ADDTIME(?, SEC_TO_TIME(?*60)))
                )
            ");
            
            // Calcular la hora de fin de la nueva reserva
            $hora_fin_timestamp = strtotime($fecha_hora_inicio) + ($duracion_servicio * 60);
            $hora_fin = date('H:i:s', $hora_fin_timestamp);
            
            $stmt->execute([
                $id_autonomo,
                $fecha,
                $fecha_hora_inicio, $fecha_hora_inicio,
                $fecha_hora_inicio, $hora_fin,
                $fecha_hora_inicio, $fecha_hora_inicio, $duracion_servicio
            ]);
            
            $resultado = $stmt->fetch();
            
            if ($resultado['conflictos'] == 0) {
                // Crear la reserva
                $stmt = $pdo->prepare("
                    INSERT INTO reservas (id_cliente, id_servicio, fecha_hora, estado)
                    VALUES (?, ?, ?, 'pendiente')
                ");
                $stmt->execute([
                    $id_cliente, 
                    $id_servicio, 
                    $fecha_hora_inicio
                ]);
                
                // Redirigir al perfil del cliente
                $_SESSION['mensaje'] = "Reserva creada correctamente. El profesional se pondrá en contacto contigo pronto.";
                header('Location: ../vistas_usuarios/perfil_cliente.php');
                exit();
            } else {
                $error = "El horario seleccionado ya no está disponible. Por favor elige otro horario.";
            }
        } else {
            $error = "El horario seleccionado no es válido o no está disponible.";
        }
    }
} catch (PDOException $e) {
    $error = "Error al procesar la solicitud: " . $e->getMessage();
}

// Array para traducir días de la semana
$dias_semana = [
    1 => 'Lunes',
    2 => 'Martes',
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado',
    7 => 'Domingo'
];

// Obtener los días de la semana en que el autónomo trabaja
try {
    $stmt = $pdo->prepare("
        SELECT DISTINCT dia_semana 
        FROM horarios_autonomo 
        WHERE id_autonomo = ? AND activo = 1
    ");
    $stmt->execute([$id_autonomo]);
    $dias_disponibles = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $error = "Error al obtener los días disponibles: " . $e->getMessage();
    $dias_disponibles = [];
}

// Convertir días disponibles a números para JavaScript (domingo = 0, lunes = 1, etc.)
$dias_js = [];
foreach ($dias_disponibles as $dia) {
    // La tabla horarios_autonomo usa: 1=Lunes, 2=Martes, ... 7=Domingo
    // JavaScript usa: 0=Domingo, 1=Lunes, ... 6=Sábado
    switch ($dia) {
        case 1: $dias_js[] = 1; break; // Lunes
        case 2: $dias_js[] = 2; break; // Martes
        case 3: $dias_js[] = 3; break; // Miércoles
        case 4: $dias_js[] = 4; break; // Jueves
        case 5: $dias_js[] = 5; break; // Viernes
        case 6: $dias_js[] = 6; break; // Sábado
        case 7: $dias_js[] = 0; break; // Domingo
    }
}
$dias_js_json = json_encode($dias_js);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reservar Servicio - FixItNow</title>
    <link rel="stylesheet" href="../vistas_usuarios/vistas.css">
    <style>
        .reserva-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .servicio-info {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .precio-info {
            font-size: 20px;
            color: #FF9B00;
            font-weight: bold;
            margin-top: 10px;
        }
        #calendar {
            margin: 20px 0;
            width: 100%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            text-align: center;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
        .day {
            cursor: pointer;
        }
        .day:hover {
            background-color: #f5f5f5;
        }
        .day.active {
            background-color: #FF9B00;
            color: white;
        }
        .day.disabled {
            color: #ccc;
            cursor: not-allowed;
        }
        .horarios-container {
            margin-top: 20px;
        }
        .horario-option {
            display: inline-block;
            background-color: #f2f2f2;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            margin: 5px;
            cursor: pointer;
        }
        .horario-option:hover {
            background-color: #e9e9e9;
        }
        .horario-option.selected {
            background-color: #FF9B00;
            color: white;
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
            <div class="user-container">
                <?php include '../includes/profile_header.php'; ?>
            </div>
        </div>
    </header>

    <div class="container1">
        <div class="profile-columns-container">
            <div class="profile-column">
                <h2 class="document-title">Reservar Servicio</h2>
                
                <?php if ($error): ?>
                    <div class="error-message"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <div class="reserva-container">
                    <div class="servicio-info">
                        <h3><?= htmlspecialchars($servicio['nombre']) ?></h3>
                        <p><?= htmlspecialchars($servicio['descripcion']) ?></p>
                        <p><strong>Profesional:</strong> <?= htmlspecialchars($servicio['autonomo_nombre'] . ' ' . $servicio['autonomo_apellido']) ?></p>
                        <p><strong>Duración:</strong> <?= htmlspecialchars($servicio['duracion']) ?> minutos</p>
                        <div class="precio-info"><?= number_format($servicio['precio'], 2) ?> €</div>
                    </div>
                    
                    <h3>Selecciona una fecha y horario</h3>
                    
                    <?php if (empty($dias_disponibles)): ?>
                        <p class="error-message">Este profesional no tiene horarios disponibles actualmente. Por favor, intenta más tarde.</p>
                        <div class="form-actions">
                            <a href="../services/ver_servicio.php?id=<?= $id_servicio ?>" class="submit-btn" style="background-color: #6c757d;">Volver</a>
                        </div>
                    <?php else: ?>
                        <div class="form-container">
                            <div id="calendar"></div>
                            <input type="hidden" id="selected-date">
                            
                            <div class="horarios-container" style="display: none;">
                                <h3>Horarios disponibles</h3>
                                <div id="horarios-list"></div>
                            </div>
                            
                            <form method="post" id="reserva-form">
                                <input type="hidden" name="fecha" id="fecha-input">
                                <input type="hidden" name="id_horario" id="horario-input">
                                <input type="hidden" name="hora_inicio" id="hora-inicio">
                                <div class="form-actions">
                                    <button type="submit" class="submit-btn" id="btn-reservar" style="display: none;">Reservar</button>
                                    <a href="../services/ver_servicio.php?id=<?= $id_servicio ?>" class="submit-btn" style="background-color: #6c757d;">Cancelar</a>
                                </div>
                            </form>
                        </div>
                        
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const diasDisponibles = <?= $dias_js_json ?>;
                            const idAutonomo = <?= $id_autonomo ?>;
                            let currentMonth = new Date().getMonth();
                            let currentYear = new Date().getFullYear();
                            
                            // Generar calendario
                            generarCalendario(currentMonth, currentYear);
                            
                            // Función para generar el calendario
                            function generarCalendario(month, year) {
                                const calendarDiv = document.getElementById('calendar');
                                const today = new Date();
                                const firstDay = new Date(year, month, 1);
                                const lastDay = new Date(year, month + 1, 0);
                                
                                const daysInMonth = lastDay.getDate();
                                const startingDay = firstDay.getDay(); // 0 = Domingo, 1 = Lunes, etc.
                                
                                let html = `
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                    <button id="prev-month" class="submit-btn">Anterior</button>
                                    <h3>${nombreMes(month)} ${year}</h3>
                                    <button id="next-month" class="submit-btn">Siguiente</button>
                                </div>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Dom</th>
                                            <th>Lun</th>
                                            <th>Mar</th>
                                            <th>Mié</th>
                                            <th>Jue</th>
                                            <th>Vie</th>
                                            <th>Sáb</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                `;
                                
                                // Filas del calendario
                                let date = 1;
                                for (let i = 0; i < 6; i++) {
                                    if (date > daysInMonth) break;
                                    
                                    html += '<tr>';
                                    
                                    // Celdas para cada día
                                    for (let j = 0; j < 7; j++) {
                                        if (i === 0 && j < startingDay) {
                                            // Celdas vacías antes del primer día
                                            html += '<td></td>';
                                        } else if (date > daysInMonth) {
                                            // Celdas vacías después del último día
                                            html += '<td></td>';
                                        } else {
                                            // Comprobar si este día es seleccionable
                                            const currentDate = new Date(year, month, date);
                                            const dayOfWeek = currentDate.getDay(); // 0 = Domingo, 1 = Lunes, etc.
                                            
                                            const isDisabled = currentDate < today || !diasDisponibles.includes(dayOfWeek);
                                            const className = isDisabled ? 'day disabled' : 'day';
                                            
                                            html += `<td class="${className}" data-date="${year}-${(month+1).toString().padStart(2, '0')}-${date.toString().padStart(2, '0')}">${date}</td>`;
                                            date++;
                                        }
                                    }
                                    
                                    html += '</tr>';
                                }
                                
                                html += `
                                    </tbody>
                                </table>
                                `;
                                
                                calendarDiv.innerHTML = html;
                                
                                // Eventos para navegar entre meses
                                document.getElementById('prev-month').addEventListener('click', function() {
                                    if (month === 0) {
                                        month = 11;
                                        year--;
                                    } else {
                                        month--;
                                    }
                                    generarCalendario(month, year);
                                });
                                
                                document.getElementById('next-month').addEventListener('click', function() {
                                    if (month === 11) {
                                        month = 0;
                                        year++;
                                    } else {
                                        month++;
                                    }
                                    generarCalendario(month, year);
                                });
                                
                                // Evento para seleccionar días
                                document.querySelectorAll('.day').forEach(function(dayCell) {
                                    if (!dayCell.classList.contains('disabled')) {
                                        dayCell.addEventListener('click', function() {
                                            // Quitar selección anterior
                                            document.querySelectorAll('.day.active').forEach(function(activeDay) {
                                                activeDay.classList.remove('active');
                                            });
                                            
                                            // Añadir selección actual
                                            this.classList.add('active');
                                            
                                            const selectedDate = this.getAttribute('data-date');
                                            document.getElementById('selected-date').value = selectedDate;
                                            document.getElementById('fecha-input').value = selectedDate;
                                            
                                            // Obtener horarios disponibles para esta fecha
                                            obtenerHorarios(selectedDate);
                                        });
                                    }
                                });
                            }
                            
                            // Función para obtener los horarios disponibles para una fecha
                            function obtenerHorarios(fecha) {
                                // Obtener el día de la semana para la fecha seleccionada (0-6, domingo-sábado)
                                const diaSemana = new Date(fecha).getDay();
                                const diasTraduccion = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
                                const nombreDia = diasTraduccion[diaSemana];
                                const idServicio = <?= $id_servicio ?>;
                                
                                fetch(`obtener_horarios.php?autonomo=${idAutonomo}&dia=${nombreDia}&fecha=${fecha}&servicio=${idServicio}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        const horariosContainer = document.querySelector('.horarios-container');
                                        const horariosList = document.getElementById('horarios-list');
                                        
                                        if (data.length > 0) {
                                            let html = '';
                                            data.forEach(horario => {
                                                html += `<div class="horario-option" data-id="${horario.id_horario}" data-hora="${horario.hora_inicio}">${horario.hora_inicio.substring(0, 5)} - ${horario.hora_fin.substring(0, 5)}</div>`;
                                            });
                                            horariosList.innerHTML = html;
                                            
                                            // Mostrar contenedor de horarios
                                            horariosContainer.style.display = 'block';
                                            
                                            // Eventos para seleccionar horarios
                                            document.querySelectorAll('.horario-option').forEach(function(option) {
                                                option.addEventListener('click', function() {
                                                    // Quitar selección anterior
                                                    document.querySelectorAll('.horario-option.selected').forEach(function(selected) {
                                                        selected.classList.remove('selected');
                                                    });
                                                    
                                                    // Añadir selección actual
                                                    this.classList.add('selected');
                                                    
                                                    // Guardar id del horario y hora de inicio
                                                    const idHorario = this.getAttribute('data-id');
                                                    const horaInicio = this.getAttribute('data-hora');
                                                    document.getElementById('horario-input').value = idHorario;
                                                    document.getElementById('hora-inicio').value = horaInicio;
                                                    
                                                    // Mostrar botón de reserva
                                                    document.getElementById('btn-reservar').style.display = 'inline-block';
                                                });
                                            });
                                        } else {
                                            horariosList.innerHTML = '<p>No hay horarios disponibles para esta fecha.</p>';
                                            horariosContainer.style.display = 'block';
                                            document.getElementById('btn-reservar').style.display = 'none';
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error al obtener horarios:', error);
                                    });
                            }
                            
                            // Función auxiliar para obtener el nombre del mes
                            function nombreMes(month) {
                                const months = [
                                    'Enero', 'Febrero', 'Marzo', 'Abril', 
                                    'Mayo', 'Junio', 'Julio', 'Agosto', 
                                    'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                                ];
                                return months[month];
                            }
                        });
                        </script>
                    <?php endif; ?>
                </div>
            </div>
        </div>
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
</body>
</html>