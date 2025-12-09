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
    // --- VERIFICAR QUE LA MESA EXISTE Y ESTÁ OCUPADA ---
    $stmt = $conn->prepare("SELECT id, id_sala, estado, asignado_por FROM mesas WHERE id = :id");
    $stmt->execute(['id' => $mesa_id]);
    $mesa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$mesa) {
        header("Location: ../PUBLIC/selector_salas.php?error=mesa_not_found");
        exit();
    }
    
    if ($mesa['estado'] != 2) {
        header("Location: ../PUBLIC/ver_sala.php?id=" . $mesa['id_sala'] . "&error=mesa_ya_libre");
        exit();
    }
    
    // --- VERIFICAR QUE EL CAMARERO QUE LIBERA ES EL QUE LA OCUPÓ ---
    if ($mesa['asignado_por'] != $id_camarero) {
        header("Location: ../PUBLIC/ver_sala.php?id=" . $mesa['id_sala'] . "&error=no_autorizado");
        exit();
    }
    
    // --- LIBERAR MESA ---
    $stmt = $conn->prepare("
        UPDATE mesas 
        SET estado = 1, 
            asignado_por = NULL
        WHERE id = :mesa_id
    ");
    
    $resultado = $stmt->execute(['mesa_id' => $mesa_id]);
    
    if ($resultado) {
        // --- ACTUALIZAR REGISTRO DE OCUPACIÓN (cerrar) ---
        $stmt = $conn->prepare("
            UPDATE ocupaciones 
            SET final_ocupacion = NOW()
            WHERE id_mesa = :mesa
            AND id_camarero = :camarero
            AND final_ocupacion IS NULL
            ORDER BY inicio_ocupacion DESC
            LIMIT 1
        ");
        
        $stmt->execute([
            'mesa' => $mesa_id,
            'camarero' => $id_camarero
        ]);
        
        header("Location: ../PUBLIC/ver_sala.php?id=" . $mesa['id_sala'] . "&success=mesa_liberada");
    } else {
        header("Location: ../PUBLIC/ver_sala.php?id=" . $mesa['id_sala'] . "&error=db_error");
    }
    
} catch (PDOException $e) {
    error_log("Error al liberar mesa: " . $e->getMessage());
    header("Location: ../PUBLIC/selector_salas.php?error=db_error");
}

exit();
?>
