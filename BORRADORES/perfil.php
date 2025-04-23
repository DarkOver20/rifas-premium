<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_login();
require_admin();

$usuario = obtenerUsuario($_SESSION['usuario_id']);
?>

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Rifas A&M</title>
    
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
        
        .sidebar-overlay {
            display: none;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 20;
        }
        
        .sidebar-open .sidebar-overlay {
            display: block;
        }
        
        @media (min-width: 1024px) {
            .sidebar {
                transform: translateX(0);
            }
            
            .sidebar-overlay {
                display: none !important;
            }
            
            main {
                padding-left: 280px !important;
            }
        }
        
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
        
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        
        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Estilos específicos para el perfil */
        .profile-card {
            background-color: var(--secondary-color);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }
        
        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .avatar-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto;
        }
        
        .avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid var(--primary-color);
        }
        
        .avatar-upload {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background-color: var(--primary-color);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .form-input {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-color);
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.2);
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
                <a href="./dashboard.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg">
                    <i class="fas fa-tachometer-alt w-5 text-center text-gray-400"></i>
                    <span>Dashboard</span>
                </a>
                
                <a href="/rifas-premium/admin/eventos/" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg">
                    <i class="fas fa-trophy w-5 text-center text-gray-400"></i>
                    <span>Eventos</span>
                </a>
                
                <a href="/rifas-premium/admin/usuario/perfil.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg">
                    <i class="fas fa-user w-5 text-center text-primary"></i>
                    <span>Perfil</span>
                </a>
                
                <a href="/rifas-premium/admin/metodos_pago/" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg">
                    <i class="fas fa-dollar-sign w-5 text-center text-gray-400"></i>
                    <span>Métodos de Pago</span>
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
                    <span class="bg-gradient-to-r from-primary to-three bg-clip-text text-transparent">Mi Perfil</span>
                </h1>
                <p class="text-gray-400">Administra tu información personal y configuración de cuenta</p>
            </div>
            
            <!-- Contenido del Perfil -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 fade-in" style="transition-delay: 0.2s">
                <!-- Tarjeta de Información del Usuario -->
                <div class="lg:col-span-1 profile-card p-6">
                    <div class="flex flex-col items-center text-center">
                        <div class="avatar-container mb-4">
                            <img src="../../uploads/avatars/<?= $usuario['avatar'] ?? 'default.jpg' ?>" 
                                 alt="Avatar" 
                                 class="avatar-img"
                                 id="avatar-preview">
                        </div>
                        <br>
                        <h3 class="text-xl font-bold mb-1"><?= htmlspecialchars($usuario['nombre']) ?></h3>
                        <p class="text-gray-400 mb-4"><?= htmlspecialchars($usuario['email']) ?></p>
                        

                    </div>
                </div>
                
                <!-- Formulario de Edición -->
                <div class="lg:col-span-2 profile-card p-6">
                    <form method="POST" action="actualizar-perfil.php" enctype="multipart/form-data">
                        <input type="file" id="avatar" name="avatar" accept="image/*" class="hidden" onchange="previewAvatar(event)">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="nombre" class="block text-sm font-medium text-gray-400 mb-1">Nombre Completo</label>
                                <input type="text" id="nombre" name="nombre" 
                                       value="<?= htmlspecialchars($usuario['nombre']) ?>" 
                                       class="w-full form-input px-4 py-2 rounded-lg" required>
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-400 mb-1">Correo Electrónico</label>
                                <input type="email" id="email" name="email" 
                                       value="<?= htmlspecialchars($usuario['email']) ?>" 
                                       class="w-full form-input px-4 py-2 rounded-lg" required>
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <h3 class="text-lg font-medium mb-4 border-b border-gray-800 pb-2">Cambiar Contraseña</h3>
                            <p class="text-sm text-gray-400 mb-4">Deja estos campos en blanco si no deseas cambiar tu contraseña.</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-400 mb-1">Nueva Contraseña</label>
                                    <input type="password" id="password" name="password" 
                                           class="w-full form-input px-4 py-2 rounded-lg">
                                </div>
                                
                                <div>
                                    <label for="confirm_password" class="block text-sm font-medium text-gray-400 mb-1">Confirmar Contraseña</label>
                                    <input type="password" id="confirm_password" name="confirm_password" 
                                           class="w-full form-input px-4 py-2 rounded-lg">
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="btn-glow bg-primary hover:bg-primary-dark text-white px-6 py-2 rounded-lg transition-all flex items-center gap-2">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
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
        const logoutButton = document.querySelector('a[href="/rifas-premium/logout.php"]');
        if (logoutButton) {
            logoutButton.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('¿Estás seguro que deseas cerrar sesión?')) {
                    window.location.href = this.href;
                }
            });
        }
        
        // Vista previa del avatar
        function previewAvatar(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('avatar-preview');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
        
        // Activar el input file al hacer clic en el ícono de cámara
        document.querySelector('.avatar-upload').addEventListener('click', function() {
            document.getElementById('avatar').click();
        });
    </script>
</body>
</html>