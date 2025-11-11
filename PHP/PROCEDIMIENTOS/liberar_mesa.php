<?php
session_start();
require_once './../CONEXION/conexion.php';

$error = ''; // Variable para mostrar errores en el formulario

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
    header("Location: ./PUBLIC/SALAS/comedor1.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT m.*, u.username AS camarero
    FROM mesas m
    LEFT JOIN users u ON m.asignado_por = u.id
    WHERE m.id = ?
");
$stmt->execute([$id_mesa]);
$mesa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mesa) {
    $error = "Mesa no encontrada.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar']) && !$error) {
    $conn->beginTransaction();
    try {
        if ($mesa['asignado_por'] != $id_camarero) {
            $error = "No puedes liberar una mesa asignada por otro camarero.";
        } else {
            $conn->prepare("UPDATE mesas SET estado=1, asignado_por=NULL WHERE id=?")->execute([$id_mesa]);
            $conn->prepare("
                UPDATE ocupaciones SET final_ocupacion=NOW()
                WHERE id_mesa=? AND final_ocupacion IS NULL
                ORDER BY inicio_ocupacion DESC LIMIT 1
            ")->execute([$id_mesa]);

            $conn->commit();
            header("Location: ./../PUBLIC/SALAS/comedor1.php");
            exit();
        }
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Error al liberar la mesa: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Liberar <?php echo htmlspecialchars($mesa['nombre']); ?></title>
    <link rel="stylesheet" href="../../../css/panel_principal.css">
    <link rel="stylesheet" href="../../../css/salas_general.css">
    <link rel="stylesheet" href="../../../css/comedor1.css">
</head>
<body>
<div class="sala-container">
    <main class="sala-layout comedor1">
        <div class="login-container" style="width: 400px; text-align:center;">
            <h2>Liberar <?php echo htmlspecialchars($mesa['nombre']); ?></h2>
            <p>Asignada por: <?php echo htmlspecialchars($mesa['camarero'] ?? 'Nadie'); ?></p>
            <p>¿Seguro que quieres liberar esta mesa?</p>

            <?php if ($error): ?>
                <div style="color:red; font-weight:bold; margin-bottom: 15px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="mesa_id" value="<?php echo $id_mesa; ?>">
                <button type="submit" name="confirmar" value="1" class="btn-danger">Sí, liberar</button>
                <a href="./../PUBLIC/SALAS/comedor1.php" class="btn-volver">Cancelar</a>
            </form>
        </div>
    </main>
</div>
</body>
</html>
