<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

require_login();
require_admin();

$transaccion_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$transaccion = obtenerSolicitudPorId($transaccion_id);

if (!$transaccion) {
    header('Location: ../solicitudes/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'];
    $notas = trim($_POST['notas']);
    
    if (in_array($accion, ['aprobar', 'rechazar'])) {
        if (procesarTransaccion($transaccion_id, $accion, $_SESSION['usuario_id'], $notas)) {
            $_SESSION['mensaje_exito'] = "Transacción {$accion}ada correctamente";
            header('Location: ../solicitudes/');
            exit;
        } else {
            $error = "Error al procesar la transacción";
        }
    }
}

$boletos = obtenerBoletosPorTransaccion($transaccion_id);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Revisar Transacción #<?= $transaccion['id'] ?> - Panel de Administración</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <main class="admin-main">
            <header class="admin-header">
                <h1>Revisar Transacción #<?= $transaccion['id'] ?></h1>
                <div class="user-info">
                    <span><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></span>
                    <a href="../../../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </header>
            
            <div class="admin-content">
                <div class="solicitud-detalle">
                    <!-- Información de la transacción -->
                    <div class="info-card">
                        <h3>Información del Cliente</h3>
                        <p><strong>Nombre:</strong> <?= htmlspecialchars($transaccion['nombre']) ?></p>
                        <p><strong>Teléfono:</strong> <?= htmlspecialchars($transaccion['telefono']) ?></p>
                        <p><strong>Cédula:</strong> <?= htmlspecialchars($transaccion['cedula']) ?></p>
                        <p><strong>Estado:</strong> <?= htmlspecialchars($transaccion['estado']) ?></p>
                    </div>
                    
                    <div class="info-card">
                        <h3>Información de Pago</h3>
                        <p><strong>Método:</strong> <?= htmlspecialchars($transaccion['metodo_pago']) ?></p>
                        <p><strong>Referencia:</strong> <?= htmlspecialchars($transaccion['referencia_transaccion']) ?></p>
                        <p><strong>Monto:</strong> $<?= number_format($transaccion['monto_total'], 2) ?></p>
                        <p><strong>Estado:</strong> 
                            <span class="badge <?= $transaccion['estado_compra'] === 'aprobada' ? 'bg-success' : 
                                               ($transaccion['estado_compra'] === 'rechazada' ? 'bg-danger' : 'bg-warning') ?>">
                                <?= ucfirst($transaccion['estado_compra']) ?>
                            </span>
                        </p>
                    </div>
                    
                    <!-- Boletos asociados -->
                    <div class="boletos-grid">
                        <h3>Boletos (<?= count($boletos) ?>)</h3>
                        <?php foreach ($boletos as $boleto): ?>
                            <div class="boleto-card <?= $boleto['estado'] ?>">
                                <span class="boleto-numero"><?= $boleto['numero_boleto'] ?></span>
                                <span class="boleto-estado"><?= ucfirst($boleto['estado']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Comprobante de pago -->
                    <div class="comprobante-pago">
                        <h3>Comprobante de Pago</h3>
                        <img src="../../uploads/<?= htmlspecialchars($transaccion['comprobante_pago']) ?>" 
                             alt="Comprobante de pago" class="img-comprobante">
                    </div>
                    
                    <!-- Acciones (solo si está pendiente) -->
                    <?php if ($transaccion['estado_compra'] === 'pendiente'): ?>
                        <form method="POST" class="form-acciones">
                            <div class="form-group">
                                <label for="notas">Notas (opcional)</label>
                                <textarea id="notas" name="notas" rows="3"></textarea>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" name="accion" value="aprobar" class="btn btn-success">
                                    <i class="fas fa-check"></i> Aprobar Pago
                                </button>
                                <button type="submit" name="accion" value="rechazar" class="btn btn-danger">
                                    <i class="fas fa-times"></i> Rechazar Pago
                                </button>
                                <a href="../solicitudes/" class="btn btn-secondary">Volver</a>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="revision-info">
                            <h3>Detalles de la Revisión</h3>
                            <p><strong>Revisado por:</strong> <?= htmlspecialchars($transaccion['admin_nombre']) ?></p>
                            <p><strong>Fecha revisión:</strong> <?= date('d/m/Y H:i', strtotime($transaccion['fecha_revision'])) ?></p>
                            <p><strong>Notas:</strong> <?= $transaccion['notas'] ? nl2br(htmlspecialchars($transaccion['notas'])) : 'Ninguna' ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>