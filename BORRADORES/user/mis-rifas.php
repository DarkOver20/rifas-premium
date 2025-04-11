<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_login();

$usuario_id = $_SESSION['usuario_id'];
$rifas_participando = obtenerRifasUsuario($usuario_id);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Rifas - Rifas Premium</title>
    <link rel="stylesheet" href="../../assets/css/user.css">
</head>
<body>
    
    <main class="user-container">
        <div class="user-sidebar">
            <div class="user-avatar">
                <img src="../../uploads/avatars/<?= $_SESSION['usuario_avatar'] ?? 'default.jpg' ?>" alt="Avatar">
                <h3><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></h3>
            </div>
            
            <nav class="user-menu">
                <a href="perfil.php"><i class="fas fa-user"></i> Mi Perfil</a>
                <a href="mis-rifas.php" class="active"><i class="fas fa-ticket-alt"></i> Mis Rifas</a>
                <a href="notificaciones.php"><i class="fas fa-bell"></i> Notificaciones</a>
                <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </nav>
        </div>
        
        <div class="user-main">
            <h1>Mis Rifas</h1>
            
            <?php if (isset($_SESSION['exito'])): ?>
                <div class="flash-message success">
                    <?= $_SESSION['exito'] ?>
                    <?php unset($_SESSION['exito']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="flash-message error">
                    <?= $_SESSION['error'] ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <div class="user-content">
                <?php if (empty($rifas_participando)): ?>
                    <div class="empty-state">
                        <i class="fas fa-ticket-alt"></i>
                        <h3>No estás participando en ninguna rifa actualmente</h3>
                        <p>Visita nuestra página de eventos para encontrar rifas emocionantes en las que puedas participar.</p>
                        <a href="../../eventos.php" class="btn btn-primary">Ver Eventos</a>
                    </div>
                <?php else: ?>
                    <div class="rifas-grid">
                        <?php foreach ($rifas_participando as $rifa): ?>
                            <div class="rifa-card">
                                <div class="rifa-header">
                                    <h3><?= htmlspecialchars($rifa['titulo']) ?></h3>
                                    <span class="badge <?= $rifa['estado_transaccion'] === 'aprobado' ? 'bg-success' : 'bg-warning' ?>">
                                        <?= ucfirst($rifa['estado_transaccion']) ?>
                                    </span>
                                </div>
                                
                                <div class="rifa-body">
                                    <p><strong>Boletos:</strong> 
                                        <?php foreach (explode(',', $rifa['numeros_boletos']) as $numero): ?>
                                            <span class="boleto-numero <?= $rifa['estado_boleto'] === 'ganador' ? 'ganador' : '' ?>">
                                                <?= $numero ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </p>
                                    
                                    <p><strong>Fecha de compra:</strong> <?= date('d/m/Y H:i', strtotime($rifa['fecha_transaccion'])) ?></p>
                                    <p><strong>Monto total:</strong> $<?= number_format($rifa['monto'], 2) ?></p>
                                    
                                    <?php if ($rifa['estado_transaccion'] === 'pendiente'): ?>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-info-circle"></i>
                                            Tu pago está pendiente de revisión por un administrador.
                                        </div>
                                    <?php elseif ($rifa['estado_boleto'] === 'ganador'): ?>
                                        <div class="alert alert-success">
                                            <i class="fas fa-trophy"></i>
                                            ¡Felicidades! Has ganado en esta rifa. Nos contactaremos contigo pronto.
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="rifa-footer">
                                    <a href="../evento.php?id=<?= $rifa['evento_id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> Ver Evento
                                    </a>
                                    
                                    <?php if ($rifa['estado_transaccion'] === 'pendiente'): ?>
                                        <a href="#" class="btn btn-sm btn-secondary" onclick="cancelarSolicitud(<?= $rifa['transaccion_id'] ?>)">
                                            <i class="fas fa-times"></i> Cancelar
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <script src="../../assets/js/user.js"></script>
    <script>
    function cancelarSolicitud(transaccion_id) {
        if (confirm('¿Estás seguro de que deseas cancelar esta solicitud de compra?')) {
            fetch(`cancelar-solicitud.php?id=${transaccion_id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error al cancelar la solicitud');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            });
        }
    }
    </script>
</body>
</html>