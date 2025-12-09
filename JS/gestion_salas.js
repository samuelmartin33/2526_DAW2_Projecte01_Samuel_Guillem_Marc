// =============================================
//   GESTIÓN DE SALAS - JavaScript
// =============================================

// Función para abrir modal de creación
function abrirModalCrear() {
    document.getElementById('formCrear').reset();
    document.getElementById('preview_fondo_nuevo').innerHTML = '';
    document.getElementById('preview_mesa_nuevo').innerHTML = '';
    document.getElementById('modalCrear').style.display = 'flex';
}

// Función para cerrar modal de crear
function cerrarModalCrear() {
    document.getElementById('modalCrear').style.display = 'none';
    document.getElementById('formCrear').reset();
}

// Función para editar sala
function editarSala(sala) {
    document.getElementById('sala_id').value = sala.id;
    document.getElementById('sala_nombre').value = sala.nombre;
    document.getElementById('sala_imagen_fondo_actual').value = sala.imagen_fondo || '';
    document.getElementById('sala_imagen_mesa_actual').value = sala.imagen_mesa || '';

    // Mostrar imágenes actuales
    const currentFondo = document.getElementById('current_fondo');
    const currentMesa = document.getElementById('current_mesa');

    if (sala.imagen_fondo) {
        currentFondo.innerHTML = `
            <div class="current-image-preview">
                <p><strong>Imagen actual:</strong></p>
                <img src="../../img/salas/fondos/${sala.imagen_fondo}" alt="Fondo actual">
            </div>
        `;
    } else {
        currentFondo.innerHTML = '<p class="sin-imagen">Sin imagen de fondo</p>';
    }

    if (sala.imagen_mesa) {
        currentMesa.innerHTML = `
            <div class="current-image-preview">
                <p><strong>Imagen actual:</strong></p>
                <img src="../../img/salas/mesas/${sala.imagen_mesa}" alt="Mesa actual">
            </div>
        `;
    } else {
        currentMesa.innerHTML = '<p class="sin-imagen">Sin imagen de mesa</p>';
    }

    // Limpiar previews de nuevas imágenes
    document.getElementById('preview_fondo_edit').innerHTML = '';
    document.getElementById('preview_mesa_edit').innerHTML = '';

    // Mostrar modal
    document.getElementById('modalEditar').style.display = 'flex';
}

// Función para cerrar modal de editar
function cerrarModalEditar() {
    document.getElementById('modalEditar').style.display = 'none';
    document.getElementById('formEditar').reset();
}

// Función para eliminar sala
function eliminarSala(id, nombre, mesas) {
    if (mesas > 0) {
        alert(`No se puede eliminar la sala "${nombre}" porque tiene ${mesas} mesa(s) asignada(s).\n\nPrimero debes eliminar o reasignar las mesas.`);
        return;
    }

    if (confirm(`¿Estás seguro de que deseas eliminar la sala "${nombre}"?\n\nEsta acción eliminará también las imágenes asociadas.`)) {
        window.location.href = `../PROCEDIMIENTOS/procesar_eliminar_sala.php?id=${id}`;
    }
}

// Función para previsualizar imagen antes de subir
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    preview.innerHTML = '';

    if (input.files && input.files[0]) {
        const file = input.files[0];

        // Validar tamaño (5MB máximo)
        if (file.size > 5 * 1024 * 1024) {
            preview.innerHTML = '<p style="color: #e74c3c;">Archivo demasiado grande. Máximo 5MB.</p>';
            input.value = '';
            return;
        }

        // Validar tipo
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
            preview.innerHTML = '<p style="color: #e74c3c;">Formato no permitido. Solo JPG, JPEG o PNG.</p>';
            input.value = '';
            return;
        }

        // Crear preview
        const reader = new FileReader();
        reader.onload = function (e) {
            preview.innerHTML = `
                <div class="preview-container">
                    <p><strong>Vista previa:</strong></p>
                    <img src="${e.target.result}" alt="Preview">
                    <p class="file-info">${file.name} (${(file.size / 1024).toFixed(2)} KB)</p>
                </div>
            `;
        };
        reader.readAsDataURL(file);
    }
}

// Función para ampliar imagen
function ampliarImagen(src) {
    document.getElementById('imagenAmpliada').src = src;
    document.getElementById('modalImagen').style.display = 'flex';
}

// Función para cerrar modal de imagen ampliada
function cerrarModalImagen() {
    document.getElementById('modalImagen').style.display = 'none';
}

// Cerrar modal al hacer clic fuera
window.onclick = function (event) {
    const modalCrear = document.getElementById('modalCrear');
    const modalEditar = document.getElementById('modalEditar');
    const modalImagen = document.getElementById('modalImagen');

    if (event.target === modalCrear) {
        cerrarModalCrear();
    }
    if (event.target === modalEditar) {
        cerrarModalEditar();
    }
    if (event.target === modalImagen) {
        cerrarModalImagen();
    }
}

// Cerrar modal con tecla ESC
document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        cerrarModalCrear();
        cerrarModalEditar();
        cerrarModalImagen();
    }
});
