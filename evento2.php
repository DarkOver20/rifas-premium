<?php
require_once './admin/includes/config.php';
require_once './admin/includes/functions.php';

$evento_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$evento = obtenerEvento($evento_id);

if (!$evento) {
    header('Location: index.php');
    exit;
}

// Paginación
$pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$boletos = obtenerBoletosDisponiblesPaginados($evento_id, $pagina_actual);
$total_boletos = contarBoletosDisponibles($evento_id);
$total_paginas = ceil($total_boletos / 156);
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
    .ticket-available, .ticket-selected, .ticket-sold {
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
        .ticket-available, .ticket-selected, .ticket-sold {
            height: 1.75rem;
            width: 1.75rem;
            font-size: 0.75rem;
        }
    }
    @media (min-width: 768px) {
        .ticket-available, .ticket-selected, .ticket-sold {
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

    </style>

</head>


<body class="antialiased bg-background text-white">
    <!-- Header -->
    <header class="fixed w-full top-0 left-0 z-50 transition-all duration-300" id="navbar">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center bg-secondary/90 backdrop-blur-md rounded-full px-6 py-3 shadow-lg border border-gray-800">
                <a href="/rifas-premium/" class="flex items-center gap-2 group" aria-label="RifasA&M">
                    <img src="./assets/img/prueba" alt="Logo RifasA&M" class="h-10 w-10 transition-all duration-300 group-hover:rotate-12" loading="eager">
                    <span class="text-2xl font-bold bg-gradient-to-r from-primary to-three bg-clip-text text-transparent">
                        Rifas Premium
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
                            <img src="/rifas-premium/admin/uploads/eventos/<?= htmlspecialchars($evento['imagen']) ?>"
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
<!-- Paso 1: Selección de boletos - Versión Mejorada -->
<div id="step-1" class="bg-gray-900 rounded-2xl shadow-xl overflow-hidden border border-gray-700 mb-8">
    <div class="p-5">
        <!-- Panel principal -->
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Panel de controles -->
            <div class="lg:w-1/4 bg-gray-800/50 rounded-xl p-4 border border-gray-700">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-sliders-h text-primary"></i>
                    <span>Opciones</span>
                </h3>
                
                <!-- Selección rápida -->
                <div class="mb-5">
                    <div class="mb-5">
                        <h4 class="text-sm font-medium text-gray-300 mb-2">Cantidad para boletos aleatorios</h4>
                        <div class="flex items-center gap-2">
                            <button id="decrease-random" class="bg-gray-700 hover:bg-gray-600 text-white px-3 py-1 rounded-lg transition-colors">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" min="1" max="20" value="1"
                                class="bg-gray-800 text-white text-center w-full py-1 px-2 rounded-lg border border-gray-700 focus:ring-1 focus:ring-primary focus:border-transparent"
                                id="random-quantity">
                            <button id="increase-random" class="bg-gray-700 hover:bg-gray-600 text-white px-3 py-1 rounded-lg transition-colors">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2">
                        <div class="col-span-2 flex justify-center">
                            <button id="random-btn" class="bg-primary/90 hover:bg-primary text-white px-5 py-3 rounded-xl text-base font-semibold flex items-center justify-center gap-2 transition-all shadow-lg w-full">
                                <i class="fas fa-random"></i> Selección aleatoria
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Navegación -->
                <div class="mb-5">
                    <h4 class="text-sm font-medium text-gray-300 mb-2">Navegación</h4>
                    <div class="flex items-center justify-between bg-gray-800 rounded-lg p-2">
                        <button id="prev-page" class="bg-gray-700 hover:bg-gray-600 text-white px-3 py-1 rounded-lg disabled:opacity-50 transition-all" disabled>
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span class="text-gray-200 text-sm font-medium">
                            Página <span id="current-page" class="text-white">1</span> de <span id="total-pages" class="text-white">1</span>
                        </span>
                        <button id="next-page" class="bg-gray-700 hover:bg-gray-600 text-white px-3 py-1 rounded-lg disabled:opacity-50 transition-all">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Resumen -->
                <div class="bg-gray-800 rounded-lg p-3 border border-gray-700">
                    <h4 class="text-sm font-medium text-gray-300 mb-1">Resumen</h4>
                    <p class="text-white font-medium"><span id="selected-count">0</span> boletos seleccionados</p>
                    <div class="flex flex-wrap gap-1 sm:gap-2 min-h-10" id="selected-tickets-list"></div>
                    <div class="flex flex-col items-center mt-3">
                        <span class="text-primary font-bold block text-lg sm:text-xl" id="selected-total">$0.00 USD</span>
                        <span class="text-xs text-gray-400" id="selected-total-equivalent"></span>
                    </div>
                </div>
            </div>
            
            <!-- Panel de boletos -->
            <div class="lg:w-3/4">

                <div class="bg-gray-800/50 rounded-xl p-4 border border-gray-700">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                            <i class="fas fa-grid text-primary"></i>
                            <span>Boletos disponibles</span>
                        </h3>
                        <div class="text-sm text-gray-400">
                            <span id="available-tickets"><?= $evento['boletos_disponibles'] ?></span> disponibles
                        </div>
                    </div>
                    
                    <!-- Grid de boletos optimizado -->
                    <div class="grid grid-cols-5 sm:grid-cols-8 md:grid-cols-10 lg:grid-cols-12 xl:grid-cols-15 gap-2" id="ticket-grid">
                        <!-- Los boletos se cargarán dinámicamente con JavaScript -->
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Botón de continuar -->
        <button id="continue-btn" onclick="showStep(2)" class="w-full mt-6 bg-gradient-to-r from-primary to-primary-dark text-white py-4 rounded-xl font-bold flex items-center justify-center gap-2 hover:from-primary-dark hover:to-primary transition-all transform hover:scale-[1.01] shadow-lg hover:shadow-primary/20 disabled:opacity-50" disabled>
            <span>Continuar</span>
            <i class="fas fa-arrow-right"></i>
        </button>
    </div>
</div>
 <style>
                @media (max-width: 639px) {
                    /* En móviles, el resumen va arriba del botón continuar */
                    #mobile-summary {
                        order: 2;
                        margin-bottom: 1rem;
                    }
                    #continue-btn {
                        order: 3;
                    }
                }
                /* Grid de boletos: altura fija y scroll en móviles */
                @media (max-width: 639px) {
                    #ticket-grid {
                        max-height: 320px;
                        min-height: 180px;
                        overflow-y: auto;
                        /* Para que el scroll sea visible */
                        -webkit-overflow-scrolling: touch;
                        background: rgba(31,41,55,0.7);
                        border-radius: 0.75rem;
                        padding-bottom: 0.5rem;
                    }
                }
                </style>
                <script>
                // Mueve el resumen arriba del botón continuar en móviles
                document.addEventListener('DOMContentLoaded', function() {
                    function moveSummaryMobile() {
                        const resumen = document.querySelector('.bg-gray-800.rounded-lg.p-3');
                        const continuar = document.getElementById('continue-btn');
                        if (window.innerWidth < 640 && resumen && continuar && continuar.parentNode) {
                            continuar.parentNode.insertBefore(resumen, continuar);
                        }
                    }
                    moveSummaryMobile();
                    window.addEventListener('resize', moveSummaryMobile);
                });
                </script>
<!-- Paso 2: Datos personales + Resumen de compra -->
<div id="step-2" class="bg-gray-800/30 rounded-xl shadow-2xl overflow-hidden border border-gray-700 mb-8 hidden">
    <div class="p-6">
        <button type="button" class="text-gray-400 hover:text-white transition-all duration-300 hover:bg-gray-800/50 px-3 py-1 rounded-lg" onclick="showStep(1)">
            <i class="fas fa-arrow-left mr-2"></i> Volver a selección
        </button>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-3">
            <!-- Columna izquierda: Datos personales -->
            <div class="bg-gray-800/30 rounded-xl p-6 border border-gray-700 shadow-inner">
                <h4 class="text-lg font-semibold text-white border-b border-gray-700 flex items-center">
                    <i class="fas fa-user-circle mr-3 text-primary"></i> Tus datos personales
                </h4>
                
                <form id="formulario-pago" class="space-y-4" action="/rifas-premium/admin/includes/procesar_compra.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="evento_id" value="<?= $evento_id ?>">
                    <input type="hidden" name="boletos_seleccionados" id="boletos-seleccionados" value="">
                    
                    <!-- Nombre completo -->
                    <div class="form-group">
                        <label class="block text-gray-300 mb-2 text-sm font-medium">Nombre completo</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                                <i class="fas fa-user"></i>
                            </div>
                            <input type="text" id="full-name" name="nombre" required
                                class="w-full bg-gray-800/50 text-white pl-10 pr-4 py-3 rounded-lg border border-gray-700 focus:border-primary focus:ring-2 focus:ring-primary/30 focus:outline-none transition-all"
                                placeholder="Ej. Alfonso Perez"
                                pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo se permiten letras y espacios.">
                        </div>
                    </div>
                    
                    <!-- Cédula y Teléfono -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="block text-gray-300 mb-2 text-sm font-medium">Cédula</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                                    <i class="fas fa-id-card"></i>
                                </div>
                                <input type="text" id="id-number" name="cedula" required
                                    class="w-full bg-gray-800/50 text-white pl-10 pr-4 py-3 rounded-lg border border-gray-700 focus:border-primary focus:ring-2 focus:ring-primary/30 focus:outline-none transition-all"
                                    placeholder="Ej. 30125963"
                                    pattern="[0-9]{8,}" title="Debe contener al menos 8 números.">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="block text-gray-300 mb-2 text-sm font-medium">Teléfono</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <input type="tel" id="phone" name="telefono" required
                                    class="w-full bg-gray-800/50 text-white pl-10 pr-4 py-3 rounded-lg border border-gray-700 focus:border-primary focus:ring-2 focus:ring-primary/30 focus:outline-none transition-all"
                                    placeholder="Ej. +58 4121234567"
                                    pattern="[0-9\-\+]+" title="No se permiten letras ni caracteres especiales. Usa el formato +00 000000000">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Estado -->
                    <div class="form-group">
                        <label class="block text-gray-300 mb-2 text-sm font-medium">Estado</label>
                        <div class="relative">
                            
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                            </div>
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
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">

                            </div>
                        </div>
                    </div>
            </div>
            
            <!-- Columna derecha: Método de pago -->
            <div class="space-y-6">
                <div class="bg-gradient-to-br from-gray-800/70 to-gray-900/70 rounded-xl p-6 border border-gray-700 shadow-inner">
                    <h4 class="text-lg font-semibold text-white mb-6 pb-2 border-b border-gray-700 flex items-center">
                        <i class="fas fa-credit-card mr-3 text-primary"></i> Método de pago
                    </h4>
                    
                    <div class="space-y-4">
                        <!-- Selección de método -->
                        <div class="form-group">
                            <label class="block text-gray-300 mb-2 text-sm font-medium">Selecciona tu método de pago</label>
<div class="relative">
    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
        <i class="fas fa-wallet"></i>
    </div>
    <select id="payment-method" name="metodo_pago" required
        class="w-full bg-gray-800 text-white pl-10 pr-4 py-3 rounded-lg border border-gray-700 focus:border-primary focus:ring-2 focus:ring-primary/30 focus:outline-none appearance-none transition-all">
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
    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
        <i class="fas fa-chevron-down"></i>
    </div>
</div>
<div id="detalles-metodo-pago-seleccionado" class="mt-2 text-m text-gray-300"></div>
                            <!-- Detalles del método de pago (se llena dinámicamente) -->
                            <div id="detalles-metodo-pago-seleccionado" class="mt-4 p-4 bg-gray-800/30 rounded-lg border border-gray-700 hidden animate-fade-in">
                                <div class="flex items-start">
                                    <div id="metodo-icono" class="text-2xl mr-3 text-primary"></div>
                                    <div>
                                        <h5 id="metodo-nombre" class="font-bold text-white"></h5>
                                        <div id="metodo-datos" class="text-sm text-gray-300 mt-1 space-y-1"></div>
                                        <div id="metodo-instructions" class="text-xs text-gray-400 mt-2 italic"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Referencia de pago -->
                        <div class="form-group">
                            <label class="block text-gray-300 mb-2 text-sm font-medium">Referencia del pago</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                                    <i class="fas fa-receipt"></i>
                                </div>
                                <input type="text" id="transaction-reference" name="referencia_transaccion" required
                                    class="w-full bg-gray-800/50 text-white pl-10 pr-4 py-3 rounded-lg border border-gray-700 focus:border-primary focus:ring-2 focus:ring-primary/30 focus:outline-none transition-all"
                                    placeholder="Número de referencia o código"
                                    pattern="[A-Za-z0-9]{6,}" title="Debe contener al menos 6 caracteres alfanuméricos.">
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Ingresa el número de referencia, código o ID de tu transacción</p>
                        </div>
                        
                        <!-- Comprobante -->
                        <div class="form-group">
                            <label class="block text-gray-300 mb-2 text-sm font-medium">Comprobante de pago</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                                    <i class="fas fa-file-upload"></i>
                                </div>
                                <input type="file" id="payment-proof" name="comprobante_pago" accept="image/*,.pdf" required
                                    class="w-full bg-gray-800/50 text-white pl-10 pr-4 py-2 rounded-lg border border-gray-700 focus:border-primary focus:ring-2 focus:ring-primary/30 focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary/20 file:text-primary hover:file:bg-primary/30 transition-all">
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Resumen de compra -->
        <div class="bg-gradient-to-r from-gray-800/70 to-gray-900/70 rounded-xl p-6 mt-6 border border-gray-700 shadow-inner">
            <h4 class="text-lg font-semibold text-white mb-4 pb-2 border-b border-gray-700 flex items-center">
                <i class="fas fa-shopping-cart mr-3 text-primary"></i> Boletos seleccionados
            </h4>
                <div class="flex flex-wrap gap-2" id="selected-tickets-display">
                    <!-- Se llenará dinámicamente con JavaScript -->
                </div>
                
                <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-700">
                    <span class="text-white font-bold text-lg">Total a pagar:</span>
                    <div class="text-right">
                        <span id="cart-total" class="text-2xl font-bold text-primary">$0.00 USD</span>
                        <div class="text-xs text-gray-400" id="cart-total-equivalent"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Botón de compra -->
        <div>
            <button type="submit" form="formulario-pago" 
                class="w-full bg-gradient-to-r from-primary to-primary-dark text-white py-4 rounded-xl font-bold flex items-center justify-center gap-2 hover:from-primary-dark hover:to-primary transition-all transform hover:scale-[1.01] shadow-lg hover:shadow-primary/20">
                <i class="fas fa-lock mr-2"></i> Confirmar y Pagar
            </button>
            
            <div class="flex items-center justify-center mt-4 text-xs text-gray-400">
                <i class="fas fa-shield-alt mr-2 text-primary"></i> Transacción segura - Tus datos están protegidos
            </div>
        </div>
    </div>
</div>

</div>  </div>  </div>
     <?php endif; ?>  </main>
<!-- Sección de búsqueda de boleto -->
<section class="bg-background py-12" id="buscar-boleto"><br>
    <div class="container mx-auto px-4">
        <div class="bg-secondary rounded-xl shadow-lg overflow-hidden border border-gray-800 p-6">
            <h2 class="text-2xl font-bold text-white mb-6">Verificar Boleto</h2>
            
            <div class="flex flex-col md:flex-row gap-4 mb-6">
                <input type="text" id="search-ticket-input" 
                       class="flex-1 bg-gray-800 text-white px-4 py-3 rounded-lg border border-gray-700 focus:border-primary focus:outline-none" 
                       placeholder="Ingresa el número de boleto">
                <button id="search-ticket-btn" 
                        class="bg-primary text-white px-6 py-3 rounded-lg font-bold hover:bg-primary-dark transition-all">
                    <i class="fas fa-search mr-2"></i> Verificar
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
                <p class="text-gray-400">Por favor asegurate de escribir correctamente el numero de boleto.</p>
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
                 alt="Rifas Premium" 
                 class="h-10 w-10 rounded-lg"
                 loading="lazy">
            <span class="text-2xl font-bold bg-gradient-to-r from-primary to-three bg-clip-text text-transparent">
              Rifas Premium
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
            © 2024 Rifas Premium. Todos los derechos reservados.
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
    // =============================================
    // VARIABLES GLOBALES Y CONFIGURACIÓN INICIAL
    // =============================================
    let selectedTickets = []; // Array para almacenar boletos seleccionados
    const ticketPrice = <?= $evento['precio_boleto'] ?>; // Precio por boleto
    const ticketsPerPage = 156; // Boletos mostrados por página
    let currentPage = <?= $pagina_actual ?>; // Página actual
    const totalPages = <?= $total_paginas ?>; // Total de páginas
    let selectedPaymentMethod = null; // Método de pago seleccionado

    // =============================================
    // FUNCIONES DE NAVEGACIÓN Y VISUALIZACIÓN
    // =============================================
    
    /**
     * Muestra/oculta los pasos del proceso de compra
     * @param {number} stepNumber - Número del paso a mostrar
     */
    window.showStep = function(stepNumber) {
        // Oculta todos los pasos
        document.querySelectorAll('[id^="step-"]').forEach(step => {
            step.classList.add('hidden');
        });

        // Muestra el paso seleccionado
        const selectedStep = document.getElementById(`step-${stepNumber}`);
        if (selectedStep) {
            selectedStep.classList.remove('hidden');
            selectedStep.scrollIntoView({ behavior: 'smooth', block: 'start' });
            
            // Restaurar método de pago si volvemos al paso 2
            if (stepNumber === 2 && selectedPaymentMethod) {
                const paymentSelect = document.getElementById('payment-method');
                paymentSelect.value = selectedPaymentMethod;
                paymentSelect.dispatchEvent(new Event('change'));
            }
        }

        // Actualiza indicadores visuales de progreso
        updateStepIndicators(stepNumber);

        // Guarda boletos seleccionados al pasar al paso 2
        if (stepNumber === 2) {
            document.getElementById('boletos-seleccionados').value = JSON.stringify(selectedTickets);
        }
    };

    /**
     * Actualiza los indicadores visuales de los pasos
     * @param {number} currentStep - Paso actual
     */
    function updateStepIndicators(currentStep) {
        document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
            indicator.classList.toggle('bg-primary', index + 1 <= currentStep);
            indicator.classList.toggle('bg-gray-700', index + 1 > currentStep);
        });
    }

    // =============================================
    // GESTIÓN DE BOLETOS (SELECCIÓN, PAGINACIÓN)
    // =============================================
    
    /**
     * Carga los boletos de una página específica
     * @param {number} page - Número de página a cargar
     */
function loadTickets(page) {
    fetch(`/rifas-premium/api/boletos.php?evento_id=<?= $evento_id ?>&pagina=${page}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderTickets(data.boletos);
                // Usar el mínimo entre total_paginas de PHP y el de la respuesta AJAX
                const calculatedTotalPages = Math.min(<?= $total_paginas ?>, data.total_paginas);
                updatePaginationControls(page, calculatedTotalPages);
            } else {
                console.error('Error al cargar boletos:', data.message);
            }
        });
}

    /**
     * Renderiza los boletos en el grid
     * @param {Array} boletos - Array de objetos de boletos
     */
    function renderTickets(boletos) {
        const ticketGrid = document.getElementById('ticket-grid');
        ticketGrid.innerHTML = '';
        
        boletos.forEach(boleto => {
            const ticketElement = createTicketElement(boleto);
            ticketGrid.appendChild(ticketElement);
        });
    }
    
// Implementar caché simple
const ticketCache = new Map();

async function loadTickets(page) {
    if (ticketCache.has(page)) {
        renderTickets(ticketCache.get(page));
        return;
    }

    try {
        const response = await fetch(`/rifas-premium/api/boletos.php?evento_id=<?= $evento_id ?>&pagina=${page}`);
        const data = await response.json();
        
        if (data.success) {
            ticketCache.set(page, data.boletos);
            renderTickets(data.boletos);
            updatePaginationControls(page, Math.min(<?= $total_paginas ?>, data.total_paginas));
        }
    } catch (error) {
        console.error('Error al cargar boletos:', error);
    }
}
    /**
     * Crea un elemento DOM para un boleto
     * @param {Object} boleto - Datos del boleto
     * @return {HTMLElement} - Elemento DOM del boleto
     */
    function createTicketElement(boleto) {
        const ticketElement = document.createElement('div');
        const isAvailable = boleto.estado === 'disponible';
        const isSelected = selectedTickets.includes(boleto.numero_boleto);
        
        ticketElement.className = `flex items-center justify-center ${
            isAvailable ? 'ticket-available' : 'ticket-sold'
        } ${isSelected ? 'ticket-selected' : ''}`;
        
        ticketElement.textContent = boleto.numero_boleto;
        ticketElement.dataset.numero = boleto.numero_boleto;

        if (isAvailable) {
            ticketElement.addEventListener('click', () => toggleTicketSelection(ticketElement, boleto.numero_boleto));
        }

        return ticketElement;
    }

    /**
     * Alterna la selección de un boleto
     * @param {HTMLElement} element - Elemento DOM del boleto
     * @param {string} ticketNumber - Número del boleto
     */
    function toggleTicketSelection(element, ticketNumber) {
        const isSelected = element.classList.contains('ticket-selected');
        
        if (isSelected) {
            element.classList.remove('ticket-selected');
            element.classList.add('ticket-available');
            selectedTickets = selectedTickets.filter(num => num !== ticketNumber);
        } else {
            element.classList.remove('ticket-available');
            element.classList.add('ticket-selected');
            selectedTickets.push(ticketNumber);
        }
        
        updateSelectedTickets();
    }

    /**
     * Actualiza los controles de paginación
     * @param {number} page - Página actual
     * @param {number} total - Total de páginas
     */
    function updatePaginationControls(page, total) {
        document.getElementById('current-page').textContent = page;
        document.getElementById('total-pages').textContent = total;
        document.getElementById('prev-page').disabled = page === 1;
        document.getElementById('next-page').disabled = page === total;
    }

    // Eventos de paginación
    document.getElementById('prev-page').addEventListener('click', () => {
        if (currentPage > 1) loadTickets(--currentPage);
    });

    document.getElementById('next-page').addEventListener('click', () => {
        if (currentPage < totalPages) loadTickets(++currentPage);
    });

    // =============================================
    // GESTIÓN DE MÉTODOS DE PAGO Y CONVERSIONES
    // =============================================
    
    /**
     * Actualiza los totales con conversión de moneda si aplica
     * @param {Object|null} conversionData - Datos de conversión de moneda
     */
    function updateTotalsWithConversion(conversionData = null) {
        const totalUSD = selectedTickets.length * ticketPrice;
        const usdFormatted = `$${totalUSD.toFixed(2)} USD`;
        
        // Actualizar en paso 1 y 3
        ['selected-total', 'cart-total'].forEach(id => {
            const element = document.getElementById(id);
            if (element) element.textContent = usdFormatted;
        });
        
        // Limpiar equivalentes
        ['selected-total-equivalent', 'cart-total-equivalent'].forEach(id => {
            const element = document.getElementById(id);
            if (element) element.textContent = '';
        });
        
        // Si hay datos de conversión, mostrar también el equivalente
        if (conversionData?.success && conversionData.conversion) {
            const convertedTotal = `${conversionData.precio_convertido.toFixed(2)} ${conversionData.moneda}`;
            const equivalentText = `Equivalente: ${usdFormatted}`;
            
            // Actualizar en paso 1
            document.getElementById('selected-total').textContent = convertedTotal;
            document.getElementById('selected-total-equivalent').textContent = equivalentText;
            
            // Actualizar en paso 3
            document.getElementById('cart-total').textContent = convertedTotal;
            document.getElementById('cart-total-equivalent').textContent = equivalentText;
        }
    }

    // Evento cambio de método de pago
    document.getElementById('payment-method').addEventListener('change', function() {
        selectedPaymentMethod = this.value;
        const selectedOption = this.options[this.selectedIndex];
        const detallesJson = selectedOption.getAttribute('data-detalles');
        
        // Mostrar detalles del método de pago
        displayPaymentMethodDetails(detallesJson);
        
        // Actualizar total con conversión si hay boletos seleccionados
        if (selectedTickets.length > 0) {
            updateTotalWithConversion(selectedPaymentMethod);
        }
    });

    /**
     * Muestra los detalles de un método de pago
     * @param {string} detallesJson - JSON con los detalles del método
     */
    function displayPaymentMethodDetails(detallesJson) {
        const detallesDiv = document.getElementById('detalles-metodo-pago-seleccionado');
        detallesDiv.innerHTML = '';
        
        if (!detallesJson) return;
        
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

    /**
     * Actualiza el total con conversión de moneda
     * @param {string} metodoPagoId - ID del método de pago
     */
    function updateTotalWithConversion(metodoPagoId) {
        const totalUSD = selectedTickets.length * ticketPrice;
        
        fetch(`/rifas-premium/api/calcular_precio.php?metodo_pago_id=${metodoPagoId}&precio=${totalUSD}`)
            .then(response => response.json())
            .then(data => updateTotalsWithConversion(data))
            .catch(error => {
                console.error('Error:', error);
                updateTotalsWithConversion();
            });
    }

    // =============================================
    // GESTIÓN DE BOLETOS SELECCIONADOS
    // =============================================
    
    /**
     * Actualiza la interfaz con los boletos seleccionados
     */

// Modificar la función updateSelectedTickets para usar la nueva función
function updateSelectedTickets() {
    const selectedCount = document.getElementById('selected-count');
    const selectedList = document.getElementById('selected-tickets-list');
    const continueBtn = document.getElementById('continue-btn');
    const cartCount = document.getElementById('cart-count');
    const selectedDisplay = document.getElementById('selected-tickets-display');
    
    // Actualizar contadores
    selectedCount.textContent = selectedTickets.length;
    if (cartCount) cartCount.textContent = selectedTickets.length;
    
    // Actualizar totales - ahora verifica si hay método de pago seleccionado
    if (selectedPaymentMethod) {
        updateTotalWithConversion(selectedPaymentMethod);
    } else {
        updateTotalsWithConversion();
    }
    
    // Resto del código para actualizar la lista de boletos...
    if (selectedTickets.length > 0) {
        selectedList.innerHTML = `
            <div class="flex flex-wrap gap-2">
                ${selectedTickets.sort((a, b) => a - b).map(ticket => `
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
        
        if (selectedDisplay) {
            selectedDisplay.innerHTML = selectedTickets.map(ticket => `
                <div class="bg-gray-700 text-white px-3 py-1 rounded-full text-sm">
                    #${ticket}
                </div>
            `).join('');
        }
    } else {
        selectedList.innerHTML = '';
        continueBtn.disabled = true;
    }
}

    /**
     * Actualiza los contadores de boletos seleccionados
     */
    function updateCounters() {
        document.getElementById('selected-count').textContent = selectedTickets.length;
        const cartCount = document.getElementById('cart-count');
        if (cartCount) cartCount.textContent = selectedTickets.length;
    }

    /**
     * Actualiza los totales monetarios
     */
    function updateTotals() {
        const totalUSD = selectedTickets.length * ticketPrice;
        document.getElementById('selected-total').textContent = `$${totalUSD.toFixed(2)} USD`;
        updateTotalsWithConversion();
    }

    /**
     * Actualiza la lista visual de boletos seleccionados
     * @param {boolean} cumpleMinimo - Indica si se cumple el mínimo requerido
     * @param {number} minimoBoletos - Mínimo de boletos requeridos
     */
    function updateSelectedTicketsList(cumpleMinimo, minimoBoletos) {
        const selectedList = document.getElementById('selected-tickets-list');
        const continueBtn = document.getElementById('continue-btn');
        
        if (selectedTickets.length > 0) {
            const formattedTickets = formatTicketNumbers(selectedTickets);
            const groupedTickets = groupTickets(formattedTickets, 10);
            
            selectedList.innerHTML = `
                <div class="flex flex-wrap gap-2">
                    ${groupedTickets.map(group => `
                        <div class="flex flex-wrap gap-2 mb-2">
                            ${group.map(ticket => createSelectedTicketBadge(ticket)).join('')}
                        </div>
                    `).join('')}
                </div>
                ${!cumpleMinimo ? `<div class="text-danger text-xs mt-2">Mínimo ${minimoBoletos} boleto(s) por compra</div>` : ''}
            `;
            
            continueBtn.disabled = !cumpleMinimo;
        } else {
            continueBtn.disabled = true;
        }
            updateTotalsWithConversion();
    }

    /**
     * Formatea los números de boletos a 4 dígitos
     * @param {Array} tickets - Array de números de boletos
     * @return {Array} - Boletos formateados
     */
    function formatTicketNumbers(tickets) {
        return tickets.map(t => parseInt(t).toString().padStart(4, '0'));
    }

    /**
     * Agrupa boletos en bloques para mejor visualización
     * @param {Array} tickets - Array de boletos
     * @param {number} groupSize - Tamaño de cada grupo
     * @return {Array} - Array de grupos de boletos
     */
    function groupTickets(tickets, groupSize) {
        const grouped = [];
        for (let i = 0; i < tickets.length; i += groupSize) {
            grouped.push(tickets.slice(i, i + groupSize));
        }
        return grouped;
    }

    /**
     * Crea un elemento DOM para un boleto seleccionado
     * @param {string} ticket - Número de boleto
     * @return {string} - HTML del boleto
     */
    function createSelectedTicketBadge(ticket) {
        return `
            <div class="bg-primary text-white px-2 py-1 rounded-full text-xs flex items-center">
                #${ticket}
                <button class="ml-1 text-xs hover:text-accent" onclick="removeTicket('${ticket}')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
    }

    /**
     * Actualiza el resumen en el paso 3
     */
    function updateCartSummary() {
        const selectedDisplay = document.getElementById('selected-tickets-display');
        if (!selectedDisplay) return;
        
        const formattedTickets = formatTicketNumbers(selectedTickets);
        selectedDisplay.innerHTML = formattedTickets.map(ticket => `
            <div class="bg-gray-700 text-white px-3 py-1 rounded-full text-sm">
                #${ticket}
            </div>
        `).join('');
    }

    /**
     * Elimina un boleto de la selección
     * @param {string} ticketNumber - Número de boleto a eliminar
     */
    window.removeTicket = function(ticketNumber) {
        const numericTicket = parseInt(ticketNumber);
        selectedTickets = selectedTickets.filter(num => parseInt(num) !== numericTicket);
        
        // Actualizar estado en el grid
        document.querySelectorAll(`.ticket-selected[data-numero="${numericTicket}"]`).forEach(el => {
            el.classList.remove('ticket-selected');
            el.classList.add('ticket-available');
        });
            updateTotalsWithConversion();
        updateSelectedTickets();
    };

    // =============================================
    // SELECCIÓN ALEATORIA DE BOLETOS
    // =============================================
    
    // Evento para selección aleatoria
    document.getElementById('random-btn').addEventListener('click', handleRandomSelection);

    /**
     * Maneja la selección aleatoria de boletos
     */
    async function handleRandomSelection() {
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
            const availableTickets = await fetchAvailableTickets();
            
            if (availableTickets.length === 0) {
                throw new Error('No hay boletos disponibles para este evento');
            }
            
            if (availableTickets.length < quantity) {
                throw new Error(`Solo quedan ${availableTickets.length} boletos disponibles (intentaste seleccionar ${quantity})`);
            }
            
            // Seleccionar boletos aleatorios
            selectRandomTickets(availableTickets, quantity);
            
            // Mostrar éxito
            mostrarMensaje('Boletos seleccionados', 
                `Se han seleccionado ${quantity} boletos aleatoriamente: ${
                    selectedTickets.map(t => `#${t}`).join(', ')
                }`);
            
        } catch (error) {
            mostrarMensaje('Error', error.message, true);
        } finally {
            randomBtn.innerHTML = originalText;
            randomBtn.disabled = false;
        }
        
    }

    /**
     * Obtiene los boletos disponibles del servidor
     * @return {Array} - Array de números de boletos disponibles
     */
    async function fetchAvailableTickets() {
        const response = await fetch(`/rifas-premium/api/todos_boletos.php?evento_id=<?= $evento_id ?>`);
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Error al obtener boletos disponibles');
        }
        
        return data.boletos
            .filter(boleto => boleto.estado === 'disponible')
            .map(boleto => boleto.numero_boleto);
    }

    /**
     * Selecciona boletos aleatorios
     * @param {Array} availableTickets - Boletos disponibles
     * @param {number} quantity - Cantidad a seleccionar
     */
    function selectRandomTickets(availableTickets, quantity) {
        // Limpiar selección actual
        selectedTickets = [];
        document.querySelectorAll('.ticket-selected').forEach(el => {
            el.classList.remove('ticket-selected');
            el.classList.add('ticket-available');
        });
        
        // Seleccionar aleatoriamente sin repetición
        const shuffled = [...availableTickets].sort(() => 0.5 - Math.random());
        selectedTickets = shuffled.slice(0, quantity).sort((a, b) => parseInt(a) - parseInt(b));
        
        // Actualizar interfaz
        updateSelectedTickets();
        highlightSelectedTicketsInGrid();
    }

    /**
     * Resalta los boletos seleccionados en el grid
     */
    async function highlightSelectedTicketsInGrid() {
        // Determinar páginas a cargar
        const pagesToLoad = new Set(
            selectedTickets.map(t => Math.ceil(parseInt(t) / ticketsPerPage))
        );

        // Cargar primera página si es necesario
        if (pagesToLoad.size > 0 && !pagesToLoad.has(currentPage)) {
            currentPage = Math.min(...pagesToLoad);
            await loadTickets(currentPage);
        }

        // Resaltar boletos en la página actual
        selectedTickets.forEach(ticket => {
            const ticketElement = document.querySelector(`[data-numero="${ticket}"]`);
            if (ticketElement) {
                ticketElement.classList.remove('ticket-available');
                ticketElement.classList.add('ticket-selected');
            }
        });
    }

    // Controles de cantidad para selección aleatoria
    document.getElementById('decrease-random').addEventListener('click', () => {
        const input = document.getElementById('random-quantity');
        if (parseInt(input.value) > 1) input.value = parseInt(input.value) - 1;
    });

    document.getElementById('increase-random').addEventListener('click', () => {
        const input = document.getElementById('random-quantity');
        input.value = parseInt(input.value) + 1;
    });

    // =============================================
    // BÚSQUEDA DE BOLETOS
    // =============================================
    
    // Eventos de búsqueda
    document.getElementById('search-ticket-btn').addEventListener('click', () => {
        const ticketNumber = document.getElementById('search-ticket-input').value.trim();
        searchTicket(ticketNumber);
    });

    document.getElementById('search-ticket-input').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            const ticketNumber = e.target.value.trim();
            searchTicket(ticketNumber);
        }
    });

    /**
     * Busca información de un boleto específico
     * @param {string} ticketNumber - Número de boleto a buscar
     */
    function searchTicket(ticketNumber) {
        if (!ticketNumber || isNaN(ticketNumber)) {
            mostrarMensaje('Error', 'Por favor ingresa un número de boleto válido', true);
            return;
        }

        fetch(`?action=buscar_boleto&numero=${encodeURIComponent(ticketNumber)}&evento_id=<?= $evento_id ?>`)
            .then(response => response.json())
            .then(displayTicketSearchResult)
            .catch(error => {
                console.error('Error:', error);
                mostrarMensaje('Error', 'Ocurrió un error al buscar el boleto', true);
            });
    }

    /**
     * Muestra el resultado de la búsqueda de un boleto
     * @param {Object} data - Datos de la respuesta
     */
    function displayTicketSearchResult(data) {
        const resultDiv = document.getElementById('ticket-result');
        const notFoundDiv = document.getElementById('ticket-not-found');
        
        if (data.success && data.boleto) {
            // Mostrar información básica del boleto
            document.getElementById('ticket-event-name').textContent = 
                data.boleto.evento || '<?= htmlspecialchars($evento["titulo"]) ?>';
            document.getElementById('ticket-number').textContent = data.boleto.numero;
            
            // Estado del boleto
            updateTicketStatus(data.boleto.estado);
            
            // Información del comprador
            updateBuyerInfo(data.comprador);
            
            resultDiv.classList.remove('hidden');
            notFoundDiv.classList.add('hidden');
        } else {
            resultDiv.classList.add('hidden');
            notFoundDiv.classList.remove('hidden');
        }
    }

    /**
     * Actualiza el estado visual del boleto
     * @param {string} status - Estado del boleto
     */
    function updateTicketStatus(status) {
        const statusElement = document.getElementById('ticket-status');
        statusElement.textContent = status.toUpperCase();
        
        // Clases CSS según estado
        const statusClasses = {
            'pagado': 'bg-green-500 text-white',
            'ganador': 'bg-green-500 text-white',
            'reservado': 'bg-yellow-500 text-white',
            'default': 'bg-gray-500 text-white'
        };
        
        statusElement.className = `px-2 py-1 rounded-full text-xs font-bold ${
            statusClasses[status] || statusClasses.default
        }`;
    }

    /**
     * Actualiza la información del comprador
     * @param {Object|null} buyer - Datos del comprador
     */
    function updateBuyerInfo(buyer) {
        const defaultText = 'No asignado';
        
        document.getElementById('ticket-buyer-name').textContent = buyer?.nombre || defaultText;
        document.getElementById('ticket-buyer-state').textContent = buyer?.estado || defaultText;
        document.getElementById('ticket-buyer-phone').textContent = 
            buyer?.telefono ? buyer.telefono.toString() : defaultText;
    }

    // =============================================
    // MANEJO DE FORMULARIO Y COMPRA
    // =============================================
    
    // Reemplaza el evento submit del formulario
    document.getElementById('formulario-pago').addEventListener('submit', handleFormSubmit);

    /**
     * Maneja el envío del formulario de compra
     * @param {Event} e - Evento de submit
     */
    async function handleFormSubmit(e) {
        e.preventDefault();
        
        try {
            const response = await fetch(e.target.action, {
                method: 'POST',
                body: new FormData(e.target)
            });
            
            const data = await response.json();
            
            if (data.success) {
                handleSuccessfulPurchase(data.message);
            } else {
                handlePurchaseError(data);
            }
        } catch (error) {
            mostrarMensaje('Error', 'Ocurrió un error al procesar la solicitud', true);
            console.error('Error:', error);
        }
    }

    /**
     * Maneja una compra exitosa
     * @param {string} message - Mensaje de éxito
     */
    function handleSuccessfulPurchase(message) {
        mostrarMensaje('Éxito', message);
        document.getElementById('formulario-pago').reset();
        selectedTickets = [];
        updateSelectedTickets();
        showStep(1);
        window.location.hash = '#buscar-boleto';
    }

    /**
     * Maneja errores en la compra
     * @param {Object} errorData - Datos del error
     */
    function handlePurchaseError(errorData) {
        // Verificar si hay boletos no disponibles
        if (errorData.unavailable_tickets?.length > 0) {
            // Eliminar boletos no disponibles
            errorData.unavailable_tickets.forEach(ticketNumber => {
                selectedTickets = selectedTickets.filter(num => num !== ticketNumber);
                
                // Ocultar en el DOM
                document.querySelectorAll(`[data-numero="${ticketNumber}"]`).forEach(el => {
                    el.classList.add('hidden');
                    el.onclick = null;
                });
            });
            
            updateSelectedTickets();
            mostrarMensaje('Error', errorData.message, true, true);
        } else {
            mostrarMensaje('Error', errorData.message, true);
        }
    }

    // =============================================
    // FUNCIONES UTILITARIAS Y MENSAJES
    // =============================================
    
    /**
     * Muestra un mensaje modal
     * @param {string} titulo - Título del mensaje
     * @param {string} mensaje - Contenido del mensaje
     * @param {boolean} esError - Indica si es un mensaje de error
     * @param {boolean} redirectToStep1 - Indica si redirigir al paso 1
     */
    function mostrarMensaje(titulo, mensaje, esError = false, redirectToStep1 = false) {
        // Eliminar modales existentes
        document.querySelectorAll('.modal-exito').forEach(modal => modal.remove());

        const modal = document.createElement('div');
        modal.className = 'modal-exito';
        modal.innerHTML = `
            <div class="modal-contenido">
                <h3 class="${esError ? 'error' : ''}">${titulo}</h3>
                <p>${mensaje}</p>
                <button onclick="cerrarModal(${redirectToStep1})">Aceptar</button>
            </div>
        `;
        document.body.appendChild(modal);
    }

    window.cerrarModal = function(redirectToStep1 = false) {
        const modal = document.querySelector('.modal-exito');
        if (modal) modal.remove();
        if (redirectToStep1) showStep(1);
    };

    // =============================================
    // CONTADOR DE TIEMPO
    // =============================================
    
    /**
     * Inicia un contador regresivo
     * @param {string} fechaFin - Fecha de finalización
     */
    function iniciarContador(fechaFin) {
        const tiempoObjetivo = new Date(fechaFin).getTime();
        const intervalo = setInterval(function() {
            updateCountdown(tiempoObjetivo, intervalo);
        }, 1000);
        // Llamar una vez al cargar
        updateCountdown(tiempoObjetivo, intervalo);
    }

    /**
     * Actualiza el contador regresivo
     * @param {number} tiempoObjetivo - Timestamp de finalización
     * @param {number} intervalo - ID del intervalo
     */
    function updateCountdown(tiempoObjetivo, intervalo) {
        const ahora = new Date().getTime();
        const diferencia = tiempoObjetivo - ahora;
        
        if (diferencia < 0) {
            clearInterval(intervalo);
            ['days', 'hours', 'minutes', 'seconds'].forEach(unit => {
                document.getElementById(`countdown-${unit}`).innerText = '00';
            });
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
    }

    // =============================================
    // INICIALIZACIÓN
    // =============================================
    
    // Iniciar contador con la fecha de fin del evento
    iniciarContador("<?= date('Y-m-d H:i:s', strtotime($evento['fecha_fin'])) ?>");
    
    // Cargar boletos iniciales y actualizar selección
    loadTickets(currentPage);
    updateSelectedTickets();
});
</script>
</body>
</html>