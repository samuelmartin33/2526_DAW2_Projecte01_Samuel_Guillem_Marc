window.onload = function() {
  const form = document.querySelector(".form-full-page");
  const boton = form.querySelector(".btn-primary");
  const inputComensales = form.querySelector("input[name='num_comensales']"); // cambia el name si es diferente

  boton.addEventListener("click", function (e) {
    e.preventDefault(); // Evita el envío automático

    const numComensales = inputComensales && inputComensales.value
      ? inputComensales.value
      : "los comensales indicados";

    const swalWithBootstrapButtons = Swal.mixin({
      customClass: {
        confirmButton: "btn-confirmar",
        cancelButton: "btn-cancelar"
      },
      buttonsStyling: false
    });

    swalWithBootstrapButtons.fire({
      title: "¿Confirmar asignación?",
      text: `¿Deseas asignar esta mesa para ${numComensales} comensales?`,
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Sí, asignar",
      cancelButtonText: "Cancelar",
      reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
          // Mostramos el popup de éxito
          swalWithBootstrapButtons.fire({
            title: "¡Éxito!",
            text: "La mesa ha sido actualizada correctamente.",
            icon: "success",
            confirmButtonText: "Aceptar"
          }).then(() => {
            form.submit(); // Ahora sí enviamos el formulario
          });
        } else
      if (result.isConfirmed) {
        form.submit();
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        swalWithBootstrapButtons.fire({
          title: "Cancelado",
          text: "No se ha asignado la mesa.",
          icon: "error"
        });
      }
    });
  });
};
 
