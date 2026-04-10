<?php
/**
 * Gestión de Usuarios
 * CRUD completo con foto de perfil
 */

require_once '../config.php';
requireAdmin();

require_once '../models/Usuario.php';

$usuarioModel = new Usuario();

$mensaje = '';
$mensaje_tipo = '';

// CREAR usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_usuario'])) {
    try {
        // Validar que username y email no existan
        if ($usuarioModel->usernameExists($_POST['username'])) {
            throw new Exception('El nombre de usuario ya existe.');
        }
        
        if ($usuarioModel->emailExists($_POST['email'])) {
            throw new Exception('El email ya está registrado.');
        }
        
        // Procesar foto de perfil
        $foto_perfil = null;
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $foto_perfil = procesarUploadFoto($_FILES['foto_perfil']);
        }
        
        $data = [
            'nombre' => sanitize($_POST['nombre']),
            'email' => sanitize($_POST['email']),
            'username' => sanitize($_POST['username']),
            'password' => hashPassword($_POST['password']),
            'foto_perfil' => $foto_perfil,
            'rol' => $_POST['rol'],
            'activo' => 1
        ];
        
        $id = $usuarioModel->create($data);
        logActivity('crear_usuario', 'usuarios', $id, "Creado usuario: {$data['username']}");
        
        $mensaje = 'Usuario creado exitosamente.';
        $mensaje_tipo = 'success';
    } catch (Exception $e) {
        $mensaje = 'Error al crear usuario: ' . $e->getMessage();
        $mensaje_tipo = 'danger';
    }
}

// EDITAR usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_usuario'])) {
    try {
        $id = (int)$_POST['usuario_id'];
        
        // Validar username único (excluyendo el usuario actual)
        if ($usuarioModel->usernameExists($_POST['username'], $id)) {
            throw new Exception('El nombre de usuario ya existe.');
        }
        
        // Validar email único (excluyendo el usuario actual)
        if ($usuarioModel->emailExists($_POST['email'], $id)) {
            throw new Exception('El email ya está registrado.');
        }
        
        // Procesar nueva foto de perfil si se sube
        $foto_perfil = null;
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            // Eliminar foto anterior si existe
            // Eliminar foto anterior si existe
$usuario_actual = $usuarioModel->getById($id);
if ($usuario_actual['foto_perfil']) {
    eliminarFotoPerfil($usuario_actual['foto_perfil']);
}
            $foto_perfil = procesarUploadFoto($_FILES['foto_perfil']);
        }
        
        $data = [
            'nombre' => sanitize($_POST['nombre']),
            'email' => sanitize($_POST['email']),
            'username' => sanitize($_POST['username']),
            'rol' => $_POST['rol'],
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];
        
        // Actualizar foto solo si se subió una nueva
        if ($foto_perfil) {
            $data['foto_perfil'] = $foto_perfil;
        }
        
        // Actualizar contraseña solo si se proporcionó una nueva
        if (!empty($_POST['password'])) {
            $data['password'] = hashPassword($_POST['password']);
        }
        
        $usuarioModel->update($id, $data);
        logActivity('editar_usuario', 'usuarios', $id, "Editado usuario: {$data['username']}");
        
        $mensaje = 'Usuario actualizado exitosamente.';
        $mensaje_tipo = 'success';
    } catch (Exception $e) {
        $mensaje = 'Error al actualizar usuario: ' . $e->getMessage();
        $mensaje_tipo = 'danger';
    }
}

// ELIMINAR usuario
if (isset($_GET['eliminar'])) {
    try {
        $id = (int)$_GET['eliminar'];
        
        // No permitir eliminar el propio usuario
        if ($id === $_SESSION['user_id']) {
            throw new Exception('No puedes eliminar tu propio usuario.');
        }
        
        $usuario = $usuarioModel->getById($id);
        
        // Eliminar foto de perfil si existe
        // Eliminar foto de perfil si existe
if ($usuario['foto_perfil']) {
    eliminarFotoPerfil($usuario['foto_perfil']);
}
        
        $usuarioModel->delete($id);
        logActivity('eliminar_usuario', 'usuarios', $id, "Eliminado usuario: {$usuario['username']}");
        
        $mensaje = 'Usuario eliminado exitosamente.';
        $mensaje_tipo = 'success';
    } catch (Exception $e) {
        $mensaje = 'Error al eliminar usuario: ' . $e->getMessage();
        $mensaje_tipo = 'danger';
    }
}



// Obtener todos los usuarios
$usuarios = $usuarioModel->getAll([], 'nombre ASC');

$page_title = 'Gestión de Usuarios';
$is_admin = true;
?>

<?php include '../partials/header.php'; ?>

<div class="page-body">
    <div class="container-xl">
        
        <div class="mb-3">
            <a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-ghost-secondary">
                <i class="ti ti-arrow-left me-1"></i>
                Volver al Dashboard
            </a>
        </div>
        
        <div class="page-header mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-users me-2"></i>
                        Gestión de Usuarios
                    </h2>
                    <div class="text-muted mt-1">Administra usuarios del sistema</div>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modal-crear">
                        <i class="ti ti-user-plus me-2"></i>
                        Nuevo Usuario
                    </button>
                </div>
            </div>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $mensaje_tipo; ?> alert-dismissible fade show">
                <i class="ti ti-<?php echo $mensaje_tipo === 'success' ? 'check' : 'alert-circle'; ?> me-2"></i>
                <?php echo $mensaje; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Tabla de usuarios -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-list me-2"></i>
                    Usuarios del Sistema
                </h3>
                <div class="card-actions">
                    <span class="badge bg-primary"><?php echo count($usuarios); ?> usuarios</span>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-hover">
                    <thead>
                        <tr>
                            <th class="w-1">Avatar</th>
                            <th>Nombre</th>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Último Acceso</th>
                            <th class="w-1">Estado</th>
                            <th class="w-1 text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td>
                                    <?php if ($u['foto_perfil']): ?>
                                        <span class="avatar avatar-sm" 
                                              style="background-image: url('<?php echo BASE_URL; ?>/uploads/avatars/<?php echo $u['foto_perfil']; ?>')"></span>
                                    <?php else: ?>
                                        <span class="avatar avatar-sm" style="background-color: #3b82f6;">
                                            <?php echo strtoupper(substr($u['nombre'], 0, 2)); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($u['nombre']); ?></div>
                                    <?php if ($u['id'] === $_SESSION['user_id']): ?>
                                        <span class="badge bg-azure-lt">Tú</span>
                                    <?php endif; ?>
                                </td>
                                <td><code><?php echo $u['username']; ?></code></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td>
                                    <?php if ($u['rol'] === 'admin'): ?>
                                        <span class="badge bg-red">Administrador</span>
                                    <?php else: ?>
                                        <span class="badge bg-blue">Visualizador</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo $u['ultimo_acceso'] ? formatDateTime($u['ultimo_acceso']) : '<span class="text-muted">Nunca</span>'; ?>
                                </td>
                                <td>
                                    <?php if ($u['activo']): ?>
                                        <span class="status status-green">
                                            <span class="status-dot"></span>
                                            Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="status status-secondary">
                                            <span class="status-dot"></span>
                                            Inactivo
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-primary" 
                                                onclick='editarUsuario(<?php echo json_encode($u, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'
                                                title="Editar">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                            <button class="btn btn-sm btn-danger" 
                                                    onclick="confirmarEliminar(<?php echo $u['id']; ?>, '<?php echo addslashes($u['nombre']); ?>')"
                                                    title="Eliminar">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>
</div>

<!-- Modal: Crear Usuario -->
<div class="modal modal-blur fade" id="modal-crear" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data" id="form-crear">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-user-plus me-2"></i>
                        Nuevo Usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php include 'partials/usuario-form.php'; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal" onclick="cerrarModal('modal-crear')">
                        <i class="ti ti-x me-1"></i>
                        Cancelar
                    </button>
                    <button type="submit" name="crear_usuario" class="btn btn-primary">
                        <i class="ti ti-check me-1"></i>
                        Crear Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Editar Usuario -->
<div class="modal modal-blur fade" id="modal-editar" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data" id="form-editar">
                <input type="hidden" name="usuario_id" id="edit-usuario-id">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-edit me-2"></i>
                        Editar Usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="cerrarModal('modal-editar')"></button>
                </div>
                <div class="modal-body">
                    <?php $es_edicion = true; include 'partials/usuario-form.php'; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal" onclick="cerrarModal('modal-editar')">
                        <i class="ti ti-x me-1"></i>
                        Cancelar
                    </button>
                    <button type="submit" name="editar_usuario" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editarUsuario(usuario) {
    const form = document.getElementById('form-editar');
    
    document.getElementById('edit-usuario-id').value = usuario.id;
    
    const campos = {
        'nombre': usuario.nombre,
        'email': usuario.email,
        'username': usuario.username,
        'rol': usuario.rol
    };
    
    for (const [campo, valor] of Object.entries(campos)) {
        const input = form.querySelector(`[name="${campo}"]`);
        if (input) input.value = valor;
    }
    
    // Checkbox de activo
    const checkboxActivo = form.querySelector('[name="activo"]');
    if (checkboxActivo) checkboxActivo.checked = usuario.activo == 1;
    
    // Actualizar preview de avatar
    const preview = form.querySelector('#avatar-preview');
    if (preview) {
        if (usuario.foto_perfil) {
            preview.style.backgroundImage = `url('<?php echo BASE_URL; ?>/uploads/avatars/${usuario.foto_perfil}')`;
            preview.innerHTML = '';
        } else {
            preview.style.backgroundImage = 'none';
            preview.innerHTML = usuario.nombre.substring(0, 2).toUpperCase();
        }
    }
    
    // Mostrar modal
    const modalElement = document.getElementById('modal-editar');
    modalElement.classList.add('show');
    modalElement.style.display = 'block';
    modalElement.setAttribute('aria-modal', 'true');
    modalElement.removeAttribute('aria-hidden');
    
    const backdrop = document.createElement('div');
    backdrop.className = 'modal-backdrop fade show';
    backdrop.id = 'modal-backdrop-usuarios';
    document.body.appendChild(backdrop);
    document.body.classList.add('modal-open');
}

function cerrarModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.remove('show');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    modal.removeAttribute('aria-modal');
    
    const backdrop = document.getElementById('modal-backdrop-usuarios');
    if (backdrop) backdrop.remove();
    
    document.body.classList.remove('modal-open');
}

function confirmarEliminar(id, nombre) {
    if (confirm(`¿Eliminar el usuario "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
        window.location.href = `?eliminar=${id}`;
    }
}
</script>

<style>
.avatar {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: white;
    background-size: cover;
    background-position: center;
}
</style>

<?php include '../partials/footer.php'; ?>