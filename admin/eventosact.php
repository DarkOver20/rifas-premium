<?php
require_once dirname(__DIR__) . '/admin/includes/config.php';
require_once dirname(__DIR__) . '/admin/includes/functions.php';
require_login();
require_admin();

$eventos_activos = obtenerEventos('activo');
$eventos_finalizados = obtenerEventos('finalizado');
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Bólidos Rifas</title>
    
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
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'float': 'float 6s ease-in-out infinite',
                        'bounce-slow': 'bounce 2s infinite'
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' }
                        }
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
            --highlight-color: hsl(0, 0%, 100%);
        }
        
        html {
            scroll-behavior: smooth;
            scroll-padding-top: 80px;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            overflow-x: hidden;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
        }
        
        /* Sidebar */
        
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
        /* Cards */
        .stat-card {
            transition: all 0.3s ease;
            transform-style: preserve-3d;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            transition: all 0.3s ease;
        }
        
        .stat-card:hover .stat-icon {
            transform: scale(1.1);
        }
        
        /* Tablas */
        .data-table {
            min-width: 100%;
        }
        
        .data-table th {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .data-table tr {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .data-table tr:hover {
            background-color: rgba(255, 255, 255, 0.03);
        }
        
        /* Eventos */
        .evento-card {
            transition: all 0.3s ease;
        }
        
        .evento-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        /* Botones */
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
        
        /* Animaciones */
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        
        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Barra de progreso */
        .progress-bar {
            height: 8px;
            border-radius: 4px;
            background-color: rgba(255, 255, 255, 0.1);
            overflow: hidden;
        }
        
        .progress-bar-fill {
            height: 100%;
            border-radius: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            transition: width 1s ease-out;
            position: relative;
        }

        /* Estilos para los nuevos botones */
.btn-warning {
    background-color: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
}

.btn-warning:hover {
    background-color: rgba(245, 158, 11, 0.2);
}

.btn-success {
    background-color: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.btn-success:hover {
    background-color: rgba(16, 185, 129, 0.2);
}

/* Espaciado entre botones */
.flex-gap-2 {
    gap: 0.5rem;
}

/* Estilo para el contenido del ganador */
#ganadorContent p {
    margin-bottom: 0.5rem;
    padding: 0.25rem 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

#ganadorContent p:last-child {
    border-bottom: none;
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
                    <img src="https://storage.googleapis.com/a1aa/image/AeamUydK5EmKTfsd6-73yLVqiwQJTSps5dL04l_p_jc.jpg" 
                         alt="Logo Bólidos Rifas" 
                         class="h-10 w-10 rounded-lg"
                         loading="eager">
                    <span class="text-xl font-bold bg-gradient-to-r from-primary to-three bg-clip-text text-transparent">
                    A&M Recreciones
                    </span>
                </a>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="hidden md:flex items-center gap-2">
                    <span class="text-sm text-gray-300">Bienvenido,</span>
                    <span class="font-medium"><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></span>
                </div>
                <a href="../logout.php" class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-800 hover:bg-gray-700 transition-all">
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
                <a href="/rifas-premium/dashboard/" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg">
                    <i class="fas fa-tachometer-alt w-5 text-center text-primary"></i>
                    <span>Dashboard</span>
                </a>
                
                <a href="./" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg">
                    <i class="fas fa-trophy w-5 text-center text-gray-400"></i>
                    <span>Eventos</span>
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
            <div class="mb-8 fade-in">
                <h1 class="text-3xl font-bold mb-2">
                    <span class="bg-gradient-to-r from-primary to-three bg-clip-text text-transparent">Panel de Control</span>
                </h1>
                <p class="text-gray-400">Bienvenido al centro de administración de A&M Recreaciones</p>
            </div>
            
            <!-- Solicitudes Recientes -->

            <!-- Eventos Activos -->
            <section class="mb-8 fade-in" style="transition-delay: 0.3s">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                    <h2 class="text-xl font-bold">
                        <span class="bg-gradient-to-r from-primary to-three bg-clip-text text-transparent">Eventos Activos</span>
                    </h2>
                    <a href="/rifas-premium/eventos/crear" class="btn-glow bg-gradient-to-r from-primary to-primary-dark hover:from-primary-dark hover:to-primary text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md hover:shadow-lg transition-all duration-300 inline-flex items-center gap-2">
                        <i class="fas fa-plus"></i> Nuevo Evento
                    </a>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($eventos_activos as $evento): ?>
                    <div class="bg-secondary rounded-xl overflow-hidden shadow-lg evento-card">
                        <div class="relative">
                            <div class="h-48 bg-gray-800 flex items-center justify-center">
                            <img src="/rifas-premium/admin/uploads/<?= htmlspecialchars($evento['imagen']) ?>" 
                                    alt="<?= htmlspecialchars($evento['titulo']) ?>" 
                                    class="w-full h-full object-cover object-center" 
                                    loading="lazy"> 
                            </div>
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-4">
                                <h3 class="text-lg font-bold text-white"><?= htmlspecialchars($evento['titulo']) ?></h3>
                                <div class="flex justify-between text-xs text-gray-300">
                                    <span>Precio: $<?= number_format($evento['precio_boleto'], 2) ?></span>
                                    <span><?= $evento['boletos_disponibles'] ?> disponibles</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-4">
                            <div class="flex justify-between items-center mb-3">
                                <div>
                                    <div class="text-xs text-gray-400">Fecha fin</div>
                                    <div class="text-sm font-medium"><?= date('d/m/Y', strtotime($evento['fecha_fin'])) ?></div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-gray-400">Estado</div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary/10 text-primary">
                                        Activo
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
      
     <!-- Eventos Finalizados -->
     <section class="mb-8 fade-in" style="transition-delay: 0.3s">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                    <h2 class="text-xl font-bold">
                        <span class="bg-gradient-to-r from-primary to-three bg-clip-text text-transparent">Eventos Finalizados</span>
                    </h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($eventos_finalizados as $evento): ?>
                    <div class="bg-secondary rounded-xl overflow-hidden shadow-lg evento-card">
                        <div class="relative">
                            <div class="h-48 bg-gray-800 flex items-center justify-center">
                            <img src="/rifas-premium/admin/uploads/<?= htmlspecialchars($evento['imagen']) ?>" 
                                    alt="<?= htmlspecialchars($evento['titulo']) ?>" 
                                    class="w-full h-full object-cover object-center" 
                                    loading="lazy"> 
                            </div>
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-4">
                                <h3 class="text-lg font-bold text-white"><?= htmlspecialchars($evento['titulo']) ?></h3>
                                <div class="flex justify-between text-xs text-gray-300">
                                    <span>Precio: $<?= number_format($evento['precio_boleto'], 2) ?></span>
                                    <span><?= $evento['boletos_disponibles'] ?> disponibles</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-4">
                            <div class="flex justify-between items-center mb-3">
                                <div>
                                    <div class="text-xs text-gray-400">Fecha fin</div>
                                    <div class="text-sm font-medium"><?= date('d/m/Y', strtotime($evento['fecha_fin'])) ?></div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-gray-400">Estado</div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary/10 text-primary">
                                        Activo
                                    </span>
                                </div>
                            </div>
                            
                            <div class="flex justify-between gap-2">
    <?php if (!empty($evento['boleto_ganador'])): ?>
        <button onclick="mostrarModalGanador(<?= $evento['id'] ?>, true)" 
                class="flex-1 btn-glow bg-warning/10 hover:bg-warning/20 text-warning px-3 py-2 rounded-md text-sm text-center transition-all">
            <i class="fas fa-sync-alt mr-1"></i> Cambiar Ganador
        </button>
        <button onclick="mostrarInfoGanador(<?= $evento['id'] ?>)" 
                class="flex-1 btn-glow bg-success/10 hover:bg-success/20 text-success px-3 py-2 rounded-md text-sm text-center transition-all">
            <i class="fas fa-eye mr-1"></i> Ver Ganador
        </button>
    <?php else: ?>
        <button onclick="mostrarModalGanador(<?= $evento['id'] ?>)" 
                class="flex-1 btn-glow bg-primary/10 hover:bg-primary/20 text-primary px-3 py-2 rounded-md text-sm text-center transition-all">
            <i class="fas fa-trophy mr-1"></i> Asignar Ganador
        </button>
    <?php endif; ?>
</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </main>
<!-- Modal para asignar boleto ganador -->
<div id="modalGanador" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black bg-opacity-50">
    <div class="bg-secondary rounded-xl shadow-xl w-full max-w-md">
        <div class="p-6">
            <h3 class="text-xl font-bold text-white mb-4">Asignar Boleto Ganador</h3>
            
            <!-- Sección de éxito (oculta inicialmente) -->
            <div id="successMessage" class="hidden mb-4 p-4 bg-success/20 border border-success/30 rounded-lg">
                <div class="flex items-center gap-3">
                    <i class="fas fa-check-circle text-success"></i>
                    <span id="successText" class="text-success"></span>
                </div>
                
                <!-- Información del comprador -->
                <div id="compradorInfo" class="mt-3 space-y-2 hidden">
                    <h4 class="font-bold text-white">Información del Ganador:</h4>
                    <p id="compradorNombre" class="text-gray-300"></p>
                    <p id="compradorCedula" class="text-gray-300"></p>
                    <p id="compradorTelefono" class="text-gray-300"></p>
                    <p id="compradorEmail" class="text-gray-300"></p>
                </div>
            </div>
            
            <form id="formGanador" method="POST" action="/rifas-premium/admin/includes/functions.php">
                <input type="hidden" name="action" value="asignar_ganador">
                <input type="hidden" id="evento_id" name="evento_id" value="">
                
                <div class="mb-4">
                    <label for="numero_boleto" class="block text-gray-300 mb-2">
                        Número de Boleto Ganador
                    </label>
                    <input type="text" id="numero_boleto" name="numero_boleto" 
                           class="w-full px-4 py-3 bg-background border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-white placeholder-gray-500"
                           placeholder="Ingrese el número del boleto ganador">
                    <p id="errorBoleto" class="text-danger text-sm mt-1 hidden"></p>
                </div>
                
                <div class="bg-gray-800/50 p-4 rounded-lg mb-4 hidden" id="infoBoleto">
                    <h4 class="font-bold text-white mb-2">Información del Boleto</h4>
                    <p id="boletoEstado" class="text-gray-300"></p>
                    <p id="boletoComprador" class="text-gray-300"></p>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="cerrarModal()" 
                            class="px-4 py-2 border border-gray-600 text-gray-300 hover:text-white rounded-lg">
                        Cancelar
                    </button>
                    <button type="submit" id="submitButton"
                            class="btn-glow bg-gradient-to-r from-primary to-primary-dark text-white px-4 py-2 rounded-lg">
                        Confirmar Ganador
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para ver información del ganador -->
<div id="modalInfoGanador" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black bg-opacity-50">
    <div class="bg-secondary rounded-xl shadow-xl w-full max-w-md">
        <div class="p-6">
            <h3 class="text-xl font-bold text-white mb-4">Información del Ganador</h3>
            
            <div id="ganadorContent" class="space-y-4">
                <!-- La información se cargará dinámicamente aquí -->
            </div>
            
            <div class="flex justify-end mt-6">
                <button onclick="cerrarModalInfo()" 
                        class="px-4 py-2 border border-gray-600 text-gray-300 hover:text-white rounded-lg">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
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
        
        // Animación de aparición de elementos
        const fadeElements = document.querySelectorAll('.fade-in');
        
        const fadeInObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });
        
        fadeElements.forEach(element => {
            fadeInObserver.observe(element);
        });
        
        // Cerrar sesión con confirmación
        const logoutButton = document.querySelector('a[href="../logout.php"]');
        if (logoutButton) {
            logoutButton.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('¿Estás seguro que deseas cerrar sesión?')) {
                    window.location.href = this.href;
                }
            });
        }



        // Mostrar modal para asignar boleto ganador
function mostrarModalGanador(eventoId) {
    document.getElementById('evento_id').value = eventoId;
    document.getElementById('modalGanador').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    
    // Limpiar campos al abrir
    document.getElementById('numero_boleto').value = '';
    document.getElementById('infoBoleto').classList.add('hidden');
    document.getElementById('errorBoleto').classList.add('hidden');
}

// Cerrar modal y limpiar todo
function cerrarModal() {
    document.getElementById('modalGanador').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    
    // Limpiar campos y mensajes
    document.getElementById('numero_boleto').value = '';
    document.getElementById('errorBoleto').classList.add('hidden');
    document.getElementById('infoBoleto').classList.add('hidden');
    document.getElementById('successMessage').classList.add('hidden');
    document.getElementById('compradorInfo').classList.add('hidden');
    
    // Habilitar campos por si estaban deshabilitados
    document.getElementById('numero_boleto').disabled = false;
    document.getElementById('submitButton').disabled = false;
}

// Verificar boleto mientras se escribe
document.getElementById('numero_boleto').addEventListener('input', function() {
    const numeroBoleto = this.value.trim();
    const eventoId = document.getElementById('evento_id').value;
    const errorElement = document.getElementById('errorBoleto');
    const infoElement = document.getElementById('infoBoleto');
    
    if (numeroBoleto.length > 0) {
        // Hacer petición AJAX para verificar el boleto
        fetch(`/rifas-premium/admin/includes/functions.php?action=verificar_boleto&evento_id=${eventoId}&numero_boleto=${numeroBoleto}`)
            .then(response => response.json())
            .then(data => {
                if (data.existe) {
                    errorElement.classList.add('hidden');
                    infoElement.classList.remove('hidden');
                    
                    document.getElementById('boletoEstado').textContent = `Estado: ${data.estado}`;
                    
                    if (data.comprador) {
                        document.getElementById('boletoComprador').textContent = `Comprador: ${data.comprador}`;
                    } else {
                        document.getElementById('boletoComprador').textContent = 'Boleto no asignado';
                    }
                } else {
                    infoElement.classList.add('hidden');
                    errorElement.textContent = 'Este boleto no existe para este evento';
                    errorElement.classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    } else {
        infoElement.classList.add('hidden');
        errorElement.classList.add('hidden');
    }
});
// Manejar envío del formulario
document.getElementById('formGanador').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const errorElement = document.getElementById('errorBoleto');
    const successElement = document.getElementById('successMessage');
    const successText = document.getElementById('successText');
    const compradorInfo = document.getElementById('compradorInfo');
    
    // Resetear estados
    errorElement.classList.add('hidden');
    successElement.classList.add('hidden');
    compradorInfo.classList.add('hidden');
    
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                throw new Error('La respuesta no es JSON: ' + text);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Mostrar mensaje de éxito
            successText.textContent = data.message || 'Boleto ganador asignado correctamente';
            successElement.classList.remove('hidden');
            
            // Mostrar información del comprador si está disponible
            if (data.comprador) {
                document.getElementById('compradorNombre').textContent = 'Nombre: ' + (data.comprador.nombre || 'Nadie compro este boleto');
                document.getElementById('compradorCedula').textContent = 'Cédula: ' + (data.comprador.cedula || 'Nadie compro este boleto');
                document.getElementById('compradorTelefono').textContent = 'Teléfono: ' + (data.comprador.telefono || 'Nadie compro este boleto');
                compradorInfo.classList.remove('hidden');
            }
            
            // Deshabilitar el formulario temporalmente
            document.getElementById('numero_boleto').disabled = true;
            document.getElementById('submitButton').disabled = true;
            

        } else {
            errorElement.textContent = data.message || 'Error al asignar el boleto ganador';
            errorElement.classList.remove('hidden');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        errorElement.textContent = 'Error en el servidor: ' + error.message;
        errorElement.classList.remove('hidden');
    });
});


// Mostrar información del ganador existente
function mostrarInfoGanador(eventoId) {
    fetch(`/rifas-premium/admin/includes/functions.php?action=obtener_ganador&evento_id=${eventoId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            const modal = document.getElementById('modalInfoGanador');
            const content = document.getElementById('ganadorContent');
            
            if (data.success) {
                content.innerHTML = `
                    <div class="bg-gray-800/50 p-4 rounded-lg">
                        <h4 class="font-bold text-white mb-2">Boleto Ganador: ${data.boleto_ganador}</h4>
                        <p class="text-gray-300"><strong>Nombre:</strong> ${data.comprador.nombre}</p>
                        <p class="text-gray-300"><strong>Cédula:</strong> ${data.comprador.cedula}</p>
                        <p class="text-gray-300"><strong>Teléfono:</strong> ${data.comprador.telefono}</p>
                    </div>
                `;
            } else {
                content.innerHTML = `
                    <div class="bg-danger/20 border border-danger/30 text-danger p-4 rounded-lg">
                        <i class="fas fa-exclamation-circle mr-2"></i> ${data.message}
                    </div>
                `;
            }
            
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            const content = document.getElementById('ganadorContent');
            content.innerHTML = `
                <div class="bg-danger/20 border border-danger/30 text-danger p-4 rounded-lg">
                    <i class="fas fa-exclamation-circle mr-2"></i> Error al cargar la información del ganador
                </div>
            `;
            document.getElementById('modalInfoGanador').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        });
}

// Cerrar modal de información
function cerrarModalInfo() {
    document.getElementById('modalInfoGanador').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

// Modificar la función mostrarModalGanador para aceptar modo "cambiar"
function mostrarModalGanador(eventoId, cambiar = false) {
    document.getElementById('evento_id').value = eventoId;
    document.getElementById('modalGanador').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    
    // Limpiar campos al abrir
    document.getElementById('numero_boleto').value = '';
    document.getElementById('errorBoleto').classList.add('hidden');
    document.getElementById('infoBoleto').classList.add('hidden');
    document.getElementById('successMessage').classList.add('hidden');
    document.getElementById('compradorInfo').classList.add('hidden');
    
    // Si estamos en modo "cambiar", mostrar el ganador actual
    if (cambiar) {
        fetch(`/rifas-premium/admin/includes/functions.php?action=obtener_ganador&evento_id=${eventoId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const infoBoleto = document.getElementById('infoBoleto');
                    infoBoleto.classList.remove('hidden');
                    document.getElementById('boletoEstado').textContent = `Boleto actual: ${data.boleto_ganador}`;
                    document.getElementById('boletoComprador').textContent = `Ganador actual: ${data.comprador.nombre || 'N/A'}`;
                }
            });
    }
}
    </script>

</body>

</html>