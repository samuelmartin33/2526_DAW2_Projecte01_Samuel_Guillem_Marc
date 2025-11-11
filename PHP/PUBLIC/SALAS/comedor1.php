<?php
session_start();
require_once '../../CONEXION/conexion.php';

// --- Verificación de sesión ---
if (!isset($_SESSION['loginok']) || $_SESSION['loginok'] !== true) {
    header("Location: ../login.php");
    exit();
}

// --- Recuperar datos del camarero ---
$username = $_SESSION['username'];
$stmt_camarero = $conn->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
$stmt_camarero->execute([':username' => $username]);
$camarero = $stmt_camarero->fetch(PDO::FETCH_ASSOC);

if (!$camarero) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}
$id_camarero = $camarero['id'];

// --- Variables para el Header ---
$nombre = htmlspecialchars($_SESSION['nombre']);
$rol = $_SESSION['rol'] ?? 1;
$saludo = "Buenos días"; // Puedes añadir lógica de hora

// --- IDs de la Sala ---
$id_sala_actual = 4;
$nombre_sala_actual = "Comedor 1";

// --- Consultar Mesas (Tu consulta) ---
$stmt_mesas = $conn->prepare("
    SELECT m.*, u.username AS camarero 
    FROM mesas m
    LEFT JOIN users u ON m.asignado_por = u.id
    WHERE m.id_sala = :sala
");
$stmt_mesas->execute(['sala' => $id_sala_actual]);
$mesas = $stmt_mesas->fetchAll(PDO::FETCH_ASSOC);

// --- Consultar Salas (Para la navegación) ---
try {
    $stmt_salas = $conn->query("SELECT id, nombre FROM salas");
    $salas = $stmt_salas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar las salas: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $nombre_sala_actual; ?> - Casa GMS</title>
    
    <!-- Fuentes y CSS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Tus CSS -->
    <link rel="stylesheet" href="../../../css/panel_principal.css">
    <link rel="stylesheet" href="../../../css/salas_general.css">
    <link rel="stylesheet" href="../../../css/comedor1.css">
</head>
<body>

    <?php 
    // Incluir el header estándar
    require_once '../header.php'; 
    ?>

    <div class="sala-container">
        
        <main class="sala-layout comedor1">
            <?php foreach ($mesas as $mesa): ?>
                <?php 
                    $clase = $mesa['estado'] == 2 ? 'ocupada' : 'libre';
                    // Determina a qué script de PROCEDIMIENTOS enviar
                    $accion = $mesa['estado'] == 1 ? '../../PROCEDIMIENTOS/asignar_mesa.php' : '../../PROCEDIMIENTOS/liberar_mesa.php';
                ?>
                
                <!-- El formulario envuelve el botón de la mesa -->
                <form action="<?php echo $accion; ?>" method="POST" class="mesa-form">
                    <input type="hidden" name="mesa_id" value="<?php echo $mesa['id']; ?>">
                    
                    <!-- El botón es la "carta" de la mesa -->
                    <button type="submit" class="mesa <?php echo $clase; ?>" id="mesa-<?php echo $mesa['id']; ?>">
                        
                        <img src="../../../img/mesa2.png" alt="Mesa" class="mesa-img">
                        
                        <span class="mesa-label"><?php echo htmlspecialchars($mesa['nombre']); ?></span>
                        
                        <div class="mesa-sillas">
                            <i class="fa-solid fa-chair"></i> <?php echo $mesa['sillas']; ?>
                        </div>
                        
                        <?php if ($mesa['estado'] == 2): ?>
                            <div class="mesa-camarero">
                                Asig: <?php echo htmlspecialchars($mesa['camarero'] ?? 'N/A'); ?>
                            </div>
                        <?php endif; ?>

                    </button>
                </form>
            <?php endforeach; ?>
        </main>

        <aside class="salas-navigation">
            <?php foreach ($salas as $sala): ?>
                <?php
                    $clase_activa = ($sala['id'] == $id_sala_actual) ? 'active' : '';
                    $url = strtolower(str_replace(' ', '', $sala['nombre'])) . ".php"; 
                ?>
                <a href="<?php echo $url; ?>" class="sala-nav-link <?php echo $clase_activa; ?>">
                    <?php echo htmlspecialchars($sala['nombre']); ?>
                </a>
            <?php endforeach; ?>
        </aside>

    </div>
</body>
</html>