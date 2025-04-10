<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Obtener eventos directamente desde PHP
$eventos_activos = obtenerEventos('activo');
$eventos_finalizados = obtenerEventos('finalizado');
$metodos_de_pago = obtener_metodos_pago();

?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="description" content="Participa en emocionantes rifas de autos de lujo con RIFAS PREMIUM. Oportunidad única de ganar coches deportivos, SUVs premium y clásicos vintage. ¡Compra tus boletos ahora!"/>
  <meta name="keywords" content="rifas de autos, sorteos de coches, ganar automóviles, premios de lujo, comprar boletos"/>
  <meta name="robots" content="index, follow">
  
  <!-- Preconexión y precarga estratégica -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preload" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Montserrat:wght@700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Montserrat:wght@700&display=swap"></noscript>
  
  <!-- Favicon -->
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
  <link rel="manifest" href="/site.webmanifest">
  
  <!-- Open Graph / Social Media -->
  <meta property="og:title" content="Rifas Premium | Sorteos Exclusivos">
  <meta property="og:description" content="Participa en nuestras exclusivas rifas y gana premios increíbles. ¡Tu sueño está a un boleto de distancia!">
  <meta property="og:image" content="https://www.tudominio.com/social-preview.jpg">
  <meta property="og:url" content="https://www.tudominio.com">
  <meta property="og:type" content="website">
  
  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Rifas Premium | Sorteos Exclusivos">
  <meta name="twitter:description" content="Rifas exclusivas con premios increíbles. ¡Participa ahora!">
  <meta name="twitter:image" content="https://www.tudominio.com/social-preview.jpg">
  
  <!-- Schema.org markup -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "Rifas Premium",
    "url": "https://www.tudominio.com",
    "logo": "https://www.tudominio.com/logo.png",
    "description": "Rifas exclusivas con transparencia garantizada",
    "sameAs": [
      "https://facebook.com/tupagina",
      "https://instagram.com/tupagina",
      "https://twitter.com/tupagina"
    ]
  }
  </script>
  
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
  
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" media="print" onload="this.media='all'"/>
  <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
  
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
    
    /* Efecto de partículas para el hero */
    .particles {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 0;
      pointer-events: none;
    }
    
    .particle {
      position: absolute;
      background-color: rgba(255, 215, 0, 0.6);
      border-radius: 50%;
      pointer-events: none;
    }
    
    /* Animación del logo */
    @keyframes logo-pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.05); }
      100% { transform: scale(1); }
    }
    
    .logo-animate:hover {
      animation: logo-pulse 1.5s infinite;
    }
    
    /* Efecto de hover para tarjetas */
    .card-hover {
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      transform-style: preserve-3d;
    }
    
    .card-hover:hover {
      transform: translateY(-10px) rotateX(5deg);
      box-shadow: 0 20px 30px rgba(0, 0, 0, 0.3);
    }
    
    .card-hover::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
      opacity: 0;
      transition: opacity 0.3s ease;
    }
    
    .card-hover:hover::before {
      opacity: 1;
    }
    
    /* Botón con efecto de brillo */
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
    
    /* Efecto de aparición suave */
    .fade-in {
      opacity: 0;
      transform: translateY(20px);
      transition: opacity 0.6s ease, transform 0.6s ease;
    }
    
    .fade-in.visible {
      opacity: 1;
      transform: translateY(0);
    }
    
    /* Barra de progreso animada */
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
    
    .progress-bar-fill::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(
        90deg,
        rgba(255, 255, 255, 0.1),
        rgba(255, 255, 255, 0.3),
        rgba(255, 255, 255, 0.1)
      );
      animation: progress-shine 2s infinite;
    }
    
    @keyframes progress-shine {
      0% { transform: translateX(-100%); }
      100% { transform: translateX(100%); }
    }
    
    /* Efecto de flotación para los ganadores */
    .winner-card {
      transition: all 0.4s ease;
    }
    
    .winner-card:hover {
      transform: translateY(-5px) scale(1.02);
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
    }
    
    .winner-card::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--accent-color), var(--primary-color));
      opacity: 0;
      transition: opacity 0.3s ease;
    }
    
    .winner-card:hover::after {
      opacity: 1;
    }
    
    /* Efecto de hover para iconos sociales */
    .social-icon {
      transition: all 0.3s ease;
    }
    
    .social-icon:hover {
      transform: translateY(-3px) scale(1.1);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    
    /* Efecto de carga para imágenes */
    .img-loading {
      position: relative;
      overflow: hidden;
      background-color: #2d2d2d;
    }
    
    .img-loading::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.1),
        transparent
      );
      animation: loading 1.5s infinite;
    }
    
    @keyframes loading {
      0% { transform: translateX(-100%); }
      100% { transform: translateX(100%); }
    }
    
    /* Animación de conteo para números */
    .count-up {
      transition: all 1s ease-out;
    }
  </style>
</head>
<body class="antialiased">
  
  <!-- Barra de navegación mejorada -->
  <header class="fixed w-full top-0 left-0 z-50 transition-all duration-300" id="navbar">
    <div class="container mx-auto px-4 py-3">
      <div class="flex justify-between items-center bg-secondary/90 backdrop-blur-md rounded-full px-6 py-3 shadow-lg border border-gray-800">
        <a href="#inicio" class="flex items-center gap-2 group" aria-label="RIFAS PREMIUM">
          <img src="./uploads/logocolor.webp" alt="Logo de RIFAS PREMIUM" class="h-12 w-12 logo-animate transition-all duration-300 group-hover:rotate-12" loading="eager">
          <span class="text-2xl font-bold bg-gradient-to-r from-primary to-three bg-clip-text text-transparent">
            RIFAS PREMIUM
          </span>
        </a>     
        <nav class="hidden lg:flex items-center gap-8">
          <a href="#inicio" class="nav-link text-white hover:text-accent transition-all relative group">
            <span class="flex items-center gap-1">
              <i class="fas fa-home text-sm opacity-70"></i>
              Inicio
            </span>
            <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-accent transition-all duration-300 group-hover:w-full"></span>
          </a>
          <a href="#eventos" class="nav-link text-white hover:text-accent transition-all relative group">
            <span class="flex items-center gap-1">
              <i class="fas fa-trophy text-sm opacity-70"></i>
              Eventos
            </span>
            <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-accent transition-all duration-300 group-hover:w-full"></span>
          </a>
          <a href="#pagos" class="nav-link text-white hover:text-accent transition-all relative group">
            <span class="flex items-center gap-1">
              <i class="fas fa-credit-card text-sm opacity-70"></i>
              Métodos de Pago
            </span>
            <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-accent transition-all duration-300 group-hover:w-full"></span>
          </a>
          <a href="#como-participar" class="nav-link text-white hover:text-accent transition-all relative group">
            <span class="flex items-center gap-1">
              <i class="fas fa-medal text-sm opacity-70"></i>
              ¿Cómo participar?
            </span>
            <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-accent transition-all duration-300 group-hover:w-full"></span>
          </a>
        </nav>
        
        <div class="flex items-center gap-4">
          <a href="#eventos" class="hidden md:inline-flex items-center gap-2 bg-gradient-to-r from-primary to-primary-dark hover:from-primary-dark hover:to-primary text-white px-5 py-2 rounded-full shadow-lg transition-all duration-300 hover:shadow-xl btn-glow">
            <i class="fas fa-ticket-alt"></i>
            Comprar Boletos
          </a>
          
          <button id="mobile-menu-button" class="lg:hidden text-white p-2 rounded-full hover:bg-gray-800 transition-all">
            <i class="fas fa-bars text-xl"></i>
          </button>
        </div>
      </div>
    </div>
    
    <!-- Menú móvil mejorado -->
    <div id="mobile-menu" class="lg:hidden fixed inset-0 z-40 bg-black/80 backdrop-blur-sm hidden transition-all duration-300 opacity-0">
      <div class="absolute top-20 right-4 bg-secondary rounded-xl shadow-2xl border border-gray-800 w-72 overflow-hidden transition-all duration-300 transform translate-y-4">
        <div class="flex flex-col p-4 gap-2">
          <a href="#inicio" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition-all text-white">
            <i class="fas fa-home w-5 text-center"></i>
            Inicio
          </a>
          <a href="#eventos" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition-all text-white">
            <i class="fas fa-trophy w-5 text-center"></i>
            Eventos
          </a>
          <a href="#como-participar" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition-all text-white">
            <i class="fas fa-medal w-5 text-center"></i>
            ¿Cómo participar?
          </a>
          <div class="border-t border-gray-800 my-2"></div>
          
          <a href="#eventos" class="flex items-center justify-center gap-2 bg-gradient-to-r from-primary to-primary-dark text-white px-4 py-3 rounded-lg mt-2 btn-glow">
            <i class="fas fa-ticket-alt"></i>
            Comprar Boletos
          </a>
        </div>
      </div>
    </div>
  </header>

  <!-- Sección Hero con efecto de partículas -->
  <section class="relative min-h-screen flex items-center pt-20 pb-16 overflow-hidden" id="inicio">
    <div class="particles" id="particles-js"></div>
    
    <div class="container mx-auto px-4 z-10">
      <div class="flex flex-col lg:flex-row items-center gap-12">
        <div class="lg:w-1/2 text-center lg:text-left fade-in">
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight">
            <span class="bg-gradient-to-r from-primary to-three bg-clip-text text-transparent">Sorteos exclusivos</span><br>
            <span class="text-white">con premios increíbles</span>
          </h1>
          
          <p class="text-xl text-gray-300 mb-8 max-w-lg mx-auto lg:mx-0">
            Participa en nuestras exclusivas rifas y conviértete en el próximo ganador. ¡Tu oportunidad está aquí!
          </p>
          
          <div class="flex flex-col sm:flex-row justify-center lg:justify-start gap-4">
            <a href="#eventos" class="btn-glow bg-gradient-to-r from-primary to-primary-dark hover:from-primary-dark hover:to-primary text-white px-8 py-4 rounded-full text-lg font-bold shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2">
              <i class="fas fa-ticket-alt"></i>
              Participar Ahora
            </a>
          </div>
        </div>
        
        <div class="lg:w-1/2 mt-12 lg:mt-0 fade-in" style="transition-delay: 0.2s">
          <div class="relative max-w-md mx-auto">
            <div class="absolute -top-6 -left-6 w-32 h-32 bg-primary rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob"></div>
            <div class="absolute -bottom-8 -right-8 w-32 h-32 bg-accent rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-2000"></div>
            <div class="absolute top-20 -right-10 w-24 h-24 bg-primary-dark rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-4000"></div>
            
            <div class="relative bg-white/5 backdrop-blur-sm border border-gray-800 rounded-3xl overflow-hidden shadow-2xl">
              <div class="p-1 from-primary to-accent">
                <div class="bg-secondary p-4 rounded-2xl">
                  <div class="flex justify-between items-center mb-4">
                    <span class="bg-primary/10 text-primary text-sm px-3 py-1 rounded-full">Rifa Activa</span>
                    <span class="text-accent text-sm font-bold"><i class="fas fa-bolt mr-1"></i> Oportunidad Única</span>
                  </div>
                  
                  <?php if (!empty($eventos_activos)): ?>
                  <?php $evento_destacado = $eventos_activos[0]; ?>
                  <img src="uploads/<?= htmlspecialchars($evento_destacado['imagen']) ?>"
                       alt="<?= htmlspecialchars($evento_destacado['titulo']) ?>" 
                       class="w-full h-48 object-cover rounded-xl mb-4 img-loading"
                       loading="lazy"
                       onload="this.classList.remove('img-loading')">
                  
                  <h3 class="text-xl font-bold text-white mb-2"><?= htmlspecialchars($evento_destacado['titulo']) ?></h3>
                  
                  <div class="flex justify-between text-sm text-gray-300 mb-3">
                    <span><i class="fas fa-calendar-alt mr-1"></i> <?= date('d M Y', strtotime($evento_destacado['fecha_fin'])) ?></span>
                    <span><i class="fas fa-ticket-alt mr-1"></i> <?= $evento_destacado['total_boletos'] - $evento_destacado['boletos_disponibles'] ?>/<?= $evento_destacado['total_boletos'] ?></span>
                  </div>
                  
                  <div class="mb-4">
                    <div class="flex justify-between text-sm mb-1">
                      <span class="text-gray-300">Boletos vendidos: <?= $evento_destacado['total_boletos'] - $evento_destacado['boletos_disponibles'] ?>/<?= $evento_destacado['total_boletos'] ?></span>
                      <span class="font-bold text-primary">$<?= number_format($evento_destacado['precio_boleto'], 2) ?> c/u</span>
                    </div>
                    <div class="progress-bar">
                      <div class="progress-bar-fill" style="width: <?= (($evento_destacado['total_boletos'] - $evento_destacado['boletos_disponibles']) / $evento_destacado['total_boletos']) * 100 ?>%"></div>
                    </div>
                  </div>
                  
                  <div class="flex justify-between items-center mb-4">
                    <div>
                      <div class="text-xs text-gray-400">Tiempo restante:</div>
                      <div class="text-lg font-bold text-white" id="countdown"><?= date_diff(new DateTime(), new DateTime($evento_destacado['fecha_fin']))->format('%d días %h horas') ?></div>
                    </div>
                    <a href="evento.php?id=<?= $evento_destacado['id'] ?>" class="btn-glow bg-gradient-to-r from-primary to-primary-dark hover:from-primary-dark hover:to-primary text-white px-6 py-2 rounded-full text-sm font-bold shadow-md hover:shadow-lg transition-all duration-300">
                      Participar <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                  </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="absolute bottom-8 left-0 right-0 flex justify-center z-10 animate-bounce-slow">
      <a href="#eventos" class="bg-white/10 hover:bg-white/20 backdrop-blur-sm border border-white/20 rounded-full p-3 text-white transition-all duration-300">
        <i class="fas fa-chevron-down"></i>
      </a>
    </div>
  </section>

  <!-- Sección de Eventos -->
  <section class="py-20 bg-background" id="eventos">
    <div class="container mx-auto px-4">
      <div class="text-center mb-16 fade-in">
        <span class="inline-block bg-primary/10 text-primary px-4 py-1 rounded-full text-sm font-semibold mb-3">
          <i class="fas fa-trophy mr-1"></i> Eventos Activos
        </span>
        <h2 class="text-3xl md:text-4xl font-bold mb-4">
          <span class="bg-gradient-to-r from-primary to-three bg-clip-text text-transparent">Participa ahora</span> 
          <span class="text-white">y gana</span>
        </h2>
        <p class="text-xl text-gray-300 max-w-3xl mx-auto">
          Elige entre nuestros increíbles eventos. Cada boleto aumenta tus posibilidades de ser el próximo afortunado ganador.
        </p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php if (!empty($eventos_activos)): ?>
          <?php foreach ($eventos_activos as $evento): ?>
            <div class="bg-secondary rounded-xl overflow-hidden shadow-xl card-hover fade-in">
              <div class="relative">
                <img src="uploads/<?= htmlspecialchars($evento['imagen']) ?>" 
                     alt="<?= htmlspecialchars($evento['titulo']) ?>" 
                     class="w-full h-56 object-cover img-loading"
                     loading="lazy"
                     onload="this.classList.remove('img-loading')">
                
                <div class="absolute top-4 right-4">
                  <span class="bg-primary text-white px-3 py-1 rounded-full text-xs font-bold flex items-center">
                    <i class="fas fa-bolt mr-1"></i> Activo
                  </span>
                </div>
                
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-4">
                  <h3 class="text-xl font-bold text-white"><?= htmlspecialchars($evento['titulo']) ?></h3>
                  <div class="flex justify-between text-sm text-gray-300">
                    <span><?= date('d M Y', strtotime($evento['fecha_fin'])) ?></span>
                    <span><?= $evento['total_boletos'] - $evento['boletos_disponibles'] ?>/<?= $evento['total_boletos'] ?></span>
                  </div>
                </div>
              </div>
              
              <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                  <div>
                    <div class="text-xs text-gray-400">Boletos vendidos</div>
                    <div class="text-sm font-bold"><?= $evento['total_boletos'] - $evento['boletos_disponibles'] ?>/<?= $evento['total_boletos'] ?></div>
                  </div>
                  <div class="text-right">
                    <div class="text-xs text-gray-400">Precio por boleto</div>
                    <div class="text-lg font-bold text-primary">$<?= number_format($evento['precio_boleto'], 2) ?></div>
                  </div>
                </div>
                
                <div class="progress-bar mb-4">
                  <div class="progress-bar-fill" style="width: <?= (($evento['total_boletos'] - $evento['boletos_disponibles']) / $evento['total_boletos']) * 100 ?>%"></div>
                </div>
                
                <div class="flex justify-between items-center mb-6">
                  <div>
                    <div class="text-xs text-gray-400">Tiempo restante</div>
                    <div class="text-sm font-bold"><?= date_diff(new DateTime(), new DateTime($evento['fecha_fin']))->format('%d días') ?></div>
                  </div>
                </div>
                
                <a href="evento.php?id=<?= $evento['id'] ?>" class="w-full btn-glow bg-gradient-to-r from-primary to-primary-dark hover:from-primary-dark hover:to-primary text-white py-3 rounded-lg font-bold shadow-md hover:shadow-lg transition-all duration-300 flex items-center justify-center gap-2">
                  <i class="fas fa-ticket-alt"></i>
                  Comprar Boletos
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-span-3 text-center py-12">
            <p class="text-xl text-gray-400">No hay eventos activos en este momento. Vuelve pronto.</p>
          </div>
        <?php endif; ?>
      </div>
      
      <!-- Eventos finalizados -->
      <div class="text-center mt-24 mb-16 fade-in">
        <span class="inline-block bg-primary/10 text-primary px-4 py-1 rounded-full text-sm font-semibold mb-3">
          <i class="fas fa-history mr-1"></i> Eventos Finalizados
        </span>
        <h2 class="text-3xl md:text-4xl font-bold mb-4">
          <span class="bg-gradient-to-r from-three to-primary bg-clip-text text-transparent">Eventos anteriores</span>
        </h2>
        <p class="text-xl text-gray-300 max-w-3xl mx-auto">
          Revisa nuestros eventos pasados y los afortunados ganadores.
        </p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php if (!empty($eventos_finalizados)): ?>
          <?php foreach ($eventos_finalizados as $evento): ?>
            <div class="bg-secondary rounded-xl overflow-hidden shadow-xl card-hover fade-in">
              <div class="relative">
                <img src="uploads/<?= htmlspecialchars($evento['imagen']) ?>" 
                     alt="<?= htmlspecialchars($evento['titulo']) ?>" 
                     class="w-full h-56 object-cover img-loading"
                     loading="lazy"
                     onload="this.classList.remove('img-loading')">
                
                <div class="absolute top-4 right-4">
                  <span class="bg-gray-700 text-white px-3 py-1 rounded-full text-xs font-bold flex items-center">
                    <i class="fas fa-check-circle mr-1"></i> Finalizado
                  </span>
                </div>
                
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-4">
                  <h3 class="text-xl font-bold text-white"><?= htmlspecialchars($evento['titulo']) ?></h3>
                  <div class="flex justify-between text-sm text-gray-300">
                    <span>Finalizado: <?= date('d M Y', strtotime($evento['fecha_fin'])) ?></span>
                  </div>
                </div>
              </div>
              
              <div class="p-6">
                <p class="text-gray-300 text-sm mb-4"><?= htmlspecialchars(substr($evento['descripcion'], 0, 150)) ?>...</p>
                
                <a href="evento.php?id=<?= $evento['id'] ?>" class="w-full border border-primary text-primary hover:bg-primary hover:text-white py-2 rounded-lg font-bold transition-all duration-300 flex items-center justify-center gap-2">
                  <i class="fas fa-info-circle"></i>
                  Ver detalles
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-span-3 text-center py-12">
            <p class="text-xl text-gray-400">No hay eventos finalizados recientemente.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Sección de Métodos de Pago -->
  <section class="py-12 bg-gradient-to-b from-secondary to-background" id="pagos">
    <div class="container mx-auto px-4">
      <div class="text-center mb-16 fade-in">
        <span class="inline-block bg-primary/10 text-primary px-4 py-1 rounded-full text-sm font-semibold mb-3">
          <i class="fas fa-credit-card mr-1"></i> Métodos de Pago
        </span>
        <h2 class="text-3xl md:text-4xl font-bold mb-4">
          <span class="bg-gradient-to-r from-primary to-three bg-clip-text text-transparent">Pagos seguros</span>
        </h2>
        <p class="text-xl text-gray-300 max-w-3xl mx-auto">
          Realiza tus pagos de forma segura a través de nuestras plataformas autorizadas.
        </p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php if (!empty($metodos_de_pago)): ?>
          <?php foreach ($metodos_de_pago as $metodo): ?>
            <div class="bg-secondary/50 backdrop-blur-sm border border-gray-800 rounded-xl p-6 text-center fade-in hover:shadow-lg transition-all duration-300">
              <?php if (!empty($metodo['icono'])): ?>
                <img src="<?= htmlspecialchars($metodo['icono']) ?>" 
                     alt="<?= htmlspecialchars($metodo['nombre']) ?>" 
                     class="h-16 mx-auto mb-4 grayscale hover:grayscale-0 transition-all">
              <?php else: ?>
                <div class="h-16 flex items-center justify-center text-4xl text-primary mb-4">
                  <i class="fas fa-credit-card"></i>
                </div>
              <?php endif; ?>
              
              <h3 class="text-xl font-bold text-white mb-3"><?= htmlspecialchars($metodo['nombre']) ?></h3>
              
              <?php if (!empty($metodo['detalles'])): ?>
                <?php $detalles = json_decode($metodo['detalles'], true); ?>
                <div class="text-left text-sm text-gray-300 space-y-2">
                  <?php foreach ($detalles as $key => $value): ?>
                    <div class="flex justify-between">
                      <span class="font-medium"><?= htmlspecialchars($key) ?>:</span>
                      <span><?= htmlspecialchars($value) ?></span>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-span-4 text-center py-12">
            <p class="text-xl text-gray-400">No hay métodos de pago disponibles actualmente.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Sección de Cómo Participar -->
  <section class="py-20 bg-gradient-to-b from-background to-secondary" id="como-participar">
    <div class="container mx-auto px-4">
      <div class="text-center mb-16 fade-in">
        <span class="inline-block bg-primary/10 text-primary px-4 py-1 rounded-full text-sm font-semibold mb-3">
          <i class="fas fa-question-circle mr-1"></i> ¿Cómo Participar?
        </span>
        <h2 class="text-3xl md:text-4xl font-bold mb-4">
          Gana premios exclusivos en <span class="bg-gradient-to-r from-three to-primary bg-clip-text text-transparent">3 simples pasos</span>
        </h2>
        <p class="text-xl text-gray-300 max-w-3xl mx-auto">
          Nuestro proceso es 100% transparente y diseñado para brindarte la mejor experiencia.
        </p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Paso 1 -->
        <div class="bg-secondary/50 backdrop-blur-sm border border-gray-800 rounded-xl p-6 text-center fade-in" style="transition-delay: 0.1s">
          <div class="w-16 h-16 bg-primary/10 text-primary rounded-full flex items-center justify-center text-2xl font-bold mb-4 mx-auto">
            1
          </div>
          <h3 class="text-xl font-bold text-white mb-3">Elige tu premio</h3>
          <p class="text-gray-300 mb-4">
            Selecciona entre nuestra colección de premios el que más te gustaría ganar.
          </p>
          <div class="text-primary text-4xl opacity-20">
            <i class="fas fa-trophy"></i>
          </div>
        </div>
        
        <!-- Paso 2 -->
        <div class="bg-secondary/50 backdrop-blur-sm border border-gray-800 rounded-xl p-6 text-center fade-in" style="transition-delay: 0.2s">
          <div class="w-16 h-16 bg-primary/10 text-primary rounded-full flex items-center justify-center text-2xl font-bold mb-4 mx-auto">
            2
          </div>
          <h3 class="text-xl font-bold text-white mb-3">Compra tus boletos</h3>
          <p class="text-gray-300 mb-4">
            Adquiere la cantidad de boletos que desees. Mientras más compres, mayores serán tus posibilidades.
          </p>
          <div class="text-primary text-4xl opacity-20">
            <i class="fas fa-ticket-alt"></i>
          </div>
        </div>
        
        <!-- Paso 3 -->
        <div class="bg-secondary/50 backdrop-blur-sm border border-gray-800 rounded-xl p-6 text-center fade-in" style="transition-delay: 0.3s">
          <div class="w-16 h-16 bg-primary/10 text-primary rounded-full flex items-center justify-center text-2xl font-bold mb-4 mx-auto">
            3
          </div>
          <h3 class="text-xl font-bold text-white mb-3">Espera el sorteo</h3>
          <p class="text-gray-300 mb-4">
            El ganador será anunciado en vivo a través de nuestras redes sociales y notificado personalmente.
          </p>
          <div class="text-primary text-4xl opacity-20">
            <i class="fas fa-medal"></i>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Sección CTA -->
  <section class="py-16 bg-gradient-to-r from-primary to-primary-dark">
    <div class="container mx-auto px-4 text-center">
      <div class="max-w-4xl mx-auto fade-in">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">¿Listo para ganar tu premio exclusivo?</h2>
        <p class="text-xl text-white/90 mb-8">Participa ahora en nuestras rifas exclusivas. Tu sueño está a solo un boleto de distancia.</p>
        <a href="#eventos" class="inline-flex items-center bg-white text-primary px-8 py-4 rounded-full text-lg font-bold shadow-lg hover:bg-accent hover:text-primary transition-all duration-300">
          Comprar Boletos Ahora
          <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-all"></i>
        </a>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-background border-t border-gray-800 pt-16 pb-8">
    <div class="container mx-auto px-4">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">
        <div>
          <a href="#" class="flex items-center gap-2 mb-6">
            <img src="./uploads/logocolor.webp" alt="Rifas Premium" class="h-10 w-10 rounded-lg" loading="lazy">
            <span class="text-2xl font-bold bg-gradient-to-r from-primary to-three bg-clip-text text-transparent">
              RIFAS PREMIUM
            </span>
          </a>
          <p class="text-gray-400 mb-6">
            Participa en nuestros exclusivos sorteos y vive la emoción de ganar increíbles premios.
          </p>
          <div class="flex space-x-4">
            <a href="#" class="social-icon text-gray-400 hover:text-primary transition-all">
              <i class="fab fa-facebook-f"></i>
            </a>
            <a href="#" class="social-icon text-gray-400 hover:text-blue-400 transition-all">
              <i class="fab fa-twitter"></i>
            </a>
            <a href="#" class="social-icon text-gray-400 hover:text-pink-600 transition-all">
              <i class="fab fa-instagram"></i>
            </a>
          </div>
        </div>
        
        <div>
          <h3 class="text-lg font-bold text-white mb-6">Enlaces Rápidos</h3>
          <ul class="space-y-3">
            <li><a href="#inicio" class="text-gray-400 hover:text-primary transition-all">Inicio</a></li>
            <li><a href="#eventos" class="text-gray-400 hover:text-primary transition-all">Eventos</a></li>
            <li><a href="#pagos" class="text-gray-400 hover:text-primary transition-all">Métodos de Pago</a></li>
            <li><a href="#como-participar" class="text-gray-400 hover:text-primary transition-all">¿Cómo participar?</a></li>
          </ul>
        </div>
        
        <div>
          <h3 class="text-lg font-bold text-white mb-6">Legal</h3>
          <ul class="space-y-3">
            <li><a href="#" class="text-gray-400 hover:text-primary transition-all">Términos y condiciones</a></li>
            <li><a href="#" class="text-gray-400 hover:text-primary transition-all">Política de privacidad</a></li>
            <li><a href="#" class="text-gray-400 hover:text-primary transition-all">Aviso legal</a></li>
          </ul>
        </div>
        
        <div>
          <h3 class="text-lg font-bold text-white mb-6">Contacto</h3>
          <ul class="space-y-3">
            <li class="flex items-center text-gray-400">
              <i class="fas fa-map-marker-alt mr-3 text-primary"></i>
              <span>Av. Principal 123, Ciudad</span>
            </li>
            <li class="flex items-center text-gray-400">
              <i class="fas fa-phone-alt mr-3 text-primary"></i>
              <span>+1 234 567 890</span>
            </li>
            <li class="flex items-center text-gray-400">
              <i class="fas fa-envelope mr-3 text-primary"></i>
              <span>info@rifaspremium.com</span>
            </li>
          </ul>
        </div>
      </div>
      
      <div class="border-t border-gray-800 pt-8">
        <div class="flex flex-col md:flex-row justify-between items-center">
          <p class="text-gray-400 text-sm mb-4 md:mb-0">
            © <?= date('Y') ?> Rifas Premium. Todos los derechos reservados.
          </p>
          <div class="flex space-x-4">
            <a href="#" class="text-gray-400 hover:text-primary transition-all text-sm">Términos</a>
            <a href="#" class="text-gray-400 hover:text-primary transition-all text-sm">Privacidad</a>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <!-- Botón de WhatsApp -->
  <a href="https://wa.me/34910000000" target="_blank" class="fixed bottom-6 right-6 bg-green-500 hover:bg-green-600 text-white w-14 h-14 rounded-full flex items-center justify-center shadow-lg z-40 transition-all hover:scale-110">
    <i class="fab fa-whatsapp text-2xl"></i>
  </a>

  <!-- Botón de Volver Arriba -->
  <button id="back-to-top" class="fixed bottom-24 right-6 bg-primary hover:bg-primary-dark text-white w-12 h-12 rounded-full flex items-center justify-center shadow-lg z-40 transition-all opacity-0 invisible hover:scale-110">
    <i class="fas fa-arrow-up"></i>
  </button>

  <!-- Scripts optimizados -->
  <script>
    // Mobile Menu Toggle
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    mobileMenuButton.addEventListener('click', () => {
      mobileMenu.classList.toggle('hidden');
      setTimeout(() => {
        mobileMenu.classList.toggle('opacity-0');
        mobileMenu.classList.toggle('translate-y-4');
      }, 10);
    });
    
    // Cerrar menú al hacer clic fuera
    document.addEventListener('click', (e) => {
      if (!mobileMenu.contains(e.target) && !mobileMenuButton.contains(e.target)) {
        mobileMenu.classList.add('opacity-0');
        mobileMenu.classList.add('translate-y-4');
        setTimeout(() => {
          mobileMenu.classList.add('hidden');
        }, 300);
      }
    });
    
    // Smooth scrolling para enlaces internos
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Cerrar menú móvil si está abierto
        if (!mobileMenu.classList.contains('hidden')) {
          mobileMenu.classList.add('opacity-0');
          mobileMenu.classList.add('translate-y-4');
          setTimeout(() => {
            mobileMenu.classList.add('hidden');
          }, 300);
        }
        
        const targetId = this.getAttribute('href');
        const targetElement = document.querySelector(targetId);
        
        if (targetElement) {
          window.scrollTo({
            top: targetElement.offsetTop - 80,
            behavior: 'smooth'
          });
        }
      });
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
    
    // Botón de volver arriba
    const backToTopButton = document.getElementById('back-to-top');
    
    window.addEventListener('scroll', () => {
      if (window.scrollY > 300) {
        backToTopButton.classList.remove('opacity-0');
        backToTopButton.classList.remove('invisible');
      } else {
        backToTopButton.classList.add('opacity-0');
        backToTopButton.classList.add('invisible');
      }
    });
    
    backToTopButton.addEventListener('click', () => {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
    
    // Efecto de partículas simple
    function createParticles() {
      const particlesContainer = document.getElementById('particles-js');
      const particleCount = 30;
      
      for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.classList.add('particle');
        
        // Posición aleatoria
        const posX = Math.random() * 100;
        const posY = Math.random() * 100;
        
        // Tamaño aleatorio
        const size = Math.random() * 5 + 2;
        
        // Duración de animación aleatoria
        const duration = Math.random() * 20 + 10;
        const delay = Math.random() * 5;
        
        // Estilos
        particle.style.left = `${posX}%`;
        particle.style.top = `${posY}%`;
        particle.style.width = `${size}px`;
        particle.style.height = `${size}px`;
        particle.style.opacity = Math.random() * 0.5 + 0.1;
        particle.style.animation = `float ${duration}s ease-in-out ${delay}s infinite`;
        
        particlesContainer.appendChild(particle);
      }
    }
    
    createParticles();
  </script>
  <?php
    // Display memory usage
    echo "<div style='position: fixed; bottom: 0; left: 0; background-color: #f0f0f0; color: #333; padding: 10px; font-size: 12px;'>";
    echo "Pico de uso de RAM: " . round(memory_get_peak_usage() / 1024 / 1024, 2) . " MB";
    echo "</div>";
    ?>

</body>
</html>