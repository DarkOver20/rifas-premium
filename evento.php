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
        
        <?php if (is_logged_in()): ?>
            <form id="form-pago" class="form-pago" enctype="multipart/form-data">
                <input type="hidden" name="evento_id" value="<?= $evento['id'] ?>">
                
                <div class="form-group">
                    <label>Método de pago:</label>
                    <div class="metodos-pago" id="metodos-pago">
                        <!-- Métodos se cargarán por AJAX -->
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="referencia">Número de referencia:</label>
                    <input type="text" id="referencia" name="referencia" required>
                </div>
                
                <div class="form-group">
                    <label for="comprobante">Comprobante de pago (opcional):</label>
                    <input type="file" id="comprobante" name="comprobante" accept="image/*,.pdf">
                </div>
                
                <button type="submit" id="procederPagoBtn" class="btn" disabled>Confirmar Pago</button>
            </form>
        <?php else: ?>
            <div class="alert">
                <p>Debes <a href="./login.php">iniciar sesión</a> para participar en esta rifa.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="./assets/js/evento.js"></script>
</body>
</html>