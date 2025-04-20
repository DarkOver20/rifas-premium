<?php
require_once '../admin/includes/config.php';
require_once '../admin/includes/functions.php';

header('Content-Type: application/json');

$evento_id = isset($_GET['evento_id']) ? intval($_GET['evento_id']) : 0;
$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;

try {
    $boletos = obtenerBoletosDisponiblesPaginados($evento_id, $pagina);
    $total = contarBoletosDisponibles($evento_id);
    
    echo json_encode([
        'success' => true,
        'boletos' => $boletos,
        'total_paginas' => ceil($total / 100)
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al cargar boletos: ' . $e->getMessage()
    ]);
}
?>