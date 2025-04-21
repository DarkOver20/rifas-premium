<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

require_login();
require_admin();

$tipos_pago = obtener_tipos_pago();
$tasas = [];

// Obtener tasas actuales
foreach ($tipos_pago as $tipo) {
    $tasa = obtener_tasa_cambio($tipo['id']);
    if ($tasa) {
        $tasas[$tipo['id']] = $tasa;
    }
}

// Procesar actualización de tasas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($tipos_pago as $tipo) {
        $campo_tasa = 'tasa_' . $tipo['id'];
        if (isset($_POST[$campo_tasa])) {
            $tasa = (float)$_POST[$campo_tasa];
            if ($tasa > 0) {
                actualizar_tasa_cambio($tipo['id'], $tasa);
            }
        }
    }

    $_SESSION['mensaje_exito'] = 'Tasas de cambio actualizadas correctamente.';
    header("Location: tasas.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrar Tasas de Cambio</title>
</head>
<body>
    <h1>Administrar Tasas de Cambio</h1>
    
    <?php if (isset($_SESSION['mensaje_exito'])): ?>
        <div class="success"><?= $_SESSION['mensaje_exito'] ?></div>
        <?php unset($_SESSION['mensaje_exito']); ?>
    <?php endif; ?>
    
    <form method="POST">
        <table>
            <thead>
                <tr>
                    <th>Tipo de Pago</th>
                    <th>Código</th>
                    <th>Tasa Actual (1 USD = ?)</th>
                    <th>Última Actualización</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tipos_pago as $tipo): ?>
                    <tr>
                        <td><?= htmlspecialchars($tipo['nombre']) ?></td>
                        <td><?= htmlspecialchars($tipo['codigo']) ?></td>
                        <td>
                            <input type="number" step="0.01" min="0.01" 
                                   name="tasa_<?= $tipo['id'] ?>" 
                                   value="<?= isset($tasas[$tipo['id']]) ? $tasas[$tipo['id']]['tasa'] : '' ?>"
                                   required>
                        </td>
                        <td>
                            <?= isset($tasas[$tipo['id']]) ? 
                                date('d/m/Y H:i', strtotime($tasas[$tipo['id']]['fecha_actualizacion'])) : 
                                'Nunca actualizado' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <button type="submit">Actualizar Tasas</button>
    </form>
</body>
</html>