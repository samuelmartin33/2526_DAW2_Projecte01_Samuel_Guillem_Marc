window.onload = function () {
    // al cargar la pagina guardamos los elementos del formulario con su id
    const form = document.getElementById("loginForm");
    const username = document.getElementById("username");
    const password = document.getElementById("password");
    // Crear o reutilizar el div de error
    let errorDiv = document.querySelector(".error");
    if (!errorDiv) {
        errorDiv = document.createElement("div");
        errorDiv.className = "error";
        form.appendChild(errorDiv);
    }

    // Ocultar inicialmente
    errorDiv.style.display = "none";
    // cuando se ejecute la funcion mostrarError se mostrara el mensaje de error
    function mostrarError(mensaje) {
        errorDiv.innerHTML = mensaje;
        errorDiv.style.display = "block";
    }
    // cuando se ejecute la funcion limpiarError se limpiara el mensaje de error
    function limpiarError() {
        errorDiv.innerHTML = "";
        errorDiv.style.display = "none";
    }
    // funcion para validar los campos del formulario
    function validarCampos() {
        limpiarError();
        // si el user esta vacio se mostrara el mensaje de error
        if (!username.value) {
            mostrarError("Selecciona un usuario.");
            return false;
        }
        // si la password esta vacia se mostrara el mensaje de error
        if (!password.value) {
            mostrarError("Introduce tu contraseña.");
            return false;
        }
        // si la password tiene menos de 6 caracteres se mostrara el mensaje de error
        if (password.value.length < 6) {
            mostrarError("La contraseña debe tener al menos 6 caracteres.");
            return false;
        }

        return true;
    }

    // Validación al salir del campo, al hacer click fuera del campo
    username.onblur = validarCampos;
    password.onblur = validarCampos;

    // Validación al enviar
    // se ejecutara la funcion validarCampos al enviar el formulario, se pone la e para evitar que se envie el formulario si no se cumplen las condiciones
    form.onsubmit = function (e) {
        if (!validarCampos()) {
            // e.preventDefault() evita que se envie el formulario porque no se cumplen las condiciones de !validarCampos()
            e.preventDefault();
        }
    };
};
