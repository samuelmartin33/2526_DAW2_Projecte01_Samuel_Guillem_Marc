# 🌟 CASA GMS – Sistema de Gestión de Mesas

¿Buscas optimizar la gestión de mesas y disponibilidad en tu restaurante? **Casa GMS** es la solución.

Este proyecto es un sistema web desarrollado en PHP y MySQL, diseñado para ser la herramienta definitiva que permite a camareros y administradores controlar la ocupación del restaurante en tiempo real, gestionar asignaciones y analizar el rendimiento histórico.

<br>

## 🚀 Características Principales

* 📊 **Panel Principal (Dashboard):** Visión global de la ocupación del restaurante en tiempo real. Estadísticas clave de un vistazo: mesas libres, ocupadas, y porcentaje de ocupación por sala.
* 🗺️ **Gestión Visual de Salas:** Representación gráfica de las diferentes salas (Comedor 1, Privada 1, Terraza, etc.). Las mesas cambian de color dinámicamente (libre/ocupada) para un control visual instantáneo.
* 🔐 **Autenticación y Roles:** Página de login segura (`login.php`). El sistema está preparado para gestionar roles (Camareros y Administradores), con permisos diferenciados.
* ✅ **Asignación y Liberación de Mesas:** Un flujo de trabajo intuitivo para asignar y liberar mesas. El sistema registra qué camarero realiza cada acción y la hora, fundamental para el análisis posterior.
* 📈 **Página de Histórico y Estadísticas:** Un potente módulo de analítica (`historico.php`) para tomar decisiones. Descubre KPIs, Top 5 de camareros, salas más rentables y horas punta.
* 🛡️ **Validaciones Robustas:** Seguridad en cada paso. Se implementan validaciones tanto en el lado del cliente (JavaScript) como en el servidor (PHP) para garantizar la integridad de los datos en todos los formularios.
* 🎨 **Estilos CSS Personalizados:** Una interfaz de usuario limpia y moderna con CSS dedicado para cada vista (login, dashboard, salas, histórico), asegurando una experiencia de usuario agradable.
* 🔮 **Base de Datos Escalable:** Una estructura de base de datos MySQL (`BBDD.sql`) diseñada para crecer, lista para incorporar futuras funcionalidades como un sistema de reservas, gestión de menús o un panel de administración avanzado.

<br>

## 🛠️ Tecnologías Utilizadas

* **Backend:** PHP 8+ (Orientado a objetos y procedural)
* **Base de Datos:** MySQL con PDO (Consultas preparadas para evitar inyección SQL)
* **Frontend:** HTML5, CSS3 (Flexbox, Grid)
* **Javascript (ES6+):** Validación de formularios en tiempo real y lógica de UI.
* **Librerías:** SweetAlert2 (Para notificaciones y confirmaciones modernas)

<br>

## 🏁 Puesta en Marcha del Proyecto

Sigue estos pasos para ejecutar el proyecto en tu entorno local:

1.  **Clonar el Repositorio**
    ```bash
    git clone [https://github.com/tu-usuario/casa-gms.git](https://github.com/tu-usuario/casa-gms.git)
    ```

2.  **Importar la Base de Datos**
    * Localiza el archivo `BBDD/BBDD.sql`.
    * Importa el archivo en tu gestor de MySQL (phpMyAdmin, Workbench, DBeaver, etc.) para crear la estructura de tablas y los datos iniciales.

3.  **Configurar la Conexión**
    * Navega al archivo de conexión, probablemente ubicado en `PHP/CONEXION/conexion.php`.
    * Edita las variables con tus credenciales de la base de datos:
        * `$servidor` (ej. "localhost")
        * `$usuario` (ej. "root")
        * `$contrasena` (ej. "")
        * `$base_datos` (ej. "restaurante_gms")

4.  **Configurar la URL Base (¡Crítico!)**
    * En el mismo archivo `conexion.php` o en un archivo de configuración principal, asegúrate de definir la `BASE_URL`. Esto es esencial para que las rutas y redirecciones funcionen correctamente.
    * ```php
        DEFINE('BASE_URL', 'http://localhost/tu-carpeta-proyecto/');
        ```

5.  **Iniciar el Servidor**
    * Asegúrate de que tu servidor local (XAMPP, WAMP, MAMP) esté ejecutando Apache y MySQL.
    * Abre tu navegador y accede a la `BASE_URL` que configuraste.

6.  **Acceder al Sistema**
    * Serás redirigido a la página de login: `(BASE_URL)/PHP/PUBLIC/login.php`
    * Utiliza las credenciales de un usuario (camarero) incluidas en la base de datos para acceder.

<br>

## 🗺️ Roadmap (Próximas Funcionalidades)

Este proyecto está en desarrollo activo. Las siguientes características están planificadas:

* [ ] **Panel de Administración:** Una sección protegida para que los administradores puedan gestionar usuarios, salas y mesas.
* [ ] **Sistema de Reservas:** Permitir a los clientes o al personal crear reservas para una fecha y hora específicas.
* [ ] **Gestión de Menús:** Asociar comandas a las mesas.
