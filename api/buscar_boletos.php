<?php
/**
 * Buscar información de un boleto específico
 */
if (isset($_GET['action']) && $_GET['action'] === 'buscar_boleto') {
    // Limpiar cualquier salida previa
    ob_clean();
    
    // Forzar el tipo de contenido a JSON
    header('Content-Type: application/json');    
    try {
        require_once './config.php';

        $evento_id = intval($_GET['evento_id']);
        $numero_boleto = trim($_GET['numero']);
        
        $pdo = getDBConnection();
        
        // Consulta para obtener información del boleto
        $stmt = $pdo->prepare("SELECT 
                b.numero_boleto,
                b.estado,
                e.titulo AS evento_titulo,
                t.nombre,
                t.estado AS estado_comprador,
                t.telefono,
                t.estado_compra,
                t.fecha_compra
            FROM boletos b
            JOIN eventos e ON b.evento_id = e.id
            LEFT JOIN transacciones t ON b.transaccion_id = t.id
            WHERE b.evento_id = ? AND b.numero_boleto = ?");
        $stmt->execute([$evento_id, $numero_boleto]);
        $boleto = $stmt->fetch();
        
        if (!$boleto) {
            echo json_encode([
                'success' => false,
                'message' => 'No se encontró el boleto en este evento'
            ]);
            exit;
        }
        
        // Formatear respuesta
        $response = [
            'success' => true,
            'boleto' => [
                'numero' => $boleto['numero_boleto'],
                'estado' => $boleto['estado'],
                'evento' => $boleto['evento_titulo']
            ]
        ];
        
        // Si tiene información de compra
        if ($boleto['nombre']) {
            $response['comprador'] = [
                'nombre' => $boleto['nombre'],
                'estado' => $boleto['estado_comprador'],
                'telefono' => $boleto['telefono'],
                'estado_pago' => $boleto['estado_compra']
            ];
        }
        
        echo json_encode($response);
        
    } catch (Exception $e) {
        error_log("Error en buscar_boleto: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error al buscar información del boleto'
        ]);
    }
    exit;
}