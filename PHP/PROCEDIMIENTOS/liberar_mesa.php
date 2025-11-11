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
$rol = $_SESSION['rol'] ?? 1; // Necesitamos el rol para permisos

// --- Obtener Mesa ---
$id_mesa = $_POST['mesa_id'] ?? null;
if (!$id_mesa) {
    header("Location: ./../PUBLIC/SALAS/comedor1.php"); // Volver a comedor1
    exit();
}

$stmt_mesa = $conn->prepare("
    SELECT m.*, u.username AS camarero
    FROM mesas m
    LEFT JOIN users u ON m.asignado_por = u.id
    WHERE m.id = ?
");
$stmt_mesa->execute([$id_mesa]);
$mesa = $stmt_mesa->fetch(PDO::FETCH_ASSOC);

if (!$mesa) die("Mesa no encontrada.");

// --- Lógica de Liberación (si se confirma) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
    $conn->beginTransaction();
    try {
        // Solo el camarero que asignó O un admin (rol 2) puede liberar
        if ($mesa['asignado_por'] != $id_camarero && $rol != 2) {
            throw new Exception("No puedes liberar una mesa asignada por otro camarero.");
        }

        $conn->prepare("UPDATE mesas SET estado=1, asignado_por=NULL WHERE id=?")->execute([$id_mesa]);
        $conn->prepare("
            UPDATE ocupaciones SET final_ocupacion=NOW()
            WHERE id_mesa=? AND final_ocupacion IS NULL
            ORDER BY inicio_ocupacion DESC LIMIT 1
        ")->execute([$id_mesa]);

        $conn->commit();
        header("Location: ./../PUBLIC/SALAS/comedor1.php"); // Volver a comedor1
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        // Aquí podrías guardar $e->getMessage() en $_SESSION y mostrarlo en comedor1.php
        die("Error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Liberar <?php echo htmlspecialchars($mesa['nombre']); ?></title>
    <!-- Rutas relativas desde PROCEDIMIENTOS/ -->
    <link rel="stylesheet" href="../../css/panel_principal.css">
    <link rel="stylesheet" href="../../css/salas_general.css">
    <link rel="stylesheet" href="../../css/comedor1.css">
</head>
<body>
<div class="sala-container">
    <!-- Mantenemos el fondo de la sala -->
    <main class="sala-layout comedor1">
        
        <!-- Usamos la clase 'interstitial-form' -->
        <div class="interstitial-form">
            <h2>Liberar <?php echo htmlspecialchars($mesa['nombre']); ?></h2>
            <p>Asignada por: <strong><?php echo htmlspecialchars($mesa['camarero'] ?? 'N/A'); ?></strong></p>
            <p>¿Seguro que quieres liberar esta mesa?</p>

            <form method="POST" class="form-full-page">
                <input type="hidden" name="mesa_id" value="<?php echo $id_mesa; ?>">
                
                <div class="form-actions">
                    <button type="submit" name="confirmar" value="1" class="btn-danger">Sí, liberar</button>
                    <a href="./../PUBLIC/SALAS/comedor1.php" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>