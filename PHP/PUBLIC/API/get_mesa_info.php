<?php
header('Content-Type: application/json');
// --- RUTA CORREGIDA ---
// Sube 2 niveles (API -> PUBLIC -> PHP) y entra en CONEXION
require_once '../../CONEXION/conexion.php'; // Usa $conn

if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    echo json_encode(['error' => 'ID de mesa no válido.']);
    exit;
}
$id_mesa = (int)$_GET['id'];
try {
    $sql = "
        SELECT 
            m.id AS mesa_id, m.nombre AS mesa_nombre, m.sillas AS mesa_sillas,
            m.estado AS mesa_estado, o.id AS ocupacion_id, o.id_camarero AS camarero_id,
            u.username AS camarero_username
        FROM mesas m
        LEFT JOIN ocupaciones o ON m.id = o.id_mesa AND o.final_ocupacion IS NULL
        LEFT JOIN users u ON o.id_camarero = u.id
        WHERE m.id = :id_mesa
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id_mesa' => $id_mesa]);
    $mesa_info = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($mesa_info) {
        echo json_encode($mesa_info);
    } else {
        echo json_encode(['error' => 'Mesa no encontrada.']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
}