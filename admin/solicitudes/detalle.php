<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
require_login();
require_admin();

$solicitud_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$solicitud = obtenerSolicitudPorId($solicitud_id);

if (!$solicitud) {
    header('Location: ../solicitudes/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'];
    $notas = trim($_POST['notas']);
    
    if (in_array($accion, ['aprobar', 'rechazar'])) {
        $nuevo_estado = $accion === 'aprobar' ? 'aprobado' : 'rechazado';
        
        $stmt = $pdo->prepare("UPDATE transacciones SET 
            estado = ?, 
            admin_id = ?, 
            fecha_revision = NOW(), 
            notas = ?
            WHERE id = ?");
        
        $stmt->execute([
            $nuevo_estado,
            $_SESSION['usuario_id'],
            $notas,
            $solicitud_id
        ]);
        
        // Si se aprueba, marcar los boletos como pagados
        if ($accion === 'aprobar') {
            $stmt = $pdo->prepare("UPDATE boletos SET 
                estado = 'pagado', 
                fecha_pago = NOW()
                WHERE transaccion_id = ?");
            $stmt->execute([$solicitud_id]);
            
            // Enviar notificación al usuario
            enviarNotificacion(
                $solicitud['usuario_id'],
                "Tu pago ha sido aprobado",
                "Tu compra de boletos para el evento '{$solicitud['evento_titulo']}' ha sido aprobada. ¡Gracias por participar!"
            );
        } else {
            // Si se rechaza, liberar los boletos
            $stmt = $pdo->prepare("UPDATE boletos SET 
                estado = 'disponible', 
                usuario_id = NULL, 
                fecha_reserva = NULL,
                transaccion_id = NULL
                WHERE transaccion_id = ?");
            $stmt->execute([$solicitud_id]);
            
            // Enviar notificación al usuario
            enviarNotificacion(
                $solicitud['usuario_id'],
                "Tu pago ha sido rechazado",
                "Lamentamos informarte que tu compra de boletos para el evento '{$solicitud['evento_titulo']}' ha sido rechazada. Razón: {$notas}"
            );
        }
        
        $_SESSION['mensaje_exito'] = "Solicitud {$nuevo_estado} correctamente";
        header('Location: ../solicitudes/');
        exit;
    }
}

// Obtener los boletos asociados a esta solicitud
$boletos = obtenerBoletosPorTransaccion($solicitud_id);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Revisar Solicitud - Panel de Administración</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        
        <main class="admin-main">
            <header class="admin-header">
                <h1>Revisar Solicitud #<?= $solicitud['id'] ?></h1>
                <div class="user-info">
                    <span><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></span>
                    <a href="../../../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </header>
            
            <div class="admin-content">
                <div class="solicitud-detalle">
                    <div class="solicitud-info">
                        <div class="info-card">
                            <h3>Información del Usuario</h3>
                            <p><strong>Nombre:</strong> <?= htmlspecialchars($solicitud['usuario_nombre']) ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($solicitud['usuario_email']) ?></p>
                            <p><strong>Teléfono:</strong> <?= htmlspecialchars($solicitud['usuario_telefono']) ?></p>
                        </div>
                        
                        <div class="info-card">
                            <h3>Información del Evento</h3>
                            <p><strong>Evento:</strong> <?= htmlspecialchars($solicitud['evento_titulo']) ?></p>
                            <p><strong>Precio por boleto:</strong> $<?= number_format($solicitud['evento_precio'], 2) ?></p>
                            <p><strong>Cantidad de boletos:</strong> <?= $solicitud['cantidad_boletos'] ?></p>
                            <p><strong>Total pagado:</strong> $<?= number_format($solicitud['monto'], 2) ?></p>
                        </div>
                        
                        <div class="info-card">
                            <h3>Información de Pago</h3>
                            <p><strong>Método:</strong> <?= ucfirst($solicitud['metodo_pago']) ?></p>
                            <p><strong>Referencia:</strong> <?= htmlspecialchars($solicitud['referencia']) ?></p>
                            <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($solicitud['fecha_transaccion'])) ?></p>
                            <p><strong>Estado:</strong> 
                                <span class="badge <?= $solicitud['estado'] === 'aprobado' ? 'bg-success' : ($solicitud['estado'] === 'rechazado' ? 'bg-danger' : 'bg-warning') ?>">
                                    <?= ucfirst($solicitud['estado']) ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="solicitud-boletos">
                        <h3>Boletos Seleccionados</h3>
                        <div class="boletos-grid">
                            <?php foreach ($boletos as $boleto): ?>
                                <div class="boleto-card <?= $boleto['estado'] ?>">
                                    <span class="boleto-numero"><?= $boleto['numero_boleto'] ?></span>
                                    <span class="boleto-estado"><?= ucfirst($boleto['estado']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <?php if ($solicitud['estado'] === 'pendiente'): ?>
                    <div class="solicitud-acciones">
                        <h3>Acciones</h3>
                        <form method="POST" class="form-acciones">
                            <div class="form-group">
                                <label for="notas">Notas (opcional)</label>
                                <textarea id="notas" name="notas" rows="3" placeholder="Agregar comentarios sobre la revisión..."></textarea>
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
                    </div>
                    <?php else: ?>
                    <div class="solicitud-revision">
                        <h3>Detalles de la Revisión</h3>
                        <p><strong>Revisado por:</strong> <?= htmlspecialchars($solicitud['admin_nombre']) ?></p>
                        <p><strong>Fecha revisión:</strong> <?= date('d/m/Y H:i', strtotime($solicitud['fecha_revision'])) ?></p>
                        <p><strong>Notas:</strong> <?= $solicitud['notas'] ? nl2br(htmlspecialchars($solicitud['notas'])) : 'Ninguna' ?></p>
                        
                        <div class="text-center mt-3">
                            <a href="../solicitudes/" class="btn btn-primary">Volver a Solicitudes</a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>