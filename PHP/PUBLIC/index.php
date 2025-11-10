<?php
session_start();
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

// ----------------------------------------------------------------------------------
// CONSULTAS A LA BASE DE DATOS
// ----------------------------------------------------------------------------------

try {
    // Obtener salas con número de mesas y mesas ocupadas
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

    $ocupacion_salas = [];
    $total_mesas = 0;
    $mesas_ocupadas = 0;
    $total_sillas = 0;
    $sillas_ocupadas = 0;

    foreach ($salas as $s) {
        $total_mesas += $s['total_mesas'];
        $mesas_ocupadas += $s['mesas_ocupadas'];
        $ocupacion_pct = $s['total_mesas'] > 0 ? round(($s['mesas_ocupadas'] / $s['total_mesas']) * 100) : 0;

        // Sillas totales y ocupadas por sala
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

        $ocupacion_salas[] = [
            'sala' => $s['sala_nombre'],
            'file' => strtolower(str_replace(' ', '', $s['sala_nombre'])) . '.php',
            'ocupacion_pct' => $ocupacion_pct,
            'mesas_ocupadas' => $s['mesas_ocupadas'],
            'total_mesas' => $s['total_mesas']
        ];
    }

    $stats = [
        'total_mesas' => $total_mesas,
        'mesas_ocupadas' => $mesas_ocupadas,
        'mesas_libres' => $total_mesas - $mesas_ocupadas,
        'total_sillas' => $total_sillas,
        'sillas_ocupadas' => $sillas_ocupadas,
        'sillas_libres' => $total_sillas - $sillas_ocupadas,
    ];

    usort($ocupacion_salas, fn($a, $b) => $b['ocupacion_pct'] <=> $a['ocupacion_pct']);
    
} catch (PDOException $e) {
    die("Error al obtener los datos: " . $e->getMessage());
}

// Saludo dinámico (necesario para header.php)
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
    
    <!-- Fuente Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- CSS para el Panel Principal (Ruta corregida: sube a PUBLIC/, sube a RESTAURANTE/, entra en css/) -->
    <link rel="stylesheet" href="../../css/panel_principal.css">
</head>
<body>
    
    <!--  INCLUIR HEADER / BARRA DE NAVEGACIÓN (Ruta Corregida) -->
    <?php 
        // ¡CORRECCIÓN DE RUTA! Como header.php está en la misma carpeta PUBLIC/, se incluye directamente.
        include 'header.php'; 
    ?>

    <!--  CONTENIDO PRINCIPAL: DASHBOARD -->
    <div class="container">
        
        <h1 class="dashboard-title">Resumen de Ocupación Hoy</h1>

        <!-- WIDGETS PRINCIPALES (3 columnas, sin Reservas) -->
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
        
        <!-- OCUPACIÓN POR SALA (Enlaces Clicables) -->
        <h2 class="section-title">Salas del Restaurante (Click para ver mesas)</h2>
        <div class="salas-grid">
            <?php foreach ($ocupacion_salas as $sala): ?>
                <?php
                    // Determinación de la clase de color basada en la ocupación
                    $color_class = 'bg-neutral-100'; // Por defecto, gris/neutro (0% ocupación)
                    if ($sala['ocupacion_pct'] >= 75) {
                        $color_class = 'bg-red-100';
                    } elseif ($sala['ocupacion_pct'] > 0) {
                        $color_class = 'bg-yellow-100';
                    }
                    
                    // Determinar el color de la barra
                    $bar_color = '#27ae60'; // Verde
                    if ($sala['ocupacion_pct'] >= 75) {
                        $bar_color = '#e74c3c'; // Rojo
                    } elseif ($sala['ocupacion_pct'] > 0) {
                        $bar_color = '#f39c12'; // Amarillo
                    }
                ?>
                <!-- Tarjeta como enlace a la página de la sala -->
                <a href="./SALAS/<?= htmlspecialchars($sala['file']) ?>" class="sala-card-link">
                    <div class="sala-card <?= $color_class ?>">
                        <h3 class="sala-name"><?= htmlspecialchars($sala['sala']) ?></h3>
                        <div class="sala-occupancy">
                            <?php if ($sala['mesas_ocupadas'] == 0): ?>
                                TODAS LIBRES (<?= $sala['total_mesas'] ?> Mesas)
                            <?php else: ?>
                                <?= $sala['mesas_ocupadas'] ?> / <?= $sala['total_mesas'] ?> Mesas Ocupadas
                            <?php endif; ?>
                        </div>
                        
                        <div class="progress-bar-container">
                            <div 
                                class="progress-bar" 
                                style="width: <?= $sala['ocupacion_pct'] ?>%; 
                                       background-color: <?= $bar_color ?>;">
                            </div>
                        </div>
                        <div class="percentage"><?= $sala['ocupacion_pct'] ?>% Ocupación</div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

    </div>

</body>
</html>