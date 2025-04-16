<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_login();
require_admin();
$errores = [];
$mensaje_exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $detalles_array = [];
    $detalles_json = '';

    if (empty($nombre)) {
        $errores['nombre'] = 'El nombre del método de pago es requerido.';
    }

    // Procesar los detalles dinámicos
    if (isset($_POST['detalle_nombre']) && is_array($_POST['detalle_nombre'])) {
        for ($i = 0; $i < count($_POST['detalle_nombre']); $i++) {
            $nombre_detalle = trim($_POST['detalle_nombre'][$i]);
            $valor_detalle = trim($_POST['detalle_valor'][$i]);
            if (!empty($nombre_detalle) && !empty($valor_detalle)) {
                $detalles_array[$nombre_detalle] = $valor_detalle;
            }
        }
        $detalles_json = json_encode($detalles_array);
    }

    // Subir el icono
    $icono = '';
    if (isset($_FILES['icono']) && $_FILES['icono']['error'] === UPLOAD_ERR_OK) {
        $nombre_archivo = $_FILES['icono']['name'];
        $extension = pathinfo($nombre_archivo, PATHINFO_EXTENSION);
        $nombre_base = uniqid('icono_') . '.' . $extension;
        $ruta_destino = UPLOAD_DIR . $nombre_base; // Asegúrate de tener UPLOAD_DIR definido en config.php

        if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
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
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO metodos_pago (nombre, detalles, icono) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $detalles_json, $icono]);

        $mensaje_exito = 'Método de pago añadido con éxito.';
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
    <div class="container">
        <h1>Añadir Nuevo Método de Pago</h1>

        <a href="/rifas-premium/metodos" class="button">Volver a la lista de métodos de pago</a>

        <?php if (!empty($errores)): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($mensaje_exito)): ?>
            <div class="success"><?php echo $mensaje_exito; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nombre">Nombre del Método de Pago:</label>
                <input type="text" id="nombre" name="nombre" required>
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