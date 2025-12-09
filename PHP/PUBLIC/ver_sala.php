<?php
session_start();
require_once '../CONEXION/conexion.php';

// Verificación de sesión
if (!isset($_SESSION['loginok']) || $_SESSION['loginok'] !== true) {
    header("Location: login.php");
    exit();
}

// Obtener ID de sala
$id_sala = intval($_GET['id'] ?? 0);

if ($id_sala <= 0) {
    header("Location: selector_salas.php");
    exit();
}

// Variables para header
$nombre = htmlspecialchars($_SESSION['nombre'] ?? $_SESSION['username']);
$username = htmlspecialchars($_SESSION['username']);
$rol = $_SESSION['rol'] ?? 1;
$saludo = "Buenos días";
$id_camarero = $_SESSION['id_usuario'] ?? 0;

// Obtener información de la sala
try {
    $stmt = $conn->prepare("SELECT id, nombre, imagen_fondo, imagen_mesa FROM salas WHERE id = :id");
    $stmt->execute(['id' => $id_sala]);
    $sala = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$sala) {
        header("Location: selector_salas.php?error=sala_not_found");
        exit();
    }
} catch (PDOException $e) {
    die("Error al cargar sala: " . $e->getMessage());
}

// Obtener mesas de la sala
try {
    $stmt = $conn->prepare("
        SELECT 
            m.id,
            m.nombre,
            m.sillas,
            m.estado,
            m.asignado_por,
            u.username as camarero_nombre,
            u.nombre as camarero_nombre_real,
            o.inicio_ocupacion
        FROM mesas m
        LEFT JOIN users u ON m.asignado_por = u.id
        LEFT JOIN ocupaciones o ON m.id = o.id_mesa AND o.final_ocupacion IS NULL
        WHERE m.id_sala = :sala
        ORDER BY m.id ASC
    ");
    $stmt->execute(['sala' => $id_sala]);
    $mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar mesas: " . $e->getMessage());
}

// Obtener todas las salas para el selector
try {
    $stmt_salas = $conn->query("SELECT id, nombre FROM salas ORDER BY nombre ASC");
    $todas_salas = $stmt_salas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $todas_salas = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($sala['nombre']) ?> - Casa GMS</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="../../css/header.css">
    <link rel="stylesheet" href="../../css/panel_principal.css">
    <link rel="stylesheet" href="../../css/ver_sala.css">
    <link rel="icon" type="image/png" href="../../img/icono.png">
    
    <style>
        /* Estilos dinámicos para la sala actual */
        .sala-layout {
            background-image: url('<?= $sala['imagen_fondo'] ? '../../img/salas/fondos/' . htmlspecialchars($sala['imagen_fondo']) : '../../img/fondo_panel_principal.png' ?>');
        }
        
        .mesa-img {
            content: url('<?= $sala['imagen_mesa'] ? '../../img/salas/mesas/' . htmlspecialchars($sala['imagen_mesa']) : '../../img/mesa2.png' ?>');
        }
    </style>
</head>
<body>

    <?php require_once 'header.php'; ?>

    <div class="sala-container">
        <div class="sala-header">
            <div class="sala-title-section">
                <a href="selector_salas.php" class="btn-back">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h1 class="sala-title"><?= htmlspecialchars($sala['nombre']) ?></h1>
            </div>
            
            <!-- Dropdown selector de salas -->
            <div class="dropdown">
                <button class="btn btn-salas dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-layer-group"></i> Cambiar Sala
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <?php foreach ($todas_salas as $s): ?>
                        <li>
                            <a class="dropdown-item <?= $s['id'] == $id_sala ? 'active' : '' ?>" 
                               href="ver_sala.php?id=<?= $s['id'] ?>">
                                <?= htmlspecialchars($s['nombre']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <main class="sala-layout">
            <?php if (empty($mesas)): ?>
                <div class="no-mesas">
                    <i class="fa-solid fa-circle-info"></i>
                    <p>Esta sala no tiene mesas asignadas</p>
                    <?php if ($rol == 2): ?>
                        <a href="gestion_mesas.php?sala=<?= $id_sala ?>" class="btn-crear">
                            <i class="fa-solid fa-plus"></i> Añadir Mesas
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="mesas-grid">
                    <?php foreach ($mesas as $mesa): ?>
                        <?php 
                            $clase_estado = $mesa['estado'] == 2 ? 'ocupada' : 'libre';
                            $es_mi_mesa = ($mesa['asignado_por'] == $id_camarero);
                        ?>
                        <div class="mesa-card <?= $clase_estado ?>" 
                             data-mesa-id="<?= $mesa['id'] ?>"
                             data-mesa-nombre="<?= htmlspecialchars($mesa['nombre']) ?>"
                             data-mesa-sillas="<?= $mesa['sillas'] ?>"
                             data-mesa-estado="<?= $mesa['estado'] ?>"
                             data-mesa-camarero="<?= htmlspecialchars($mesa['camarero_nombre_real'] ?? $mesa['camarero_nombre'] ?? '') ?>"
                             data-mesa-asignado-por="<?= $mesa['asignado_por'] ?? '' ?>"
                             data-mesa-hora-ocupacion="<?= $mesa['inicio_ocupacion'] ?? '' ?>"
                             data-id-camarero="<?= $id_camarero ?>"
                             data-sala-id="<?= $id_sala ?>"
                             onclick="mostrarInfoMesa(this)">
                            
                            <img src="<?= $sala['imagen_mesa'] ? '../../img/salas/mesas/' . htmlspecialchars($sala['imagen_mesa']) : '../../img/mesa2.png' ?>" 
                                 alt="Mesa" 
                                 class="mesa-img">
                            
                            <span class="mesa-label"><?= htmlspecialchars($mesa['nombre']) ?></span>
                            
                            <div class="mesa-sillas">
                                <i class="fa-solid fa-chair"></i> <?= $mesa['sillas'] ?>
                            </div>
                            
                            <?php if ($mesa['estado'] == 2): ?>
                                <div class="mesa-camarero">
                                    <i class="fa-solid fa-user"></i>
                                    <?= htmlspecialchars($mesa['camarero_nombre_real'] ?? $mesa['camarero_nombre'] ?? 'N/A') ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mesa-estado-badge">
                                <?= $clase_estado == 'libre' ? '<i class="fa-solid fa-check"></i> Libre' : '<i class="fa-solid fa-utensils"></i> Ocupada' ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Scripts separados para SweetAlert -->
    <script src="../../JS/ver_sala.js"></script>
    <script src="../../JS/asignar_mesa.js"></script>
    <script src="../../JS/liberar_mesa.js"></script>

</body>
</html>
