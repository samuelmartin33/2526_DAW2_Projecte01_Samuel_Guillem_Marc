<?php
session_start();
require_once '../CONEXION/conexion.php';

// --- VERIFICACIÓN DE SEGURIDAD (SOLO ADMIN) ---
// 1. Que esté logueado
if (!isset($_SESSION['loginok']) || $_SESSION['loginok'] !== true) {
    header("Location: login.php");
    exit();
}
// 2. Que tenga rol 2 (Admin)
$rol = $_SESSION['rol'] ?? 1;
if ($rol != 2) {
    // Si intenta entrar un camarero, lo mandamos al index normal
    header("Location: index.php");
    exit();
}

// Variables de sesión para el header
$nombre = htmlspecialchars($_SESSION['nombre'] ?? $_SESSION['username']);
$username = htmlspecialchars($_SESSION['username']);

// --- CONSULTAS DE ESTADÍSTICAS BÁSICAS ---
try {
    // Contar usuarios
    $total_users = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
    
    // Contar salas
    $total_salas = $conn->query("SELECT COUNT(*) FROM salas")->fetchColumn();
    
    // Contar mesas totales
    $total_mesas = $conn->query("SELECT COUNT(*) FROM mesas")->fetchColumn();

} catch (PDOException $e) {
    $error_msg = "Error al cargar estadísticas: " . $e->getMessage();
}

// Saludo para header
$hora = date('H');
if ($hora >= 6 && $hora < 12) { $saludo = "Buenos días"; } 
elseif ($hora >= 12 && $hora < 20) { $saludo = "Buenas tardes"; } 
else { $saludo = "Buenas noches"; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador - Casa GMS</title>
    
    <!-- Fuentes y Estilos -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Reutilizamos tus estilos existentes -->
    <link rel="stylesheet" href="../../css/header.css">
    <link rel="stylesheet" href="../../css/panel_principal.css">
    <link rel="icon" type="image/png" href="../../img/icono.png">

    <!-- Estilos específicos para las tarjetas de administración -->
    <style>
        /* Estilos para las tarjetas de gestión (botones grandes) */
        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .admin-card {
            background-color: white;
            border-radius: 15px;
            padding: 40px 30px;
            text-align: center;
            text-decoration: none;
            color: #333;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            border-color: #ce4535;
        }

        .admin-card i {
            font-size: 3.5rem;
            color: #ce4535;
            margin-bottom: 10px;
        }

        .admin-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .admin-card p {
            font-size: 0.95rem;
            color: #666;
            margin: 0;
        }
        
        .admin-section {
            margin-top: 50px;
            border-top: 1px solid rgba(0,0,0,0.1);
            padding-top: 40px;
        }
    </style>
</head>
<body>

    <!-- Incluir Header Global -->
    <?php require_once 'header.php'; ?>

    <div class="container">
        
        <!-- Título -->
        <h1 class="dashboard-title">Panel de Administración</h1>

        <!-- 1. Estadísticas Rápidas (Reutilizando estilos de stat-card) -->
        <div class="stats-grid">
            <!-- Usuarios -->
            <div class="stat-card primary" style="color: #2c3e50;">
                <div class="stat-value"><?= $total_users ?></div>
                <div class="stat-label">Usuarios Registrados</div>
                <i class="stat-icon fa-solid fa-users"></i>
            </div>
            
            <!-- Salas -->
            <div class="stat-card warning" style="color: #e67e22;">
                <div class="stat-value"><?= $total_salas ?></div>
                <div class="stat-label">Salas Activas</div>
                <i class="stat-icon fa-solid fa-door-open"></i>
            </div>

            <!-- Mesas -->
            <div class="stat-card success" style="color: #27ae60;">
                <div class="stat-value"><?= $total_mesas ?></div>
                <div class="stat-label">Mesas Totales</div>
                <i class="stat-icon fa-solid fa-chair"></i>
            </div>
        </div>

        <!-- 2. Menú de Gestión (CRUDs) -->
        <div class="admin-section">
            <h2 class="section-title">Herramientas de Gestión</h2>
            
            <div class="admin-grid">
                
                <!-- Opción 1: Usuarios -->
                <a href="gestion_usuarios.php" class="admin-card">
                    <i class="fa-solid fa-user-gear"></i>
                    <h3>Gestionar Usuarios</h3>
                    <p>Crear, editar y eliminar camareros y clientes.</p>
                </a>

                <!-- Opción 2: Salas -->
                <a href="gestion_salas.php" class="admin-card">
                    <i class="fa-solid fa-shop"></i>
                    <h3>Gestionar Salas</h3>
                    <p>Administrar zonas, terrazas y comedores.</p>
                </a>

                <!-- Opción 3: Mesas -->
                <a href="gestion_mesas.php" class="admin-card">
                    <i class="fa-solid fa-utensils"></i>
                    <h3>Gestionar Mesas</h3>
                    <p>Configurar mesas, sillas y distribución.</p>
                </a>

            </div>
        </div>

    </div>

</body>
</html>