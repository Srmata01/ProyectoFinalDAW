<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['autonomo'], $_GET['dia'], $_GET['fecha'])) {
    echo json_encode([]);
    exit;
}

$id_autonomo = (int)$_GET['autonomo'];
$dia_nombre = $_GET['dia']; // nombre del día: lunes, martes, etc.
$fecha = $_GET['fecha'];  // Formato YYYY-MM-DD
$id_servicio = isset($_GET['servicio']) ? (int)$_GET['servicio'] : 0;

// Convertir el nombre del día al número correspondiente
$dias_conversion = [
    'domingo' => 7,
    'lunes' => 1,
    'martes' => 2,
    'miercoles' => 3,
    'jueves' => 4,
    'viernes' => 5,
    'sabado' => 6,
];

$dia_semana = $dias_conversion[$dia_nombre] ?? null;

if (!$dia_semana) {
    echo json_encode([]);
    exit;
}

try {
    // Obtener la duración del servicio que se quiere reservar
    $duracion_servicio = 60; // Valor por defecto: 60 minutos
    if ($id_servicio > 0) {
        $stmt = $pdo->prepare("SELECT duracion FROM servicios WHERE id_servicio = ?");
        $stmt->execute([$id_servicio]);
        $servicio = $stmt->fetch();
        if ($servicio) {
            $duracion_servicio = $servicio['duracion'];
        }
    }
    
    // Obtener los horarios del autónomo para el día de la semana seleccionado
    $stmt = $pdo->prepare("
        SELECT h.*
        FROM horarios_autonomo h
        WHERE h.id_autonomo = ?
        AND h.dia_semana = ?
        AND h.activo = 1
    ");
    $stmt->execute([$id_autonomo, $dia_semana]);
    $horarios = $stmt->fetchAll();
    
    // Si no hay horarios configurados, devolver un array vacío
    if (empty($horarios)) {
        echo json_encode([]);
        exit;
    }
    
    // Obtener todas las reservas existentes para ese día y ese autónomo
    $stmt = $pdo->prepare("
        SELECT r.fecha_hora, s.duracion 
        FROM reservas r
        JOIN servicios s ON r.id_servicio = s.id_servicio
        WHERE s.id_autonomo = ? 
        AND DATE(r.fecha_hora) = ?
        AND r.estado IN ('pendiente', 'aceptada')
        ORDER BY r.fecha_hora ASC
    ");
    $stmt->execute([$id_autonomo, $fecha]);
    $reservas = $stmt->fetchAll();
    
    // Transformar las reservas en intervalos de tiempo ocupados
    $intervalos_ocupados = [];
    foreach ($reservas as $reserva) {
        $inicio = strtotime($reserva['fecha_hora']);
        $fin = $inicio + ($reserva['duracion'] * 60); // duración en segundos
        $intervalos_ocupados[] = [
            'inicio' => $inicio,
            'fin' => $fin
        ];
    }
    
    // Generar slots de tiempo disponibles
    $slots_disponibles = [];
    $interval_minutos = 15; // Intervalos de 15 minutos para slots
    
    foreach ($horarios as $horario) {
        $hora_inicio = strtotime($fecha . ' ' . $horario['hora_inicio']);
        $hora_fin = strtotime($fecha . ' ' . $horario['hora_fin']);
        
        // Iterar en intervalos de 15 minutos
        for ($inicio = $hora_inicio; $inicio <= ($hora_fin - ($duracion_servicio * 60)); $inicio += ($interval_minutos * 60)) {
            $fin = $inicio + ($duracion_servicio * 60);
            $disponible = true;
            
            // Verificar que este slot no se solape con ninguna reserva existente
            foreach ($intervalos_ocupados as $ocupado) {
                // Hay solapamiento si:
                // - El inicio del slot está dentro de un período ocupado
                // - El fin del slot está dentro de un período ocupado
                // - El slot engloba completamente un período ocupado
                if (
                    ($inicio >= $ocupado['inicio'] && $inicio < $ocupado['fin']) || 
                    ($fin > $ocupado['inicio'] && $fin <= $ocupado['fin']) ||
                    ($inicio <= $ocupado['inicio'] && $fin >= $ocupado['fin'])
                ) {
                    $disponible = false;
                    break;
                }
            }
            
            if ($disponible) {
                // Crear un objeto de slot con el formato necesario
                $slot = [
                    'id_horario' => $horario['id_horario'],
                    'hora_inicio' => date('H:i:s', $inicio),
                    'hora_fin' => date('H:i:s', $fin),
                    'duracion_servicio' => $duracion_servicio
                ];
                
                $slots_disponibles[] = $slot;
            }
        }
    }
    
    echo json_encode($slots_disponibles);
    
} catch (PDOException $e) {
    // En caso de error, devolver un mensaje de error
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => $e->getMessage()]);
}
?>