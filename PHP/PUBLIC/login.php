<?php
session_start();

// Si el usuario ya está autenticado, redirige a index
if (isset($_SESSION["id_usuario"])) {
    header('Location: ./index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login</title>
    <!-- Ruta absoluta hacia CSS -->
    <link rel="stylesheet" href="../../CSS/login.css">
</head>
<body>
    <div class="wrapper">
        <div class="imagen">
            <!-- Ruta absoluta hacia IMG -->
            <img src="./IMG/logo_cole.png" alt="Logo">
        </div>

        <div class="login-container">
            <h2>Iniciar sesión</h2>

            <?php if (isset($_GET['error'])): ?>
                <div class="error" style="color: #c0392b; font-size: 14px; text-align:center; margin-bottom: 15px;">
                    <?php
                    switch ($_GET['error']) {
                        case 'campos_vacios':
                            echo 'Por favor, completa todos los campos.';
                            break;
                        case 'credenciales_invalidas':
                            echo 'Usuario o contraseña incorrectos.';
                            break;
                        case 'usuario_corto':
                            echo 'El nombre de usuario es demasiado corto (mín. 3 caracteres).';
                            break;
                        case 'password_corto':
                            echo 'La contraseña es demasiado corta (mín. 6 caracteres).';
                            break;
                        case 'error_bd':
                            echo 'Error de servidor. Intenta más tarde.';
                            break;
                        default:
                            echo 'Error en el inicio de sesión.';
                            break;
                    }
                    ?>
                </div>
            <?php endif; ?>

            <!-- Ruta absoluta al PHP que procesa el login -->
            <form id="loginForm" method="post" action="../PROCEDIMIENTOS/procesar_login.php" novalidate>
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" required><br>

                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required><br>

                <button type="submit">Iniciar sesión</button>
            </form>
        </div>
    </div>

    <!-- JS absoluto -->
    <script src="./JS/validacion_login.js"></script>
</body>
</html>
