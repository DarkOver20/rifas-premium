<?php
require_once './includes/config.php';
require_once './includes/functions.php';

$evento_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$evento = obtenerEvento($evento_id);

if (!$evento) {
    header('Location: index.php');
    exit;
}

$boletos_disponibles = obtenerBoletosDisponibles($evento_id);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($evento['titulo']) ?> - Rifas Premium</title>
    <link rel="stylesheet" href="./assets/css/evento.css">
    <link rel="stylesheet" href="./assets/css/main.css">
</head>
<body>
    
    <main class="evento-detalle">
        <div class="container">
            <div class="evento-header">
                <h1><?= htmlspecialchars($evento['titulo']) ?></h1>
                <div class="evento-estado <?= $evento['estado'] ?>">
                    <?= strtoupper($evento['estado']) ?>
                </div>
            </div>
            
            <div class="evento-grid">
                <div class="evento-imagen">
                    <img src="./uploads/<?= htmlspecialchars($evento['imagen']) ?>" alt="<?= htmlspecialchars($evento['titulo']) ?>">
                </div>
                
                <div class="evento-info">
                    <h2>Premio Principal</h2>
                    <p><?= nl2br(htmlspecialchars($evento['premio_principal'])) ?></p>
                    
                    <h2>Premios Secundarios</h2>
                    <p><?= nl2br(htmlspecialchars($evento['premios_secundarios'])) ?></p>
                    
                    <div class="evento-precio">
                        <span>Precio por boleto:</span>
                        <strong>$<?= number_format($evento['precio_boleto'], 2) ?></strong>
                    </div>
                    
                    <div class="evento-boletos">
                        <span>Boletos disponibles:</span>
                        <strong><?= $evento['boletos_disponibles'] ?> de <?= $evento['total_boletos'] ?></strong>
                    </div>
                    
                    <div class="evento-fechas">
                        <div>
                            <span>Fecha de inicio:</span>
                            <strong><?= date('d/m/Y', strtotime($evento['fecha_inicio'])) ?></strong>
                        </div>
                        <div>
                            <span>Fecha de cierre:</span>
                            <strong><?= date('d/m/Y', strtotime($evento['fecha_fin'])) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="seleccion-boletos">
    <h2>Selecciona tus boletos</h2>
    
    <div class="seleccion-opciones">
        <button id="seleccion-aleatoria" class="btn">Selección Aleatoria</button>
        <button id="seleccion-manual" class="btn">Selección Manual</button>
        <input type="number" id="cantidad-boletos" min="1" max="20" value="1" placeholder="Cantidad">
    </div>
    
    <div class="boletos-container">
        <?php foreach ($boletos_disponibles as $boleto): ?>
            <div class="boleto <?= $boleto['estado'] !== 'disponible' ? 'reservado' : '' ?>" 
                 data-numero="<?= $boleto['numero_boleto'] ?>">
                <?= $boleto['numero_boleto'] ?>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="resumen-compra">
        <h3>Resumen de tu compra</h3>
        <div id="boletos-seleccionados"></div>
        <div class="total">
            <span>Total a pagar:</span>
            <strong id="total-pagar">$0.00</strong>
        </div>
        
        <div class="formulario-compra-boletos">
    <h3>Información de Contacto y Pago</h3>
    <form id="formulario-pago" action="procesar_compra.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="evento_id" value="<?php echo $evento_id; ?>"> <div class="form-group">
            <label for="nombre">Nombre Completo:</label>
            <input type="text" id="nombre" name="nombre" required>
        </div>
        <div class="form-group">
            <label for="telefono">Teléfono:</label>
            <input type="tel" id="telefono" name="telefono" required>
        </div>
        <div class="form-group">
            <label for="cedula">Cédula:</label>
            <input type="text" id="cedula" name="cedula" required>
        </div>
        <div class="form-group">
            <label for="estado">Estado:</label>
            <input type="text" id="estado" name="estado" required>
        </div>
        <div class="form-group">
            <label for="referencia_pago">Referencia de Pago (Opcional):</label>
            <input type="text" id="referencia_pago" name="referencia_pago">
        </div>
        <div class="form-group">
            <label for="metodo_pago">Método de Pago:</label>
            <select id="metodo_pago" name="metodo_pago" required>
                <option value="">Selecciona un método de pago</option>
                <?php
                // Aquí deberás cargar los métodos de pago desde la base de datos
                $metodos_de_pago = obtener_metodos_pago(); // Usamos la función que ya creamos
                foreach ($metodos_de_pago as $metodo): ?>
                    <option value="<?php echo $metodo['id']; ?>" data-detalles='<?php echo htmlspecialchars($metodo['detalles']); ?>'>
                        <?php echo htmlspecialchars($metodo['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div id="detalles-metodo-pago-seleccionado" style="margin-top: 10px;">
                </div>
        </div>
        <div class="form-group">
            <label for="referencia_transaccion">Referencia del Pago (Proporcionada por el Banco):</label>
            <input type="text" id="referencia_transaccion" name="referencia_transaccion" required>
        </div>
        <div class="form-group">
            <label for="comprobante_pago">Comprobante de Pago (Imagen):</label>
            <input type="file" id="comprobante_pago" name="comprobante_pago" accept="image/*" required>
            <small>Formatos permitidos: JPG, JPEG, PNG.</small>
        </div>
        <button type="submit" class="button">Confirmar Pago</button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const metodoPagoSelect = document.getElementById('metodo_pago');
        const detallesMetodoPagoDiv = document.getElementById('detalles-metodo-pago-seleccionado');

        metodoPagoSelect.addEventListener('change', function() {
            detallesMetodoPagoDiv.innerHTML = ''; // Limpiar detalles anteriores
            const selectedOption = this.options[this.selectedIndex];
            const detallesJson = selectedOption.getAttribute('data-detalles');

            if (detallesJson) {
                try {
                    const detalles = JSON.parse(detallesJson);
                    let detallesHTML = '<ul>';
                    for (const key in detalles) {
                        if (detalles.hasOwnProperty(key)) {
                            detallesHTML += `<li><strong>${key}:</strong> ${detalles[key]}</li>`;
                        }
                    }
                    detallesHTML += '</ul>';
                    detallesMetodoPagoDiv.innerHTML = detallesHTML;
                } catch (error) {
                    console.error('Error al parsear JSON de detalles:', error);
                    detallesMetodoPagoDiv.innerHTML = '<p>Error al mostrar los detalles del método de pago.</p>';
                }
            }
        });
    });
</script>
</div>

<script src="./assets/js/evento.js"></script>
</body>
</html>