<?php
header('Content-Type: application/json');
// --- RUTA CORREGIDA ---
require_once '../../CONEXION/conexion.php'; // Usa $conn

if (!isset($_POST['id_mesa'], $_POST['id_camarero'], $_POST['num_comensales'])) {
    echo json_encode(['error' => 'Faltan datos.']);
    exit;
}
$id_mesa = (int)$_POST['id_mesa'];
$id_camarero = (int)$_POST['id_camarero'];
$num_comensales = (int)$_POST['num_comensales'];
$conn->beginTransaction();
try {
    $stmt_sala = $conn->prepare("SELECT id_sala FROM mesas WHERE id = ?");
    $stmt_sala->execute([$id_mesa]);
    $id_sala = $stmt_sala->fetchColumn();
    if (!$id_sala) { throw new Exception("Mesa no encontrada."); }

    $stmt_update_mesa = $conn->prepare("UPDATE mesas SET estado = 2 WHERE id = ? AND estado = 1");
    $stmt_update_mesa->execute([$id_mesa]);
    if ($stmt_update_mesa->rowCount() == 0) { throw new Exception("La mesa ya estaba ocupada."); }

    $sql_insert_ocupacion = "INSERT INTO ocupaciones (id_camarero, id_sala, id_mesa, inicio_ocupacion, num_comensales) VALUES (?, ?, ?, NOW(), ?)";
    $stmt_insert = $conn->prepare($sql_insert_ocupacion);
    $stmt_insert->execute([$id_camarero, $id_sala, $id_mesa, $num_comensales]);

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Mesa asignada correctamente.']);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['error' => $e->getMessage()]);
}