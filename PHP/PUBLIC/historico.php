<?php
session_start();
require_once __DIR__ . '/../CONEXION/conexion.php';

// Comprobar sesión
if (!isset($_SESSION['loginok']) || $_SESSION['loginok'] !== true) {
    header("Location: login.php");
    exit();
}

// Variables de sesión
$username = $_SESSION['username'];
$rol = $_SESSION['rol'] ?? 1;

// --- DEFINICIÓN DEL SALUDO DINÁMICO ---
// Obtener la hora actual

$hora = date('H');
if ($hora >= 6 && $hora < 12) {
    $saludo = "Buenos días";
} elseif ($hora >= 12 && $hora < 20) {
    $saludo = "Buenas tardes";
} else {
    $saludo = "Buenas noches";
}

// ---------------------------------------

// --- VARIABLES DE FILTRO ---
$filtro_sala = $_GET['sala'] ?? '';
$filtro_mesa = $_GET['mesa'] ?? '';
$filtro_camarero = $_GET['camarero'] ?? ''; 
$filtro_mes = $_GET['mes'] ?? '';           
$filtro_dia = $_GET['dia'] ?? '';           


// --- OBTENER LISTADOS PARA FILTROS ---
// Salas y mesas existentes
$salas_stmt = $conn->query("SELECT id, nombre FROM salas ORDER BY nombre");
$salas = $salas_stmt->fetchAll(PDO::FETCH_ASSOC);

$mesas_stmt = $conn->query("SELECT id, nombre, id_sala FROM mesas ORDER BY nombre");
$mesas = $mesas_stmt->fetchAll(PDO::FETCH_ASSOC);

// Nuevo: Obtener listado de camareros (usuarios)
$users_stmt = $conn->query("SELECT id, username FROM users ORDER BY username");
$users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

// Listado de meses para el filtro
$meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];


// --- CONSTRUIR CONSULTA HISTÓRICO CON FILTROS ---
$sql = "
    SELECT o.*, s.nombre AS sala_nombre, m.nombre AS mesa_nombre, u.username AS camarero
    FROM ocupaciones o
    JOIN salas s ON o.id_sala = s.id
    JOIN mesas m ON o.id_mesa = m.id
    JOIN users u ON o.id_camarero = u.id
    WHERE 1=1
";

$params = [];

if ($filtro_sala !== '') {
    $sql .= " AND o.id_sala = :sala";
    $params[':sala'] = $filtro_sala;
}
if ($filtro_mesa !== '') {
    $sql .= " AND o.id_mesa = :mesa";
    $params[':mesa'] = $filtro_mesa;
}
if ($filtro_camarero !== '') {
    $sql .= " AND o.id_camarero = :camarero";
    $params[':camarero'] = $filtro_camarero;
}
if ($filtro_mes !== '') {
    $sql .= " AND MONTH(o.inicio_ocupacion) = :mes";
    $params[':mes'] = $filtro_mes;
}
if ($filtro_dia !== '') {
    $sql .= " AND DAY(o.inicio_ocupacion) = :dia";
    $params[':dia'] = $filtro_dia;
}


$sql .= " ORDER BY o.inicio_ocupacion DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$ocupaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Histórico de Ocupaciones</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../../css/historico.css">
</head>
<body>

<header class="main-header">
    <div class="header-logo">
        <a href="./index.php">
            <img src="./../../img/basic_logo_blanco.png" alt="Logo GMS">
        </a>        
        <div class="logo-text">
            <span class="gms-title">CASA GMS</span>
        </div> 
    </div>

    <div class="header-greeting">
        <?= $saludo ?> 
        <span class="username-tag"><?= $username ?></span> 
    </div>

    <div class="header-menu"> 
        <a href="./index.php" class="nav-link"><i class="fa-solid fa-house"></i> Inicio</a>
        <a href="./historico.php" class="nav-link"><i class="fa-solid fa-chart-bar"></i> Histórico</a>
        <?php if ($rol == 2): ?>
            <a href="admin_panel.php" class="nav-link"><i class="fa-solid fa-gear"></i> Admin</a>
        <?php endif; ?>
    </div>

    <div class="logout-section">
        <form method="post" action="./../PROCEDIMIENTOS/logout.php">
            <button type="submit" class="logout-btn">
                <i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión
            </button>
        </form>
    </div>
</header>


<form method="get" action="" class="filter-form">
    <fieldset>
        <legend>Filtros de Búsqueda</legend>
        
        <label for="sala">Sala:</label>
        <select name="sala" id="sala">
            <option value="">Todas</option>
            <?php foreach ($salas as $s): ?>
                <option value="<?= $s['id'] ?>" <?= $filtro_sala == $s['id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['nombre']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="mesa">Mesa:</label>
        <select name="mesa" id="mesa">
            <option value="">Todas</option>
            <?php foreach ($mesas as $m): ?>
                <option value="<?= $m['id'] ?>" <?= $filtro_mesa == $m['id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['nombre']) ?> (Sala <?= $m['id_sala'] ?>)</option>
            <?php endforeach; ?>
        </select>

        <label for="camarero">Camarero:</label>
        <select name="camarero" id="camarero">
            <option value="">Todos</option>
            <?php foreach ($users as $u): ?>
                <option value="<?= $u['id'] ?>" <?= $filtro_camarero == $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['username']) ?></option>
            <?php endforeach; ?>
        </select>

        <br>
        
        <label for="mes">Mes:</label>
        <select name="mes" id="mes">
            <option value="">Todos</option>
            <?php foreach ($meses as $num => $nombre): ?>
                <option value="<?= $num ?>" <?= $filtro_mes == $num ? 'selected' : '' ?>><?= $nombre ?></option>
            <?php endforeach; ?>
        </select>

        <label for="dia">Día:</label>
        <select name="dia" id="dia">
            <option value="">Todos</option>
            <?php for ($d = 1; $d <= 31; $d++): ?>
                <option value="<?= $d ?>" <?= $filtro_dia == $d ? 'selected' : '' ?>><?= $d ?></option>
            <?php endfor; ?>
        </select>
        
        <button type="submit">Filtrar</button>
    </fieldset>
</form>

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>Sala</th>
            <th>Mesa</th>
            <th>Camarero</th>
            <th>Inicio</th>
            <th>Fin</th>
            <th>Duración (min)</th>
            <th>Comensales</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($ocupaciones as $o): ?>
            <tr>
                <td><?= htmlspecialchars($o['sala_nombre']) ?></td>
                <td><?= htmlspecialchars($o['mesa_nombre']) ?></td>
                <td><?= htmlspecialchars($o['camarero']) ?></td>
                <td><?= $o['inicio_ocupacion'] ?></td>
                <td><?= $o['final_ocupacion'] ?? 'En uso' ?></td>
                <td>
                    <?php 
                        if ($o['final_ocupacion'] !== null) {
                            $inicio = new DateTime($o['inicio_ocupacion']);
                            $fin = new DateTime($o['final_ocupacion']);
                            // Calcula la diferencia total en minutos
                            $diff = $inicio->diff($fin);
                            echo $diff->days * 24 * 60 + $diff->h * 60 + $diff->i;
                        } else {
                            echo '-';
                        }
                    ?>
                </td>
                <td><?= $o['num_comensales'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>