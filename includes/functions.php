<?php
// ... otras funciones existentes ...

function obtenerSolicitudPorId($id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT t.*, 
                          u.nombre AS usuario_nombre, u.email AS usuario_email, u.telefono AS usuario_telefono,
                          e.titulo AS evento_titulo, e.precio_boleto AS evento_precio,
                          a.nombre AS admin_nombre,
                          (SELECT COUNT(*) FROM boletos WHERE transaccion_id = t.id) AS cantidad_boletos
                          FROM transacciones t
                          JOIN usuarios u ON t.usuario_id = u.id
                          JOIN eventos e ON t.evento_id = e.id
                          LEFT JOIN usuarios a ON t.admin_id = a.id
                          WHERE t.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function obtenerBoletosPorTransaccion($transaccion_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM boletos WHERE transaccion_id = ?");
    $stmt->execute([$transaccion_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function obtenerSolicitudesPendientes() {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT t.*, 
                          u.nombre AS usuario_nombre, 
                          e.titulo AS evento_titulo,
                          (SELECT COUNT(*) FROM boletos WHERE transaccion_id = t.id) AS cantidad_boletos
                          FROM transacciones t
                          JOIN usuarios u ON t.usuario_id = u.id
                          JOIN eventos e ON t.evento_id = e.id
                          WHERE t.estado = 'pendiente'
                          ORDER BY t.fecha_transaccion DESC
                          LIMIT 10");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function contarUsuarios() {
    global $pdo;
    return $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
}

function calcularVentasTotales() {
    global $pdo;
    return $pdo->query("SELECT COALESCE(SUM(monto), 0) FROM transacciones WHERE estado = 'aprobado'")->fetchColumn();
}

function enviarNotificacion($usuario_id, $titulo, $mensaje) {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO notificaciones 
                          (usuario_id, titulo, mensaje, leida, fecha_creacion)
                          VALUES (?, ?, ?, 0, NOW())");
    $stmt->execute([$usuario_id, $titulo, $mensaje]);
    
    // Aquí podrías agregar también el envío de email si lo deseas
}

function generarBoletos($evento_id, $total_boletos) {
    global $pdo;
    
    // Generar números de boletos con ceros a la izquierda
    $longitud = strlen((string)$total_boletos);
    $boletos = array_map(function($n) use ($longitud) {
        return str_pad($n, $longitud, '0', STR_PAD_LEFT);
    }, range(1, $total_boletos));
    
    // Insertar todos los boletos en una sola consulta
    $sql = "INSERT INTO boletos (evento_id, numero_boleto) VALUES ";
    $values = [];
    $params = [];
    
    foreach ($boletos as $i => $boleto) {
        $values[] = "(?, ?)";
        $params[] = $evento_id;
        $params[] = $boleto;
    }
    
    $sql .= implode(", ", $values);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}
function obtenerRifasUsuario($usuario_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT 
        t.id AS transaccion_id,
        e.id AS evento_id,
        e.titulo,
        e.imagen,
        t.monto,
        t.estado AS estado_transaccion,
        t.fecha_transaccion,
        GROUP_CONCAT(b.numero_boleto ORDER BY b.numero_boleto) AS numeros_boletos,
        MAX(CASE WHEN b.estado = 'ganador' THEN 1 ELSE 0 END) AS tiene_ganador
    FROM transacciones t
    JOIN eventos e ON t.evento_id = e.id
    JOIN boletos b ON t.id = b.transaccion_id
    WHERE t.usuario_id = ?
    GROUP BY t.id, e.id, e.titulo, e.imagen, t.monto, t.estado, t.fecha_transaccion
    ORDER BY t.fecha_transaccion DESC");
    
    $stmt->execute([$usuario_id]);
    $rifas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Agregar estado del boleto a cada rifa
    foreach ($rifas as &$rifa) {
        $rifa['estado_boleto'] = $rifa['tiene_ganador'] ? 'ganador' : ($rifa['estado_transaccion'] === 'aprobado' ? 'pagado' : 'pendiente');
    }
    
    return $rifas;
}

function obtenerNotificaciones($usuario_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM notificaciones 
                          WHERE usuario_id = ? 
                          ORDER BY fecha_creacion DESC");
    $stmt->execute([$usuario_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function enviarNotificacionAdmin($titulo, $mensaje) {
    global $pdo;
    
    // Obtener todos los administradores
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE rol = 'admin'");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Enviar notificación a cada admin
    foreach ($admins as $admin_id) {
        $stmt = $pdo->prepare("INSERT INTO notificaciones 
                              (usuario_id, titulo, mensaje)
                              VALUES (?, ?, ?)");
        $stmt->execute([$admin_id, $titulo, $mensaje]);
    }
}

function obtenerUsuario($id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function login($email, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario && password_verify($password, $usuario['password'])) {
        // Configurar sesión
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['usuario_rol'] = $usuario['rol'];
        $_SESSION['usuario_avatar'] = $usuario['avatar'];
        
        return true;
    }
    
    return false;
}

function registrarUsuario($nombre, $email, $password, $telefono = null, $direccion = null) {
    global $pdo;
    
    // Verificar si el email ya existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        return false; // Email ya registrado
    }
    
    // Hash de la contraseña
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    // Insertar nuevo usuario
    $stmt = $pdo->prepare("INSERT INTO usuarios 
                          (nombre, email, password, telefono, direccion)
                          VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nombre, $email, $hashed_password, $telefono, $direccion]);
    
    return $pdo->lastInsertId();
}

function obtenerEvento($id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM eventos WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function obtenerBoletosDisponibles($evento_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM boletos 
                          WHERE evento_id = ? AND estado = 'disponible'
                          ORDER BY numero_boleto");
    $stmt->execute([$evento_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function obtenerBoleto($evento_id, $numero_boleto) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM boletos 
                          WHERE evento_id = ? AND numero_boleto = ?");
    $stmt->execute([$evento_id, $numero_boleto]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function obtenerEventos($estado = null) {
    global $pdo;
    
    $sql = "SELECT * FROM eventos";
    $params = [];
    
    if ($estado) {
        $sql .= " WHERE estado = ?";
        $params[] = $estado;
    }
    
    $sql .= " ORDER BY fecha_inicio DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}