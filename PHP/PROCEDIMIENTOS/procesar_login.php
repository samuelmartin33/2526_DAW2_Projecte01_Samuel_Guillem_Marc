<?php
session_start();

// Conexión con ruta relativa real (sube un nivel y entra a CONEXION)
require_once __DIR__ . '/../CONEXION/conexion.php';

// Recibe usuario y contraseña por POST
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validar campos vacíos
if ($username === '' || $password === '') {
    header('Location: ../PUBLIC/login.php?error=campos_vacios');
    exit;
}

// Validaciones básicas servidor-side
if (mb_strlen($username) < 3) {
    header('Location: ../PUBLIC/login.php?error=usuario_corto');
    exit;
}

if (mb_strlen($password) < 6) {
    header('Location: ../PUBLIC/login.php?error=password_corto');
    exit;
}

try {
    // Buscar usuario en la tabla `users`
    $stmt = $conn->prepare('SELECT id, username, nombre, apellido, email, password_hash FROM users WHERE username = :username LIMIT 1');
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        header('Location: ../PUBLIC/login.php?error=credenciales_invalidas');
        exit;
    }

    // Login correcto: guardar datos en sesión
    $_SESSION['id_usuario'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['nombre'] = $user['nombre'];
    $_SESSION['loginok'] = true;

    header('Location: ../PUBLIC/index.php');
    exit;

} catch (PDOException $e) {
    // Error en la base de datos
    header('Location: ../PUBLIC/login.php?error=error_bd');
    exit;
}
?>
