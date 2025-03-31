<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT * FROM metodos_pago WHERE activo = 1");
    $metodos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $metodos
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al cargar métodos de pago'
    ]);
}