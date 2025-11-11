<?php
session_start();
require_once './../CONEXION/conexion.php'; // Ruta desde PROCEDIMIENTOS

// --- Verificación de sesión ---
if (!isset($_SESSION['loginok']) || $_SESSION['loginok'] !== true) {
    header("Location: ../PUBLIC/login.php");
    exit();
}
$username = $_SESSION['username'] ?? null;
if (!$username) {
    session_destroy(); header("Location: ../PUBLIC/login.php"); exit();
}

// --- Consultar ID del camarero ---
$stmt_camarero = $conn->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
$stmt_camarero->execute([':username' => $username]);
$camarero = $stmt_camarero->fetch(PDO::FETCH_ASSOC);
if (!$camarero) {
    session_destroy(); header("Location: ../PUBLIC/login.php"); exit();
}
$id_camarero = $camarero['id'];

// --- Obtener Mesa ---
$id_mesa = $_POST['mesa_id'] ?? null;
if (!$id_mesa) {
    header("Location: ./../PUBLIC/SALAS/comedor1.php"); // Volver a comedor1
    exit();
}

$stmt_mesa = $conn->prepare("SELECT * FROM mesas WHERE id = ?");
$stmt_mesa->execute([$id_mesa]);
$mesa = $stmt_mesa->fetch(PDO::FETCH_ASSOC);

if (!$mesa || $mesa['estado'] != 1) {
    // Aquí podrías guardar un mensaje de error en la sesión y redirigir
    die("Mesa no disponible o ya ocupada.");
}

// --- Lógica de Asignación (si se envía el formulario de esta página) ---
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
        header("Location: ./../PUBLIC/SALAS/comedor1.php"); // Volver a comedor1
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
    <!-- Rutas relativas desde PROCEDIMIENTOS/ -->
    <link rel="stylesheet" href="../../css/panel_principal.css">
    <link rel="stylesheet" href="../../css/salas_general.css">
    <link rel="stylesheet" href="../../css/comedor1.css">
</head>
<body>
<div class="sala-container">
    <!-- Mantenemos el fondo de la sala -->
    <main class="sala-layout comedor1">
        
        <!-- Usamos la clase 'interstitial-form' (nueva en salas_general.css) -->
        <div class="interstitial-form">
            <h2>Asignar <?php echo htmlspecialchars($mesa['nombre']); ?></h2>
            <p><strong>Sala:</strong> Comedor 1</p>
            <p><strong>Capacidad:</strong> <?php echo $mesa['sillas']; ?> comensales</p>

            <form method="POST" class="form-full-page">
                <input type="hidden" name="mesa_id" value="<?php echo $id_mesa; ?>">
                
                <label for="num-comensales">Número de comensales:</label>
                <input type="number" id="num-comensales" name="num_comensales" min="1" max="<?php echo $mesa['sillas']; ?>" required>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Asignar Mesa</button>
                    <!-- Ruta para cancelar -->
                    <a href="./../PUBLIC/SALAS/comedor1.php" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>