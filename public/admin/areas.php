<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Services\PermissionService;
use App\Models\Area;
use App\Models\Departamento;

AuthMiddleware::handle();
PermissionMiddleware::requireAdmin();

$user = getCurrentUser();
$areaModel = new Area();
$deptModel = new Departamento();

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ✅ PROTECCIÓN CSRF
    csrf_validate();

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'crear':
                // Generar slug automático
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['nombre'])));

                // Obtener el siguiente orden disponible para este departamento
                $maxOrden = $areaModel->getMaxOrdenByDepartamento((int)$_POST['departamento_id']);

                $data = [
                    'departamento_id' => (int)$_POST['departamento_id'],
                    'nombre' => sanitize($_POST['nombre']),
                    'descripcion' => sanitize($_POST['descripcion'] ?? ''),
                    'slug' => $slug,
                    'icono' => sanitize($_POST['icono']),
                    'color' => sanitize($_POST['color']),
                    'orden' => $maxOrden + 1,
                    'activo' => 1
                ];

                if ($areaModel->create($data)) {
                    setFlash('success', 'Área creada exitosamente');
                } else {
                    setFlash('error', 'Error al crear el área');
                }
                redirect('/public/admin/areas.php' . (isset($_POST['departamento_id']) ? '?departamento=' . $_POST['departamento_id'] : ''));
                break;

            case 'editar':
                $id = (int)$_POST['id'];
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['nombre'])));

                $data = [
                    'departamento_id' => (int)$_POST['departamento_id'],
                    'nombre' => sanitize($_POST['nombre']),
                    'descripcion' => sanitize($_POST['descripcion'] ?? ''),
                    'slug' => $slug,
                    'icono' => sanitize($_POST['icono']),
                    'color' => sanitize($_POST['color'])
                ];

                if ($areaModel->update($id, $data)) {
                    setFlash('success', 'Área actualizada exitosamente');
                } else {
                    setFlash('error', 'Error al actualizar el área');
                }
                redirect('/public/admin/areas.php' . (isset($_POST['departamento_id']) ? '?departamento=' . $_POST['departamento_id'] : ''));
                break;

            case 'eliminar':
                $id = (int)$_POST['id'];
                if ($areaModel->update($id, ['activo' => 0])) {
                    setFlash('success', 'Área eliminada exitosamente');
                } else {
                    setFlash('error', 'Error al eliminar el área');
                }
                redirect('/public/admin/areas.php');
                break;
        }
    }
}

// Filtro por departamento
$departamento_filtro = isset($_GET['departamento']) ? (int)$_GET['departamento'] : null;

// Para dept_admin, forzar su departamento
if ($user['rol'] === 'dept_admin' && $user['departamento_id']) {
    $departamento_filtro = $user['departamento_id'];
}

// Obtener áreas
if ($departamento_filtro) {
    // Verificar permiso
    if (!PermissionService::canViewDepartamento($user, $departamento_filtro)) {
        die('No tienes permiso para ver este departamento.');
    }

    $areas = $areaModel->getAllWithStats($departamento_filtro);
    $departamento_actual = $deptModel->find($departamento_filtro);
} else {
    $areas = $areaModel->getAllWithStats();
    $departamento_actual = null;
}

// Obtener departamentos permitidos para el selector
$departamentos = PermissionService::getDepartamentosPermitidos($user);

// Área a editar
$editando = null;
if (isset($_GET['editar'])) {
    $editando = $areaModel->findWithDepartamento($_GET['editar']);
}

$pageTitle = 'Gestión de Áreas';
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
                                <?php if ($departamento_actual): ?>
                                    <li class="breadcrumb-item"><a href="<?php echo baseUrl('/public/admin/departamentos.php'); ?>">Departamentos</a></li>
                                    <li class="breadcrumb-item active"><?php echo e($departamento_actual['nombre']); ?></li>
                                <?php else: ?>
                                    <li class="breadcrumb-item active">Áreas</li>
                                <?php endif; ?>
                            </ol>
                        </nav>
                        <h2 class="page-title">
                            <i class="ti ti-layout-grid me-2"></i>
                            <?php echo $departamento_actual ? 'Áreas de ' . e($departamento_actual['nombre']) : 'Gestión de Áreas'; ?>
                        </h2>
                    </div>
                    <div class="col-auto">
                        <div class="btn-list">
                            <?php if (!$departamento_actual): ?>
                            <div class="dropdown">
                                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="ti ti-filter me-1"></i>
                                    Filtrar por Departamento
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item <?php echo !$departamento_filtro ? 'active' : ''; ?>"
                                       href="<?php echo baseUrl('/public/admin/areas.php'); ?>">
                                        Todos los departamentos
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <?php foreach ($departamentos as $dept): ?>
                                    <a class="dropdown-item"
                                       href="<?php echo baseUrl('/public/admin/areas.php?departamento=' . $dept['id']); ?>">
                                        <i class="ti ti-<?php echo e($dept['icono']); ?> me-2"></i>
                                        <?php echo e($dept['nombre']); ?>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalArea">
                                <i class="ti ti-plus me-1"></i>
                                Nueva Área
                            </button>
                        </div>
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

            <!-- Lista de Áreas -->
            <div class="row row-cards">
                <?php foreach ($areas as $area): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card <?php echo $area['activo'] ? '' : 'opacity-50'; ?>">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="avatar avatar-lg" style="background-color: <?php echo e($area['color']); ?>">
                                        <i class="ti ti-<?php echo e($area['icono']); ?>" style="font-size: 2rem;"></i>
                                    </span>
                                </div>
                                <div class="col">
                                    <h3 class="card-title mb-1">
                                        <?php echo e($area['nombre']); ?>
                                        <?php if (!$area['activo']): ?>
                                            <span class="badge bg-secondary ms-2">Inactiva</span>
                                        <?php endif; ?>
                                    </h3>
                                    <div class="text-muted small mb-2">
                                        <i class="ti ti-building me-1"></i>
                                        <?php echo e($area['departamento_nombre']); ?>
                                        <?php
                                        // Mostrar badge de tipo de departamento
                                        $tipo = $area['departamento_tipo'] ?? 'corporativo';
                                        if ($tipo === 'agencia'):
                                        ?>
                                            <span class="badge bg-success-lt ms-1" title="Agencia">
                                                <i class="ti ti-building-bank"></i>
                                            </span>
                                        <?php elseif ($tipo === 'global'): ?>
                                            <span class="badge bg-purple-lt ms-1" title="Global">
                                                <i class="ti ti-world"></i>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-blue-lt ms-1" title="Corporativo">
                                                <i class="ti ti-building"></i>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted small">
                                        <?php echo e($area['descripcion'] ?: 'Sin descripción'); ?>
                                    </div>
                                    <div class="mt-2">
                                        <span class="badge bg-blue-lt">
                                            <i class="ti ti-chart-line me-1"></i>
                                            <?php echo $area['total_metricas'] ?? 0; ?> métricas
                                        </span>
                                        <span class="badge bg-purple-lt ms-1">
                                            <i class="ti ti-chart-bar me-1"></i>
                                            <?php echo $area['total_graficos'] ?? 0; ?> gráficos
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="btn-list">
                                <a href="?editar=<?php echo $area['id']; ?><?php echo $departamento_filtro ? '&departamento=' . $departamento_filtro : ''; ?>"
                                   class="btn btn-sm btn-primary">
                                    <i class="ti ti-edit"></i> Editar
                                </a>
                                <a href="<?php echo baseUrl('/public/admin/metricas.php?area=' . $area['id']); ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-chart-line"></i> Ver Métricas
                                </a>
                                <?php if ($area['activo']): ?>
                                <form method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta área?');">
                                    <input type="hidden" name="action" value="eliminar">
                                    <input type="hidden" name="id" value="<?php echo $area['id']; ?>">
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

                <?php if (empty($areas)): ?>
                <div class="col-12">
                    <div class="empty text-center py-5">
                        <div class="empty-icon mb-3">
                            <i class="ti ti-layout-grid" style="font-size: 4rem; color: #94a3b8;"></i>
                        </div>
                        <h2>No hay áreas<?php echo $departamento_actual ? ' en este departamento' : ''; ?></h2>
                        <p class="text-muted">Crea la primera área para comenzar.</p>
                        <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#modalArea">
                            <i class="ti ti-plus me-1"></i>
                            Crear Primera Área
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<!-- Modal de Crear/Editar -->
<div class="modal modal-blur fade" id="modalArea" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <?php echo $editando ? 'Editar Área' : 'Nueva Área'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="<?php echo $editando ? 'editar' : 'crear'; ?>">
                    <?php if ($editando): ?>
                        <input type="hidden" name="id" value="<?php echo $editando['id']; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label required">Departamento</label>
                        <select name="departamento_id" class="form-select" required <?php echo $user['rol'] === 'dept_admin' ? 'disabled' : ''; ?>>
                            <?php if ($user['rol'] === 'dept_admin'): ?>
                                <option value="<?php echo $user['departamento_id']; ?>" selected>
                                    <?php echo e($departamento_actual['nombre']); ?>
                                </option>
                            <?php else: ?>
                                <option value="">Seleccionar departamento...</option>
                                <?php foreach ($departamentos as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>"
                                        <?php echo ($editando && $editando['departamento_id'] == $dept['id']) || ($departamento_filtro == $dept['id']) ? 'selected' : ''; ?>
                                        data-tipo="<?php echo e($dept['tipo'] ?? 'corporativo'); ?>">
                                    <?php
                                    echo e($dept['nombre']);
                                    // Mostrar tipo entre paréntesis
                                    $tipo = $dept['tipo'] ?? 'corporativo';
                                    $tipo_label = $tipo === 'agencia' ? 'Agencia' : ($tipo === 'global' ? 'Global' : 'Corporativo');
                                    echo ' (' . $tipo_label . ')';
                                    ?>
                                </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <?php if ($user['rol'] === 'dept_admin'): ?>
                            <input type="hidden" name="departamento_id" value="<?php echo $user['departamento_id']; ?>">
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Nombre</label>
                        <input type="text" name="nombre" class="form-control"
                               value="<?php echo e($editando['nombre'] ?? ''); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3"><?php echo e($editando['descripcion'] ?? ''); ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?php
                            $input_name = 'icono';
                            $selected_icon = $editando['icono'] ?? 'layout-grid';
                            $label = 'Icono';
                            include __DIR__ . '/../../views/components/icon-picker.php';
                            ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Color</label>
                            <div class="input-group">
                                <input type="color" name="color" class="form-control form-control-color"
                                       value="<?php echo e($editando['color'] ?? '#10b981'); ?>" required>
                                <input type="text" class="form-control" id="color-hex"
                                       value="<?php echo e($editando['color'] ?? '#10b981'); ?>"
                                       pattern="^#[0-9A-Fa-f]{6}$" readonly>
                            </div>
                            <div class="form-hint">Color distintivo del área</div>
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
            const modal = new bootstrap.Modal(document.getElementById('modalArea'));
            modal.show();
        }, 100);
    <?php endif; ?>
});

// Limpiar formulario y URL al cerrar modal
document.getElementById('modalArea').addEventListener('hidden.bs.modal', function() {
    <?php if ($editando): ?>
        // Limpiar parámetro editar de la URL
        window.history.replaceState({}, '', '<?php echo baseUrl('/public/admin/areas.php'); ?><?php echo $departamento_filtro ? '?departamento=' . $departamento_filtro : ''; ?>');
    <?php else: ?>
        document.getElementById('modalArea').querySelector('form').reset();
    <?php endif; ?>
});
</script>

<!-- Tabler JS -->
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/js/tabler.min.js"></script>
<script src="<?php echo baseUrl('/public/assets/js/theme-toggle.js'); ?>"></script>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
