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
$saludo = "Buenos días"; // El header.php lo recalcula

// --- OBTENER USUARIOS DE LA BASE DE DATOS ---
$usuarios = [];
$mensaje = '';
$tipo_mensaje = '';

// Procesar mensajes de operaciones previas
if (isset($_GET['success'])) {
    $tipo_mensaje = 'success';
    switch ($_GET['success']) {
        case 'created':
            $mensaje = 'Usuario creado exitosamente.';
            break;
        case 'updated':
            $mensaje = 'Usuario actualizado exitosamente.';
            break;
        case 'deleted':
            $mensaje = 'Usuario desactivado exitosamente.';
            break;
        case 'reactivated':
            $mensaje = 'Usuario reactivado exitosamente.';
            break;
        case 'deleted_permanent':
            $mensaje = 'Usuario eliminado permanentemente.';
            break;
    }
}

if (isset($_GET['error'])) {
    $tipo_mensaje = 'error';
    switch ($_GET['error']) {
        case 'duplicate_username':
            $mensaje = 'El nombre de usuario ya existe.';
            break;
        case 'duplicate_email':
            $mensaje = 'El email ya está registrado.';
            break;
        case 'invalid_data':
            $mensaje = 'Datos inválidos. Por favor verifica la información.';
            break;
        case 'db_error':
            $mensaje = 'Error de base de datos. Intenta nuevamente.';
            break;
        case 'cannot_delete_self':
            $mensaje = 'No puedes eliminarte a ti mismo.';
            break;
        case 'user_not_found':
            $mensaje = 'Usuario no encontrado.';
            break;
        case 'user_already_active':
            $mensaje = 'El usuario ya está activo.';
            break;
        case 'fk_constraint':
            $mensaje = 'No se puede eliminar el usuario porque tiene registros asociados. Desactívalo en su lugar.';
            break;
        default:
            $mensaje = 'Ocurrió un error. Intenta nuevamente.';
    }
}

try {
    // Obtener todos los usuarios con información de rol
    $stmt = $conn->query("
        SELECT 
            id, 
            username, 
            nombre, 
            apellido, 
            email, 
            rol,
            fecha_alta,
            fecha_baja,
            CASE 
                WHEN fecha_baja IS NULL THEN 'Activo'
                WHEN fecha_baja IS NOT NULL THEN 'Inactivo'
            END as estado
        FROM users
        ORDER BY fecha_baja ASC, id DESC
    ");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensaje = "Error al cargar usuarios: " . $e->getMessage();
    $tipo_mensaje = 'error';
}

// Función auxiliar para obtener el nombre del rol
function getNombreRol($rol_id) {
    switch ($rol_id) {
        case 1: return 'Camarero';
        case 2: return 'Administrador';
        case 3: return 'Cliente';
        default: return 'Desconocido';
    }
}

// Función auxiliar para obtener la clase del badge del rol
function getRolBadgeClass($rol_id) {
    switch ($rol_id) {
        case 1: return 'badge-camarero';
        case 2: return 'badge-admin';
        case 3: return 'badge-cliente';
        default: return 'badge-default';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Admin</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <link rel="stylesheet" href="../../css/header.css">
    <link rel="stylesheet" href="../../css/panel_principal.css">
    <link rel="stylesheet" href="../../css/gestion_usuarios.css">
    <link rel="icon" type="image/png" href="../../img/icono.png">
</head>
<body>

    <?php require_once 'header.php'; ?>

    <div class="container">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1 class="dashboard-title" style="margin-bottom: 0;">Gestión de Usuarios</h1>
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
                <h2 style="color: #333; margin: 0;">Listado de Usuarios</h2>
                <button class="btn-crear" onclick="abrirModalCrear()">
                    <i class="fa-solid fa-user-plus"></i> Añadir Nuevo Usuario
                </button>
            </div>

            <div class="table-container">
                <table class="usuarios-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Nombre Completo</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: #999;">
                                    No hay usuarios registrados
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr class="<?= $usuario['fecha_baja'] ? 'usuario-inactivo' : '' ?>">
                                    <td><?= htmlspecialchars($usuario['id']) ?></td>
                                    <td><strong><?= htmlspecialchars($usuario['username']) ?></strong></td>
                                    <td><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></td>
                                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                                    <td>
                                        <span class="badge <?= getRolBadgeClass($usuario['rol']) ?>">
                                            <?= getNombreRol($usuario['rol']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $usuario['fecha_baja'] ? 'badge-inactivo' : 'badge-activo' ?>">
                                            <?= $usuario['estado'] ?>
                                        </span>
                                    </td>
                                    <td class="acciones">
                                        <?php if (!$usuario['fecha_baja']): ?>
                                            <!-- Usuario Activo: Editar y Desactivar -->
                                            <button class="btn-accion btn-editar" onclick='editarUsuario(<?= json_encode($usuario) ?>)' title="Editar">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <?php if ($usuario['id'] != $_SESSION['id_usuario']): ?>
                                                <button class="btn-accion btn-eliminar" onclick="eliminarUsuario(<?= $usuario['id'] ?>, '<?= htmlspecialchars($usuario['username']) ?>')" title="Desactivar">
                                                    <i class="fa-solid fa-ban"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <!-- Usuario Inactivo: Reactivar y Eliminar Permanentemente -->
                                            <button class="btn-accion btn-reactivar" onclick="reactivarUsuario(<?= $usuario['id'] ?>, '<?= htmlspecialchars($usuario['username']) ?>')" title="Reactivar">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                            <button class="btn-accion btn-eliminar-permanente" onclick="eliminarPermanente(<?= $usuario['id'] ?>, '<?= htmlspecialchars($usuario['username']) ?>')" title="Eliminar Permanentemente">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- MODAL PARA EDITAR USUARIO -->
    <div id="modalUsuario" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitulo">Editar Usuario</h2>
                <button class="modal-close" onclick="cerrarModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formUsuario" action="../PROCEDIMIENTOS/procesar_editar_usuario.php" method="POST">
                    <input type="hidden" name="id" id="usuario_id">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="usuario_username">Username *</label>
                            <input type="text" name="username" id="usuario_username" required>
                        </div>
                        <div class="form-group">
                            <label for="usuario_rol">Rol *</label>
                            <select name="rol" id="usuario_rol" required>
                                <option value="1">Camarero</option>
                                <option value="2">Administrador</option>
                                <option value="3">Cliente</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="usuario_nombre">Nombre *</label>
                            <input type="text" name="nombre" id="usuario_nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="usuario_apellido">Apellido</label>
                            <input type="text" name="apellido" id="usuario_apellido">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="usuario_email">Email *</label>
                        <input type="email" name="email" id="usuario_email" required>
                    </div>

                    <div class="form-group">
                        <label for="usuario_password">Nueva Contraseña (dejar vacío para no cambiar)</label>
                        <input type="password" name="password" id="usuario_password" minlength="5">
                        <small>Mínimo 5 caracteres. Dejar vacío si no desea cambiarla.</small>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
                        <button type="submit" class="btn-confirmar">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL PARA CREAR USUARIO -->
    <div id="modalCrear" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Crear Nuevo Usuario</h2>
                <button class="modal-close" onclick="cerrarModalCrear()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formCrear" action="../PROCEDIMIENTOS/procesar_crear_usuario.php" method="POST">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nuevo_username">Username *</label>
                            <input type="text" name="username" id="nuevo_username" required minlength="3">
                        </div>
                        <div class="form-group">
                            <label for="nuevo_rol">Rol *</label>
                            <select name="rol" id="nuevo_rol" required>
                                <option value="1">Camarero</option>
                                <option value="2">Administrador</option>
                                <option value="3">Cliente</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="nuevo_nombre">Nombre *</label>
                            <input type="text" name="nombre" id="nuevo_nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="nuevo_apellido">Apellido</label>
                            <input type="text" name="apellido" id="nuevo_apellido">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nuevo_email">Email *</label>
                        <input type="email" name="email" id="nuevo_email" required>
                    </div>

                    <div class="form-group">
                        <label for="nuevo_password">Contraseña *</label>
                        <input type="password" name="password" id="nuevo_password" required minlength="5">
                        <small>Mínimo 5 caracteres</small>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-cancelar" onclick="cerrarModalCrear()">Cancelar</button>
                        <button type="submit" class="btn-confirmar">Crear Usuario</button>
                    </div>
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
                <h2 style="color: #333; margin: 0;">Listado de Usuarios</h2>
                <button class="btn-crear" onclick="abrirModalCrear()">
                    <i class="fa-solid fa-user-plus"></i> Añadir Nuevo Usuario
                </button>
            </div>

            <div class="table-container">
                <table class="usuarios-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Nombre Completo</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: #999;">
                                    No hay usuarios registrados
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr class="<?= $usuario['fecha_baja'] ? 'usuario-inactivo' : '' ?>">
                                    <td><?= htmlspecialchars($usuario['id']) ?></td>
                                    <td><strong><?= htmlspecialchars($usuario['username']) ?></strong></td>
                                    <td><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></td>
                                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                                    <td>
                                        <span class="badge <?= getRolBadgeClass($usuario['rol']) ?>">
                                            <?= getNombreRol($usuario['rol']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $usuario['fecha_baja'] ? 'badge-inactivo' : 'badge-activo' ?>">
                                            <?= $usuario['estado'] ?>
                                        </span>
                                    </td>
                                    <td class="acciones">
                                        <?php if (!$usuario['fecha_baja']): ?>
                                            <!-- Usuario Activo: Editar y Desactivar -->
                                            <button class="btn-accion btn-editar" onclick='editarUsuario(<?= json_encode($usuario) ?>)' title="Editar">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <?php if ($usuario['id'] != $_SESSION['id_usuario']): ?>
                                                <button class="btn-accion btn-eliminar" onclick="eliminarUsuario(<?= $usuario['id'] ?>, '<?= htmlspecialchars($usuario['username']) ?>')" title="Desactivar">
                                                    <i class="fa-solid fa-ban"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <!-- Usuario Inactivo: Reactivar y Eliminar Permanentemente -->
                                            <button class="btn-accion btn-reactivar" onclick="reactivarUsuario(<?= $usuario['id'] ?>, '<?= htmlspecialchars($usuario['username']) ?>')" title="Reactivar">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                            <button class="btn-accion btn-eliminar-permanente" onclick="eliminarPermanente(<?= $usuario['id'] ?>, '<?= htmlspecialchars($usuario['username']) ?>')" title="Eliminar Permanentemente">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- MODAL PARA EDITAR USUARIO -->
    <div id="modalUsuario" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitulo">Editar Usuario</h2>
                <button class="modal-close" onclick="cerrarModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formUsuario" action="../PROCEDIMIENTOS/procesar_editar_usuario.php" method="POST">
                    <input type="hidden" name="id" id="usuario_id">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="usuario_username">Username *</label>
                            <input type="text" name="username" id="usuario_username" required>
                        </div>
                        <div class="form-group">
                            <label for="usuario_rol">Rol *</label>
                            <select name="rol" id="usuario_rol" required>
                                <option value="1">Camarero</option>
                                <option value="2">Administrador</option>
                                <option value="3">Cliente</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="usuario_nombre">Nombre *</label>
                            <input type="text" name="nombre" id="usuario_nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="usuario_apellido">Apellido</label>
                            <input type="text" name="apellido" id="usuario_apellido">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="usuario_email">Email *</label>
                        <input type="email" name="email" id="usuario_email" required>
                    </div>

                    <div class="form-group">
                        <label for="usuario_password">Nueva Contraseña (dejar vacío para no cambiar)</label>
                        <input type="password" name="password" id="usuario_password" minlength="5">
                        <small>Mínimo 5 caracteres. Dejar vacío si no desea cambiarla.</small>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
                        <button type="submit" class="btn-confirmar">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL PARA CREAR USUARIO -->
    <div id="modalCrear" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Crear Nuevo Usuario</h2>
                <button class="modal-close" onclick="cerrarModalCrear()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formCrear" action="../PROCEDIMIENTOS/procesar_crear_usuario.php" method="POST">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nuevo_username">Username *</label>
                            <input type="text" name="username" id="nuevo_username" required minlength="3">
                        </div>
                        <div class="form-group">
                            <label for="nuevo_rol">Rol *</label>
                            <select name="rol" id="nuevo_rol" required>
                                <option value="1">Camarero</option>
                                <option value="2">Administrador</option>
                                <option value="3">Cliente</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="nuevo_nombre">Nombre *</label>
                            <input type="text" name="nombre" id="nuevo_nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="nuevo_apellido">Apellido</label>
                            <input type="text" name="apellido" id="nuevo_apellido">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nuevo_email">Email *</label>
                        <input type="email" name="email" id="nuevo_email" required>
                    </div>

                    <div class="form-group">
                        <label for="nuevo_password">Contraseña *</label>
                        <input type="password" name="password" id="nuevo_password" required minlength="5">
                        <small>Mínimo 5 caracteres</small>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-cancelar" onclick="cerrarModalCrear()">Cancelar</button>
                        <button type="submit" class="btn-confirmar">Crear Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript Externo -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../JS/gestion_usuarios.js"></script>

</body>
</html>