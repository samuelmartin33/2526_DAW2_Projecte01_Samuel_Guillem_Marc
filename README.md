Casa GMS - Sistema de Gestión de Mesas

Proyecto de aplicación web para la gestión en tiempo real de las mesas de un restaurante. Permite a los camareros y administradores controlar la ocupación de las salas, asignar y liberar mesas de forma visual e intuitiva.

🚀 Características Principales

Autenticación por Roles: Inicio de sesión diferenciado para Camareros (rol 1) y Administradores (rol 2).

Panel Principal (Dashboard): Vista general con estadísticas en tiempo real (mesas libres, ocupadas, sillas totales) y una cuadrícula de todas las salas con su porcentaje de ocupación.

Gestión Visual de Salas: Múltiples salas (Terrazas, Comedores, Privados) con un layout gráfico que muestra el estado de cada mesa (Verde para Libre, Gris/Rojo para Ocupada).

Asignación de Mesas: Al hacer clic en una mesa libre, se solicita el número de comensales. El sistema valida la capacidad antes de asignar.

Liberación de Mesas: Al hacer clic en una mesa ocupada, se muestra quién la asignó y a qué hora, permitiendo su liberación.

Control de Permisos: Un camarero no puede liberar una mesa asignada por otro compañero. Los Administradores (rol 2) pueden liberar cualquier mesa.

Histórico y Estadísticas: Un panel (historico.php) que muestra KPIs (métricas clave), gráficos de rendimiento (horas pico, camareros más activos, salas más usadas) y un registro histórico filtrable de todas las ocupaciones.

Notificaciones Modernas: Uso de SweetAlert2 para todas las validaciones, confirmaciones y notificaciones de éxito, mejorando la experiencia de usuario.

🛠️ Tecnologías Utilizadas

Backend: PHP 8+ (Scripting del lado del servidor, manejo de sesiones)

Base de Datos: MySQL (Gestión de datos con PDO para conexiones seguras)

Frontend: HTML5, CSS3 (con Flexbox y Grid para layouts)

JavaScript (ES6+): Manipulación del DOM, validación de formularios y gestión de eventos asíncronos.

Librerías: SweetAlert2 (Para todas las notificaciones y popups)

🏁 Puesta en Marcha

Para ejecutar este proyecto en un entorno local (como XAMPP, WAMP, etc.), sigue estos pasos:

Base de Datos: Importa el archivo BBDD/BBDD.sql en tu gestor de MySQL (por ejemplo, phpMyAdmin). Esto creará la estructura de tablas y algunos datos de prueba.

Archivos: Copia la carpeta completa del proyecto (ej. restaurante/) en el directorio de tu servidor web (ej. C:/xampp/htdocs/).

Configuración de Conexión (¡CRÍTICO!):

Abre el archivo PHP/CONEXION/conexion.php.

Modifica las variables de conexión ($servername, $username_db, $password_db, $dbname) para que coincidan con tu configuración local de MySQL.

¡MUY IMPORTANTE! Modifica la constante BASE_URL. Debe apuntar a la URL raíz absoluta de tu proyecto.

Ejemplo: Si accedes al proyecto desde http://localhost/restaurante/, la línea debe ser:

define('BASE_URL', 'http://localhost/restaurante/');


Acceder: Inicia tus servicios de Apache y MySQL. Abre tu navegador y ve a la página de login:
http://localhost/restaurante/PHP/PUBLIC/login.php (o la URL que corresponda a tu BASE_URL).

📖 Funcionamiento Detallado

La aplicación sigue un flujo lógico centrado en la gestión de mesas.

1. Flujo de Autenticación

Login (login.php): El usuario accede y ve un formulario. El desplegable "Selecciona tu usuario" se rellena dinámicamente con los camareros (rol = 1) activos de la base de datos (users).

Proceso (procesar_login.php): El backend recibe el username y password. Compara el hash de la contraseña de la BBDD con la contraseña introducida. Si es correcta, inicia una sesión (session_start()) y guarda el id_usuario, username, nombre y rol en la variable $_SESSION.

Redirección: El usuario es redirigido al panel principal (index.php).

2. Panel Principal (index.php)

Muestra un header.php unificado que saluda al usuario por su nombre.

Presenta estadísticas rápidas (KPIs) sobre la ocupación actual.

Muestra una cuadrícula con todas las salas disponibles, indicando su porcentaje de ocupación y permitiendo hacer clic para entrar a cada una.

3. Gestión de Salas (Ej. terraza1.php)

Muestra un layout visual de la sala.

Cada mesa es un <button> dentro de un <form>.

El estado (libre/ocupada) se aplica con clases CSS (.libre, .ocupada) según los datos de la BBDD.

Si la mesa está libre (estado 1): El formulario apunta a asignar_mesa.php.

Si la mesa está ocupada (estado 2): El formulario apunta a liberar_mesa.php.

4. Proceso: Asignar Mesa (asignar_mesa.php)

Este es un proceso con validación dual (dos scripts JS trabajando juntos):

Carga de Página: Se muestra un formulario pidiendo el número de comensales. El max del input se define por la capacidad de la mesa.

Validación en Tiempo Real (validar_asignacion.js):

Este script se activa mientras el usuario escribe (onmouseleave).

Comprueba si el campo está vacío, no es un número, es menor que 1 o supera la capacidad (maxSillas).

Si hay un error, crea y muestra un <div> de error rojo debajo del formulario.

Si el error se corrige, oculta el <div>.

Envío y Confirmación (alert_asignar.js):

Este script se activa al hacer clic en el botón "Asignar Mesa".

Previene el envío (e.preventDefault()).

Comprueba si la validación del otro script (validar_asignacion.js) es correcta (básicamente, si el <div> de error está oculto).

Si la validación es correcta, lanza un SweetAlert de confirmación ("¿Estás seguro?").

Si el usuario pulsa "Sí, asignar", el script envía el formulario (form.submit()).

Si el usuario pulsa "Cancelar", muestra una alerta de "Cancelado".

5. Proceso: Liberar Mesa (liberar_mesa.php)

Este proceso también tiene validación dual para gestionar los permisos:

Carga de Página: Muestra información de la mesa (quién la asignó, a qué hora).

Validación de Permisos (liberar_mesa.js):

Este script se ejecuta al cargar la página (window.onload).

Compara el ID del camarero que asignó la mesa (leído del input oculto id="camarero") con el ID del camarero actual (leído de id="camarero_sesion").

¡IMPORTANTE! Si los IDs no coinciden, este script oculta el botón "Sí, liberar" (botonAsignar.style.display = "none").

Nota: Esta validación no contempla a los Admins (rol 2), pero la lógica del backend sí lo hace.

Envío y Notificación (alert_liberar.js):

Este script se activa al hacer clic en "Sí, liberar".

Previene el envío (e.preventDefault()).

Comprueba si liberar_mesa.js ha ocultado el botón (o si el usuario es Admin).

Si el botón está visible (o es Admin), muestra un toast de éxito ("¡Mesa liberada!").

Espera a que el toast se cierre (1.5 segundos) y entonces envía el formulario (form.submit()). Esto evita que la página se recargue antes de que el usuario vea el mensaje.

Seguridad del Backend: Aunque el JS oculte el botón, la validación final ocurre en el PHP (liberar_mesa.php), que comprueba si $_SESSION['rol'] == 2 o si los IDs coinciden antes de ejecutar la consulta UPDATE en la base de datos.

6. Histórico (historico.php)

Página de solo lectura accesible desde el header.

Realiza múltiples consultas SQL para obtener:

KPIs generales (total ocupaciones, comensales).

Top 5 camareros.

Top 5 salas.

Ocupaciones por hora del día.

Ocupaciones por día de la semana.

Muestra los datos en tarjetas de métricas y gráficos de barras (hechos con HTML/CSS).

Incluye un formulario de filtros que permite buscar en el historial de la tabla ocupaciones.

📂 Estructura del Proyecto (Simplificada)

restaurante/
│
├── BBDD/
│   └── BBDD.sql
│
├── css/
│   ├── login.css
│   ├── panel_principal.css
│   ├── salas_general.css
│   ├── historico.css
│   └── ... (CSS de cada sala)
│
├── img/
│   └── ... (Todas las imágenes)
│
└── PHP/
    │
    ├── CONEXION/
    │   └── conexion.php      (Configuración de BBDD y BASE_URL)
    │
    ├── PROCEDIMIENTOS/
    │   ├── procesar_login.php  (Backend de login)
    │   ├── asignar_mesa.php    (Backend de asignación)
    │   ├── liberar_mesa.php    (Backend de liberación)
    │   └── logout.php
    │
    └── PUBLIC/
        ├── JS/
        │   ├── validar_asignacion.js (Validación en vivo para asignar)
        │   ├── alert_asignar.js      (Confirmación SweetAlert para asignar)
        │   ├── liberar_mesa.js       (Validación de permisos para liberar)
        │   ├── alert_liberar.js      (Notificación SweetAlert para liberar)
        │   └── ...
        │
        ├── SALAS/
        │   ├── terraza1.php
        │   ├── comedor1.php
        │   └── ... (Todas las páginas de salas)
        │
        ├── header.php          (Cabecera unificada)
        ├── login.php           (Página de inicio de sesión)
        ├── index.php           (Panel principal / Dashboard)
        └── historico.php       (Página de estadísticas)
