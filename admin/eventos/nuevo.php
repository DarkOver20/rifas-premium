<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
// En tu config.php o al inicio del archivo nuevo.php
define('UPLOAD_DIR', __DIR__ . '/../../uploads/'); // Ruta absoluta

// Crear el directorio si no existe
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}
require_login();
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $precio_boleto = floatval($_POST['precio_boleto']);
    $total_boletos = intval($_POST['total_boletos']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $premio_principal = trim($_POST['premio_principal']);
    $premios_secundarios = trim($_POST['premios_secundarios']);
    $estado = $_POST['estado'];
    
    // Validación de datos
    $errores = [];
    
    if (empty($titulo)) {
        $errores['titulo'] = 'El título es requerido';
    }
    
    if ($precio_boleto <= 0) {
        $errores['precio_boleto'] = 'El precio debe ser mayor a 0';
    }
    
    if ($total_boletos <= 0) {
        $errores['total_boletos'] = 'Debe haber al menos 1 boleto';
    }
    
    if (empty($fecha_inicio) || empty($fecha_fin)) {
        $errores['fechas'] = 'Las fechas son requeridas';
    } elseif (strtotime($fecha_fin) <= strtotime($fecha_inicio)) {
        $errores['fechas'] = 'La fecha de fin debe ser posterior a la de inicio';
    }
    
    // Procesar imagen
$imagen = '';
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
    $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (in_array(strtolower($extension), $extensiones_permitidas)) {
        $nombre_archivo = uniqid('evento_') . '.' . $extension;
        $ruta_destino = UPLOAD_DIR . $nombre_archivo;
        
        // Verificar si el archivo es realmente una imagen
        $check = getimagesize($_FILES['imagen']['tmp_name']);
        if ($check !== false) {
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
                $imagen = $nombre_archivo;
            } else {
                $errores['imagen'] = 'Error al subir la imagen. Verifica los permisos del directorio.';
                // Para debugging:
                error_log("Error al mover archivo. Ruta destino: " . $ruta_destino);
                error_log("Upload directory exists: " . (file_exists(UPLOAD_DIR) ? 'Yes' : 'No'));
                error_log("Upload directory writable: " . (is_writable(UPLOAD_DIR) ? 'Yes' : 'No'));
            }
        } else {
            $errores['imagen'] = 'El archivo no es una imagen válida';
        }
    } else {
        $errores['imagen'] = 'Formato de imagen no permitido. Solo se aceptan JPG, JPEG, PNG o GIF.';
    }
} else {
    $errores['imagen'] = 'La imagen es requerida';
}
    
    if (empty($errores)) {
        // Insertar en la base de datos
        $stmt = $pdo->prepare("INSERT INTO eventos 
            (titulo, descripcion, imagen, precio_boleto, total_boletos, boletos_disponibles, 
             fecha_inicio, fecha_fin, estado, premio_principal, premios_secundarios) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $titulo, $descripcion, $imagen, $precio_boleto, $total_boletos, $total_boletos,
            $fecha_inicio, $fecha_fin, $estado, $premio_principal, $premios_secundarios
        ]);
        
        $evento_id = $pdo->lastInsertId();
        
        // Generar los boletos
        generarBoletos($evento_id, $total_boletos);
        
        $_SESSION['mensaje_exito'] = 'Evento creado exitosamente';
        header('Location: ../eventos/');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Evento - Panel de Administración</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        
        <main class="admin-main">
            <header class="admin-header">
                <h1>Crear Nuevo Evento</h1>
                <div class="user-info">
                    <span><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></span>
                    <a href="../../../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </header>
            
            <div class="admin-content">
                <form action="" method="POST" enctype="multipart/form-data" class="form-eventos">
                    <div class="form-group">
                        <label for="titulo">Título del Evento</label>
                        <input type="text" id="titulo" name="titulo" required 
                               value="<?= isset($_POST['titulo']) ? htmlspecialchars($_POST['titulo']) : '' ?>">
                        <?php if (isset($errores['titulo'])): ?>
                            <span class="error"><?= $errores['titulo'] ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="4"><?= isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : '' ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="precio_boleto">Precio por boleto</label>
                            <input type="number" id="precio_boleto" name="precio_boleto" step="0.01" min="0.01" required
                                   value="<?= isset($_POST['precio_boleto']) ? htmlspecialchars($_POST['precio_boleto']) : '' ?>">
                            <?php if (isset($errores['precio_boleto'])): ?>
                                <span class="error"><?= $errores['precio_boleto'] ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="total_boletos">Total de boletos</label>
                            <input type="number" id="total_boletos" name="total_boletos" min="1" required
                                   value="<?= isset($_POST['total_boletos']) ? htmlspecialchars($_POST['total_boletos']) : '' ?>">
                            <?php if (isset($errores['total_boletos'])): ?>
                                <span class="error"><?= $errores['total_boletos'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fecha_inicio">Fecha de inicio</label>
                            <input type="datetime-local" id="fecha_inicio" name="fecha_inicio" required
                                   value="<?= isset($_POST['fecha_inicio']) ? htmlspecialchars($_POST['fecha_inicio']) : '' ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="fecha_fin">Fecha de cierre</label>
                            <input type="datetime-local" id="fecha_fin" name="fecha_fin" required
                                   value="<?= isset($_POST['fecha_fin']) ? htmlspecialchars($_POST['fecha_fin']) : '' ?>">
                        </div>
                    </div>
                    <?php if (isset($errores['fechas'])): ?>
                        <div class="error"><?= $errores['fechas'] ?></div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado" required>
                            <option value="activo" <?= (isset($_POST['estado']) && $_POST['estado'] === 'activo') ? 'selected' : '' ?>>Activo</option>
                            <option value="proximamente" <?= (isset($_POST['estado']) && $_POST['estado'] === 'proximamente') ? 'selected' : '' ?>>Próximamente</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="imagen">Imagen del evento</label>
                        <input type="file" id="imagen" name="imagen" accept="image/*" required>
                        <?php if (isset($errores['imagen'])): ?>
                            <span class="error"><?= $errores['imagen'] ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="premio_principal">Premio principal</label>
                        <textarea id="premio_principal" name="premio_principal" rows="3" required><?= isset($_POST['premio_principal']) ? htmlspecialchars($_POST['premio_principal']) : '' ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="premios_secundarios">Premios secundarios (separados por línea)</label>
                        <textarea id="premios_secundarios" name="premios_secundarios" rows="5"><?= isset($_POST['premios_secundarios']) ? htmlspecialchars($_POST['premios_secundarios']) : '' ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Evento
                        </button>
                        <a href="../eventos/" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>