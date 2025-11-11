<?php
session_start();
require_once './../CONEXION/conexion.php';

$error = ''; // Variable para mostrar errores en el formulario

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
$rol = $_SESSION['rol'] ?? 1; // Necesitamos el rol para permisos


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
    header("Location: ./../PUBLIC/SALAS/comedor1.php"); // Redirigir
    exit();
}

$stmt_mesa = $conn->prepare("
    SELECT m.*, u.username AS camarero, s.nombre AS sala_nombre
    FROM mesas m
    LEFT JOIN users u ON m.asignado_por = u.id
    JOIN salas s ON m.id_sala = s.id
    WHERE m.id = ?
");
$stmt_mesa->execute([$id_mesa]);
$mesa = $stmt_mesa->fetch(PDO::FETCH_ASSOC);

if (!$mesa) {
    $error = "Mesa no encontrada.";
}

// --- Info de la Sala ---
$id_sala_actual = $mesa['id_sala'] ?? 0;
$sala_nombre = $mesa['sala_nombre'] ?? 'Sala Desconocida';
$sala_css_class = strtolower(str_replace(' ', '', $sala_nombre));
$sala_redirect_url = './../PUBLIC/SALAS/' . $sala_css_class . '.php';

// --- Lógica de Liberación (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar']) && !$error) {
    $conn->beginTransaction();
    try {
        // Solo el camarero que asignó O un admin (rol 2) puede liberar
        if ($mesa['asignado_por'] != $id_camarero && $rol != 2) {
            // Asignar el error para mostrarlo en el formulario
            $error = "No puedes liberar una mesa asignada por otro camarero.";
        } else {
            $conn->prepare("UPDATE mesas SET estado=1, asignado_por=NULL WHERE id=?")->execute([$id_mesa]);
            $conn->prepare("
                UPDATE ocupaciones SET final_ocupacion=NOW()
                WHERE id_mesa=? AND final_ocupacion IS NULL
                ORDER BY inicio_ocupacion DESC LIMIT 1
            ")->execute([$id_mesa]);

            $conn->commit();
            header("Location: " . $sala_redirect_url); // Redirigir a la sala de origen
            exit();
        }
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Error al liberar la mesa: " . $e->getMessage();
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
    <title>Liberar <?php echo htmlspecialchars($mesa['nombre'] ?? 'Mesa'); ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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
                <h2>Liberar <?php echo htmlspecialchars($mesa['nombre']); ?></h2>
                <p>Asignada por: <strong><?php echo htmlspecialchars($mesa['camarero'] ?? 'N/A'); ?></strong></p>
                <p>¿Seguro que quieres liberar esta mesa?</p>

                <?php if ($error): ?>
                    <div class="form-error-message">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="form-full-page">
                    <input type="hidden" name="mesa_id" value="<?php echo $id_mesa; ?>">
                    
                    <div class="form-actions">
                        <button type="submit" name="confirmar" value="1" class="btn-danger">Sí, liberar</button>
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
</body>
</html>