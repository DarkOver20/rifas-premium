<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Obtener eventos directamente desde PHP
$eventos_activos = obtenerEventos('activo');
$eventos_finalizados = obtenerEventos('finalizado');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rifas Premium - Sorteos Exclusivos</title>
    <style>
        :root {
            --primary-color: #1a5f9e;
            --primary-dark: #0d3b66;
            --primary-light: #2b8be5;
            --accent-color: #FFD700;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --white: #ffffff;
            --transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.12);
            --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.16);
            --border-radius: 12px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', 'Segoe UI', sans-serif;
        }
        
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap');
        
        body {
            background-color: var(--light-color);
            color: var(--dark-color);
            line-height: 1.7;
            overflow-x: hidden;
        }
        
        /* Header */
        .main-header {
            background: var(--white);
            padding: 1.5rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow-sm);
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.9);
        }
        
        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 800;
            text-decoration: none;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .logo-icon {
            color: var(--accent-color);
            font-size: 2rem;
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
        }
        
        .nav-item {
            margin-left: 2rem;
        }
        
        .nav-link {
            color: var(--primary-dark);
            text-decoration: none;
            font-weight: 600;
            padding: 0.5rem 0;
            position: relative;
            transition: var(--transition);
            font-size: 0.95rem;
            letter-spacing: 0.5px;
        }
        
        .nav-link:hover {
            color: var(--primary-light);
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--accent-color);
            transition: var(--transition);
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        /* Hero section */
        .hero {
            background: linear-gradient(rgba(26, 95, 158, 0.85), rgba(26, 95, 158, 0.85)), 
                        url('https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?ixlib=rb-1.2.1&auto=format&fit=crop&w=1480&q=80');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            color: var(--white);
            padding: 8rem 0 6rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            bottom: -50px;
            left: 0;
            width: 100%;
            height: 100px;
            background: var(--light-color);
            transform: skewY(-3deg);
            z-index: 1;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            position: relative;
            z-index: 2;
        }
        
        .hero h1 {
            font-size: 3.2rem;
            margin-bottom: 1.5rem;
            font-weight: 800;
            line-height: 1.2;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 1s ease;
        }
        
        .hero p {
            font-size: 1.3rem;
            max-width: 700px;
            margin: 0 auto 3rem;
            font-weight: 300;
            opacity: 0.9;
            animation: fadeInUp 1s ease 0.2s forwards;
            opacity: 0;
        }
        
        .hero-button {
            display: inline-block;
            background: var(--accent-color);
            color: var(--primary-dark);
            padding: 1rem 2.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.1rem;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
            animation: fadeInUp 1s ease 0.4s forwards;
            opacity: 0;
            border: none;
            cursor: pointer;
        }
        
        .hero-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(255, 215, 0, 0.4);
        }
        
        /* Sección de Eventos */
        .eventos-section {
            padding: 6rem 0 4rem;
            position: relative;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
            color: var(--primary-dark);
            font-size: 2.5rem;
            font-weight: 700;
            position: relative;
            display: inline-block;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .section-title::after {
            content: '';
            display: block;
            width: 100px;
            height: 4px;
            background: var(--accent-color);
            margin: 1rem auto;
            border-radius: 2px;
        }
        
        .section-subtitle {
            text-align: center;
            max-width: 700px;
            margin: 0 auto 4rem;
            color: #555;
            font-size: 1.1rem;
        }
        
        .eventos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2.5rem;
            margin-bottom: 4rem;
        }
        
        .evento-card {
            background: var(--white);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            position: relative;
            z-index: 1;
        }
        
        .evento-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            opacity: 0;
            transition: var(--transition);
            z-index: -1;
        }
        
        .evento-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
        }
        
        .evento-card:hover::before {
            opacity: 0.05;
        }
        
        .evento-img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            display: block;
        }
        
        .evento-content {
            padding: 2rem;
            text-align: center;
            position: relative;
        }
        
        .evento-titulo {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
            color: var(--primary-dark);
        }
        
        .evento-estado {
            position: absolute;
            top: 20px;
            right: -30px;
            background: var(--accent-color);
            color: var(--primary-dark);
            padding: 0.3rem 2rem;
            font-weight: 700;
            transform: rotate(45deg);
            font-size: 0.8rem;
            box-shadow: var(--shadow-sm);
        }
        
        .evento-estado.activo {
            background: #4CAF50;
            color: white;
        }
        
        .evento-estado.finalizado {
            background: #F44336;
            color: white;
        }
        
        .evento-descripcion {
            font-style: italic;
            color: #666;
            margin-bottom: 1rem;
            font-size: 1rem;
        }
        
        .evento-precio {
            font-size: 1.5rem;
            color: var(--primary-color);
            font-weight: 800;
            margin: 1.5rem 0;
            position: relative;
        }
        
        .evento-precio::after {
            content: '';
            display: block;
            width: 60px;
            height: 3px;
            background: var(--accent-color);
            margin: 1rem auto;
        }
        
        .btn {
            display: inline-block;
            background: var(--primary-color);
            color: var(--white);
            padding: 0.9rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            border: 2px solid var(--primary-color);
            font-size: 0.95rem;
            letter-spacing: 0.5px;
        }
        
        .btn:hover {
            background: transparent;
            color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(26, 95, 158, 0.2);
        }
        
        /* Sección de FAQ */
        .faq-section {
            padding: 5rem 0;
            background: linear-gradient(135deg, #f9fafb, #f0f4f8);
            position: relative;
        }
        
        .faq-section::before {
            content: '';
            position: absolute;
            top: -50px;
            left: 0;
            width: 100%;
            height: 100px;
            background: var(--light-color);
            transform: skewY(3deg);
            z-index: 1;
        }
        
        .faq-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .faq-item {
            background: var(--white);
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }
        
        .faq-question {
            padding: 1.5rem;
            font-weight: 600;
            cursor: pointer;
            position: relative;
            color: var(--primary-dark);
        }
        
        .faq-question::after {
            content: '+';
            position: absolute;
            right: 1.5rem;
            font-size: 1.5rem;
            transition: var(--transition);
        }
        
        .faq-item.active .faq-question::after {
            content: '-';
        }
        
        .faq-answer {
            padding: 0 1.5rem;
            max-height: 0;
            overflow: hidden;
            transition: var(--transition);
        }
        
        .faq-item.active .faq-answer {
            padding: 0 1.5rem 1.5rem;
            max-height: 300px;
        }
        
        /* Footer */
        footer {
            background: var(--dark-color);
            color: var(--white);
            text-align: center;
            padding: 3rem 0 2rem;
            font-size: 0.95rem;
            position: relative;
        }
        
        .footer-logo {
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            display: inline-block;
            color: var(--white);
        }
        
        .footer-text {
            max-width: 600px;
            margin: 0 auto 2rem;
            opacity: 0.8;
            line-height: 1.8;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        
        .footer-link {
            color: var(--white);
            text-decoration: none;
            transition: var(--transition);
            opacity: 0.8;
        }
        
        .footer-link:hover {
            color: var(--accent-color);
            opacity: 1;
        }
        
        .copyright {
            opacity: 0.6;
            font-size: 0.9rem;
            margin-top: 2rem;
        }
        
        /* Animaciones */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero p {
                font-size: 1.1rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 768px) {
            .header-container {
                padding: 0 1.5rem;
            }
            
            .logo {
                font-size: 1.5rem;
            }
            
            .nav-menu {
                display: none;
            }
            
            .hero {
                padding: 6rem 0 4rem;
            }
            
            .hero h1 {
                font-size: 2.2rem;
            }
            
            .hero p {
                font-size: 1rem;
                margin-bottom: 2rem;
            }
            
            .hero-button {
                padding: 0.9rem 2rem;
                font-size: 1rem;
            }
            
            .eventos-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .section-title {
                font-size: 1.8rem;
            }
            
            .evento-img {
                height: 180px;
            }
            
            .evento-content {
                padding: 1.5rem;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <a href="#inicio" class="logo">
                <i class="fas fa-trophy logo-icon"></i>
                RIFAS PREMIUM
            </a>
            <nav>
                <ul class="nav-menu">
                    <li class="nav-item"><a href="#inicio" class="nav-link">Inicio</a></li>
                    <li class="nav-item"><a href="#eventos" class="nav-link">Eventos</a></li>
                    <li class="nav-item"><a href="#faq" class="nav-link">FAQ</a></li>
                    <li class="nav-item"><a href="#contacto" class="nav-link">Contacto</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <section class="hero" id="inicio">
        <div class="container">
            <h1>SORTEOS EXCLUSIVOS CON PREMIOS INCREÍBLES</h1>
            <p>Participa por la oportunidad de ganar los mejores premios con sorteos 100% transparentes</p>
            <a href="#eventos" class="hero-button">VER EVENTOS DISPONIBLES</a>
        </div>
    </section>
    
    <section class="eventos-section" id="eventos">
        <div class="container">
            <h2 class="section-title">EVENTOS ACTIVOS</h2>
            
            <div class="eventos-grid" id="eventos-activos">
                <?php if (!empty($eventos_activos)): ?>
                    <?php foreach ($eventos_activos as $evento): ?>
                        <div class="evento-card">
                            <img src="uploads/<?= htmlspecialchars($evento['imagen']) ?>" alt="<?= htmlspecialchars($evento['titulo']) ?>" class="evento-img">
                            <div class="evento-content">
                                <h3 class="evento-titulo"><?= htmlspecialchars($evento['titulo']) ?></h3>
                                <div class="evento-estado <?= $evento['estado'] ?>">
                                    <?= strtoupper($evento['estado']) ?>
                                </div>
                                <p class="evento-descripcion"><?= htmlspecialchars(substr($evento['descripcion'], 0, 100)) ?>...</p>
                                <p class="evento-precio">$<?= number_format($evento['precio_boleto'], 2) ?> por boleto</p>
                                <p>Boletos: <?= $evento['boletos_disponibles'] ?>/<?= $evento['total_boletos'] ?></p>
                                <a href="evento.php?id=<?= $evento['id'] ?>" class="btn">VER DETALLES</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="section-subtitle">No hay eventos activos en este momento.</p>
                <?php endif; ?>
            </div>

            <h2 class="section-title">EVENTOS FINALIZADOS</h2>
            
            <div class="eventos-grid" id="eventos-finalizados">
                <?php if (!empty($eventos_finalizados)): ?>
                    <?php foreach ($eventos_finalizados as $evento): ?>
                        <div class="evento-card">
                            <img src="uploads/<?= htmlspecialchars($evento['imagen']) ?>" alt="<?= htmlspecialchars($evento['titulo']) ?>" class="evento-img">
                            <div class="evento-content">
                                <h3 class="evento-titulo"><?= htmlspecialchars($evento['titulo']) ?></h3>
                                <div class="evento-estado <?= $evento['estado'] ?>">
                                    <?= strtoupper($evento['estado']) ?>
                                </div>
                                <p class="evento-descripcion"><?= htmlspecialchars(substr($evento['descripcion'], 0, 100)) ?>...</p>
                                <p class="evento-precio">$<?= number_format($evento['precio_boleto'], 2) ?> por boleto</p>
                                <a href="evento.php?id=<?= $evento['id'] ?>" class="btn">VER DETALLES</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="section-subtitle">No hay eventos finalizados recientemente.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="faq-section" id="faq">
        <div class="container">
            <h2 class="section-title">PREGUNTAS FRECUENTES</h2>
            
            <div class="faq-container">
                <div class="faq-item">
                    <div class="faq-question">¿Cuántos boletos mínimos necesito para participar?</div>
                    <div class="faq-answer">Puedes participar con solo 1 boleto. No hay mínimo requerido.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">¿Cómo selecciono mis boletos?</div>
                    <div class="faq-answer">Puedes elegirlos manualmente o usar nuestro sistema de selección aleatoria.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">¿Qué métodos de pago aceptan?</div>
                    <div class="faq-answer">Aceptamos transferencias bancarias, pago móvil y efectivo en algunos casos.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">¿Cómo sé que el sorteo es legítimo?</div>
                    <div class="faq-answer">Todos nuestros sorteos son grabados y transmitidos en vivo, con testigos de fe pública para garantizar transparencia.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">¿Cuándo se entregan los premios?</div>
                    <div class="faq-answer">Los premios se entregan dentro de los 15 días hábiles posteriores al sorteo.</div>
                </div>
            </div>
        </div>
    </section>

    <footer id="contacto">
        <div class="container">
            <a href="#" class="footer-logo">RIFAS PREMIUM</a>
            <p class="footer-text">Participa en nuestros exclusivos sorteos y vive la emoción de ganar increíbles premios.</p>
            
            <div class="footer-links">
                <a href="#inicio" class="footer-link">Inicio</a>
                <a href="#eventos" class="footer-link">Eventos</a>
                <a href="#faq" class="footer-link">Preguntas</a>
                <a href="#contacto" class="footer-link">Contacto</a>
                <a href="#" class="footer-link">Términos</a>
                <a href="#" class="footer-link">Privacidad</a>
            </div>
            
            <div class="social-links">
                <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-link"><i class="fab fa-whatsapp"></i></a>
            </div>
            
            <p class="copyright">&copy; <?= date('Y') ?> Rifas Premium. Todos los derechos reservados.</p>
        </div>
    </footer>
    
    <script>
        // FAQ toggle functionality
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', () => {
                const item = question.parentNode;
                item.classList.toggle('active');
                
                // Cerrar los demás items
                document.querySelectorAll('.faq-item').forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.classList.remove('active');
                    }
                });
            });
        });

        // Smooth scrolling para los enlaces
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                    
                    // Actualizar URL sin recargar
                    history.pushState(null, null, targetId);
                }
            });
        });

        // Mostrar elementos con animación al hacer scroll
        const animateOnScroll = () => {
            const elements = document.querySelectorAll('.evento-card, .faq-item');
            
            elements.forEach(element => {
                const elementPosition = element.getBoundingClientRect().top;
                const screenPosition = window.innerHeight / 1.2;
                
                if (elementPosition < screenPosition) {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }
            });
        };

        // Configurar animaciones iniciales
        window.addEventListener('load', () => {
            const eventCards = document.querySelectorAll('.evento-card');
            const faqItems = document.querySelectorAll('.faq-item');
            
            eventCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = `opacity 0.5s ease ${index * 0.1}s, transform 0.5s ease ${index * 0.1}s`;
            });
            
            faqItems.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                item.style.transition = `opacity 0.5s ease ${index * 0.1}s, transform 0.5s ease ${index * 0.1}s`;
            });
            
            // Forzar reflow
            void document.body.offsetHeight;
            
            animateOnScroll();
        });

        window.addEventListener('scroll', animateOnScroll);
    </script>
</body>
</html>