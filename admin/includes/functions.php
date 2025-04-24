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


// Funciones de ayuda
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
function is_logged_in() {
    return isset($_SESSION['usuario_id']);
}

function require_login() {
    if (!is_logged_in()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        redirect('/rifas-premium/sys-396/access');
    }
}

function require_admin() {
    require_login();
    if ($_SESSION['usuario_rol'] !== 'admin') {
        $_SESSION['error'] = 'Acceso denegado. Se requieren privilegios de administrador.';
        redirect('/');
    }
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function generarSlug($texto) {
    if (empty($texto)) {
        return 'sin-titulo'; // Valor por defecto
    }
    $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($texto));
    return trim($slug, '-');
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

/**
 * Verificar un boleto antes de asignarlo como ganador
 */
if (isset($_GET['action']) && $_GET['action'] === 'verificar_boleto') {
    $evento_id = intval($_GET['evento_id']);
    $numero_boleto = sanitize($_GET['numero_boleto']);
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT b.*, t.nombre AS comprador 
                          FROM boletos b
                          LEFT JOIN transacciones t ON b.transaccion_id = t.id
                          WHERE b.evento_id = ? AND b.numero_boleto = ?");
    $stmt->execute([$evento_id, $numero_boleto]);
    $boleto = $stmt->fetch();
    
    if ($boleto) {
        echo json_encode([
            'existe' => true,
            'estado' => $boleto['estado'],
            'comprador' => $boleto['comprador'] ?? null
        ]);
    } else {
        echo json_encode(['existe' => false]);
    }
    exit;
}
/**
 * Asignar boleto ganador
 */
if (isset($_POST['action']) && $_POST['action'] === 'asignar_ganador') {
    header('Content-Type: application/json');
    
    $evento_id = intval($_POST['evento_id']);
    $numero_boleto = sanitize($_POST['numero_boleto']);
    
    $pdo = getDBConnection();
    
    try {
        // Verificar que el boleto existe
        $stmt = $pdo->prepare("SELECT b.*, t.nombre, t.cedula, t.telefono
                              FROM boletos b
                              LEFT JOIN transacciones t ON b.transaccion_id = t.id
                              WHERE b.evento_id = ? AND b.numero_boleto = ?");
        $stmt->execute([$evento_id, $numero_boleto]);
        $boleto = $stmt->fetch();
        
        if ($boleto) {
            // Asignar boleto ganador
            $stmt = $pdo->prepare("UPDATE eventos SET boleto_ganador = ? WHERE id = ?");
            $stmt->execute([$numero_boleto, $evento_id]);
            
            // Devolver información del comprador
            echo json_encode([
                'success' => true,
                'message' => 'Boleto ganador asignado correctamente',
                'comprador' => [
                    'nombre' => $boleto['nombre'],
                    'cedula' => $boleto['cedula'],
                    'telefono' => $boleto['telefono'],
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'El boleto no existe'
            ]);
        }
    } catch (PDOException $e) {
        error_log("Error al asignar boleto ganador: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error al asignar el boleto ganador: ' . $e->getMessage()
        ]);
    }
    exit;
}
/**
 * Obtener información del ganador actual
 */
if (isset($_GET['action']) && $_GET['action'] === 'obtener_ganador') {
    // Limpiar cualquier salida previa
    ob_clean();
    
    // Forzar el tipo de contenido a JSON
    header('Content-Type: application/json');    
    try {
        $evento_id = intval($_GET['evento_id']);
        
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT 
                e.boleto_ganador, 
                t.nombre, 
                t.cedula, 
                t.telefono,
                t.email
            FROM eventos e
            LEFT JOIN boletos b ON e.boleto_ganador = b.numero_boleto AND e.id = b.evento_id
            LEFT JOIN transacciones t ON b.transaccion_id = t.id
            WHERE e.id = ?");
        $stmt->execute([$evento_id]);
        $ganador = $stmt->fetch();
        
        if (!$ganador || empty($ganador['boleto_ganador'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Este evento no tiene un boleto ganador asignado'
            ]);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'boleto_ganador' => $ganador['boleto_ganador'],
            'comprador' => [
                'nombre' => $ganador['nombre'] ?? 'No disponible',
                'cedula' => $ganador['cedula'] ?? 'No disponible',
                'telefono' => $ganador['telefono'] ?? 'No disponible',
                'email' => $ganador['email'] ?? 'No disponible'
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Error en obtener_ganador: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener información del ganador'
        ]);
    }
    exit;
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
                          e.titulo AS evento_titulo, 
                          e.precio_boleto AS evento_precio,
                          (SELECT COUNT(*) FROM boletos WHERE transaccion_id = t.id) AS cantidad_boletos
                          FROM transacciones t
                          JOIN eventos e ON t.evento_id = e.id
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
    
    $sql = "SELECT t.id, 
    t.nombre AS comprador_nombre,
    t.cedula AS comprador_cedula,
    e.titulo AS evento_titulo,
    t.monto_total AS monto,
    t.fecha_compra AS fecha_transaccion,
    (SELECT COUNT(*) FROM boletos WHERE transaccion_id = t.id) AS cantidad_boletos
FROM transacciones t
JOIN eventos e ON t.evento_id = e.id
WHERE t.estado_compra = 'pendiente'
ORDER BY t.fecha_compra DESC
LIMIT 10";
    
    return $pdo->query($sql)->fetchAll();
}

function procesarTransaccion($transaccion_id, $accion, $usuario_id, $notas = '') {
    $pdo = getDBConnection();
    $nuevo_estado = $accion === 'aprobar' ? 'aprobada' : 'rechazada';
    $estado_boleto = $accion === 'aprobar' ? 'pagado' : 'disponible';

    try {
        $pdo->beginTransaction();

        // Actualizar transacción
        $stmt = $pdo->prepare("UPDATE transacciones
                              SET estado_compra = ?, usuario_id = ?, notas = ?, fecha_revision = NOW()
                              WHERE id = ?");
        $stmt->execute([$nuevo_estado, $usuario_id, $notas, $transaccion_id]);

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

function obtenerBoletosDisponiblesPaginados($evento_id, $pagina = 1, $por_pagina = 100) {
    $pdo = getDBConnection();
    $offset = ($pagina - 1) * $por_pagina;
    $stmt = $pdo->prepare("
        SELECT * FROM boletos 
        WHERE evento_id = ? AND estado = 'disponible'
        ORDER BY numero_boleto
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$evento_id, $por_pagina, $offset]);
    return $stmt->fetchAll();
}

function contarBoletosDisponibles($evento_id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM boletos WHERE evento_id = ? AND estado = 'disponible'");
    $stmt->execute([$evento_id]);
    return $stmt->fetchColumn();
}

function verificarDisponibilidadBoletos($evento_id, $numeros_boletos) {
    $pdo = getDBConnection();
    
    // Convertir array a string para la consulta
    $placeholders = implode(',', array_fill(0, count($numeros_boletos), '?'));
    
    $sql = "SELECT numero_boleto FROM boletos 
            WHERE evento_id = ? 
            AND numero_boleto IN ($placeholders)
            AND estado != 'disponible'";
    
    $stmt = $pdo->prepare($sql);
    $params = array_merge([$evento_id], $numeros_boletos);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// TASAS

// Obtener todos los tipos de pago
function obtener_tipos_pago($activos = true) {
    global $pdo;
    $sql = "SELECT * FROM tipos_pago";
    if ($activos) {
        $sql .= " WHERE activo = 1";
    }
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener tasa de cambio por tipo de pago
function obtener_tasa_cambio($tipo_pago_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM tasas_cambio WHERE tipo_pago_id = ?");
    $stmt->execute([$tipo_pago_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Actualizar o crear tasa de cambio
function actualizar_tasa_cambio($tipo_pago_id, $tasa) {
    global $pdo;
    
    // Verificar si ya existe una tasa para este tipo de pago
    $tasa_existente = obtener_tasa_cambio($tipo_pago_id);
    
    if ($tasa_existente) {
        // Actualizar tasa existente
        $stmt = $pdo->prepare("UPDATE tasas_cambio SET tasa = ?, fecha_actualizacion = NOW() WHERE tipo_pago_id = ?");
        return $stmt->execute([$tasa, $tipo_pago_id]);
    } else {
        // Crear nueva tasa
        $stmt = $pdo->prepare("INSERT INTO tasas_cambio (tipo_pago_id, tasa) VALUES (?, ?)");
        return $stmt->execute([$tipo_pago_id, $tasa]);
    }
}

// Obtener métodos de pago con información de tasa de cambio
function obtener_metodos_pago_con_tasas($activos = true) {
    global $pdo;
    
    $sql = "SELECT mp.*, tp.nombre AS tipo_pago_nombre, tp.codigo AS tipo_pago_codigo, 
                   tp.simbolo AS tipo_pago_simbolo, tc.tasa
            FROM metodos_pago mp
            LEFT JOIN tipos_pago tp ON mp.tipo_pago_id = tp.id
            LEFT JOIN tasas_cambio tc ON tp.id = tc.tipo_pago_id";
    
    if ($activos) {
        $sql .= " WHERE mp.activo = 1";
    }
    
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Calcular precio con tasa de cambio
function calcular_precio_con_tasa($precio_dolares, $metodo_pago_id) {
    global $pdo;
    
    $sql = "SELECT tc.tasa 
            FROM metodos_pago mp
            LEFT JOIN tipos_pago tp ON mp.tipo_pago_id = tp.id
            LEFT JOIN tasas_cambio tc ON tp.id = tc.tipo_pago_id
            WHERE mp.id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$metodo_pago_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result && $result['tasa'] !== null) {
        return $precio_dolares * $result['tasa'];
    }
    
    return $precio_dolares; // Si no hay tasa, devolver el precio original en dólares
}

