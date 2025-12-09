<?php
session_start();
require_once '../CONEXION/conexion.php';

// --- VERIFICAR SESIÓN Y ROL ADMIN ---
if (!isset($_SESSION['loginok']) || $_SESSION['loginok'] !== true || ($_SESSION['rol'] ?? 1) != 2) {
    header("Location: ../PUBLIC/index.php");
    exit();
}

// --- OBTENER Y VALIDAR ID DE LA SALA ---
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: ../PUBLIC/gestion_salas.php?error=invalid_data");
    exit();
}

try {
    // --- VERIFICAR QUE LA SALA EXISTE Y OBTENER IMÁGENES ---
    $stmt = $conn->prepare("
        SELECT nombre, imagen_fondo, imagen_mesa 
        FROM salas 
        WHERE id = :id
    ");
    $stmt->execute(['id' => $id]);
    $sala = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$sala) {
        header("Location: ../PUBLIC/gestion_salas.php?error=sala_not_found");
        exit();
    }
    
    // --- VERIFICAR SI LA SALA TIENE MESAS ASOCIADAS ---
    $stmt = $conn->prepare("SELECT COUNT(*) FROM mesas WHERE id_sala = :id");
    $stmt->execute(['id' => $id]);
    $tiene_mesas = $stmt->fetchColumn() > 0;
    
    if ($tiene_mesas) {
        header("Location: ../PUBLIC/gestion_salas.php?error=has_tables");
        exit();
    }
    
    // --- ELIMINAR SALA DE LA BASE DE DATOS ---
    $stmt = $conn->prepare("DELETE FROM salas WHERE id = :id");
    $resultado = $stmt->execute(['id' => $id]);
    
    if ($resultado) {
        // --- ELIMINAR IMÁGENES DEL SERVIDOR ---
        if ($sala['imagen_fondo']) {
            @unlink("../../img/salas/fondos/" . $sala['imagen_fondo']);
        }
        if ($sala['imagen_mesa']) {
            @unlink("../../img/salas/mesas/" . $sala['imagen_mesa']);
        }
        
        header("Location: ../PUBLIC/gestion_salas.php?success=deleted");
    } else {
        header("Location: ../PUBLIC/gestion_salas.php?error=db_error");
    }
    
} catch (PDOException $e) {
    // Error de base de datos
    error_log("Error al eliminar sala: " . $e->getMessage());
    
    // Si es error de foreign key constraint
    if ($e->getCode() == 23000) {
        header("Location: ../PUBLIC/gestion_salas.php?error=has_tables");
    } else {
        header("Location: ../PUBLIC/gestion_salas.php?error=db_error");
    }
}

exit();
?>
