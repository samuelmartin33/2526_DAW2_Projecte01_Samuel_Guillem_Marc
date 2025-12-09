<?php
session_start();
require_once '../CONEXION/conexion.php';

// --- SEGURIDAD: SOLO ADMIN ---
if (!isset($_SESSION['loginok']) || $_SESSION['loginok'] !== true || ($_SESSION['rol'] ?? 1) != 2) {
    header("Location: index.php");
    exit();
}

// Variables header
$nombre = htmlspecialchars($_SESSION['nombre'] ?? $_SESSION['username']);
$username = htmlspecialchars($_SESSION['username']);
$rol = $_SESSION['rol'];
$saludo = "Buenos días";

// --- OBTENER SALAS DE LA BASE DE DATOS ---
$salas = [];
$mensaje = '';
$tipo_mensaje = '';

// Procesar mensajes de operaciones previas
if (isset($_GET['success'])) {
    $tipo_mensaje = 'success';
    switch ($_GET['success']) {
        case 'created':
            $mensaje = 'Sala creada exitosamente.';
            break;
        case 'updated':
            $mensaje = 'Sala actualizada exitosamente.';
            break;
        case 'deleted':
            $mensaje = 'Sala eliminada exitosamente.';
            break;
    }
}

if (isset($_GET['error'])) {
    $tipo_mensaje = 'error';
    switch ($_GET['error']) {
        case 'duplicate_name':
            $mensaje = 'Ya existe una sala con ese nombre.';
            break;
        case 'invalid_data':
            $mensaje = 'Datos inválidos. Por favor verifica la información.';
            break;
        case 'invalid_image':
            $mensaje = 'Formato de imagen inválido. Solo se permiten JPG, JPEG y PNG.';
            break;
        case 'image_too_large':
            $mensaje = 'La imagen es demasiado grande. Máximo 5MB.';
            break;
        case 'upload_failed':
            $mensaje = 'Error al subir la imagen. Intenta nuevamente.';
            break;
        case 'has_tables':
            $mensaje = 'No se puede eliminar la sala porque tiene mesas asignadas.';
            break;
        case 'db_error':
            $mensaje = 'Error de base de datos. Intenta nuevamente.';
            break;
        case 'sala_not_found':
            $mensaje = 'Sala no encontrada.';
            break;
        default:
            $mensaje = 'Ocurrió un error. Intenta nuevamente.';
    }
}

try {
    // Obtener todas las salas con el conteo de mesas
    $stmt = $conn->query("
        SELECT 
            s.id, 
            s.nombre, 
            s.num_mesas,
            s.imagen_fondo,
            s.imagen_mesa,
            COUNT(m.id) as mesas_reales
        FROM salas s
        LEFT JOIN mesas m ON s.id = m.id_sala
        GROUP BY s.id
        ORDER BY s.id ASC
    ");
    $salas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensaje = "Error al cargar salas: " . $e->getMessage();
    $tipo_mensaje = 'error';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Salas - Admin</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <link rel="stylesheet" href="../../css/header.css">
    <link rel="stylesheet" href="../../css/panel_principal.css">
    <link rel="stylesheet" href="../../css/gestion_salas.css">
    <link rel="icon" type="image/png" href="../../img/icono.png">
</head>
<body>

    <?php require_once 'header.php'; ?>

    <div class="container">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1 class="dashboard-title" style="margin-bottom: 0;">Gestión de Salas</h1>
            <a href="panel_administrador.php" class="logout-btn" style="text-decoration: none; background-color: #eee; color: #333;">
                <i class="fa-solid fa-arrow-left"></i> Volver al Panel
            </a>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-<?= $tipo_mensaje ?>">
                <i class="fa-solid fa-<?= $tipo_mensaje === 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <div class="stat-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="color: #333; margin: 0;">Listado de Salas</h2>
                <button class="btn-crear" onclick="abrirModalCrear()">
                    <i class="fa-solid fa-plus"></i> Nueva Sala
                </button>
            </div>

            <div class="table-container">
                <table class="salas-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Mesas</th>
                            <th>Imagen Fondo</th>
                            <th>Imagen Mesa</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($salas)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px; color: #999;">
                                    No hay salas registradas
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($salas as $sala): ?>
                                <tr>
                                    <td><?= htmlspecialchars($sala['id']) ?></td>
                                    <td><strong><?= htmlspecialchars($sala['nombre']) ?></strong></td>
                                    <td><?= htmlspecialchars($sala['mesas_reales']) ?></td>
                                    <td>
                                        <?php if ($sala['imagen_fondo']): ?>
                                            <img src="../../img/salas/fondos/<?= htmlspecialchars($sala['imagen_fondo']) ?>" 
                                                 alt="Fondo" 
                                                 class="imagen-miniatura"
                                                 onclick="ampliarImagen(this.src)">
                                        <?php else: ?>
                                            <span class="sin-imagen">Sin imagen</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($sala['imagen_mesa']): ?>
                                            <img src="../../img/salas/mesas/<?= htmlspecialchars($sala['imagen_mesa']) ?>" 
                                                 alt="Mesa" 
                                                 class="imagen-miniatura"
                                                 onclick="ampliarImagen(this.src)">
                                        <?php else: ?>
                                            <span class="sin-imagen">Sin imagen</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="acciones">
                                        <button class="btn-accion btn-editar" onclick='editarSala(<?= json_encode($sala) ?>)' title="Editar">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button class="btn-accion btn-eliminar" onclick="eliminarSala(<?= $sala['id'] ?>, '<?= htmlspecialchars($sala['nombre']) ?>', <?= $sala['mesas_reales'] ?>)" title="Eliminar">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- MODAL PARA CREAR SALA -->
    <div id="modalCrear" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Crear Nueva Sala</h2>
                <button class="modal-close" onclick="cerrarModalCrear()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formCrear" action="../PROCEDIMIENTOS/procesar_crear_sala.php" method="POST" enctype="multipart/form-data">
                    
                    <div class="form-group">
                        <label for="nuevo_nombre">Nombre de la Sala *</label>
                        <input type="text" name="nombre" id="nuevo_nombre" required placeholder="Ej: Terraza 4, Comedor 3">
                    </div>

                    <div class="form-group">
                        <label for="nueva_imagen_fondo">Imagen de Fondo</label>
                        <input type="file" name="imagen_fondo" id="nueva_imagen_fondo" accept="image/jpeg,image/jpg,image/png" onchange="previewImage(this, 'preview_fondo_nuevo')">
                        <small>Formatos: JPG, JPEG, PNG. Máximo 5MB</small>
                        <div id="preview_fondo_nuevo" class="image-preview"></div>
                    </div>

                    <div class="form-group">
                        <label for="nueva_imagen_mesa">Imagen de Mesa</label>
                        <input type="file" name="imagen_mesa" id="nueva_imagen_mesa" accept="image/jpeg,image/jpg,image/png" onchange="previewImage(this, 'preview_mesa_nuevo')">
                        <small>Formatos: JPG, JPEG, PNG. Máximo 5MB</small>
                        <div id="preview_mesa_nuevo" class="image-preview"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-cancelar" onclick="cerrarModalCrear()">Cancelar</button>
                        <button type="submit" class="btn-confirmar">Crear Sala</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL PARA EDITAR SALA -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Editar Sala</h2>
                <button class="modal-close" onclick="cerrarModalEditar()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formEditar" action="../PROCEDIMIENTOS/procesar_editar_sala.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="sala_id">
                    <input type="hidden" name="imagen_fondo_actual" id="sala_imagen_fondo_actual">
                    <input type="hidden" name="imagen_mesa_actual" id="sala_imagen_mesa_actual">
                    
                    <div class="form-group">
                        <label for="sala_nombre">Nombre de la Sala *</label>
                        <input type="text" name="nombre" id="sala_nombre" required>
                    </div>

                    <div class="form-group">
                        <label for="sala_imagen_fondo">Imagen de Fondo</label>
                        <div id="current_fondo" class="current-image"></div>
                        <input type="file" name="imagen_fondo" id="sala_imagen_fondo" accept="image/jpeg,image/jpg,image/png" onchange="previewImage(this, 'preview_fondo_edit')">
                        <small>Dejar vacío para mantener la imagen actual</small>
                        <div id="preview_fondo_edit" class="image-preview"></div>
                    </div>

                    <div class="form-group">
                        <label for="sala_imagen_mesa">Imagen de Mesa</label>
                        <div id="current_mesa" class="current-image"></div>
                        <input type="file" name="imagen_mesa" id="sala_imagen_mesa" accept="image/jpeg,image/jpg,image/png" onchange="previewImage(this, 'preview_mesa_edit')">
                        <small>Dejar vacío para mantener la imagen actual</small>
                        <div id="preview_mesa_edit" class="image-preview"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-cancelar" onclick="cerrarModalEditar()">Cancelar</button>
                        <button type="submit" class="btn-confirmar">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL PARA AMPLIAR IMAGEN -->
    <div id="modalImagen" class="modal-imagen" onclick="cerrarModalImagen()">
        <span class="close-imagen">&times;</span>
        <img class="imagen-ampliada" id="imagenAmpliada">
    </div>

    <!-- JavaScript Externo -->
    <script src="../../JS/gestion_salas.js"></script>

</body>
</html>