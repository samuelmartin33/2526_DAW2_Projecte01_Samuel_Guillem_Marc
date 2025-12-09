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
$id = intval($_POST['id'] ?? 0);
$username = trim($_POST['username'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? ''); // Puede estar vacío
$rol = intval($_POST['rol'] ?? 1);

// Validaciones básicas
if ($id <= 0 || empty($username) || empty($nombre) || empty($email)) {
    header("Location: ../PUBLIC/gestion_usuarios.php?error=invalid_data");
    exit();
}

// Validar longitud de username (mínimo 3 caracteres)
if (strlen($username) < 3) {
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
    $rol = 1;
}

// Si se proporciona nueva contraseña, validar longitud mínima
if (!empty($password) && strlen($password) < 5) {
    header("Location: ../PUBLIC/gestion_usuarios.php?error=invalid_data");
    exit();
}

try {
    // --- VERIFICAR SI EL USUARIO EXISTE ---
    $stmt = $conn->prepare("SELECT id FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);
    if (!$stmt->fetch()) {
        header("Location: ../PUBLIC/gestion_usuarios.php?error=user_not_found");
        exit();
    }

    // --- VERIFICAR SI EL USERNAME YA EXISTE (en otro usuario) ---
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = :username AND id != :id");
    $stmt->execute(['username' => $username, 'id' => $id]);
    if ($stmt->fetchColumn() > 0) {
        header("Location: ../PUBLIC/gestion_usuarios.php?error=duplicate_username");
        exit();
    }

    // --- VERIFICAR SI EL EMAIL YA EXISTE (en otro usuario) ---
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = :email AND id != :id");
    $stmt->execute(['email' => $email, 'id' => $id]);
    if ($stmt->fetchColumn() > 0) {
        header("Location: ../PUBLIC/gestion_usuarios.php?error=duplicate_email");
        exit();
    }

    // --- PREPARAR ACTUALIZACIÓN ---
    if (!empty($password)) {
        // Si hay nueva contraseña, actualizar todos los campos incluyendo password
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $conn->prepare("
            UPDATE users 
            SET username = :username,
                nombre = :nombre,
                apellido = :apellido,
                email = :email,
                password_hash = :password_hash,
                rol = :rol
            WHERE id = :id
        ");

        $resultado = $stmt->execute([
            'username' => $username,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => $email,
            'password_hash' => $password_hash,
            'rol' => $rol,
            'id' => $id
        ]);
    } else {
        // Si NO hay nueva contraseña, actualizar solo los demás campos
        $stmt = $conn->prepare("
            UPDATE users 
            SET username = :username,
                nombre = :nombre,
                apellido = :apellido,
                email = :email,
                rol = :rol
            WHERE id = :id
        ");

        $resultado = $stmt->execute([
            'username' => $username,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => $email,
            'rol' => $rol,
            'id' => $id
        ]);
    }

    if ($resultado) {
        header("Location: ../PUBLIC/gestion_usuarios.php?success=updated");
    } else {
        header("Location: ../PUBLIC/gestion_usuarios.php?error=db_error");
    }

} catch (PDOException $e) {
    // Error de base de datos
    error_log("Error al editar usuario: " . $e->getMessage());
    header("Location: ../PUBLIC/gestion_usuarios.php?error=db_error");
}

exit();
?>
