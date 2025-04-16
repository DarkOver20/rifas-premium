<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_login();
require_admin();

$eventos_activos = obtenerEventos('activo');
$eventos_finalizados = obtenerEventos('finalizado');
$solicitudes_pendientes = obtenerSolicitudesPendientes();
$total_usuarios = contarUsuarios();
$total_ventas = calcularVentasTotales();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Rifas Premium</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        
        <main class="admin-main">
            <header class="admin-header">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <span><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></span>
                    <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </header>
            
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon bg-primary">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Eventos Activos</h3>
                        <p><?= count($eventos_activos) ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Usuarios</h3>
                        <p><?= $total_usuarios ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Solicitudes Pendientes</h3>
                        <p><?= count($solicitudes_pendientes) ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-danger">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Ventas Totales</h3>
                        <p>$<?= number_format($total_ventas, 2) ?></p>
                    </div>
                </div>
            </div>
            
            <section class="dashboard-section">
    <h2>Solicitudes Recientes</h2>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Comprador</th> <!-- Cambiado de "Usuario" a "Comprador" -->
                    <th>Evento</th>
                    <th>Boletos</th>
                    <th>Monto</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($solicitudes_pendientes)): ?>
                    <tr>
                        <td colspan="7" class="text-center">No hay solicitudes pendientes</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($solicitudes_pendientes as $solicitud): ?>
                    <tr>
                        <td><?= $solicitud['id'] ?></td>
                        <td>
    <div><?= htmlspecialchars($solicitud['comprador_nombre'] ?? 'N/A') ?></div>
    <small class="text-muted">C.I. <?= htmlspecialchars($solicitud['comprador_cedula'] ?? 'N/A') ?></small>
</td>                        <td><?= htmlspecialchars($solicitud['evento_titulo'] ?? 'N/A') ?></td>
                        <td><?= $solicitud['cantidad_boletos'] ?? 0 ?></td>
                        <td>$<?= isset($solicitud['monto']) ? number_format($solicitud['monto'], 2) : '0.00' ?></td>
                        <td><?= isset($solicitud['fecha_transaccion']) ? date('d/m/Y H:i', strtotime($solicitud['fecha_transaccion'])) : 'N/A' ?></td>
                        <td>
                            <a href="./solicitudes/detalle.php?id=<?= $solicitud['id'] ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> Revisar
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
            
            <section class="dashboard-section">
                <h2>Eventos Activos</h2>
                <div class="eventos-grid">
                    <?php foreach ($eventos_activos as $evento): ?>
                    <div class="evento-card">
                        <div class="evento-header">
                            <h3><?= htmlspecialchars($evento['titulo']) ?></h3>
                            <span class="badge bg-primary"><?= $evento['boletos_disponibles'] ?> disponibles</span>
                        </div>
                        <div class="evento-body">
                            <p>Precio: $<?= number_format($evento['precio_boleto'], 2) ?></p>
                            <p>Fecha fin: <?= date('d/m/Y', strtotime($evento['fecha_fin'])) ?></p>
                        </div>
                        <div class="evento-actions">
                            <a href="eventos/editar.php?id=<?= $evento['id'] ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="eventos/boletos.php?id=<?= $evento['id'] ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-ticket-alt"></i> Boletos
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-3">
                    <a href="eventos/nuevo.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Crear Nuevo Evento
                    </a>
                </div>
            </section>
        </main>
    </div>
    
    <script src="../assets/js/admin.js"></script>
</body>
</html>