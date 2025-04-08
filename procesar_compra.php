<?php
while (ob_get_level()) ob_end_clean();

require_once './includes/config.php';
require_once './includes/functions.php';

header('Content-Type: application/json');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
if (error_get_last()) {
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor'
    ]);
    exit;
}

// Validar y sanitizar datos
$evento_id = filter_input(INPUT_POST, 'evento_id', FILTER_VALIDATE_INT);
$nombre = trim(filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING));
$telefono = preg_replace('/[^0-9+\- ]/', '', trim(filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING)));
$cedula = preg_replace('/[^0-9]/', '', trim(filter_input(INPUT_POST, 'cedula', FILTER_SANITIZE_STRING)));
$estado = trim(filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_STRING));
$referencia_pago = trim(filter_input(INPUT_POST, 'referencia_pago', FILTER_SANITIZE_STRING));
$metodo_pago_id = filter_input(INPUT_POST, 'metodo_pago', FILTER_VALIDATE_INT);
$referencia_transaccion = trim(filter_input(INPUT_POST, 'referencia_transaccion', FILTER_SANITIZE_STRING));
$boletos_seleccionados = json_decode($_POST['boletos_seleccionados'], true);

// Validaciones básicas
if (!$evento_id || !$nombre || !$telefono || !$cedula || !$estado || !$metodo_pago_id || !$referencia_transaccion || empty($boletos_seleccionados)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos o inválidos']);
    exit;
}

// Obtener información del evento
$evento = obtenerEvento($evento_id);
if (!$evento) {
    echo json_encode(['success' => false, 'message' => 'Evento no encontrado']);
    exit;
}

// Calcular monto total
$monto_total = count($boletos_seleccionados) * $evento['precio_boleto'];

// Procesar comprobante de pago
$comprobante_pago = '';
if (isset($_FILES['comprobante_pago']) && $_FILES['comprobante_pago']['error'] === UPLOAD_ERR_OK) {
    $extension = pathinfo($_FILES['comprobante_pago']['name'], PATHINFO_EXTENSION);
    $nombre_archivo = uniqid('comprobante_') . '.' . $extension;
    $ruta_destino = UPLOAD_DIR . $nombre_archivo;
    
    if (move_uploaded_file($_FILES['comprobante_pago']['tmp_name'], $ruta_destino)) {
        $comprobante_pago = $nombre_archivo;
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al subir el comprobante de pago']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Comprobante de pago requerido']);
    exit;
}

// Iniciar transacción
$pdo->beginTransaction();

try {
    // 1. Crear la transacción
    $stmt = $pdo->prepare("INSERT INTO transacciones (
        evento_id, nombre, telefono, cedula, estado, referencia_pago, 
        metodo_pago_id, referencia_transaccion, comprobante_pago, 
        boletos_seleccionados, monto_total, estado_compra
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')");
    
    $stmt->execute([
        $evento_id, $nombre, $telefono, $cedula, $estado, $referencia_pago,
        $metodo_pago_id, $referencia_transaccion, $comprobante_pago,
        json_encode($boletos_seleccionados), $monto_total
    ]);
    
    $transaccion_id = $pdo->lastInsertId();
    
    // 2. Actualizar el estado de los boletos a "reservado"
    $placeholders = implode(',', array_fill(0, count($boletos_seleccionados), '?'));
    $sql = "UPDATE boletos 
            SET estado = 'reservado', 
                transaccion_id = ?,
                fecha_reserva = NOW()
            WHERE evento_id = ? 
            AND numero_boleto IN ($placeholders)";
    
    $params = array_merge([$transaccion_id, $evento_id], $boletos_seleccionados);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    // Verificar que se actualizaron todos los boletos
    if ($stmt->rowCount() !== count($boletos_seleccionados)) {
        throw new Exception("Algunos boletos no pudieron ser reservados");
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Compra procesada correctamente. Los boletos han sido reservados pendientes de aprobación.',
        'transaccion_id' => $transaccion_id,
        'boletos_reservados' => $boletos_seleccionados // Envía los números de boletos reservados
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar la compra: ' . $e->getMessage()
    ]);
}
?>