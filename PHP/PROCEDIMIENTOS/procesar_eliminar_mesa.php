<?php
session_start();
require_once '../CONEXION/conexion.php';

// --- VERIFICAR SESIÓN Y ROL ADMIN ---
if (!isset($_SESSION['loginok']) || $_SESSION['loginok'] !== true || ($_SESSION['rol'] ?? 1) != 2) {
    header("Location: ../PUBLIC/index.php");
    exit();
}

// --- OBTENER Y VALIDAR ID DE LA MESA ---
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: ../PUBLIC/gestion_mesas.php?error=invalid_data");
    exit();
}

try {
    // --- VERIFICAR QUE LA MESA EXISTE Y OBTENER SU ESTADO ---
    $stmt = $conn->prepare("SELECT id, estado FROM mesas WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $mesa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$mesa) {
        header("Location: ../PUBLIC/gestion_mesas.php?error=mesa_not_found");
        exit();
    }
    
    // --- VERIFICAR SI LA MESA ESTÁ OCUPADA ---
    if ($mesa['estado'] == 2) {
        header("Location: ../PUBLIC/gestion_mesas.php?error=table_occupied");
        exit();
    }
    
    // --- ELIMINAR MESA DE LA BASE DE DATOS ---
    $stmt = $conn->prepare("DELETE FROM mesas WHERE id = :id");
    $resultado = $stmt->execute(['id' => $id]);
    
    if ($resultado) {
        header("Location: ../PUBLIC/gestion_mesas.php?success=deleted");
    } else {
        header("Location: ../PUBLIC/gestion_mesas.php?error=db_error");
    }
    
} catch (PDOException $e) {
    error_log("Error al eliminar mesa: " . $e->getMessage());
    header("Location: ../PUBLIC/gestion_mesas.php?error=db_error");
}

exit();
?>
