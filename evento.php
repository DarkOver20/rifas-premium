<?php
require_once './admin/includes/config.php';
require_once './admin/includes/functions.php';

$evento_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$evento = obtenerEvento($evento_id);

if (!$evento) {
    header('Location: index.php');
    exit;
}

$boletos_disponibles = obtenerBoletosDisponibles($evento_id);
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
        height: 1.5rem;
        width: 1.5rem;
        font-size: 0.65rem;
        margin: 0.1rem;
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
    </style>
</head>
<body class="antialiased bg-background text-white">
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

                <!-- Columna derecha (Proceso de compra) -->
                <div class="lg:col-span-8">
                    <!-- Pasos de compra -->
                    <div class="mb-8">
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
                            <div class="w-16 h-0.5 bg-gray-700"></div>
                            <div class="flex items-center">
                                <div class="step-indicator w-8 h-8 rounded-full bg-gray-700 text-white flex items-center justify-center">3</div>
                                <span class="ml-2">Pago</span>
                            </div>
                        </div>
                    </div>

                    <!-- Paso 1: Selección de boletos -->
                    <div id="step-1" class="bg-secondary rounded-xl shadow-lg overflow-hidden border border-gray-800 mb-8">
                        <div class="p-6">
                            <h2 class="text-2xl font-bold text-white mb-6">Selecciona tus boletos</h2>
                            
                            <!-- Selector de método aleatorio -->
                            <div class="mb-6 flex items-center gap-4">
                                <div class="flex items-center flex-1">
                                    <label class="block text-gray-300 mr-2">Cantidad:</label>
                                    <div class="flex items-center max-w-xs">
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
                                <button id="random-btn" class="bg-primary text-white px-4 py-2 rounded-lg font-bold flex items-center justify-center gap-2 hover:bg-primary-dark transition-all">
                                    <i class="fas fa-random"></i>
                                    <span>Selección aleatoria</span>
                                </button>
                            </div>
                            
                            <!-- Grid de boletos con paginación -->
                            <div class="mb-6">
                                <label class="block text-gray-300 mb-2">Boletos disponibles:</label>
                                
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
                            </div>                            
                            <!-- Boletos seleccionados -->
                            <div class="mb-6 bg-gray-800/50 rounded-lg p-4">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-gray-300">Boletos seleccionados: <span id="selected-count">0</span></span>
                                    <span class="text-primary font-bold">Total: $<span id="selected-total">0.00</span></span>
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

                    <!-- Paso 2: Datos personales -->
                    <div id="step-2" class="bg-secondary rounded-xl shadow-lg overflow-hidden border border-gray-800 mb-8 hidden">
                        <div class="p-6">
                            <h2 class="text-2xl font-bold text-white mb-6">Tus datos personales</h2>
                            
                            <form id="formulario-pago" class="space-y-4" action="/rifas-premium/admin/includes/procesar_compra.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="evento_id" value="<?= $evento_id ?>">
                                <input type="hidden" name="boletos_seleccionados" id="boletos-seleccionados" value="">
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-gray-300 mb-2">Nombre completo</label>
                                        <input type="text" id="full-name" name="nombre" required
                                            class="w-full bg-gray-800 text-white px-4 py-2 rounded-lg border border-gray-700 focus:border-primary focus:outline-none">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-gray-300 mb-2">Cédula</label>
                                        <input type="text" id="id-number" name="cedula" required
                                            class="w-full bg-gray-800 text-white px-4 py-2 rounded-lg border border-gray-700 focus:border-primary focus:outline-none">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-gray-300 mb-2">Teléfono</label>
                                        <input type="tel" id="phone" name="telefono" required
                                            class="w-full bg-gray-800 text-white px-4 py-2 rounded-lg border border-gray-700 focus:border-primary focus:outline-none">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-gray-300 mb-2">Estado</label>
                                        <input type="text" id="state" name="estado" required
                                            class="w-full bg-gray-800 text-white px-4 py-2 rounded-lg border border-gray-700 focus:border-primary focus:outline-none">
                                    </div>
                                </div>
                                
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-gray-300 mb-2">Método de pago</label>
                                        <select id="payment-method" name="metodo_pago" required
                                            class="w-full bg-gray-800 text-white px-4 py-2 rounded-lg border border-gray-700 focus:border-primary focus:outline-none">
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
                                        <div id="detalles-metodo-pago-seleccionado" class="mt-2 text-sm text-gray-300"></div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-gray-300 mb-2">Comprobante de pago</label>
                                        <input type="file" id="payment-proof" name="comprobante_pago" accept="image/*" required
                                            class="w-full bg-gray-800 text-white px-4 py-2 rounded-lg border border-gray-700 focus:border-primary focus:outline-none">
                                        <div class="text-xs text-gray-400 mt-1">Sube una imagen del comprobante (JPG, PNG)</div>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-gray-300 mb-2">Referencia del pago (Proporcionada por el Banco)</label>
                                        <input type="text" id="transaction-reference" name="referencia_transaccion" required
                                            class="w-full bg-gray-800 text-white px-4 py-2 rounded-lg border border-gray-700 focus:border-primary focus:outline-none">
                                    </div>
                                </div>
                                
                                <div class="flex justify-between items-center pt-4">
                                    <button type="button" class="text-gray-400 hover:text-white transition-all" onclick="showStep(1)">
                                        <i class="fas fa-arrow-left mr-2"></i> Volver
                                    </button>
                                    <button type="button" class="bg-primary text-white px-6 py-2 rounded-lg font-bold hover:bg-primary-dark transition-all" onclick="showStep(3)">
                                        Continuar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Paso 3: Resumen y pago -->
                    <div id="step-3" class="bg-secondary rounded-xl shadow-lg overflow-hidden border border-gray-800 hidden">
                        <div class="p-6">
                            <h2 class="text-2xl font-bold text-white mb-6">Resumen de tu compra</h2>
                            
                            <div class="bg-gray-800/50 rounded-lg p-6 mb-6">
                                <h3 class="text-lg font-bold text-white mb-4">Boletos seleccionados</h3>
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
                                        <span id="cart-total">$0</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <button type="button" class="text-gray-400 hover:text-white transition-all" onclick="showStep(2)">
                                    <i class="fas fa-arrow-left mr-2"></i> Volver
                                </button>
                                <button type="submit" form="formulario-pago" class="bg-success text-white px-8 py-3 rounded-lg font-bold hover:bg-green-700 transition-all">
                                    <i class="fas fa-credit-card mr-2"></i> Proceder al Pago
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

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
    let currentPage = 1;
    const allTickets = <?= json_encode($boletos_disponibles) ?>;
    const totalPages = Math.ceil(allTickets.length / ticketsPerPage);
    const soldTickets = <?= json_encode(array_map(function($boleto) { 
        return $boleto['estado'] !== 'disponible' ? $boleto['numero_boleto'] : null; 
    }, $boletos_disponibles)) ?>;

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

        // Actualizar campo oculto de boletos al mostrar el paso 2
        if (stepNumber === 2) {
            document.getElementById('boletos-seleccionados').value = JSON.stringify(selectedTickets);
        }
    };

    // Función para cargar boletos por página
    function loadTickets(page) {
        const startIndex = (page - 1) * ticketsPerPage;
        const endIndex = startIndex + ticketsPerPage;
        const ticketsToShow = allTickets.slice(startIndex, endIndex);
        
        const ticketGrid = document.getElementById('ticket-grid');
        ticketGrid.innerHTML = '';
        
        ticketsToShow.forEach(boleto => {
            const ticketElement = document.createElement('div');
            ticketElement.className = `text-center py-1 text-xs rounded transition-all h-6 w-6 flex items-center justify-center ${
                boleto.estado !== 'disponible' ? 'ticket-sold' : 'ticket-available'
            }`;
            ticketElement.textContent = boleto.numero_boleto;
            ticketElement.setAttribute('data-numero', boleto.numero_boleto);
            
            if (boleto.estado === 'disponible') {
                ticketElement.addEventListener('click', function() {
                    toggleTicketSelection(this, boleto.numero_boleto);
                });
            }
            
            // Resaltar si está seleccionado
            if (selectedTickets.includes(boleto.numero_boleto)) {
                ticketElement.classList.remove('ticket-available');
                ticketElement.classList.add('ticket-selected');
            }
            
            ticketGrid.appendChild(ticketElement);
        });
        
        // Actualizar controles de paginación
        updatePaginationControls(page);
    }

    // Función para alternar selección de boleto
    function toggleTicketSelection(element, ticketNumber) {
        if (element.classList.contains('ticket-selected')) {
            // Deseleccionar
            element.classList.remove('ticket-selected');
            element.classList.add('ticket-available');
            selectedTickets = selectedTickets.filter(num => num !== ticketNumber);
        } else {
            // Seleccionar
            if (selectedTickets.length >= 20) {
                alert('Máximo 20 boletos por compra');
                return;
            }
            element.classList.remove('ticket-available');
            element.classList.add('ticket-selected');
            selectedTickets.push(ticketNumber);
        }
        
        updateSelectedTickets();
    }

    // Función para actualizar controles de paginación
    function updatePaginationControls(page) {
        document.getElementById('current-page').textContent = page;
        document.getElementById('total-pages').textContent = totalPages;
        document.getElementById('prev-page').disabled = page === 1;
        document.getElementById('next-page').disabled = page === totalPages;
    }

    // Función para actualizar la lista de boletos seleccionados
    function updateSelectedTickets() {
        const selectedCount = document.getElementById('selected-count');
        const selectedTotal = document.getElementById('selected-total');
        const selectedList = document.getElementById('selected-tickets-list');
        const continueBtn = document.getElementById('continue-btn');
        const cartCount = document.getElementById('cart-count');
        const cartTotal = document.getElementById('cart-total');
        const selectedDisplay = document.getElementById('selected-tickets-display');
        
        // Actualizar contadores
        selectedCount.textContent = selectedTickets.length;
        selectedTotal.textContent = (selectedTickets.length * ticketPrice).toFixed(2);
        
        // Actualizar lista de boletos
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
            
            // Actualizar resumen en paso 3
            if (cartCount) cartCount.textContent = selectedTickets.length;
            if (cartTotal) cartTotal.textContent = `$${(selectedTickets.length * ticketPrice).toFixed(2)}`;
            if (selectedDisplay) {
                selectedDisplay.innerHTML = selectedTickets.map(ticket => `
                    <div class="bg-gray-700 text-white px-3 py-1 rounded-full text-sm">
                        #${ticket}
                    </div>
                `).join('');
            }
        } else {
            selectedList.innerHTML = '<p class="text-gray-400 text-sm">No hay boletos seleccionados</p>';
            continueBtn.disabled = true;
        }
    }

    // Función para remover un boleto de la selección
    window.removeTicket = function(ticketNumber) {
        selectedTickets = selectedTickets.filter(num => num !== ticketNumber);
        
        // Actualizar estado en el grid si está visible
        document.querySelectorAll(`.ticket-selected[data-numero="${ticketNumber}"]`).forEach(el => {
            el.classList.remove('ticket-selected');
            el.classList.add('ticket-available');
        });
        
        updateSelectedTickets();
    };

    // Eventos de paginación
    document.getElementById('prev-page').addEventListener('click', function() {
        if (currentPage > 1) {
            currentPage--;
            loadTickets(currentPage);
        }
    });

    document.getElementById('next-page').addEventListener('click', function() {
        if (currentPage < totalPages) {
            currentPage++;
            loadTickets(currentPage);
        }
    });

    // Selección aleatoria
    document.getElementById('random-btn').addEventListener('click', function() {
        const quantity = parseInt(document.getElementById('random-quantity').value);
        
        if (isNaN(quantity) || quantity < 1 || quantity > 20) {
            alert('Por favor ingresa una cantidad válida entre 1 y 20');
            return;
        }
        
        // Limpiar selección actual
        selectedTickets = [];
        document.querySelectorAll('.ticket-selected').forEach(el => {
            el.classList.remove('ticket-selected');
            el.classList.add('ticket-available');
        });
        
        // Generar boletos aleatorios disponibles
        const availableTickets = allTickets
            .filter(boleto => boleto.estado === 'disponible' && !selectedTickets.includes(boleto.numero_boleto))
            .map(boleto => boleto.numero_boleto);
        
        if (availableTickets.length < quantity) {
            alert(`Solo quedan ${availableTickets.length} boletos disponibles`);
            return;
        }
        
        // Seleccionar aleatoriamente
        for (let i = 0; i < quantity; i++) {
            const randomIndex = Math.floor(Math.random() * availableTickets.length);
            const randomTicket = availableTickets[randomIndex];
            selectedTickets.push(randomTicket);
            availableTickets.splice(randomIndex, 1);
            
            // Actualizar visualmente si está en la página actual
            const ticketElement = document.querySelector(`.ticket-available[data-numero="${randomTicket}"]`);
            if (ticketElement) {
                ticketElement.classList.remove('ticket-available');
                ticketElement.classList.add('ticket-selected');
            }
        }
        
        updateSelectedTickets();
    });

    // Controles de cantidad
    document.getElementById('decrease-random').addEventListener('click', function() {
        const input = document.getElementById('random-quantity');
        if (parseInt(input.value) > 1) {
            input.value = parseInt(input.value) - 1;
        }
    });

    document.getElementById('increase-random').addEventListener('click', function() {
        const input = document.getElementById('random-quantity');
        if (parseInt(input.value) < 20) {
            input.value = parseInt(input.value) + 1;
        }
    });

    // Mostrar detalles del método de pago
    document.getElementById('payment-method').addEventListener('change', function() {
        const detallesDiv = document.getElementById('detalles-metodo-pago-seleccionado');
        const selectedOption = this.options[this.selectedIndex];
        const detallesJson = selectedOption.getAttribute('data-detalles');
        
        if (!detallesJson) {
            detallesDiv.innerHTML = '';
            return;
        }
        
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
    // Iniciar el contador con la fecha de fin del evento
    iniciarContador("<?= date('Y-m-d H:i:s', strtotime($evento['fecha_fin'])) ?>");
    // Inicializar
    loadTickets(1);
    updateSelectedTickets();
});
    </script>
</body>
</html>