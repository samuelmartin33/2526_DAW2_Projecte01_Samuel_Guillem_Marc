document.addEventListener('DOMContentLoaded', () => {

    // --- Referencias a elementos del Modal ---
    const modal = document.getElementById('modal-gestion-mesa');
    const modalCloseBtn = document.getElementById('modal-close-btn');
    const modalTitle = document.getElementById('modal-title');
    const modalStatus = document.getElementById('modal-status');
    const modalCapacidad = document.getElementById('modal-capacidad');
    
    const infoOcupada = document.getElementById('modal-info-ocupada');
    const modalCamarero = document.getElementById('modal-camarero');
    const formAsignar = document.getElementById('form-asignar-mesa');
    const accionesOcupada = document.getElementById('modal-acciones-ocupada');
    
    const hiddenMesaId = document.getElementById('hidden-mesa-id');
    const hiddenCamareroId = document.getElementById('hidden-camarero-id');
    const numComensalesInput = document.getElementById('num-comensales');
    const btnAsignar = document.getElementById('btn-asignar');
    const btnDesasignar = document.getElementById('btn-desasignar');
    const modalErrorMessage = document.getElementById('modal-error-message');

    const camareroLogueadoId = parseInt(hiddenCamareroId.value, 10);

    // --- 1. Abrir el Modal ---
    document.querySelectorAll('.mesa').forEach(mesa => {
        mesa.addEventListener('click', () => {
            const mesaId = mesa.dataset.mesaId;
            openModalWithMesaInfo(mesaId);
        });
    });

    // --- 2. Cerrar el Modal ---
    modalCloseBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });

    function openModal() {
        modal.classList.add('show');
    }

    function closeModal() {
        modal.classList.remove('show');
        resetModal();
    }

    // --- 3. Resetear el modal a su estado inicial ---
    function resetModal() {
        modalTitle.textContent = 'Cargando...';
        modalStatus.textContent = '...';
        modalStatus.className = 'modal-status';
        modalCapacidad.textContent = '...';
        
        infoOcupada.style.display = 'none';
        formAsignar.style.display = 'none';
        accionesOcupada.style.display = 'none';
        btnDesasignar.style.display = 'none'; 
        modalErrorMessage.style.display = 'none';
        
        numComensalesInput.value = '';
        hiddenMesaId.value = '';
    }

    // --- 4. Cargar datos de la mesa vía API (Fetch) ---
    async function openModalWithMesaInfo(mesaId) {
        resetModal();
        openModal();

        try {
            // La ruta es relativa a la página (SALAS/ -> API/)
            const response = await fetch(`../API/get_mesa_info.php?id=${mesaId}`);
            if (!response.ok) {
                throw new Error('Error al conectar con el servidor.');
            }
            
            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.error);
            }

            // Rellenar datos comunes
            hiddenMesaId.value = data.mesa_id;
            modalTitle.textContent = data.mesa_nombre;
            modalCapacidad.textContent = `${data.mesa_sillas} sillas`;
            
            if (data.mesa_estado == 1) { // Estado: LIBRE
                modalStatus.textContent = 'Libre';
                modalStatus.classList.add('libre');
                formAsignar.style.display = 'flex';
                numComensalesInput.max = data.mesa_sillas;

            } else if (data.mesa_estado == 2) { // Estado: OCUPADA
                modalStatus.textContent = 'Ocupada';
                modalStatus.classList.add('ocupada');
                infoOcupada.style.display = 'block';
                accionesOcupada.style.display = 'flex';
                modalCamarero.textContent = data.camarero_username || 'No definido';

                // Solo mostrar el botón si el camarero logueado es el dueño
                if (camareroLogueadoId === data.camarero_id) {
                    btnDesasignar.style.display = 'block';
                } else {
                    btnDesasignar.style.display = 'none';
                }
            }

        } catch (error) {
            modalErrorMessage.textContent = `Error: ${error.message}`;
            modalErrorMessage.style.display = 'block';
        }
    }


    // --- 5. Acción: Asignar Mesa ---
    formAsignar.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const mesaId = hiddenMesaId.value;
        const camareroId = hiddenCamareroId.value;
        const comensales = numComensalesInput.value;
        const capacidad = parseInt(numComensalesInput.max, 10);

        if (comensales <= 0) {
            showError('El número de comensales debe ser al menos 1.');
            return;
        }
        if (comensales > capacidad) {
            showError(`El número de comensales (${comensales}) supera la capacidad de la mesa (${capacidad}).`);
            return;
        }

        try {
            const formData = new FormData();
            formData.append('id_mesa', mesaId);
            formData.append('id_camarero', camareroId);
            formData.append('num_comensales', comensales);

            const response = await fetch('../API/asignar_mesa.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                document.getElementById(`mesa-${mesaId}`).classList.remove('libre');
                document.getElementById(`mesa-${mesaId}`).classList.add('ocupada');
                closeModal();
            } else {
                showError(result.error || 'No se pudo asignar la mesa.');
            }

        } catch (error) {
            showError(`Error de conexión: ${error.message}`);
        }
    });


    // --- 6. Acción: Desasignar Mesa ---
    btnDesasignar.addEventListener('click', async () => {
        
        const mesaId = hiddenMesaId.value;

        if (!confirm('¿Estás seguro de que quieres liberar esta mesa?')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('id_mesa', mesaId);

            const response = await fetch('../API/desasignar_mesa.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                document.getElementById(`mesa-${mesaId}`).classList.remove('ocupada');
                document.getElementById(`mesa-${mesaId}`).classList.add('libre');
                closeModal();
            } else {
                showError(result.error || 'No se pudo liberar la mesa.');
            }

        } catch (error) {
            showError(`Error de conexión: ${error.message}`);
        }
    });

    function showError(message) {
        modalErrorMessage.textContent = message;
        modalErrorMessage.style.display = 'block';
    }
});