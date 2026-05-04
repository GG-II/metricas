<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Services\PermissionService;
use App\Models\Grafico;
use App\Models\Area;
use App\Models\Metrica;
use App\Models\Departamento;
use App\Utils\ChartRegistry;

AuthMiddleware::handle();
PermissionMiddleware::requireAdmin();

$user = getCurrentUser();
$graficoModel = new Grafico();
$areaModel = new Area();
$metricaModel = new Metrica();
$deptModel = new Departamento();

// Cargar registro de gráficos
ChartRegistry::load();

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ✅ PROTECCIÓN CSRF
    csrf_validate();

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'crear':
                $area_id = (int)$_POST['area_id'];

                // Verificar permiso
                if (!PermissionService::canEditArea($user, $area_id)) {
                    setFlash('error', 'No tienes permiso para editar esta área');
                    redirect('/admin/graficos.php');
                    break;
                }

                // Procesar configuración según el tipo de gráfico
                $tipo = sanitize($_POST['tipo']);
                $configuracion = ChartRegistry::processForm($tipo, $_POST);

                $data = [
                    'area_id' => $area_id,
                    'tipo' => $tipo,
                    'titulo' => sanitize($_POST['titulo']),
                    'configuracion' => json_encode($configuracion),
                    'grid_w' => (int)($_POST['grid_w'] ?? 4),
                    'grid_h' => (int)($_POST['grid_h'] ?? 3),
                    'grid_x' => 0,
                    'grid_y' => 0,
                    'activo' => 1
                ];

                if ($graficoModel->create($data)) {
                    setFlash('success', '✓ Gráfico creado exitosamente');
                    redirect('/admin/graficos.php?area=' . $area_id);
                } else {
                    setFlash('error', 'Error al crear el gráfico');
                }
                break;

            case 'editar':
                $id = (int)$_POST['id'];
                $grafico = $graficoModel->find($id);

                if (!$grafico || !PermissionService::canEditArea($user, $grafico['area_id'])) {
                    setFlash('error', 'No tienes permiso para editar este gráfico');
                    redirect('/admin/graficos.php');
                    break;
                }

                $tipo = sanitize($_POST['tipo']);
                $configuracion = ChartRegistry::processForm($tipo, $_POST);

                $data = [
                    'titulo' => sanitize($_POST['titulo']),
                    'tipo' => $tipo,
                    'configuracion' => json_encode($configuracion),
                    'grid_w' => (int)($_POST['grid_w'] ?? 4),
                    'grid_h' => (int)($_POST['grid_h'] ?? 3)
                ];

                if ($graficoModel->update($id, $data)) {
                    setFlash('success', '✓ Gráfico actualizado exitosamente');
                } else {
                    setFlash('error', 'Error al actualizar el gráfico');
                }
                redirect('/admin/graficos.php?area=' . $grafico['area_id']);
                break;

            case 'eliminar':
                $id = (int)$_POST['id'];
                $grafico = $graficoModel->find($id);

                if ($grafico && PermissionService::canEditArea($user, $grafico['area_id'])) {
                    $graficoModel->delete($id);
                    setFlash('success', '✓ Gráfico eliminado permanentemente');
                }
                redirect('/admin/graficos.php?area=' . ($grafico['area_id'] ?? ''));
                break;
        }
    }
}

// Obtener área seleccionada
// Para area_admin, forzar su área asignada
if ($user['rol'] === 'area_admin') {
    $area_id = $user['area_id'];
} else {
    $area_id = $_GET['area'] ?? null;
}

$area = null;
$metricas = [];
$graficos = [];

if ($area_id) {
    if (!PermissionService::canViewArea($user, $area_id)) {
        die('No tienes permiso para ver esta área.');
    }

    $area = $areaModel->findWithDepartamento($area_id);
    $metricas = $metricaModel->getByArea($area_id, true);
    $graficos = $graficoModel->getByArea($area_id, false);

    // Marcar qué métricas tienen metas definidas
    $db = getDB();
    $stmt = $db->query("SELECT DISTINCT metrica_id FROM metas_metricas WHERE activo = 1");
    $metricas_con_meta = array_column($stmt->fetchAll(), 'metrica_id');

    foreach ($metricas as &$metrica) {
        $metrica['tiene_meta'] = in_array($metrica['id'], $metricas_con_meta);
    }
    unset($metrica);
}

// Obtener todos los tipos de gráficos
$tipos_graficos = ChartRegistry::getMetadata();

// Gráfico a editar
$editando = null;
if (isset($_GET['editar'])) {
    $editando = $graficoModel->find($_GET['editar']);
    if ($editando) {
        $editando['configuracion_array'] = json_decode($editando['configuracion'], true);
    }
}

// Departamentos para selector
$departamentos = PermissionService::getDepartamentosPermitidos($user);

$pageTitle = 'Configuración de Gráficos';
require_once __DIR__ . '/../../views/layouts/header.php';
?>

<div class="page-wrapper">
    <div class="page-body">
        <div class="container-xl">

            <!-- Header -->
            <div class="page-header mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo baseUrl('/admin/index.php'); ?>">Administración</a></li>
                                <li class="breadcrumb-item active">Gráficos</li>
                            </ol>
                        </nav>
                        <h2 class="page-title">
                            <i class="ti ti-chart-bar me-2"></i>
                            <?php echo $area ? 'Gráficos de ' . e($area['nombre']) : 'Configuración de Gráficos'; ?>
                        </h2>
                    </div>
                    <div class="col-auto">
                        <?php if ($area): ?>
                        <button type="button" class="btn btn-primary" onclick="mostrarSelectorTipo()">
                            <i class="ti ti-plus me-1"></i>
                            Nuevo Gráfico
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
            <?php if (isset($_SESSION['flash']) && isset($_SESSION['flash']['type']) && isset($_SESSION['flash']['message'])): ?>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const type = '<?php echo $_SESSION['flash']['type']; ?>';
                    const message = '<?php echo addslashes($_SESSION['flash']['message']); ?>';

                    const iconMap = {
                        'success': 'success',
                        'error': 'error',
                        'warning': 'warning',
                        'info': 'info'
                    };

                    Swal.fire({
                        icon: iconMap[type] || 'info',
                        title: message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'swal-toast-custom'
                        }
                    });
                });
                </script>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <!-- Selector de Área -->
            <?php if (!$area && $user['rol'] !== 'area_admin'): ?>
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-3">Selecciona un Área</h3>
                    <form method="GET" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Departamento</label>
                            <select id="dept-select" class="form-select">
                                <option value="">Seleccionar departamento...</option>
                                <?php foreach ($departamentos as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>">
                                    <?php echo e($dept['nombre']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Área</label>
                            <select name="area" id="area-select" class="form-select" onchange="this.form.submit()">
                                <option value="">Seleccionar área...</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
            <?php else: ?>

            <!-- Lista de Gráficos del Área -->
            <?php if (empty($graficos)): ?>
                <div class="empty text-center py-5">
                    <div class="empty-icon mb-3">
                        <i class="ti ti-chart-bar" style="font-size: 4rem; color: #94a3b8;"></i>
                    </div>
                    <h2>No hay gráficos en esta área</h2>
                    <p class="text-muted">Crea el primer gráfico para visualizar tus métricas.</p>
                    <button type="button" class="btn btn-primary mt-3" onclick="mostrarSelectorTipo()">
                        <i class="ti ti-plus me-1"></i>
                        Crear Primer Gráfico
                    </button>
                </div>
            <?php else: ?>
                <div class="row row-cards">
                    <?php foreach ($graficos as $grafico):
                        $tipo_info = $tipos_graficos[$grafico['tipo']] ?? null;
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center mb-3">
                                    <div class="col-auto">
                                        <span class="avatar" style="background-color: <?php echo $area['color']; ?>;">
                                            <i class="ti ti-<?php echo $tipo_info['icono'] ?? 'chart-bar'; ?>"></i>
                                        </span>
                                    </div>
                                    <div class="col">
                                        <h3 class="card-title mb-0"><?php echo e($grafico['titulo']); ?></h3>
                                        <div class="text-muted small">
                                            <?php echo $tipo_info['nombre'] ?? $grafico['tipo']; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-muted small mb-2">
                                    <strong>Tamaño:</strong> <?php echo $grafico['grid_w']; ?> × <?php echo $grafico['grid_h']; ?> unidades
                                </div>

                                <?php if ($grafico['activo']): ?>
                                    <span class="badge bg-success-lt">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactivo</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer">
                                <div class="btn-list">
                                    <a href="?area=<?php echo $area_id; ?>&editar=<?php echo $grafico['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="ti ti-edit"></i> Editar
                                    </a>
                                    <a href="<?php echo baseUrl('/index.php?area=' . $area_id . '&edit=1'); ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-layout"></i> Ver en Dashboard
                                    </a>
                                    <form method="POST" class="d-inline" onsubmit="confirmarEliminacion(event, <?php echo $grafico['id']; ?>, '<?php echo addslashes($grafico['titulo']); ?>')">
                                        <input type="hidden" name="action" value="eliminar">
                                        <input type="hidden" name="id" value="<?php echo $grafico['id']; ?>">
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

<!-- Modal: Selector de Tipo de Gráfico -->
<div class="modal modal-blur fade" id="modalSelectorTipo" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Selecciona el Tipo de Gráfico</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <?php foreach ($tipos_graficos as $tipo_id => $tipo): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-link chart-type-card" onclick="seleccionarTipo('<?php echo $tipo_id; ?>')">
                            <div class="card-body text-center">
                                <div class="avatar avatar-xl mb-3" style="background-color: rgba(59, 130, 246, 0.1);">
                                    <i class="ti ti-<?php echo e($tipo['icono']); ?>" style="font-size: 2.5rem; color: #3b82f6;"></i>
                                </div>
                                <h3 class="card-title mb-2"><?php echo e($tipo['nombre']); ?></h3>
                                <p class="text-muted small"><?php echo e($tipo['descripcion']); ?></p>

                                <?php
                                // Guías de uso por tipo
                                $guias = [
                                    'kpi_card' => 'Ideal para destacar un valor importante como ventas totales o tickets resueltos.',
                                    'line' => 'Perfecto para mostrar tendencias a lo largo del tiempo.',
                                    'bar' => 'Excelente para comparar valores entre diferentes categorías.',
                                    'donut' => 'Muestra la distribución porcentual de un total.',
                                    'gauge' => 'Visualiza el progreso hacia una meta específica.',
                                    'progress' => 'Muestra el avance porcentual de un objetivo.',
                                    'multi_bar' => 'Compara múltiples métricas en un mismo gráfico de barras.',
                                    'comparison' => 'Compara dos períodos o dos métricas lado a lado.',
                                    'data_table' => 'Tabla de datos para métricas que requieren detalle numérico.',
                                    'line_with_goal' => 'Compara la evolución histórica de una métrica contra su meta objetivo.',
                                    'gauge_with_goal' => 'Visualiza el cumplimiento de metas en formato circular con porcentaje.',
                                    'kpi_with_goal' => 'Muestra un valor destacado con badge de cumplimiento de meta y tendencia.',
                                    'area' => 'Visualiza volumen acumulado a lo largo del tiempo con área sombreada.',
                                    'sparkline' => 'Mini-gráfico perfecto para tablas o dashboards densos sin ocupar espacio.',
                                    'bullet' => 'Compara valor actual vs meta con rangos de rendimiento en formato compacto.',
                                    'multi_line' => 'Compara tendencias de 2-5 métricas simultáneamente en el mismo gráfico.',
                                    'stacked_area' => 'Visualiza cómo las partes conforman el total a lo largo del tiempo.',
                                    'mixed' => 'Combina barras (valores puntuales) con líneas (tendencias) en un gráfico.',
                                    'stacked_bar' => 'Barras apiladas mostrando composición total por período.',
                                    'scatter' => 'Descubre relaciones y correlaciones entre dos métricas.',
                                    'horizontal_bar' => 'Compara múltiples métricas del mismo período con barras horizontales.',
                                    'radar_comparison' => 'Compara perfiles de múltiples métricas entre períodos.',
                                    'percentage_bar' => 'Muestra cómo cambia la distribución porcentual en el tiempo.',
                                    'period_comparison' => 'Compara el rendimiento de una métrica entre dos períodos específicos.'
                                ];
                                $guia = $guias[$tipo_id] ?? '';

                                // Ejemplos visuales SVG por tipo
                                $ejemplos = [
                                    'kpi_card' => '<svg width="100%" height="60" viewBox="0 0 200 60"><text x="100" y="25" text-anchor="middle" font-size="24" font-weight="bold" fill="#3b82f6">1,234</text><text x="100" y="45" text-anchor="middle" font-size="12" fill="#64748b">Tickets Resueltos</text></svg>',
                                    'line' => '<svg width="100%" height="60" viewBox="0 0 200 60"><polyline points="10,50 50,35 90,40 130,20 170,25 190,15" fill="none" stroke="#3b82f6" stroke-width="2"/><circle cx="10" cy="50" r="3" fill="#3b82f6"/><circle cx="50" cy="35" r="3" fill="#3b82f6"/><circle cx="90" cy="40" r="3" fill="#3b82f6"/><circle cx="130" cy="20" r="3" fill="#3b82f6"/><circle cx="170" cy="25" r="3" fill="#3b82f6"/><circle cx="190" cy="15" r="3" fill="#3b82f6"/></svg>',
                                    'bar' => '<svg width="100%" height="60" viewBox="0 0 200 60"><rect x="20" y="20" width="30" height="35" fill="#3b82f6" rx="2"/><rect x="60" y="30" width="30" height="25" fill="#3b82f6" rx="2"/><rect x="100" y="10" width="30" height="45" fill="#3b82f6" rx="2"/><rect x="140" y="25" width="30" height="30" fill="#3b82f6" rx="2"/></svg>',
                                    'donut' => '<svg width="100%" height="60" viewBox="0 0 200 60"><circle cx="100" cy="30" r="20" fill="none" stroke="#3b82f6" stroke-width="8" stroke-dasharray="75 125"/><circle cx="100" cy="30" r="20" fill="none" stroke="#60a5fa" stroke-width="8" stroke-dasharray="50 125" transform="rotate(135 100 30)"/><circle cx="100" cy="30" r="20" fill="none" stroke="#93c5fd" stroke-width="8" stroke-dasharray="25 125" transform="rotate(225 100 30)"/></svg>',
                                    'gauge' => '<svg width="100%" height="60" viewBox="0 0 200 60"><path d="M 30 50 A 1 1 0 0 1 170 50" fill="none" stroke="#e2e8f0" stroke-width="8" stroke-linecap="round"/><path d="M 30 50 A 1 1 0 0 1 130 20" fill="none" stroke="#3b82f6" stroke-width="8" stroke-linecap="round"/><text x="100" y="50" text-anchor="middle" font-size="16" font-weight="bold" fill="#3b82f6">75%</text></svg>',
                                    'progress' => '<svg width="100%" height="60" viewBox="0 0 200 60"><rect x="20" y="20" width="160" height="20" fill="#e2e8f0" rx="10"/><rect x="20" y="20" width="110" height="20" fill="#3b82f6" rx="10"/><text x="100" y="52" text-anchor="middle" font-size="12" fill="#64748b">68% completado</text></svg>',
                                    'multi_bar' => '<svg width="100%" height="60" viewBox="0 0 200 60"><rect x="20" y="25" width="15" height="30" fill="#3b82f6" rx="2"/><rect x="40" y="30" width="15" height="25" fill="#60a5fa" rx="2"/><rect x="70" y="20" width="15" height="35" fill="#3b82f6" rx="2"/><rect x="90" y="25" width="15" height="30" fill="#60a5fa" rx="2"/><rect x="120" y="15" width="15" height="40" fill="#3b82f6" rx="2"/><rect x="140" y="20" width="15" height="35" fill="#60a5fa" rx="2"/></svg>',
                                    'comparison' => '<svg width="100%" height="60" viewBox="0 0 200 60"><rect x="30" y="15" width="50" height="35" fill="#3b82f6" rx="2"/><rect x="120" y="20" width="50" height="30" fill="#60a5fa" rx="2"/><text x="55" y="60" text-anchor="middle" font-size="10" fill="#64748b">2025</text><text x="145" y="60" text-anchor="middle" font-size="10" fill="#64748b">2024</text></svg>',
                                    'data_table' => '<svg width="100%" height="60" viewBox="0 0 200 60"><line x1="20" y1="15" x2="180" y2="15" stroke="#94a3b8" stroke-width="1"/><line x1="20" y1="30" x2="180" y2="30" stroke="#cbd5e1" stroke-width="1"/><line x1="20" y1="45" x2="180" y2="45" stroke="#cbd5e1" stroke-width="1"/><circle cx="30" cy="23" r="2" fill="#3b82f6"/><circle cx="30" cy="38" r="2" fill="#3b82f6"/><rect x="60" y="20" width="40" height="6" fill="#e2e8f0" rx="2"/><rect x="60" y="35" width="40" height="6" fill="#e2e8f0" rx="2"/></svg>',
                                    'line_with_goal' => '<svg width="100%" height="60" viewBox="0 0 200 60"><line x1="10" y1="30" x2="190" y2="30" stroke="#94a3b8" stroke-width="1" stroke-dasharray="4,3"/><polyline points="10,50 50,35 90,40 130,20 170,25 190,15" fill="none" stroke="#3b82f6" stroke-width="2"/><circle cx="10" cy="50" r="3" fill="#3b82f6"/><circle cx="50" cy="35" r="3" fill="#3b82f6"/><circle cx="90" cy="40" r="3" fill="#3b82f6"/><circle cx="130" cy="20" r="3" fill="#3b82f6"/><circle cx="170" cy="25" r="3" fill="#3b82f6"/><circle cx="190" cy="15" r="3" fill="#3b82f6"/></svg>',
                                    'gauge_with_goal' => '<svg width="100%" height="60" viewBox="0 0 200 60"><path d="M 30 50 A 1 1 0 0 1 170 50" fill="none" stroke="#e2e8f0" stroke-width="8" stroke-linecap="round"/><path d="M 30 50 A 1 1 0 0 1 150 15" fill="none" stroke="#10b981" stroke-width="8" stroke-linecap="round"/><line x1="100" y1="50" x2="120" y2="18" stroke="#94a3b8" stroke-width="2" stroke-dasharray="3,2"/><circle cx="120" cy="18" r="3" fill="#94a3b8"/><text x="100" y="50" text-anchor="middle" font-size="14" font-weight="bold" fill="#10b981">92%</text></svg>',
                                    'kpi_with_goal' => '<svg width="100%" height="60" viewBox="0 0 200 60"><text x="100" y="20" text-anchor="middle" font-size="20" font-weight="bold" fill="#3b82f6">1,850</text><text x="100" y="32" text-anchor="middle" font-size="9" fill="#64748b">Meta: 2,000</text><rect x="40" y="38" width="120" height="6" fill="#e2e8f0" rx="3"/><rect x="40" y="38" width="110" height="6" fill="#10b981" rx="3"/><rect x="85" y="48" width="30" height="10" fill="#10b981" rx="2"/><text x="100" y="56" text-anchor="middle" font-size="7" fill="#fff">92%</text></svg>',
                                    'area' => '<svg width="100%" height="60" viewBox="0 0 200 60"><defs><linearGradient id="areaGrad" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" style="stop-color:#3b82f6;stop-opacity:0.4" /><stop offset="100%" style="stop-color:#3b82f6;stop-opacity:0.05" /></linearGradient></defs><polygon points="10,60 10,50 50,35 90,40 130,20 170,25 190,15 190,60" fill="url(#areaGrad)"/><polyline points="10,50 50,35 90,40 130,20 170,25 190,15" fill="none" stroke="#3b82f6" stroke-width="2"/></svg>',
                                    'sparkline' => '<svg width="100%" height="60" viewBox="0 0 200 60"><text x="100" y="22" text-anchor="middle" font-size="18" font-weight="bold" fill="#3b82f6">842</text><polyline points="30,45 50,42 70,38 90,43 110,35 130,40 150,32 170,30" fill="none" stroke="#3b82f6" stroke-width="2"/></svg>',
                                    'bullet' => '<svg width="100%" height="60" viewBox="0 0 200 60"><rect x="20" y="20" width="160" height="20" fill="#fee2e2" rx="2"/><rect x="20" y="20" width="106" height="20" fill="#fef3c7" rx="2"/><rect x="20" y="20" width="80" height="20" fill="#d1fae5" rx="2"/><rect x="20" y="22" width="130" height="16" fill="#3b82f6" rx="2"/><line x1="106" y1="15" x2="106" y2="45" stroke="#94a3b8" stroke-width="3"/><circle cx="106" cy="30" r="4" fill="#94a3b8"/></svg>',
                                    'multi_line' => '<svg width="100%" height="60" viewBox="0 0 200 60"><polyline points="10,50 50,35 90,40 130,25 170,30 190,20" fill="none" stroke="#3b82f6" stroke-width="2"/><polyline points="10,40 50,30 90,35 130,20 170,25 190,15" fill="none" stroke="#10b981" stroke-width="2"/><polyline points="10,45 50,38 90,32 130,30 170,22 190,18" fill="none" stroke="#f59e0b" stroke-width="2"/></svg>',
                                    'stacked_area' => '<svg width="100%" height="60" viewBox="0 0 200 60"><defs><linearGradient id="grad1" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" style="stop-color:#3b82f6;stop-opacity:0.5"/><stop offset="100%" style="stop-color:#3b82f6;stop-opacity:0.1"/></linearGradient><linearGradient id="grad2" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" style="stop-color:#10b981;stop-opacity:0.5"/><stop offset="100%" style="stop-color:#10b981;stop-opacity:0.1"/></linearGradient></defs><polygon points="10,60 10,45 50,40 90,35 130,40 170,38 190,35 190,60" fill="url(#grad1)"/><polygon points="10,45 10,30 50,28 90,25 130,28 170,26 190,24 190,35 170,38 130,40 90,35 50,40" fill="url(#grad2)"/></svg>',
                                    'mixed' => '<svg width="100%" height="60" viewBox="0 0 200 60"><rect x="20" y="30" width="20" height="25" fill="#3b82f6" rx="2"/><rect x="60" y="35" width="20" height="20" fill="#3b82f6" rx="2"/><rect x="100" y="25" width="20" height="30" fill="#3b82f6" rx="2"/><rect x="140" y="32" width="20" height="23" fill="#3b82f6" rx="2"/><polyline points="30,35 70,28 110,32 150,25" fill="none" stroke="#10b981" stroke-width="2"/><circle cx="30" cy="35" r="3" fill="#10b981"/><circle cx="70" cy="28" r="3" fill="#10b981"/><circle cx="110" cy="32" r="3" fill="#10b981"/><circle cx="150" cy="25" r="3" fill="#10b981"/></svg>',
                                    'stacked_bar' => '<svg width="100%" height="60" viewBox="0 0 200 60"><rect x="30" y="35" width="25" height="20" fill="#3b82f6" rx="2"/><rect x="30" y="20" width="25" height="15" fill="#10b981" rx="2"/><rect x="75" y="30" width="25" height="25" fill="#3b82f6" rx="2"/><rect x="75" y="18" width="25" height="12" fill="#10b981" rx="2"/><rect x="120" y="28" width="25" height="27" fill="#3b82f6" rx="2"/><rect x="120" y="15" width="25" height="13" fill="#10b981" rx="2"/><rect x="165" y="32" width="25" height="23" fill="#3b82f6" rx="2"/><rect x="165" y="20" width="25" height="12" fill="#10b981" rx="2"/></svg>',
                                    'scatter' => '<svg width="100%" height="60" viewBox="0 0 200 60"><circle cx="30" cy="45" r="4" fill="#3b82f6"/><circle cx="50" cy="35" r="4" fill="#3b82f6"/><circle cx="70" cy="40" r="4" fill="#3b82f6"/><circle cx="90" cy="30" r="4" fill="#3b82f6"/><circle cx="110" cy="25" r="4" fill="#3b82f6"/><circle cx="130" cy="32" r="4" fill="#3b82f6"/><circle cx="150" cy="22" r="4" fill="#3b82f6"/><circle cx="170" cy="28" r="4" fill="#3b82f6"/><line x1="20" y1="50" x2="180" y2="20" stroke="#94a3b8" stroke-width="1" stroke-dasharray="3,2"/></svg>',
                                    'horizontal_bar' => '<svg width="100%" height="60" viewBox="0 0 200 60"><rect x="40" y="8" width="120" height="8" fill="#3b82f6" rx="2"/><rect x="40" y="20" width="90" height="8" fill="#10b981" rx="2"/><rect x="40" y="32" width="140" height="8" fill="#f59e0b" rx="2"/><rect x="40" y="44" width="75" height="8" fill="#8b5cf6" rx="2"/></svg>',
                                    'radar_comparison' => '<svg width="100%" height="60" viewBox="0 0 200 60"><polygon points="100,15 135,25 145,45 100,50 55,45 65,25" fill="none" stroke="#e2e8f0" stroke-width="1"/><polygon points="100,20 130,27 138,42 100,45 62,42 70,27" fill="#3b82f6" fill-opacity="0.2" stroke="#3b82f6" stroke-width="2"/><polygon points="100,22 128,30 135,40 100,43 65,40 72,30" fill="#10b981" fill-opacity="0.2" stroke="#10b981" stroke-width="2"/></svg>',
                                    'percentage_bar' => '<svg width="100%" height="60" viewBox="0 0 200 60"><rect x="30" y="15" width="15" height="35" fill="#3b82f6" rx="2"/><rect x="45" y="15" width="10" height="35" fill="#10b981" rx="2"/><rect x="75" y="15" width="18" height="35" fill="#3b82f6" rx="2"/><rect x="93" y="15" width="12" height="35" fill="#10b981" rx="2"/><rect x="120" y="15" width="20" height="35" fill="#3b82f6" rx="2"/><rect x="140" y="15" width="10" height="35" fill="#10b981" rx="2"/><rect x="165" y="15" width="12" height="35" fill="#3b82f6" rx="2"/><rect x="177" y="15" width="13" height="35" fill="#10b981" rx="2"/></svg>',
                                    'period_comparison' => '<svg width="100%" height="60" viewBox="0 0 200 60"><rect x="30" y="15" width="50" height="35" fill="#e2e8f0" rx="4"/><text x="55" y="25" text-anchor="middle" font-size="10" fill="#64748b">Ene</text><text x="55" y="40" text-anchor="middle" font-size="16" font-weight="bold" fill="#3b82f6">842</text><rect x="120" y="15" width="50" height="35" fill="#e2e8f0" rx="4"/><text x="145" y="25" text-anchor="middle" font-size="10" fill="#64748b">Feb</text><text x="145" y="40" text-anchor="middle" font-size="16" font-weight="bold" fill="#10b981">965</text><line x1="85" y1="30" x2="115" y2="30" stroke="#10b981" stroke-width="2" marker-end="url(#arrow)"/><defs><marker id="arrow" markerWidth="10" markerHeight="10" refX="5" refY="3" orient="auto" markerUnits="strokeWidth"><path d="M0,0 L0,6 L9,3 z" fill="#10b981"/></marker></defs><text x="100" y="24" text-anchor="middle" font-size="9" fill="#10b981" font-weight="bold">+15%</text></svg>'
                                ];
                                $ejemplo_svg = $ejemplos[$tipo_id] ?? '';
                                ?>

                                <!-- Ejemplo visual -->
                                <?php if ($ejemplo_svg): ?>
                                <div class="mb-3 p-2 bg-light rounded" style="min-height: 70px;">
                                    <?php echo $ejemplo_svg; ?>
                                </div>
                                <?php endif; ?>

                                <?php if ($guia): ?>
                                <div class="card-hint mt-3 mb-0 text-start" style="font-size: 0.75rem; padding: 0.5rem; background-color: rgba(59, 130, 246, 0.1); border-left: 3px solid #3b82f6; border-radius: 4px; color: #3b82f6;">
                                    <i class="ti ti-bulb me-1"></i>
                                    <?php echo $guia; ?>
                                </div>
                                <?php endif; ?>

                                <div class="mt-3">
                                    <span class="badge bg-blue-lt">
                                        <?php echo $tipo['requiere_metricas']; ?>
                                        <?php echo $tipo['requiere_metricas'] == 1 ? 'métrica' : 'métricas'; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Configurar Gráfico -->
<div class="modal modal-blur fade" id="modalConfigurar" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="formGrafico">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <?php echo $editando ? 'Editar Gráfico' : 'Configurar Gráfico'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="<?php echo $editando ? 'editar' : 'crear'; ?>">
                    <input type="hidden" name="area_id" value="<?php echo $area_id; ?>">
                    <input type="hidden" name="tipo" id="tipo_seleccionado" value="<?php echo $editando['tipo'] ?? ''; ?>">
                    <?php if ($editando): ?>
                        <input type="hidden" name="id" value="<?php echo $editando['id']; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label required">Título del Gráfico</label>
                        <input type="text" name="titulo" class="form-control"
                               value="<?php echo e($editando['titulo'] ?? ''); ?>"
                               placeholder="Ej: Tickets Resueltos Mensuales" required>
                    </div>

                    <!-- Configuración Dinámica -->
                    <div id="configuracion-dinamica">
                        <!-- Se carga dinámicamente según el tipo -->
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Ancho (columnas)</label>
                            <input type="number" name="grid_w" class="form-control"
                                   value="<?php echo $editando['grid_w'] ?? 4; ?>" min="1" max="12">
                            <div class="form-hint">1-12 columnas (12 = ancho completo)</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Alto (filas)</label>
                            <input type="number" name="grid_h" class="form-control"
                                   value="<?php echo $editando['grid_h'] ?? 3; ?>" min="1" max="10">
                            <div class="form-hint">Altura en unidades</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check me-1"></i>
                        <?php echo $editando ? 'Actualizar' : 'Crear'; ?> Gráfico
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Datos de métricas y tipos de gráficos
const metricasArea = <?php echo json_encode($metricas); ?>;
const tiposGraficos = <?php echo json_encode($tipos_graficos); ?>;
const chartForms = {};

// Cargar formularios de configuración
<?php foreach ($tipos_graficos as $tipo_id => $tipo): ?>
chartForms['<?php echo $tipo_id; ?>'] = <?php echo json_encode(ChartRegistry::renderForm($tipo_id)); ?>;
<?php endforeach; ?>;

// Cargar funciones de configuración
const loadConfigFunctions = {};
<?php foreach ($tipos_graficos as $tipo_id => $tipo):
    $loadFunc = ChartRegistry::getLoadConfigJS($tipo_id);
    if ($loadFunc):
?>
loadConfigFunctions['<?php echo $tipo_id; ?>'] = <?php echo $loadFunc; ?>;
<?php endif; endforeach; ?>

function mostrarSelectorTipo() {
    const modal = new bootstrap.Modal(document.getElementById('modalSelectorTipo'));
    modal.show();
}

function seleccionarTipo(tipoId) {
    // Cerrar selector
    bootstrap.Modal.getInstance(document.getElementById('modalSelectorTipo')).hide();

    // Configurar tipo
    document.getElementById('tipo_seleccionado').value = tipoId;

    // Cargar formulario dinámico
    cargarFormularioDinamico(tipoId);

    // Abrir modal de configuración
    setTimeout(() => {
        const modal = new bootstrap.Modal(document.getElementById('modalConfigurar'));
        modal.show();
    }, 300);
}

function cargarFormularioDinamico(tipoId) {
    const container = document.getElementById('configuracion-dinamica');
    let html = chartForms[tipoId] || '<p>Formulario no disponible</p>';

    // Verificar si este tipo de gráfico requiere metas
    const tipoInfo = tiposGraficos[tipoId];
    const requiereMetas = tipoInfo?.requiere_metas || false;

    // Filtrar métricas según si el gráfico requiere metas
    const metricasFiltradas = requiereMetas
        ? metricasArea.filter(m => m.tiene_meta)
        : metricasArea;

    // Reemplazar TODOS los selects de métricas con las opciones reales
    const selectOptions = metricasFiltradas.map(m =>
        `<option value="${m.id}">${m.nombre} (${m.unidad || 'sin unidad'})</option>`
    ).join('');

    // Buscar todos los selects que tengan name con "metrica" (metrica_id, metrica_1, metrica_2, metricas[], etc.)
    html = html.replace(
        /<select[^>]+name="[^"]*metrica[^"]*"[^>]*>[\s\S]*?<\/select>/gi,
        function(match) {
            // Extraer el name original del select
            const nameMatch = match.match(/name="([^"]*)"/);
            const originalName = nameMatch ? nameMatch[1] : 'metrica_id';

            // Verificar si es required
            const isRequired = match.includes('required');

            const placeholder = requiereMetas ? 'Seleccionar métrica con meta...' : 'Seleccionar métrica...';

            return `<select name="${originalName}" class="form-select" ${isRequired ? 'required' : ''}>
                <option value="">${placeholder}</option>
                ${selectOptions}
            </select>`;
        }
    );

    container.innerHTML = html;
}

// Cargar áreas al seleccionar departamento
document.addEventListener('DOMContentLoaded', function() {
    const deptSelect = document.getElementById('dept-select');
    const areaSelect = document.getElementById('area-select');

    if (deptSelect && areaSelect) {
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
    }

    // Auto-abrir modal de edición si hay parámetro
    <?php if ($editando): ?>
        cargarFormularioDinamico('<?php echo $editando['tipo']; ?>');
        setTimeout(() => {
            const modal = new bootstrap.Modal(document.getElementById('modalConfigurar'));
            modal.show();

            // Cargar configuración existente
            const config = <?php echo json_encode($editando['configuracion_array']); ?>;
            const form = document.getElementById('formGrafico');

            if (loadConfigFunctions['<?php echo $editando['tipo']; ?>']) {
                loadConfigFunctions['<?php echo $editando['tipo']; ?>'](form, config);
            }
        }, 100);
    <?php endif; ?>
});
</script>

<style>
.chart-type-card {
    transition: all 0.2s;
    cursor: pointer;
}

.chart-type-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}

.chart-type-card .card-body {
    padding: 1.5rem;
}

/* Ejemplos de gráficos en modo oscuro */
[data-bs-theme="dark"] .chart-type-card .bg-light {
    background-color: rgba(255, 255, 255, 0.05) !important;
}

/* Hints permanentes en modo oscuro */
[data-bs-theme="dark"] .card-hint {
    background-color: rgba(59, 130, 246, 0.15) !important;
    border-left-color: #60a5fa !important;
    color: #93c5fd !important;
}

/* Modal en modo oscuro - corrección adicional */
[data-bs-theme="dark"] .modal-header {
    background-color: var(--bg-surface, #1e293b);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

[data-bs-theme="dark"] .modal-body {
    background-color: var(--bg-surface, #1e293b);
}

[data-bs-theme="dark"] .modal-footer {
    background-color: var(--bg-surface, #1e293b);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}
</style>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Tabler JS -->
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/js/tabler.min.js"></script>
<script src="<?php echo baseUrl('/assets/js/theme-toggle.js'); ?>"></script>

<style>
/* SweetAlert2 Dark Mode */
[data-bs-theme="dark"] .swal2-popup {
    background-color: #1e293b !important;
    color: #e2e8f0 !important;
}

[data-bs-theme="dark"] .swal2-title {
    color: #f1f5f9 !important;
}

[data-bs-theme="dark"] .swal2-html-container {
    color: #cbd5e1 !important;
}

[data-bs-theme="dark"] .swal2-confirm {
    background-color: #dc2626 !important;
}

[data-bs-theme="dark"] .swal2-cancel {
    background-color: #475569 !important;
}

[data-bs-theme="dark"] .swal2-icon.swal2-warning {
    border-color: #f59e0b !important;
    color: #f59e0b !important;
}

[data-bs-theme="dark"] .swal2-icon.swal2-success {
    border-color: #10b981 !important;
    color: #10b981 !important;
}

[data-bs-theme="dark"] .swal2-icon.swal2-error {
    border-color: #ef4444 !important;
    color: #ef4444 !important;
}

/* Toast notifications */
[data-bs-theme="dark"] .swal2-toast {
    background-color: #1e293b !important;
    border: 1px solid #334155 !important;
}

[data-bs-theme="dark"] .swal2-toast .swal2-title {
    color: #f1f5f9 !important;
}

/* Light mode toast */
.swal2-toast {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}
</style>

<script>
function confirmarEliminacion(event, graficoId, graficoNombre) {
    event.preventDefault();

    Swal.fire({
        title: '¿Eliminar gráfico?',
        html: `Se eliminará permanentemente:<br><strong>${graficoNombre}</strong>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="ti ti-trash me-1"></i> Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: {
            popup: 'swal-popup-custom'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Enviar formulario
            event.target.submit();
        }
    });
}
</script>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
