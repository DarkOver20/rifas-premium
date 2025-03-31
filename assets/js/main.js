document.addEventListener('DOMContentLoaded', function() {
    // Cargar eventos al iniciar
    cargarEventos();
    
    // Configurar FAQs
    setupFAQs();
    
    // Otros event listeners
});

function cargarEventos() {
    fetch('../../api/eventos.php?tipo=activos')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('eventos-activos');
            mostrarEventos(data, container);
        });
    
    fetch('../../api/eventos.php?tipo=finalizados')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('eventos-finalizados');
            mostrarEventos(data, container);
        });
}

function mostrarEventos(eventos, container) {
    container.innerHTML = '';
    
    if (eventos.length === 0) {
        container.innerHTML = '<p>No hay eventos disponibles en este momento.</p>';
        return;
    }
    
    eventos.forEach(evento => {
        const eventoCard = document.createElement('div');
        eventoCard.className = 'evento-card';
        
        eventoCard.innerHTML = `
            <img src="uploads/${evento.imagen}" alt="${evento.titulo}" class="evento-img">
            <div class="evento-content">
                <h3 class="evento-titulo">${evento.titulo}</h3>
                <div class="evento-estado ${evento.estado}">${evento.estado.toUpperCase()}</div>
                <p class="evento-descripcion">${evento.descripcion.substring(0, 100)}...</p>
                <p class="evento-precio">Precio por boleto: $${evento.precio_boleto}</p>
                <p>Boletos disponibles: ${evento.boletos_disponibles}/${evento.total_boletos}</p>
                <a href="evento.php?id=${evento.id}" class="btn">Ver detalles</a>
            </div>
        `;
        
        container.appendChild(eventoCard);
    });
}

function setupFAQs() {
    const faqQuestions = document.querySelectorAll('.faq-question');
    
    faqQuestions.forEach(question => {
        question.addEventListener('click', () => {
            const answer = question.nextElementSibling;
            const isActive = question.classList.contains('active');
            
            // Cerrar todas las respuestas primero
            document.querySelectorAll('.faq-answer').forEach(ans => {
                ans.classList.remove('show');
            });
            document.querySelectorAll('.faq-question').forEach(q => {
                q.classList.remove('active');
            });
            
            // Abrir la respuesta clickeada si no estaba activa
            if (!isActive) {
                question.classList.add('active');
                answer.classList.add('show');
            }
        });
    });
}
document.addEventListener('DOMContentLoaded', function() {
    // Cargar eventos activos
    fetch('../../api/eventos.php?tipo=activos')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('eventos-activos');
            mostrarEventos(data.data, container);
        });
    
    // Cargar eventos finalizados
    fetch('../../api/eventos.php?tipo=finalizados')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('eventos-finalizados');
            mostrarEventos(data.data, container);
        });
});

function mostrarEventos(eventos, container) {
    container.innerHTML = '';
    
    if (eventos.length === 0) {
        container.innerHTML = '<p>No hay eventos disponibles en este momento.</p>';
        return;
    }
    
    eventos.forEach(evento => {
        const eventoCard = document.createElement('div');
        eventoCard.className = 'evento-card';
        
        eventoCard.innerHTML = `
            <img src="uploads/${evento.imagen}" alt="${evento.titulo}" class="evento-img">
            <div class="evento-content">
                <h3 class="evento-titulo">${evento.titulo}</h3>
                <div class="evento-estado ${evento.estado}">
                    ${evento.estado.toUpperCase()}
                </div>
                <p class="evento-descripcion">${evento.descripcion.substring(0, 100)}...</p>
                <p class="evento-precio">Precio por boleto: $${evento.precio_boleto.toFixed(2)}</p>
                <p>Boletos disponibles: ${evento.boletos_disponibles}/${evento.total_boletos}</p>
                <a href="evento.php?id=${evento.id}" class="btn">Ver detalles</a>
            </div>
        `;
        
        container.appendChild(eventoCard);
    });
}