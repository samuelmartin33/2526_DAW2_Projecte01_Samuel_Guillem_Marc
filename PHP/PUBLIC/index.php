<?php
session_start();

// Comprobar si el usuario está logueado correctamente
if (isset($_SESSION['loginok']) && $_SESSION['loginok'] === true && isset($_SESSION['username'])) {
    $nombre = htmlspecialchars($_SESSION['nombre']);
    $username = htmlspecialchars($_SESSION['username']);
} else {
    header("Location: ../PUBLIC/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio</title>
    <link rel="stylesheet" href="../../CSS/login.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        /* Barra de navegación */
        nav {
            background-color: #2c3e50;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 30px;
        }

        nav .logo {
            font-weight: bold;
            font-size: 20px;
        }

        nav .menu {
            display: flex;
            gap: 20px;
        }

        nav .menu a {
            color: white;
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 5px;
            transition: background-color 0.2s;
        }

        nav .menu a:hover {
            background-color: #34495e;
        }

        nav form {
            margin: 0;
        }

        nav button {
            background-color: #e74c3c;
            border: none;
            color: white;
            padding: 8px 14px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        nav button:hover {
            background-color: #c0392b;
        }

        .wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-top: 80px;
        }

        .login-container {
            width: 400px;
            background-color: #ecf0f1;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.2);
        }

        .login-container h2 {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <!-- 🔹 Barra de navegación -->
    <nav>
        <div class="logo">🍽️ Restaurante</div>

        <div class="menu">
            <a href="./consultar_mesas.php">Consultar Mesas</a>
            <a href="./consultar_estadisticas.php">Consultar Estadísticas</a>
        </div>

        <form method="post" action="../PROCEDIMIENTOS/logout.php">
            <button type="submit">Cerrar sesión</button>
        </form>
    </nav>

    <!-- 🔹 Contenido principal -->
    <div class="wrapper">
        <div class="login-container">
            <h2>Bienvenido 👋</h2>
            <p><strong>Nombre:</strong> <?= $nombre ?></p>
            <p><strong>Usuario:</strong> <?= $username ?></p>
        </div>
    </div>
</body>
</html>
