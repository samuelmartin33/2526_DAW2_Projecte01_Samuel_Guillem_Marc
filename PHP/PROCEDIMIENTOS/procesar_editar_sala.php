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
    header("Location: ../PUBLIC/gestion_salas.php?error=invalid_method");
    exit();
}

// --- OBTENER Y VALIDAR DATOS DEL FORMULARIO ---
$id = intval($_POST['id'] ?? 0);
$nombre = trim($_POST['nombre'] ?? '');
$imagen_fondo_actual = trim($_POST['imagen_fondo_actual'] ?? '');
$imagen_mesa_actual = trim($_POST['imagen_mesa_actual'] ?? '');

// Validaciones básicas
if ($id <= 0 || empty($nombre)) {
    header("Location: ../PUBLIC/gestion_salas.php?error=invalid_data");
    exit();
}

// Función para procesar upload de imagen
function procesarImagen($file, $tipo, $imagen_anterior = null) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return $imagen_anterior; // Mantener imagen actual
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false; // Error en la subida
    }
    
    // Validar extensión del archivo
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png'];
    
    if (!in_array($extension, $allowed_extensions)) {
        return false; // Extensión no permitida
    }
    
    // Validar tamaño (máximo 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        header("Location: ../PUBLIC/gestion_salas.php?error=image_too_large");
        exit();
    }
    
    // Validación opcional de tipo MIME (más permisiva)
    $image_info = @getimagesize($file['tmp_name']);
    if ($image_info === false) {
        // Si getimagesize falla, aún intentamos por extensión
        // Solo validamos que sea una extensión permitida
        if (!in_array($extension, $allowed_extensions)) {
            return false;
        }
    }
    
    // Generar nombre único
    $nombre_archivo = time() . '_' . uniqid() . '.' . $extension;
    
    // Determinar carpeta destino
    $carpeta = ($tipo === 'fondo') ? 'fondos' : 'mesas';
    $ruta_destino = "../../img/salas/{$carpeta}/{$nombre_archivo}";
    
    // Mover archivo
    if (move_uploaded_file($file['tmp_name'], $ruta_destino)) {
        // Eliminar imagen anterior si existe
        if ($imagen_anterior) {
            @unlink("../../img/salas/{$carpeta}/{$imagen_anterior}");
        }
        return $nombre_archivo;
    }
    
    return false;
}

try {
    // --- VERIFICAR QUE LA SALA EXISTE ---
    $stmt = $conn->prepare("SELECT id FROM salas WHERE id = :id");
    $stmt->execute(['id' => $id]);
    if (!$stmt->fetch()) {
        header("Location: ../PUBLIC/gestion_salas.php?error=sala_not_found");
        exit();
    }
    
    // --- VERIFICAR SI EL NOMBRE YA EXISTE (en otra sala) ---
    $stmt = $conn->prepare("SELECT COUNT(*) FROM salas WHERE nombre = :nombre AND id != :id");
    $stmt->execute(['nombre' => $nombre, 'id' => $id]);
    if ($stmt->fetchColumn() > 0) {
        header("Location: ../PUBLIC/gestion_salas.php?error=duplicate_name");
        exit();
    }
    
    // --- PROCESAR IMÁGENES ---
    $imagen_fondo = $imagen_fondo_actual;
    $imagen_mesa = $imagen_mesa_actual;
    
    if (isset($_FILES['imagen_fondo'])) {
        $result = procesarImagen($_FILES['imagen_fondo'], 'fondo', $imagen_fondo_actual);
        if ($result === false) {
            header("Location: ../PUBLIC/gestion_salas.php?error=invalid_image");
            exit();
        }
        $imagen_fondo = $result;
    }
    
    if (isset($_FILES['imagen_mesa'])) {
        $result = procesarImagen($_FILES['imagen_mesa'], 'mesa', $imagen_mesa_actual);
        if ($result === false) {
            header("Location: ../PUBLIC/gestion_salas.php?error=invalid_image");
            exit();
        }
        $imagen_mesa = $result;
    }
    
    // --- ACTUALIZAR SALA ---
    $stmt = $conn->prepare("
        UPDATE salas 
        SET nombre = :nombre,
            imagen_fondo = :imagen_fondo,
            imagen_mesa = :imagen_mesa
        WHERE id = :id
    ");
    
    $resultado = $stmt->execute([
        'nombre' => $nombre,
        'imagen_fondo' => $imagen_fondo,
        'imagen_mesa' => $imagen_mesa,
        'id' => $id
    ]);
    
    if ($resultado) {
        header("Location: ../PUBLIC/gestion_salas.php?success=updated");
    } else {
        header("Location: ../PUBLIC/gestion_salas.php?error=db_error");
    }
    
} catch (PDOException $e) {
    // Error de base de datos
    error_log("Error al editar sala: " . $e->getMessage());
    header("Location: ../PUBLIC/gestion_salas.php?error=db_error");
}

exit();
?>
