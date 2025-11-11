<?php
session_start();
require_once '../../CONEXION/conexion.php';

// Verificación de sesión
if (!isset($_SESSION['loginok']) || $_SESSION['loginok'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Recuperar username desde la sesión
$username = $_SESSION['username'];

// Consultar el ID del camarero correspondiente
$stmt = $conn->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
$stmt->execute([':username' => $username]);
$camarero = $stmt->fetch(PDO::FETCH_ASSOC);

// Si no se encuentra, forzar logout por seguridad
if (!$camarero) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}

// Guardar el id para usarlo en operaciones
$id_camarero = $camarero['id'];

$nombre = htmlspecialchars($_SESSION['nombre']);

// Sala Comedor 1
$id_sala_actual = 4;
$nombre_sala = "Comedor 1";

// Obtener mesas
$stmt = $conn->prepare("
    SELECT m.*, u.username AS camarero
    FROM mesas m
    LEFT JOIN users u ON m.asignado_por = u.id
    WHERE m.id_sala = :sala
");
$stmt->execute(['sala' => $id_sala_actual]);
$mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $nombre_sala; ?> - Casa GMS</title>
    <link rel="stylesheet" href="../../../css/panel_principal.css">
    <link rel="stylesheet" href="../../../css/salas_general.css">
    <link rel="stylesheet" href="../../../css/comedor1.css">
</head>
<body>
<div class="sala-container">
    <main class="sala-layout comedor1">
        <?php foreach ($mesas as $mesa): ?>
            <?php 
                $clase = $mesa['estado'] == 2 ? 'ocupada' : 'libre';
                $accion = $mesa['estado'] == 1 ? './../../PROCEDIMIENTOS/asignar_mesa.php' : './../../PROCEDIMIENTOS/liberar_mesa.php';
            ?>
            <form action="<?php echo $accion; ?>" method="POST" class="mesa-form" style="display:inline;">
                <input type="hidden" name="mesa_id" value="<?php echo $mesa['id']; ?>">
                <button type="submit" class="mesa <?php echo $clase; ?>" id="mesa-<?php echo $mesa['id']; ?>">
                    <img src="../../../img/mesa2.png" alt="Mesa">
                    <span class="mesa-label"><?php echo htmlspecialchars($mesa['nombre']); ?></span>
                    <div class="mesa-sillas"><i class="fa-solid fa-chair"></i> <?php echo $mesa['sillas']; ?></div>
                    <?php if ($mesa['estado'] == 2): ?>
                        <div class="mesa-camarero">
                            Asignada: <?php echo htmlspecialchars($mesa['camarero'] ?? 'Nadie'); ?>
                        </div>
                    <?php endif; ?>
                </button>
            </form>
        <?php endforeach; ?>
    </main>
</div>
</body>
</html>
