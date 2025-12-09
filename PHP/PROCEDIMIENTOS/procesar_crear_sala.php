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
$nombre = trim($_POST['nombre'] ?? '');

// Validaciones básicas
if (empty($nombre)) {
    header("Location: ../PUBLIC/gestion_salas.php?error=invalid_data");
    exit();
}

// Función para procesar upload de imagen
function procesarImagen($file, $tipo) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // No se subió archivo
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
        return $nombre_archivo;
    }
    
    return false;
}

try {
    // --- VERIFICAR SI EL NOMBRE YA EXISTE ---
    $stmt = $conn->prepare("SELECT COUNT(*) FROM salas WHERE nombre = :nombre");
    $stmt->execute(['nombre' => $nombre]);
    if ($stmt->fetchColumn() > 0) {
        header("Location: ../PUBLIC/gestion_salas.php?error=duplicate_name");
        exit();
    }
    
    // --- PROCESAR IMÁGENES ---
    $imagen_fondo = null;
    $imagen_mesa = null;
    
    if (isset($_FILES['imagen_fondo'])) {
        $result = procesarImagen($_FILES['imagen_fondo'], 'fondo');
        if ($result === false) {
            header("Location: ../PUBLIC/gestion_salas.php?error=invalid_image");
            exit();
        }
        $imagen_fondo = $result;
    }
    
    if (isset($_FILES['imagen_mesa'])) {
        $result = procesarImagen($_FILES['imagen_mesa'], 'mesa');
        if ($result === false) {
            // Eliminar imagen de fondo si ya se subió
            if ($imagen_fondo) {
                @unlink("../../img/salas/fondos/{$imagen_fondo}");
            }
            header("Location: ../PUBLIC/gestion_salas.php?error=invalid_image");
            exit();
        }
        $imagen_mesa = $result;
    }
    
    // --- INSERTAR NUEVA SALA ---
    $stmt = $conn->prepare("
        INSERT INTO salas (nombre, imagen_fondo, imagen_mesa, num_mesas)
        VALUES (:nombre, :imagen_fondo, :imagen_mesa, 0)
    ");
    
    $resultado = $stmt->execute([
        'nombre' => $nombre,
        'imagen_fondo' => $imagen_fondo,
        'imagen_mesa' => $imagen_mesa
    ]);
    
    if ($resultado) {
        header("Location: ../PUBLIC/gestion_salas.php?success=created");
    } else {
        // Eliminar imágenes si falló la inserción
        if ($imagen_fondo) @unlink("../../img/salas/fondos/{$imagen_fondo}");
        if ($imagen_mesa) @unlink("../../img/salas/mesas/{$imagen_mesa}");
        header("Location: ../PUBLIC/gestion_salas.php?error=db_error");
    }
    
} catch (PDOException $e) {
    // Error de base de datos
    error_log("Error al crear sala: " . $e->getMessage());
    
    // Eliminar imágenes subidas
    if (isset($imagen_fondo) && $imagen_fondo) @unlink("../../img/salas/fondos/{$imagen_fondo}");
    if (isset($imagen_mesa) && $imagen_mesa) @unlink("../../img/salas/mesas/{$imagen_mesa}");
    
    header("Location: ../PUBLIC/gestion_salas.php?error=db_error");
}

exit();
?>
