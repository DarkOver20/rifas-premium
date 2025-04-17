<?php
require_once dirname(__DIR__) . '/admin/includes/config.php';
require_once dirname(__DIR__) . '/admin/includes/functions.php';
require_login();
require_admin();

$eventos_finalizados = obtenerEventos('finalizado');
$solicitudes_pendientes = obtenerSolicitudesPendientes();
$total_usuarios = contarUsuarios();
$total_ventas = calcularVentasTotales();
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Rifas A&M</title>
    
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
                <a href="" class="flex items-center gap-2 group" aria-label="Rifas A&M">
                    <img src="./uploads/logocolor.webp" 
                         alt="Logo Rifas A&M" 
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
                <a href="/rifas-premium/logout.php" class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-800 hover:bg-gray-700 transition-all">
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
                <a href="./" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg">
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
            <div class="mb-8 fade-in">
                <h1 class="text-3xl font-bold mb-2">
                    <span class="bg-gradient-to-r from-primary to-three bg-clip-text text-transparent">Panel de Control</span>
                </h1>
                <p class="text-gray-400">Bienvenido al centro de administración de Rifas A&M</p>
            </div>
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 mb-8">
                <div class="bg-secondary rounded-xl p-6 shadow-lg stat-card fade-in" style="transition-delay: 0.4s">
                    <div class="flex items-center gap-4">
                        <div class="stat-icon bg-danger/10 text-danger w-14 h-14 rounded-full flex items-center justify-center">
                            <i class="fas fa-dollar-sign text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-gray-400 text-sm font-medium">Ventas Totales</h3>
                            <p class="text-2xl font-bold">$<?= number_format($total_ventas, 2) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Solicitudes Recientes -->
            <section class="mb-8 fade-in" style="transition-delay: 0.2s">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                    <h2 class="text-xl font-bold">
                        <span class="bg-gradient-to-r from-primary to-three bg-clip-text text-transparent">Solicitudes Recientes</span>
                    </h2>
                </div>
                
                <div class="bg-secondary rounded-xl shadow-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full data-table">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Comprador</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Evento</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Boletos</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Monto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800">
                                <?php if (empty($solicitudes_pendientes)): ?>
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-400">No hay solicitudes pendientes</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($solicitudes_pendientes as $solicitud): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-200"><?= $solicitud['id'] ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-white"><?= htmlspecialchars($solicitud['comprador_nombre'] ?? 'N/A') ?></div>
                                            <div class="text-xs text-gray-400">C.I. <?= htmlspecialchars($solicitud['comprador_cedula'] ?? 'N/A') ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= htmlspecialchars($solicitud['evento_titulo'] ?? 'N/A') ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= $solicitud['cantidad_boletos'] ?? 0 ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">$<?= isset($solicitud['monto']) ? number_format($solicitud['monto'], 2) : '0.00' ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= isset($solicitud['fecha_transaccion']) ? date('d/m/Y H:i', strtotime($solicitud['fecha_transaccion'])) : 'N/A' ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="/rifas-premium/admin/solicitud/<?= $solicitud['id'] ?>/<?= preg_replace('/[^0-9]/', '', $solicitud['comprador_cedula']) ?>" class="btn-glow bg-primary/10 hover:bg-primary/20 text-primary px-3 py-1 rounded-md text-xs transition-all inline-flex items-center gap-1">
                                                <i class="fas fa-eye text-xs"></i> Revisar
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
            
            
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
    </script>

</body>

</html>