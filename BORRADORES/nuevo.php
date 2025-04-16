<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

require_login();
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar y validar datos usando filter_input con filtros actuales
    $titulo = trim(filter_input(INPUT_POST, 'titulo', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW));
    $slogan = trim(filter_input(INPUT_POST, 'slogan', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW));
    $descripcion = trim(filter_input(INPUT_POST, 'descripcion', FILTER_UNSAFE_RAW));
    $precio_boleto = floatval(filter_input(INPUT_POST, 'precio_boleto', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
    $total_boletos = intval(filter_input(INPUT_POST, 'total_boletos', FILTER_SANITIZE_NUMBER_INT));
    $fecha_inicio = filter_input(INPUT_POST, 'fecha_inicio', FILTER_UNSAFE_RAW);
    $fecha_fin = filter_input(INPUT_POST, 'fecha_fin', FILTER_UNSAFE_RAW);
    $premio_principal = trim(filter_input(INPUT_POST, 'premio_principal', FILTER_UNSAFE_RAW));
    $estado = filter_input(INPUT_POST, 'estado', FILTER_UNSAFE_RAW);
    
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
    } elseif ($total_boletos > 100000) {
        $errores['total_boletos'] = 'Máximo 100,000 boletos por evento';
    }
    
    if (empty($fecha_inicio) || empty($fecha_fin)) {
        $errores['fechas'] = 'Las fechas son requeridas';
    } elseif (strtotime($fecha_fin) <= strtotime($fecha_inicio)) {
        $errores['fechas'] = 'La fecha de fin debe ser posterior a la de inicio';
    }
    
    // Procesar imagen
    $imagen = '';
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        $extensiones_permitidas = ['jpg', 'jpeg', 'png'];
        
        if (in_array($extension, $extensiones_permitidas)) {
            $nombre_archivo = uniqid('evento_') . '.' . $extension;
            $ruta_destino = UPLOAD_DIR . $nombre_archivo;
            
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
                $imagen = $nombre_archivo;
                
                // Intentar optimizar si GD está instalado
                if (function_exists('imagecreatefromjpeg')) {
                    optimizarImagen($ruta_destino, 1200, 800);
                }
            } else {
                $errores['imagen'] = 'Error al subir la imagen. Verifica los permisos del directorio.';
                error_log("Error al mover archivo: " . print_r(error_get_last(), true));
            }
        } else {
            $errores['imagen'] = 'Formato de imagen no permitido. Solo se aceptan JPG, JPEG, PNG.';
        }
    } else {
        $errores['imagen'] = 'La imagen es requerida';
    }
    
    if (empty($errores)) {
        $pdo = getDBConnection();
        
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("INSERT INTO eventos 
                (titulo, slogan, descripcion, imagen, precio_boleto, total_boletos, boletos_disponibles, 
                 fecha_inicio, fecha_fin, estado, premio_principal) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $titulo, $slogan, $descripcion, $imagen, $precio_boleto, $total_boletos, $total_boletos,
                $fecha_inicio, $fecha_fin, $estado, $premio_principal
            ]);
            
            $evento_id = $pdo->lastInsertId();
            generarBoletosLotes($evento_id, $total_boletos);
            
            $pdo->commit();
            
            $_SESSION['mensaje_exito'] = 'Evento creado exitosamente con ' . number_format($total_boletos) . ' boletos';
            header('Location: ../eventos/');
            exit;
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errores['general'] = 'Error al crear el evento: ' . $e->getMessage();
            error_log("Error al crear evento: " . $e->getMessage());
            
            if (!empty($imagen)) {
                @unlink(UPLOAD_DIR . $imagen);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Evento - Panel de Administración</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                <?php if (isset($errores['general'])): ?>
                    <div class="alert alert-danger"><?= $errores['general'] ?></div>
                <?php endif; ?>
                
                <form action="" method="POST" enctype="multipart/form-data" class="form-eventos">
                    <div class="form-group">
                        <label for="titulo">Título del Evento*</label>
                        <input type="text" id="titulo" name="titulo" required 
                               value="<?= isset($_POST['titulo']) ? htmlspecialchars($_POST['titulo']) : '' ?>">
                        <?php if (isset($errores['titulo'])): ?>
                            <span class="error"><?= $errores['titulo'] ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="slogan">Slogan (opcional)</label>
                        <input type="text" id="slogan" name="slogan"
                               value="<?= isset($_POST['slogan']) ? htmlspecialchars($_POST['slogan']) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="4"><?= isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : '' ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="precio_boleto">Precio por boleto*</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" id="precio_boleto" name="precio_boleto" step="0.01" min="0.01" required
                                       value="<?= isset($_POST['precio_boleto']) ? htmlspecialchars($_POST['precio_boleto']) : '' ?>">
                            </div>
                            <?php if (isset($errores['precio_boleto'])): ?>
                                <span class="error"><?= $errores['precio_boleto'] ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="total_boletos">Total de boletos*</label>
                            <input type="number" id="total_boletos" name="total_boletos" min="1" max="100000" required
                                   value="<?= isset($_POST['total_boletos']) ? htmlspecialchars($_POST['total_boletos']) : '' ?>">
                            <?php if (isset($errores['total_boletos'])): ?>
                                <span class="error"><?= $errores['total_boletos'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fecha_inicio">Fecha de inicio*</label>
                            <input type="datetime-local" id="fecha_inicio" name="fecha_inicio" required
                                   value="<?= isset($_POST['fecha_inicio']) ? htmlspecialchars($_POST['fecha_inicio']) : '' ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="fecha_fin">Fecha de cierre*</label>
                            <input type="datetime-local" id="fecha_fin" name="fecha_fin" required
                                   value="<?= isset($_POST['fecha_fin']) ? htmlspecialchars($_POST['fecha_fin']) : '' ?>">
                        </div>
                    </div>
                    <?php if (isset($errores['fechas'])): ?>
                        <div class="error"><?= $errores['fechas'] ?></div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="estado">Estado*</label>
                        <select id="estado" name="estado" required>
                            <option value="activo" <?= (isset($_POST['estado']) && $_POST['estado'] === 'activo') ? 'selected' : '' ?>>Activo</option>
                            <option value="proximamente" <?= (isset($_POST['estado']) && $_POST['estado'] === 'proximamente') ? 'selected' : '' ?>>Próximamente</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="imagen">Imagen del evento*</label>
                        <input type="file" id="imagen" name="imagen" accept="image/*" required>
                        <small class="text-muted">Formatos permitidos: JPG, PNG. Tamaño recomendado: 1200x800px</small>
                        <?php if (isset($errores['imagen'])): ?>
                            <span class="error"><?= $errores['imagen'] ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="premio_principal">Premio principal*</label>
                        <textarea id="premio_principal" name="premio_principal" rows="3" required><?= isset($_POST['premio_principal']) ? htmlspecialchars($_POST['premio_principal']) : '' ?></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Evento
                        </button>
                        <a href="../eventos/" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>
    
    <script>
        // Validación en cliente para fechas
        document.addEventListener('DOMContentLoaded', function() {
            const fechaInicio = document.getElementById('fecha_inicio');
            const fechaFin = document.getElementById('fecha_fin');
            
            if (fechaInicio && fechaFin) {
                fechaInicio.addEventListener('change', function() {
                    fechaFin.min = this.value;
                });
            }
        });
    </script>
</body>
</html>