<?php
session_start();
require_once './../CONEXION/conexion.php';

// --- Verificación de sesión ---
if (!isset($_SESSION['loginok']) || $_SESSION['loginok'] !== true) {
    header("Location: ../PUBLIC/login.php");
    exit();
}
$username = $_SESSION['username'] ?? null;
if (!$username) {
    session_destroy(); header("Location: ../PUBLIC/login.php"); exit();
}

// --- Consultar ID del camarero ---
$stmt_camarero = $conn->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
$stmt_camarero->execute([':username' => $username]);
$camarero = $stmt_camarero->fetch(PDO::FETCH_ASSOC);
if (!$camarero) {
    session_destroy(); header("Location: ../PUBLIC/login.php"); exit();
}
$id_camarero = $camarero['id'];

// --- Variables para el Header ---
$nombre = htmlspecialchars($_SESSION['nombre'] ?? $username);
$rol = $_SESSION['rol'] ?? 1;
// Saludo dinámico
$hora = date('H');
if ($hora >= 6 && $hora < 12) {
    $saludo = "Buenos días";
} elseif ($hora >= 12 && $hora < 20) {
    $saludo = "Buenas tardes";
} else {
    $saludo = "Buenas noches";
}

// --- Obtener Mesa ---
$id_mesa = $_POST['mesa_id'] ?? null;
if (!$id_mesa) {
    header("Location: ./../PUBLIC/SALAS/comedor1.php"); // Redirigir si no hay ID
    exit();
}
$stmt_mesa = $conn->prepare("SELECT * FROM mesas WHERE id = ?");
$stmt_mesa->execute([$id_mesa]);
$mesa = $stmt_mesa->fetch(PDO::FETCH_ASSOC);

if (!$mesa || $mesa['estado'] != 1) {
    die("Mesa no disponible o ya ocupada.");
}

// --- Obtener info de la Sala (para el fondo y la navegación) ---
$id_sala_actual = $mesa['id_sala'];
$stmt_sala_info = $conn->prepare("SELECT nombre FROM salas WHERE id = ?");
$stmt_sala_info->execute([$id_sala_actual]);
$sala_nombre = $stmt_sala_info->fetchColumn();
$sala_css_class = strtolower(str_replace(' ', '', $sala_nombre));
$sala_redirect_url = './../PUBLIC/SALAS/' . $sala_css_class . '.php';

// --- Lógica de Asignación (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['num_comensales'])) {
    $num_comensales = (int)$_POST['num_comensales'];
    $conn->beginTransaction();
    try {
        $update = $conn->prepare("UPDATE mesas SET estado=2, asignado_por=? WHERE id=?");
        $update->execute([$id_camarero, $id_mesa]);

        $insert = $conn->prepare("
            INSERT INTO ocupaciones (id_camarero, id_sala, id_mesa, inicio_ocupacion, num_comensales)
            VALUES (?, ?, ?, NOW(), ?)
        ");
        $insert->execute([$id_camarero, $mesa['id_sala'], $id_mesa, $num_comensales]);

        $conn->commit();
        header("Location: " . $sala_redirect_url); // Redirigir a la sala de origen
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        die("Error: " . $e->getMessage());
    }
}

// --- Consulta para la barra lateral ---
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
    <title>Asignar <?php echo htmlspecialchars($mesa['nombre']); ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" type="image/png" href="../../img/icono.png">
    
    <!-- Cargamos SweetAlert PRIMERO -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- (salas.js no interfiere, lo dejamos) -->
    <script src="../PUBLIC/JS/salas.js"></script>

    <link rel="stylesheet" href="../../css/panel_principal.css">
    <link rel="stylesheet" href="../../css/salas_general.css">
    <link rel="stylesheet" href="../../css/<?php echo $sala_css_class; ?>.css">
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
            <a href="../PUBLIC/index.php" class="nav-link">
                <i class="fa-solid fa-house"></i> Inicio
            </a>
            <a href="../PUBLIC/historico.php" class="nav-link">
                <i class="fa-solid fa-chart-bar"></i> Histórico
            </a>
            <?php if ($rol == 2): ?>
                <a href="../PUBLIC/admin_panel.php" class="nav-link">
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

    <div class="sala-container">
        <main class="sala-layout <?php echo $sala_css_class; ?>">
            
            <div class="interstitial-form">
                <h2>Asignar <?php echo htmlspecialchars($mesa['nombre']); ?></h2>
                <p><strong>Sala:</strong> <?php echo htmlspecialchars($sala_nombre); ?></p>
                <p><strong>Capacidad:</strong> <?php echo $mesa['sillas']; ?> comensales</p>
                
                <!-- El <div> de error que 'validar_asignacion.js' usa se creará aquí -->

                <form method="POST" id="asignar-mesa-form" class="form-full-page">
                    <input type="hidden" name="mesa_id" value="<?php echo $id_mesa; ?>">
                    <label for="num-comensales">Número de comensales:</label>
                    <input type="number" id="num-comensales" name="num_comensales" min="1" max="<?php echo $mesa['sillas']; ?>" >
                    <input type="hidden" id="max-sillas" value="<?php echo (int)$mesa['sillas']; ?>">

                    
                    <div class="form-actions">
                        <button type="submit" id="btn-asignar" class="btn-primary">Asignar Mesa</button>
                        <a href="<?php echo $sala_redirect_url; ?>" class="btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </main>

        <aside class="salas-navigation">
            <?php foreach ($salas as $sala): ?>
                <?php
                    $clase_activa = ($sala['id'] == $id_sala_actual) ? 'active' : '';
                    $nombre_fichero = strtolower(str_replace(' ', '', $sala['nombre']));
                    
                    // Ruta desde PROCEDIMIENTOS/ hasta PUBLIC/SALAS/
                    $url = './../PUBLIC/SALAS/' . $nombre_fichero . ".php"; 
                ?>
                <a href="<?php echo $url; ?>" class="sala-nav-link <?php echo $clase_activa; ?>">
                    <?php echo htmlspecialchars($sala['nombre']); ?>
                </a>
            <?php endforeach; ?>
        </aside>

    </div>
    
    <!-- ===== MODIFICACIÓN AQUÍ ===== -->
    <!-- Cargamos TUS dos scripts, en este orden -->
    <script src="../../JS/validar_asignacion.js"></script>
    <script src="../../JS/alert_asignar.js"></script>
    
</body>
</html>