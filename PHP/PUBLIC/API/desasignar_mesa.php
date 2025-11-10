<?php
header('Content-Type: application/json');
// --- RUTA CORREGIDA ---
require_once '../../CONEXION/conexion.php'; // Usa $conn
session_start();

if (!isset($_POST['id_mesa'])) {
    echo json_encode(['error' => 'Faltan datos.']);
    exit;
}
$id_mesa = (int)$_POST['id_mesa'];
$id_camarero_logueado = $_SESSION['user_id'] ?? 0;
if ($id_camarero_logueado == 0) {
    echo json_encode(['error' => 'Sesión no válida.']);
    exit;
}

$conn->beginTransaction();
try {
    $sql_find = "SELECT id, id_camarero FROM ocupaciones WHERE id_mesa = ? AND final_ocupacion IS NULL ORDER BY inicio_ocupacion DESC LIMIT 1";
    $stmt_find = $conn->prepare($sql_find);
    $stmt_find->execute([$id_mesa]);
    $ocupacion = $stmt_find->fetch(PDO::FETCH_ASSOC);
    if (!$ocupacion) { throw new Exception("Esta mesa no tiene una ocupación activa."); }
    if ($ocupacion['id_camarero'] != $id_camarero_logueado) { throw new Exception("No tienes permiso para liberar esta mesa."); }

    $stmt_update_mesa = $conn->prepare("UPDATE mesas SET estado = 1 WHERE id = ?");
    $stmt_update_mesa->execute([$id_mesa]);

    $stmt_update_ocupacion = $conn->prepare("UPDATE ocupaciones SET final_ocupacion = NOW() WHERE id = ?");
    $stmt_update_ocupacion->execute([$ocupacion['id']]);

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Mesa liberada correctamente.']);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['error' => $e->getMessage()]);
}