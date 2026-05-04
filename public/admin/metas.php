<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Services\PermissionService;
use App\Services\MetaValidatorService;
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
$metaValidator = new MetaValidatorService();

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
                    redirect('/admin/metas.php');
                    break;
                }

                // Verificar permiso
                $area = $areaModel->findWithDepartamento($metrica['area_id']);
                if (!PermissionService::canEditArea($user, $metrica['area_id'])) {
                    setFlash('error', 'No tienes permiso para editar metas de esta área');
                    redirect('/admin/metas.php');
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
                        redirect('/admin/metas.php?metrica=' . $metrica_id);
                        break;
                    }

                    // Validar que la meta anual sea >= suma de metas mensuales existentes
                    $suma_mensual = $metaValidator->getSumaMensual($metrica_id, $data['ejercicio']);
                    if ($suma_mensual > 0 && $data['valor_objetivo'] < $suma_mensual) {
                        setFlash('error', sprintf(
                            'La meta anual ($%s) debe ser mayor o igual a la suma de metas mensuales existentes ($%s)',
                            number_format($data['valor_objetivo'], 2),
                            number_format($suma_mensual, 2)
                        ));
                        redirect('/admin/metas.php?metrica=' . $metrica_id);
                        break;
                    }
                } else {
                    $data['periodo_id'] = (int)$_POST['periodo_id'];
                    $periodo = $periodoModel->find($data['periodo_id']);
                    $data['ejercicio'] = $periodo['ejercicio'];

                    // Verificar que no exista ya
                    if ($metaModel->exists($metrica_id, 'mensual', null, $data['periodo_id'])) {
                        setFlash('error', 'Ya existe una meta para este período');
                        redirect('/admin/metas.php?metrica=' . $metrica_id);
                        break;
                    }

                    // Validar que no exceda la meta anual
                    $validacion = $metaValidator->validarMetaMensual(
                        $metrica_id,
                        $data['ejercicio'],
                        $data['valor_objetivo']
                    );

                    if (!$validacion['valido']) {
                        setFlash('error', $validacion['mensaje']);
                        redirect('/admin/metas.php?metrica=' . $metrica_id);
                        break;
                    }
                }

                if ($metaModel->create($data)) {
                    setFlash('success', '✓ Meta creada exitosamente');
                } else {
                    setFlash('error', 'Error al crear la meta');
                }
                redirect('/admin/metas.php?metrica=' . $metrica_id);
                break;

            case 'editar':
                $id = (int)$_POST['id'];
                $meta = $metaModel->find($id);

                if ($meta) {
                    // Verificar permiso
                    $metrica = $metricaModel->find($meta['metrica_id']);
                    if (!PermissionService::canEditArea($user, $metrica['area_id'])) {
                        setFlash('error', 'No tienes permiso');
                        redirect('/admin/metas.php');
                        break;
                    }

                    $nuevo_valor = (float)$_POST['valor_objetivo'];

                    // Validar según tipo de meta
                    if ($meta['tipo_meta'] === 'mensual') {
                        // Validar que no exceda la meta anual (excluyendo el período actual)
                        $validacion = $metaValidator->validarMetaMensual(
                            $meta['metrica_id'],
                            $meta['ejercicio'],
                            $nuevo_valor,
                            $meta['periodo_id']
                        );

                        if (!$validacion['valido']) {
                            setFlash('error', $validacion['mensaje']);
                            redirect('/admin/metas.php?metrica=' . $meta['metrica_id']);
                            break;
                        }
                    } elseif ($meta['tipo_meta'] === 'anual') {
                        // Validar que sea >= suma de metas mensuales
                        $suma_mensual = $metaValidator->getSumaMensual($meta['metrica_id'], $meta['ejercicio']);
                        if ($suma_mensual > 0 && $nuevo_valor < $suma_mensual) {
                            setFlash('error', sprintf(
                                'La meta anual ($%s) debe ser mayor o igual a la suma de metas mensuales existentes ($%s)',
                                number_format($nuevo_valor, 2),
                                number_format($suma_mensual, 2)
                            ));
                            redirect('/admin/metas.php?metrica=' . $meta['metrica_id']);
                            break;
                        }
                    }

                    $data = [
                        'valor_objetivo' => $nuevo_valor,
                        'tipo_comparacion' => sanitize($_POST['tipo_comparacion'])
                    ];

                    if ($metaModel->update($id, $data)) {
                        setFlash('success', '✓ Meta actualizada');
                    } else {
                        setFlash('error', 'Error al actualizar');
                    }
                }
                redirect('/admin/metas.php?metrica=' . $meta['metrica_id']);
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
                redirect('/admin/metas.php');
                break;
        }
    }
}

// Obtener métricas con metas según permisos
$metrica_seleccionada_id = $_GET['metrica'] ?? null;

if ($user['rol'] === 'super_admin') {
    // Super admin ve todas las métricas con metas
    $metricas_con_metas = $metricaModel->query("
        SELECT m.*, a.nombre as area_nombre, a.color as area_color, d.nombre as departamento_nombre
        FROM metricas m
        JOIN areas a ON m.area_id = a.id
        JOIN departamentos d ON a.departamento_id = d.id
        WHERE m.tiene_meta = 1 AND m.activo = 1
        ORDER BY d.nombre, a.nombre, m.nombre
    ")->fetchAll();
} elseif ($user['rol'] === 'area_admin') {
    // Area admin solo ve métricas de su área asignada
    $metricas_con_metas = $metricaModel->query("
        SELECT m.*, a.nombre as area_nombre, a.color as area_color, d.nombre as departamento_nombre
        FROM metricas m
        JOIN areas a ON m.area_id = a.id
        JOIN departamentos d ON a.departamento_id = d.id
        WHERE m.tiene_meta = 1
        AND m.activo = 1
        AND a.id = ?
        ORDER BY m.nombre
    ", [$user['area_id']])->fetchAll();
} else {
    // Dept admin ve métricas de su departamento
    $metricas_con_metas = $metricaModel->query("
        SELECT m.*, a.nombre as area_nombre, a.color as area_color, d.nombre as departamento_nombre
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
$periodos_usados = [];
$ejercicios_usados = [];

if ($metrica_seleccionada_id) {
    $metrica_seleccionada = $metricaModel->findWithRelations($metrica_seleccionada_id);
    if ($metrica_seleccionada && PermissionService::canViewArea($user, $metrica_seleccionada['area_id'])) {
        $metas = $metaModel->getByMetrica($metrica_seleccionada_id);

        // Obtener períodos y ejercicios ya usados
        foreach ($metas as $meta) {
            if ($meta['tipo_meta'] === 'mensual' && $meta['periodo_id']) {
                $periodos_usados[] = $meta['periodo_id'];
            } elseif ($meta['tipo_meta'] === 'anual') {
                $ejercicios_usados[] = $meta['ejercicio'];
            }
        }
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
                                <li class="breadcrumb-item"><a href="<?php echo baseUrl('/admin/index.php'); ?>">Administración</a></li>
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
                                    <a href="<?php echo baseUrl('/admin/metricas.php'); ?>" class="btn btn-sm btn-primary mt-2">
                                        Ir a Métricas
                                    </a>
                                </div>
                            <?php else: ?>
                                <?php foreach ($metricas_con_metas as $m): ?>
                                <a href="?metrica=<?php echo $m['id']; ?>"
                                   class="list-group-item list-group-item-action <?php echo $metrica_seleccionada_id == $m['id'] ? 'active' : ''; ?>">
                                    <div class="d-flex align-items-center">
                                        <span class="avatar avatar-sm me-2" style="background-color: <?php echo e($m['area_color'] ?? '#6c757d'); ?>;">
                                            <i class="ti ti-<?php echo e($m['icono']); ?>" style="color: white;"></i>
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
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="mb-2">
                                            <i class="ti ti-<?php echo e($metrica_seleccionada['icono']); ?> me-2"></i>
                                            <?php echo e($metrica_seleccionada['nombre']); ?>
                                        </h3>
                                        <div class="text-muted mb-2">
                                            <?php echo e($metrica_seleccionada['departamento_nombre']); ?> › <?php echo e($metrica_seleccionada['area_nombre']); ?>
                                        </div>
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
                            <?php
                            // Agrupar metas por año
                            $metas_por_año = [];
                            foreach ($metas as $meta) {
                                $ejercicio = $meta['ejercicio'];
                                if (!isset($metas_por_año[$ejercicio])) {
                                    $metas_por_año[$ejercicio] = [
                                        'anual' => null,
                                        'mensuales' => []
                                    ];
                                }

                                if ($meta['tipo_meta'] === 'anual') {
                                    $metas_por_año[$ejercicio]['anual'] = $meta;
                                } else {
                                    $metas_por_año[$ejercicio]['mensuales'][] = $meta;
                                }
                            }

                            // Ordenar por año descendente
                            krsort($metas_por_año);

                            $comparaciones = [
                                'mayor_igual' => '≥ Mayor o igual',
                                'menor_igual' => '≤ Menor o igual',
                                'igual' => '= Igual',
                                'rango' => '↔ Rango'
                            ];
                            ?>

                            <?php foreach ($metas_por_año as $ejercicio => $metas_año): ?>
                            <div class="card mb-4 metas-year-card">
                                <div class="card-body">
                                    <!-- Año Header -->
                                    <div class="year-header">
                                        <h3>
                                            <i class="ti ti-calendar me-2"></i>
                                            Año <?php echo $ejercicio; ?>
                                        </h3>
                                    </div>

                                    <!-- Meta Anual -->
                                    <?php if ($metas_año['anual']): ?>
                                        <?php $meta_anual = $metas_año['anual']; ?>
                                        <div class="meta-anual-card mb-4">
                                            <div class="row align-items-center">
                                                <div class="col-lg-8">
                                                    <div class="d-flex align-items-start mb-2">
                                                        <span class="badge bg-blue-lt me-2">
                                                            <i class="ti ti-calendar me-1"></i>
                                                            Meta Anual
                                                        </span>
                                                        <?php if (!$meta_anual['activo']): ?>
                                                            <span class="badge bg-secondary">Inactiva</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="h1 mb-2">
                                                        <?php echo number_format($meta_anual['valor_objetivo'], 0); ?>
                                                        <span class="text-muted fs-4"><?php echo e($metrica_seleccionada['unidad']); ?></span>
                                                    </div>
                                                    <div class="text-muted">
                                                        <i class="ti ti-calculator me-1"></i>
                                                        Promedio mensual: <?php echo number_format($meta_anual['valor_objetivo'] / 12, 2); ?> <?php echo e($metrica_seleccionada['unidad']); ?>
                                                    </div>
                                                    <div class="small text-muted mt-2">
                                                        <strong>Comparación:</strong> <?php echo $comparaciones[$meta_anual['tipo_comparacion']] ?? $meta_anual['tipo_comparacion']; ?>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                                                    <div class="btn-list">
                                                        <a href="?metrica=<?php echo $metrica_seleccionada_id; ?>&editar=<?php echo $meta_anual['id']; ?>"
                                                           class="btn btn-sm btn-primary">
                                                            <i class="ti ti-edit"></i> Editar
                                                        </a>
                                                        <form method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta meta anual?');">
                                                            <input type="hidden" name="action" value="eliminar">
                                                            <input type="hidden" name="id" value="<?php echo $meta_anual['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-ghost-danger">
                                                                <i class="ti ti-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Metas Mensuales -->
                                    <?php if (!empty($metas_año['mensuales'])): ?>
                                        <div class="metas-mensuales-section">
                                            <h4 class="mb-3">
                                                <i class="ti ti-calendar-month me-2"></i>
                                                Metas Mensuales
                                                <span class="badge bg-green-lt ms-2"><?php echo count($metas_año['mensuales']); ?> meses</span>
                                            </h4>

                                            <div class="table-responsive">
                                                <table class="table table-metas-mensuales">
                                                    <thead>
                                                        <tr>
                                                            <th>Mes</th>
                                                            <th class="text-end">Meta</th>
                                                            <th>Comparación</th>
                                                            <th class="text-end">Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($metas_año['mensuales'] as $meta_mensual): ?>
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <i class="ti ti-calendar-month me-2 text-muted"></i>
                                                                    <span class="fw-medium"><?php echo e($meta_mensual['periodo_nombre']); ?></span>
                                                                </div>
                                                            </td>
                                                            <td class="text-end">
                                                                <strong><?php echo number_format($meta_mensual['valor_objetivo'], 0); ?></strong>
                                                                <span class="text-muted"><?php echo e($metrica_seleccionada['unidad']); ?></span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-azure-lt">
                                                                    <?php echo $comparaciones[$meta_mensual['tipo_comparacion']] ?? $meta_mensual['tipo_comparacion']; ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-end">
                                                                <div class="btn-list justify-content-end">
                                                                    <a href="?metrica=<?php echo $metrica_seleccionada_id; ?>&editar=<?php echo $meta_mensual['id']; ?>"
                                                                       class="btn btn-sm btn-icon btn-primary" title="Editar">
                                                                        <i class="ti ti-edit"></i>
                                                                    </a>
                                                                    <form method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta meta?');">
                                                                        <input type="hidden" name="action" value="eliminar">
                                                                        <input type="hidden" name="id" value="<?php echo $meta_mensual['id']; ?>">
                                                                        <button type="submit" class="btn btn-sm btn-icon btn-ghost-danger" title="Eliminar">
                                                                            <i class="ti ti-trash"></i>
                                                                        </button>
                                                                    </form>
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
                            <?php endforeach; ?>
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
                        <input type="number" name="ejercicio" id="input_ejercicio" class="form-control"
                               value="<?php echo date('Y'); ?>" min="2020" max="2099">
                        <div id="ejercicio_warning" class="form-hint text-danger d-none">
                            Ya existe una meta anual para este año
                        </div>
                        <?php if (!empty($ejercicios_usados)): ?>
                        <div class="form-hint text-muted">
                            Años con meta anual: <?php echo implode(', ', $ejercicios_usados); ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3" id="div_periodo" style="display: none;">
                        <label class="form-label required">Período</label>
                        <select name="periodo_id" class="form-select">
                            <option value="">Seleccionar período...</option>
                            <?php foreach ($periodos as $p): ?>
                                <?php if (!in_array($p['id'], $periodos_usados)): ?>
                                <option value="<?php echo $p['id']; ?>">
                                    <?php echo e($p['nombre']); ?>
                                </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($periodos_usados)): ?>
                        <div class="form-hint text-muted">
                            Períodos con meta ya configurada no se muestran
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Info dinámica de metas -->
                    <div id="metaInfo" class="alert alert-info d-none mb-3">
                        <div class="d-flex align-items-center">
                            <i class="ti ti-info-circle me-2"></i>
                            <div id="metaInfoContent"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Valor Objetivo</label>
                        <input type="number" name="valor_objetivo" id="valor_objetivo" class="form-control"
                               step="0.01" value="<?php echo $editando['valor_objetivo'] ?? ''; ?>" required>
                        <div class="form-hint" id="valorHint">
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
    const inputEjercicio = divEjercicio?.querySelector('input');
    const selectPeriodo = divPeriodo?.querySelector('select');
    const metaInfo = document.getElementById('metaInfo');
    const metaInfoContent = document.getElementById('metaInfoContent');
    const valorObjetivo = document.getElementById('valor_objetivo');
    const valorHint = document.getElementById('valorHint');

    const metricaId = <?php echo $metrica_seleccionada_id ?? 'null'; ?>;
    const ejerciciosUsados = <?php echo json_encode($ejercicios_usados); ?>;
    const inputEjercicioEl = document.getElementById('input_ejercicio');
    const ejercicioWarning = document.getElementById('ejercicio_warning');
    const formModal = document.querySelector('#modalMeta form');

    // Función para obtener info de metas
    async function obtenerInfoMetas() {
        if (!metricaId) return;

        const tipoMetaVal = tipoMeta?.value;
        let ejercicio = null;

        if (tipoMetaVal === 'anual') {
            ejercicio = inputEjercicio?.value;
        } else if (tipoMetaVal === 'mensual') {
            const periodoId = selectPeriodo?.value;
            if (!periodoId) {
                metaInfo.classList.add('d-none');
                return;
            }

            // Obtener ejercicio del período seleccionado
            const periodoOption = selectPeriodo.options[selectPeriodo.selectedIndex];
            const periodoText = periodoOption.text;
            const match = periodoText.match(/\d{4}/);
            ejercicio = match ? match[0] : new Date().getFullYear();
        } else {
            metaInfo.classList.add('d-none');
            return;
        }

        if (!ejercicio) return;

        try {
            const response = await fetch(`../api/metas-info.php?metrica_id=${metricaId}&ejercicio=${ejercicio}&tipo_meta=${tipoMetaVal}`);
            const result = await response.json();

            if (result.success) {
                mostrarInfo(result.data, tipoMetaVal);
            }
        } catch (error) {
            console.error('Error al obtener info de metas:', error);
        }
    }

    // Función para mostrar la información
    function mostrarInfo(data, tipoMetaVal) {
        // Resetear clases
        metaInfo.classList.remove('alert-warning', 'alert-info', 'alert-secondary');
        metaInfo.classList.add('alert-info');

        if (tipoMetaVal === 'mensual') {
            if (data.tiene_meta_anual) {
                // CON meta anual
                const mesesRestantes = data.meses_restantes || (12 - data.meses_configurados);

                let html = '<strong>📊 Información de Meta:</strong><br>';
                html += `Meta anual: <strong>${formatNumber(data.meta_anual)}</strong> <?php echo e($metrica_seleccionada['unidad'] ?? ''); ?><br>`;
                html += `✅ Ya asignado en ${data.meses_configurados} meses: <strong>${formatNumber(data.suma_mensual)}</strong> (${data.porcentaje_asignado}%)<br>`;
                html += `📅 Meses sin asignar: <strong>${mesesRestantes}</strong><br>`;
                html += `💡 Valor sugerido: <strong class="text-primary">${formatNumber(data.valor_sugerido_mensual)}</strong><br>`;
                html += `⚠️ Máximo permitido: <strong class="${data.restante_disponible > 0 ? 'text-success' : 'text-danger'}">${formatNumber(data.restante_disponible)}</strong>`;

                metaInfoContent.innerHTML = html;
                metaInfo.classList.remove('d-none');

                // Actualizar hint
                valorHint.innerHTML = `Sugerido: <strong class="text-primary">${formatNumber(data.valor_sugerido_mensual)}</strong> | Máximo: <strong class="text-danger">${formatNumber(data.restante_disponible)}</strong> <?php echo e($metrica_seleccionada['unidad'] ?? ''); ?>`;

                // Sugerir valor
                if (!valorObjetivo.value || valorObjetivo.value == 0) {
                    valorObjetivo.value = data.valor_sugerido_mensual;
                }

                // Validación de máximo
                valorObjetivo.setAttribute('max', data.restante_disponible);

            } else {
                // SIN meta anual
                metaInfo.classList.remove('alert-info');
                metaInfo.classList.add('alert-secondary');

                let html = '<strong>ℹ️ Sin meta anual configurada</strong><br>';
                if (data.meses_configurados > 0) {
                    html += `Ya asignado en ${data.meses_configurados} meses: <strong>${formatNumber(data.suma_mensual)}</strong> <?php echo e($metrica_seleccionada['unidad'] ?? ''); ?><br>`;
                    html += `Puedes ingresar cualquier valor para este mes.`;
                } else {
                    html += `Esta es la primera meta mensual. Puedes ingresar cualquier valor.<br>`;
                    html += `<small class="text-muted">💡 Tip: Considera crear una meta anual primero para mejor control.</small>`;
                }

                metaInfoContent.innerHTML = html;
                metaInfo.classList.remove('d-none');

                // Sin restricciones
                valorObjetivo.removeAttribute('max');
                valorHint.innerHTML = 'Ingresa el valor objetivo para este mes';
            }

        } else if (tipoMetaVal === 'anual') {
            if (data.suma_mensual > 0) {
                // Ya hay metas mensuales
                let html = '<strong>⚠️ Advertencia:</strong><br>';
                html += `Ya existen metas mensuales que suman <strong>${formatNumber(data.suma_mensual)}</strong> <?php echo e($metrica_seleccionada['unidad'] ?? ''); ?>.<br>`;
                html += `La meta anual debe ser mayor o igual a este valor.`;

                metaInfoContent.innerHTML = html;
                metaInfo.classList.remove('d-none', 'alert-info');
                metaInfo.classList.add('alert-warning');

                // Establecer mínimo
                valorObjetivo.setAttribute('min', data.suma_mensual);
            } else {
                // Primera meta anual
                metaInfo.classList.remove('alert-info');
                metaInfo.classList.add('alert-secondary');

                let html = '<strong>ℹ️ Nueva meta anual</strong><br>';
                html += `Define el objetivo para todo el año. Se dividirá automáticamente entre 12 meses.<br>`;
                html += `<small class="text-muted">💡 Ejemplo: Si ingresas 12,000, cada mes tendrá meta de 1,000</small>`;

                metaInfoContent.innerHTML = html;
                metaInfo.classList.remove('d-none');
            }

        } else {
            metaInfo.classList.add('d-none');
        }
    }

    // Helpers
    function formatNumber(num) {
        return new Intl.NumberFormat('es-MX', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        }).format(num);
    }

    if (tipoMeta) {
        tipoMeta.addEventListener('change', function() {
            // Resetear validaciones
            valorObjetivo?.removeAttribute('max');
            valorObjetivo?.removeAttribute('min');
            valorObjetivo.value = '';

            if (this.value === 'anual') {
                divEjercicio.style.display = 'block';
                divPeriodo.style.display = 'none';
                inputEjercicio?.setAttribute('required', 'required');
                selectPeriodo?.removeAttribute('required');
                selectPeriodo.value = '';
                metaInfo?.classList.add('d-none'); // Ocultar hasta obtener info
                // Esperar un momento para que el campo se renderice
                setTimeout(() => obtenerInfoMetas(), 100);
            } else if (this.value === 'mensual') {
                divEjercicio.style.display = 'none';
                divPeriodo.style.display = 'block';
                inputEjercicio?.removeAttribute('required');
                selectPeriodo?.setAttribute('required', 'required');
                // NO ocultar metaInfo aquí - se ocultará solo si no hay período seleccionado
                // metaInfo?.classList.add('d-none'); ❌ REMOVIDO
            } else {
                divEjercicio.style.display = 'none';
                divPeriodo.style.display = 'none';
                metaInfo?.classList.add('d-none');
            }
        });
    }

    // Detectar cambio de período para metas mensuales
    selectPeriodo?.addEventListener('change', function() {
        if (tipoMeta?.value === 'mensual') {
            obtenerInfoMetas();
        }
    });

    // Detectar cambio de ejercicio para metas anuales
    inputEjercicio?.addEventListener('change', function() {
        if (tipoMeta?.value === 'anual') {
            obtenerInfoMetas();
        }
    });

    // Validar ejercicio en tiempo real
    inputEjercicioEl?.addEventListener('input', function() {
        const ejercicioVal = parseInt(this.value);
        if (ejerciciosUsados.includes(ejercicioVal)) {
            ejercicioWarning?.classList.remove('d-none');
            this.classList.add('is-invalid');
        } else {
            ejercicioWarning?.classList.add('d-none');
            this.classList.remove('is-invalid');
        }
    });

    // Prevenir submit si el ejercicio ya está usado
    formModal?.addEventListener('submit', function(e) {
        if (tipoMeta?.value === 'anual') {
            const ejercicioVal = parseInt(inputEjercicioEl?.value);
            if (ejerciciosUsados.includes(ejercicioVal)) {
                e.preventDefault();
                alert('Ya existe una meta anual para el año ' + ejercicioVal + '. Por favor seleccione otro año.');
                inputEjercicioEl?.focus();
                return false;
            }
        }
    });

    // Resetear formulario al cerrar modal
    const modalElement = document.getElementById('modalMeta');
    if (modalElement) {
        // Evento al abrir modal
        modalElement.addEventListener('shown.bs.modal', function() {
            // Si ya hay un período seleccionado al abrir, obtener info automáticamente
            if (tipoMeta?.value === 'mensual' && selectPeriodo?.value) {
                setTimeout(() => obtenerInfoMetas(), 200);
            } else if (tipoMeta?.value === 'anual' && inputEjercicio?.value) {
                setTimeout(() => obtenerInfoMetas(), 200);
            }
        });

        // Evento al cerrar modal
        modalElement.addEventListener('hidden.bs.modal', function() {
            // Resetear campos
            if (tipoMeta) tipoMeta.value = '';
            if (valorObjetivo) valorObjetivo.value = '';
            if (selectPeriodo) selectPeriodo.value = '';

            // Ocultar divs
            if (divEjercicio) divEjercicio.style.display = 'none';
            if (divPeriodo) divPeriodo.style.display = 'none';

            // Ocultar info
            if (metaInfo) metaInfo.classList.add('d-none');

            // Resetear validaciones
            valorObjetivo?.removeAttribute('max');
            valorObjetivo?.removeAttribute('min');

            // Resetear hint
            if (valorHint) {
                valorHint.innerHTML = '<?php if ($metrica_seleccionada): ?>Meta en <?php echo e($metrica_seleccionada['unidad'] ?: 'unidades'); ?><?php endif; ?>';
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

<style>
/* Estilos para metas por año */
.metas-year-card {
    border-left: 4px solid var(--tblr-primary);
}

.year-header {
    padding-bottom: 0.75rem;
    margin-bottom: 1.5rem;
    border-bottom: 2px solid var(--tblr-border-color);
}

.year-header h3 {
    margin: 0;
    color: var(--tblr-primary);
    font-weight: 600;
}

.meta-anual-card {
    background: linear-gradient(135deg, rgba(32, 107, 196, 0.05) 0%, rgba(32, 107, 196, 0.02) 100%);
    border-radius: 0.5rem;
    padding: 1.5rem;
    border: 1px solid rgba(32, 107, 196, 0.1);
}

[data-bs-theme="dark"] .meta-anual-card {
    background: linear-gradient(135deg, rgba(32, 107, 196, 0.15) 0%, rgba(32, 107, 196, 0.05) 100%);
    border-color: rgba(32, 107, 196, 0.3);
}

.metas-mensuales-section {
    margin-top: 0;
    padding-top: 0;
}

/* Separación solo si hay meta anual arriba */
.meta-anual-card + .metas-mensuales-section {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 2px solid var(--tblr-border-color);
}

.table-metas-mensuales {
    margin-bottom: 0;
}

.table-metas-mensuales thead {
    background-color: var(--tblr-bg-surface-secondary);
}

.table-metas-mensuales thead th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    color: var(--tblr-muted);
    border-bottom: 2px solid var(--tblr-border-color);
    padding: 0.75rem 1rem;
}

.table-metas-mensuales tbody tr {
    transition: background-color 0.2s ease;
}

.table-metas-mensuales tbody tr:hover {
    background-color: var(--tblr-bg-surface-secondary);
}

.table-metas-mensuales tbody td {
    vertical-align: middle;
    padding: 0.875rem 1rem;
}

/* Responsive para móviles */
@media (max-width: 768px) {
    .meta-anual-card {
        padding: 1rem;
    }

    .meta-anual-card .h1 {
        font-size: 2rem;
    }

    .table-metas-mensuales {
        font-size: 0.875rem;
    }

    .table-metas-mensuales thead th,
    .table-metas-mensuales tbody td {
        padding: 0.5rem 0.75rem;
    }

    /* Ocultar columna de comparación en móvil */
    .table-metas-mensuales thead th:nth-child(3),
    .table-metas-mensuales tbody td:nth-child(3) {
        display: none;
    }

    /* Hacer botones más pequeños */
    .table-metas-mensuales .btn-sm {
        padding: 0.25rem 0.5rem;
    }
}

/* Modo oscuro */
[data-bs-theme="dark"] .metas-year-card {
    border-left-color: rgba(32, 107, 196, 0.6);
}

[data-bs-theme="dark"] .table-metas-mensuales tbody tr:hover {
    background-color: rgba(255, 255, 255, 0.05);
}

/* Animación suave al cargar */
.metas-year-card {
    animation: fadeInUp 0.3s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<!-- Tabler JS -->
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/js/tabler.min.js"></script>
<script src="<?php echo baseUrl('/assets/js/theme-toggle.js'); ?>"></script>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
