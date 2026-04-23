<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Services\PermissionService;
use App\Models\Meta;
use App\Models\Metrica;
use App\Models\Periodo;
use App\Models\Departamento;
use App\Models\Area;

AuthMiddleware::handle();
PermissionMiddleware::requireAdmin();

$user = getCurrentUser();
$metaModel = new Meta();
$metricaModel = new Metrica();
$periodoModel = new Periodo();
$deptModel = new Departamento();
$areaModel = new Area();

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ✅ PROTECCIÓN CSRF
    csrf_validate();

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'crear':
                $metrica_id = (int)$_POST['metrica_id'];
                $tipo_meta = sanitize($_POST['tipo_meta']);

                // Verificar que la métrica tenga tiene_meta = 1
                $metrica = $metricaModel->find($metrica_id);
                if (!$metrica || !$metrica['tiene_meta']) {
                    setFlash('error', 'Esta métrica no está configurada para tener metas');
                    redirect('/public/admin/metas.php');
                    break;
                }

                // Verificar permiso
                $area = $areaModel->findWithDepartamento($metrica['area_id']);
                if (!PermissionService::canEditArea($user, $metrica['area_id'])) {
                    setFlash('error', 'No tienes permiso para editar metas de esta área');
                    redirect('/public/admin/metas.php');
                    break;
                }

                $data = [
                    'metrica_id' => $metrica_id,
                    'tipo_meta' => $tipo_meta,
                    'valor_objetivo' => (float)$_POST['valor_objetivo'],
                    'tipo_comparacion' => sanitize($_POST['tipo_comparacion']),
                    'activo' => 1
                ];

                if ($tipo_meta === 'anual') {
                    $data['ejercicio'] = (int)$_POST['ejercicio'];
                    $data['periodo_id'] = null;

                    // Verificar que no exista ya
                    if ($metaModel->exists($metrica_id, 'anual', $data['ejercicio'])) {
                        setFlash('error', 'Ya existe una meta anual para este ejercicio');
                        redirect('/public/admin/metas.php?metrica=' . $metrica_id);
                        break;
                    }
                } else {
                    $data['periodo_id'] = (int)$_POST['periodo_id'];
                    $periodo = $periodoModel->find($data['periodo_id']);
                    $data['ejercicio'] = $periodo['ejercicio'];

                    // Verificar que no exista ya
                    if ($metaModel->exists($metrica_id, 'mensual', null, $data['periodo_id'])) {
                        setFlash('error', 'Ya existe una meta para este período');
                        redirect('/public/admin/metas.php?metrica=' . $metrica_id);
                        break;
                    }
                }

                if ($metaModel->create($data)) {
                    setFlash('success', '✓ Meta creada exitosamente');
                } else {
                    setFlash('error', 'Error al crear la meta');
                }
                redirect('/public/admin/metas.php?metrica=' . $metrica_id);
                break;

            case 'editar':
                $id = (int)$_POST['id'];
                $meta = $metaModel->find($id);

                if ($meta) {
                    // Verificar permiso
                    $metrica = $metricaModel->find($meta['metrica_id']);
                    if (!PermissionService::canEditArea($user, $metrica['area_id'])) {
                        setFlash('error', 'No tienes permiso');
                        redirect('/public/admin/metas.php');
                        break;
                    }

                    $data = [
                        'valor_objetivo' => (float)$_POST['valor_objetivo'],
                        'tipo_comparacion' => sanitize($_POST['tipo_comparacion'])
                    ];

                    if ($metaModel->update($id, $data)) {
                        setFlash('success', '✓ Meta actualizada');
                    } else {
                        setFlash('error', 'Error al actualizar');
                    }
                }
                redirect('/public/admin/metas.php?metrica=' . $meta['metrica_id']);
                break;

            case 'eliminar':
                $id = (int)$_POST['id'];
                $meta = $metaModel->find($id);

                if ($meta) {
                    $metrica = $metricaModel->find($meta['metrica_id']);
                    if (PermissionService::canEditArea($user, $metrica['area_id'])) {
                        $metaModel->update($id, ['activo' => 0]);
                        setFlash('success', '✓ Meta eliminada');
                    }
                }
                redirect('/public/admin/metas.php');
                break;
        }
    }
}

// Obtener métricas con metas según permisos
$metrica_seleccionada_id = $_GET['metrica'] ?? null;

if ($user['rol'] === 'super_admin') {
    // Super admin ve todas las métricas con metas
    $metricas_con_metas = $metricaModel->query("
        SELECT m.*, a.nombre as area_nombre, d.nombre as departamento_nombre
        FROM metricas m
        JOIN areas a ON m.area_id = a.id
        JOIN departamentos d ON a.departamento_id = d.id
        WHERE m.tiene_meta = 1 AND m.activo = 1
        ORDER BY d.nombre, a.nombre, m.nombre
    ")->fetchAll();
} else {
    // Dept admin solo ve métricas de su departamento
    $metricas_con_metas = $metricaModel->query("
        SELECT m.*, a.nombre as area_nombre, d.nombre as departamento_nombre
        FROM metricas m
        JOIN areas a ON m.area_id = a.id
        JOIN departamentos d ON a.departamento_id = d.id
        WHERE m.tiene_meta = 1
        AND m.activo = 1
        AND d.id = ?
        ORDER BY a.nombre, m.nombre
    ", [$user['departamento_id']])->fetchAll();
}

// Obtener metas si hay métrica seleccionada
$metas = [];
$metrica_seleccionada = null;
if ($metrica_seleccionada_id) {
    $metrica_seleccionada = $metricaModel->findWithRelations($metrica_seleccionada_id);
    if ($metrica_seleccionada && PermissionService::canViewArea($user, $metrica_seleccionada['area_id'])) {
        $metas = $metaModel->getByMetrica($metrica_seleccionada_id);
    }
}

// Obtener períodos disponibles para metas mensuales
$periodos = $periodoModel->getAll();

// Meta a editar
$editando = null;
if (isset($_GET['editar'])) {
    $editando = $metaModel->find($_GET['editar']);
}

$pageTitle = 'Gestión de Metas';
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
                                <li class="breadcrumb-item active">Metas</li>
                            </ol>
                        </nav>
                        <h2 class="page-title">
                            <i class="ti ti-target me-2"></i>
                            Gestión de Metas
                        </h2>
                    </div>
                    <?php if ($metrica_seleccionada): ?>
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalMeta">
                            <i class="ti ti-plus me-1"></i>
                            Nueva Meta
                        </button>
                    </div>
                    <?php endif; ?>
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

            <div class="row">
                <!-- Columna izquierda: Lista de métricas -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Métricas con Metas</h3>
                        </div>
                        <div class="list-group list-group-flush" style="max-height: 600px; overflow-y: auto;">
                            <?php if (empty($metricas_con_metas)): ?>
                                <div class="list-group-item text-muted text-center py-4">
                                    <i class="ti ti-target mb-2" style="font-size: 2rem;"></i>
                                    <div>No hay métricas configuradas con metas</div>
                                    <a href="<?php echo baseUrl('/public/admin/metricas.php'); ?>" class="btn btn-sm btn-primary mt-2">
                                        Ir a Métricas
                                    </a>
                                </div>
                            <?php else: ?>
                                <?php foreach ($metricas_con_metas as $m): ?>
                                <a href="?metrica=<?php echo $m['id']; ?>"
                                   class="list-group-item list-group-item-action <?php echo $metrica_seleccionada_id == $m['id'] ? 'active' : ''; ?>">
                                    <div class="d-flex align-items-center">
                                        <span class="avatar avatar-sm me-2">
                                            <i class="ti ti-<?php echo e($m['icono']); ?>"></i>
                                        </span>
                                        <div class="flex-fill">
                                            <div class="fw-bold"><?php echo e($m['nombre']); ?></div>
                                            <div class="text-muted small">
                                                <?php echo e($m['departamento_nombre']); ?> › <?php echo e($m['area_nombre']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha: Metas de la métrica seleccionada -->
                <div class="col-md-8">
                    <?php if (!$metrica_seleccionada): ?>
                        <div class="empty text-center py-5">
                            <div class="empty-icon mb-3">
                                <i class="ti ti-target" style="font-size: 4rem; color: #94a3b8;"></i>
                            </div>
                            <h2>Selecciona una métrica</h2>
                            <p class="text-muted">Elige una métrica de la lista para ver y gestionar sus metas</p>
                        </div>
                    <?php else: ?>
                        <div class="card mb-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="ti ti-<?php echo e($metrica_seleccionada['icono']); ?> me-2"></i>
                                    <?php echo e($metrica_seleccionada['nombre']); ?>
                                </h3>
                                <div class="card-subtitle">
                                    <?php echo e($metrica_seleccionada['departamento_nombre']); ?> ›
                                    <?php echo e($metrica_seleccionada['area_nombre']); ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-auto">
                                        <span class="badge bg-blue-lt"><?php echo e($metrica_seleccionada['unidad'] ?: 'sin unidad'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (empty($metas)): ?>
                            <div class="empty text-center py-5">
                                <div class="empty-icon mb-3">
                                    <i class="ti ti-target-off" style="font-size: 3rem; color: #94a3b8;"></i>
                                </div>
                                <h3>No hay metas definidas</h3>
                                <p class="text-muted">Crea la primera meta para esta métrica</p>
                                <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#modalMeta">
                                    <i class="ti ti-plus me-1"></i>
                                    Crear Primera Meta
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="row row-cards">
                                <?php foreach ($metas as $meta): ?>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <span class="badge bg-<?php echo $meta['tipo_meta'] === 'anual' ? 'blue' : 'green'; ?>-lt">
                                                    <i class="ti ti-<?php echo $meta['tipo_meta'] === 'anual' ? 'calendar' : 'calendar-month'; ?> me-1"></i>
                                                    <?php echo ucfirst($meta['tipo_meta']); ?>
                                                </span>
                                                <?php if (!$meta['activo']): ?>
                                                    <span class="badge bg-secondary">Inactiva</span>
                                                <?php endif; ?>
                                            </div>

                                            <div class="h1 mb-2">
                                                <?php echo number_format($meta['valor_objetivo'], 0); ?>
                                                <span class="text-muted fs-4"><?php echo e($metrica_seleccionada['unidad']); ?></span>
                                            </div>

                                            <div class="text-muted mb-3">
                                                <?php if ($meta['tipo_meta'] === 'anual'): ?>
                                                    <i class="ti ti-calendar me-1"></i>
                                                    Año <?php echo $meta['ejercicio']; ?>
                                                    <div class="small text-muted mt-1">
                                                        ≈ <?php echo number_format($meta['valor_objetivo'] / 12, 1); ?> <?php echo e($metrica_seleccionada['unidad']); ?>/mes
                                                    </div>
                                                <?php else: ?>
                                                    <i class="ti ti-calendar-month me-1"></i>
                                                    <?php echo e($meta['periodo_nombre']); ?>
                                                <?php endif; ?>
                                            </div>

                                            <div class="small text-muted mb-2">
                                                Comparación: <strong><?php
                                                    $comparaciones = [
                                                        'mayor_igual' => '≥ Mayor o igual',
                                                        'menor_igual' => '≤ Menor o igual',
                                                        'igual' => '= Igual',
                                                        'rango' => '↔ Rango'
                                                    ];
                                                    echo $comparaciones[$meta['tipo_comparacion']] ?? $meta['tipo_comparacion'];
                                                ?></strong>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <div class="btn-list">
                                                <a href="?metrica=<?php echo $metrica_seleccionada_id; ?>&editar=<?php echo $meta['id']; ?>"
                                                   class="btn btn-sm btn-primary">
                                                    <i class="ti ti-edit"></i> Editar
                                                </a>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta meta?');">
                                                    <input type="hidden" name="action" value="eliminar">
                                                    <input type="hidden" name="id" value="<?php echo $meta['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-ghost-danger">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal Crear/Editar Meta -->
<div class="modal modal-blur fade" id="modalMeta" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <?php echo $editando ? 'Editar Meta' : 'Nueva Meta'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="<?php echo $editando ? 'editar' : 'crear'; ?>">
                    <?php if ($editando): ?>
                        <input type="hidden" name="id" value="<?php echo $editando['id']; ?>">
                    <?php else: ?>
                        <input type="hidden" name="metrica_id" value="<?php echo $metrica_seleccionada_id; ?>">
                    <?php endif; ?>

                    <?php if (!$editando): ?>
                    <div class="mb-3">
                        <label class="form-label required">Tipo de Meta</label>
                        <select name="tipo_meta" id="tipo_meta" class="form-select" required>
                            <option value="">Seleccionar...</option>
                            <option value="anual">Meta Anual (se divide entre 12 meses)</option>
                            <option value="mensual">Meta Mensual (específica para un mes)</option>
                        </select>
                    </div>

                    <div class="mb-3" id="div_ejercicio" style="display: none;">
                        <label class="form-label required">Año (Ejercicio)</label>
                        <input type="number" name="ejercicio" class="form-control"
                               value="<?php echo date('Y'); ?>" min="2020" max="2099">
                    </div>

                    <div class="mb-3" id="div_periodo" style="display: none;">
                        <label class="form-label required">Período</label>
                        <select name="periodo_id" class="form-select">
                            <option value="">Seleccionar período...</option>
                            <?php foreach ($periodos as $p): ?>
                            <option value="<?php echo $p['id']; ?>">
                                <?php echo e($p['nombre']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label required">Valor Objetivo</label>
                        <input type="number" name="valor_objetivo" class="form-control"
                               step="0.01" value="<?php echo $editando['valor_objetivo'] ?? ''; ?>" required>
                        <div class="form-hint">
                            <?php if ($metrica_seleccionada): ?>
                                Meta en <?php echo e($metrica_seleccionada['unidad'] ?: 'unidades'); ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Tipo de Comparación</label>
                        <select name="tipo_comparacion" class="form-select" required>
                            <option value="mayor_igual" <?php echo ($editando && $editando['tipo_comparacion'] === 'mayor_igual') ? 'selected' : ''; ?>>
                                ≥ Mayor o igual (cumple si supera la meta)
                            </option>
                            <option value="menor_igual" <?php echo ($editando && $editando['tipo_comparacion'] === 'menor_igual') ? 'selected' : ''; ?>>
                                ≤ Menor o igual (cumple si está por debajo)
                            </option>
                            <option value="igual" <?php echo ($editando && $editando['tipo_comparacion'] === 'igual') ? 'selected' : ''; ?>>
                                = Igual (cumple si es exacto)
                            </option>
                        </select>
                        <div class="form-hint">
                            Define cómo se evalúa el cumplimiento de la meta
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
document.addEventListener('DOMContentLoaded', function() {
    const tipoMeta = document.getElementById('tipo_meta');
    const divEjercicio = document.getElementById('div_ejercicio');
    const divPeriodo = document.getElementById('div_periodo');

    if (tipoMeta) {
        tipoMeta.addEventListener('change', function() {
            if (this.value === 'anual') {
                divEjercicio.style.display = 'block';
                divPeriodo.style.display = 'none';
                divEjercicio.querySelector('input').setAttribute('required', 'required');
                divPeriodo.querySelector('select').removeAttribute('required');
            } else if (this.value === 'mensual') {
                divEjercicio.style.display = 'none';
                divPeriodo.style.display = 'block';
                divEjercicio.querySelector('input').removeAttribute('required');
                divPeriodo.querySelector('select').setAttribute('required', 'required');
            } else {
                divEjercicio.style.display = 'none';
                divPeriodo.style.display = 'none';
            }
        });
    }

    // Auto-abrir modal si hay edición
    <?php if ($editando): ?>
        const modal = new bootstrap.Modal(document.getElementById('modalMeta'));
        modal.show();
    <?php endif; ?>
});
</script>

<!-- Tabler JS -->
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/js/tabler.min.js"></script>
<script src="<?php echo baseUrl('/public/assets/js/theme-toggle.js'); ?>"></script>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
