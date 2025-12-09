<?php
// Inicia o reanuda la sesión existente
session_start();

// --- Conexión a la BBDD ---
require_once __DIR__ . '/../CONEXION/conexion.php';

// Comprobar si el usuario está logueado correctamente
if (isset($_SESSION['loginok']) && $_SESSION['loginok'] === true && isset($_SESSION['username'])) {
    $nombre = htmlspecialchars($_SESSION['nombre']);
    $username = htmlspecialchars($_SESSION['username']);
    $rol = $_SESSION['rol'] ?? 1;
} else {
    header("Location: login.php");
    exit();
}

// Comprueba si la variable de sesión 'show_welcome_message' existe y es true
if (isset($_SESSION['show_welcome_message']) && $_SESSION['show_welcome_message'] === true) {
    $welcome_data_flag = "true";
    $welcome_data_name = $nombre;
    unset($_SESSION['show_welcome_message']); 
}

// --- CONSULTAS A LA BASE DE DATOS ---
try {
    $sql = "
        SELECT 
            s.id AS id_sala,
            s.nombre AS sala_nombre,
            COUNT(m.id) AS total_mesas,
            SUM(CASE WHEN m.estado = 2 THEN 1 ELSE 0 END) AS mesas_ocupadas
        FROM salas s
        LEFT JOIN mesas m ON s.id = m.id_sala
        GROUP BY s.id
        ORDER BY s.nombre ASC
    ";
    $stmt = $conn->query($sql);
    $salas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_mesas = 0;
    $mesas_ocupadas = 0;
    $total_sillas = 0;
    $sillas_ocupadas = 0;

    foreach ($salas as $s) {
        $total_mesas += $s['total_mesas'];
        $mesas_ocupadas += $s['mesas_ocupadas'];

        $querySillas = $conn->prepare("
            SELECT 
                SUM(sillas) AS total_sillas,
                SUM(CASE WHEN estado = 2 THEN sillas ELSE 0 END) AS sillas_ocupadas
            FROM mesas WHERE id_sala = :id
        ");
        $querySillas->execute([':id' => $s['id_sala']]);
        $sillas = $querySillas->fetch(PDO::FETCH_ASSOC);

        $total_sillas += intval($sillas['total_sillas']);
        $sillas_ocupadas += intval($sillas['sillas_ocupadas']);
    }

    $stats = [
        'total_mesas' => $total_mesas,
        'mesas_ocupadas' => $mesas_ocupadas,
        'mesas_libres' => $total_mesas - $mesas_ocupadas,
        'total_sillas' => $total_sillas,
        'sillas_ocupadas' => $sillas_ocupadas,
        'sillas_libres' => $total_sillas - $sillas_ocupadas,
    ];
    
} catch (PDOException $e) {
    die("Error al obtener los datos: " . $e->getMessage());
}

// Saludo dinámico
$hora = date('H');
if ($hora >= 6 && $hora < 12) {
    $saludo = "Buenos días";
} elseif ($hora >= 12 && $hora < 20) {
    $saludo = "Buenas tardes";
} else {
    $saludo = "Buenas noches";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Principal - Casa GMS</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/panel_principal.css">
    <link rel="icon" type="image/png" href="../../img/icono.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>

<body>
    
    <nav class="main-header">
        <div class="header-logo">
            <img src="../../img/basic_logo_blanco.png" alt="Logo GMS">
            <div class="logo-text">
                <span class="gms-title">CASA GMS</span>
            </div>
        </div>

        <div class="header-greeting">
            <?= $saludo ?> <span class="username-tag"><?= $username ?></span>
        </div>

        <div class="header-menu">
            <a href="./index.php" class="nav-link">
                <i class="fa-solid fa-house"></i> Inicio
            </a>
            <a href="./historico.php" class="nav-link">
                <i class="fa-solid fa-chart-bar"></i> Histórico
            </a>
            <?php if ($rol == 2): ?>
                <a href="./panel_administrador.php" class="nav-link">
                    <i class="fa-solid fa-gear"></i> Admin
                </a>
            <?php endif; ?>
        </div>

        <form method="post" action="../PROCEDIMIENTOS/logout.php">
            <button type="submit" class="logout-btn">
                <i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión
            </button>
        </form>
    </nav>

    <div class="container">
        
        <h1 class="dashboard-title">Resumen de Ocupación Hoy</h1>

        <div class="stats-grid">
            
            <div class="stat-card primary">
                <div class="stat-value"><?= $stats['mesas_libres'] ?> / <?= $stats['total_mesas'] ?></div>
                <div class="stat-label">Mesas Disponibles</div>
                <i class="stat-icon fa-solid fa-check-circle"></i>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-value"><?= $stats['mesas_ocupadas'] ?></div>
                <div class="stat-label">Mesas Ocupadas</div>
                <i class="stat-icon fa-solid fa-users"></i>
            </div>

            <div class="stat-card success">
                <div class="stat-value"><?= $stats['sillas_ocupadas'] ?> / <?= $stats['total_sillas'] ?></div>
                <div class="stat-label">Sillas Ocupadas (Total)</div>
                <i class="stat-icon fa-solid fa-user-group"></i>
            </div>
        </div>
        
        <div style="margin-top: 50px; text-align: center;">
            <a href="selector_salas.php" class="btn-ver-salas">
                <i class="fa-solid fa-door-open"></i>
                <span>VER SALAS Y MESAS</span>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../JS/mensaje_inicio.js"></script>
    <script src="../../JS/inactivity_timer.js"></script>

</body>
</html>