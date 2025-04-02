<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Obtener eventos directamente desde PHP
$eventos_activos = obtenerEventos('activo');
$eventos_finalizados = obtenerEventos('finalizado');
$metodos_de_pago = obtener_metodos_pago();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rifas Premium - Sorteos Exclusivos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/inicio.css">
    
</head>
<body>
    <header class="main-header">
        <div class="header-container">
        <a href="#inicio" class="logo" style="display: flex; align-items: center; text-decoration: none;">
    <i class="logo-icon" style="margin-right: 8px;">
        <img src="./uploads/logocolor.webp" alt="Logo de RIFAS PREMIUM" style="height: 40px; width: auto;">
    </i>
    <span style="font-size: 24px; color: #333;">RIFAS PREMIUM</span>
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
            <p class="section-subtitle">Participa en nuestros exclusivos sorteos y gana increíbles premios</p>
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
                                <!-- <p class="evento-precio">$<?= number_format($evento['precio_boleto'], 2) ?> por boleto</p>
                                <p>Boletos: <?= $evento['boletos_disponibles'] ?>/<?= $evento['total_boletos'] ?></p> -->
                                <a href="evento.php?id=<?= $evento['id'] ?>" class="btn">VER DETALLES</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="section-subtitle">No hay eventos activos en este momento.</p>
                <?php endif; ?>
            </div>
<br>
            <h2 class="section-title">EVENTOS FINALIZADOS</h2>
            <p class="section-subtitle">Puedes ver nuestros anteriores eventos</p>
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
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="section-subtitle">No hay eventos finalizados recientemente.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="payment-section" id="pagos">
    <div class="container">
        <h2 class="section-title">MÉTODOS DE PAGO</h2>
        <p class="section-subtitle">Realiza tus pagos de forma segura a través de nuestras plataformas autorizadas</p>

        <div class="payment-methods">
            <?php if (!empty($metodos_de_pago)): ?>
                <?php foreach ($metodos_de_pago as $metodo): ?>
                    <div class="payment-card hover-scale">
                        <?php if (!empty($metodo['icono'])): ?>
                            <img src="<?php echo htmlspecialchars($metodo['icono']); ?>" alt="<?php echo htmlspecialchars($metodo['nombre']); ?>" class="payment-icon" width="50">
                        <?php else: ?>
                            <i class="fas fa-credit-card payment-icon"></i> <?php endif; ?>
                        <h3 class="payment-title"><?php echo htmlspecialchars($metodo['nombre']); ?></h3>
                        <span><?php echo htmlspecialchars($metodo['detalles']); ?> </span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay métodos de pago disponibles.</p>
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
            <div class="footer-grid">
                <div class="footer-col">
                    <a href="#" class="footer-logo">RIFAS PREMIUM</a>
                    <p class="footer-about">Participa en nuestros exclusivos sorteos y vive la emoción de ganar increíbles premios.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h3 class="footer-title">Enlaces rápidos</h3>
                    <ul class="footer-links">
                        <li><a href="#inicio" class="footer-link">Inicio</a></li>
                        <li><a href="#eventos" class="footer-link">Eventos</a></li>
                        <li><a href="#faq" class="footer-link">Preguntas</a></li>
                        <li><a href="#contacto" class="footer-link">Contacto</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3 class="footer-title">Legal</h3>
                    <ul class="footer-links">
                        <li><a href="#" class="footer-link">Términos y condiciones</a></li>
                        <li><a href="#" class="footer-link">Política de privacidad</a></li>
                        <li><a href="#" class="footer-link">Aviso legal</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3 class="footer-title">Contacto</h3>
                    <div class="footer-contact-item">
                        <i class="fas fa-map-marker-alt footer-contact-icon"></i>
                        <span>Av. Principal 123, Ciudad</span>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fas fa-phone-alt footer-contact-icon"></i>
                        <span>+1 234 567 890</span>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fas fa-envelope footer-contact-icon"></i>
                        <span>info@rifaspremium.com</span>
                    </div>
                </div>
            </div>
            <p class="copyright">&copy; <?= date('Y') ?> Rifas Premium. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- Botón flotante de WhatsApp -->
    <a href="#" class="whatsapp-float">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- Botón para ir arriba -->
    <button class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>
    
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

// Configurar animaciones iniciales (SOLO para entrada)
window.addEventListener('load', () => {
    const eventCards = document.querySelectorAll('.evento-card');
    const faqItems = document.querySelectorAll('.faq-item');
    
    // Añadir clase inicial para animación
    eventCards.forEach((card, index) => {
        card.classList.add('initial-animation');
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = `opacity 0.4s ease ${index * 0.1}s, transform 0.4s ease ${index * 0.1}s`;
    });
    
    faqItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(20px)';
        item.style.transition = `opacity 0.4s ease ${index * 0.1}s, transform 0.4s ease ${index * 0.1}s`;
    });
    
    // Forzar reflow
    void document.body.offsetHeight;
    
    animateOnScroll();
    
    // Eliminar estilos inline después de la animación
    setTimeout(() => {
        eventCards.forEach(card => {
            card.style.transition = '';
            card.classList.remove('initial-animation');
        });
    }, 1500); // Tiempo suficiente para todas las animaciones
});

window.addEventListener('scroll', animateOnScroll);

// Animación del título (scroll direction)
document.addEventListener('DOMContentLoaded', function() {
    const eventosSection = document.querySelector('.eventos-section');
    let lastScrollY = window.scrollY;
    let ticking = false;

    function checkScrollDirection() {
        const currentScrollY = window.scrollY;
        const sectionRect = eventosSection.getBoundingClientRect();
        const isSectionVisible = (sectionRect.top <= window.innerHeight * 0.7) && (sectionRect.bottom >= 0);

        if (isSectionVisible) {
            if (currentScrollY > lastScrollY) {
                eventosSection.classList.add('scroll-down');
                eventosSection.classList.remove('scroll-up');
            } else if (currentScrollY < lastScrollY) {
                eventosSection.classList.add('scroll-up');
                eventosSection.classList.remove('scroll-down');
            }
        }
        lastScrollY = currentScrollY;
        ticking = false;
    }

    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(checkScrollDirection);
            ticking = true;
        }
    });
});
    </script>
</body>
</html>