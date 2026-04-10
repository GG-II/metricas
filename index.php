<?php
/**
 * Dashboard Principal - Sistema de Grid Dinámico
 * Métricas IT - Modo Vista y Modo Edición
 */

require_once 'config.php';
requireLogin();

require_once 'models/Area.php';
require_once 'models/Periodo.php';
require_once 'models/Grafico.php';
require_once 'models/ValorMetrica.php';
require_once 'models/ChartRegistry.php';

// ========================================
// CONFIGURACIÓN INICIAL
// ========================================

$areaModel = new Area();
$periodoModel = new Periodo();
$graficoModel = new Grafico();
$valorMetricaModel = new ValorMetrica();

$user_role = $_SESSION['user_role'] ?? 'viewer';
$is_admin = isAdmin();

// Detectar modo edición (solo admin)
$is_edit_mode = isset($_GET['edit']) && $_GET['edit'] === '1' && $is_admin;

// ========================================
// ÁREA SELECCIONADA
// ========================================

$areas = $areaModel->getAll();
$area_id = isset($_GET['area']) ? (int)$_GET['area'] : ($areas[0]['id'] ?? 1);
$area_actual = $areaModel->getById($area_id);

if (!$area_actual) {
    die('Área no encontrada');
}

// ========================================
// PERÍODO SELECCIONADO
// ========================================

$periodos = $periodoModel->getAll();
$periodo_param = $_GET['periodo'] ?? null;

if ($periodo_param && strpos($periodo_param, '-') !== false) {
    list($ejercicio, $periodo_mes) = explode('-', $periodo_param);
} else {
    // Obtener período ACTUAL (mes y año actuales)
    $mes_actual = (int)date('n'); // 1-12
    $anio_actual = (int)date('Y');
    
    // Buscar el período que coincida con mes/año actual
    $periodo_encontrado = false;
    foreach ($periodos as $p) {
        if ($p['ejercicio'] == $anio_actual && $p['periodo'] == $mes_actual) {
            $ejercicio = $p['ejercicio'];
            $periodo_mes = $p['periodo'];
            $periodo_encontrado = true;
            break;
        }
    }
    
    // Si no existe el período actual, usar el más reciente
    if (!$periodo_encontrado && !empty($periodos)) {
        $ultimo_periodo = end($periodos);
        $ejercicio = $ultimo_periodo['ejercicio'];
        $periodo_mes = $ultimo_periodo['periodo'];
    } else if (!$periodo_encontrado) {
        // Fallback final
        $ejercicio = $anio_actual;
        $periodo_mes = $mes_actual;
    }
}

$periodo = $periodoModel->getByEjercicioYPeriodo($ejercicio, $periodo_mes);

if (!$periodo) {
    die('Período no encontrado');
}

// ========================================
// CARGAR GRÁFICOS DEL ÁREA
// ========================================

$graficos = $graficoModel->getLayoutByArea($area_id, $user_role);

// Cargar registro de gráficos
ChartRegistry::load();

// ========================================
// HELPERS: OBTENER DATOS DE MÉTRICAS
// ========================================

/**
 * Obtener datos de una métrica específica para el período actual
 */
function obtenerDatosMetrica($metrica_id, $periodo_id) {
    global $valorMetricaModel;
    
    $datos = $valorMetricaModel->getByMetricaYPeriodo($metrica_id, $periodo_id);
    
    if (!$datos) {
        return null;
    }
    
    return $datos;
}

/**
 * Obtener histórico de una métrica (últimos 12 meses)
 */
function obtenerHistoricoMetrica($metrica_id, $limite = 12) {
    global $valorMetricaModel;
    
    $historico = $valorMetricaModel->getHistorico($metrica_id, $limite);
    
    return $historico;
}

// ========================================
// PREPARAR DATOS PARA HEADER
// ========================================

$page_title = $area_actual['nombre'] . ' - Dashboard';
$periodo_selector = true;

// ========================================
// INCLUIR HEADER
// ========================================

include 'partials/header.php';
?>

<!-- ========================================
     NAVBAR DE ÁREAS
     ======================================== -->
<div class="navbar navbar-expand-md navbar-light border-bottom">
    <div class="container-xl">
        <div class="navbar-nav">
            <?php foreach ($areas as $area): ?>
                <a class="nav-link <?php echo ($area['id'] == $area_id) ? 'active' : ''; ?>"
                   href="?area=<?php echo $area['id']; ?>&periodo=<?php echo $ejercicio . '-' . $periodo_mes; ?><?php echo $is_edit_mode ? '&edit=1' : ''; ?>">
                    <i class="ti ti-<?php echo $area['icono'] ?? 'chart-bar'; ?> me-1"></i>
                    <?php echo htmlspecialchars($area['nombre']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ========================================
     CONTENEDOR PRINCIPAL DEL DASHBOARD
     ======================================== -->
<div class="page-wrapper">
    <div class="page-body">
        <div class="container-xl dashboard-container">
            
            <?php if (empty($graficos)): ?>
                <!-- ========================================
                     DASHBOARD VACÍO
                     ======================================== -->
                <div class="empty-dashboard">
                    <div class="empty-dashboard-icon">
                        <i class="ti ti-layout-dashboard"></i>
                    </div>
                    <h2 class="empty-dashboard-title">Dashboard vacío</h2>
                    <p class="empty-dashboard-subtitle">
                        No hay gráficos configurados para esta área.
                        <?php if ($is_admin): ?>
                            <br>Haz clic en "Agregar Gráfico" para comenzar.
                        <?php endif; ?>
                    </p>
                    
                    <?php if ($is_admin): ?>
                        <button type="button" class="btn btn-primary" onclick="agregarGrafico()">
                            <i class="ti ti-plus me-1"></i>
                            Agregar Primer Gráfico
                        </button>
                    <?php endif; ?>
                </div>
                
            <?php else: ?>
                <!-- ========================================
                     GRID DE WIDGETS (GridStack)
                     ======================================== -->
                <div class="grid-stack" id="dashboard-grid">
                    <?php foreach ($graficos as $grafico): ?>
                        <?php
                        // Parsear configuración JSON
                        $config = json_decode($grafico['configuracion'], true);
                        
                        // Variables para widget-base
                        $grafico_id = $grafico['id'];
                        $titulo = $grafico['titulo'];
                        $grid_x = $grafico['grid_x'];
                        $grid_y = $grafico['grid_y'];
                        $grid_w = $grafico['grid_w'];
                        $grid_h = $grafico['grid_h'];
                        
                        // Obtener datos según tipo de gráfico
                        $widget_content = '';
                        $area_color = $area_actual['color'] ?? '#3b82f6';
                        
                        // Obtener datos de métrica(s)
                        $metrica_data = null;
                        if (isset($config['metrica_id'])) {
                            $metrica_data = obtenerDatosMetrica($config['metrica_id'], $periodo['id']);
                        }
                        
                        // Renderizar usando ChartRegistry
                        $widget_content = ChartRegistry::render(
                            $grafico['tipo'],
                            $config,
                            $metrica_data,
                            $area_color
                        );
                        
                        // Renderizar widget con base
                        include 'components/widgets/widget-base.php';
                        ?>
                    <?php endforeach; ?>
                </div>
                
            <?php endif; ?>
            
            <?php if ($is_admin && $is_edit_mode && !empty($graficos)): ?>
                <!-- ========================================
                     BOTÓN AGREGAR GRÁFICO (Modo Edición)
                     ======================================== -->
                <div class="add-widget-container">
                    <button type="button" class="btn btn-add-widget" onclick="agregarGrafico()">
                        <i class="ti ti-plus me-2"></i>
                        Agregar Gráfico
                    </button>
                </div>
            <?php endif; ?>
            
        </div>
    </div>
</div>

<!-- ========================================
     JAVASCRIPT: GridStack + Funciones
     ======================================== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isEditMode = <?php echo $is_edit_mode ? 'true' : 'false'; ?>;
    const areaId = <?php echo $area_id; ?>;
    
    // ========================================
    // INICIALIZAR GRIDSTACK
    // ========================================
    
    const gridOptions = {
        column: 12,
        cellHeight: 80,
        margin: 16,
        float: true,
        animate: true,
        disableOneColumnMode: false,
        draggable: {
            handle: '.drag-handle'
        }
    };
    
    if (!isEditMode) {
        gridOptions.staticGrid = true;
        gridOptions.disableDrag = true;
        gridOptions.disableResize = true;
    }
    
    const grid = GridStack.init(gridOptions);
    
    // ========================================
    // REDIMENSIONAR GRÁFICOS APEXCHARTS
    // ========================================
    
    // Esperar a que el grid esté completamente renderizado
    setTimeout(() => {
        // Redimensionar todos los gráficos ApexCharts
        if (typeof ApexCharts !== 'undefined') {
            ApexCharts.exec('*', 'updateOptions', {}, true, true);
        }
        
        // Forzar resize de todos los gráficos
        window.dispatchEvent(new Event('resize'));
    }, 500);
    
    // También redimensionar cuando cambie el tamaño de ventana
    window.addEventListener('resize', () => {
        if (typeof ApexCharts !== 'undefined') {
            setTimeout(() => {
                ApexCharts.exec('*', 'updateOptions', {}, true, true);
            }, 100);
        }
    });
    
    // ========================================
    // AUTO-SAVE AL MOVER/REDIMENSIONAR
    // ========================================
    
    if (isEditMode) {
        let saveTimeout;
        
        grid.on('change', function(event, items) {
            clearTimeout(saveTimeout);
            
            saveTimeout = setTimeout(() => {
                guardarLayout(items);
            }, 500);
        });
        
        console.log('✅ Modo Edición: Drag & Drop habilitado');
    } else {
        console.log('👁️ Modo Vista: Grid estático');
    }
    
    // ========================================
    // GUARDAR LAYOUT EN BD
    // ========================================
    
    function guardarLayout(items) {
        if (!items || items.length === 0) return;
        
        const layout = items.map(item => ({
            id: parseInt(item.id),
            x: item.x,
            y: item.y,
            w: item.w,
            h: item.h
        }));
        
        fetch('<?php echo BASE_URL; ?>/api/guardar-layout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                area_id: areaId,
                items: layout
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('💾 Layout guardado:', data.actualizados, 'gráficos');
                mostrarToast('success', 'Layout guardado correctamente');
            } else {
                console.error('❌ Error:', data.message);
                mostrarToast('danger', 'Error al guardar: ' + data.message);
            }
        })
        .catch(error => {
            console.error('❌ Error de red:', error);
            mostrarToast('danger', 'Error de conexión');
        });
    }
    
    // ========================================
    // MOSTRAR TOAST (Notificaciones)
    // ========================================
    
    function mostrarToast(tipo, mensaje) {
        const toastHtml = `
            <div class="toast align-items-center text-bg-${tipo} border-0 position-fixed top-0 end-0 m-3" 
                 role="alert" 
                 style="z-index: 9999;">
                <div class="d-flex">
                    <div class="toast-body">
                        ${mensaje}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', toastHtml);
        
        const toastElement = document.querySelector('.toast:last-child');
        const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
        toast.show();
        
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }
    
    window.mostrarToast = mostrarToast;
});

// ========================================
// CAMBIAR PERÍODO
// ========================================

function cambiarPeriodo(valor) {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('periodo', valor);
    
    window.location.href = '?' + urlParams.toString();
}

// ========================================
// AGREGAR GRÁFICO
// ========================================

function agregarGrafico() {
    window.location.href = '<?php echo BASE_URL; ?>/admin/configurar-graficos.php?action=create&area_id=<?php echo $area_id; ?>';
}

// ========================================
// CONFIGURAR GRÁFICO
// ========================================

function configurarGrafico(graficoId) {
    window.location.href = '<?php echo BASE_URL; ?>/admin/configurar-graficos.php?action=edit&id=' + graficoId;
}

// ========================================
// ELIMINAR GRÁFICO
// ========================================

function eliminarGrafico(graficoId) {
    if (!confirm('¿Estás seguro de que deseas eliminar este gráfico?\n\nEsta acción no se puede deshacer.')) {
        return;
    }
    
    const grid = GridStack.init();
    const element = document.querySelector(`[gs-id="${graficoId}"]`);
    
    if (element) {
        grid.removeWidget(element);
    }
    
    fetch('<?php echo BASE_URL; ?>/admin/configurar-graficos.php?action=delete&id=' + graficoId, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarToast('success', 'Gráfico eliminado correctamente');
            
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            mostrarToast('danger', 'Error al eliminar: ' + data.message);
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarToast('danger', 'Error de conexión');
        window.location.reload();
    });
}
</script>

<?php include 'partials/footer.php'; ?>