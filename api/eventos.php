<?php
require_once  '../includes/config.php';
require_once  '../includes/functions.php';

header('Content-Type: application/json');

$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'activos';

try {
    switch ($tipo) {
        case 'activos':
            $eventos = obtenerEventos('activo');
            break;
        case 'finalizados':
            $eventos = obtenerEventos('finalizado');
            break;
        case 'proximamente':
            $eventos = obtenerEventos('proximamente');
            break;
        default:
            throw new Exception('Tipo de evento no válido');
    }
    
    echo json_encode([
        'success' => true,
        'data' => $eventos
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}