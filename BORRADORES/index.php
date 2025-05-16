
<p class="evento-precio">$<?= number_format($evento['precio_boleto'], 2) ?> por boleto</p>
                                <a href="evento.php?id=<?= $evento['id'] ?>" class="btn">VER DETALLES</a>



                                // Función optimizada en functions.php
function obtenerBoletosDisponiblesOptimizado($evento_id) {
    $pdo = getDBConnection();
    $query = "SELECT numero_boleto, estado FROM boletos 
              WHERE evento_id = ? AND estado = 'disponible'
              ORDER BY numero_boleto ASC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$evento_id]);
    $boletos = $stmt->fetchAll();
    
    return $boletos;
}

<?php
require_once './includes/config.php';
require_once './includes/functions.php';

// Optimización: Cache de consultas
$evento_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$evento = obtenerEvento($evento_id);

if (!$evento) {
    header('Location: index.php');
    exit;
}

// Consulta optimizada para boletos
$boletos_disponibles = obtenerBoletosDisponiblesOptimizado($evento_id);


?>

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= htmlspecialchars($evento['titulo']) ?> | Rifas Premium</title>
    <meta name="description" content="<?= htmlspecialchars($evento['descripcion']) ?>"/>

    <!-- Preload critical resources -->
    <link rel="preload" href="https://fonts.googleapis.com" as="font" crossorigin>
    <link rel="preload" href="https://fonts.gstatic.com" as="font" crossorigin>
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS with custom config -->
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
                    secondary: '#1a1a1a',
                    accent: '#ffd700',
                    background: '#0f0f0f',
                    success: '#10b981',
                    danger: '#ef4444',
                    warning: '#f59e0b',
                    info: '#3b82f6'
                },
                fontFamily: {
                    sans: ['Poppins', 'sans-serif'],
                    heading: ['Montserrat', 'sans-serif']
                },
                animation: {
                    'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    'bounce-slow': 'bounce 2s infinite'
                }
            }
        },
        corePlugins: {
            container: false
        }
    }
    </script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <style>
    :root {
        --color-primary: #0066cc;
        --color-accent: #ffd700;
    }
    
    /* Smooth transitions */
    * {
        transition: all 0.2s ease;
    }
    
    /* Scrollbar styling */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    ::-webkit-scrollbar-track {
        background: #1a1a1a;
    }
    ::-webkit-scrollbar-thumb {
        background: var(--color-primary);
        border-radius: 4px;
    }
    
    /* Ticket styles */
    .ticket {
        position: relative;
        height: 2.5rem;
        width: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 500;
        cursor: pointer;
        background-color: #2d3748;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .ticket:hover {
        transform: scale(1.05);
        box-shadow: 0 0 0 2px rgba(0, 102, 204, 0.5);
    }
    
    .ticket.selected {
        background-color: var(--color-primary);
        color: white;
        transform: scale(1.05);
        box-shadow: 0 0 0 2px var(--color-primary), 0 0 15px rgba(0, 102, 204, 0.5);
    }
    
    .ticket.sold {
        background-color: rgba(239, 68, 68, 0.2);
        color: #ef4444;
        cursor: not-allowed;
        opacity: 0.7;
    }
    
    /* Countdown styles */
    .countdown-item {
        position: relative;
        min-width: 5rem;
    }
    
    .countdown-item:not(:last-child):after {
        content: ":";
        position: absolute;
        right: -0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--color-primary);
        font-weight: bold;
        font-size: 1.5rem;
    }
    
    /* Progress bar */
    .progress-bar {
        height: 0.5rem;
        border-radius: 0.25rem;
        background-color: #2d3748;
        overflow: hidden;
    }
    
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--color-primary), var(--color-accent));
        transition: width 0.5s ease;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .ticket {
            height: 2rem;
            width: 2rem;
            font-size: 0.65rem;
        }
        
        .countdown-item {
            min-width: 4rem;
        }
    }
    
    /* Animation for selected tickets */
    @keyframes ticketSelected {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
    
    .ticket-selected-animation {
        animation: ticketSelected 0.5s ease;
    }
    
    /* Loading skeleton */
    .skeleton {
        background-color: #2d3748;
        background-image: linear-gradient(90deg, #2d3748, #4a5568, #2d3748);
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite;
    }
    
    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }
    </style>
</head>
<body class="antialiased bg-background text-gray-100 font-sans min-h-screen flex flex-col">
    <!-- Header -->
    <header class="fixed w-full top-0 left-0 z-50 bg-secondary/90 backdrop-blur-md shadow-lg border-b border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="index.php" class="flex items-center gap-2 group" aria-label="Rifas Premium">
                    <img src="./assets/img/prueba" alt="Logo Rifas Premium" class="h-10 w-10 transition-all duration-300 group-hover:rotate-12" loading="eager">
                    <span class="text-2xl font-bold bg-gradient-to-r from-primary to-accent bg-clip-text text-transparent font-heading">
                        Rifas Premium
                    </span>
                </a>
                
                <nav class="hidden md:flex items-center gap-6">
                    <a href="index.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium flex items-center gap-2">
                        <i class="fas fa-home text-sm"></i>
                        Inicio
                    </a>
                    <a href="#rifas" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium flex items-center gap-2">
                        <i class="fas fa-trophy text-sm"></i>
                        Rifas
                    </a>
                </nav>
                
                <div class="flex items-center gap-4">
                    <button id="mobile-menu-button" class="md:hidden text-gray-300 hover:text-white focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow pt-20 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Left Column - Event Info -->
                <div class="lg:col-span-4">
                    <div class="bg-secondary rounded-xl shadow-lg overflow-hidden border border-gray-800 sticky top-24">
                        <!-- Event Image -->
                        <div class="relative h-64 overflow-hidden">
                            <img src="./uploads/<?= htmlspecialchars($evento['imagen']) ?>" 
                                alt="<?= htmlspecialchars($evento['titulo']) ?>" 
                                class="w-full h-full object-cover transition-transform duration-500 hover:scale-105"
                                loading="lazy">
                            <div class="absolute top-4 right-4 bg-primary text-white px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1">
                                <i class="fas fa-bolt"></i>
                                <span><?= strtoupper($evento['estado']) ?></span>
                            </div>
                        </div>
                        
                        <!-- Event Details -->
                        <div class="p-6">
                            <h1 class="text-2xl font-bold text-white mb-4 font-heading"><?= htmlspecialchars($evento['titulo']) ?></h1>
                            
                            <!-- Event Dates -->
                            <div class="space-y-3 mb-6">
                                <div class="flex items-center text-gray-300">
                                    <i class="fas fa-calendar-alt w-6 text-primary"></i>
                                    <span>Inicio: <?= date('d F Y', strtotime($evento['fecha_inicio'])) ?></span>
                                </div>
                                <div class="flex items-center text-gray-300">
                                    <i class="fas fa-calendar-check w-6 text-primary"></i>
                                    <span>Cierre: <?= date('d F Y', strtotime($evento['fecha_fin'])) ?></span>
                                </div>
                            </div>
                            
                            <!-- Main Prize -->
                            <div class="mb-6">
                                <h3 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
                                    <i class="fas fa-trophy text-accent"></i>
                                    <span>Premio Principal</span>
                                </h3>
                                <p class="text-gray-300 text-sm bg-gray-800/50 rounded-lg p-3">
                                    <?= nl2br(htmlspecialchars($evento['premio_principal'])) ?>
                                </p>
                            </div>
                            
                            <!-- Description -->
                            <div class="mb-6">
                                <h3 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
                                    <i class="fas fa-info-circle text-primary"></i>
                                    <span>Descripción</span>
                                </h3>
                                <p class="text-gray-300 text-sm bg-gray-800/50 rounded-lg p-3">
                                    <?= nl2br(htmlspecialchars($evento['descripcion'])) ?>
                                </p>
                            </div>
                            
                            <!-- Tickets Progress -->
                            <div class="mb-6">
                                <div class="flex justify-between text-sm text-gray-300 mb-2">
                                    <span>Boletos disponibles: <span id="sold-tickets"><?= $evento['boletos_disponibles'] ?></span>/<?= $evento['total_boletos'] ?></span>
                                    <span class="font-bold text-primary">$<?= number_format($evento['precio_boleto'], 2) ?> c/u</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= (($evento['total_boletos'] - $evento['boletos_disponibles']) / $evento['total_boletos']) * 100 ?>%"></div>
                                </div>
                                <div class="text-right text-xs text-gray-400 mt-1">
                                    <?= round((($evento['total_boletos'] - $evento['boletos_disponibles']) / $evento['total_boletos']) * 100) ?>% vendido
                                </div>
                            </div>

                            <!-- Countdown Timer -->
                            <div class="bg-gray-800/50 rounded-lg p-4 mb-6">
                                <h3 class="text-lg font-bold text-white mb-3 flex items-center gap-2">
                                    <i class="fas fa-clock text-accent"></i>
                                    <span>Tiempo restante:</span>
                                </h3>
                                <div class="flex justify-center items-center gap-4">
                                    <div class="countdown-item bg-background rounded-lg p-3 text-center">
                                        <div class="text-2xl font-bold text-primary" id="countdown-days">00</div>
                                        <div class="text-xs text-gray-300 uppercase">Días</div>
                                    </div>
                                    <div class="countdown-item bg-background rounded-lg p-3 text-center">
                                        <div class="text-2xl font-bold text-primary" id="countdown-hours">00</div>
                                        <div class="text-xs text-gray-300 uppercase">Horas</div>
                                    </div>
                                    <div class="countdown-item bg-background rounded-lg p-3 text-center">
                                        <div class="text-2xl font-bold text-primary" id="countdown-minutes">00</div>
                                        <div class="text-xs text-gray-300 uppercase">Minutos</div>
                                    </div>
                                    <div class="countdown-item bg-background rounded-lg p-3 text-center">
                                        <div class="text-2xl font-bold text-primary" id="countdown-seconds">00</div>
                                        <div class="text-xs text-gray-300 uppercase">Segundos</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Purchase Process -->
                <div class="lg:col-span-8">
                    <!-- Purchase Steps -->
                    <div class="mb-8 bg-secondary rounded-xl shadow-lg overflow-hidden border border-gray-800">
                        <div class="p-6">
                            <div class="relative">
                                <!-- Progress Bar -->
                                <div class="absolute top-1/2 left-0 right-0 h-1 bg-gray-700 -translate-y-1/2 z-0"></div>
                                
                                <div class="relative flex justify-between z-10">
                                    <!-- Step 1 - Active -->
                                    <div class="flex flex-col items-center">
                                        <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center mb-2">
                                            <i class="fas fa-ticket-alt"></i>
                                        </div>
                                        <span class="text-sm font-medium text-white">Boletos</span>
                                    </div>
                                    
                                    <!-- Step 2 -->
                                    <div class="flex flex-col items-center">
                                        <div class="w-10 h-10 rounded-full bg-gray-700 text-gray-400 flex items-center justify-center mb-2">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <span class="text-sm font-medium text-gray-400">Datos</span>
                                    </div>
                                    
                                    <!-- Step 3 -->
                                    <div class="flex flex-col items-center">
                                        <div class="w-10 h-10 rounded-full bg-gray-700 text-gray-400 flex items-center justify-center mb-2">
                                            <i class="fas fa-credit-card"></i>
                                        </div>
                                        <span class="text-sm font-medium text-gray-400">Pago</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 1: Ticket Selection -->
                    <div id="step-1" class="bg-secondary rounded-xl shadow-lg overflow-hidden border border-gray-800 mb-8">
                        <div class="p-6">
                            <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-2">
                                <i class="fas fa-ticket-alt text-accent"></i>
                                <span>Selecciona tus boletos</span>
                            </h2>
                            
                            <!-- Random Selection -->
                            <div class="mb-6 flex flex-col sm:flex-row gap-4 items-center">
                                <div class="flex items-center w-full sm:w-auto">
                                    <label class="block text-gray-300 mr-2 whitespace-nowrap">Cantidad:</label>
                                    <div class="flex items-center max-w-xs w-full">
                                        <button id="decrease-random" class="bg-gray-700 text-white px-3 py-2 rounded-l-lg hover:bg-gray-600 transition-colors">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" min="1" max="20" value="1"
                                            class="bg-gray-800 text-white text-center w-full py-2 border-t border-b border-gray-700 focus:outline-none focus:ring-1 focus:ring-primary"
                                            id="random-quantity">
                                        <button id="increase-random" class="bg-gray-700 text-white px-3 py-2 rounded-r-lg hover:bg-gray-600 transition-colors">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <button id="random-btn" class="w-full sm:w-auto bg-primary hover:bg-primary-dark text-white px-6 py-2 rounded-lg font-bold flex items-center justify-center gap-2 transition-colors">
                                    <i class="fas fa-random"></i>
                                    <span>Selección aleatoria</span>
                                </button>
                            </div>
                            
                            <!-- Ticket Grid with Pagination -->
                            <div class="mb-6">
                                <div class="flex justify-between items-center mb-3">
                                    <div class="text-sm text-gray-400">
                                        Página <span id="current-page">1</span> de <span id="total-pages">1</span>
                                    </div>
                                    <div class="flex gap-2">
                                        <button id="prev-page" class="bg-gray-700 text-white px-3 py-1 rounded hover:bg-gray-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                            <i class="fas fa-chevron-left mr-1"></i> Anterior
                                        </button>
                                        <button id="next-page" class="bg-gray-700 text-white px-3 py-1 rounded hover:bg-gray-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                            Siguiente <i class="fas fa-chevron-right ml-1"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Ticket Grid -->
                                <div class="grid grid-cols-5 sm:grid-cols-7 md:grid-cols-8 lg:grid-cols-10 gap-2 mb-3" id="ticket-grid">
                                    <!-- Tickets will be loaded dynamically -->
                                </div>
                                
                                <!-- Loading Skeleton -->
                                <div id="ticket-loading" class="grid grid-cols-5 sm:grid-cols-7 md:grid-cols-8 lg:grid-cols-10 gap-2 mb-3 hidden">
                                    <?php for ($i = 0; $i < 50; $i++): ?>
                                    <div class="ticket skeleton"></div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            
                            <!-- Selected Tickets -->
                            <div class="mb-6 bg-gray-800/50 rounded-lg p-4">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-gray-300">Boletos seleccionados: <span id="selected-count">0</span></span>
                                    <span class="text-primary font-bold">Total: $<span id="selected-total">0.00</span></span>
                                </div>
                                
                                <div class="flex flex-wrap gap-2 min-h-10" id="selected-tickets-list">
                                    <p class="text-gray-400 text-sm">No hay boletos seleccionados</p>
                                </div>
                                
                                <div class="flex justify-end mt-2">
                                    <button id="clear-selection" class="text-xs text-gray-400 hover:text-danger transition-colors hidden">
                                        <i class="fas fa-trash-alt mr-1"></i> Limpiar selección
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Continue Button -->
                            <button id="continue-btn" onclick="showStep(2)" class="w-full bg-success hover:bg-green-700 text-white py-3 rounded-lg font-bold flex items-center justify-center gap-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                <span>Continuar</span>
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Personal Data -->
                    <div id="step-2" class="bg-secondary rounded-xl shadow-lg overflow-hidden border border-gray-800 mb-8 hidden">
                        <div class="p-6">
                            <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-2">
                                <i class="fas fa-user-circle text-accent"></i>
                                <span>Tus datos personales</span>
                            </h2>
                            
                            <form id="formulario-pago" class="space-y-4" action="procesar_compra.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="evento_id" value="<?= $evento_id ?>">
                                <input type="hidden" name="boletos_seleccionados" id="boletos-seleccionados" value="">
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="full-name" class="block text-gray-300 mb-2">Nombre completo</label>
                                        <input type="text" id="full-name" name="nombre" required
                                            class="w-full bg-gray-800 text-white px-4 py-2 rounded-lg border border-gray-700 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none">
                                    </div>
                                    
                                    <div>
                                        <label for="id-number" class="block text-gray-300 mb-2">Cédula</label>
                                        <input type="text" id="id-number" name="cedula" required
                                            class="w-full bg-gray-800 text-white px-4 py-2 rounded-lg border border-gray-700 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none">
                                    </div>
                                    
                                    <div>
                                        <label for="phone" class="block text-gray-300 mb-2">Teléfono</label>
                                        <input type="tel" id="phone" name="telefono" required
                                            class="w-full bg-gray-800 text-white px-4 py-2 rounded-lg border border-gray-700 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none">
                                    </div>
                                    
                                    <div>
                                        <label for="state" class="block text-gray-300 mb-2">Estado</label>
                                        <input type="text" id="state" name="estado" required
                                            class="w-full bg-gray-800 text-white px-4 py-2 rounded-lg border border-gray-700 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none">
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="reference" class="block text-gray-300 mb-2">Referencia de pago (Opcional)</label>
                                    <input type="text" id="reference" name="referencia_pago"
                                        class="w-full bg-gray-800 text-white px-4 py-2 rounded-lg border border-gray-700 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none">
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="payment-method" class="block text-gray-300 mb-2">Método de pago</label>
                                        <select id="payment-method" name="metodo_pago" required
                                            class="w-full bg-gray-800 text-white px-4 py-2 rounded-lg border border-gray-700 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none">
                                            <option value="">Selecciona un método</option>
                                            <?php
                                            $metodos_pago = obtener_metodos_pago(true);
                                            foreach ($metodos_pago as $metodo):
                                            ?>
                                            <option value="<?= $metodo['id'] ?>" data-detalles='<?= htmlspecialchars(json_encode($metodo)) ?>'>
                                                <?= htmlspecialchars($metodo['nombre']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div id="detalles-metodo-pago-seleccionado" class="mt-2 text-sm text-gray-300 bg-gray-800/50 rounded-lg p-3 hidden"></div>
                                    </div>
                                    
                                    <div>
                                        <label for="payment-proof" class="block text-gray-300 mb-2">Comprobante de pago</label>
                                        <div class="relative">
                                            <input type="file" id="payment-proof" name="comprobante_pago" accept="image/*" required
                                                class="w-full bg-gray-800 text-white px-4 py-2 rounded-lg border border-gray-700 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none opacity-0 absolute z-10">
                                            <div class="bg-gray-800 text-gray-300 px-4 py-2 rounded-lg border border-gray-700 flex items-center justify-between">
                                                <span id="file-name">Seleccionar archivo</span>
                                                <i class="fas fa-upload ml-2"></i>
                                            </div>
                                        </div>
                                        <div class="text-xs text-gray-400 mt-1">Formatos aceptados: JPG, PNG (Máx. 5MB)</div>
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="transaction-reference" class="block text-gray-300 mb-2">Referencia del pago (Proporcionada por el Banco)</label>
                                    <input type="text" id="transaction-reference" name="referencia_transaccion" required
                                        class="w-full bg-gray-800 text-white px-4 py-2 rounded-lg border border-gray-700 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none">
                                </div>
                                
                                <div class="flex justify-between items-center pt-4">
                                    <button type="button" class="text-gray-400 hover:text-white transition-colors flex items-center gap-2" onclick="showStep(1)">
                                        <i class="fas fa-arrow-left"></i>
                                        <span>Volver</span>
                                    </button>
                                    <button type="button" class="bg-primary hover:bg-primary-dark text-white px-6 py-2 rounded-lg font-bold flex items-center gap-2 transition-colors" onclick="validateStep2()">
                                        <span>Continuar</span>
                                        <i class="fas fa-arrow-right"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Step 3: Summary and Payment -->
                    <div id="step-3" class="bg-secondary rounded-xl shadow-lg overflow-hidden border border-gray-800 hidden">
                        <div class="p-6">
                            <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-2">
                                <i class="fas fa-file-invoice-dollar text-accent"></i>
                                <span>Resumen de tu compra</span>
                            </h2>
                            
                            <div class="bg-gray-800/50 rounded-lg p-6 mb-6">
                                <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                                    <i class="fas fa-ticket-alt text-primary"></i>
                                    <span>Boletos seleccionados</span>
                                </h3>
                                <div class="flex flex-wrap gap-2 mb-4" id="selected-tickets-display"></div>
                                
                                <div class="border-t border-gray-700 pt-4">
                                    <div class="flex justify-between text-gray-300 mb-2">
                                        <span>Precio por boleto:</span>
                                        <span>$<?= number_format($evento['precio_boleto'], 2) ?></span>
                                    </div>
                                    <div class="flex justify-between text-gray-300 mb-2">
                                        <span>Cantidad de boletos:</span>
                                        <span id="cart-count">0</span>
                                    </div>
                                    <div class="flex justify-between text-white font-bold text-xl pt-2 border-t border-gray-700">
                                        <span>Total a pagar:</span>
                                        <span id="cart-total">$0.00</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <button type="button" class="text-gray-400 hover:text-white transition-colors flex items-center gap-2" onclick="showStep(2)">
                                    <i class="fas fa-arrow-left"></i>
                                    <span>Volver</span>
                                </button>
                                <button type="submit" form="formulario-pago" class="bg-success hover:bg-green-700 text-white px-8 py-3 rounded-lg font-bold flex items-center gap-2 transition-colors">
                                    <i class="fas fa-credit-card"></i>
                                    <span>Proceder al Pago</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-background border-t border-gray-800 pt-12 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12 mb-12">
                <div>
                    <a href="index.php" class="flex items-center gap-2 mb-6">
                        <img src="./assets/img/prueba" alt="Rifas Premium" class="h-10 w-10 rounded-lg" loading="lazy">
                        <span class="text-2xl font-bold bg-gradient-to-r from-primary to-accent bg-clip-text text-transparent font-heading">
                            Rifas Premium
                        </span>
                    </a>
                    <p class="text-gray-400 mb-6">
                        Participa en nuestros exclusivos sorteos y vive la emoción de ganar increíbles premios.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-primary transition-colors">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-blue-400 transition-colors">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-pink-600 transition-colors">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white mb-6 font-heading">Enlaces Rápidos</h3>
                    <ul class="space-y-3">
                        <li><a href="index.php" class="text-gray-400 hover:text-primary transition-colors flex items-center gap-2"><i class="fas fa-chevron-right text-xs"></i> Inicio</a></li>
                        <li><a href="#rifas" class="text-gray-400 hover:text-primary transition-colors flex items-center gap-2"><i class="fas fa-chevron-right text-xs"></i> Rifas Activas</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-primary transition-colors flex items-center gap-2"><i class="fas fa-chevron-right text-xs"></i> Términos y Condiciones</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-primary transition-colors flex items-center gap-2"><i class="fas fa-chevron-right text-xs"></i> Política de Privacidad</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white mb-6 font-heading">Contacto</h3>
                    <ul class="space-y-3">
                        <li class="text-gray-400 flex items-center gap-2"><i class="fas fa-envelope text-primary"></i> contacto@rifaspremium.com</li>
                        <li class="text-gray-400 flex items-center gap-2"><i class="fas fa-phone text-primary"></i> +1 234 567 890</li>
                        <li class="text-gray-400 flex items-center gap-2"><i class="fas fa-map-marker-alt text-primary"></i> Ciudad, País</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <p class="text-gray-400 text-sm mb-4 md:mb-0">
                        © <?= date('Y') ?> Rifas Premium. Todos los derechos reservados.
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="text-gray-400 hover:text-primary text-sm">Términos y Condiciones</a>
                        <a href="#" class="text-gray-400 hover:text-primary text-sm">Política de Privacidad</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- WhatsApp Button -->
    <a href="https://wa.me/" target="_blank" class="fixed bottom-6 right-6 bg-green-500 hover:bg-green-600 text-white w-14 h-14 rounded-full flex items-center justify-center shadow-lg z-40 transition-all hover:scale-110 animate-bounce-slow">
        <i class="fab fa-whatsapp text-2xl"></i>
    </a>

    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed bottom-4 left-1/2 transform -translate-x-1/2 z-50 space-y-2 hidden">
        <!-- Toast messages will be inserted here -->
    </div>

    <!-- Scripts -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Variables globales optimizadas
        const config = {
            ticketPrice: <?= $evento['precio_boleto'] ?>,
            maxTickets: 20,
            ticketsPerPage: 50,
            eventEndDate: "<?= date('Y-m-d H:i:s', strtotime($evento['fecha_fin'])) ?>"
        };

        let state = {
            selectedTickets: [],
            currentPage: 1,
            allTickets: <?= json_encode($boletos_disponibles) ?>,
            totalPages: Math.ceil(<?= count($boletos_disponibles) ?> / 50)
        };

        // Inicialización
        initCountdown();
        loadTickets(1);
        setupEventListeners();

        // Función para mostrar notificaciones toast
        function showToast(message, type = 'info') {
            const toastContainer = document.getElementById('toast-container');
            const toast = document.createElement('div');
            const types = {
                success: 'bg-success',
                error: 'bg-danger',
                warning: 'bg-warning',
                info: 'bg-primary'
            };

            toast.className = `${types[type]} text-white px-4 py-2 rounded-lg shadow-lg flex items-center justify-between max-w-xs`;
            toast.innerHTML = `
                <span>${message}</span>
                <button class="ml-2" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;

            toastContainer.classList.remove('hidden');
            toastContainer.appendChild(toast);

            setTimeout(() => {
                toast.remove();
                if (toastContainer.children.length === 0) {
                    toastContainer.classList.add('hidden');
                }
            }, 5000);
        }

        // Contador regresivo optimizado
        function initCountdown() {
            const endDate = new Date(config.eventEndDate).getTime();
            
            const updateCountdown = () => {
                const now = new Date().getTime();
                const distance = endDate - now;
                
                if (distance < 0) {
                    document.getElementById("countdown-days").textContent = "00";
                    document.getElementById("countdown-hours").textContent = "00";
                    document.getElementById("countdown-minutes").textContent = "00";
                    document.getElementById("countdown-seconds").textContent = "00";
                    return;
                }
                
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                document.getElementById("countdown-days").textContent = days.toString().padStart(2, '0');
                document.getElementById("countdown-hours").textContent = hours.toString().padStart(2, '0');
                document.getElementById("countdown-minutes").textContent = minutes.toString().padStart(2, '0');
                document.getElementById("countdown-seconds").textContent = seconds.toString().padStart(2, '0');
                
                // Efecto cuando quedan menos de 24 horas
                if (days === 0) {
                    document.querySelectorAll('.countdown-item').forEach(item => {
                        item.classList.add('animate-pulse-slow');
                    });
                }
            };
            
            updateCountdown();
            setInterval(updateCountdown, 1000);
        }

        // Configuración de event listeners
        function setupEventListeners() {
            // Paginación
            document.getElementById('prev-page').addEventListener('click', () => {
                if (state.currentPage > 1) {
                    state.currentPage--;
                    loadTickets(state.currentPage);
                }
            });

            document.getElementById('next-page').addEventListener('click', () => {
                if (state.currentPage < state.totalPages) {
                    state.currentPage++;
                    loadTickets(state.currentPage);
                }
            });

            // Selección aleatoria
            document.getElementById('random-btn').addEventListener('click', selectRandomTickets);

            // Controles de cantidad
            document.getElementById('decrease-random').addEventListener('click', () => {
                const input = document.getElementById('random-quantity');
                if (parseInt(input.value) > 1) {
                    input.value = parseInt(input.value) - 1;
                }
            });

            document.getElementById('increase-random').addEventListener('click', () => {
                const input = document.getElementById('random-quantity');
                if (parseInt(input.value) < config.maxTickets) {
                    input.value = parseInt(input.value) + 1;
                }
            });

            // Limpiar selección
            document.getElementById('clear-selection').addEventListener('click', clearSelection);

            // Mostrar detalles del método de pago
            document.getElementById('payment-method').addEventListener('change', showPaymentMethodDetails);

            // Mostrar nombre del archivo seleccionado
            document.getElementById('payment-proof').addEventListener('change', function() {
                const fileName = this.files[0] ? this.files[0].name : 'Seleccionar archivo';
                document.getElementById('file-name').textContent = fileName;
            });

            // Validar formulario antes de continuar
            window.validateStep2 = validateStep2;
        }

        // Cargar boletos con paginación
        function loadTickets(page) {
            // Mostrar skeleton loading
            document.getElementById('ticket-loading').classList.remove('hidden');
            document.getElementById('ticket-grid').classList.add('hidden');

            // Simular carga (en producción sería instantáneo con los datos ya cargados)
            setTimeout(() => {
                const startIndex = (page - 1) * config.ticketsPerPage;
                const endIndex = startIndex + config.ticketsPerPage;
                const ticketsToShow = state.allTickets.slice(startIndex, endIndex);
                
                const ticketGrid = document.getElementById('ticket-grid');
                ticketGrid.innerHTML = '';
                
                ticketsToShow.forEach(boleto => {
                    const ticketElement = document.createElement('div');
                    ticketElement.className = `ticket ${boleto.estado !== 'disponible' ? 'sold' : 'available'}`;
                    ticketElement.textContent = boleto.numero_boleto;
                    ticketElement.dataset.numero = boleto.numero_boleto;
                    
                    if (boleto.estado === 'disponible') {
                        ticketElement.addEventListener('click', function() {
                            toggleTicketSelection(this, boleto.numero_boleto);
                        });
                    }
                    
                    if (state.selectedTickets.includes(boleto.numero_boleto)) {
                        ticketElement.classList.add('selected');
                    }
                    
                    ticketGrid.appendChild(ticketElement);
                });
                
                // Actualizar controles de paginación
                updatePaginationControls(page);
                
                // Ocultar skeleton y mostrar grid
                document.getElementById('ticket-loading').classList.add('hidden');
                document.getElementById('ticket-grid').classList.remove('hidden');
            }, 300);
        }

        // Actualizar controles de paginación
        function updatePaginationControls(page) {
            document.getElementById('current-page').textContent = page;
            document.getElementById('total-pages').textContent = state.totalPages;
            document.getElementById('prev-page').disabled = page === 1;
            document.getElementById('next-page').disabled = page === state.totalPages;
        }

        // Alternar selección de boleto
        function toggleTicketSelection(element, ticketNumber) {
            if (element.classList.contains('selected')) {
                // Deseleccionar
                element.classList.remove('selected');
                state.selectedTickets = state.selectedTickets.filter(num => num !== ticketNumber);
                showToast('Boleto removido', 'info');
            } else {
                // Seleccionar
                if (state.selectedTickets.length >= config.maxTickets) {
                    showToast(`Máximo ${config.maxTickets} boletos por compra`, 'error');
                    return;
                }
                element.classList.add('selected', 'ticket-selected-animation');
                state.selectedTickets.push(ticketNumber);
                showToast('Boleto añadido', 'success');
            }
            
            updateSelectedTickets();
        }

        // Selección aleatoria de boletos
        function selectRandomTickets() {
            const quantity = parseInt(document.getElementById('random-quantity').value);
            
            if (isNaN(quantity) || quantity < 1 || quantity > config.maxTickets) {
                showToast('Por favor ingresa una cantidad válida entre 1 y 20', 'error');
                return;
            }
            
            // Limpiar selección actual
            clearSelection();
            
            // Filtrar boletos disponibles no seleccionados
            const availableTickets = state.allTickets
                .filter(boleto => boleto.estado === 'disponible' && !state.selectedTickets.includes(boleto.numero_boleto))
                .map(boleto => boleto.numero_boleto);
            
            if (availableTickets.length < quantity) {
                showToast(`Solo quedan ${availableTickets.length} boletos disponibles`, 'error');
                return;
            }
            
            // Seleccionar aleatoriamente
            for (let i = 0; i < quantity; i++) {
                const randomIndex = Math.floor(Math.random() * availableTickets.length);
                const randomTicket = availableTickets[randomIndex];
                state.selectedTickets.push(randomTicket);
                availableTickets.splice(randomIndex, 1);
                
                // Actualizar visualmente si está en la página actual
                const ticketElement = document.querySelector(`.ticket.available[data-numero="${randomTicket}"]`);
                if (ticketElement) {
                    ticketElement.classList.add('selected', 'ticket-selected-animation');
                }
            }
            
            showToast(`${quantity} boletos seleccionados aleatoriamente`, 'success');
            updateSelectedTickets();
        }

        // Limpiar selección
        function clearSelection() {
            state.selectedTickets = [];
            
            // Actualizar visualmente
            document.querySelectorAll('.ticket.selected').forEach(el => {
                el.classList.remove('selected');
            });
            
            updateSelectedTickets();
            showToast('Selección limpiada', 'info');
        }

        // Actualizar la lista de boletos seleccionados
        function updateSelectedTickets() {
            const selectedCount = document.getElementById('selected-count');
            const selectedTotal = document.getElementById('selected-total');
            const selectedList = document.getElementById('selected-tickets-list');
            const continueBtn = document.getElementById('continue-btn');
            const clearBtn = document.getElementById('clear-selection');
            const cartCount = document.getElementById('cart-count');
            const cartTotal = document.getElementById('cart-total');
            const selectedDisplay = document.getElementById('selected-tickets-display');
            const boletosSeleccionadosInput = document.getElementById('boletos-seleccionados');
            
            // Actualizar contadores
            selectedCount.textContent = state.selectedTickets.length;
            selectedTotal.textContent = (state.selectedTickets.length * config.ticketPrice).toFixed(2);
            
            // Actualizar lista de boletos
            if (state.selectedTickets.length > 0) {
                selectedList.innerHTML = `
                    <div class="flex flex-wrap gap-2">
                        ${state.selectedTickets.sort((a, b) => a - b).map(ticket => `
                        <div class="bg-primary text-white px-2 py-1 rounded-full text-xs flex items-center">
                            #${ticket.toString().padStart(5, '0')}
                            <button class="ml-1 text-xs hover:text-accent" onclick="removeTicket('${ticket}')">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        `).join('')}
                    </div>
                `;
                
                continueBtn.disabled = false;
                clearBtn.classList.remove('hidden');
                
                // Actualizar resumen en paso 3
                if (cartCount) cartCount.textContent = state.selectedTickets.length;
                if (cartTotal) cartTotal.textContent = `$${(state.selectedTickets.length * config.ticketPrice).toFixed(2)}`;
                if (selectedDisplay) {
                    selectedDisplay.innerHTML = state.selectedTickets.map(ticket => `
                        <div class="bg-gray-700 text-white px-3 py-1 rounded-full text-sm">
                            #${ticket.toString().padStart(5, '0')}
                        </div>
                    `).join('');
                }
                
                // Actualizar campo oculto
                boletosSeleccionadosInput.value = JSON.stringify(state.selectedTickets);
            } else {
                selectedList.innerHTML = '<p class="text-gray-400 text-sm">No hay boletos seleccionados</p>';
                continueBtn.disabled = true;
                clearBtn.classList.add('hidden');
            }
        }

        // Remover un boleto específico
        window.removeTicket = function(ticketNumber) {
            state.selectedTickets = state.selectedTickets.filter(num => num !== ticketNumber);
            
            // Actualizar estado en el grid si está visible
            document.querySelectorAll(`.ticket.selected[data-numero="${ticketNumber}"]`).forEach(el => {
                el.classList.remove('selected');
            });
            
            updateSelectedTickets();
            showToast('Boleto removido', 'info');
        };

        // Mostrar detalles del método de pago
        function showPaymentMethodDetails() {
            const detallesDiv = document.getElementById('detalles-metodo-pago-seleccionado');
            const selectedOption = this.options[this.selectedIndex];
            const detallesJson = selectedOption.getAttribute('data-detalles');
            
            if (!detallesJson) {
                detallesDiv.classList.add('hidden');
                return;
            }
            
            try {
                const detalles = JSON.parse(detallesJson);
                let detallesHTML = '<div class="space-y-2">';
                
                if (detalles.detalles) {
                    const detallesMetodo = JSON.parse(detalles.detalles);
                    for (const [key, value] of Object.entries(detallesMetodo)) {
                        detallesHTML += `<p><strong class="text-accent">${key}:</strong> <span class="text-gray-300">${value}</span></p>`;
                    }
                }
                
                detallesHTML += '</div>';
                detallesDiv.innerHTML = detallesHTML;
                detallesDiv.classList.remove('hidden');
            } catch (error) {
                console.error('Error al parsear JSON:', error);
                detallesDiv.innerHTML = '<p class="text-danger">Error al mostrar detalles</p>';
                detallesDiv.classList.remove('hidden');
            }
        }

        // Validar paso 2 antes de continuar
        function validateStep2() {
            const form = document.getElementById('formulario-pago');
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('border-danger', 'ring-1', 'ring-danger');
                    isValid = false;
                } else {
                    field.classList.remove('border-danger', 'ring-1', 'ring-danger');
                }
            });
            
            if (!isValid) {
                showToast('Por favor completa todos los campos requeridos', 'error');
                return;
            }
            
            const paymentProof = document.getElementById('payment-proof');
            if (!paymentProof.files || paymentProof.files.length === 0) {
                showToast('Debes subir un comprobante de pago', 'error');
                return;
            }
            
            showStep(3);
        }

        // Función para mostrar/ocultar pasos
        window.showStep = function(stepNumber) {
            // Ocultar todos los pasos
            document.querySelectorAll('[id^="step-"]').forEach(step => {
                step.classList.add('hidden');
            });

            // Mostrar el paso seleccionado
            const selectedStep = document.getElementById(`step-${stepNumber}`);
            if (selectedStep) {
                selectedStep.classList.remove('hidden');
            }

            // Actualizar indicadores de progreso
            document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
                if (index + 1 <= stepNumber) {
                    indicator.classList.remove('bg-gray-700');
                    indicator.classList.add('bg-primary');
                } else {
                    indicator.classList.remove('bg-primary');
                    indicator.classList.add('bg-gray-700');
                }
            });

            // Scroll suave al paso
            selectedStep.scrollIntoView({ behavior: 'smooth', block: 'start' });
        };
    });
    </script>
</body>
</html>