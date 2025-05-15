<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$numero = isset($_GET['numero']) ? $_GET['numero'] : '';
$evento_id = isset($_GET['evento_id']) ? intval($_GET['evento_id']) : 0;

if (empty($numero) || $evento_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
    exit;
}

try {
    $db = getDBConnection();
    
    // Verificar estado del boleto
    $stmt = $db->prepare("
        SELECT estado 
        FROM boletos 
        WHERE evento_id = :evento_id 
        AND numero_boleto = :numero
    ");
    $stmt->bindParam(':evento_id', $evento_id, PDO::PARAM_INT);
    $stmt->bindParam(':numero', $numero);
    $stmt->execute();
    
    $boleto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$boleto) {
        echo json_encode(['success' => false, 'disponible' => false, 'message' => 'Boleto no encontrado']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'disponible' => $boleto['estado'] === 'disponible',
        'estado' => $boleto['estado']
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
}