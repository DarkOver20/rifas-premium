<?php
require_once __DIR__ . '/../admin/includes/config.php';
require_once __DIR__ . '/../admin/includes/functions.php';

header('Content-Type: application/json');

$evento_id = isset($_GET['evento_id']) ? intval($_GET['evento_id']) : 0;
$cantidad = isset($_GET['cantidad']) ? intval($_GET['cantidad']) : 1;

if ($evento_id <= 0 || $cantidad <= 0) {
    echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
    exit;
}

try {
    $db = getDBConnection();
    
    // Usar transacción para evitar condiciones de carrera
    $db->beginTransaction();
    
    // Obtener boletos disponibles con bloqueo para evitar duplicados
    $stmt = $db->prepare("
        SELECT numero_boleto 
        FROM boletos 
        WHERE evento_id = :evento_id 
        AND estado = 'disponible'
        ORDER BY RAND()
        LIMIT :cantidad
        FOR UPDATE SKIP LOCKED
    ");
    $stmt->bindValue(':evento_id', $evento_id, PDO::PARAM_INT);
    $stmt->bindValue(':cantidad', $cantidad, PDO::PARAM_INT);
    $stmt->execute();
    
    $boletos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($boletos) < $cantidad) {
        $db->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'No hay suficientes boletos disponibles',
            'boletos' => $boletos
        ]);
        exit;
    }
    
    // Marcar boletos como reservados temporalmente (opcional)
    $numbers = array_column($boletos, 'numero_boleto');
    $placeholders = implode(',', array_fill(0, count($numbers), '?'));
    
    $stmt = $db->prepare("
        UPDATE boletos 
        SET estado = 'reservado', 
            reservado_hasta = DATE_ADD(NOW(), INTERVAL 5 MINUTE)
        WHERE evento_id = ? 
        AND numero_boleto IN ($placeholders)
    ");
    $stmt->execute(array_merge([$evento_id], $numbers));
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'boletos' => $boletos,
        'message' => 'Boletos seleccionados aleatoriamente'
    ]);
    
} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
}