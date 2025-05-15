<?php
require_once './admin/includes/config.php';
require_once './admin/includes/functions.php';

$evento_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$evento = obtenerEvento($evento_id);

if (!$evento) {
    header('Location: index.php');
    exit;
}

// Solo necesitamos contar los boletos para la paginación
$total_boletos = contarBoletosDisponibles($evento_id);
$total_paginas = ceil($total_boletos / 100);
$pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
?>

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <!-- Meta tags -->
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= htmlspecialchars($evento['titulo']) ?> | Rifas Premium</title>
    <meta name="description" content="<?= htmlspecialchars($evento['descripcion']) ?>"/>

    <!-- Preconexión y precarga -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Montserrat:wght@700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#0066cc',
                    'primary-dark': '#004999',
                    secondary: '#222222',
                    accent: '#ffd700',
                    background: '#121212',
                    success: '#10b981',
                    danger: '#ef4444',
                    three: '#fcfcfc'
                },
                fontFamily: {
                    sans: ['Poppins', 'sans-serif'],
                    heading: ['Montserrat', 'sans-serif']
                }
            }
        }
    }
    </script>

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Estilos CSS -->
    <style>
    .btn-glow:hover {
        box-shadow: 0 0 15px rgba(0, 102, 204, 0.7);
    }
    .ticket-available {
        background-color: #374151;
        cursor: pointer;
    }
    .ticket-available:hover {
        background-color: #0066cc;
    }
    .ticket-selected {
        background-color: #0066cc !important;
    }
    .ticket-sold {
        background-color: rgba(239, 68, 68, 0.2);
        color: #ef4444;
        cursor: not-allowed;
    }
    .ticket-loading {
        background-color: #1f2937;
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 0.6; }
        50% { opacity: 1; }
    }
    .nav-link {
        position: relative;
    }
    .nav-link span.absolute {
        transition: all 0.3s ease;
    }
    .nav-link:hover span.absolute {
        width: 100%;
    }
    /* Estilos para los boletos */
    .ticket-available, .ticket-selected, .ticket-sold, .ticket-loading {
        height: 2.5rem !important;
        width: 2.5rem !important;
        border-radius: 50% !important;
        font-size: 0.85rem;
        margin: 0.15rem;
        display: flex !important;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    @media (min-width: 640px) {
        .ticket-available, .ticket-selected, .ticket-sold, .ticket-loading {
            height: 1.75rem;
            width: 1.75rem;
            font-size: 0.75rem;
        }
    }
    @media (min-width: 768px) {
        .ticket-available, .ticket-selected, .ticket-sold, .ticket-loading {
            height: 2rem;
            width: 2rem;
        }
    }
    /* Estilos para modales y errores */
    .ticket-error {
        animation: errorBlink 0.5s 3;
    }
    @keyframes errorBlink {
        0% { background-color: #374151; }
        50% { background-color: #ef4444; }
        100% { background-color: #374151; }
    }
    .modal-exito {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        backdrop-filter: blur(5px);
    }
    .modal-exito .modal-contenido {
        background-color: #222;
        border-radius: 1rem;
        padding: 2rem;
        max-width: 500px;
        width: 90%;
        text-align: center;
        border: 1px solid #0066cc;
        box-shadow: 0 0 20px rgba(0, 102, 204, 0.5);
    }
    .modal-exito h3 {
        color: #10b981;
        font-size: 1.5rem;
        margin-bottom: 1rem;
        font-weight: bold;
    }
    .modal-exito h3.error {
        color: #ef4444 !important;
    }
    .modal-exito p {
        color: #fff;
        margin-bottom: 2rem;
    }
    .modal-exito button {
        background-color: #0066cc;
        color: white;
        border: none;
        padding: 0.5rem 1.5rem;
        border-radius: 0.5rem;
        cursor: pointer;
        font-weight: bold;
        transition: background-color 0.3s;
    }
    .modal-exito button:hover {
        background-color: #004999;
    }
    .hidden {
        display: none !important;
    }
    /* Estilos para campos con error */
    .border-danger {
        border-color: #ef4444 !important;
        animation: errorBlink 0.5s 2;
    }
    /* Estilos para mensajes de error debajo de los campos */
    .error-message {
        color: #ef4444;
        font-size: 0.75rem;
        margin-top: 0.25rem;
        display: none;
    }
    /* Mostrar mensaje de error cuando el campo tiene error */
    .has-error .error-message {
        display: block;
    }
    /* Animación para campos con error */
    @keyframes errorBlink {
        0% { border-color: #ef4444; }
        50% { border-color: #fca5a5; }
        100% { border-color: #ef4444; }
    }
    </style>
</head>

<body class="antialiased bg-background text-white"><?php
    // Display memory usage
    echo "<div style='position: fixed; bottom: 0; left: 0; background-color: #f0f0f0; color: #333; padding: 10px; font-size: 12px;'>";
    echo "Pico de uso de RAM: " . round(memory_get_peak_usage() / 1024 / 1024, 2) . " MB";
    echo "</div>";
    ?>
    <!-- Header -->
    <header class="fixed w-full top-0 left-0 z-50 transition-all duration-300" id="navbar">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center bg-secondary/90 backdrop-blur-md rounded-full px-6 py-3 shadow-lg border border-gray-800">
                <a href="/rifas-premium/" class="flex items-center gap-2 group" aria-label="RifasA&M">
                    <img src="./assets/img/logo.png" alt="Logo RifasA&M" class="h-10 w-10 transition-all duration-300 group-hover:rotate-12" loading="eager">
                    <span class="text-2xl font-bold bg-gradient-to-r from-primary to-three bg-clip-text text-transparent">
                        A&M Recreaciones
                    </span>
                </a>
                <nav class="hidden lg:flex items-center gap-8">
                    <a href="/rifas-premium/" class="nav-link text-white hover:text-accent transition-all relative group">
                        <span class="flex items-center gap-1">
                            <i class="fas fa-home text-sm opacity-70"></i>
                            Inicio
                        </span>
                        <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-accent transition-all duration-300 group-hover:w-full"></span>
                    </a>
                </nav>
                <div class="flex items-center gap-4">
                    <button id="mobile-menu-button" class="lg:hidden text-white p-2 rounded-full hover:bg-gray-800 transition-all">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="pt-32 pb-12 bg-background">
        <div class="container mx-auto px-4">
            <!-- Grid principal -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Columna izquierda (Información del evento) -->
                <div class="lg:col-span-4">
                    <div class="bg-secondary rounded-xl shadow-lg overflow-hidden border border-gray-800 sticky top-32">
                        <div class="relative h-64 overflow-hidden">
                            <img src="/rifas-premium/admin/uploads/<?= htmlspecialchars($evento['imagen']) ?>"
                                alt="<?= htmlspecialchars($evento['titulo']) ?>"
                                class="w-full h-full object-cover transition-transform duration-500 hover:scale-105">
                            <div class="absolute top-4 right-4 bg-primary text-white px-3 py-1 rounded-full text-xs font-bold">
                                <i class="fas fa-bolt mr-1"></i> <?= strtoupper($evento['estado']) ?>
                            </div>
                        </div>
                        <div class="p-6">
                            <h1 class="text-2xl font-bold text-white mb-4"><?= htmlspecialchars($evento['titulo']) ?></h1>
                            
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
                            
                            <div class="mb-6">
                                <h3 class="text-lg font-bold text-white mb-2">Premio Principal</h3>
                                <p class="text-gray-300 text-sm">
                                    <?= nl2br(htmlspecialchars($evento['premio_principal'])) ?>
                                </p>
                            </div>
                            
                            <div class="mb-6">
                                <h3 class="text-lg font-bold text-white mb-2">Descripción</h3>
                                <p class="text-gray-300 text-sm">
                                    <?= nl2br(htmlspecialchars($evento['descripcion'])) ?>
                                </p>
                            </div>
                            
                            <!-- Progreso de Boletos -->
                            <div class="mb-6">
                                <div class="flex justify-between text-sm text-gray-300 mb-2">
                                    <span>Boletos disponibles: <span id="sold-tickets"><?= $evento['boletos_disponibles'] ?></span>/<?= $evento['total_boletos'] ?></span>
                                    <span class="font-bold text-primary">$<?= number_format($evento['precio_boleto'], 2) ?> c/u</span>
                                </div>
                                <div class="w-full bg-gray-800 rounded-full h-2.5">
                                    <div class="bg-primary h-2.5 rounded-full" style="width: <?= ((($evento['total_boletos'] - $evento['boletos_disponibles']) / $evento['total_boletos']) * 100) ?>%"></div>
                                </div>
                                <div class="text-right text-xs text-gray-400 mt-1"><?= round((($evento['total_boletos'] - $evento['boletos_disponibles']) / $evento['total_boletos']) * 100) ?>% vendido</div>
                            </div>

                            <!--contador de tiempo-->
                            <div class="bg-gray-800/50 rounded-lg p-4">
                                <h3 class="text-lg font-bold text-white mb-3">Tiempo restante:</h3>
                                <div class="grid grid-cols-4 gap-2 text-center">
                                    <div class="bg-background rounded p-2">
                                        <div class="text-2xl font-bold text-primary" id="countdown-days">00</div>
                                        <div class="text-xs text-gray-300">Días</div>
                                    </div>
                                    <div class="bg-background rounded p-2">
                                        <div class="text-2xl font-bold text-primary" id="countdown-hours">00</div>
                                        <div class="text-xs text-gray-300">Horas</div>
                                    </div>
                                    <div class="bg-background rounded p-2">
                                        <div class="text-2xl font-bold text-primary" id="countdown-minutes">00</div>
                                        <div class="text-xs text-gray-300">Minutos</div>
                                    </div>
                                    <div class="bg-background rounded p-2">
                                        <div class="text-2xl font-bold text-primary" id="countdown-seconds">00</div>
                                        <div class="text-xs text-gray-300">Segundos</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

               <!-- Columna derecha (Proceso de compra o mensaje de evento finalizado) -->
<div class="lg:col-span-8">
    <?php if ($evento['estado'] == 'finalizado'): ?>
        <!-- Mensaje de evento finalizado -->
        <div class="bg-secondary rounded-xl shadow-lg overflow-hidden border border-gray-800 p-8 text-center">
            <div class="text-5xl mb-4 text-primary">
                <i class="fas fa-flag-checkered"></i>
            </div>
            <h2 class="text-2xl font-bold text-white mb-4">¡Este evento ha finalizado!</h2>
            <p class="text-gray-300 mb-6">El sorteo de esta rifa ya se realizó y no es posible comprar más boletos.</p>
            
            <?php if (!empty($evento['boleto_ganador'])): ?>
                <div class="bg-gray-800/50 rounded-lg p-4 max-w-md mx-auto">
                    <h3 class="text-lg font-bold text-primary mb-2">Boleto ganador</h3>
                    <div class="text-3xl font-bold text-white mb-2"><?= $evento['boleto_ganador'] ?></div>
                    <p class="text-gray-300 text-sm">¡Felicidades al ganador!</p>
                </div>
            <?php endif; ?>
            
            <a href="/rifas-premium" class="inline-block mt-6 bg-primary text-white px-6 py-3 rounded-lg font-bold hover:bg-primary-dark transition-all">
                <i class="fas fa-arrow-left mr-2"></i> Ver otros eventos
            </a>
        </div>
    <?php else: ?>
        <!-- Contenido actual del proceso de compra -->
                    <!-- Pasos de compra -->
                    <div class="mb-1">
                        <div class="flex items-center justify-between text-sm text-gray-400">
                            <div class="flex items-center">
                                <div class="step-indicator w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center">1</div>
                                <span class="ml-2">Seleccionar boletos</span>
                            </div>
                            <div class="w-16 h-0.5 bg-gray-700"></div>
                            <div class="flex items-center">
                                <div class="step-indicator w-8 h-8 rounded-full bg-gray-700 text-white flex items-center justify-center">2</div>
                                <span class="ml-2">Datos personales</span>
                            </div>
                      
                        </div>
                    </div>
                   <!-- Paso 1: Selección de boletos -->
<div id="step-1" class="bg-secondary rounded-xl shadow-lg overflow-hidden border border-gray-800 mb-8">
    <div class="p-6">
        
        <!-- Sección de selección aleatoria -->
        <div class="mb-6 bg-gray-800/50 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-white mb-3 flex items-center gap-2">
                <i class="fas fa-random text-primary"></i>
                <span>Selección aleatoria de boletos</span>
            </h3>
            
            <div class="flex flex-col sm:flex-row items-center gap-4">
                <div class="flex items-center w-full sm:w-auto">
                    <label class="block text-gray-300 mr-2 whitespace-nowrap">Cantidad de boletos:</label>
                    <div class="flex items-center w-full sm:w-32">
                        <button id="decrease-random" class="bg-gray-700 text-white px-3 py-1 rounded-l-lg hover:bg-gray-600">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" min="1" max="20" value="1"
                            class="bg-gray-800 text-white text-center w-full py-1 border-t border-b border-gray-700"
                            id="random-quantity">
                        <button id="increase-random" class="bg-gray-700 text-white px-3 py-1 rounded-r-lg hover:bg-gray-600">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <button id="random-btn" class="bg-primary text-white px-4 py-2 rounded-lg font-bold flex items-center justify-center gap-2 hover:bg-primary-dark transition-all w-full sm:w-auto">
                    <i class="fas fa-random"></i>
                    <span>Seleccionar aleatoriamente</span>
                </button>
            </div>
            <p class="text-gray-400 text-sm mt-2">Seleccionará automáticamente la cantidad especificada de boletos disponibles al azar</p>
        </div>
        
        <!-- Sección de selección manual -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-white mb-3 flex items-center gap-2">
                <i class="fas fa-mouse-pointer text-primary"></i>
                <span>Selección manual de boletos </span>
            </h3>
            
            <!-- Controles de paginación -->
            <div class="flex justify-between items-center mb-3">
                <div class="text-sm text-gray-400">
                    Página <span id="current-page">1</span> de <span id="total-pages">1</span>
                </div>
                <div class="flex gap-2">
                    <button id="prev-page" class="bg-gray-700 text-white px-3 py-1 rounded hover:bg-gray-600 disabled:opacity-50" disabled>
                        <i class="fas fa-chevron-left"></i> Anterior
                    </button>
                    <button id="next-page" class="bg-gray-700 text-white px-3 py-1 rounded hover:bg-gray-600 disabled:opacity-50">
                        Siguiente <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
            
            <!-- Grid de boletos compacto -->
            <div class="grid grid-cols-5 sm:grid-cols-7 md:grid-cols-8 lg:grid-cols-10 gap-1 mb-3" id="ticket-grid">
                <!-- Los boletos se cargarán dinámicamente con JavaScript -->
            </div>
            
            <p class="text-gray-400 text-sm">Haz clic en los boletos para seleccionarlos manualmente</p>
        </div>
        
        <!-- Boletos seleccionados -->
        <div class="mb-6 bg-gray-800/50 rounded-lg p-4">
            <div class="flex justify-between items-center mb-2">
                <span class="text-gray-300">Boletos seleccionados: <span id="selected-count">0</span></span>
                <div class="text-right">
                    <span class="text-primary font-bold block" id="selected-total">$0.00 USD</span>
                    <span class="text-xs text-gray-400" id="selected-total-equivalent"></span>
                </div>
            </div>
            
            <div class="flex flex-wrap gap-2 min-h-10" id="selected-tickets-list">
                <p class="text-gray-400 text-sm">No hay boletos seleccionados</p>
            </div>
        </div>

                            <!-- Botón de continuar -->
                            <button id="continue-btn" onclick="showStep(2)" class="w-full bg-success text-white py-3 rounded-lg font-bold flex items-center justify-center gap-2 hover:bg-green-700 transition-all disabled:opacity-50" disabled>
                                <span>Continuar</span>
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

<!-- Paso 2: Datos personales + Resumen de compra -->
<div id="step-2" class="bg-secondary rounded-xl shadow-lg overflow-hidden border border-gray-800 mb-8 hidden">
    <div class="p-6">
        <button type="button" class="text-gray-400 hover:text-white transition-all" onclick="showStep(1)">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </button>
        <br>
        <div class="text-center"><h3 class="text-lg font-bold text-white mb-4">Tus datos personales</h3></div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Columna izquierda: Datos personales -->
            <div>
                <form id="formulario-pago" class="space-y-3" action="/rifas-premium/admin/includes/procesar_compra.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="evento_id" value="<?= $evento_id ?>">
                    <input type="hidden" name="boletos_seleccionados" id="boletos-seleccionados" value="">
                    
                    <div class="space-y-4">
                        <!-- Nombre completo -->
                        <div>
                            <label class="block text-gray-300 mb-1">Nombre completo</label>
                            <input type="text" id="full-name" name="nombre" required
                                class="w-full bg-gray-800 text-white px-4 py-2 rounded-lg border border-gray-700 focus:border-primary focus:outline-none" placeholder="Ej. Alfonso Perez"
                                pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo se permiten letras y espacios.">
                        </div>
                        
                        <!-- Cédula y Teléfono -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-300 mb-1">Cédula</label>
                                <input type="text" id="id-number" name="cedula" required
                                    class="w-full bg-gray-800 text-white px-4 py-2 rounded-lg border border-gray-700 focus:border-primary focus:outline-none" placeholder="Ej. 30125963"
                                    pattern="[0-9]{8,}" title="Debe contener al menos 8 números.">
                            </div>
                            
                            <div>
                                <label class="block text-gray-300 mb-1">Teléfono</label>
                                <input type="tel" id="phone" name="telefono" required
                                    class="w-full bg-gray-800 text-white px-4 py-2 rounded-lg border border-gray-700 focus:border-primary focus:outline-none" placeholder="Ej. +00 000000000"
                                    pattern="[0-9\-\+]+" title="No se permiten letras ni caracteres especiales. Usa el formato +00 000000000">
                            </div>
                        </div>
                        
                        <!-- Estado -->
                        <div>
                            <label class="block text-gray-300 mb-1">Estado</label>
                            <select id="state" name="estado" required
                                class="w-full bg-gray-800 text-white px-4 py-2 rounded-lg border border-gray-700 focus:border-primary focus:outline-none">
                                <option value="">Selecciona un estado</option>
                                <option value="Amazonas">Amazonas</option>
                                <option value="Anzoátegui">Anzoátegui</option>
                                <option value="Apure">Apure</option>
                                <option value="Aragua">Aragua</option>
                                <option value="Barinas">Barinas</option>
                                <option value="Bolívar">Bolívar</option>
                                <option value="Carabobo">Carabobo</option>
                                <option value="Cojedes">Cojedes</option>
                                <option value="Delta Amacuro">Delta Amacuro</option>
                                <option value="Caracas">Caracas</option>
                                <option value="Falcón">Falcón</option>
                                <option value="Guárico">Guárico</option>
                                <option value="Lara">Lara</option>
                                <option value="Mérida">Mérida</option>
                                <option value="Miranda">Miranda</option>
                                <option value="Monagas">Monagas</option>
                                <option value="Nueva Esparta">Nueva Esparta</option>
                                <option value="Portuguesa">Portuguesa</option>
                                <option value="Sucre">Sucre</option>
                                <option value="Táchira">Táchira</option>
                                <option value="Trujillo">Trujillo</option>
                                <option value="Vargas">Vargas</option>
                                <option value="Yaracuy">Yaracuy</option>
                                <option value="Zulia">Zulia</option>
                                <option value="Otro país">Otro país</option>
                            </select>
                        </div>
                    </div>
            </div>
            
            <!-- Columna derecha: Método de pago -->
            <div class="space-y-6">
                <div class="bg-gray-800/50 rounded-lg p-3 mt-10 lg:mt-0">
                    <h3 class="text-lg font-bold text-white mb-4">Método de pago</h3>
                    
                    <div class="space-y-2">
                        <!-- Selección de método -->
                        <div>
                            <label class="block text-gray-300 mb-2">Selecciona tu método de pago</label>
                            <select id="payment-method" name="metodo_pago" required
                                class="w-full bg-gray-800 text-white px-4 py-2 rounded-lg border border-gray-700 focus:border-primary focus:outline-none">
                                <option value="">Selecciona un método</option>
                                <?php
                                $metodos_pago = obtener_metodos_pago(true);
                                foreach ($metodos_pago as $metodo):
                                    if ($metodo['activo'] == 1): 
                                ?>
                                <option value="<?= $metodo['id'] ?>" data-detalles='<?= htmlspecialchars(json_encode($metodo)) ?>'>
                                    <?= htmlspecialchars($metodo['nombre']) ?>
                                </option>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </select>
                            <div id="detalles-metodo-pago-seleccionado" class="mt-2 text-m text-gray-300"></div>
                        </div>
                        
                        <!-- Referencia de pago (siempre visible) -->
                        <div>
                            <label class="block text-gray-300 mb-1">Referencia del pago</label>
                            <input type="text" id="transaction-reference" name="referencia_transaccion" required
                                class="w-full bg-gray-800 text-white px-4 py-2 rounded-lg border border-gray-700 focus:border-primary focus:outline-none" pattern="[A-Za-z0-9]{6,}" title="Debe contener al menos 6 caracteres alfanuméricos.">
                        </div>
                        
                        <!-- Comprobante -->
                        <div>
                            <label class="block text-gray-300 mb-2">Comprobante de pago</label>
                            <input type="file" id="payment-proof" name="comprobante_pago" accept="image/*" required
                                class="w-full bg-gray-800 text-white px-4 py-2 rounded-lg border border-gray-700 focus:border-primary focus:outline-none">
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Resumen de compra -->
        <div class="bg-gray-800/50 rounded-lg p-3 mt-6">
            <div class="mb-4">
                <h4 class="text-s font-semibold text-gray-300 mb-2">Boletos seleccionados</h4>
                <div class="flex flex-wrap gap-2 mb-4" id="selected-tickets-display">
                    <!-- Se llenará dinámicamente con JavaScript -->
                </div>
            </div>
            
            <div class="border-t border-gray-700 pt-4 space-y-3">
                <div class="flex justify-between text-white font-bold text-x pt-2">
                    <span>Total a pagar:</span>
                    <div class="text-right">
                        <span id="cart-total">$0.00 USD</span>
                        <div class="text-xs text-gray-400" id="cart-total-equivalent"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Botón de compra -->
        <div class="mt-6 flex justify-between items-center">
            <button type="submit" form="formulario-pago" class="w-full bg-success text-white py-3 rounded-lg font-bold flex items-center justify-center gap-2 hover:bg-green-700 transition-all">
                <i class="fas fa-credit-card mr-2"></i> Procesar Compra
            </button>
        </div>
    </div>
</div>

</div>  </div>  </div>
     <?php endif; ?>  </main>
    

<!-- Sección de búsqueda de boleto -->
<section class="bg-background py-12" id="buscar-boleto"><br>
    <div class="container mx-auto px-4">
        <div class="bg-secondary rounded-xl shadow-lg overflow-hidden border border-gray-800 p-6">
            <h2 class="text-2xl font-bold text-white mb-6">Buscar Boleto</h2>
            
            <div class="flex flex-col md:flex-row gap-4 mb-6">
                <input type="text" id="search-ticket-input" 
                       class="flex-1 bg-gray-800 text-white px-4 py-3 rounded-lg border border-gray-700 focus:border-primary focus:outline-none" 
                       placeholder="Ingresa el número de boleto">
                <button id="search-ticket-btn" 
                        class="bg-primary text-white px-6 py-3 rounded-lg font-bold hover:bg-primary-dark transition-all">
                    <i class="fas fa-search mr-2"></i> Buscar
                </button>
            </div>
            
            <div id="ticket-result" class="hidden bg-gray-800/50 rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-bold text-primary mb-2">Información del Boleto</h3>
                        <div class="space-y-3">
                            <p><strong>Evento:</strong> <span id="ticket-event-name">-</span></p>
                            <p><strong>Número:</strong> <span id="ticket-number">-</span></p>
                            <p><strong>Estado:</strong> <span id="ticket-status" class="px-2 py-1 rounded-full text-xs font-bold">-</span></p>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-bold text-primary mb-2">Información del Comprador</h3>
                        <div class="space-y-3" id="buyer-info">
                            <p><strong>Nombre:</strong> <span id="ticket-buyer-name">-</span></p>
                            <p><strong>Estado:</strong> <span id="ticket-buyer-state">-</span></p>
                            <p><strong>Teléfono:</strong> <span id="ticket-buyer-phone">-</span></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="ticket-not-found" class="hidden bg-gray-800/50 rounded-lg p-6 text-center">
                <i class="fas fa-ticket-alt text-4xl text-gray-500 mb-3"></i>
                <p class="text-gray-400">No se encontró información para este boleto</p>
            </div>
        </div>
    </div>
</section>
    <!-- Footer -->
    <footer class="bg-background border-t border-gray-800 pt-16 pb-8">
    <div class="container mx-auto px-4">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12 mb-12">
        <div>
          <a href="#" class="flex items-center gap-2 mb-6">
            <img src=/rifasym.jpg  
                 alt="Bólidos Rifas" 
                 class="h-10 w-10 rounded-lg"
                 loading="lazy">
            <span class="text-2xl font-bold bg-gradient-to-r from-primary to-three bg-clip-text text-transparent">
              A&M Recreaciones
            </span>
          </a>
          <p class="text-gray-400 mb-6">
            Participa en nuestros exclusivos sorteos y vive la emoción de ganar increíbles premios.
          </p>

        </div>
        
        <div>
          <h3 class="text-lg font-bold text-white mb-6">Enlaces Rápidos</h3>
          <ul class="space-y-3">
            <li><a href="#inicio" class="text-gray-400 hover:text-primary transition-all">Inicio</a></li>
            <li><a href="#eventos" class="text-gray-400 hover:text-primary transition-all">Rifas Activas</a></li>
            <li><a href="#como-participar" class="text-gray-400 hover:text-primary transition-all">Como Participar</a></li>
          </ul>
        </div>
        
        
        <div>
          <h3 class="text-lg font-bold text-white mb-6">Boletín Informativo</h3>
          <p class="text-gray-400 mb-4">
            Siguenos para recibir información sobre nuevas rifas, promociones exclusivas y resultados de sorteos.
          </p>
          <div class="flex space-x-4">
            <a href="#" class="text-gray-400 hover:text-primary transition-all">
              <i class="fab fa-facebook-f"></i>
            </a>
            <a href="#" class="text-gray-400 hover:text-blue-400 transition-all">
              <i class="fab fa-twitter"></i>
            </a>
            <a href="#" class="text-gray-400 hover:text-pink-600 transition-all">
              <i class="fab fa-instagram"></i>
            </a>
            <a href="#" class="text-gray-400 hover:text-red-600 transition-all">
              <i class="fab fa-youtube"></i>
            </a>
          </div>
        </div>
      </div>
      
      <div class="border-t border-gray-800 pt-8">
        <div class="flex flex-col md:flex-row justify-between items-center">
          <p class="text-gray-400 text-sm mb-4 md:mb-0">
            © 2024 A&M Recreaciones. Todos los derechos reservados.
          </p>
          
        </div>
      </div>
    </div>
  </footer>

    <!-- Botón de WhatsApp -->
    <a href="https://wa.me/" target="_blank" class="fixed bottom-6 right-6 bg-green-500 hover:bg-green-600 text-white w-14 h-14 rounded-full flex items-center justify-center shadow-lg z-40 transition-all hover:scale-110">
        <i class="fab fa-whatsapp text-2xl"></i>
    </a>

    <!-- Scripts -->
   <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Variables globales
        let selectedTickets = [];
        const ticketPrice = <?= $evento['precio_boleto'] ?>;
        const ticketsPerPage = 100;
        let currentPage = <?= $pagina_actual ?>;
        const totalPages = <?= $total_paginas ?>;
        let selectedPaymentMethod = null;
        let ticketAvailabilityCache = {}; // Cache para disponibilidad de boletos
        let debounceTimer = null;

        // Función para mostrar/ocultar pasos
        window.showStep = function(stepNumber) {
            document.querySelectorAll('[id^="step-"]').forEach(step => {
                step.classList.add('hidden');
            });

            const selectedStep = document.getElementById(`step-${stepNumber}`);
            if (selectedStep) {
                selectedStep.classList.remove('hidden');
                selectedStep.scrollIntoView({ behavior: 'smooth', block: 'start' });
                
                if (stepNumber === 2 && selectedPaymentMethod) {
                    const paymentSelect = document.getElementById('payment-method');
                    paymentSelect.value = selectedPaymentMethod;
                    const event = new Event('change');
                    paymentSelect.dispatchEvent(event);
                }
            }

            document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
                if (index + 1 <= stepNumber) {
                    indicator.classList.remove('bg-gray-700');
                    indicator.classList.add('bg-primary');
                } else {
                    indicator.classList.remove('bg-primary');
                    indicator.classList.add('bg-gray-700');
                }
            });

            if (stepNumber === 2) {
                document.getElementById('boletos-seleccionados').value = JSON.stringify(selectedTickets);
            }
        };

        // Función optimizada para cargar boletos por página con WebSocket
        function loadTickets(page) {
            const ticketGrid = document.getElementById('ticket-grid');
            
            // Mostrar estado de carga
            ticketGrid.innerHTML = '';
            for (let i = 0; i < ticketsPerPage; i++) {
                const loadingDiv = document.createElement('div');
                loadingDiv.className = 'ticket-loading';
                ticketGrid.appendChild(loadingDiv);
            }

            // Usar fetch con cache para evitar peticiones redundantes
            if (ticketAvailabilityCache[page]) {
                renderTickets(ticketAvailabilityCache[page]);
                return;
            }

            fetch(`/rifas-premium/api/boletos.php?evento_id=<?= $evento_id ?>&pagina=${page}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Almacenar en cache
                        ticketAvailabilityCache[page] = data.boletos;
                        renderTickets(data.boletos);
                        updatePaginationControls(page, data.total_paginas);
                        
                        // Iniciar WebSocket para actualizaciones en tiempo real
                        if (page === 1 && !window.ticketSocket) {
                            initWebSocket();
                        }
                    } else {
                        console.error('Error al cargar boletos:', data.message);
                        mostrarMensaje('Error', 'No se pudieron cargar los boletos', true);
                    }
                })
                .catch(error => {
                    console.error('Error en la petición:', error);
                    mostrarMensaje('Error', 'Error de conexión al cargar boletos', true);
                });
        }

        // Inicializar WebSocket para actualizaciones en tiempo real
       // Reemplaza la función initWebSocket() con esta versión mejorada:
// Reemplaza la función initWebSocket() con esta versión mejorada
function initWebSocket() {
    // Usar el mismo protocolo (http/https) para el websocket
    const protocol = window.location.protocol === 'https:' ? 'wss://' : 'ws://';
    const host = window.location.hostname;
    const port = window.location.port ? `:${window.location.port}` : '';
    
    // Asegúrate de que el puerto del WebSocket coincida con el del servidor (8080)
    const wsPort = ':8080'; // Puerto donde corre el servidor WebSocket
    const path = '/'; // Ruta base o puedes usar '/ticket_updates'
    const params = `?evento_id=<?= $evento_id ?>`;
    
    // Construir la URL correcta
    const wsUrl = `${protocol}${host}${wsPort}${path}${params}`;
    
    console.log('Intentando conectar a:', wsUrl);
    
    if (window.ticketSocket) {
        window.ticketSocket.close();
    }
    
    window.ticketSocket = new WebSocket(wsUrl);
    
    ticketSocket.onopen = function(e) {
        console.log('Conexión WebSocket establecida');
    };
    
    ticketSocket.onmessage = function(event) {
        try {
            const data = JSON.parse(event.data);
            
            if (data.type === 'ticket_update') {
                updateTicketStatus(data.ticket_number, data.status);
                
                if (data.status !== 'disponible') {
                    updateAvailableTicketsCount(-1);
                }
            }
        } catch (e) {
            console.error('Error procesando mensaje WebSocket:', e);
        }
    };
    
    ticketSocket.onclose = function(e) {
        if (e.wasClean) {
            console.log(`Conexión cerrada limpiamente, código=${e.code} motivo=${e.reason}`);
        } else {
            console.log('Conexión interrumpida, reconectando...');
        }
        
        // Reconexión exponencial
        let reconnectDelay = 1000;
        const maxReconnectDelay = 30000;
        
        const tryReconnect = () => {
            if (reconnectDelay < maxReconnectDelay) {
                reconnectDelay *= 2;
            }
            
            console.log(`Intentando reconectar en ${reconnectDelay}ms...`);
            setTimeout(initWebSocket, reconnectDelay);
        };
        
        tryReconnect();
    };
    
    ticketSocket.onerror = function(error) {
        console.error('Error en WebSocket:', error);
    };
}

function updateAvailableTicketsCount(change) {
    const soldElement = document.getElementById('sold-tickets');
    if (soldElement) {
        const current = parseInt(soldElement.textContent);
        if (!isNaN(current)) {
            soldElement.textContent = current + change;
        }
    }
}

function updateTicketStatus(ticketNumber, status) {
    // Actualizar en el grid si está visible
    const ticketElements = document.querySelectorAll(`[data-numero="${ticketNumber}"]`);
    
    ticketElements.forEach(ticketElement => {
        if (status === 'disponible') {
            ticketElement.className = 'ticket-available';
            ticketElement.onclick = () => toggleTicketSelection(ticketElement, ticketNumber);
        } else {
            ticketElement.className = 'ticket-sold';
            ticketElement.onclick = null;
            
            // Si estaba seleccionado, quitarlo
            if (selectedTickets.includes(ticketNumber)) {
                selectedTickets = selectedTickets.filter(num => num !== ticketNumber);
                updateSelectedTickets();
            }
        }
    });
}

function updateTicketStatus(ticketNumber, status) {
    const ticketElement = document.querySelector(`[data-numero="${ticketNumber}"]`);
    if (ticketElement) {
        ticketElement.className = `flex items-center justify-center ${
            status === 'disponible' ? 'ticket-available' : 'ticket-sold'
        }`;
        
        if (status === 'disponible') {
            ticketElement.onclick = () => toggleTicketSelection(ticketElement, ticketNumber);
        } else {
            ticketElement.onclick = null;
        }
    }
}

        // Actualizar estado de un boleto específico
        function updateTicketStatus(ticketNumber, status) {
            // Actualizar en el grid si está visible
            const ticketElement = document.querySelector(`[data-numero="${ticketNumber}"]`);
            if (ticketElement) {
                ticketElement.className = `flex items-center justify-center ${
                    status !== 'disponible' ? 'ticket-sold' : 'ticket-available'
                }`;
                
                if (status === 'disponible') {
                    ticketElement.addEventListener('click', () => toggleTicketSelection(ticketElement, ticketNumber));
                } else {
                    ticketElement.onclick = null;
                    
                    // Si estaba seleccionado, quitarlo
                    if (selectedTickets.includes(ticketNumber)) {
                        selectedTickets = selectedTickets.filter(num => num !== ticketNumber);
                        updateSelectedTickets();
                    }
                }
            }
            
            // Actualizar en el cache si existe
            for (const page in ticketAvailabilityCache) {
                const ticketIndex = ticketAvailabilityCache[page].findIndex(t => t.numero_boleto === ticketNumber);
                if (ticketIndex !== -1) {
                    ticketAvailabilityCache[page][ticketIndex].estado = status;
                    break;
                }
            }
        }

        // Renderizar boletos en el grid
        function renderTickets(boletos) {
            const ticketGrid = document.getElementById('ticket-grid');
            ticketGrid.innerHTML = '';
            
            boletos.forEach(boleto => {
                const ticketElement = document.createElement('div');
                ticketElement.className = `flex items-center justify-center ${
                    boleto.estado !== 'disponible' ? 'ticket-sold' : 'ticket-available'
                }`;
                ticketElement.textContent = boleto.numero_boleto;
                ticketElement.dataset.numero = boleto.numero_boleto;

                if (boleto.estado === 'disponible') {
                    ticketElement.addEventListener('click', () => toggleTicketSelection(ticketElement, boleto.numero_boleto));
                }

                // Resaltar si está seleccionado
                if (selectedTickets.includes(boleto.numero_boleto)) {
                    ticketElement.classList.remove('ticket-available');
                    ticketElement.classList.add('ticket-selected');
                }

                ticketGrid.appendChild(ticketElement);
            });
        }

        // Función para alternar selección de boleto con verificación en tiempo real
       async function toggleTicketSelection(element, ticketNumber) {
    // Verificar disponibilidad en tiempo real antes de seleccionar
    try {
        const response = await fetch(`/rifas-premium/api/todos_boletos.php?numero=${ticketNumber}&evento_id=<?= $evento_id ?>`);        
        // Verificar si la respuesta es JSON válido
        const text = await response.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('Respuesta no JSON:', text);
            throw new Error('Respuesta inválida del servidor');
        }
        
        if (!data.success) {
            throw new Error(data.message || 'Error al verificar boleto');
        }
        
        if (!data.disponible) {
            // Actualizar estado en la interfaz
            element.classList.remove('ticket-available');
            element.classList.add('ticket-sold');
            element.onclick = null;
            
            // Mostrar mensaje al usuario
            mostrarMensaje('Boleto no disponible', `El boleto #${ticketNumber} ya no está disponible (Estado: ${data.estado})`, true);
            return;
        }
        
        // Proceder con la selección/deselección
        if (element.classList.contains('ticket-selected')) {
            // Deseleccionar
            element.classList.remove('ticket-selected');
            element.classList.add('ticket-available');
            selectedTickets = selectedTickets.filter(num => num !== ticketNumber);
        } else {
            // Seleccionar
            element.classList.remove('ticket-available');
            element.classList.add('ticket-selected');
            selectedTickets.push(ticketNumber);
        }
        
        updateSelectedTickets();
    } catch (error) {
        console.error('Error al verificar boleto:', error);
        mostrarMensaje('Error', 'No se pudo verificar la disponibilidad del boleto: ' + error.message, true);
    }
}

// Modificar la función updateSelectedTickets para usar la nueva función
function updateSelectedTickets() {
    const selectedCount = document.getElementById('selected-count');
    const selectedList = document.getElementById('selected-tickets-list');
    const continueBtn = document.getElementById('continue-btn');
    const selectedDisplay = document.getElementById('selected-tickets-display');
    const minimoBoletos = <?= $evento['minimo_boletos'] ?? 1 ?>;
    const cumpleMinimo = selectedTickets.length >= minimoBoletos;

    // Actualizar contadores
    selectedCount.textContent = selectedTickets.length;
    
    // Actualizar totales
    const totalUSD = selectedTickets.length * ticketPrice;
    document.getElementById('selected-total').textContent = `$${totalUSD.toFixed(2)} USD`;
    
    // Actualizar lista de boletos seleccionados
    if (selectedTickets.length > 0) {
        // Formatear los números para mostrar (eliminar ceros adicionales)
        const formattedTickets = selectedTickets.map(t => {
            // Convertir a número para eliminar ceros a la izquierda, luego formatear a 4 dígitos
            const num = parseInt(t);
            return num.toString().padStart(4, '0');
        });
        
        // Agrupar en bloques de 10 para mejor visualización
        const groupedTickets = [];
        for (let i = 0; i < formattedTickets.length; i += 10) {
            groupedTickets.push(formattedTickets.slice(i, i + 10));
        }
        
        selectedList.innerHTML = `
            <div class="flex flex-wrap gap-2">
                ${groupedTickets.map(group => `
                    <div class="flex flex-wrap gap-2 mb-2">
                        ${group.map(ticket => `
                            <div class="bg-primary text-white px-2 py-1 rounded-full text-xs flex items-center">
                                #${ticket}
                                <button class="ml-1 text-xs hover:text-accent" onclick="removeTicket('${ticket}')">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `).join('')}
                    </div>
                `).join('')}
            </div>
            ${!cumpleMinimo ? `<div class="text-danger text-xs mt-2">Mínimo ${minimoBoletos} boleto(s) por compra</div>` : ''}
        `;
        
        continueBtn.disabled = !cumpleMinimo;
        
        // Actualizar resumen en paso 3
        if (selectedDisplay) {
            selectedDisplay.innerHTML = formattedTickets.map(ticket => `
                <div class="bg-gray-700 text-white px-3 py-1 rounded-full text-sm">
                    #${ticket}
                </div>
            `).join('');
        }
    } else {
        selectedList.innerHTML = '<p class="text-gray-400 text-sm">No hay boletos seleccionados</p>';
        continueBtn.disabled = true;
    }
    
    // Actualizar total con conversión si hay método de pago seleccionado
    if (selectedPaymentMethod && selectedTickets.length > 0) {
        const totalUSD = selectedTickets.length * ticketPrice;
        fetch(`/rifas-premium/api/calcular_precio.php?metodo_pago_id=${selectedPaymentMethod}&precio=${totalUSD}`)
            .then(response => response.json())
            .then(data => {
                updateTotalsWithConversion(data);
            })
            .catch(error => {
                console.error('Error:', error);
                updateTotalsWithConversion();
            });
    }
}

    window.removeTicket = function(ticketNumber) {
    // Convertir el número formateado (ej: "0018") a su valor numérico (18) para comparar
    const numericTicket = parseInt(ticketNumber);
    
    // Filtrar el boleto a eliminar (comparando como números)
    selectedTickets = selectedTickets.filter(num => parseInt(num) !== numericTicket);
    
    // Actualizar estado en el grid si está visible
    document.querySelectorAll(`.ticket-selected[data-numero="${numericTicket}"]`).forEach(el => {
        el.classList.remove('ticket-selected');
        el.classList.add('ticket-available');
    });
    
    // También buscar por el número formateado (por si acaso)
    document.querySelectorAll(`.ticket-selected[data-numero="${ticketNumber}"]`).forEach(el => {
        el.classList.remove('ticket-selected');
        el.classList.add('ticket-available');
    });
    
    updateSelectedTickets();
};

    // Eventos de paginación
        document.getElementById('prev-page').addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                loadTickets(currentPage);
            }
        });

        document.getElementById('next-page').addEventListener('click', () => {
            if (currentPage < totalPages) {
                currentPage++;
                loadTickets(currentPage);
            }
        });

    // Selección aleatoria
// Selección aleatoria con verificación en tiempo real
        document.getElementById('random-btn').addEventListener('click', async function() {
            const quantity = parseInt(document.getElementById('random-quantity').value);
            
            if (isNaN(quantity) || quantity < 1) {
                mostrarMensaje('Error', 'Por favor ingresa una cantidad válida mayor a cero', true);
                return;
            }
            
            // Mostrar loading
            const randomBtn = this;
            const originalText = randomBtn.innerHTML;
            randomBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Buscando boletos...';
            randomBtn.disabled = true;
            
            try {
                // Obtener boletos disponibles del servidor con verificación en tiempo real
                const response = await fetch(`/rifas-premium/api/boletos_aleatorios.php?evento_id=<?= $evento_id ?>&cantidad=${quantity}`);
                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.message || 'Error al obtener boletos disponibles');
                }
                
                if (data.boletos.length === 0) {
                    throw new Error('No hay boletos disponibles para este evento');
                }
                
                if (data.boletos.length < quantity) {
                    throw new Error(`Solo quedan ${data.boletos.length} boletos disponibles (intentaste seleccionar ${quantity})`);
                }
                
                // Limpiar selección actual
                selectedTickets = [];
                document.querySelectorAll('.ticket-selected').forEach(el => {
                    el.classList.remove('ticket-selected');
                    el.classList.add('ticket-available');
                });
                
                // Asignar los boletos disponibles
                selectedTickets = data.boletos.map(b => b.numero_boleto).sort((a, b) => parseInt(a) - parseInt(b));
                
                // Actualizar la interfaz
                updateSelectedTickets();
                await highlightSelectedTicketsInGrid();
                
                // Mostrar mensaje de éxito
                const ticketsFormatted = selectedTickets.map(t => `#${t}`).join(', ');
                mostrarMensaje('Boletos seleccionados', `Se han seleccionado ${quantity} boletos aleatoriamente: ${ticketsFormatted}`);
                
            } catch (error) {
                mostrarMensaje('Error', error.message, true);
            } finally {
                // Restaurar botón
                randomBtn.innerHTML = originalText;
                randomBtn.disabled = false;
            }
        });

// Función para resaltar los boletos seleccionados en el grid
async function highlightSelectedTicketsInGrid() {
    // Primero, determinar en qué páginas están los boletos seleccionados
    const ticketsPerPage = 100;
    const pagesToLoad = new Set();

    selectedTickets.forEach(ticket => {
        const ticketNum = parseInt(ticket);
        const page = Math.ceil(ticketNum / ticketsPerPage);
        pagesToLoad.add(page);
    });

    // Si hay páginas a cargar y la página actual no contiene ningún boleto seleccionado,
    // cambiar a la primera página que contenga un boleto seleccionado
    if (pagesToLoad.size > 0 && !pagesToLoad.has(currentPage)) {
        // Elegimos la página más baja (primer boleto seleccionado)
        const firstPage = Math.min(...pagesToLoad);
        currentPage = firstPage;
        await loadTickets(currentPage);
    }

    // Resaltar los boletos seleccionados en la página actual
    selectedTickets.forEach(ticket => {
        const ticketElement = document.querySelector(`[data-numero="${ticket}"]`);
        if (ticketElement) {
            ticketElement.classList.remove('ticket-available');
            ticketElement.classList.add('ticket-selected');
        }
    });
}

    // Controles de cantidad
    document.getElementById('decrease-random').addEventListener('click', function() {
        const input = document.getElementById('random-quantity');
        if (parseInt(input.value) > 1) {
            input.value = parseInt(input.value) - 1;
        }
    });

    document.getElementById('increase-random').addEventListener('click', function() {
        const input = document.getElementById('random-quantity');
        input.value = parseInt(input.value) + 1;
    });

// Modificar el event listener del método de pago
document.getElementById('payment-method').addEventListener('change', function() {
    selectedPaymentMethod = this.value; // Guardar el método seleccionado
    const detallesDiv = document.getElementById('detalles-metodo-pago-seleccionado');
    const selectedOption = this.options[this.selectedIndex];
    const detallesJson = selectedOption.getAttribute('data-detalles');
    const metodo_pago_id = this.value;

    // Limpiar detalles previos
    detallesDiv.innerHTML = '';

    // Mostrar detalles del método de pago
    if (detallesJson) {
        try {
            const detalles = JSON.parse(detallesJson);
            let detallesHTML = '<ul class="space-y-1">';

            if (detalles.detalles) {
                const detallesMetodo = JSON.parse(detalles.detalles);
                for (const [key, value] of Object.entries(detallesMetodo)) {
                    detallesHTML += `<li><strong>${key}:</strong> ${value}</li>`;
                }
            }

            detallesHTML += '</ul>';
            detallesDiv.innerHTML = detallesHTML;
        } catch (error) {
            console.error('Error al parsear JSON:', error);
            detallesDiv.innerHTML = '<p class="text-danger">Error al mostrar detalles</p>';
        }
    }

    // Obtener y aplicar tasa de cambio si hay boletos seleccionados
 if (selectedTickets.length > 0) {
        const totalUSD = selectedTickets.length * ticketPrice;
        
        fetch(`/rifas-premium/api/calcular_precio.php?metodo_pago_id=${selectedPaymentMethod}&precio=${totalUSD}`)
            .then(response => response.json())
            .then(data => {
                updateTotalsWithConversion(data);
            })
            .catch(error => {
                console.error('Error:', error);
                updateTotalsWithConversion(); // Mostrar solo en USD si hay error
            });
    }
});

// Función para buscar información de un boleto
function searchTicket(ticketNumber) {
    if (!ticketNumber || isNaN(ticketNumber)) {
        mostrarMensaje('Error', 'Por favor ingresa un número de boleto válido', true);
        return;
    }

    fetch(`?action=buscar_boleto&numero=${encodeURIComponent((ticketNumber))}&evento_id=<?= $evento_id ?>`)
            .then(response => response.json())
        .then(data => {
            const resultDiv = document.getElementById('ticket-result');
            const notFoundDiv = document.getElementById('ticket-not-found');
            
            if (data.success && data.boleto) {
                // Mostrar información básica del boleto
                document.getElementById('ticket-event-name').textContent = data.boleto.evento || '<?= htmlspecialchars($evento["titulo"]) ?>';
                document.getElementById('ticket-number').textContent = data.boleto.numero;
                
                // Estado del boleto
                const statusElement = document.getElementById('ticket-status');
                statusElement.textContent = data.boleto.estado.toUpperCase();
                statusElement.className = 'px-2 py-1 rounded-full text-xs font-bold ';
                
                if (data.boleto.estado === 'pagado' || data.boleto.estado === 'ganador') {
                    statusElement.className += 'bg-green-500 text-white';
                } else if (data.boleto.estado === 'reservado') {
                    statusElement.className += 'bg-yellow-500 text-white';
                } else {
                    statusElement.className += 'bg-gray-500 text-white';
                }
                
                // Información del comprador si existe
                if (data.comprador) {
                    document.getElementById('ticket-buyer-name').textContent = data.comprador.nombre;
                    document.getElementById('ticket-buyer-state').textContent = data.comprador.estado;
                    
                    // Mostrar teléfono sin ocultar, pero con formato
                    if (data.comprador.telefono) {
                        const phone = data.comprador.telefono.toString();
                        document.getElementById('ticket-buyer-phone').textContent = phone;
                    }
                    
                
                } else {
                    document.getElementById('ticket-buyer-name').textContent = 'No asignado';
                    document.getElementById('ticket-buyer-state').textContent = 'No asignado';
                    document.getElementById('ticket-buyer-phone').textContent = 'No asignado';
                }
                
                resultDiv.classList.remove('hidden');
                notFoundDiv.classList.add('hidden');
            } else {
                resultDiv.classList.add('hidden');
                notFoundDiv.classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensaje('Error', 'Ocurrió un error al buscar el boleto', true);
        });
}

function updateTotalsWithConversion(conversionData = null) {
    const totalUSD = selectedTickets.length * ticketPrice;
    
    // Actualizar en paso 1
    document.getElementById('selected-total').textContent = `$${totalUSD.toFixed(2)} USD`;
    
    // Actualizar en paso 3
    document.getElementById('cart-total').textContent = `$${totalUSD.toFixed(2)} USD`;
    
    // Limpiar equivalentes
    document.getElementById('selected-total-equivalent').textContent = '';
    document.getElementById('cart-total-equivalent').textContent = '';
    
    // Si hay datos de conversión, mostrar también el equivalente
    if (conversionData && conversionData.success && conversionData.conversion) {
        const convertedTotal = `${conversionData.precio_convertido.toFixed(2)} ${conversionData.moneda}`;
        
        // Actualizar en paso 1
        document.getElementById('selected-total').textContent = convertedTotal;
        document.getElementById('selected-total-equivalent').textContent = `Equivalente: $${totalUSD.toFixed(2)} USD`;
        
        // Actualizar en paso 3
        document.getElementById('cart-total').textContent = convertedTotal;
        document.getElementById('cart-total-equivalent').textContent = `Equivalente: $${totalUSD.toFixed(2)} USD`;
    }
}
// Función para mostrar mensajes (éxito/error)
function mostrarMensaje(titulo, mensaje, esError = false, callback = null) {
    // Eliminar modales existentes primero
    const modalesExistentes = document.querySelectorAll('.modal-exito');
    modalesExistentes.forEach(modal => modal.remove());

    const modal = document.createElement('div');
    modal.className = 'modal-exito';
    modal.innerHTML = `
        <div class="modal-contenido">
            <h3 class="${esError ? 'error' : ''}">${titulo}</h3>
            <p>${mensaje}</p>
            <button onclick="cerrarModal(${callback ? 'true' : 'false'})">Aceptar</button>
        </div>
    `;
    document.body.appendChild(modal);
}

window.cerrarModal = function(redirectToStep1 = false) {
    const modal = document.querySelector('.modal-exito');
    if (modal) {
        modal.remove();
    }
    if (redirectToStep1) {
        showStep(1);
    }
};

// Reemplaza el evento submit del formulario
// Función para validar campos del formulario
function validarFormulario() {
    let valido = true;
    const form = document.getElementById('formulario-pago');
    
    // Validar nombre completo
    const nombreInput = form.querySelector('input[name="nombre"]');
    if (!nombreInput.value.trim() || !/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/.test(nombreInput.value)) {
        nombreInput.classList.add('border-danger');
        valido = false;
    } else {
        nombreInput.classList.remove('border-danger');
    }
    
    // Validar cédula
    const cedulaInput = form.querySelector('input[name="cedula"]');
    if (!cedulaInput.value.trim() || !/^[0-9]{8,}$/.test(cedulaInput.value)) {
        cedulaInput.classList.add('border-danger');
        valido = false;
    } else {
        cedulaInput.classList.remove('border-danger');
    }
    
    // Validar teléfono
    const telefonoInput = form.querySelector('input[name="telefono"]');
    if (!telefonoInput.value.trim() || !/^[0-9\-\+]+$/.test(telefonoInput.value)) {
        telefonoInput.classList.add('border-danger');
        valido = false;
    } else {
        telefonoInput.classList.remove('border-danger');
    }
    
    // Validar estado
    const estadoSelect = form.querySelector('select[name="estado"]');
    if (!estadoSelect.value) {
        estadoSelect.classList.add('border-danger');
        valido = false;
    } else {
        estadoSelect.classList.remove('border-danger');
    }
    
    // Validar método de pago
    const metodoPagoSelect = form.querySelector('select[name="metodo_pago"]');
    if (!metodoPagoSelect.value) {
        metodoPagoSelect.classList.add('border-danger');
        valido = false;
    } else {
        metodoPagoSelect.classList.remove('border-danger');
    }
    
    // Validar referencia de pago
    const referenciaInput = form.querySelector('input[name="referencia_transaccion"]');
    if (!referenciaInput.value.trim() || !/^[A-Za-z0-9]{6,}$/.test(referenciaInput.value)) {
        referenciaInput.classList.add('border-danger');
        valido = false;
    } else {
        referenciaInput.classList.remove('border-danger');
    }
    
    // Validar comprobante de pago
    const comprobanteInput = form.querySelector('input[name="comprobante_pago"]');
    if (!comprobanteInput.files || comprobanteInput.files.length === 0) {
        comprobanteInput.classList.add('border-danger');
        valido = false;
    } else {
        comprobanteInput.classList.remove('border-danger');
    }
    
    return valido;
}
// Añade esta función en tu código JavaScript
function updatePaginationControls(page, total) {
    document.getElementById('current-page').textContent = page;
    document.getElementById('total-pages').textContent = total;
    document.getElementById('prev-page').disabled = page === 1;
    document.getElementById('next-page').disabled = page === total || total <= 1;
}

// Modificar el evento submit del formulario para incluir validación
document.getElementById('formulario-pago').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Validar campos antes de enviar
    if (!validarFormulario()) {
        mostrarMensaje('Error', 'Por favor completa todos los campos correctamente', true);
        return;
    }
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch(this.action, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarMensaje('Éxito', data.message);
            // Opcional: resetear el formulario o redirigir
            this.reset();
            selectedTickets = [];
            updateSelectedTickets();
            showStep(1);
            window.location.hash = '#buscar-boleto';
        } else {
            // Verificar si hay boletos no disponibles en la respuesta
            if (data.unavailable_tickets && data.unavailable_tickets.length > 0) {
                // Ocultar los boletos no disponibles en todas las páginas
                data.unavailable_tickets.forEach(ticketNumber => {
                    // Quitar de la selección si estaba seleccionado
                    selectedTickets = selectedTickets.filter(num => num !== ticketNumber);
                    
                    // Buscar y ocultar los elementos en el DOM
                    const ticketElements = document.querySelectorAll(`[data-numero="${ticketNumber}"]`);
                    ticketElements.forEach(element => {
                        element.classList.add('hidden'); // Ocultar completamente
                        element.onclick = null; // Eliminar el evento click
                    });
                });
                
                // Actualizar la lista de seleccionados
                updateSelectedTickets();
                
                // Mostrar mensaje con opción de redirigir al paso 1
                mostrarMensaje('Error', data.message, true, true);
            } else {
                // Mostrar mensaje de error normal
                mostrarMensaje('Error', data.message, true);
            }
        }
    } catch (error) {
        mostrarMensaje('Error', 'Ocurrió un error al procesar la solicitud', true);
        console.error('Error:', error);
    }
});

// Agregar eventos de validación en tiempo real a los campos
document.querySelectorAll('#formulario-pago input, #formulario-pago select').forEach(input => {
    input.addEventListener('blur', function() {
        validarFormulario();
    });
});


    function iniciarContador(fechaFin) {
        const tiempoObjetivo = new Date(fechaFin).getTime();
        const intervalo = setInterval(function() {
            const ahora = new Date().getTime();
            const diferencia = tiempoObjetivo - ahora;
            if (diferencia < 0) {
                clearInterval(intervalo);
                document.getElementById("countdown-days").innerText = "00";
                document.getElementById("countdown-hours").innerText = "00";
                document.getElementById("countdown-minutes").innerText = "00";
                document.getElementById("countdown-seconds").innerText = "00";
                return;
            }
            const dias = Math.floor(diferencia / (1000 * 60 * 60 * 24));
            const horas = Math.floor((diferencia % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutos = Math.floor((diferencia % (1000 * 60 * 60)) / (1000 * 60));
            const segundos = Math.floor((diferencia % (1000 * 60)) / 1000);
            document.getElementById("countdown-days").innerText = dias.toString().padStart(2, '0');
            document.getElementById("countdown-hours").innerText = horas.toString().padStart(2, '0');
            document.getElementById("countdown-minutes").innerText = minutos.toString().padStart(2, '0');
            document.getElementById("countdown-seconds").innerText = segundos.toString().padStart(2, '0');
        }, 1);
    }
    // Agrega esto al final de tu script principal
document.getElementById('search-ticket-btn').addEventListener('click', function() {
    const ticketNumber = document.getElementById('search-ticket-input').value.trim();
    searchTicket(ticketNumber);
});

document.getElementById('search-ticket-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const ticketNumber = this.value.trim();
        searchTicket(ticketNumber);
    }
});

    // Iniciar el contador con la fecha de fin del evento
    iniciarContador("<?= date('Y-m-d H:i:s', strtotime($evento['fecha_fin'])) ?>");
    
    // Inicializar
    loadTickets(currentPage);
    updateSelectedTickets();
    
});
</script>
</body>
</html>