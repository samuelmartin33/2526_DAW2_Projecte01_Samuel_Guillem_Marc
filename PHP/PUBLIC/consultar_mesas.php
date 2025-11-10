<?php
session_start();
require_once __DIR__ . '/../CONEXION/conexion.php';

// Si no está logueado, redirigir
if (!isset($_SESSION['loginok']) || $_SESSION['loginok'] !== true) {
    header("Location: ../PUBLIC/login.php");
    exit();
}

$msg = "";
$salas = [];
// $mesas = [];

// Cargar salas
try {
    $stmt = $conn->query("SELECT id, nombre FROM salas ORDER BY nombre ASC");
    $salas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar salas: " . $e->getMessage());
}


// // Si se envía el formulario de asignación
// if (isset($_POST['asignar'])) {
//     $id_camarero = $_SESSION['id_usuario'];
//     $id_sala = intval($_POST['sala']);
//     $id_mesa = intval($_POST['mesa']);
//     $num_comensales = intval($_POST['num_comensales']);

//     if ($id_sala && $id_mesa && $num_comensales > 0) {
//         try {
//             $conn->beginTransaction();

//             // Verificar que la mesa sigue libre
//             $check = $conn->prepare("SELECT estado FROM mesas WHERE id = :mesa FOR UPDATE");
//             $check->execute([':mesa' => $id_mesa]);
//             $mesa = $check->fetch(PDO::FETCH_ASSOC);

//             if (!$mesa || $mesa['estado'] != 1) {
//                 throw new Exception("La mesa ya no está disponible.");
//             }

//             // Cambiar estado a ocupada
//             $update = $conn->prepare("UPDATE mesas SET estado = 2 WHERE id = :mesa");
//             $update->execute([':mesa' => $id_mesa]);

//             // Insertar ocupación
//             $insert = $conn->prepare("
//                 INSERT INTO ocupaciones (id_camarero, id_sala, id_mesa, inicio_ocupacion, num_comensales)
//                 VALUES (:camarero, :sala, :mesa, NOW(), :num)
//             ");
//             $insert->execute([
//                 ':camarero' => $id_camarero,
//                 ':sala' => $id_sala,
//                 ':mesa' => $id_mesa,
//                 ':num' => $num_comensales
//             ]);

//             $conn->commit();
//             $msg = "✅ Mesa asignada correctamente.";
//         } catch (Exception $e) {
//             $conn->rollBack();
//             $msg = "❌ Error: " . $e->getMessage();
//         }
//     } else {
//         $msg = "⚠️ Debes seleccionar sala, mesa y número de comensales.";
//     }
// }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Mesas</title>
    <link rel="stylesheet" href="../../CSS/login.css">
    <style>
        body { font-family: Arial; background-color: #f4f6f7; }
        nav { background-color: #2c3e50; color: white; display: flex; justify-content: space-between; align-items: center; padding: 10px 30px; }
        nav .menu a { color: white; text-decoration: none; padding: 8px 14px; }
        nav .menu a:hover { background-color: #34495e; border-radius: 5px; }
        .container { max-width: 700px; margin: 60px auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.2); }
        select, input, button { width: 100%; padding: 10px; margin: 10px 0; border-radius: 5px; border: 1px solid #ccc; }
        button { background-color: #27ae60; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #1e8449; }
        .mensaje { text-align: center; font-weight: bold; margin: 15px 0; }
    </style>
</head>
<body>

<nav>
    <div class="logo">🍽️ Restaurante</div>
    <div class="menu">
        <a href="./index.php">Inicio</a>
        <a href="./consultar_estadisticas.php">Consultar Estadísticas</a>
    </div>
    <form method="post" action="../PROCEDIMIENTOS/logout.php">
        <button type="submit">Cerrar sesión</button>
    </form>
</nav>

<div class="container">
    <h2>Asignación de Mesas 🪑</h2>

    <?php if (!empty($msg)): ?>
        <p class="mensaje"><?= htmlspecialchars($msg) ?></p>
    <?php endif; ?>

    <!-- Paso 1: seleccionar sala -->
    <form method="post" action="../PROCEDIMIENTOS/procesar_sala.php">
        <label for="sala">Selecciona una sala:</label>
        <select name="sala" id="sala" required onchange="this.form.submit()">
            <option value="">-- Selecciona --</option>
            <?php foreach ($salas as $s): ?>
                <option value="<?= $s['id'] ?>" <?= (isset($_POST['sala']) && $_POST['sala'] == $s['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
            <button type="submit" name="asignar">Asignar Mesa</button>
        </form>


</div>

</body>
</html>
