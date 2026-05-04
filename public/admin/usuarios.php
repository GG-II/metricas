<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Usuario;
use App\Models\Departamento;
use App\Models\Area;

AuthMiddleware::handle();
PermissionMiddleware::requireSuperAdmin();

$user = getCurrentUser();
$usuarioModel = new Usuario();
$deptModel = new Departamento();
$areaModel = new Area();

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ✅ PROTECCIÓN CSRF
    csrf_validate();

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'crear':
                // Validar username único
                if ($usuarioModel->usernameExists($_POST['username'])) {
                    setFlash('error', 'El nombre de usuario ya existe');
                    redirect('/public/admin/usuarios.php');
                    break;
                }

                $data = [
                    'username' => sanitize($_POST['username']),
                    'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                    'nombre' => sanitize($_POST['nombre']),
                    'email' => sanitize($_POST['email']),
                    'rol' => sanitize($_POST['rol']),
                    'departamento_id' => $_POST['departamento_id'] ? (int)$_POST['departamento_id'] : null,
                    'area_id' => $_POST['area_id'] ? (int)$_POST['area_id'] : null,
                    'activo' => 1
                ];

                // Validar que area_admin tenga area_id
                $validation = $usuarioModel->validateAreaAdmin($data);
                if (!$validation['valid']) {
                    setFlash('error', $validation['error']);
                    redirect('/public/admin/usuarios.php');
                    break;
                }

                if ($usuarioModel->create($data)) {
                    setFlash('success', 'Usuario creado exitosamente');
                } else {
                    setFlash('error', 'Error al crear el usuario');
                }
                redirect('/public/admin/usuarios.php');
                break;

            case 'editar':
                $id = (int)$_POST['id'];

                // Validar username único (excluyendo el usuario actual)
                if ($usuarioModel->usernameExists($_POST['username'], $id)) {
                    setFlash('error', 'El nombre de usuario ya existe');
                    redirect('/public/admin/usuarios.php');
                    break;
                }

                $data = [
                    'username' => sanitize($_POST['username']),
                    'nombre' => sanitize($_POST['nombre']),
                    'email' => sanitize($_POST['email']),
                    'rol' => sanitize($_POST['rol']),
                    'departamento_id' => $_POST['departamento_id'] ? (int)$_POST['departamento_id'] : null,
                    'area_id' => $_POST['area_id'] ? (int)$_POST['area_id'] : null
                ];

                // Validar que area_admin tenga area_id
                $validation = $usuarioModel->validateAreaAdmin($data);
                if (!$validation['valid']) {
                    setFlash('error', $validation['error']);
                    redirect('/public/admin/usuarios.php');
                    break;
                }

                // Solo actualizar password si se proporciona
                if (!empty($_POST['password'])) {
                    $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                }

                if ($usuarioModel->update($id, $data)) {
                    setFlash('success', 'Usuario actualizado exitosamente');
                } else {
                    setFlash('error', 'Error al actualizar el usuario');
                }
                redirect('/public/admin/usuarios.php');
                break;

            case 'eliminar':
                $id = (int)$_POST['id'];

                // No permitir eliminar el usuario actual
                if ($id == $user['id']) {
                    setFlash('error', 'No puedes eliminar tu propio usuario');
                    redirect('/public/admin/usuarios.php');
                    break;
                }

                if ($usuarioModel->update($id, ['activo' => 0])) {
                    setFlash('success', 'Usuario eliminado exitosamente');
                } else {
                    setFlash('error', 'Error al eliminar el usuario');
                }
                redirect('/public/admin/usuarios.php');
                break;

            case 'toggle_activo':
                $id = (int)$_POST['id'];
                $nuevo_estado = (int)$_POST['activo'];

                if ($usuarioModel->update($id, ['activo' => $nuevo_estado])) {
                    setFlash('success', $nuevo_estado ? 'Usuario activado' : 'Usuario desactivado');
                } else {
                    setFlash('error', 'Error al cambiar el estado');
                }
                redirect('/public/admin/usuarios.php');
                break;
        }
    }
}

// Obtener todos los usuarios
$usuarios = $usuarioModel->getAllWithRelations();

// Obtener departamentos y áreas para el formulario
$departamentos = $deptModel->getAll();

// Usuario a editar
$editando = null;
if (isset($_GET['editar'])) {
    $editando = $usuarioModel->findWithRelations($_GET['editar']);
}

$pageTitle = 'Gestión de Usuarios';
require_once __DIR__ . '/../../views/layouts/header.php';
?>

<div class="page-wrapper">
    <div class="page-body">
        <div class="container-xl">

            <!-- Breadcrumb -->
            <div class="page-header mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo baseUrl('/public/admin/index.php'); ?>">Administración</a></li>
                                <li class="breadcrumb-item active">Usuarios</li>
                            </ol>
                        </nav>
                        <h2 class="page-title">
                            <i class="ti ti-users me-2"></i>
                            Gestión de Usuarios
                        </h2>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUsuario">
                            <i class="ti ti-plus me-1"></i>
                            Nuevo Usuario
                        </button>
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
            <?php if (isset($_SESSION['flash']) && isset($_SESSION['flash']['type']) && isset($_SESSION['flash']['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['flash']['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo e($_SESSION['flash']['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <!-- Tabla de Usuarios -->
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Departamento</th>
                                <th>Área</th>
                                <th>Último Acceso</th>
                                <th>Estado</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                            <tr class="<?php echo !$u['activo'] ? 'opacity-50' : ''; ?>">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="avatar avatar-sm me-2" style="background-color: <?php echo $u['avatar_color'] ?? ($u['departamento_color'] ?? '#3b82f6'); ?>">
                                            <?php if (isset($u['avatar_icono']) && $u['avatar_icono']): ?>
                                                <i class="ti ti-<?php echo e($u['avatar_icono']); ?>"></i>
                                            <?php else: ?>
                                                <?php echo strtoupper(substr($u['nombre'], 0, 2)); ?>
                                            <?php endif; ?>
                                        </span>
                                        <div>
                                            <div class="fw-bold"><?php echo e($u['nombre']); ?></div>
                                            <div class="text-muted small">@<?php echo e($u['username']); ?></div>
                                            <?php if ($u['email']): ?>
                                            <div class="text-muted small"><?php echo e($u['email']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $roles = [
                                        'super_admin' => ['nombre' => 'Super Admin', 'class' => 'bg-red'],
                                        'dept_admin' => ['nombre' => 'Admin Departamento', 'class' => 'bg-blue'],
                                        'area_admin' => ['nombre' => 'Admin de Área', 'class' => 'bg-purple'],
                                        'dept_viewer' => ['nombre' => 'Visualizador', 'class' => 'bg-green']
                                    ];
                                    $rol_info = $roles[$u['rol']] ?? ['nombre' => $u['rol'], 'class' => 'bg-secondary'];
                                    ?>
                                    <span class="badge <?php echo $rol_info['class']; ?>">
                                        <?php echo $rol_info['nombre']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($u['departamento_nombre']): ?>
                                        <span class="text-muted"><?php echo e($u['departamento_nombre']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($u['area_nombre']): ?>
                                        <span class="text-muted"><?php echo e($u['area_nombre']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($u['ultimo_acceso']): ?>
                                        <span class="text-muted small"><?php echo date('d/m/Y H:i', strtotime($u['ultimo_acceso'])); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">Nunca</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($u['activo']): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-list">
                                        <a href="?editar=<?php echo $u['id']; ?>" class="btn btn-sm btn-icon btn-ghost-primary"
                                           title="Editar">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        <?php if ($u['id'] != $user['id']): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="toggle_activo">
                                            <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                            <input type="hidden" name="activo" value="<?php echo $u['activo'] ? 0 : 1; ?>">
                                            <button type="submit" class="btn btn-sm btn-icon btn-ghost-<?php echo $u['activo'] ? 'warning' : 'success'; ?>"
                                                    title="<?php echo $u['activo'] ? 'Desactivar' : 'Activar'; ?>">
                                                <i class="ti ti-<?php echo $u['activo'] ? 'ban' : 'check'; ?>"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>

                            <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="ti ti-users" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <p class="mt-3">No hay usuarios registrados</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal de Crear/Editar -->
<div class="modal modal-blur fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="formUsuario">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <?php echo $editando ? 'Editar Usuario' : 'Nuevo Usuario'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="<?php echo $editando ? 'editar' : 'crear'; ?>">
                    <?php if ($editando): ?>
                        <input type="hidden" name="id" value="<?php echo $editando['id']; ?>">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label required">Nombre Completo</label>
                                <input type="text" name="nombre" class="form-control"
                                       value="<?php echo e($editando['nombre'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label required">Nombre de Usuario</label>
                                <div class="input-group">
                                    <span class="input-group-text">@</span>
                                    <input type="text" name="username" class="form-control"
                                           value="<?php echo e($editando['username'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                               value="<?php echo e($editando['email'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label <?php echo !$editando ? 'required' : ''; ?>">
                            Contraseña
                            <?php if ($editando): ?>
                                <span class="text-muted">(dejar en blanco para mantener la actual)</span>
                            <?php endif; ?>
                        </label>
                        <input type="password" name="password" class="form-control"
                               <?php echo !$editando ? 'required' : ''; ?>>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Rol</label>
                        <select name="rol" id="rol-select" class="form-select" required>
                            <option value="">Seleccionar rol...</option>
                            <option value="super_admin" <?php echo ($editando && $editando['rol'] == 'super_admin') ? 'selected' : ''; ?>>
                                Super Admin (Acceso global)
                            </option>
                            <option value="dept_admin" <?php echo ($editando && $editando['rol'] == 'dept_admin') ? 'selected' : ''; ?>>
                                Admin de Departamento
                            </option>
                            <option value="area_admin" <?php echo ($editando && $editando['rol'] == 'area_admin') ? 'selected' : ''; ?>>
                                Admin de Área
                            </option>
                            <option value="dept_viewer" <?php echo ($editando && $editando['rol'] == 'dept_viewer') ? 'selected' : ''; ?>>
                                Visualizador (solo lectura)
                            </option>
                        </select>
                        <div class="form-hint">
                            <strong>Super Admin:</strong> Acceso completo al sistema<br>
                            <strong>Admin Departamento:</strong> Gestiona todas las áreas de un departamento<br>
                            <strong>Admin de Área:</strong> Gestiona solo un área específica<br>
                            <strong>Visualizador:</strong> Solo lectura de todo el departamento
                        </div>
                    </div>

                    <div class="row" id="asignacion-container">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" id="label-departamento">Departamento</label>
                                <select name="departamento_id" id="departamento-select" class="form-select">
                                    <option value="">Ninguno</option>
                                    <?php foreach ($departamentos as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>"
                                            <?php echo ($editando && $editando['departamento_id'] == $dept['id']) ? 'selected' : ''; ?>>
                                        <?php echo e($dept['nombre']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-hint" id="hint-dept">
                                    Necesario para seleccionar el área específica
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6" id="area-container">
                            <div class="mb-3">
                                <label class="form-label" id="label-area">Área</label>
                                <select name="area_id" id="area-select" class="form-select">
                                    <option value="">Ninguna</option>
                                    <?php if ($editando && $editando['area_id']): ?>
                                    <option value="<?php echo $editando['area_id']; ?>" selected>
                                        <?php echo e($editando['area_nombre']); ?>
                                    </option>
                                    <?php endif; ?>
                                </select>
                                <div class="form-hint" id="hint-area">Para Admin de Área (edición) y Visualizador (lectura)</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check me-1"></i>
                        <?php echo $editando ? 'Actualizar' : 'Crear'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Cargar áreas cuando se selecciona departamento
document.addEventListener('DOMContentLoaded', function() {
    const rolSelect = document.getElementById('rol-select');
    const deptSelect = document.getElementById('departamento-select');
    const areaSelect = document.getElementById('area-select');
    const labelDept = document.getElementById('label-departamento');
    const labelArea = document.getElementById('label-area');

    // Actualizar campos requeridos según el rol
    function updateRequiredFields() {
        const rol = rolSelect.value;
        const asignacionContainer = document.getElementById('asignacion-container');
        const areaContainer = document.getElementById('area-container');
        const hintDept = document.getElementById('hint-dept');
        const hintArea = document.getElementById('hint-area');

        if (rol === 'super_admin') {
            deptSelect.removeAttribute('required');
            areaSelect.removeAttribute('required');
            labelDept.classList.remove('required');
            labelArea.classList.remove('required');
            asignacionContainer.style.display = 'none';
        } else if (rol === 'dept_admin') {
            deptSelect.setAttribute('required', 'required');
            areaSelect.removeAttribute('required');
            areaSelect.value = ''; // Limpiar área
            labelDept.classList.add('required');
            labelArea.classList.remove('required');
            asignacionContainer.style.display = '';
            areaContainer.style.display = 'none';
            hintDept.innerHTML = '<strong>Admin Departamento:</strong> Acceso a todas las áreas del departamento';
        } else if (rol === 'area_admin') {
            deptSelect.setAttribute('required', 'required');
            areaSelect.setAttribute('required', 'required');
            labelDept.classList.add('required');
            labelArea.classList.add('required');
            asignacionContainer.style.display = '';
            areaContainer.style.display = '';
            hintDept.innerHTML = '<strong>Admin de Área:</strong> Selecciona departamento para filtrar áreas';
            hintArea.innerHTML = '<strong>Admin de Área:</strong> Puede ver y editar solo esta área';
        } else if (rol === 'dept_viewer') {
            deptSelect.setAttribute('required', 'required');
            areaSelect.removeAttribute('required'); // No es obligatorio para viewer
            labelDept.classList.add('required');
            labelArea.classList.remove('required');
            asignacionContainer.style.display = '';
            areaContainer.style.display = '';
            hintDept.innerHTML = '<strong>Visualizador:</strong> Solo lectura de todo el departamento';
            hintArea.innerHTML = 'Opcional: restringir a un área específica (si se deja vacío, ve todo el departamento)';
        }
    }

    rolSelect.addEventListener('change', updateRequiredFields);

    // Cargar áreas al seleccionar departamento
    deptSelect.addEventListener('change', function() {
        const deptId = this.value;
        areaSelect.innerHTML = '<option value="">Cargando...</option>';

        if (!deptId) {
            areaSelect.innerHTML = '<option value="">Ninguna</option>';
            return;
        }

        fetch('<?php echo baseUrl("/public/api/get-areas-by-departamento.php"); ?>?departamento_id=' + deptId)
            .then(response => response.json())
            .then(data => {
                areaSelect.innerHTML = '<option value="">Seleccionar área...</option>';
                data.areas.forEach(area => {
                    const option = document.createElement('option');
                    option.value = area.id;
                    option.textContent = area.nombre;
                    areaSelect.appendChild(option);
                });
            })
            .catch(error => {
                areaSelect.innerHTML = '<option value="">Error al cargar</option>';
            });
    });

    // Auto-abrir modal si hay parámetro editar
    <?php if ($editando): ?>
        setTimeout(function() {
            const modal = new bootstrap.Modal(document.getElementById('modalUsuario'));
            modal.show();
            updateRequiredFields();
        }, 100);
    <?php endif; ?>

    // Limpiar formulario y URL al cerrar modal
    document.getElementById('modalUsuario').addEventListener('hidden.bs.modal', function() {
        <?php if ($editando): ?>
            // Limpiar parámetro editar de la URL
            window.history.replaceState({}, '', '<?php echo baseUrl('/public/admin/usuarios.php'); ?>');
        <?php else: ?>
            document.getElementById('formUsuario').reset();
        <?php endif; ?>
    });
});
</script>

<!-- Tabler JS -->
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/js/tabler.min.js"></script>
<script src="<?php echo baseUrl('/public/assets/js/theme-toggle.js'); ?>"></script>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
