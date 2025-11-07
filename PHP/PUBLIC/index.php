<?php
session_start();

// Comprobar si el usuario está logueado correctamente
if (isset($_SESSION['loginok']) && $_SESSION['loginok'] === true && isset($_SESSION['username'])) {
    $nombre = htmlspecialchars($_SESSION['nombre']);
    $username = htmlspecialchars($_SESSION['username']);
    $rol = $_SESSION['rol'] ?? 1; // 1=camarero, 2=admin
    
} else {
    // Redirección al login
    header("Location: login.php");
    exit();
}

// ----------------------------------------------------------------------------------
// LÓGICA DE SIMULACIÓN DE ESTADÍSTICAS
// ----------------------------------------------------------------------------------

// 1. Definición de la lista de archivos de salas (Basado en tu estructura de carpetas)
$archivos_sala = [
    'comedor1.php', 'comedor2.php', 
    'sprivada1.php', 'sprivada2.php', 'sprivada3.php', 'sprivada4.php', 
    'terraza1.php', 'terraza2.php', 'terraza3.php'
];

// Función para limpiar el nombre de archivo y obtener el nombre de la sala para mostrar
function obtenerNombreSala($filename) {
    $name = str_replace('.php', '', $filename);
    $name = str_replace('sprivada', 'Sala Privada ', $name);
    $name = str_replace('terraza', 'Terraza ', $name);
    $name = str_replace('comedor', 'Comedor ', $name);
    return ucwords(trim($name));
}

// 2. Datos simulados de ocupación (Se generan datos para TODAS las salas)
$datos_simulados_ocupacion = [
    'Comedor 1'      => ['ocupacion_pct' => 50,  'mesas_ocupadas' => 2, 'total_mesas' => 4],
    'Comedor 2'      => ['ocupacion_pct' => 25,  'mesas_ocupadas' => 1, 'total_mesas' => 4],
    'Sala Privada 1' => ['ocupacion_pct' => 100, 'mesas_ocupadas' => 4, 'total_mesas' => 4],
    'Sala Privada 2' => ['ocupacion_pct' => 0,   'mesas_ocupadas' => 0, 'total_mesas' => 4],
    'Sala Privada 3' => ['ocupacion_pct' => 10,  'mesas_ocupadas' => 1, 'total_mesas' => 10], 
    'Sala Privada 4' => ['ocupacion_pct' => 0,   'mesas_ocupadas' => 0, 'total_mesas' => 4],
    'Terraza 1'      => ['ocupacion_pct' => 75,  'mesas_ocupadas' => 3, 'total_mesas' => 4],
    'Terraza 2'      => ['ocupacion_pct' => 25,  'mesas_ocupadas' => 1, 'total_mesas' => 4],
    'Terraza 3'      => ['ocupacion_pct' => 40,  'mesas_ocupadas' => 2, 'total_mesas' => 5],
];


$ocupacion_salas = [];
$total_mesas = 0;
$mesas_ocupadas = 0;
$total_sillas = 160; 

foreach ($archivos_sala as $file) {
    $nombre_sala = obtenerNombreSala($file);
    // Recuperar datos simulados o usar valores por defecto (0% ocupación)
    $data = $datos_simulados_ocupacion[$nombre_sala] ?? ['ocupacion_pct' => 0, 'mesas_ocupadas' => 0, 'total_mesas' => 4];
    
    $ocupacion_salas[] = array_merge($data, ['sala' => $nombre_sala, 'file' => $file]);

    // Recalcular los totales principales en base a los datos simulados de sala
    $total_mesas += $data['total_mesas'];
    $mesas_ocupadas += $data['mesas_ocupadas'];
}

// Recalcular los stats principales basados en el nuevo cálculo
$stats = [
    'total_mesas' => $total_mesas,
    'mesas_ocupadas' => $mesas_ocupadas,
    'mesas_libres' => $total_mesas - $mesas_ocupadas,
    'total_sillas' => $total_sillas, 
    'sillas_ocupadas' => 50, 
    'sillas_libres' => $total_sillas - 50,
];

// Ordenar para mostrar más ocupadas primero
usort($ocupacion_salas, fn($a, $b) => $b['ocupacion_pct'] <=> $a['ocupacion_pct']);

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
    
    <!-- 🔹 INCLUIR HEADER / BARRA DE NAVEGACIÓN (Ruta Corregida) -->
    <?php 
        // ¡CORRECCIÓN DE RUTA! Como header.php está en la misma carpeta PUBLIC/, se incluye directamente.
        include 'header.php'; 
    ?>

    <!-- 🔹 CONTENIDO PRINCIPAL: DASHBOARD -->
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
                <a href="./MESAS/<?= htmlspecialchars($sala['file']) ?>" class="sala-card-link">
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