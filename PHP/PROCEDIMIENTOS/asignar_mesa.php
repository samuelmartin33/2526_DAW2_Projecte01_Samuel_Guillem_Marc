<?php
session_start();
require_once './../CONEXION/conexion.php';

if (!isset($_SESSION['loginok']) || $_SESSION['loginok'] !== true) {
    header("Location: ../PUBLIC/login.php");
    exit();
}

// Recuperar username desde la sesión
$username = $_SESSION['username'] ?? null;

if (!$username) {
    session_destroy();
    header("Location: ../PUBLIC/login.php");
    exit();
}

// Consultar el ID del camarero correspondiente
$stmt = $conn->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
$stmt->execute([':username' => $username]);
$camarero = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$camarero) {
    session_destroy();
    header("Location: ../PUBLIC/login.php");
    exit();
}

// Guardar el id para usarlo en operaciones
$id_camarero = $camarero['id'];

$id_mesa = $_POST['mesa_id'] ?? null;

if (!$id_mesa) {
    header("Location: ./../PUBLIC/SALAS/comedor1.php");
    exit();
}

// Obtener info de la mesa
$stmt = $conn->prepare("SELECT * FROM mesas WHERE id = ?");
$stmt->execute([$id_mesa]);
$mesa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mesa || $mesa['estado'] != 1) {
    die("Mesa no disponible o ya ocupada.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['num_comensales'])) {
    $num_comensales = (int)$_POST['num_comensales'];
    $conn->beginTransaction();

    try {
        // Actualizar estado de la mesa
        $update = $conn->prepare("UPDATE mesas SET estado=2, asignado_por=? WHERE id=?");
        $update->execute([$id_camarero, $id_mesa]);

        // Crear registro en ocupaciones
        $insert = $conn->prepare("
            INSERT INTO ocupaciones (id_camarero, id_sala, id_mesa, inicio_ocupacion, num_comensales)
            VALUES (?, ?, ?, NOW(), ?)
        ");
        $insert->execute([$id_camarero, $mesa['id_sala'], $id_mesa, $num_comensales]);

        $conn->commit();
        header("Location: ./../PUBLIC/SALAS/comedor1.php");
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        die("Error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar <?php echo htmlspecialchars($mesa['nombre']); ?></title>
    <link rel="stylesheet" href="../../../css/panel_principal.css">
    <link rel="stylesheet" href="../../../css/salas_general.css">
    <link rel="stylesheet" href="../../../css/comedor1.css">
</head>
<body>
<div class="sala-container">
    <main class="sala-layout comedor1">
        <div class="login-container" style="width: 400px; text-align:center;">
            <h2>Asignar <?php echo htmlspecialchars($mesa['nombre']); ?></h2>
            <p><strong>Sala:</strong> Comedor 1</p>
            <p><strong>Capacidad:</strong> <?php echo $mesa['sillas']; ?> comensales</p>

            <form method="POST">
                <input type="hidden" name="mesa_id" value="<?php echo $id_mesa; ?>">
                <label>Número de comensales:</label>
                <input type="number" name="num_comensales" min="1" max="<?php echo $mesa['sillas']; ?>" required>
                <br><br>
                <button type="submit">Asignar Mesa</button>
                <a href="./../PUBLIC/SALAS/comedor1.php" class="btn-volver">Cancelar</a>
            </form>
        </div>
    </main>
</div>
</body>
</html>
