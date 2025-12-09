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
    header("Location: ../PUBLIC/gestion_mesas.php?error=invalid_method");
    exit();
}

// --- OBTENER Y VALIDAR DATOS DEL FORMULARIO ---
$id = intval($_POST['id'] ?? 0);
$nombre = trim($_POST['nombre'] ?? '');
$id_sala = intval($_POST['id_sala'] ?? 0);
$sillas = intval($_POST['sillas'] ?? 0);

// Validaciones básicas
if ($id <= 0 || empty($nombre) || $id_sala <= 0 || $sillas < 1 || $sillas > 50) {
    header("Location: ../PUBLIC/gestion_mesas.php?error=invalid_data");
    exit();
}

try {
    // --- VERIFICAR QUE LA MESA EXISTE ---
    $stmt = $conn->prepare("SELECT id FROM mesas WHERE id = :id");
    $stmt->execute(['id' => $id]);
    if (!$stmt->fetch()) {
        header("Location: ../PUBLIC/gestion_mesas.php?error=mesa_not_found");
        exit();
    }
    
    // --- VERIFICAR QUE LA SALA EXISTE ---
    $stmt = $conn->prepare("SELECT id FROM salas WHERE id = :id");
    $stmt->execute(['id' => $id_sala]);
    if (!$stmt->fetch()) {
        header("Location: ../PUBLIC/gestion_mesas.php?error=invalid_data");
        exit();
    }
    
    // --- ACTUALIZAR MESA ---
    $stmt = $conn->prepare("
        UPDATE mesas 
        SET nombre = :nombre,
            id_sala = :id_sala,
            sillas = :sillas
        WHERE id = :id
    ");
    
    $resultado = $stmt->execute([
        'nombre' => $nombre,
        'id_sala' => $id_sala,
        'sillas' => $sillas,
        'id' => $id
    ]);
    
    if ($resultado) {
        header("Location: ../PUBLIC/gestion_mesas.php?success=updated");
    } else {
        header("Location: ../PUBLIC/gestion_mesas.php?error=db_error");
    }
    
} catch (PDOException $e) {
    error_log("Error al editar mesa: " . $e->getMessage());
    header("Location: ../PUBLIC/gestion_mesas.php?error=db_error");
}

exit();
?>
