// =============================================
//   GESTIÓN DE USUARIOS - SweetAlert2
// =============================================

// Función para abrir modal de edición
function editarUsuario(usuario) {
    document.getElementById('modalTitulo').textContent = 'Editar Usuario';
    document.getElementById('formUsuario').action = '../PROCEDIMIENTOS/procesar_editar_usuario.php';

    // Llenar los campos
    document.getElementById('usuario_id').value = usuario.id;
    document.getElementById('usuario_username').value = usuario.username;
    document.getElementById('usuario_nombre').value = usuario.nombre;
    document.getElementById('usuario_apellido').value = usuario.apellido || '';
    document.getElementById('usuario_email').value = usuario.email;
    document.getElementById('usuario_rol').value = usuario.rol;
    document.getElementById('usuario_password').value = '';

    // Configurar modo edición
    document.getElementById('usuario_username').removeAttribute('readonly');
    document.getElementById('usuario_nombre').removeAttribute('readonly');
    document.getElementById('usuario_apellido').removeAttribute('readonly');
    document.getElementById('usuario_email').removeAttribute('readonly');
    document.getElementById('usuario_rol').removeAttribute('disabled');

    // Mostrar modal
    document.getElementById('modalUsuario').style.display = 'flex';
}

// Función para abrir modal de creación
function abrirModalCrear() {
    document.getElementById('formCrear').reset();
    document.getElementById('modalCrear').style.display = 'flex';
}

// Función para cerrar modal de editar
function cerrarModal() {
    document.getElementById('modalUsuario').style.display = 'none';
    document.getElementById('formUsuario').reset();
}

// Función para cerrar modal de crear
function cerrarModalCrear() {
    document.getElementById('modalCrear').style.display = 'none';
    document.getElementById('formCrear').reset();
}

// =================== SWEETALERT2 FUNCTIONS ===================

// Función para desactivar usuario (soft delete)
function eliminarUsuario(id, username) {
    Swal.fire({
        title: '¿Desactivar usuario?',
        html: `
            <p>¿Estás seguro de que deseas desactivar al usuario <strong>"${username}"</strong>?</p>
            <p style="color: #666; font-size: 0.9rem; margin-top: 10px;">
                <i class="fa-solid fa-info-circle"></i> El usuario será marcado como inactivo pero no se eliminará.
            </p>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-user-slash"></i> Sí, desactivar',
        cancelButtonText: '<i class="fa-solid fa-times"></i> Cancelar',
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#6c757d',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Desactivando usuario...',
                html: '<i class="fa-solid fa-spinner fa-spin"></i> Por favor espera',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    window.location.href = `../PROCEDIMIENTOS/procesar_eliminar_usuario.php?id=${id}`;
                }
            });
        }
    });
}

// Función para reactivar usuario
function reactivarUsuario(id, username) {
    Swal.fire({
        title: '¿Reactivar usuario?',
        html: `
            <p>¿Deseas reactivar al usuario <strong>"${username}"</strong>?</p>
            <p style="color: #666; font-size: 0.9rem; margin-top: 10px;">
                <i class="fa-solid fa-info-circle"></i> El usuario volverá a estar activo en el sistema.
            </p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-user-check"></i> Sí, reactivar',
        cancelButtonText: '<i class="fa-solid fa-times"></i> Cancelar',
        confirmButtonColor: '#27ae60',
        cancelButtonColor: '#6c757d',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Reactivando usuario...',
                html: '<i class="fa-solid fa-spinner fa-spin"></i> Por favor espera',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    window.location.href = `../PROCEDIMIENTOS/procesar_reactivar_usuario.php?id=${id}`;
                }
            });
        }
    });
}

// Función para eliminar usuario permanentemente
function eliminarPermanente(id, username) {
    Swal.fire({
        title: '⚠️ ELIMINAR PERMANENTEMENTE',
        html: `
            <p>¿Estás seguro de que deseas <strong style="color: #e74c3c;">ELIMINAR PERMANENTEMENTE</strong> al usuario <strong>"${username}"</strong>?</p>
            <div style="background-color: #fff3cd; border-left: 4px solid #ff9800; padding: 10px; margin: 15px 0; text-align: left;">
                <strong><i class="fa-solid fa-exclamation-triangle"></i> ADVERTENCIA:</strong>
                <ul style="margin: 5px 0 0 20px; font-size: 0.9rem;">
                    <li>Esta acción NO SE PUEDE DESHACER</li>
                    <li>Se eliminarán todos los registros relacionados</li>
                    <li>El usuario desaparecerá completamente del sistema</li>
                </ul>
            </div>
        `,
        icon: 'error',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-trash-alt"></i> Sí, eliminar permanentemente',
        cancelButtonText: '<i class="fa-solid fa-times"></i> Cancelar',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Segunda confirmación
            Swal.fire({
                title: 'Confirmación Final',
                html: `
                    <p><strong>¿REALMENTE deseas eliminar a "${username}" de forma permanente?</strong></p>
                    <p style="color: #e74c3c; font-weight: bold; margin-top: 15px;">
                        Esta es tu última oportunidad para cancelar.
                    </p>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="fa-solid fa-trash"></i> SÍ, ELIMINAR AHORA',
                cancelButtonText: '<i class="fa-solid fa-ban"></i> NO, Cancelar',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#27ae60',
                reverseButtons: true
            }).then((finalResult) => {
                if (finalResult.isConfirmed) {
                    // Mostrar loading
                    Swal.fire({
                        title: 'Eliminando usuario...',
                        html: '<i class="fa-solid fa-spinner fa-spin"></i> Por favor espera',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            window.location.href = `../PROCEDIMIENTOS/procesar_eliminar_permanente.php?id=${id}`;
                        }
                    });
                }
            });
        }
    });
}

// Cerrar modal al hacer clic fuera
window.onclick = function (event) {
    const modalUsuario = document.getElementById('modalUsuario');
    const modalCrear = document.getElementById('modalCrear');
    if (event.target === modalUsuario) {
        cerrarModal();
    }
    if (event.target === modalCrear) {
        cerrarModalCrear();
    }
}

// Cerrar modal con tecla ESC
document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        cerrarModal();
        cerrarModalCrear();
    }
});
