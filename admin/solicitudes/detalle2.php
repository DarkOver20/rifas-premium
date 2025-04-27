<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_login();
require_admin();
$transaccion_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$transaccion = obtenerSolicitudPorId($transaccion_id);

if (!$transaccion || !isset($transaccion['id'])) {
    $_SESSION['error'] = 'Transacción no encontrada';
    header('Location: ../solicitudes/');
    exit;
}

if (!function_exists('obtenerNombreMetodoPago')) {
    function obtenerNombreMetodoPago($metodo_pago_id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT nombre FROM metodos_pago WHERE id = ?");
        $stmt->execute([$metodo_pago_id]);
        return $stmt->fetchColumn();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'];
    $notas = trim($_POST['notas']);
    
    if (in_array($accion, ['aprobar', 'rechazar'])) {
        if (procesarTransaccion($transaccion_id, $accion, $_SESSION['usuario_id'], $notas)) {
            $_SESSION['mensaje_exito'] = "Transacción {$accion}ada correctamente";
            header('Location: /rifas-premium/dashboard');
            exit;
        } else {
            $error = "Error al procesar la transacción";
        }
    }
}

$boletos = obtenerBoletosPorTransaccion($transaccion_id);
?>

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Revisar Transacción #<?= $transaccion['id'] ?> - Panel de Administración</title>
    
    <!-- Preconexión y precarga estratégica -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Montserrat:wght@700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Montserrat:wght@700&display=swap"></noscript>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" media="print" onload="this.media='all'"/>
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#0066cc',
                            dark: '#004999',
                            light: '#3385d6'
                        },
                        secondary: '#222222',
                        accent: '#ffd700',
                        three: '#fcfcfc',
                        background: '#121212',
                        success: '#10b981',
                        warning: '#f59e0b',
                        danger: '#ef4444'
                    },
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                        heading: ['Montserrat', 'sans-serif']
                    }
                }
            }
        }
    </script>
    
    <style>
        :root {
            --primary-color: #0066cc;
            --secondary-color: #222222;
            --accent-color: #fcfcfc;
            --background-color: #121212;
            --text-color: #f8f9fa;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Montserrat', sans-serif;
        }
        
        /* Sidebar styles (copied from dashboard) */
        .sidebar {
            width: 280px;
            transition: all 0.3s ease;
            transform: translateX(-100%);
            z-index: 30;
        }
        
        .sidebar-open .sidebar {
            transform: translateX(0);
        }
        
        .sidebar-link {
            transition: all 0.3s ease;
        }
        
        .sidebar-link:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .sidebar-link.active {
            background-color: rgba(0, 102, 204, 0.2);
            border-left: 4px solid var(--primary-color);
        }
        
        /* Overlay para sidebar móvil */
        .sidebar-overlay {
            display: none;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 20;
        }
        
        .sidebar-open .sidebar-overlay {
            display: block;
        }
        /* Sidebar */
.sidebar {
    width: 280px;
    transition: all 0.3s ease;
    transform: translateX(-100%);
    z-index: 30;
}


/* Mostrar sidebar por defecto en desktop */
@media (min-width: 1024px) {
    .sidebar {
        transform: translateX(0);
    }
    
    /* Ocultar overlay en desktop */
    .sidebar-overlay {
        display: none !important;
    }
    
    /* Asegurar que el main content tenga margen */
    main {
        padding-left: 280px !important;
    }
}

.sidebar-open .sidebar {
    transform: translateX(0);
}
        
        /* Card styles */
        .info-card {
            background-color: var(--secondary-color);
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .info-card:hover {
            transform: translateY(-2px);
        }
        
        /* Boleto styles */
        .boleto-card {
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 0.5rem;
            padding: 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s ease;
        }
        
        .boleto-card:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .boleto-card.aprobado {
            border-left: 4px solid #10b981;
        }
        
        .boleto-card.pendiente {
            border-left: 4px solid #f59e0b;
        }
        
        .boleto-card.rechazado {
            border-left: 4px solid #ef4444;
        }
        
        /* Comprobante styles */
        .img-comprobante {
            max-width: 100%;
            max-height: 300px;
            border-radius: 0.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        
        .img-comprobante:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        /* Button styles */
        .btn-glow {
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .btn-glow::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to bottom right,
                rgba(255, 255, 255, 0.3),
                rgba(255, 255, 255, 0.1),
                transparent
            );
            transform: rotate(30deg);
            z-index: -1;
            transition: all 0.6s ease;
            opacity: 0;
        }
        
        .btn-glow:hover::before {
            opacity: 1;
            animation: shine 1.5s infinite;
        }
        
        @keyframes shine {
            0% { left: -50%; }
            100% { left: 150%; }
        }

    /* Agregar esto al CSS */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.7);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    
    .modal-content {
        background-color: #222;
        padding: 2rem;
        border-radius: 0.5rem;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 0 20px rgba(0,0,0,0.5);
    }
    </style>
</head>
<body class="antialiased bg-background text-white">
    <!-- Navbar -->
    <header class="fixed w-full top-0 left-0 z-40 bg-secondary/90 backdrop-blur-md shadow-lg border-b border-gray-800">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center">
                <button id="sidebar-toggle" class="lg:hidden text-white mr-4">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <a href="../" class="flex items-center gap-2 group" aria-label="Bólidos Rifas">
                    <img src="./uploads/logocolor.webp" 
                         alt="Logo Bólidos Rifas" 
                         class="h-10 w-10 rounded-lg"
                         loading="eager">
                    <span class="text-xl font-bold bg-gradient-to-r from-primary to-three bg-clip-text text-transparent">
                        Bólidos Rifas
                    </span>
                </a>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="hidden md:flex items-center gap-2">
                    <span class="text-sm text-gray-300">Bienvenido,</span>
                    <span class="font-medium"><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></span>
                </div>
                <a href="/logout" class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-800 hover:bg-gray-700 transition-all">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <div class="sidebar-overlay fixed inset-0"></div>
    <button id="sidebar-toggle" class="text-white mr-4 lg:flex lg:items-center lg:gap-2">
    <i class="fas fa-bars text-xl"></i>
    <span class="hidden lg:inline">Menú</span>
</button>
    <aside class="sidebar fixed top-0 left-0 h-full bg-secondary shadow-xl overflow-y-auto pt-16">
        <div class="p-4">
            <div class="mb-8 px-4 py-3 bg-primary/10 rounded-lg border border-primary/20">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center text-primary">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-bold text-white">Panel de Administración</h4>
                        <p class="text-xs text-gray-400">Acceso completo</p>
                    </div>
                </div>
            </div>
            
            <nav class="space-y-1">
                <a href="/rifas-premium/dashboard/" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg">
                    <i class="fas fa-tachometer-alt w-5 text-center text-primary"></i>
                    <span>Dashboard</span>
                </a>
                
                <a href="/rifas-premium/eventos/" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg">
                    <i class="fas fa-trophy w-5 text-center text-gray-400"></i>
                    <span>Eventos</span>
                </a>
                
                <a href="/rifas-premium/admin/perfil" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg">
                    <i class="fas fa-users w-5 text-center text-gray-400"></i>
                    <span>Perfil</span>
                </a>
                
                <a href="/rifas-premium/metodos" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg">
                    <i class="fas fa-dollar-sign w-5 text-center text-gray-400"></i>
                    <span>Metodos de Pagos</span>
                </a>
            </nav>
        </div>
    </aside>
    <!-- Main Content -->
    <main class="relative min-h-screen pt-16 pl-0 lg:pl-[280px] transition-all duration-300">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                    <h1 class="text-2xl md:text-3xl font-bold">
                        <span class="bg-gradient-to-r from-primary to-three bg-clip-text text-transparent">Revisar Transacción</span>
                        <span class="text-white">#<?= $transaccion['id'] ?></span>
                    </h1>
                    <a href="/rifas-premium/dashboard" class="btn-glow bg-secondary hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md hover:shadow-lg transition-all duration-300 inline-flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
                
                <?php if (isset($error)): ?>
                <div class="bg-danger/20 border border-danger/30 text-danger px-4 py-3 rounded-lg mb-4">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?= $error ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Información de la transacción -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Información del Cliente -->
                <div class="info-card">
                    <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                        <i class="fas fa-user text-primary"></i>
                        <span>Información del Cliente</span>
                    </h3>
                    <div class="space-y-3">
                        <p><strong class="text-gray-400">Nombre:</strong> <?= htmlspecialchars($transaccion['nombre']) ?></p>
                        <p><strong class="text-gray-400">Teléfono:</strong> <?= htmlspecialchars($transaccion['telefono']) ?></p>
                        <p><strong class="text-gray-400">Cédula:</strong> <?= htmlspecialchars($transaccion['cedula']) ?></p>
                        <p><strong class="text-gray-400">Estado:</strong> 
                            <span class="px-2 py-1 rounded-full text-xs font-medium 
                                <?= $transaccion['estado_compra'] === 'aprobada' ? 'bg-success/20 text-success' : 
                                   ($transaccion['estado_compra'] === 'rechazada' ? 'bg-danger/20 text-danger' : 'bg-warning/20 text-warning') ?>">
                                <?= ucfirst($transaccion['estado_compra']) ?>
                            </span>
                        </p>
                    </div>
                </div>
                
                <!-- Información de Pago -->
                <div class="info-card">
                    <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                        <i class="fas fa-credit-card text-primary"></i>
                        <span>Información de Pago</span>
                    </h3>
                    <div class="space-y-3">
                    <?php
                    // Obtener el nombre del método de pago basado en el ID
                    $metodo_pago_nombre = obtenerNombreMetodoPago($transaccion['metodo_pago_id']);
                    ?>
                    <p><strong class="text-gray-400">Método:</strong> <?= $metodo_pago_nombre ? htmlspecialchars($metodo_pago_nombre) : 'No especificado' ?></p>
                    <p><strong class="text-gray-400">Referencia:</strong> <?= isset($transaccion['referencia_transaccion']) ? htmlspecialchars($transaccion['referencia_transaccion']) : 'N/A' ?></p>
                    <p><strong class="text-gray-400">Fecha:</strong> <?= isset($transaccion['fecha_compra']) ? date('d/m/Y H:i', strtotime($transaccion['fecha_compra'])) : 'No registrada' ?></p>                    </div>
                </div>
            </div>
            
            <!-- Boletos asociados -->
            <div class="info-card mb-8">
                <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                    <i class="fas fa-ticket-alt text-primary"></i>
                    <span>Boletos Asociados (<?= count($boletos) ?>)</span>
                </h3>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                    <?php foreach ($boletos as $boleto): ?>
                        <div class="boleto-card <?= $boleto['estado'] ?>">
                            <span class="boleto-numero font-medium"><?= $boleto['numero_boleto'] ?></span>
                            <span class="boleto-estado text-xs px-2 py-1 rounded-full 
                                <?= $boleto['estado'] === 'aprobado' ? 'bg-success/20 text-success' : 
                                   ($boleto['estado'] === 'rechazado' ? 'bg-danger/20 text-danger' : 'bg-warning/20 text-warning') ?>">
                                <?= ucfirst($boleto['estado']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Comprobante de pago -->
<div class="info-card mb-8">
    <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
        <i class="fas fa-receipt text-primary"></i>
        <span>Comprobante de Pago</span>
    </h3>
    
    <?php 
    // Construir la ruta correcta al comprobante
    $comprobantePath = dirname(__DIR__) . "/uploads/" . htmlspecialchars($transaccion['comprobante_pago']);
    $publicPath = "/rifas-premium/admin/uploads/" . htmlspecialchars($transaccion['comprobante_pago']);
    
    if (isset($transaccion['comprobante_pago']) && !empty($transaccion['comprobante_pago']) && file_exists($comprobantePath)): ?>
            <img src="<?= $publicPath ?>" 
                 alt="Comprobante de pago" 
                 class="img-comprobante">
    <?php else: ?>
        <div class="bg-gray-800/50 rounded-lg p-6 text-center">
            <i class="fas fa-file-image text-4xl text-gray-600 mb-2"></i>
            <p class="text-gray-400">Comprobante no disponible</p>
        </div>
    <?php endif; ?>
</div>
            
            <!-- Acciones o Detalles de Revisión -->
            <?php if ($transaccion['estado_compra'] === 'pendiente'): ?>
                <form method="POST" class="info-card">
                    <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                        <i class="fas fa-clipboard-check text-primary"></i>
                        <span>Acciones</span>
                    </h3>
                    
                    <div class="mb-4">
                        <label for="notas" class="block text-sm font-medium text-gray-400 mb-2">Notas (opcional)</label>
                        <textarea id="notas" name="notas" rows="3" class="w-full bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="Agregar comentarios sobre esta transacción..."></textarea>
                    </div>
                    
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" name="accion" value="aprobar" class="btn-glow bg-success hover:bg-success/90 text-white px-6 py-3 rounded-lg font-bold shadow-md hover:shadow-lg transition-all duration-300 flex-1 flex items-center justify-center gap-2">
                            <i class="fas fa-check"></i> Aprobar Pago
                        </button>
                        <button type="submit" name="accion" value="rechazar" class="btn-glow bg-danger hover:bg-danger/90 text-white px-6 py-3 rounded-lg font-bold shadow-md hover:shadow-lg transition-all duration-300 flex-1 flex items-center justify-center gap-2">
                            <i class="fas fa-times"></i> Rechazar Pago
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div class="info-card">
                    <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                        <i class="fas fa-clipboard-list text-primary"></i>
                        <span>Detalles de la Revisión</span>
                    </h3>
                    
                    <div class="space-y-4">              
                        <div>
                            <p class="text-sm text-gray-400">Fecha de revisión:</p>
                            <p class="font-medium"><?= date('d/m/Y H:i', strtotime($transaccion['fecha_revision'])) ?></p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-400">Notas:</p>
                            <p class="font-medium whitespace-pre-line"><?= $transaccion['notas'] ? htmlspecialchars($transaccion['notas']) : 'Ninguna' ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Scripts -->
    <script>


        // Toggle Sidebar
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.querySelector('.sidebar');
        const sidebarOverlay = document.querySelector('.sidebar-overlay');
        const html = document.documentElement;
        
        sidebarToggle.addEventListener('click', () => {
            html.classList.toggle('sidebar-open');
        });
        
        sidebarOverlay.addEventListener('click', () => {
            html.classList.remove('sidebar-open');
        });
        
          // Modal de confirmación mejorado
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        if (!form) return;
        
        // Crear modal
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content">
                <h2 class="text-lg font-bold mb-4 text-white">Confirmación</h2>
                <p id="modal-message" class="text-gray-300 mb-6"></p>
                <div class="flex justify-center gap-4">
                    <button id="modal-confirm" class="bg-success hover:bg-success/90 text-white px-4 py-2 rounded-lg font-bold shadow-md transition-all">Confirmar</button>
                    <button id="modal-cancel" class="bg-danger hover:bg-danger/90 text-white px-4 py-2 rounded-lg font-bold shadow-md transition-all">Cancelar</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        
        // Manejar clics en los botones de acción
        const actionButtons = form.querySelectorAll('button[type="submit"]');
        
        actionButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const action = this.value;
                const message = action === 'aprobar' 
                    ? '¿Estás seguro que deseas APROBAR esta transacción?'
                    : '¿Estás seguro que deseas RECHAZAR esta transacción?';
                
                document.getElementById('modal-message').textContent = message;
                modal.style.display = 'flex';
                
                // Configurar acciones del modal
                document.getElementById('modal-confirm').onclick = function() {
                    // Crear un input oculto con la acción
                    const actionInput = document.createElement('input');
                    actionInput.type = 'hidden';
                    actionInput.name = 'accion';
                    actionInput.value = action;
                    form.appendChild(actionInput);
                    
                    modal.style.display = 'none';
                    form.submit();
                };
                
                document.getElementById('modal-cancel').onclick = function() {
                    modal.style.display = 'none';
                };
            });
        });
    });
</script>
</body>
</html>