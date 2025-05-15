<?php
require_once __DIR__ . '/../admin/includes/config.php';
require_once __DIR__ . '/../admin/includes/functions.php';

header('Content-Type: application/json');

$evento_id = isset($_GET['evento_id']) ? intval($_GET['evento_id']) : 0;
$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$por_pagina = 100; // Boletos por página

if ($evento_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de evento inválido']);
    exit;
}

try {
    $db = getDBConnection();
    
    // Obtener conteo total
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM boletos WHERE evento_id = ?");
    $stmt->execute([$evento_id]);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Calcular páginas
    $total_paginas = ceil($total / $por_pagina);
    $offset = ($pagina - 1) * $por_pagina;
    
    // Obtener boletos para la página actual
    $stmt = $db->prepare("
        SELECT numero_boleto, estado 
        FROM boletos 
        WHERE evento_id = ? 
        ORDER BY numero_boleto 
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $evento_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $por_pagina, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $boletos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'boletos' => $boletos,
        'pagina_actual' => $pagina,
        'total_paginas' => $total_paginas,
        'total_boletos' => $total
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
}