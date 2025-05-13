<?php
require_once __DIR__ . '/../admin/includes/config.php';
require_once __DIR__ . '/../admin/includes/functions.php';

header('Content-Type: application/json');

$evento_id = isset($_GET['evento_id']) ? intval($_GET['evento_id']) : 0;

if ($evento_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de evento inválido']);
    exit;
}

try {
    $db = getDBConnection();
    
    // Consulta para obtener todos los boletos del evento
    $stmt = $db->prepare("SELECT numero_boleto, estado FROM boletos WHERE evento_id = ?");
    $stmt->execute([$evento_id]);
    $boletos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'boletos' => $boletos
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener boletos: ' . $e->getMessage()
    ]);
}