<?php
// Iniciar la sesión PRIMERO
session_start();

// --- RUTA A CONEXION ---
require_once '../../CONEXION/conexion.php'; // Usa tu $conn

// --- Simulación de inicio de sesión (PARA PRUEBAS) ---
if (!isset($_SESSION['user_id'])) {
     $_SESSION['user_id'] = 1; // ID de 'camarero1'
     $_SESSION['username'] = 'camarero1';
     $_SESSION['rol'] = 1; // Rol 1 = camarero
}

// --- Definimos las variables ANTES de incluir el header ---
$username = $_SESSION['username'] ?? 'Invitado';
$rol = $_SESSION['rol'] ?? 0;
$saludo = "Buenos días"; // Puedes añadir lógica de hora aquí

// --- RUTA A HEADER.PHP ---
require_once '../header.php';
// --- FIN DE CORRECCIÓN ---


// --- Lógica de la página ---
$id_sala_actual = 2; // <-- CAMBIO (Terraza 2 es ID 2)
$nombre_sala_actual = "Terraza 2"; // <-- CAMBIO

try {
    $stmt_salas = $conn->query("SELECT id, nombre FROM salas");
    $salas = $stmt_salas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar las salas: " . $e->getMessage());
}

try {
    // La consulta SQL es la misma, solo cambia el :id_sala_actual
    $sql = "
        SELECT 
            m.id AS mesa_id,
            m.nombre AS mesa_nombre,
            m.sillas AS mesa_sillas,
            m.estado AS mesa_estado,
            m.asignado_por AS camarero_id,
            u.username AS camarero_username
        FROM 
            mesas m
        LEFT JOIN 
            users u ON m.asignado_por = u.id
        WHERE 
            m.id_sala = :id_sala_actual
    ";
    $stmt_mesas = $conn->prepare($sql);
    $stmt_mesas->execute(['id_sala_actual' => $id_sala_actual]);
    $mesas = $stmt_mesas->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error al cargar las mesas: " . $e->getMessage());
}

$id_camarero_logueado = $_SESSION['user_id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($nombre_sala_actual); ?> - Casa GMS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="../../../css/panel_principal.css"> 
    <link rel="stylesheet" href="../../../css/salas_general.css">
    <link rel="stylesheet" href="../../../css/terraza2.css"> </head>
<body>

    <div class="sala-container">

        <main class="sala-layout terraza2"> <?php foreach ($mesas as $mesa): ?>
                <?php $estado_clase = ($mesa['mesa_estado'] == 2) ? 'ocupada' : 'libre'; ?>
                
                <div 
                    class="mesa <?php echo $estado_clase; ?>" 
                    id="mesa-<?php echo $mesa['mesa_id']; ?>" 
                    data-mesa-id="<?php echo $mesa['mesa_id']; ?>"
                >
                    <img src="../../../img/mesa1.png" alt="Mesa" class="mesa-img">
                    
                    <div class="mesa-sillas">
                        <i class="fa-solid fa-chair"></i> <?php echo $mesa['mesa_sillas']; ?>
                    </div>

                    <span class="mesa-label"><?php echo htmlspecialchars($mesa['mesa_nombre']); ?></span>

                </div>
            <?php endforeach; ?>

        </main>

        <aside class="salas-navigation">
            <?php foreach ($salas as $sala): ?>
                <?php
                    $clase_activa = ($sala['id'] == $id_sala_actual) ? 'active' : '';
                    $url = strtolower(str_replace(' ', '', $sala['nombre'])) . ".php"; 
                ?>
                <a href="<?php echo $url; ?>" class="sala-nav-link <?php echo $clase_activa; ?>">
                    <?php echo htmlspecialchars($sala['nombre']); ?>
                </a>
            <?php endforeach; ?>
        </aside>

    </div>


    <div id="modal-gestion-mesa" class="modal-backdrop">
        <div class="modal-content">
            
            <div class="modal-header">
                <div>
                    <h2 id="modal-title" class="modal-title">Cargando...</h2>
                    <span id="modal-status" class="modal-status">...</span>
                </div>
                <button id="modal-close-btn" class="modal-close-btn">&times;</button>
            </div>

            <div class="modal-body">
                
                <div class="info-grupo">
                    <span class="info-label">Capacidad:</span>
                    <span id="modal-capacidad" class="info-value">...</span>
                </div>

                <div id="modal-info-ocupada" style="display: none;">
                    <div class="info-grupo">
                        <span class="info-label">Asignada por:</span>
                        <span id="modal-camarero" class_name="info-value">...</span>
                    </div>
                </div>

                <form id="form-asignar-mesa" style="display: none;">
                    <input type="hidden" id="hidden-mesa-id" value="">
                    <input type="hidden" id="hidden-camarero-id" value="<?php echo $id_camarero_logueado; ?>">
                    
                    <div class="form-grupo">
                        <label for="num-comensales" class="form-label">Número de Comensales</label>
                        <input type="number" id="num-comensales" class="form-input" min="1" max="99" required>
                    </div>

                    <div class="modal-actions">
                        <button type="submit" id="btn-asignar" class="modal-btn btn-primary">Asignar Mesa</button>
                    </div>
                </form>

                <div id="modal-acciones-ocupada" class="modal-actions" style="display: none;">
                    <button id="btn-desasignar" class="modal-btn btn-danger">Poner como Libre (Desasignar)</button>
                </div>

                <p id="modal-error-message"></p>
            </div>

        </div>
    </div>

    <script src="../JS/salas.js"></script>

</body>
</html>