<?php
session_start();
require_once '../CONEXION/conexion.php';

// --- VERIFICAR SESIÓN ---
if (!isset($_SESSION['loginok']) || $_SESSION['loginok'] !== true) {
    header("Location: ../PUBLIC/login.php");
    exit();
}

// --- VALIDAR MÉTODO POST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../PUBLIC/selector_salas.php");
    exit();
}

// --- OBTENER DATOS ---
$mesa_id = intval($_POST['mesa_id'] ?? 0);
$id_camarero = $_SESSION['id_usuario'] ?? 0;

if ($mesa_id <= 0 || $id_camarero <= 0) {
    header("Location: ../PUBLIC/selector_salas.php?error=invalid_data");
    exit();
}

try {
    // --- VERIFICAR QUE LA MESA EXISTE Y ESTÁ LIBRE ---
    $stmt = $conn->prepare("SELECT id, id_sala, estado FROM mesas WHERE id = :id");
    $stmt->execute(['id' => $mesa_id]);
    $mesa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$mesa) {
        header("Location: ../PUBLIC/selector_salas.php?error=mesa_not_found");
        exit();
    }
    
    if ($mesa['estado'] != 1) {
        header("Location: ../PUBLIC/ver_sala.php?id=" . $mesa['id_sala'] . "&error=mesa_ocupada");
        exit();
    }
    
    // --- ASIGNAR MESA AL CAMARERO ---
    $stmt = $conn->prepare("
        UPDATE mesas 
        SET estado = 2, 
            asignado_por = :camarero
        WHERE id = :mesa_id
    ");
    
    $resultado = $stmt->execute([
        'camarero' => $id_camarero,
        'mesa_id' => $mesa_id
    ]);
    
    if ($resultado) {
        // --- CREAR REGISTRO DE OCUPACIÓN ---
        $stmt = $conn->prepare("
            INSERT INTO ocupaciones (id_camarero, id_sala, id_mesa, inicio_ocupacion, num_comensales)
            VALUES (:camarero, :sala, :mesa, NOW(), 0)
        ");
        
        $stmt->execute([
            'camarero' => $id_camarero,
            'sala' => $mesa['id_sala'],
            'mesa' => $mesa_id
        ]);
        
        header("Location: ../PUBLIC/ver_sala.php?id=" . $mesa['id_sala'] . "&success=mesa_asignada");
    } else {
        header("Location: ../PUBLIC/ver_sala.php?id=" . $mesa['id_sala'] . "&error=db_error");
    }
    
} catch (PDOException $e) {
    error_log("Error al asignar mesa: " . $e->getMessage());
    header("Location: ../PUBLIC/selector_salas.php?error=db_error");
}

exit();
?>
