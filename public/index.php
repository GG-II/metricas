<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Services\PermissionService;
use App\Models\Grafico;
use App\Models\ValorMetrica;
use App\Models\Periodo;
use App\Models\Departamento;
use App\Models\Area;
use App\Utils\ChartRegistry;

// Verificar autenticación
AuthMiddleware::handle();

$user = getCurrentUser();
$is_admin = in_array($user['rol'], ['super_admin', 'dept_admin', 'area_admin']);

// Inicializar modelos (necesarios para home_selector.php)
$deptModel = new Departamento();
$areaModel = new Area();

// CASO ESPECIAL: area_admin debe ir directo a su área asignada
if ($user['rol'] === 'area_admin') {
    if (empty($user['area_id'])) {
        die('Tu usuario no tiene un área asignada. Contacta al administrador.');
    }

    // Si no hay área en URL o es diferente a la asignada, redirigir
    $area_param = $_GET['area'] ?? null;
    if (!$area_param || (int)$area_param !== (int)$user['area_id']) {
        $periodo_param = $_GET['periodo'] ?? date('Y') . '-' . date('n');
        header('Location: ?area=' . $user['area_id'] . '&periodo=' . $periodo_param);
        exit;
    }
}

// Obtener departamentos permitidos
$departamentos = PermissionService::getDepartamentosPermitidos($user);

if (empty($departamentos) && $user['rol'] !== 'area_admin') {
    die('No tienes acceso a ningún departamento. Contacta al administrador.');
}

// Departamento actual
$dept_id = $_GET['dept'] ?? null;

// Si es super_admin y no hay departamento seleccionado, mostrar todas las áreas
if ($user['rol'] === 'super_admin' && !$dept_id) {
    $areas = PermissionService::getAreasPermitidas($user, null);
} else if ($user['rol'] === 'area_admin') {
    // area_admin solo ve su área asignada
    $areas = PermissionService::getAreasPermitidas($user, null);
} else {
    // Para otros roles o si hay departamento seleccionado
    if (!$dept_id) {
        $dept_id = $departamentos[0]['id'] ?? null;
    }

    if ($dept_id && !PermissionService::canViewDepartamento($user, $dept_id)) {
        die('No tienes permiso para ver este departamento.');
    }

    $areas = PermissionService::getAreasPermitidas($user, $dept_id);
}

// Si no hay área seleccionada, mostrar página de selección por tipos (solo para super_admin)
// Para otros usuarios, seleccionar la primera área automáticamente
if (!isset($_GET['area']) && $user['rol'] === 'super_admin' && count($areas) > 1) {
    // Mostrar vista de selección organizada por tipos
    require_once __DIR__ . '/../views/home_selector.php';
    exit;
}

$area_id = $_GET['area'] ?? ($areas[0]['id'] ?? null);

// Si no hay áreas disponibles, redirigir según el rol
if (!$area_id) {
    // Si es admin, redirigir al panel de administración para crear áreas
    if (in_array($user['rol'], ['super_admin', 'dept_admin', 'area_admin'])) {
        header('Location: ' . baseUrl('/admin/index.php') . '?mensaje=sin_areas');
        exit;
    }

    // Si es viewer, mostrar mensaje
    $pageTitle = 'Sin áreas disponibles';
    require_once __DIR__ . '/../views/layouts/header.php';
    echo '<div class="page-wrapper"><div class="page-body"><div class="container-xl">';
    echo '<div class="empty text-center py-5">';
    echo '<div class="empty-icon mb-3"><i class="ti ti-alert-circle" style="font-size: 4rem; color: #f59f00;"></i></div>';
    echo '<h2>No hay áreas disponibles</h2>';
    echo '<p class="text-muted">Contacta al administrador para que configure áreas en el sistema.</p>';
    echo '</div></div></div></div>';
    require_once __DIR__ . '/../views/layouts/footer.php';
    exit;
}

// Verificar permiso para el área
if (!PermissionService::canViewArea($user, $area_id)) {
    die('No tienes permiso para ver esta área.');
}

// Obtener información del área y departamento
$departamento_actual = $dept_id ? $deptModel->find($dept_id) : null;
$area_actual = $area_id ? $areaModel->findWithDepartamento($area_id) : null;

// Verificar que el área existe
if (!$area_actual) {
    die('El área solicitada no existe o no está disponible. <a href="?">Volver al inicio</a>');
}

// Obtener período actual o seleccionado
$periodoModel = new Periodo();
$periodos = $periodoModel->getAll();

$periodo_param = $_GET['periodo'] ?? null;

if ($periodo_param && strpos($periodo_param, '-') !== false) {
    list($ejercicio, $periodo_mes) = explode('-', $periodo_param);
} else {
    // Obtener período actual
    $mes_actual = (int)date('n');
    $anio_actual = (int)date('Y');

    $ejercicio = $anio_actual;
    $periodo_mes = $mes_actual;
}

$periodo = $periodoModel->findByEjercicioAndPeriodo($ejercicio, $periodo_mes);

if (!$periodo) {
    // Si no existe, usar el último período disponible
    $periodo = $periodos[count($periodos) - 1] ?? null;
    if ($periodo) {
        $ejercicio = $periodo['ejercicio'];
        $periodo_mes = $periodo['periodo'];
    }
}

// Detectar modo edición
$is_edit_mode = isset($_GET['edit']) && $_GET['edit'] === '1' && $is_admin;

// Cargar gráficos del área
$graficoModel = new Grafico();
$graficos = $graficoModel->getLayoutByArea($area_id, $user['rol']);

// Cargar registro de gráficos
ChartRegistry::load();

// Helpers para obtener datos de métricas
function obtenerDatosMetrica($metrica_id, $periodo_id) {
    $valorMetricaModel = new ValorMetrica();
    return $valorMetricaModel->getByMetricaYPeriodo($metrica_id, $periodo_id);
}

$pageTitle = $area_actual['nombre'] . ' - Dashboard';
require_once __DIR__ . '/../views/layouts/header.php';
?>

<!-- Navbar de Departamentos y Áreas -->
<div class="navbar navbar-expand-md navbar-light border-bottom">
    <div class="container-xl">
        <?php if ($user['rol'] === 'super_admin' && count($departamentos) > 1): ?>
            <!-- Selector de Departamentos para Super Admin -->
            <div class="navbar-nav me-3">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="ti ti-building me-1"></i>
                        <?php echo $departamento_actual ? e($departamento_actual['nombre']) : 'Todos los Departamentos'; ?>
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item <?php echo !$dept_id ? 'active' : ''; ?>"
                           href="?periodo=<?php echo $ejercicio . '-' . $periodo_mes; ?>">
                            <i class="ti ti-layout-grid me-2"></i>
                            Todas las Áreas
                        </a>
                        <div class="dropdown-divider"></div>
                        <?php foreach ($departamentos as $dept):
                            // Obtener la primera área del departamento para el enlace directo
                            $areas_dept = $areaModel->getByDepartamento($dept['id']);
                            $primera_area = !empty($areas_dept) ? $areas_dept[0]['id'] : null;
                        ?>
                        <a class="dropdown-item <?php echo ($dept_id == $dept['id']) ? 'active' : ''; ?>"
                           href="?dept=<?php echo $dept['id']; ?><?php echo $primera_area ? '&area=' . $primera_area : ''; ?>&periodo=<?php echo $ejercicio . '-' . $periodo_mes; ?>">
                            <i class="ti ti-<?php echo e($dept['icono']); ?> me-2"></i>
                            <?php echo e($dept['nombre']); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Áreas -->
        <div class="navbar-nav flex-nowrap overflow-auto" id="areas-nav">
            <?php foreach ($areas as $area): ?>
                <a class="nav-link area-nav-link <?php echo ($area['id'] == $area_id) ? 'active' : ''; ?>"
                   href="?<?php echo $dept_id ? 'dept=' . $dept_id . '&' : ''; ?>area=<?php echo $area['id']; ?>&periodo=<?php echo $ejercicio . '-' . $periodo_mes; ?><?php echo $is_edit_mode ? '&edit=1' : ''; ?>"
                   data-area-color="<?php echo e($area['color'] ?? '#206bc4'); ?>"
                   style="<?php echo ($area['id'] == $area_id) ? 'background-color: ' . e($area['color'] ?? '#206bc4') . '; color: white;' : ''; ?>">
                    <i class="ti ti-<?php echo e($area['icono'] ?? 'chart-bar'); ?> me-1"></i><?php echo e($area['nombre']); ?><?php if ($user['rol'] === 'super_admin' && !$dept_id && isset($area['departamento_nombre'])): ?><span class="badge ms-1 dept-badge" style="<?php echo ($area['id'] == $area_id) ? 'background-color: rgba(255,255,255,0.2); color: white;' : 'background-color: rgba(0,0,0,0.06);'; ?>"><?php echo e(substr($area['departamento_nombre'], 0, 3)); ?></span><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>

        <style>
        /* Mejorar navegación de áreas */
        #areas-nav {
            gap: 0.25rem;
            padding: 0.25rem 0;
        }

        .area-nav-link {
            white-space: nowrap !important;
            padding: 0.5rem 0.75rem !important;
            font-size: 0.875rem;
            border-radius: 0.375rem;
            display: inline-flex !important;
            align-items: center;
            flex-shrink: 0 !important;
            width: auto !important;
            max-width: none !important;
            overflow: visible !important;
            text-overflow: clip !important;
            transition: all 0.2s;
        }

        .area-nav-link:hover {
            opacity: 0.8;
        }

        .area-nav-link.active {
            color: white !important;
            font-weight: 500;
        }

        .area-nav-link .dept-badge {
            font-size: 0.65rem;
            padding: 0.15rem 0.35rem;
            flex-shrink: 0;
        }

        /* Scroll suave */
        #areas-nav {
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }

        #areas-nav::-webkit-scrollbar {
            height: 6px;
        }

        #areas-nav::-webkit-scrollbar-thumb {
            background-color: rgba(0,0,0,0.2);
            border-radius: 3px;
        }

        #areas-nav::-webkit-scrollbar-thumb:hover {
            background-color: rgba(0,0,0,0.3);
        }
        </style>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-scroll al área activa
            const activeLink = document.querySelector('.area-nav-link.active');
            if (activeLink) {
                setTimeout(() => {
                    activeLink.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                }, 100);
            }
        });
        </script>
    </div>
</div>

<!-- Dashboard Principal -->
<div class="page-wrapper">
    <div class="page-body">
        <div class="container-xl">

            <!-- Header del Dashboard -->
            <div class="page-header mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <?php if ($departamento_actual): ?>
                        <div class="page-pretitle">
                            <?php echo e($departamento_actual['nombre']); ?>
                        </div>
                        <?php endif; ?>
                        <h2 class="page-title">
                            <?php if ($area_actual): ?>
                                <i class="ti ti-<?php echo e($area_actual['icono']); ?> me-2"></i>
                                <?php echo e($area_actual['nombre']); ?>
                            <?php else: ?>
                                <i class="ti ti-dashboard me-2"></i>
                                Dashboard General
                            <?php endif; ?>
                        </h2>
                    </div>
                    <div class="col-auto">
                        <!-- Selector de Período -->
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="ti ti-calendar me-1"></i>
                                <?php echo $periodo ? e($periodo['nombre']) : 'Seleccionar período'; ?>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <?php foreach (array_slice(array_reverse($periodos), 0, 12) as $p): ?>
                                <a class="dropdown-item <?php echo ($p['id'] == ($periodo['id'] ?? 0)) ? 'active' : ''; ?>"
                                   href="?<?php echo $dept_id ? 'dept=' . $dept_id . '&' : ''; ?><?php echo $area_id ? 'area=' . $area_id . '&' : ''; ?>periodo=<?php echo $p['ejercicio'] . '-' . $p['periodo']; ?>">
                                    <?php echo e($p['nombre']); ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Botones de Admin -->
                        <?php if ($is_admin && $area_id): ?>
                            <?php if (!$is_edit_mode): ?>
                                <a href="?<?php echo $dept_id ? 'dept=' . $dept_id . '&' : ''; ?>area=<?php echo $area_id; ?>&periodo=<?php echo $ejercicio . '-' . $periodo_mes; ?>&edit=1"
                                   class="btn btn-primary">
                                    <i class="ti ti-edit me-1"></i>
                                    Modo Edición
                                </a>
                            <?php else: ?>
                                <a href="?<?php echo $dept_id ? 'dept=' . $dept_id . '&' : ''; ?>area=<?php echo $area_id; ?>&periodo=<?php echo $ejercicio . '-' . $periodo_mes; ?>"
                                   class="btn btn-success">
                                    <i class="ti ti-check me-1"></i>
                                    Guardar y Salir
                                </a>
                                <button type="button" class="btn btn-primary" onclick="agregarGrafico()">
                                    <i class="ti ti-plus me-1"></i>
                                    Agregar Gráfico
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Grid de Gráficos -->
            <?php if (empty($graficos)): ?>
                <!-- Dashboard vacío -->
                <div class="empty text-center py-5">
                    <div class="empty-icon mb-3">
                        <i class="ti ti-layout-dashboard" style="font-size: 4rem; color: #94a3b8;"></i>
                    </div>
                    <h2>Dashboard vacío</h2>
                    <p class="text-muted">
                        No hay gráficos configurados para esta área.
                        <?php if ($is_admin): ?>
                            <br>Haz clic en "Agregar Gráfico" para comenzar.
                        <?php endif; ?>
                    </p>
                    <?php if ($is_admin): ?>
                        <button type="button" class="btn btn-primary mt-3" onclick="agregarGrafico()">
                            <i class="ti ti-plus me-1"></i>
                            Agregar Primer Gráfico
                        </button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Grid con GridStack -->
                <div class="grid-stack" id="dashboard-grid">
                    <?php foreach ($graficos as $grafico): ?>
                        <?php
                        $config = json_decode($grafico['configuracion'], true);
                        $metrica_data = null;

                        // Obtener datos de la métrica si el gráfico la usa
                        if (isset($config['metrica_id']) && $periodo) {
                            $metrica_data = obtenerDatosMetrica($config['metrica_id'], $periodo['id']);
                        }

                        $area_color = $area_actual['color'] ?? '#3b82f6';

                        // Extraer IDs de métricas del gráfico
                        $metrica_ids = [];
                        if (isset($config['metrica_id']) && is_scalar($config['metrica_id'])) {
                            $metrica_ids[] = $config['metrica_id'];
                        }
                        if (isset($config['metricas']) && is_array($config['metricas'])) {
                            foreach ($config['metricas'] as $m) {
                                if (is_scalar($m)) {
                                    $metrica_ids[] = $m;
                                }
                            }
                        }
                        if (isset($config['metrica_1'])) {
                            for ($i = 1; $i <= 5; $i++) {
                                if (!empty($config["metrica_$i"]) && is_scalar($config["metrica_$i"])) {
                                    $metrica_ids[] = $config["metrica_$i"];
                                }
                            }
                        }
                        $metrica_ids = array_unique(array_filter($metrica_ids));
                        ?>
                        <div class="grid-stack-item"
                             gs-id="<?php echo $grafico['id']; ?>"
                             gs-x="<?php echo $grafico['grid_x']; ?>"
                             gs-y="<?php echo $grafico['grid_y']; ?>"
                             gs-w="<?php echo $grafico['grid_w']; ?>"
                             gs-h="<?php echo $grafico['grid_h']; ?>"
                             <?php if (!empty($metrica_ids)): ?>
                             data-metrica-id="<?php echo implode(',', $metrica_ids); ?>"
                             <?php endif; ?>>
                            <div class="grid-stack-item-content card">
                                <div class="card-header">
                                    <h3 class="card-title"><?php echo e($grafico['titulo']); ?></h3>
                                    <?php if ($is_edit_mode): ?>
                                    <div class="card-actions">
                                        <a href="#" class="btn btn-sm btn-ghost-danger" onclick="eliminarGrafico(<?php echo $grafico['id']; ?>)">
                                            <i class="ti ti-trash"></i>
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body p-0">
                                    <?php echo ChartRegistry::render($grafico['tipo'], $config, $metrica_data, $area_color, $periodo); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/gridstack@9.0.0/dist/gridstack-all.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<link href="https://cdn.jsdelivr.net/npm/gridstack@9.0.0/dist/gridstack.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/gridstack@9.0.0/dist/gridstack-extra.min.css" rel="stylesheet">

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($is_edit_mode): ?>
    // Inicializar GridStack en modo edición
    const grid = GridStack.init({
        cellHeight: 80,
        margin: 10,
        float: false,
        animate: true,
        minRow: 20  // Mínimo 20 filas para asegurar espacio de scroll
    });

    // Guardar posiciones al cambiar
    grid.on('change', function(event, items) {
        const posiciones = items.map(item => ({
            id: parseInt(item.el.getAttribute('gs-id')),
            x: item.x,
            y: item.y,
            w: item.w,
            h: item.h
        }));

        // Guardar en servidor
        fetch('<?php echo baseUrl("/api/guardar-layout.php"); ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                area_id: <?php echo $area_id; ?>,
                items: posiciones
            })
        });
    });
    <?php else: ?>
    // Inicializar GridStack en modo vista (sin edición)
    GridStack.init({
        staticGrid: true,
        cellHeight: 80,
        margin: 10
    });
    <?php endif; ?>
});

function agregarGrafico() {
    window.location.href = '<?php echo baseUrl("/admin/graficos.php?area="); ?><?php echo $area_id; ?>';
}

function eliminarGrafico(id) {
    if (confirm('¿Eliminar este gráfico?')) {
        window.location.href = '<?php echo baseUrl("/admin/graficos.php?area="); ?><?php echo $area_id; ?>&eliminar=' + id;
    }
}
</script>

<!-- Export Module -->
<script src="<?php echo baseUrl('/assets/js/export.js'); ?>"></script>

<style>
.grid-stack {
    background: transparent;
}

.grid-stack-item-content {
    overflow: hidden;
}

.grid-stack-item-content .card {
    height: 100%;
    margin: 0;
}

.grid-stack-item-content .card-body {
    height: calc(100% - 60px);
    overflow: auto;
}
</style>

<?php require_once __DIR__ . '/../views/layouts/footer.php'; ?>
