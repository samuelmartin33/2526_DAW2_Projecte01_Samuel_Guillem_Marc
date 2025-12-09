// =============================================
//   GESTIÓN DE MESAS - JavaScript
// =============================================

// Función para abrir modal de creación
function abrirModalCrear() {
    document.getElementById('formCrear').reset();
    document.getElementById('modalCrear').style.display = 'flex';
}

// Función para cerrar modal de crear
function cerrarModalCrear() {
    document.getElementById('modalCrear').style.display = 'none';
    document.getElementById('formCrear').reset();
}

// Función para editar mesa
function editarMesa(mesa) {
    document.getElementById('mesa_id').value = mesa.id;
    document.getElementById('mesa_nombre').value = mesa.nombre;
    document.getElementById('mesa_sala').value = mesa.id_sala;
    document.getElementById('mesa_sillas').value = mesa.sillas;
    document.getElementById('mesa_pos_top').value = mesa.posicion_top || '50%';
    document.getElementById('mesa_pos_left').value = mesa.posicion_left || '50%';

    // Mostrar modal
    document.getElementById('modalEditar').style.display = 'flex';
}

// Función para cerrar modal de editar
function cerrarModalEditar() {
    document.getElementById('modalEditar').style.display = 'none';
    document.getElementById('formEditar').reset();
}

// Función para eliminar mesa
function eliminarMesa(id, nombre, estado) {
    // estado: 1=libre, 2=ocupada, 3=reservada
    if (estado == 2) {
        alert(`No se puede eliminar la mesa "${nombre}" porque está ocupada actualmente.`);
        return;
    }

    if (confirm(`¿Estás seguro de que deseas eliminar la mesa "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
        window.location.href = `../PROCEDIMIENTOS/procesar_eliminar_mesa.php?id=${id}`;
    }
}

// Cerrar modal al hacer clic fuera
window.onclick = function (event) {
    const modalCrear = document.getElementById('modalCrear');
    const modalEditar = document.getElementById('modalEditar');

    if (event.target === modalCrear) {
        cerrarModalCrear();
    }
    if (event.target === modalEditar) {
        cerrarModalEditar();
    }
}

// Cerrar modal con tecla ESC
document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        cerrarModalCrear();
        cerrarModalEditar();
    }
});

// Validación de formato de posición en tiempo real
document.addEventListener('DOMContentLoaded', function () {
    const posInputs = document.querySelectorAll('[id$="_pos_top"], [id$="_pos_left"]');

    posInputs.forEach(input => {
        input.addEventListener('blur', function () {
            const value = this.value.trim();
            if (value && !value.match(/^\d+(%|px)$/)) {
                this.style.borderColor = '#e74c3c';
                this.setCustomValidity('Formato inválido. Usa % o px (ej: 15%, 200px)');
            } else {
                this.style.borderColor = '';
                this.setCustomValidity('');
            }
        });
    });
});
