<?php
header('Content-Type: application/json');
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
    // 1. CAMBIO: Verificar permisos usando la tabla 'mesas'
    $stmt_check = $conn->prepare("SELECT asignado_por FROM mesas WHERE id = ?");
    $stmt_check->execute([$id_mesa]);
    $mesa = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$mesa) {
        throw new Exception("La mesa no existe.");
    }

    if ($mesa['asignado_por'] != $id_camarero_logueado) {
        throw new Exception("No tienes permiso para liberar esta mesa (le pertenece a otro camarero).");
    }

    // 2. CAMBIO: Actualizar la tabla 'mesas'
    // Pone estado=1 (libre) y quita el camarero asignado
    $stmt_update_mesa = $conn->prepare(
        "UPDATE mesas 
         SET estado = 1, asignado_por = NULL 
         WHERE id = ?"
    );
    $stmt_update_mesa->execute([$id_mesa]);

    // 3. Finalizar la ocupación activa en el log 'ocupaciones'
    // (Cierra la entrada más reciente que aún esté abierta para esa mesa)
    $stmt_update_ocupacion = $conn->prepare(
        "UPDATE ocupaciones 
         SET final_ocupacion = NOW() 
         WHERE id_mesa = ? AND final_ocupacion IS NULL 
         ORDER BY inicio_ocupacion DESC 
         LIMIT 1"
    );
    $stmt_update_ocupacion->execute([$id_mesa]);

    // Confirmar transacción
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Mesa liberada correctamente.']);

} catch (Exception $e) {
    // Revertir transacción
    $conn->rollBack();
    echo json_encode(['error' => $e->getMessage()]);
}