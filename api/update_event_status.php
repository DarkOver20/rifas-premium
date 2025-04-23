<?php
// // Configuración inicial para manejo de errores
// ini_set('display_errors', 0);
// ini_set('log_errors', 1);
// ini_set('error_log', __DIR__ . '/../logs/api_errors.log');

// // Encabezados para respuesta JSON
// header('Content-Type: application/json');
// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods: POST');
// header('Access-Control-Allow-Headers: Content-Type');

// // Verificar si el script se está ejecutando directamente
// if (basename(__FILE__) !== basename($_SERVER['SCRIPT_FILENAME'])) {
//     http_response_code(403);
//     echo json_encode(['success' => false, 'message' => 'Acceso directo no permitido']);
//     exit;
// }

// Incluir archivos necesarios
require_once __DIR__ . '/../admin/includes/config.php';
require_once __DIR__ . '/../admin/includes/functions.php';

// Función para enviar respuestas JSON estandarizadas
function sendResponse($success, $message, $data = [], $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

try {
    // Verificar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Método no permitido', [], 405);
    }

    // Obtener y validar datos de entrada
    $input = file_get_contents('php://input');
    if (empty($input)) {
        sendResponse(false, 'Datos no proporcionados', [], 400);
    }

    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendResponse(false, 'JSON inválido: ' . json_last_error_msg(), [], 400);
    }

    // Validar ID del evento
    if (empty($data['eventId']) || !is_numeric($data['eventId'])) {
        sendResponse(false, 'ID de evento no válido', [], 400);
    }

    $eventId = (int)$data['eventId'];
    $db = getDBConnection();

    // Verificar si el evento existe
    $stmt = $db->prepare("SELECT id, estado, fecha_fin FROM eventos WHERE id = ?");
    $stmt->execute([$eventId]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$evento) {
        sendResponse(false, 'Evento no encontrado', ['eventId' => $eventId], 404);
    }

    // Verificar si el evento ya está finalizado
    if ($evento['estado'] === 'finalizado') {
        sendResponse(true, 'El evento ya estaba finalizado', [
            'eventId' => $eventId,
            'fecha_fin' => $evento['fecha_fin']
        ]);
    }

    // Verificar si la fecha de finalización ya pasó
    $fechaFin = new DateTime($evento['fecha_fin']);
    $now = new DateTime();

    if ($fechaFin > $now) {
        sendResponse(false, 'El evento aún no ha finalizado', [
            'eventId' => $eventId,
            'fecha_fin' => $evento['fecha_fin'],
            'current_time' => $now->format('Y-m-d H:i:s')
        ], 400);
    }

    // Actualizar el estado del evento
    $stmt = $db->prepare("UPDATE eventos SET estado = 'finalizado', updated_at = NOW() WHERE id = ?");
    $stmt->execute([$eventId]);
    $affectedRows = $stmt->rowCount();

    if ($affectedRows > 0) {
        sendResponse(true, 'Evento actualizado a finalizado', [
            'eventId' => $eventId,
            'previous_status' => $evento['estado'],
            'fecha_fin' => $evento['fecha_fin']
        ]);
    } else {
        sendResponse(false, 'No se pudo actualizar el evento', [
            'eventId' => $eventId,
            'affected_rows' => $affectedRows
        ], 500);
    }

} catch (PDOException $e) {
    error_log('Error de base de datos: ' . $e->getMessage());
    sendResponse(false, 'Error de base de datos', [
        'error_code' => $e->getCode(),
        'error_message' => $e->getMessage()
    ], 500);

} catch (Exception $e) {
    error_log('Error general: ' . $e->getMessage());
    sendResponse(false, 'Error en el servidor', [
        'error_code' => $e->getCode(),
        'error_message' => $e->getMessage()
    ], 500);
}