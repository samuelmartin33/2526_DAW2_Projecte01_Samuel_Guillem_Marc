<?php
session_start();
require_once '../CONEXION/conexion.php';

// --- VERIFICAR SESIÓN Y ROL ADMIN ---
if (!isset($_SESSION['loginok']) || $_SESSION['loginok'] !== true || ($_SESSION['rol'] ?? 1) != 2) {
    header("Location: ../PUBLIC/index.php");
    exit();
}

// --- VALIDAR MÉTODO POST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../PUBLIC/gestion_usuarios.php?error=invalid_method");
    exit();
}

// --- OBTENER Y VALIDAR DATOS DEL FORMULARIO ---
$username = trim($_POST['username'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$rol = intval($_POST['rol'] ?? 1);

// Validaciones básicas
if (empty($username) || empty($nombre) || empty($email) || empty($password)) {
    header("Location: ../PUBLIC/gestion_usuarios.php?error=invalid_data");
    exit();
}

// Validar longitud de username (mínimo 3 caracteres)
if (strlen($username) < 3) {
    header("Location: ../PUBLIC/gestion_usuarios.php?error=invalid_data");
    exit();
}

// Validar longitud de password (mínimo 5 caracteres)
if (strlen($password) < 5) {
    header("Location: ../PUBLIC/gestion_usuarios.php?error=invalid_data");
    exit();
}

// Validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../PUBLIC/gestion_usuarios.php?error=invalid_data");
    exit();
}

// Validar rol (solo 1, 2 o 3)
if (!in_array($rol, [1, 2, 3])) {
    $rol = 1; // Por defecto camarero
}

try {
    // --- VERIFICAR SI EL USERNAME YA EXISTE ---
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    if ($stmt->fetchColumn() > 0) {
        header("Location: ../PUBLIC/gestion_usuarios.php?error=duplicate_username");
        exit();
    }

    // --- VERIFICAR SI EL EMAIL YA EXISTE ---
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetchColumn() > 0) {
        header("Location: ../PUBLIC/gestion_usuarios.php?error=duplicate_email");
        exit();
    }

    // --- HASHEAR LA CONTRASEÑA ---
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // --- INSERTAR NUEVO USUARIO ---
    $stmt = $conn->prepare("
        INSERT INTO users (username, nombre, apellido, email, password_hash, rol, fecha_alta)
        VALUES (:username, :nombre, :apellido, :email, :password_hash, :rol, NOW())
    ");

    $resultado = $stmt->execute([
        'username' => $username,
        'nombre' => $nombre,
        'apellido' => $apellido,
        'email' => $email,
        'password_hash' => $password_hash,
        'rol' => $rol
    ]);

    if ($resultado) {
        header("Location: ../PUBLIC/gestion_usuarios.php?success=created");
    } else {
        header("Location: ../PUBLIC/gestion_usuarios.php?error=db_error");
    }

} catch (PDOException $e) {
    // Error de base de datos
    error_log("Error al crear usuario: " . $e->getMessage());
    header("Location: ../PUBLIC/gestion_usuarios.php?error=db_error");
}

exit();
?>
