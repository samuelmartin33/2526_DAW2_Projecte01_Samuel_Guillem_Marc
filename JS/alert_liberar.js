// alert_liberar.js

window.onload = function () {

    // ================================
    // Elementos del DOM
    // ================================
    const form = document.getElementById("liberar-mesa-form");
    const inputcamarero = document.getElementById("camarero");
    const inputcamareroSesion = document.getElementById("camarero_sesion");
    const botonLiberar = document.getElementById("btn-liberar");

    // ================================
    // Validación para mostrar / ocultar botón
    // ================================
    function validarCamarero() {
        if (inputcamarero.value !== inputcamareroSesion.value) {
            botonLiberar.style.display = "none";
            return false;
        }
        return true;
    }

    validarCamarero();


    // ================================
    // SweetAlert en lugar de submit directo
    // ================================
    if (botonLiberar) {
        botonLiberar.addEventListener("click", function (e) {
            e.preventDefault(); // evitar envío inmediato

            Swal.fire({
                title: "¿Liberar mesa?",
                text: "Esta acción liberará la mesa. ¿Deseas continuar?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, liberar",
                cancelButtonText: "Cancelar",
            }).then((result) => {
                if (result.isConfirmed) {
                    // Confirmado → enviar formulario real
                    form.submit();
                }
            });
        });
    }
};
