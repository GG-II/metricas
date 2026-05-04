<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Departamento;

AuthMiddleware::handle();
PermissionMiddleware::requireSuperAdmin();

$user = getCurrentUser();
$deptModel = new Departamento();

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ✅ PROTECCIÓN CSRF
    csrf_validate();

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'crear':
                // Obtener el siguiente orden disponible
                $maxOrden = $deptModel->getMaxOrden();

                $data = [
                    'nombre' => sanitize($_POST['nombre']),
                    'descripcion' => sanitize($_POST['descripcion'] ?? ''),
                    'tipo' => sanitize($_POST['tipo']),
                    'icono' => sanitize($_POST['icono']),
                    'color' => sanitize($_POST['color']),
                    'orden' => $maxOrden + 1,
                    'activo' => 1
                ];

                if ($deptModel->create($data)) {
                    setFlash('success', 'Departamento creado exitosamente');
                } else {
                    setFlash('error', 'Error al crear el departamento');
                }
                redirect('/admin/departamentos.php');
                break;

            case 'editar':
                $id = (int)$_POST['id'];
                $data = [
                    'nombre' => sanitize($_POST['nombre']),
                    'descripcion' => sanitize($_POST['descripcion'] ?? ''),
                    'tipo' => sanitize($_POST['tipo']),
                    'icono' => sanitize($_POST['icono']),
                    'color' => sanitize($_POST['color'])
                ];

                if ($deptModel->update($id, $data)) {
                    setFlash('success', 'Departamento actualizado exitosamente');
                } else {
                    setFlash('error', 'Error al actualizar el departamento');
                }
                redirect('/admin/departamentos.php');
                break;

            case 'eliminar':
                $id = (int)$_POST['id'];
                if ($deptModel->update($id, ['activo' => 0])) {
                    setFlash('success', 'Departamento eliminado exitosamente');
                } else {
                    setFlash('error', 'Error al eliminar el departamento');
                }
                redirect('/admin/departamentos.php');
                break;
        }
    }
}

// Obtener todos los departamentos (incluidos inactivos)
$departamentos = $deptModel->getAllWithStats();

// Departamento a editar
$editando = null;
if (isset($_GET['editar'])) {
    $editando = $deptModel->find($_GET['editar']);
}

$pageTitle = 'Gestión de Departamentos';
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
                                <li class="breadcrumb-item"><a href="<?php echo baseUrl('/admin/index.php'); ?>">Administración</a></li>
                                <li class="breadcrumb-item active">Departamentos</li>
                            </ol>
                        </nav>
                        <h2 class="page-title">
                            <i class="ti ti-building me-2"></i>
                            Gestión de Departamentos
                        </h2>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalDepartamento">
                            <i class="ti ti-plus me-1"></i>
                            Nuevo Departamento
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

            <!-- Lista de Departamentos -->
            <div class="row row-cards">
                <?php foreach ($departamentos as $dept): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card <?php echo $dept['activo'] ? '' : 'opacity-50'; ?>">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="avatar avatar-lg" style="background-color: <?php echo e($dept['color']); ?>">
                                        <i class="ti ti-<?php echo e($dept['icono']); ?>" style="font-size: 2rem;"></i>
                                    </span>
                                </div>
                                <div class="col">
                                    <h3 class="card-title mb-1">
                                        <?php echo e($dept['nombre']); ?>
                                        <?php if (!$dept['activo']): ?>
                                            <span class="badge bg-secondary ms-2">Inactivo</span>
                                        <?php endif; ?>
                                    </h3>
                                    <div class="text-muted small">
                                        <?php echo e($dept['descripcion'] ?: 'Sin descripción'); ?>
                                    </div>
                                    <div class="mt-2">
                                        <span class="badge bg-blue-lt">
                                            <i class="ti ti-layout-grid me-1"></i>
                                            <?php echo $dept['total_areas'] ?? 0; ?> áreas
                                        </span>
                                        <span class="badge bg-purple-lt ms-1">
                                            <i class="ti ti-users me-1"></i>
                                            <?php echo $dept['total_usuarios'] ?? 0; ?> usuarios
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="btn-list">
                                <a href="?editar=<?php echo $dept['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="ti ti-edit"></i> Editar
                                </a>
                                <a href="<?php echo baseUrl('/admin/areas.php?departamento=' . $dept['id']); ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-layout-grid"></i> Ver Áreas
                                </a>
                                <?php if ($dept['activo']): ?>
                                <form method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este departamento?');">
                                    <input type="hidden" name="action" value="eliminar">
                                    <input type="hidden" name="id" value="<?php echo $dept['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-ghost-danger">
                                        <i class="ti ti-trash"></i> Eliminar
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if (empty($departamentos)): ?>
                <div class="col-12">
                    <div class="empty text-center py-5">
                        <div class="empty-icon mb-3">
                            <i class="ti ti-building" style="font-size: 4rem; color: #94a3b8;"></i>
                        </div>
                        <h2>No hay departamentos</h2>
                        <p class="text-muted">Crea el primer departamento para comenzar.</p>
                        <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#modalDepartamento">
                            <i class="ti ti-plus me-1"></i>
                            Crear Primer Departamento
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<!-- Modal de Crear/Editar -->
<div class="modal modal-blur fade" id="modalDepartamento" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <?php echo $editando ? 'Editar Departamento' : 'Nuevo Departamento'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="<?php echo $editando ? 'editar' : 'crear'; ?>">
                    <?php if ($editando): ?>
                        <input type="hidden" name="id" value="<?php echo $editando['id']; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label required">Nombre</label>
                        <input type="text" name="nombre" class="form-control"
                               value="<?php echo e($editando['nombre'] ?? ''); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3"><?php echo e($editando['descripcion'] ?? ''); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Tipo</label>
                        <select name="tipo" class="form-select" required>
                            <option value="">Seleccionar tipo...</option>
                            <option value="agencia" <?php echo (isset($editando['tipo']) && $editando['tipo'] === 'agencia') ? 'selected' : ''; ?>>
                                🏢 Agencia (Sucursal física)
                            </option>
                            <option value="corporativo" <?php echo (isset($editando['tipo']) && $editando['tipo'] === 'corporativo') ? 'selected' : ''; ?>>
                                🏛️ Corporativo (Administrativo)
                            </option>
                            <option value="global" <?php echo (isset($editando['tipo']) && $editando['tipo'] === 'global') ? 'selected' : ''; ?>>
                                🌐 Global (Métricas consolidadas)
                            </option>
                        </select>
                        <div class="form-hint">Define la naturaleza del departamento</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?php
                            $input_name = 'icono';
                            $selected_icon = $editando['icono'] ?? 'building';
                            $label = 'Icono';
                            include __DIR__ . '/../../views/components/icon-picker.php';
                            ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Color</label>
                            <div class="input-group">
                                <input type="color" name="color" class="form-control form-control-color"
                                       value="<?php echo e($editando['color'] ?? '#3b82f6'); ?>" required>
                                <input type="text" class="form-control" id="color-hex"
                                       value="<?php echo e($editando['color'] ?? '#3b82f6'); ?>"
                                       pattern="^#[0-9A-Fa-f]{6}$" readonly>
                            </div>
                            <div class="form-hint">Color distintivo del departamento</div>
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
// Sincronizar color picker con input de texto
document.addEventListener('DOMContentLoaded', function() {
    const colorPicker = document.querySelector('input[name="color"]');
    const colorHex = document.getElementById('color-hex');

    if (colorPicker && colorHex) {
        colorPicker.addEventListener('input', function() {
            colorHex.value = this.value.toUpperCase();
        });
    }

    // Auto-abrir modal si hay parámetro editar
    <?php if ($editando): ?>
        setTimeout(function() {
            const modal = new bootstrap.Modal(document.getElementById('modalDepartamento'));
            modal.show();
        }, 100);
    <?php endif; ?>
});

// Limpiar formulario y URL al cerrar modal
document.getElementById('modalDepartamento').addEventListener('hidden.bs.modal', function() {
    <?php if ($editando): ?>
        // Limpiar parámetro editar de la URL
        window.history.replaceState({}, '', '<?php echo baseUrl('/admin/departamentos.php'); ?>');
    <?php else: ?>
        document.getElementById('modalDepartamento').querySelector('form').reset();
    <?php endif; ?>
});
</script>

<!-- Tabler JS -->
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/js/tabler.min.js"></script>
<script src="<?php echo baseUrl('/assets/js/theme-toggle.js'); ?>"></script>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
