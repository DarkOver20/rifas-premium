<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Obtener eventos directamente desde PHP (opcional, puedes usar solo AJAX si prefieres)
$eventos_activos = obtenerEventos('activo');
$eventos_finalizados = obtenerEventos('finalizado');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rifas Premium - Los mejores sorteos</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <header class="hero">
        <div class="container">
            <h1>Rifas Premium</h1>
            <p>Participa en los mejores sorteos con premios increíbles</p>
        </div>
    </header>

    <section class="eventos-section">
        <div class="container">
            <h2>Eventos Activos</h2>
            <div class="eventos-grid" id="eventos-activos">
                <?php if (!empty($eventos_activos)): ?>
                    <?php foreach ($eventos_activos as $evento): ?>
                        <div class="evento-card">
                            <img src="uploads/<?= htmlspecialchars($evento['imagen']) ?>" alt="<?= htmlspecialchars($evento['titulo']) ?>" class="evento-img">
                            <div class="evento-content">
                                <h3 class="evento-titulo"><?= htmlspecialchars($evento['titulo']) ?></h3>
                                <div class="evento-estado <?= $evento['estado'] ?>">
                                    <?= strtoupper($evento['estado']) ?>
                                </div>
                                <p class="evento-descripcion"><?= htmlspecialchars(substr($evento['descripcion'], 0, 100)) ?>...</p>
                                <p class="evento-precio">Precio por boleto: $<?= number_format($evento['precio_boleto'], 2) ?></p>
                                <p>Boletos disponibles: <?= $evento['boletos_disponibles'] ?>/<?= $evento['total_boletos'] ?></p>
                                <a href="evento.php?id=<?= $evento['id'] ?>" class="btn">Ver detalles</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No hay eventos activos en este momento.</p>
                <?php endif; ?>
            </div>

            <h2>Eventos Finalizados</h2>
            <div class="eventos-grid" id="eventos-finalizados">
                <?php if (!empty($eventos_finalizados)): ?>
                    <?php foreach ($eventos_finalizados as $evento): ?>
                        <div class="evento-card">
                            <img src="uploads/<?= htmlspecialchars($evento['imagen']) ?>" alt="<?= htmlspecialchars($evento['titulo']) ?>" class="evento-img">
                            <div class="evento-content">
                                <h3 class="evento-titulo"><?= htmlspecialchars($evento['titulo']) ?></h3>
                                <div class="evento-estado <?= $evento['estado'] ?>">
                                    <?= strtoupper($evento['estado']) ?>
                                </div>
                                <p class="evento-descripcion"><?= htmlspecialchars(substr($evento['descripcion'], 0, 100)) ?>...</p>
                                <p class="evento-precio">Precio por boleto: $<?= number_format($evento['precio_boleto'], 2) ?></p>
                                <a href="evento.php?id=<?= $evento['id'] ?>" class="btn">Ver detalles</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No hay eventos finalizados recientemente.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="faq-section">
        <div class="container">
            <h2>Preguntas Frecuentes</h2>
            <div class="faq-container">
                <div class="faq-item">
                    <div class="faq-question">¿Cuántos boletos mínimos necesito para participar?</div>
                    <div class="faq-answer">Puedes participar con solo 1 boleto. No hay mínimo requerido.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">¿Cómo selecciono mis boletos?</div>
                    <div class="faq-answer">Puedes elegirlos manualmente o usar nuestro sistema de selección aleatoria.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">¿Qué métodos de pago aceptan?</div>
                    <div class="faq-answer">Aceptamos transferencias bancarias, pago móvil y efectivo en algunos casos.</div>
                </div>
            </div>
        </div>
    </section>

    <script src="./assets/js/main.js"></script>
</body>
</html>