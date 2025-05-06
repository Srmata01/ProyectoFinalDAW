<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['autonomo'], $_GET['dia'], $_GET['fecha'])) {
    echo json_encode([]);
    exit;
}

$id_autonomo = (int)$_GET['autonomo'];
$dia_semana = $_GET['dia']; // 1=lunes, 2=martes, etc.
$fecha = $_GET['fecha'];  // Formato YYYY-MM-DD

try {
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
    
    // Si hay horarios, verificar que no estén ya ocupados por otras reservas
    $horarios_disponibles = [];
    
    foreach ($horarios as $horario) {
        // Crear las cadenas de fecha y hora para comparación
        $fecha_inicio = $fecha . ' ' . $horario['hora_inicio'];
        $fecha_fin = $fecha . ' ' . $horario['hora_fin'];
        
        // Verificar si ya hay reservas que ocupen este horario
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as conflictos
            FROM reservas r
            JOIN servicios s ON r.id_servicio = s.id_servicio
            WHERE s.id_autonomo = ? 
            AND DATE(r.fecha_hora) = ?
            AND r.estado IN ('pendiente', 'aceptada')
            AND (
                (r.fecha_hora <= ? AND ADDTIME(r.fecha_hora, SEC_TO_TIME(s.duracion * 60)) > ?)
                OR (r.fecha_hora < ? AND ADDTIME(r.fecha_hora, SEC_TO_TIME(s.duracion * 60)) >= ?)
                OR (r.fecha_hora >= ? AND ADDTIME(r.fecha_hora, SEC_TO_TIME(s.duracion * 60)) <= ?)
            )
        ");
        $stmt->execute([
            $id_autonomo, 
            $fecha,
            $fecha_inicio, $fecha_inicio,
            $fecha_fin, $fecha_fin,
            $fecha_inicio, $fecha_fin
        ]);
        
        $resultado = $stmt->fetch();
        
        if ($resultado['conflictos'] == 0) {
            $horarios_disponibles[] = $horario;
        }
    }
    
    echo json_encode($horarios_disponibles);
    
} catch (PDOException $e) {
    // En caso de error, devolver un mensaje de error
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => $e->getMessage()]);
}
?>