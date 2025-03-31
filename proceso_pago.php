<?php
require_once './includes/config.php';
require_once './includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

// Validar datos del formulario
$evento_id = isset($_POST['evento_id']) ? intval($_POST['evento_id']) : 0;
$boletos_seleccionados = isset($_POST['boletos']) ? $_POST['boletos'] : [];
$metodo_pago = isset($_POST['metodo_pago']) ? sanitize($_POST['metodo_pago']) : '';
$referencia = isset($_POST['referencia']) ? sanitize($_POST['referencia']) : '';

if (empty($evento_id) || empty($boletos_seleccionados) || empty($metodo_pago)) {
    $_SESSION['error'] = 'Datos incompletos para procesar el pago.';
    redirect("evento.php?id=$evento_id");
}

// Obtener información del evento
$evento = obtenerEvento($evento_id);
if (!$evento || $evento['estado'] !== 'activo') {
    $_SESSION['error'] = 'El evento seleccionado no está disponible.';
    redirect('index.php');
}

// Verificar que los boletos estén disponibles
$boletos_disponibles = true;
foreach ($boletos_seleccionados as $numero_boleto) {
    $boleto = obtenerBoleto($evento_id, $numero_boleto);
    if (!$boleto || $boleto['estado'] !== 'disponible') {
        $boletos_disponibles = false;
        break;
    }
}

if (!$boletos_disponibles) {
    $_SESSION['error'] = 'Uno o más boletos seleccionados ya no están disponibles.';
    redirect("evento.php?id=$evento_id");
}

// Calcular monto total
$monto_total = count($boletos_seleccionados) * $evento['precio_boleto'];

// Crear transacción
try {
    $pdo->beginTransaction();
    
    // Insertar transacción
    $stmt = $pdo->prepare("INSERT INTO transacciones 
        (usuario_id, evento_id, referencia, monto, metodo_pago, estado)
        VALUES (?, ?, ?, ?, ?, 'pendiente')");
    $stmt->execute([
        $_SESSION['usuario_id'],
        $evento_id,
        $referencia,
        $monto_total,
        $metodo_pago
    ]);
    $transaccion_id = $pdo->lastInsertId();
    
    // Reservar boletos
    foreach ($boletos_seleccionados as $numero_boleto) {
        $stmt = $pdo->prepare("UPDATE boletos SET 
            estado = 'reservado', 
            usuario_id = ?, 
            transaccion_id = ?, 
            fecha_reserva = NOW()
            WHERE evento_id = ? AND numero_boleto = ? AND estado = 'disponible'");
        $stmt->execute([
            $_SESSION['usuario_id'],
            $transaccion_id,
            $evento_id,
            $numero_boleto
        ]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception("Error al reservar el boleto $numero_boleto");
        }
    }
    
    // Actualizar contador de boletos disponibles
    $stmt = $pdo->prepare("UPDATE eventos SET 
        boletos_disponibles = boletos_disponibles - ?
        WHERE id = ?");
    $stmt->execute([count($boletos_seleccionados), $evento_id]);
    
    $pdo->commit();
    
    // Enviar notificación al administrador
    $titulo = "Nueva solicitud de pago";
    $mensaje = "El usuario {$_SESSION['usuario_nombre']} ha realizado una solicitud de pago por $" . number_format($monto_total, 2);
    enviarNotificacionAdmin($titulo, $mensaje);
    
    $_SESSION['exito'] = 'Tu solicitud de compra ha sido procesada. Un administrador revisará tu pago pronto.';
    redirect('user/mis-rifas.php');
    
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'Ocurrió un error al procesar tu pago: ' . $e->getMessage();
    redirect("evento.php?id=$evento_id");
}