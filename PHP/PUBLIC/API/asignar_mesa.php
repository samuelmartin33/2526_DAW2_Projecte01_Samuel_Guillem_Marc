<?php
header('Content-Type: application/json');
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
    // 1. Obtener el id_sala de la mesa (necesario para la tabla ocupaciones)
    $stmt_sala = $conn->prepare("SELECT id_sala FROM mesas WHERE id = ?");
    $stmt_sala->execute([$id_mesa]);
    $id_sala = $stmt_sala->fetchColumn();

    if (!$id_sala) {
        throw new Exception("Mesa no encontrada.");
    }

    // 2. CAMBIO: Actualizar la tabla 'mesas'
    // Pone estado=2 y asigna el camarero
    $stmt_update_mesa = $conn->prepare(
        "UPDATE mesas 
         SET estado = 2, asignado_por = ? 
         WHERE id = ? AND estado = 1"
    );
    $stmt_update_mesa->execute([$id_camarero, $id_mesa]);
    
    // Si rowCount es 0, significa que la mesa no estaba libre (estado != 1)
    if ($stmt_update_mesa->rowCount() == 0) {
        throw new Exception("La mesa ya estaba ocupada por otro usuario.");
    }

    // 3. Crear el registro en la tabla 'ocupaciones' (para el historial)
    $sql_insert_ocupacion = "
        INSERT INTO ocupaciones 
            (id_camarero, id_sala, id_mesa, inicio_ocupacion, num_comensales)
        VALUES
            (?, ?, ?, NOW(), ?)
    ";
    $stmt_insert = $conn->prepare($sql_insert_ocupacion);
    $stmt_insert->execute([$id_camarero, $id_sala, $id_mesa, $num_comensales]);

    // Confirmar transacción
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Mesa asignada correctamente.']);

} catch (Exception $e) {
    // Revertir transacción
    $conn->rollBack();
    echo json_encode(['error' => $e->getMessage()]);
}