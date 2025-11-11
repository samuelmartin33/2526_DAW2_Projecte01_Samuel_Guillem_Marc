<?php
$hora = date('H');
if ($hora >= 6 && $hora < 12) {
    $saludo = "Buenos días";
} elseif ($hora >= 12 && $hora < 20) {
    $saludo = "Buenas tardes";
} else {
    $saludo = "Buenas noches";
}
?>

<nav class="main-header">
    <div class="header-logo">
        <a href="../index.php">
            <!-- RUTA CORREGIDA: sube a php/, sube a restaurante/, entra en img/ -->
            <img src="../../../img/basic_logo_blanco.png" alt="Logo GMS">
        </a>
        <div class="logo-text">
            <span class="gms-title">CASA GMS</span>
           
        </div>
    </div>

    <!-- Saludo dinámico -->
    <div class="header-greeting">
        <?= $saludo ?> <span class="username-tag"><?= $username ?></span>
    </div>

    <!-- Menú de navegación (Opciones de camarero) -->
    <div class="header-menu">
        <a href="../index.php" class="nav-link">
            <i class="fa-solid fa-house"></i> Inicio
        </a>
        <a href="../historico.php" class="nav-link">
            <i class="fa-solid fa-chart-bar"></i> Histórico
        </a>
        <?php if ($rol == 2): ?>
            <a href="admin_panel.php" class="nav-link">
                <i class="fa-solid fa-gear"></i> Admin
            </a>
        <?php endif; ?>
    </div>

    <!-- Botón de Cerrar Sesión -->
    <form method="post" action="../../PROCEDIMIENTOS/logout.php">
        <button type="submit" class="logout-btn">
            <i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión
        </button>
    </form>
</nav>