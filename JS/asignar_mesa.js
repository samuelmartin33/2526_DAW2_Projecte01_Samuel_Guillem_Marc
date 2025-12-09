// =============================================
//   ASIGNAR MESA - SweetAlert Process
// =============================================

function asignarMesa(mesaId, mesaNombre, salaId) {
    Swal.fire({
        title: '¿Asignar mesa?',
        html: `
            <p>¿Deseas asignarte la mesa <strong>${mesaNombre}</strong>?</p>
            <p style="color: #666; font-size: 0.9rem; margin-top: 10px;">
                <i class="fa-solid fa-info-circle"></i> La mesa quedará marcada como ocupada.
            </p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Sí, asignar',
        cancelButtonText: '<i class="fa-solid fa-times"></i> Cancelar',
        confirmButtonColor: '#27ae60',
        cancelButtonColor: '#6c757d',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Crear formulario y enviarlo
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '../PROCEDIMIENTOS/asignar_mesa.php';

            const inputMesa = document.createElement('input');
            inputMesa.type = 'hidden';
            inputMesa.name = 'mesa_id';
            inputMesa.value = mesaId;

            form.appendChild(inputMesa);
            document.body.appendChild(form);

            // Mostrar loading
            Swal.fire({
                title: 'Asignando mesa...',
                html: '<i class="fa-solid fa-spinner fa-spin"></i> Por favor espera',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    form.submit();
                }
            });
        }
    });
}
