<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_login();
require_admin();

$errores = [];
$mensaje_exito = '';

// Obtener tipos de pago para el select
$tipos_pago = obtener_tipos_pago();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $tipo_pago_id = isset($_POST['tipo_pago_id']) && $_POST['tipo_pago_id'] ? (int)$_POST['tipo_pago_id'] : null;
    
    // Procesar los detalles dinámicos
    $detalles_array = [];
    if (isset($_POST['detalle_nombre']) && is_array($_POST['detalle_nombre'])) {
        for ($i = 0; $i < count($_POST['detalle_nombre']); $i++) {
            $nombre_detalle = trim($_POST['detalle_nombre'][$i]);
            $valor_detalle = trim($_POST['detalle_valor'][$i]);
            if (!empty($nombre_detalle) && !empty($valor_detalle)) {
                $detalles_array[$nombre_detalle] = $valor_detalle;
            }
        }
    }
    $detalles_json = json_encode($detalles_array);

    // Validaciones
    if (empty($nombre)) {
        $errores['nombre'] = 'El nombre del método de pago es requerido.';
    }

    // Procesar icono
    $icono = '';
    if (isset($_FILES['icono']) && $_FILES['icono']['error'] === UPLOAD_ERR_OK) {
        $nombre_archivo = $_FILES['icono']['name'];
        $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
        $nombre_base = uniqid('icono_') . '.' . $extension;
        $ruta_destino = UPLOAD_DIR . $nombre_base;
        
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            if (move_uploaded_file($_FILES['icono']['tmp_name'], $ruta_destino)) {
                $icono = 'admin/uploads/' . $nombre_base;
            } else {
                $errores['icono'] = 'Error al subir el icono.';
            }
        } else {
            $errores['icono'] = 'Formato de icono no válido (solo JPG, JPEG, PNG, GIF).';
        }
    }

    if (empty($errores)) {
        try {
            global $pdo;
            
            if (isset($_GET['editar']) && is_numeric($_GET['editar'])) {
                // Editar método existente
                $id = (int)$_GET['editar'];
                $stmt = $pdo->prepare("UPDATE metodos_pago SET nombre = ?, detalles = ?, icono = ?, tipo_pago_id = ? WHERE id = ?");
                $stmt->execute([$nombre, $detalles_json, $icono, $tipo_pago_id, $id]);
                $mensaje_exito = 'Método de pago actualizado con éxito.';
            } else {
                // Crear nuevo método
                $stmt = $pdo->prepare("INSERT INTO metodos_pago (nombre, detalles, icono, tipo_pago_id) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nombre, $detalles_json, $icono, $tipo_pago_id]);
                $mensaje_exito = 'Método de pago añadido con éxito.';
            }
            
            header("Location: index.php?exito=" . urlencode($mensaje_exito));
            exit();
        } catch (PDOException $e) {
            $errores['general'] = 'Error al guardar el método de pago: ' . $e->getMessage();
        }
    }
}

// Si estamos editando, cargar los datos existentes
$metodo_actual = null;
if (isset($_GET['editar']) && is_numeric($_GET['editar'])) {
    $id = (int)$_GET['editar'];
    $stmt = $pdo->prepare("SELECT * FROM metodos_pago WHERE id = ?");
    $stmt->execute([$id]);
    $metodo_actual = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$metodo_actual) {
        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir Método de Pago - Panel de Administración</title>
    <link rel="stylesheet" href="../../css/admin.css"> <style>
        .detalles-container {
            margin-bottom: 15px;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
        }
        .detalle-par {
            display: flex;
            gap: 10px;
            margin-bottom: 5px;
        }
        .detalle-par input[type="text"] {
            flex-grow: 1;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        .agregar-detalle {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        .agregar-detalle:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1><?= isset($metodo_actual) ? 'Editar' : 'Añadir' ?> Método de Pago</h1>
    
    <?php if (!empty($errores)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <div>
            <label for="nombre">Nombre del Método de Pago:</label>
            <input type="text" id="nombre" name="nombre" required 
                   value="<?= isset($metodo_actual['nombre']) ? htmlspecialchars($metodo_actual['nombre']) : '' ?>">
        </div>
        
        <div>
            <label for="tipo_pago_id">Tipo de Pago (Moneda):</label>
            <select id="tipo_pago_id" name="tipo_pago_id">
                <option value="">Dólares (USD) - Sin conversión</option>
                <?php foreach ($tipos_pago as $tipo): ?>
                    <option value="<?= $tipo['id'] ?>" 
                        <?= (isset($metodo_actual['tipo_pago_id']) && $metodo_actual['tipo_pago_id'] == $tipo['id'] ? 'selected' : '') ?>>
                        <?= htmlspecialchars($tipo['nombre']) ?> (<?= $tipo['codigo'] ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
                <small>Selecciona un tipo de pago si requiere conversión de moneda</small>
            </div>

            <div id="detalles-dinamicos">
                <label>Detalles Adicionales:</label>
                <div class="detalles-container">
                    <div class="detalle-par">
                        <input type="text" name="detalle_nombre[]" placeholder="Nombre del campo (Ej: Teléfono, Correo, RUT)">
                        <input type="text" name="detalle_valor[]" placeholder="Valor del campo">
                    </div>
                </div>
                <button type="button" id="btn-agregar-detalle" class="agregar-detalle">Añadir Otro Detalle</button>
            </div>

            <div class="form-group">
                <label for="icono">Icono del Método de Pago:</label>
                <input type="file" id="icono" name="icono">
                <small>Formatos permitidos: JPG, JPEG, PNG, GIF.</small>
            </div>

            <button type="submit" class="button">Guardar Método de Pago</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const detallesContainer = document.getElementById('detalles-dinamicos');
            const btnAgregarDetalle = document.getElementById('btn-agregar-detalle');

            btnAgregarDetalle.addEventListener('click', function() {
                const nuevoDetalle = document.createElement('div');
                nuevoDetalle.classList.add('detalles-container');
                nuevoDetalle.innerHTML = `
                    <div class="detalle-par">
                        <input type="text" name="detalle_nombre[]" placeholder="Nombre del campo (Ej: Teléfono, Correo, RUT)">
                        <input type="text" name="detalle_valor[]" placeholder="Valor del campo">
                    </div>
                `;
                detallesContainer.insertBefore(nuevoDetalle, btnAgregarDetalle);
            });
        });
    </script>
</body>
</html>