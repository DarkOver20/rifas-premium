/**
 * EVENTO.JS - Módulo para manejo de selección de boletos
 * 
 * Este script maneja la selección de boletos (manual y aleatoria),
 * validación del formulario y envío de datos al servidor.
 */

document.addEventListener('DOMContentLoaded', function() {
    /**********************************************
     * CONSTANTES Y VARIABLES
     **********************************************/
    const DOM = {
        seleccionAleatoriaBtn: document.getElementById('seleccion-aleatoria'),
        seleccionManualBtn: document.getElementById('seleccion-manual'),
        cantidadBoletosInput: document.getElementById('cantidad-boletos'),
        boletosContainer: document.querySelector('.boletos-container'),
        boletosSeleccionadosDiv: document.getElementById('boletos-seleccionados'),
        totalPagarSpan: document.getElementById('total-pagar'),
        formPago: document.getElementById('formulario-pago'),
        metodoPagoSelect: document.getElementById('metodo_pago'),
        detallesMetodoPagoDiv: document.getElementById('detalles-metodo-pago-seleccionado'),
        telefonoInput: document.getElementById('telefono'),
        cedulaInput: document.getElementById('cedula'),
        metodosPagoContainer: document.getElementById('detalles-metodo-pago-seleccionado')
    };

    const precioBoleto = parseFloat(
        document.querySelector('.evento-precio strong').textContent.replace(/[^0-9.]/g, '')
    );
    
    let boletosSeleccionados = [];

    /**********************************************
     * FUNCIONES DE INICIALIZACIÓN
     **********************************************/
    function init() {
        setupEventListeners();
        cargarMetodosPago();
        DOM.seleccionManualBtn.classList.add('active');
        setupValidacionCampos();
    }

    /**********************************************
     * FUNCIONES DE MANEJO DE EVENTOS
     **********************************************/
    function setupEventListeners() {
        DOM.seleccionAleatoriaBtn.addEventListener('click', handleSeleccionAleatoria);
        DOM.seleccionManualBtn.addEventListener('click', handleSeleccionManual);
        DOM.boletosContainer.addEventListener('click', handleClickBoleto);
        DOM.formPago.addEventListener('submit', handleSubmitForm);
        DOM.metodoPagoSelect.addEventListener('change', mostrarDetallesMetodoPago);
    }

    function setupValidacionCampos() {
        if (DOM.telefonoInput) {
            DOM.telefonoInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9+\- ]/g, '');
            });
        }
        
        if (DOM.cedulaInput) {
            DOM.cedulaInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
    }

    /**********************************************
     * FUNCIONES DE SELECCIÓN DE BOLETOS
     **********************************************/
    function handleSeleccionAleatoria() {
        const cantidad = parseInt(DOM.cantidadBoletosInput.value) || 1;
        const boletosDisponibles = getBoletosDisponibles();
        
        if (!validarCantidadBoletos(cantidad, boletosDisponibles.length)) {
            return;
        }
        
        limpiarSeleccion();
        seleccionarBoletosAleatorios(boletosDisponibles, cantidad);
        actualizarResumen();
    }

    function handleSeleccionManual() {
        DOM.seleccionManualBtn.classList.add('active');
        DOM.seleccionAleatoriaBtn.classList.remove('active');
        DOM.cantidadBoletosInput.value = 1;
        limpiarSeleccion();
    }

    function handleClickBoleto(e) {
        if (!e.target.classList.contains('boleto') || e.target.classList.contains('reservado')) {
            return;
        }
        
        if (DOM.seleccionManualBtn.classList.contains('active')) {
            toggleSeleccionBoleto(e.target);
            actualizarResumen();
        }
    }

    /**********************************************
     * FUNCIONES DE MANEJO DE BOLETOS
     **********************************************/
    function getBoletosDisponibles() {
        return Array.from(document.querySelectorAll('.boleto:not(.seleccionado):not(.reservado)'));
    }

    function validarCantidadBoletos(cantidad, disponibles) {
        if (cantidad < 1 || cantidad > 20) {
            alert('La cantidad debe estar entre 1 y 20');
            return false;
        }
        
        if (disponibles < cantidad) {
            alert(`Solo hay ${disponibles} boletos disponibles`);
            return false;
        }
        
        return true;
    }

    function seleccionarBoletosAleatorios(boletosDisponibles, cantidad) {
        const shuffled = [...boletosDisponibles].sort(() => 0.5 - Math.random());
        const boletosAleatorios = shuffled.slice(0, cantidad);
        
        boletosAleatorios.forEach(boleto => {
            const numeroBoleto = boleto.getAttribute('data-numero');
            boleto.classList.add('seleccionado');
            
            if (!boletosSeleccionados.includes(numeroBoleto)) {
                boletosSeleccionados.push(numeroBoleto);
            }
        });
    }

    function toggleSeleccionBoleto(boleto) {
        if (boleto.classList.contains('seleccionado')) {
            deseleccionarBoleto(boleto);
        } else {
            if (boletosSeleccionados.length >= 20) {
                alert('Máximo 20 boletos por transacción');
                return;
            }
            seleccionarBoleto(boleto);
        }
    }

    function seleccionarBoleto(boleto) {
        boleto.classList.add('seleccionado');
        const numeroBoleto = boleto.getAttribute('data-numero');
        
        if (!boletosSeleccionados.includes(numeroBoleto)) {
            boletosSeleccionados.push(numeroBoleto);
        }
    }

    function deseleccionarBoleto(boleto) {
        boleto.classList.remove('seleccionado');
        const numeroBoleto = boleto.getAttribute('data-numero');
        boletosSeleccionados = boletosSeleccionados.filter(num => num !== numeroBoleto);
    }

    function limpiarSeleccion() {
        document.querySelectorAll('.boleto.seleccionado').forEach(boleto => {
            boleto.classList.remove('seleccionado');
        });
        boletosSeleccionados = [];
    }

    /**********************************************
     * FUNCIONES DE ACTUALIZACIÓN DE UI
     **********************************************/
    function actualizarResumen() {
        actualizarListaBoletosSeleccionados();
        actualizarTotalPagar();
        verificarEstadoBotonPago();
    }

    function actualizarListaBoletosSeleccionados() {
        if (boletosSeleccionados.length > 0) {
            DOM.boletosSeleccionadosDiv.innerHTML = `
                <div class="boletos-seleccionados-list">
                    ${boletosSeleccionados.map(num => `
                        <span class="boleto-seleccionado">${num}</span>
                    `).join('')}
                </div>
                <p>${boletosSeleccionados.length} boleto(s) seleccionado(s)</p>
            `;
        } else {
            DOM.boletosSeleccionadosDiv.innerHTML = '<p>No hay boletos seleccionados</p>';
        }
    }

    function actualizarTotalPagar() {
        const total = boletosSeleccionados.length * precioBoleto;
        DOM.totalPagarSpan.textContent = `$${total.toFixed(2)}`;
    }

    function verificarEstadoBotonPago() {
        const formValido = DOM.formPago.checkValidity();
        const boletosSeleccionadosValidos = boletosSeleccionados.length > 0;
        const submitBtn = DOM.formPago.querySelector('button[type="submit"]');
        
        if (submitBtn) {
            submitBtn.disabled = !(formValido && boletosSeleccionadosValidos);
        }
    }

    /**********************************************
     * FUNCIONES DE MÉTODOS DE PAGO
     **********************************************/
    async function cargarMetodosPago() {
        try {
            const response = await fetch('api/metodos_pago.php');
            
            if (!response.ok) {
                throw new Error('Error al cargar métodos de pago');
            }
            
            const data = await response.json();
            
            if (data.success) {
                renderizarMetodosPago(data.data);
            } else {
                throw new Error(data.message || 'Error en los datos recibidos');
            }
        } catch (error) {
            console.error('Error:', error);
            DOM.metodosPagoContainer.innerHTML = '<p class="error">Error al cargar los métodos de pago</p>';
        }
    }

    function renderizarMetodosPago(metodos) {
        DOM.metodosPagoContainer.innerHTML = metodos.map(metodo => `
            <div class="metodo-pago" data-id="${metodo.id}">
                <input type="radio" name="metodo_pago" id="metodo-${metodo.id}" value="${metodo.id}" required>
                <label for="metodo-${metodo.id}">
                    <img src="uploads/${metodo.icono}" alt="${metodo.nombre}" onerror="this.src='assets/img/pago-default.png'">
                    <span>${metodo.nombre}</span>
                    ${metodo.descripcion ? `<p>${metodo.descripcion}</p>` : ''}
                </label>
            </div>
        `).join('');
    }

    function mostrarDetallesMetodoPago() {
        DOM.detallesMetodoPagoDiv.innerHTML = '';
        const selectedOption = DOM.metodoPagoSelect.options[DOM.metodoPagoSelect.selectedIndex];
        const detallesJson = selectedOption.getAttribute('data-detalles');

        if (!detallesJson) return;

        try {
            const detalles = JSON.parse(detallesJson);
            let detallesHTML = '<ul class="detalles-pago">';
            
            for (const [key, value] of Object.entries(detalles)) {
                detallesHTML += `<li><strong>${key}:</strong> ${value}</li>`;
            }
            
            detallesHTML += '</ul>';
            DOM.detallesMetodoPagoDiv.innerHTML = detallesHTML;
        } catch (error) {
            console.error('Error al parsear JSON:', error);
            DOM.detallesMetodoPagoDiv.innerHTML = '<p class="error">Error al mostrar detalles</p>';
        }
    }

    /**********************************************
     * MANEJO DEL FORMULARIO
     **********************************************/
    async function handleSubmitForm(e) {
        e.preventDefault();
        
        if (boletosSeleccionados.length === 0) {
            alert('Por favor selecciona al menos un boleto');
            return;
        }
        
        const formData = new FormData(DOM.formPago);
        formData.append('boletos_seleccionados', JSON.stringify(boletosSeleccionados));
        
        try {
            const submitBtn = DOM.formPago.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Procesando...';
            
            const response = await fetch('procesar_compra.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Marcar los boletos como reservados en el frontend
                data.boletos_reservados.forEach(numero => {
                    const boleto = document.querySelector(`.boleto[data-numero="${numero}"]`);
                    if (boleto) {
                        boleto.classList.add('reservado');
                        boleto.classList.remove('seleccionado');
                    }
                });
                
                mostrarMensajeExito(data.message);
                DOM.formPago.reset();
                limpiarSeleccion();
            } else {
                throw new Error(data.message || 'Error al procesar el pago');
            }
        } catch (error) {
            console.error('Error:', error);
            alert(error.message || 'Ocurrió un error al procesar tu pago');
        } finally {
            const submitBtn = DOM.formPago.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Confirmar Pago';
            }
        }
    }

    function mostrarMensajeExito(mensaje) {
        // Crear un modal de éxito
        const modal = document.createElement('div');
        modal.className = 'modal-exito';
        modal.innerHTML = `
            <div class="modal-contenido">
                <h3>¡Compra exitosa!</h3>
                <p>${mensaje || 'Tu compra ha sido procesada correctamente.'}</p>
                <button onclick="cerrarModal()">Aceptar</button>
            </div>
        `;
        document.body.appendChild(modal);
        
        // Limpiar selección de boletos
        limpiarSeleccion();
        
        // Limpiar formulario
        DOM.formPago.reset();
        
        // Actualizar UI
        actualizarResumen();
    }
    
    window.cerrarModal = function cerrarModal() {
        const modal = document.querySelector('.modal-exito');
        if (modal) {
            modal.remove();
        }
        // Opcional: Desplazar al usuario al inicio de la página después de cerrar el modal
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };
    
    /**********************************************
     * INICIALIZACIÓN
     **********************************************/
    init();
});
