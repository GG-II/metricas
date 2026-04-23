<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Services\PermissionService;
use App\Services\MetricaCalculadaService;
use App\Models\Metrica;
use App\Models\Meta;
use App\Models\ValorMetrica;
use App\Models\Periodo;
use App\Models\Area;
use App\Models\Departamento;

AuthMiddleware::handle();

$user = getCurrentUser();
$metricaModel = new Metrica();
$metaModel = new Meta();
$valorModel = new ValorMetrica();
$periodoModel = new Periodo();
$areaModel = new Area();
$deptModel = new Departamento();
$calculadaService = new MetricaCalculadaService();

// Procesar formulario de captura
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'guardar_valores') {
    // ✅ PROTECCIÓN CSRF
    csrf_validate();

    $periodo_id = (int)$_POST['periodo_id'];
    $area_id = (int)$_POST['area_id'];

    // Verificar permiso
    if (!PermissionService::canEditArea($user, $area_id)) {
        setFlash('error', 'No tienes permiso para editar esta área');
        redirect('/public/captura-valores.php');
        exit;
    }

    $guardados = 0;
    $errores = 0;
    $metricas_guardadas = []; // Para recalcular después

    // Procesar cada métrica
    foreach ($_POST['metricas'] ?? [] as $metrica_id => $datos) {
        $valor_texto = trim($datos['valor'] ?? '');

        // Si el valor está vacío, continuar
        if ($valor_texto === '') {
            continue;
        }

        // Obtener información de la métrica
        $metrica = $metricaModel->find($metrica_id);
        if (!$metrica) continue;

        // Preparar datos según el tipo
        $data = [
            'metrica_id' => $metrica_id,
            'periodo_id' => $periodo_id,
            'nota' => sanitize($datos['nota'] ?? '')
        ];

        // Asignar valor según tipo
        if (in_array($metrica['tipo_valor'], ['numero', 'tiempo'])) {
            $data['valor_numero'] = (int)$valor_texto;
            $data['valor_decimal'] = null;
        } else {
            $data['valor_numero'] = null;
            $data['valor_decimal'] = (float)$valor_texto;
        }

        // Verificar si ya existe un valor para este período y métrica
        $existente = $valorModel->findByMetricaYPeriodo($metrica_id, $periodo_id);

        if ($existente) {
            // Actualizar
            $data['usuario_modificacion_id'] = $user['id'];
            if ($valorModel->update($existente['id'], $data)) {
                $guardados++;
                $metricas_guardadas[] = $metrica_id;
            } else {
                $errores++;
            }
        } else {
            // Crear nuevo
            $data['usuario_registro_id'] = $user['id'];
            if ($valorModel->create($data)) {
                $guardados++;
                $metricas_guardadas[] = $metrica_id;
            } else {
                $errores++;
            }
        }
    }

    // 🎯 Recalcular métricas calculadas que dependen de las guardadas
    $recalculadas = 0;
    foreach ($metricas_guardadas as $metrica_id) {
        $recalculadas += $calculadaService->recalcularDependientes($metrica_id, $periodo_id, $user['id']);
    }

    if ($guardados > 0) {
        $mensaje = "✓ $guardados valores guardados exitosamente";
        if ($recalculadas > 0) {
            $mensaje .= " ($recalculadas métricas calculadas actualizadas)";
        }
        if ($errores > 0) {
            $mensaje .= " ($errores errores)";
        }
        setFlash('success', $mensaje);
    } elseif ($errores > 0) {
        setFlash('error', "✗ Error al guardar los valores");
    } else {
        setFlash('warning', 'ℹ No se ingresaron valores para guardar');
    }

    redirect('/public/captura-valores.php?periodo=' . $periodo_id . '&area=' . $area_id);
    exit;
}

// Obtener departamentos y áreas permitidas
$departamentos = PermissionService::getDepartamentosPermitidos($user);

// Obtener períodos
$periodos = $periodoModel->getAll();

// Período actual o seleccionado
$periodo_param = $_GET['periodo'] ?? null;
if ($periodo_param) {
    $periodo_id = (int)$periodo_param;
    $periodo = $periodoModel->find($periodo_id);
} else {
    // Período actual
    $mes_actual = (int)date('n');
    $anio_actual = (int)date('Y');
    $periodo = $periodoModel->findByEjercicioAndPeriodo($anio_actual, $mes_actual);
    if (!$periodo && !empty($periodos)) {
        $periodo = $periodos[count($periodos) - 1];
    }
}

// Área seleccionada
$area_id = $_GET['area'] ?? null;
$area = null;
$metricas = [];
$valores_existentes = [];

if ($area_id && $periodo) {
    $area = $areaModel->findWithDepartamento($area_id);

    // Verificar permiso
    if (!PermissionService::canViewArea($user, $area_id)) {
        die('No tienes permiso para ver esta área.');
    }

    // Obtener métricas del área (SOLO NO CALCULADAS - las calculadas se actualizan automáticamente)
    $metricas = $metricaModel->getByArea($area_id, true);

    // Filtrar métricas calculadas
    $metricas = array_filter($metricas, function($m) {
        return !$m['es_calculada']; // Excluir métricas calculadas
    });

    // Obtener valores existentes y metas para este período
    foreach ($metricas as &$metrica) {
        // Obtener valor actual
        $valor = $valorModel->findByMetricaYPeriodo($metrica['id'], $periodo['id']);
        if ($valor) {
            $metrica['valor_actual'] = $valor['valor_numero'] ?? $valor['valor_decimal'];
            $metrica['nota_actual'] = $valor['nota'];
        }

        // Obtener meta aplicable si la métrica tiene metas
        if ($metrica['tiene_meta']) {
            $meta = $metaModel->getMetaAplicable($metrica['id'], $periodo['id']);
            if ($meta) {
                $metrica['meta'] = $meta;

                // Calcular cumplimiento si hay valor actual
                if (isset($metrica['valor_actual'])) {
                    $metrica['cumplimiento'] = $metaModel->calcularCumplimiento(
                        $metrica['valor_actual'],
                        $meta['valor_objetivo'],
                        $meta['tipo_comparacion']
                    );
                }
            }
        }
    }
    unset($metrica); // IMPORTANTE: Eliminar referencia después del foreach
}

$puede_editar = $area ? PermissionService::canEditArea($user, $area_id) : false;

$pageTitle = 'Captura de Valores';
require_once __DIR__ . '/../views/layouts/header.php';
?>

<div class="page-wrapper">
    <div class="page-body">
        <div class="container-xl">

            <!-- Header -->
            <div class="page-header mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h2 class="page-title">
                            <i class="ti ti-edit me-2"></i>
                            Captura de Valores
                        </h2>
                        <div class="text-muted mt-1">Registra los valores de las métricas por período</div>
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

            <!-- Selectores -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Período</label>
                            <select name="periodo" class="form-select" onchange="this.form.submit()">
                                <option value="">Seleccionar período...</option>
                                <?php foreach (array_reverse($periodos) as $p): ?>
                                <option value="<?php echo $p['id']; ?>"
                                        <?php echo ($periodo && $p['id'] == $periodo['id']) ? 'selected' : ''; ?>>
                                    <?php echo e($p['nombre']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Departamento</label>
                            <select id="dept-select" class="form-select">
                                <option value="">Seleccionar departamento...</option>
                                <?php foreach ($departamentos as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>"
                                        <?php echo ($area && $area['departamento_id'] == $dept['id']) ? 'selected' : ''; ?>>
                                    <?php echo e($dept['nombre']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Área</label>
                            <select name="area" id="area-select" class="form-select" onchange="this.form.submit()">
                                <option value="">Seleccionar área...</option>
                                <?php if ($area): ?>
                                <option value="<?php echo $area['id']; ?>" selected>
                                    <?php echo e($area['nombre']); ?>
                                </option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Formulario de Captura -->
            <?php if (!$periodo): ?>
                <div class="empty text-center py-5">
                    <div class="empty-icon mb-3">
                        <i class="ti ti-calendar" style="font-size: 4rem; color: #94a3b8;"></i>
                    </div>
                    <h2>Selecciona un período</h2>
                    <p class="text-muted">Elige el período para el cual deseas capturar valores</p>
                </div>
            <?php elseif (!$area): ?>
                <div class="empty text-center py-5">
                    <div class="empty-icon mb-3">
                        <i class="ti ti-layout-grid" style="font-size: 4rem; color: #94a3b8;"></i>
                    </div>
                    <h2>Selecciona un área</h2>
                    <p class="text-muted">Elige el área para la cual deseas capturar valores</p>
                </div>
            <?php elseif (empty($metricas)): ?>
                <div class="empty text-center py-5">
                    <div class="empty-icon mb-3">
                        <i class="ti ti-chart-line" style="font-size: 4rem; color: #94a3b8;"></i>
                    </div>
                    <h2>No hay métricas en esta área</h2>
                    <p class="text-muted">Primero debes crear métricas para poder capturar valores</p>
                    <?php if (in_array($user['rol'], ['super_admin', 'dept_admin'])): ?>
                        <a href="<?php echo baseUrl('/public/admin/metricas.php?area=' . $area_id); ?>" class="btn btn-primary mt-3">
                            <i class="ti ti-plus me-1"></i>
                            Crear Métricas
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="action" value="guardar_valores">
                    <input type="hidden" name="periodo_id" value="<?php echo $periodo['id']; ?>">
                    <input type="hidden" name="area_id" value="<?php echo $area_id; ?>">

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-<?php echo e($area['icono']); ?> me-2"></i>
                                <?php echo e($area['nombre']); ?> - <?php echo e($periodo['nombre']); ?>
                            </h3>
                            <?php if ($puede_editar): ?>
                            <div class="card-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-check me-1"></i>
                                    Guardar Valores
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-vcenter card-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 30%">Métrica</th>
                                            <th style="width: 15%">Tipo</th>
                                            <th style="width: 20%">Valor</th>
                                            <th>Notas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($metricas as $metrica): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="avatar avatar-sm me-2" style="background-color: <?php echo e($area['color']); ?>">
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
                                                <?php
                                                $tipos = [
                                                    'numero' => 'Número',
                                                    'porcentaje' => 'Porcentaje',
                                                    'tiempo' => 'Tiempo',
                                                    'decimal' => 'Decimal'
                                                ];
                                                ?>
                                                <span class="badge bg-blue-lt">
                                                    <?php echo $tipos[$metrica['tipo_valor']] ?? $metrica['tipo_valor']; ?>
                                                </span>
                                                <?php if ($metrica['unidad']): ?>
                                                <div class="small text-muted mt-1"><?php echo e($metrica['unidad']); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <input type="<?php echo in_array($metrica['tipo_valor'], ['decimal', 'porcentaje']) ? 'number' : 'text'; ?>"
                                                       name="metricas[<?php echo $metrica['id']; ?>][valor]"
                                                       class="form-control <?php echo isset($metrica['cumplimiento']) ? ($metrica['cumplimiento'] >= 100 ? 'border-success' : 'border-warning') : ''; ?>"
                                                       value="<?php echo e($metrica['valor_actual'] ?? ''); ?>"
                                                       <?php echo in_array($metrica['tipo_valor'], ['decimal', 'porcentaje']) ? 'step="0.01"' : ''; ?>
                                                       <?php echo !$puede_editar ? 'readonly' : ''; ?>
                                                       placeholder="<?php echo $metrica['es_calculada'] ? 'Calculada' : 'Ingrese valor'; ?>">

                                                <?php if (isset($metrica['meta'])): ?>
                                                    <div class="small mt-1">
                                                        <span class="text-muted">
                                                            <i class="ti ti-target me-1"></i>
                                                            Meta: <strong><?php echo number_format($metrica['meta']['valor_objetivo'], 1); ?></strong>
                                                            <?php echo e($metrica['unidad']); ?>
                                                        </span>

                                                        <?php if (isset($metrica['cumplimiento'])): ?>
                                                            <?php if ($metrica['cumplimiento'] >= 100): ?>
                                                                <span class="badge bg-success-lt ms-2">
                                                                    <i class="ti ti-check me-1"></i>
                                                                    Cumplido (<?php echo number_format($metrica['cumplimiento'], 0); ?>%)
                                                                </span>
                                                            <?php elseif ($metrica['cumplimiento'] >= 80): ?>
                                                                <span class="badge bg-warning-lt ms-2">
                                                                    <i class="ti ti-alert-triangle me-1"></i>
                                                                    <?php echo number_format($metrica['cumplimiento'], 0); ?>%
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge bg-danger-lt ms-2">
                                                                    <i class="ti ti-x me-1"></i>
                                                                    <?php echo number_format($metrica['cumplimiento'], 0); ?>%
                                                                </span>
                                                            <?php endif; ?>
                                                        <?php endif; ?>

                                                        <?php if ($metrica['meta']['origen'] === 'anual_mensualizado'): ?>
                                                            <div class="text-muted" style="font-size: 0.7rem;">
                                                                <i class="ti ti-calendar me-1"></i>
                                                                Meta anual: <?php echo number_format($metrica['meta']['valor_anual'], 0); ?> / 12 meses
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <input type="text"
                                                       name="metricas[<?php echo $metrica['id']; ?>][nota]"
                                                       class="form-control"
                                                       value="<?php echo e($metrica['nota_actual'] ?? ''); ?>"
                                                       <?php echo !$puede_editar ? 'readonly' : ''; ?>
                                                       placeholder="Notas opcionales">
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php if ($puede_editar): ?>
                        <div class="card-footer text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-check me-1"></i>
                                Guardar Valores
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </form>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deptSelect = document.getElementById('dept-select');
    const areaSelect = document.getElementById('area-select');
    const periodoInput = document.querySelector('select[name="periodo"]');

    // Cargar áreas al seleccionar departamento
    deptSelect.addEventListener('change', function() {
        const deptId = this.value;
        areaSelect.innerHTML = '<option value="">Cargando...</option>';

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
            });
    });

    // Trigger para cargar áreas si hay departamento seleccionado
    if (deptSelect.value) {
        deptSelect.dispatchEvent(new Event('change'));
    }
});
</script>

<!-- Tabler JS -->
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/js/tabler.min.js"></script>
<script src="<?php echo baseUrl('/public/assets/js/theme-toggle.js'); ?>"></script>
<script src="<?php echo baseUrl('/public/assets/js/toast-notifications.js'); ?>"></script>

<script>
// Mostrar toast al guardar valores
document.querySelector('form[method="POST"]')?.addEventListener('submit', function(e) {
    const valores = Array.from(document.querySelectorAll('input[name^="metricas"][name$="[valor]"]'))
        .filter(input => input.value.trim() !== '');

    if (valores.length === 0) {
        e.preventDefault();
        showToast('Por favor ingresa al menos un valor', 'warning');
        return false;
    }
});
</script>

<?php require_once __DIR__ . '/../views/layouts/footer.php'; ?>
