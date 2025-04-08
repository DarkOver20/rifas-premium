<?php
/**********************************************
 * FUNCIONES DE CONEXIÓN Y CONFIGURACIÓN
 **********************************************/

/**
 * Establece conexión a la base de datos
 * @return PDO Objeto de conexión PDO
 */
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", 
                DB_USER, 
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log("Error de conexión: " . $e->getMessage());
            die("Error de conexión a la base de datos");
        }
    }
    
    return $pdo;
}

/**********************************************
 * FUNCIONES DE AUTENTICACIÓN Y USUARIOS
 **********************************************/

function login_admin($email, $password) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND activo = 1");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();
    
    if ($usuario && password_verify($password, $usuario['password'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['usuario_rol'] = $usuario['rol'];
        $_SESSION['usuario_avatar'] = $usuario['avatar'];
        
        return true;
    }
    
    return false;
}

function registrarUsuario($nombre, $email, $password, $telefono = null) {
    $pdo = getDBConnection();
    
    // Verificar si el email ya existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        return false;
    }
    
    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, telefono) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $nombre,
        $email,
        password_hash($password, PASSWORD_BCRYPT),
        $telefono
    ]);
    
    return $pdo->lastInsertId();
}

function obtenerUsuario($id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**********************************************
 * FUNCIONES DE EVENTOS
 **********************************************/

function obtenerEvento($id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM eventos WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function obtenerEventos($estado = null) {
    $pdo = getDBConnection();
    $sql = "SELECT * FROM eventos";
    $params = [];
    
    if ($estado) {
        $sql .= " WHERE estado = ?";
        $params[] = $estado;
    }
    
    $sql .= " ORDER BY fecha_inicio DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function generarBoletosLotes($evento_id, $total_boletos, $lote_size = 1000) {
    $pdo = getDBConnection();
    $longitud = strlen((string)$total_boletos);
    $lotes = ceil($total_boletos / $lote_size);
    
    for ($i = 0; $i < $lotes; $i++) {
        $inicio = $i * $lote_size + 1;
        $fin = min(($i + 1) * $lote_size, $total_boletos);
        
        $boletos = array_map(function($n) use ($longitud) {
            return str_pad($n, $longitud, '0', STR_PAD_LEFT);
        }, range($inicio, $fin));
        
        $sql = "INSERT INTO boletos (evento_id, numero_boleto) VALUES ";
        $placeholders = [];
        $params = [];
        
        foreach ($boletos as $boleto) {
            $placeholders[] = "(?, ?)";
            $params[] = $evento_id;
            $params[] = $boleto;
        }
        
        $sql .= implode(", ", $placeholders);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }
}

function asignarBoletoGanador($evento_id, $numero_boleto) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT id FROM boletos WHERE evento_id = ? AND numero_boleto = ? AND estado = 'pagado'");
    $stmt->execute([$evento_id, $numero_boleto]);
    
    if (!$stmt->fetch()) {
        return false;
    }
    
    $stmt = $pdo->prepare("UPDATE eventos SET boleto_ganador = ? WHERE id = ?");
    return $stmt->execute([$numero_boleto, $evento_id]);
}

/**********************************************
 * FUNCIONES DE BOLETOS
 **********************************************/

function obtenerBoletosDisponibles($evento_id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM boletos WHERE evento_id = ? AND estado = 'disponible' ORDER BY numero_boleto");
    $stmt->execute([$evento_id]);
    return $stmt->fetchAll();
}

function obtenerBoleto($evento_id, $numero_boleto) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM boletos WHERE evento_id = ? AND numero_boleto = ?");
    $stmt->execute([$evento_id, $numero_boleto]);
    return $stmt->fetch();
}

function obtenerBoletosEvento($evento_id, $estado = null) {
    $pdo = getDBConnection();
    $sql = "SELECT * FROM boletos WHERE evento_id = ?";
    $params = [$evento_id];
    
    if ($estado) {
        $sql .= " AND estado = ?";
        $params[] = $estado;
    }
    
    $sql .= " ORDER BY numero_boleto";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**********************************************
 * FUNCIONES DE TRANSACCIONES
 **********************************************/

function obtenerSolicitudPorId($id) {
    $pdo = getDBConnection();
    
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
    return $stmt->fetch();
}

function obtenerBoletosPorTransaccion($transaccion_id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM boletos WHERE transaccion_id = ?");
    $stmt->execute([$transaccion_id]);
    return $stmt->fetchAll();
}

function obtenerSolicitudesPendientes() {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT t.*, 
                          u.nombre AS usuario_nombre, 
                          e.titulo AS evento_titulo,
                          (SELECT COUNT(*) FROM boletos WHERE transaccion_id = t.id) AS cantidad_boletos
                          FROM transacciones t
                          JOIN usuarios u ON t.usuario_id = u.id
                          JOIN eventos e ON t.evento_id = e.id
                          WHERE t.estado_compra = 'pendiente'
                          ORDER BY t.fecha_compra DESC
                          LIMIT 10");
    $stmt->execute();
    return $stmt->fetchAll();
}

function procesarTransaccion($transaccion_id, $accion, $admin_id, $notas = '') {
    $pdo = getDBConnection();
    $nuevo_estado = $accion === 'aprobar' ? 'aprobada' : 'rechazada';
    $estado_boleto = $accion === 'aprobar' ? 'pagado' : 'disponible';
    
    try {
        $pdo->beginTransaction();
        
        // Actualizar transacción
        $stmt = $pdo->prepare("UPDATE transacciones 
                              SET estado_compra = ?, admin_id = ?, notas = ?, fecha_revision = NOW()
                              WHERE id = ?");
        $stmt->execute([$nuevo_estado, $admin_id, $notas, $transaccion_id]);
        
        // Actualizar boletos
        $stmt = $pdo->prepare("UPDATE boletos 
                              SET estado = ?,
                                  fecha_pago = IF(? = 'aprobada', NOW(), NULL),
                                  transaccion_id = IF(? = 'aprobada', transaccion_id, NULL)
                              WHERE transaccion_id = ?");
        $stmt->execute([$estado_boleto, $nuevo_estado, $nuevo_estado, $transaccion_id]);
        
        $pdo->commit();
        return true;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error al procesar transacción: " . $e->getMessage());
        return false;
    }
}

/**********************************************
 * FUNCIONES DE NOTIFICACIONES
 **********************************************/

function enviarNotificacion($usuario_id, $titulo, $mensaje) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("INSERT INTO notificaciones 
                          (usuario_id, titulo, mensaje, leida, fecha_creacion)
                          VALUES (?, ?, ?, 0, NOW())");
    return $stmt->execute([$usuario_id, $titulo, $mensaje]);
}

function enviarNotificacionAdmin($titulo, $mensaje) {
    $pdo = getDBConnection();
    $admins = $pdo->query("SELECT id FROM usuarios WHERE rol = 'admin'")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($admins as $admin_id) {
        enviarNotificacion($admin_id, $titulo, $mensaje);
    }
}

function obtenerNotificaciones($usuario_id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM notificaciones WHERE usuario_id = ? ORDER BY fecha_creacion DESC");
    $stmt->execute([$usuario_id]);
    return $stmt->fetchAll();
}

/**********************************************
 * FUNCIONES DE MÉTODOS DE PAGO
 **********************************************/

function obtener_metodos_pago($activo = true) {
    $pdo = getDBConnection();
    $sql = "SELECT * FROM metodos_pago";
    if ($activo) {
        $sql .= " WHERE activo = 1";
    }
    return $pdo->query($sql)->fetchAll();
}

/**********************************************
 * FUNCIONES DE REPORTES Y ESTADÍSTICAS
 **********************************************/

function contarUsuarios() {
    $pdo = getDBConnection();
    return $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
}

function calcularVentasTotales() {
    $pdo = getDBConnection();
    return $pdo->query("SELECT COALESCE(SUM(monto_total), 0) FROM transacciones WHERE estado_compra = 'aprobada'")->fetchColumn();
}

/**********************************************
 * FUNCIONES DE MANIPULACIÓN DE IMÁGENES
 **********************************************/

function optimizarImagen($ruta, $max_width = 1200, $max_height = 800) {
    if (!function_exists('imagecreatefromjpeg')) {
        error_log("La extensión GD no está instalada");
        return false;
    }
    
    $info = @getimagesize($ruta);
    if (!$info) {
        error_log("No se pudo leer la imagen: $ruta");
        return false;
    }
    
    list($width, $height, $type) = $info;
    
    // Solo optimizar si es más grande que los máximos
    if ($width <= $max_width && $height <= $max_height) {
        return true;
    }
    
    // Calcular nuevas dimensiones manteniendo aspect ratio
    $ratio = $width / $height;
    if ($max_width / $max_height > $ratio) {
        $new_width = $max_height * $ratio;
        $new_height = $max_height;
    } else {
        $new_width = $max_width;
        $new_height = $max_width / $ratio;
    }
    
    // Crear imagen según el tipo
    switch ($type) {
        case IMAGETYPE_JPEG:
            $src = @imagecreatefromjpeg($ruta);
            break;
        case IMAGETYPE_PNG:
            $src = @imagecreatefrompng($ruta);
            break;
        case IMAGETYPE_GIF:
            $src = @imagecreatefromgif($ruta);
            break;
        default:
            return false;
    }
    
    if (!$src) {
        error_log("No se pudo crear la imagen desde el archivo: $ruta");
        return false;
    }
    
    $dst = @imagecreatetruecolor($new_width, $new_height);
    if (!$dst) {
        imagedestroy($src);
        return false;
    }
    
    // Preservar transparencia para PNG y GIF
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagecolortransparent($dst, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
    }
    
    if (!@imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height)) {
        imagedestroy($src);
        imagedestroy($dst);
        return false;
    }
    
    // Guardar la imagen optimizada
    $result = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($dst, $ruta, 85);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($dst, $ruta, 8);
            break;
        case IMAGETYPE_GIF:
            $result = imagegif($dst, $ruta);
            break;
    }
    
    imagedestroy($src);
    imagedestroy($dst);
    
    return $result;
}