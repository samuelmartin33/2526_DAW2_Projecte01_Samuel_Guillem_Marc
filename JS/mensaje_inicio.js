// Espera a que todo el contenido de la página (HTML) esté cargado
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Lee los datos del <body>
    const body = document.body;
    const showWelcome = body.dataset.showWelcome; // Obtiene el valor de "data-show-welcome"
    const welcomeName = body.dataset.welcomeName; // Obtiene el valor de "data-welcome-name"

    // 2. Comprueba la "bandera"
    if (showWelcome === "true") {
        
        // 3. ¡Muestra la alerta!
        Swal.fire({
            title: '¡Bienvenido, ' + welcomeName + '!', // Usa la variable que leímos del HTML
            text: 'Has iniciado sesión correctamente.',
            icon: 'success',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
    }
});