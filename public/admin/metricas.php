<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Services\PermissionService;
use App\Services\MetricaCalculadaService;
use App\Models\Metrica;
use App\Models\Meta;
use App\Models\Departamento;
use App\Models\Area;

AuthMiddleware::handle();
PermissionMiddleware::requireAdmin();

$user = getCurrentUser();
$metricaModel = new Metrica();
$metaModel = new Meta();
$deptModel = new Departamento();
$areaModel = new Area();
$calculadaService = new MetricaCalculadaService();

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ✅ PROTECCIÓN CSRF
    csrf_validate();

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'crear':
                // Generar slug automático
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['nombre'])));

                // Obtener el siguiente orden disponible para esta área
                $maxOrden = $metricaModel->getMaxOrdenByArea((int)$_POST['area_id']);

                $data = [
                    'area_id' => (int)$_POST['area_id'],
                    'nombre' => sanitize($_POST['nombre']),
                    'descripcion' => sanitize($_POST['descripcion'] ?? ''),
                    'slug' => $slug,
                    'unidad' => sanitize($_POST['unidad'] ?? ''),
                    'tipo_valor' => sanitize($_POST['tipo_valor']),
                    'icono' => sanitize($_POST['icono']),
                    'es_calculada' => isset($_POST['es_calculada']) ? 1 : 0,
                    'tiene_meta' => isset($_POST['tiene_meta']) ? 1 : 0,
                    'orden' => $maxOrden + 1,
                    'activo' => 1
                ];

                $metrica_id = $metricaModel->create($data);

                if ($metrica_id) {
                    // Si es calculada, guardar componentes
                    if (isset($_POST['es_calculada']) && !empty($_POST['componentes'])) {
                        $operacion = sanitize($_POST['operacion'] ?? 'suma');
                        $calculadaService->guardarComponentes($metrica_id, $_POST['componentes'], $operacion);
                    }

                    setFlash('success', 'Métrica creada exitosamente');
                } else {
                    setFlash('error', 'Error al crear la métrica');
                }
                redirect('/public/admin/metricas.php' . (isset($_POST['area_id']) ? '?area=' . $_POST['area_id'] : ''));
                break;

            case 'editar':
                $id = (int)$_POST['id'];
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['nombre'])));

                $data = [
                    'area_id' => (int)$_POST['area_id'],
                    'nombre' => sanitize($_POST['nombre']),
                    'descripcion' => sanitize($_POST['descripcion'] ?? ''),
                    'slug' => $slug,
                    'unidad' => sanitize($_POST['unidad'] ?? ''),
                    'tipo_valor' => sanitize($_POST['tipo_valor']),
                    'icono' => sanitize($_POST['icono']),
                    'es_calculada' => isset($_POST['es_calculada']) ? 1 : 0,
                    'tiene_meta' => isset($_POST['tiene_meta']) ? 1 : 0
                ];

                if ($metricaModel->update($id, $data)) {
                    // Si es calculada, actualizar componentes
                    if (isset($_POST['es_calculada']) && !empty($_POST['componentes'])) {
                        $operacion = sanitize($_POST['operacion'] ?? 'suma');
                        $calculadaService->guardarComponentes($id, $_POST['componentes'], $operacion);
                    }

                    setFlash('success', 'Métrica actualizada exitosamente');
                } else {
                    setFlash('error', 'Error al actualizar la métrica');
                }
                redirect('/public/admin/metricas.php' . (isset($_POST['area_id']) ? '?area=' . $_POST['area_id'] : ''));
                break;

            case 'eliminar':
                $id = (int)$_POST['id'];

                // Soft delete: marcar métrica como inactiva
                if ($metricaModel->update($id, ['activo' => 0])) {
                    // Cascada: también desactivar todas las metas asociadas
                    $metas = $metaModel->getByMetrica($id);
                    $metas_desactivadas = 0;
                    foreach ($metas as $meta) {
                        if ($meta['activo'] == 1) {
                            $metaModel->update($meta['id'], ['activo' => 0]);
                            $metas_desactivadas++;
                        }
                    }

                    $mensaje = 'Métrica eliminada exitosamente';
                    if ($metas_desactivadas > 0) {
                        $mensaje .= " ($metas_desactivadas meta(s) también desactivada(s))";
                    }
                    setFlash('success', $mensaje);
                } else {
                    setFlash('error', 'Error al eliminar la métrica');
                }
                redirect('/public/admin/metricas.php');
                break;

            case 'restaurar':
                $id = (int)$_POST['id'];

                // Restaurar métrica
                if ($metricaModel->update($id, ['activo' => 1])) {
                    // Cascada: también reactivar todas las metas asociadas
                    $metas = $metaModel->getByMetrica($id);
                    $metas_restauradas = 0;
                    foreach ($metas as $meta) {
                        if ($meta['activo'] == 0) {
                            $metaModel->update($meta['id'], ['activo' => 1]);
                            $metas_restauradas++;
                        }
                    }

                    $mensaje = '✓ Métrica restaurada exitosamente';
                    if ($metas_restauradas > 0) {
                        $mensaje .= " ($metas_restauradas meta(s) también restaurada(s))";
                    }
                    setFlash('success', $mensaje);
                } else {
                    setFlash('error', 'Error al restaurar la métrica');
                }
                redirect('/public/admin/metricas.php?mostrar=todas');
                break;
        }
    }
}

// Filtro por área
$area_filtro = isset($_GET['area']) ? (int)$_GET['area'] : null;

// Filtro para mostrar activas o todas (incluyendo eliminadas)
$mostrar_filtro = $_GET['mostrar'] ?? 'activas';

// Obtener métricas
if ($area_filtro) {
    // Verificar permiso para ver el área
    if (!PermissionService::canViewArea($user, $area_filtro)) {
        die('No tienes permiso para ver esta área.');
    }

    $metricas = $metricaModel->getByAreaWithStats($area_filtro);
    $area_actual = $areaModel->findWithDepartamento($area_filtro);
} else {
    // Si es dept_admin, solo mostrar métricas de su departamento
    if ($user['rol'] === 'dept_admin' && $user['departamento_id']) {
        $metricas = $metricaModel->getAllWithStatsByDepartamento($user['departamento_id']);
    } else {
        $metricas = $metricaModel->getAllWithStats();
    }
    $area_actual = null;
}

// Aplicar filtro de activo/inactivo
if ($mostrar_filtro === 'activas') {
    $metricas = array_filter($metricas, function($m) {
        return $m['activo'] == 1;
    });
}

// Obtener departamentos permitidos para el selector
$departamentos = PermissionService::getDepartamentosPermitidos($user);

// Métrica a editar
$editando = null;
$componentes_editando = [];
if (isset($_GET['editar'])) {
    $editando = $metricaModel->findWithRelations($_GET['editar']);

    // Si es calculada, obtener componentes
    if ($editando && $editando['es_calculada']) {
        $componentes_editando = $calculadaService->getComponentes($_GET['editar']);
    }
}

$pageTitle = 'Gestión de Métricas';
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
                                <?php if ($area_actual): ?>
                                    <li class="breadcrumb-item"><a href="<?php echo baseUrl('/public/admin/areas.php?departamento=' . $area_actual['departamento_id']); ?>">Áreas</a></li>
                                    <li class="breadcrumb-item active"><?php echo e($area_actual['nombre']); ?></li>
                                <?php else: ?>
                                    <li class="breadcrumb-item active">Métricas</li>
                                <?php endif; ?>
                            </ol>
                        </nav>
                        <h2 class="page-title">
                            <i class="ti ti-chart-line me-2"></i>
                            <?php echo $area_actual ? 'Métricas de ' . e($area_actual['nombre']) : 'Gestión de Métricas'; ?>
                        </h2>
                    </div>
                    <div class="col-auto">
                        <div class="btn-list">
                            <?php if (!$area_actual): ?>
                            <div class="dropdown">
                                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="ti ti-filter me-1"></i>
                                    Filtrar por Área
                                </button>
                                <div class="dropdown-menu dropdown-menu-end" style="max-height: 400px; overflow-y: auto;">
                                    <a class="dropdown-item <?php echo !$area_filtro ? 'active' : ''; ?>"
                                       href="<?php echo baseUrl('/public/admin/metricas.php'); ?>">
                                        Todas las áreas
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <?php foreach ($departamentos as $dept):
                                        $areas = $areaModel->getByDepartamento($dept['id']);
                                        if (!empty($areas)):
                                    ?>
                                        <h6 class="dropdown-header"><?php echo e($dept['nombre']); ?></h6>
                                        <?php foreach ($areas as $area): ?>
                                        <a class="dropdown-item"
                                           href="<?php echo baseUrl('/public/admin/metricas.php?area=' . $area['id']); ?>">
                                            <i class="ti ti-<?php echo e($area['icono']); ?> me-2"></i>
                                            <?php echo e($area['nombre']); ?>
                                        </a>
                                        <?php endforeach; ?>
                                    <?php endif; endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Toggle Activas/Todas -->
                            <div class="btn-group" role="group">
                                <a href="?<?php echo $area_filtro ? 'area=' . $area_filtro . '&' : ''; ?>mostrar=activas"
                                   class="btn btn-outline-secondary <?php echo $mostrar_filtro === 'activas' ? 'active' : ''; ?>">
                                    <i class="ti ti-eye me-1"></i>
                                    Activas
                                </a>
                                <a href="?<?php echo $area_filtro ? 'area=' . $area_filtro . '&' : ''; ?>mostrar=todas"
                                   class="btn btn-outline-secondary <?php echo $mostrar_filtro === 'todas' ? 'active' : ''; ?>">
                                    <i class="ti ti-archive me-1"></i>
                                    Todas
                                </a>
                            </div>

                            <?php if (!empty($metricas)): ?>
                            <button type="button" class="btn btn-outline-primary" onclick="exportarMetricasActuales()">
                                <i class="ti ti-download me-1"></i>
                                Exportar
                            </button>
                            <?php endif; ?>

                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalMetrica">
                                <i class="ti ti-plus me-1"></i>
                                Nueva Métrica
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

            <!-- Lista de Métricas -->
            <?php if (empty($metricas)): ?>
                <div class="empty text-center py-5">
                    <div class="empty-icon mb-3">
                        <i class="ti ti-chart-line" style="font-size: 4rem; color: #94a3b8;"></i>
                    </div>
                    <h2>No hay métricas<?php echo $area_actual ? ' en esta área' : ''; ?></h2>
                    <p class="text-muted">Crea la primera métrica para comenzar a medir resultados.</p>
                    <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#modalMetrica">
                        <i class="ti ti-plus me-1"></i>
                        Crear Primera Métrica
                    </button>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Métrica</th>
                                    <th>Área</th>
                                    <th>Tipo</th>
                                    <th>Unidad</th>
                                    <th>Valores</th>
                                    <th>Estado</th>
                                    <th class="w-1"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($metricas as $metrica): ?>
                                <tr class="<?php echo !$metrica['activo'] ? 'opacity-50' : ''; ?>">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="avatar avatar-sm me-2" style="background-color: <?php echo e($metrica['area_color'] ?? '#3b82f6'); ?>">
                                                <i class="ti ti-<?php echo e($metrica['icono']); ?>"></i>
                                            </span>
                                            <div>
                                                <div class="fw-bold"><?php echo e($metrica['nombre']); ?></div>
                                                <?php if ($metrica['descripcion']): ?>
                                                <div class="text-muted small"><?php echo e($metrica['descripcion']); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-muted small"><?php echo e($metrica['departamento_nombre'] ?? ''); ?></div>
                                        <div><?php echo e($metrica['area_nombre']); ?></div>
                                    </td>
                                    <td>
                                        <?php
                                        $tipos = [
                                            'numero' => ['nombre' => 'Número', 'icon' => 'hash'],
                                            'porcentaje' => ['nombre' => 'Porcentaje', 'icon' => 'percentage'],
                                            'tiempo' => ['nombre' => 'Tiempo', 'icon' => 'clock'],
                                            'decimal' => ['nombre' => 'Decimal', 'icon' => 'decimal']
                                        ];
                                        $tipo = $tipos[$metrica['tipo_valor']] ?? ['nombre' => $metrica['tipo_valor'], 'icon' => 'hash'];
                                        ?>
                                        <span class="badge bg-blue-lt">
                                            <i class="ti ti-<?php echo $tipo['icon']; ?> me-1"></i>
                                            <?php echo $tipo['nombre']; ?>
                                        </span>
                                        <?php if ($metrica['es_calculada']): ?>
                                        <span class="badge bg-purple-lt ms-1">
                                            <i class="ti ti-calculator me-1"></i>
                                            Calculada
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo e($metrica['unidad'] ?: '—'); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-green-lt">
                                            <?php echo $metrica['total_valores'] ?? 0; ?> registros
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($metrica['activo']): ?>
                                            <span class="badge bg-success">Activa</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactiva</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-list">
                                            <a href="?editar=<?php echo $metrica['id']; ?><?php echo $area_filtro ? '&area=' . $area_filtro : ''; ?>"
                                               class="btn btn-sm btn-icon btn-ghost-primary" title="Editar">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                            <?php if ($metrica['activo']): ?>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta métrica?');">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="action" value="eliminar">
                                                <input type="hidden" name="id" value="<?php echo $metrica['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-icon btn-ghost-danger" title="Eliminar">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                            <?php else: ?>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('¿Restaurar esta métrica y sus metas asociadas?');">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="action" value="restaurar">
                                                <input type="hidden" name="id" value="<?php echo $metrica['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-icon btn-ghost-success" title="Restaurar">
                                                    <i class="ti ti-refresh"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Modal de Crear/Editar -->
<div class="modal modal-blur fade" id="modalMetrica" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="formMetrica">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <?php echo $editando ? 'Editar Métrica' : 'Nueva Métrica'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="<?php echo $editando ? 'editar' : 'crear'; ?>">
                    <?php if ($editando): ?>
                        <input type="hidden" name="id" value="<?php echo $editando['id']; ?>">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label required">Departamento</label>
                                <select id="departamento-select" class="form-select" required>
                                    <option value="">Seleccionar departamento...</option>
                                    <?php foreach ($departamentos as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>"
                                            <?php echo ($editando && $editando['departamento_id'] == $dept['id']) || ($area_actual && $area_actual['departamento_id'] == $dept['id']) ? 'selected' : ''; ?>>
                                        <?php echo e($dept['nombre']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label required">Área</label>
                                <select name="area_id" id="area-select" class="form-select" required>
                                    <option value="">Seleccionar área...</option>
                                    <?php if ($editando): ?>
                                    <option value="<?php echo $editando['area_id']; ?>" selected>
                                        <?php echo e($editando['area_nombre']); ?>
                                    </option>
                                    <?php elseif ($area_actual): ?>
                                    <option value="<?php echo $area_actual['id']; ?>" selected>
                                        <?php echo e($area_actual['nombre']); ?>
                                    </option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Nombre de la Métrica</label>
                        <input type="text" name="nombre" class="form-control"
                               value="<?php echo e($editando['nombre'] ?? ''); ?>" required
                               placeholder="Ej: Tickets Resueltos, Ventas Mensuales, NPS">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="2"
                                  placeholder="Breve descripción de qué mide esta métrica"><?php echo e($editando['descripcion'] ?? ''); ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label required">Tipo de Valor</label>
                                <select name="tipo_valor" class="form-select" required>
                                    <option value="numero" <?php echo ($editando && $editando['tipo_valor'] == 'numero') || !$editando ? 'selected' : ''; ?>>
                                        Número entero
                                    </option>
                                    <option value="decimal" <?php echo ($editando && $editando['tipo_valor'] == 'decimal') ? 'selected' : ''; ?>>
                                        Decimal
                                    </option>
                                    <option value="porcentaje" <?php echo ($editando && $editando['tipo_valor'] == 'porcentaje') ? 'selected' : ''; ?>>
                                        Porcentaje
                                    </option>
                                    <option value="tiempo" <?php echo ($editando && $editando['tipo_valor'] == 'tiempo') ? 'selected' : ''; ?>>
                                        Tiempo
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Unidad de Medida</label>
                                <input type="text" name="unidad" class="form-control"
                                       value="<?php echo e($editando['unidad'] ?? ''); ?>"
                                       placeholder="tickets, $, hrs, etc.">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <?php
                            $input_name = 'icono';
                            $selected_icon = $editando['icono'] ?? 'chart-line';
                            $label = 'Icono';
                            include __DIR__ . '/../../views/components/icon-picker.php';
                            ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-check">
                            <input type="checkbox" name="es_calculada" id="checkbox-calculada" class="form-check-input"
                                   <?php echo ($editando && $editando['es_calculada']) ? 'checked' : ''; ?>>
                            <span class="form-check-label">
                                Esta métrica es calculada automáticamente
                                <span class="form-hint">Las métricas calculadas se derivan de otras métricas y NO se capturan manualmente</span>
                            </span>
                        </label>
                    </div>

                    <!-- Configuración de Métrica Calculada -->
                    <div id="config-calculada" style="display: <?php echo ($editando && $editando['es_calculada']) ? 'block' : 'none'; ?>; background: #f8f9fa; padding: 1rem; border-radius: 6px; margin-bottom: 1rem;">
                        <h4 class="mb-3">
                            <i class="ti ti-calculator me-2"></i>
                            Configuración de Cálculo
                        </h4>

                        <div class="mb-3">
                            <label class="form-label required">Operación</label>
                            <select name="operacion" id="select-operacion" class="form-select">
                                <option value="suma">Suma (+)</option>
                                <option value="resta">Resta (-)</option>
                                <option value="promedio">Promedio</option>
                            </select>
                            <div class="form-hint">Cómo se combinan las métricas componentes</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Métricas Componentes</label>
                            <div id="metricas-componentes-list" class="border rounded p-2" style="min-height: 100px; background: white;">
                                <p class="text-muted text-center py-3">
                                    <small>Primero selecciona un área para ver las métricas disponibles</small>
                                </p>
                            </div>
                            <div class="form-hint">
                                Selecciona las métricas que se usarán para calcular esta métrica.
                                <strong>Las métricas calculadas NO aparecerán en la captura de valores.</strong>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-check">
                            <input type="checkbox" name="tiene_meta" class="form-check-input"
                                   <?php echo ($editando && ($editando['tiene_meta'] ?? 0)) ? 'checked' : ''; ?>>
                            <span class="form-check-label">
                                <i class="ti ti-target me-1"></i>
                                Esta métrica tiene metas definidas
                                <span class="form-hint">Podrás configurar metas anuales o mensuales para esta métrica</span>
                            </span>
                        </label>
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
    const deptSelect = document.getElementById('departamento-select');
    const areaSelect = document.getElementById('area-select');
    const checkboxCalculada = document.getElementById('checkbox-calculada');
    const configCalculada = document.getElementById('config-calculada');
    const componentesList = document.getElementById('metricas-componentes-list');

    let metricasDisponibles = [];
    let componentesSeleccionados = [];
    let esAreaGlobal = false;

    // Función helper para verificar y cargar métricas
    function verificarYCargarMetricas() {
        const areaId = areaSelect.value;
        const esCalculada = checkboxCalculada.checked;

        console.log('Verificar y cargar:', { areaId, esCalculada });

        if (esCalculada && areaId) {
            cargarMetricasDisponibles(areaId);
        } else if (esCalculada && !areaId) {
            componentesList.innerHTML = '<p class="text-muted text-center py-3"><small>Primero selecciona un área para ver las métricas disponibles</small></p>';
        }
    }

    // Mostrar/ocultar configuración de métrica calculada
    checkboxCalculada?.addEventListener('change', function() {
        configCalculada.style.display = this.checked ? 'block' : 'none';
        verificarYCargarMetricas();
    });

    // Cargar áreas al seleccionar departamento
    deptSelect.addEventListener('change', function() {
        const deptId = this.value;
        areaSelect.innerHTML = '<option value="">Cargando...</option>';
        componentesList.innerHTML = '<p class="text-muted text-center py-3"><small>Primero selecciona un área</small></p>';
        metricasDisponibles = [];
        componentesSeleccionados = [];

        if (!deptId) {
            areaSelect.innerHTML = '<option value="">Seleccionar área...</option>';
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

    // Cargar métricas disponibles al seleccionar área
    areaSelect.addEventListener('change', function() {
        verificarYCargarMetricas();
    });

    // Función para cargar métricas disponibles
    function cargarMetricasDisponibles(areaId) {
        console.log('Cargando métricas para área:', areaId);
        componentesList.innerHTML = '<p class="text-muted text-center py-2"><small>Cargando...</small></p>';

        const url = '../api/get-metricas-by-area.php?area_id=' + areaId;
        console.log('URL:', url);

        fetch(url)
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Data recibida:', data);
                if (data.success) {
                    metricasDisponibles = data.data || [];
                    esAreaGlobal = data.is_global || false;
                    console.log('Métricas disponibles:', metricasDisponibles.length, 'Global:', esAreaGlobal);
                    renderizarComponentes();
                } else {
                    componentesList.innerHTML = '<p class="text-danger text-center py-2"><small>' + (data.error || 'Error al cargar') + '</small></p>';
                }
            })
            .catch(error => {
                console.error('Error completo:', error);
                componentesList.innerHTML = '<p class="text-danger text-center py-2"><small>Error al cargar métricas: ' + error.message + '</small></p>';
            });
    }

    // Renderizar lista de componentes
    function renderizarComponentes() {
        console.log('Renderizando componentes:', metricasDisponibles, 'Global:', esAreaGlobal);

        if (metricasDisponibles.length === 0) {
            componentesList.innerHTML = '<p class="text-muted text-center py-3"><small>No hay métricas disponibles</small></p>';
            return;
        }

        let html = '<div style="max-height: 350px; overflow-y: auto; padding: 0.5rem;">';

        if (esAreaGlobal) {
            // ÁREA GLOBAL: Agrupar por Departamento > Área
            const grupos = {};

            metricasDisponibles.forEach(metrica => {
                const dept = metrica.departamento_nombre || 'Sin Departamento';
                const area = metrica.area_nombre || 'Sin Área';

                if (!grupos[dept]) grupos[dept] = {};
                if (!grupos[dept][area]) grupos[dept][area] = [];

                grupos[dept][area].push(metrica);
            });

            // Renderizar grupos
            Object.keys(grupos).sort().forEach(dept => {
                html += `<div style="margin-bottom: 1rem;">`;
                html += `<div style="font-weight: 700; padding: 0.5rem; background: #e9ecef; border-radius: 4px; margin-bottom: 0.5rem;">
                            <i class="ti ti-building me-2"></i>${dept}
                         </div>`;

                Object.keys(grupos[dept]).sort().forEach(area => {
                    html += `<div style="margin-left: 1rem; margin-bottom: 0.75rem;">`;
                    html += `<div style="font-weight: 600; font-size: 0.9rem; padding: 0.25rem; color: #495057; margin-bottom: 0.25rem;">
                                <i class="ti ti-folder me-1"></i>${area}
                             </div>`;

                    grupos[dept][area].forEach(metrica => {
                        const isSelected = componentesSeleccionados.includes(parseInt(metrica.id));
                        const iconoSafe = (metrica.icono || 'chart-line').replace(/[^a-z0-9-]/gi, '');
                        const nombreSafe = (metrica.nombre || '').replace(/[<>]/g, '');
                        const unidadSafe = metrica.unidad ? (metrica.unidad).replace(/[<>]/g, '') : '';

                        html += `
                            <label style="display: flex; align-items: center; padding: 0.5rem 0.75rem; margin-bottom: 0.25rem; margin-left: 1.5rem; background: #f8f9fa; border-radius: 4px; cursor: pointer; user-select: none; font-size: 0.9rem;">
                                <input type="checkbox"
                                       style="width: 16px; height: 16px; margin-right: 0.5rem; cursor: pointer;"
                                       value="${metrica.id}"
                                       ${isSelected ? 'checked' : ''}
                                       onchange="toggleComponente(${metrica.id}, this.checked)">
                                <span style="flex: 1;">
                                    <i class="ti ti-${iconoSafe} me-1"></i>
                                    ${nombreSafe}
                                    ${unidadSafe ? `<span class="text-muted ms-1">(${unidadSafe})</span>` : ''}
                                </span>
                            </label>
                        `;
                    });

                    html += `</div>`;
                });

                html += `</div>`;
            });

        } else {
            // ÁREA NORMAL: Lista simple
            metricasDisponibles.forEach(metrica => {
                const isSelected = componentesSeleccionados.includes(parseInt(metrica.id));
                const iconoSafe = (metrica.icono || 'chart-line').replace(/[^a-z0-9-]/gi, '');
                const nombreSafe = (metrica.nombre || '').replace(/[<>]/g, '');
                const unidadSafe = metrica.unidad ? (metrica.unidad).replace(/[<>]/g, '') : '';

                html += `
                    <label style="display: flex; align-items: center; padding: 0.75rem; margin-bottom: 0.5rem; background: #f8f9fa; border-radius: 4px; cursor: pointer; user-select: none;">
                        <input type="checkbox"
                               style="width: 18px; height: 18px; margin-right: 0.75rem; cursor: pointer;"
                               value="${metrica.id}"
                               ${isSelected ? 'checked' : ''}
                               onchange="toggleComponente(${metrica.id}, this.checked)">
                        <span style="flex: 1;">
                            <i class="ti ti-${iconoSafe} me-1"></i>
                            <strong>${nombreSafe}</strong>
                            ${unidadSafe ? `<span class="text-muted ms-1">(${unidadSafe})</span>` : ''}
                        </span>
                    </label>
                `;
            });
        }

        html += '</div>';
        componentesList.innerHTML = html;
    }

    // Toggle componente
    window.toggleComponente = function(metricaId, checked) {
        if (checked) {
            if (!componentesSeleccionados.includes(metricaId)) {
                componentesSeleccionados.push(metricaId);
            }
        } else {
            componentesSeleccionados = componentesSeleccionados.filter(id => id !== metricaId);
        }
    };

    // Interceptar submit para agregar componentes
    document.getElementById('formMetrica').addEventListener('submit', function(e) {
        if (checkboxCalculada.checked && componentesSeleccionados.length > 0) {
            // Agregar campos hidden con los componentes seleccionados
            componentesSeleccionados.forEach(metricaId => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'componentes[]';
                input.value = metricaId;
                this.appendChild(input);
            });
        }
    });

    // Auto-abrir modal si hay parámetro editar
    <?php if ($editando): ?>
        <?php if ($editando['es_calculada'] && !empty($componentes_editando)): ?>
            // Cargar componentes existentes
            componentesSeleccionados = <?php echo json_encode(array_column($componentes_editando, 'metrica_componente_id')); ?>;

            // Cargar operación
            const operacion = '<?php echo $componentes_editando[0]['operacion'] ?? 'suma'; ?>';
            document.getElementById('select-operacion').value = operacion;

            // Cargar métricas disponibles y marcar seleccionadas
            if (areaSelect.value) {
                cargarMetricasDisponibles(areaSelect.value);
            }
        <?php endif; ?>

        setTimeout(function() {
            const modal = new bootstrap.Modal(document.getElementById('modalMetrica'));
            modal.show();
        }, 100);
    <?php endif; ?>

    // Limpiar formulario y URL al cerrar modal
    document.getElementById('modalMetrica').addEventListener('hidden.bs.modal', function() {
        <?php if ($editando): ?>
            window.history.replaceState({}, '', '<?php echo baseUrl('/public/admin/metricas.php'); ?><?php echo $area_filtro ? '?area=' . $area_filtro : ''; ?>');
        <?php else: ?>
            document.getElementById('formMetrica').reset();
            configCalculada.style.display = 'none';
            componentesSeleccionados = [];
        <?php endif; ?>
    });
});

function exportarMetricasActuales() {
    // Obtener todas las IDs de métricas actuales
    const metricasIds = <?php echo json_encode(array_column($metricas, 'id')); ?>;

    if (metricasIds.length === 0) {
        alert('No hay métricas para exportar');
        return;
    }

    const opciones = {
        periodos: 12
        <?php if ($area_filtro): ?>
        , area_id: <?php echo $area_filtro; ?>
        <?php endif; ?>
    };

    ExportModule.showExportModal(metricasIds);
}
</script>

<!-- Export Module -->
<script src="<?php echo baseUrl('/public/assets/js/export.js'); ?>"></script>

<!-- Tabler JS -->
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/js/tabler.min.js"></script>
<script src="<?php echo baseUrl('/public/assets/js/theme-toggle.js'); ?>"></script>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
