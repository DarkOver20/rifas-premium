document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('DOMContentLoaded', function() {
        // Elementos del DOM
        const seleccionAleatoriaBtn = document.getElementById('seleccion-aleatoria');
        const seleccionManualBtn = document.getElementById('seleccion-manual');
        const cantidadBoletosInput = document.getElementById('cantidad-boletos');
        const boletosContainer = document.querySelector('.boletos-container');
        const boletosSeleccionadosDiv = document.getElementById('boletos-seleccionados');
        const totalPagarSpan = document.getElementById('total-pagar');
        const confirmarPagoBtn = document.getElementById('confirmar-pago');
        const formPago = document.getElementById('form-pago');
        const metodosPagoContainer = document.getElementById('metodos-pago');
        
        // Variables
        const precioBoleto = parseFloat(document.querySelector('.evento-precio strong').textContent.replace(/[^0-9.]/g, ''));
        let boletosSeleccionados = [];
        
        // Inicialización
        cargarMetodosPago();
        seleccionManualBtn.classList.add('active');
        
        // Event Listeners
        seleccionAleatoriaBtn.addEventListener('click', seleccionAleatoria);
        seleccionManualBtn.addEventListener('click', activarSeleccionManual);
        boletosContainer.addEventListener('click', manejarClickBoleto);
        formPago.addEventListener('submit', procesarPago);
        document.getElementById('referencia').addEventListener('input', verificarEstadoBotonPago);
        
        // ... resto de tus funciones (seleccionAleatoria, verificarEstadoBotonPago, etc.)
    });
    // Función para cargar métodos de pago
    async function cargarMetodosPago() {
        try {
            const response = await fetch('api/metodos_pago.php');
            const data = await response.json();
            
            if (data.success) {
                metodosPagoContainer.innerHTML = data.data.map(metodo => `
                    <div class="metodo-pago" data-id="${metodo.id}">
                        <input type="radio" name="metodo_pago" id="metodo-${metodo.id}" required>
                        <label for="metodo-${metodo.id}">
                            <img src="uploads/${metodo.icono}" alt="${metodo.nombre}">
                            <span>${metodo.nombre}</span>
                            <p>${metodo.descripcion}</p>
                        </label>
                    </div>
                `).join('');
                
                // Evento para selección de método de pago
                document.querySelectorAll('.metodo-pago').forEach(metodo => {
                    metodo.addEventListener('click', function() {
                        metodoPagoSeleccionado = this.getAttribute('data-id');
                        verificarEstadoBotonPago();
                    });
                });
            }
        } catch (error) {
            console.error('Error al cargar métodos de pago:', error);
        }
    }

    // Reemplaza el código existente por este:
function seleccionAleatoria() {
    const cantidad = parseInt(cantidadBoletosInput.value) || 1;
    const boletosDisponibles = Array.from(document.querySelectorAll('.boleto:not(.seleccionado):not(.reservado)'));
    
    if (cantidad < 1 || cantidad > 20) {
        alert('La cantidad debe estar entre 1 y 20');
        return;
    }
    
    if (boletosDisponibles.length < cantidad) {
        alert(`Solo hay ${boletosDisponibles.length} boletos disponibles`);
        return;
    }
    
    // Limpiar selección previa
    limpiarSeleccion();
    
    // Seleccionar aleatoriamente
    const shuffled = [...boletosDisponibles].sort(() => 0.5 - Math.random());
    const boletosAleatorios = shuffled.slice(0, cantidad);
    
    boletosAleatorios.forEach(boleto => {
        const numeroBoleto = boleto.getAttribute('data-numero');
        boleto.classList.add('seleccionado');
        boletosSeleccionados.push(numeroBoleto);
    });
    
    actualizarResumen();
}

// Configura el event listener (solo uno)
seleccionAleatoriaBtn.addEventListener('click', seleccionAleatoria);
    // Evento para selección manual
    seleccionManualBtn.addEventListener('click', function() {
        limpiarSeleccion();
        cantidadBoletosInput.value = 1;
        actualizarResumen();
    });
    
    // Evento para seleccionar boletos manualmente
    boletosContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('boleto') && !e.target.classList.contains('reservado')) {
            if (seleccionManualBtn.classList.contains('active')) {
                if (e.target.classList.contains('seleccionado')) {
                    deseleccionarBoleto(e.target);
                } else {
                    seleccionarBoleto(e.target);
                }
                actualizarResumen();
            }
        }
    });
    

    // Función para verificar el estado del botón de pago
function verificarEstadoBotonPago() {
    const referenciaValida = document.getElementById('referencia').value.trim().length > 0;
    const metodoSeleccionado = document.querySelector('input[name="metodo_pago"]:checked');
    const boletosSeleccionadosValidos = boletosSeleccionados.length > 0;
    
    confirmarPagoBtn.disabled = !(referenciaValida && metodoSeleccionado && boletosSeleccionadosValidos);
}

// Evento para procesar el pago
async function procesarPago(e) {
    e.preventDefault();
    
    if (boletosSeleccionados.length === 0) {
        alert('Por favor selecciona al menos un boleto');
        return;
    }
    
    const metodoPago = document.querySelector('input[name="metodo_pago"]:checked');
    if (!metodoPago) {
        alert('Por favor selecciona un método de pago');
        return;
    }
    
    const referencia = document.getElementById('referencia').value.trim();
    if (!referencia) {
        alert('Por favor ingresa el número de referencia');
        return;
    }
    
    try {
        confirmarPagoBtn.disabled = true;
        confirmarPagoBtn.textContent = 'Procesando...';
        
        const formData = new FormData(formPago);
        // Agregar boletos seleccionados al formData
        boletosSeleccionados.forEach((boleto, index) => {
            formData.append(`boletos[${index}]`, boleto);
        });
        
        const response = await fetch('proceso_pago.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = 'user/mis-rifas.php?exito=' + encodeURIComponent(data.message);
        } else {
            alert(data.message || 'Error al procesar el pago');
            confirmarPagoBtn.disabled = false;
            confirmarPagoBtn.textContent = 'Confirmar Pago';
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Ocurrió un error al procesar tu pago');
        confirmarPagoBtn.disabled = false;
        confirmarPagoBtn.textContent = 'Confirmar Pago';
    }
}

// Configura los event listeners para validación en tiempo real
document.getElementById('referencia').addEventListener('input', verificarEstadoBotonPago);
document.querySelectorAll('input[name="metodo_pago"]').forEach(input => {
    input.addEventListener('change', verificarEstadoBotonPago);
});
    // Toggle entre selección manual/aleatoria
    seleccionManualBtn.addEventListener('click', function() {
        this.classList.add('active');
        seleccionAleatoriaBtn.classList.remove('active');
    });
    
    seleccionAleatoriaBtn.addEventListener('click', function() {
        this.classList.add('active');
        seleccionManualBtn.classList.remove('active');
    });
    
    // Activar selección manual por defecto
    seleccionManualBtn.classList.add('active');
    
    // Función para seleccionar un boleto
    function seleccionarBoleto(boleto) {
        boleto.classList.add('seleccionado');
        const numeroBoleto = boleto.getAttribute('data-numero');
        
        if (!boletosSeleccionados.includes(numeroBoleto)) {
            boletosSeleccionados.push(numeroBoleto);
        }
    }
    
    // Función para deseleccionar un boleto
    function deseleccionarBoleto(boleto) {
        boleto.classList.remove('seleccionado');
        const numeroBoleto = boleto.getAttribute('data-numero');
        boletosSeleccionados = boletosSeleccionados.filter(num => num !== numeroBoleto);
    }
    
    // Función para limpiar la selección
    function limpiarSeleccion() {
        document.querySelectorAll('.boleto.seleccionado').forEach(boleto => {
            boleto.classList.remove('seleccionado');
        });
        boletosSeleccionados = [];
        actualizarResumen();
    }
    
    // Función para actualizar el resumen de compra
    function actualizarResumen() {
        // Actualizar lista de boletos seleccionados
        if (boletosSeleccionados.length > 0) {
            boletosSeleccionadosDiv.innerHTML = `
                <div class="boletos-seleccionados-list">
                    ${boletosSeleccionados.map(num => `
                        <span class="boleto-seleccionado">${num}</span>
                    `).join('')}
                </div>
                <p>${boletosSeleccionados.length} boleto(s) seleccionado(s)</p>
            `;
        } else {
            boletosSeleccionadosDiv.innerHTML = '<p>No hay boletos seleccionados</p>';
        }
        
        // Actualizar total
        const total = boletosSeleccionados.length * precioBoleto;
        totalPagarSpan.textContent = `$${total.toFixed(2)}`;
        
        // Verificar estado del botón de pago
        verificarEstadoBotonPago();
    }
    
    // Evento para proceder al pago
    procederPagoBtn.addEventListener('click', function() {
        if (boletosSeleccionados.length === 0) return;
        
        // Crear formulario dinámico
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'proceso_pago.php';
        
        // Agregar input para el evento_id
        const eventoIdInput = document.createElement('input');
        eventoIdInput.type = 'hidden';
        eventoIdInput.name = 'evento_id';
        eventoIdInput.value = new URLSearchParams(window.location.search).get('id');
        form.appendChild(eventoIdInput);
        
        // Agregar inputs para los boletos seleccionados
        boletosSeleccionados.forEach((boleto, index) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `boletos[${index}]`;
            input.value = boleto;
            form.appendChild(input);
        });
        
        // Agregar formulario al body y enviar
        document.body.appendChild(form);
        form.submit();
    });
});