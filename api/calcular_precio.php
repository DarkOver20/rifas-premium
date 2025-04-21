<?php
require_once '../admin/includes/config.php';
require_once '../admin/includes/functions.php';

// Establecer el tipo de contenido como JSON
header('Content-Type: application/json');

// Validar que los parámetros existan y sean válidos
if (!isset($_GET['metodo_pago_id'])) {
    echo json_encode(['success' => false, 'message' => 'Falta el parámetro metodo_pago_id']);
    exit;
}

if (!isset($_GET['precio'])) {
    echo json_encode(['success' => false, 'message' => 'Falta el parámetro precio']);
    exit;
}

$metodo_pago_id = (int)$_GET['metodo_pago_id'];
$precio = (float)$_GET['precio'];

if ($metodo_pago_id <= 0 || $precio <= 0) {
    echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
    exit;
}

try {
    // Obtener información del método de pago y tasa
    $sql = "SELECT mp.*, tp.codigo AS moneda, tc.tasa
            FROM metodos_pago mp
            LEFT JOIN tipos_pago tp ON mp.tipo_pago_id = tp.id
            LEFT JOIN tasas_cambio tc ON tp.id = tc.tipo_pago_id
            WHERE mp.id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$metodo_pago_id]);
    $metodo = $stmt->fetch(PDO::FETCH_ASSOC);

    $response = [
        'success' => true,
        'conversion' => false,
        'precio_original' => $precio,
        'precio_convertido' => $precio,
        'moneda' => 'USD'
    ];

    if ($metodo && isset($metodo['tasa']) && $metodo['tasa'] > 0) {
        $response['conversion'] = true;
        $response['precio_convertido'] = $precio * $metodo['tasa'];
        $response['moneda'] = $metodo['moneda'];
    }

    echo json_encode($response);
    
} catch (PDOException $e) {
    // Registrar el error en el log
    error_log("Error en calcular_precio.php: " . $e->getMessage());
    
    // Devolver un error en formato JSON
    echo json_encode([
        'success' => false,
        'message' => 'Error al calcular el precio',
        'error' => $e->getMessage()
    ]);
}