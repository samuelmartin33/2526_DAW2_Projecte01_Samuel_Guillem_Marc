<?php
session_start();
require_once '../CONEXION/conexion.php';

// Verificación de sesión
if (!isset($_SESSION['loginok']) || $_SESSION['loginok'] !== true) {
    header("Location: login.php");
    exit();
}

// Variables para header
$nombre = htmlspecialchars($_SESSION['nombre'] ?? $_SESSION['username']);
$username = htmlspecialchars($_SESSION['username']);
$rol = $_SESSION['rol'] ?? 1;
$saludo = "Buenos días";

// Obtener todas las salas con el conteo de mesas
try {
    $stmt = $conn->query("
        SELECT 
            s.id, 
            s.nombre, 
            s.imagen_fondo,
            s.imagen_mesa,
            COUNT(m.id) as total_mesas,
            SUM(CASE WHEN m.estado = 2 THEN 1 ELSE 0 END) as mesas_ocupadas
        FROM salas s
        LEFT JOIN mesas m ON s.id = m.id_sala
        GROUP BY s.id
        ORDER BY s.nombre ASC
    ");
    $salas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar salas: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Sala - Casa GMS</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <link rel="stylesheet" href="../../css/header.css">
    <link rel="stylesheet" href="../../css/panel_principal.css">
    <link rel="stylesheet" href="../../css/selector_salas.css">
    <link rel="icon" type="image/png" href="../../img/icono.png">
</head>
<body>

    <?php require_once 'header.php'; ?>

    <div class="container">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1 class="dashboard-title" style="margin-bottom: 0;">Selecciona una Sala</h1>
            <a href="index.php" class="logout-btn" style="text-decoration: none; background-color: #eee; color: #333;">
                <i class="fa-solid fa-arrow-left"></i> Volver
            </a>
        </div>

        <?php if (empty($salas)): ?>
            <div class="alert" style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 20px; border-radius: 10px; text-align: center;">
                <i class="fa-solid fa-exclamation-triangle"></i>
                <p style="margin: 10px 0 0 0;">No hay salas disponibles. Contacta con el administrador.</p>
            </div>
        <?php else: ?>
            <div class="salas-grid">
                <?php foreach ($salas as $sala): ?>
                    <a href="ver_sala.php?id=<?= $sala['id'] ?>" class="sala-card">
                        <div class="sala-imagen" style="background-image: url('<?= $sala['imagen_fondo'] ? '../../img/salas/fondos/' . htmlspecialchars($sala['imagen_fondo']) : '../../img/fondo_panel_principal.png' ?>');">
                            <div class="sala-overlay">
                                <h3 class="sala-nombre"><?= htmlspecialchars($sala['nombre']) ?></h3>
                            </div>
                        </div>
                        <div class="sala-info">
                            <div class="sala-stat">
                                <i class="fa-solid fa-chair"></i>
                                <span><?= $sala['total_mesas'] ?> mesa<?= $sala['total_mesas'] != 1 ? 's' : '' ?></span>
                            </div>
                            <div class="sala-stat">
                                <?php if ($sala['mesas_ocupadas'] > 0): ?>
                                    <i class="fa-solid fa-circle" style="color: #e74c3c;"></i>
                                    <span><?= $sala['mesas_ocupadas'] ?> ocupada<?= $sala['mesas_ocupadas'] != 1 ? 's' : '' ?></span>
                                <?php else: ?>
                                    <i class="fa-solid fa-circle" style="color: #27ae60;"></i>
                                    <span>Todas libres</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="sala-accion">
                            <span>Ver Sala</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>
