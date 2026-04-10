<?php
/**
 * Configurar Gráficos del Dashboard
 * Sistema auto-descubierto con ChartRegistry
 */

require_once '../config.php';
requireAdmin();

require_once '../models/Area.php';
require_once '../models/Metrica.php';
require_once '../models/Grafico.php';
require_once '../models/ChartRegistry.php';

$areaModel = new Area();
$metricaModel = new Metrica();
$graficoModel = new Grafico();

// Cargar tipos de gráficos disponibles
ChartRegistry::load();

$area_id = isset($_GET['area']) ? (int)$_GET['area'] : AREA_SOFTWARE;
$area = $areaModel->getById($area_id);
if (!$area) redirect('/index.php');

$areas = $areaModel->getActivas();
$mensaje = '';
$mensaje_tipo = '';

// ========================================
// CREAR GRÁFICO
// ========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_grafico'])) {
    try {
        $tipo = $_POST['tipo'];
        
        // Procesar configuración usando ChartRegistry
        $configuracion = ChartRegistry::processForm($tipo, $_POST);
        
        $data = [
            'area_id' => $area_id,
            'titulo' => sanitize($_POST['titulo']),
            'descripcion' => sanitize($_POST['descripcion']) ?: null,
            'tipo' => $tipo,
            'configuracion' => json_encode($configuracion), // ← IMPORTANTE: Convertir a JSON
            'grid_w' => (int)$_POST['grid_w'],
            'grid_h' => (int)$_POST['grid_h'],
            'activo' => isset($_POST['activo']) ? 1 : 0,
            'permisos_visualizacion' => $_POST['permisos_visualizacion'],
            'creado_por' => $_SESSION['user_id']
        ];
        
        $id = $graficoModel->createConPosicionAutomatica($data);
        logActivity('crear_grafico', 'configuracion_graficos', $id, "Creado gráfico: {$data['titulo']}");
        
        $mensaje = 'Gráfico creado exitosamente.';
        $mensaje_tipo = 'success';
    } catch (Exception $e) {
        $mensaje = 'Error al crear gráfico: ' . $e->getMessage();
        $mensaje_tipo = 'danger';
    }
}

// ========================================
// EDITAR GRÁFICO
// ========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_grafico'])) {
    try {
        $id = (int)$_POST['grafico_id'];
        $tipo = $_POST['tipo'];
        
        // Procesar configuración usando ChartRegistry
        $configuracion = ChartRegistry::processForm($tipo, $_POST);
        
        $data = [
            'titulo' => sanitize($_POST['titulo']),
            'descripcion' => sanitize($_POST['descripcion']) ?: null,
            'tipo' => $tipo,
            'configuracion' => json_encode($configuracion), // ← IMPORTANTE: Convertir a JSON
            'grid_w' => (int)$_POST['grid_w'],
            'grid_h' => (int)$_POST['grid_h'],
            'activo' => isset($_POST['activo']) ? 1 : 0,
            'permisos_visualizacion' => $_POST['permisos_visualizacion']
        ];
        
        $graficoModel->update($id, $data);
        logActivity('editar_grafico', 'configuracion_graficos', $id, "Editado gráfico: {$data['titulo']}");
        
        $mensaje = 'Gráfico actualizado exitosamente.';
        $mensaje_tipo = 'success';
    } catch (Exception $e) {
        $mensaje = 'Error al actualizar gráfico: ' . $e->getMessage();
        $mensaje_tipo = 'danger';
    }
}

// ========================================
// ELIMINAR GRÁFICO (GET y POST para AJAX)
// ========================================
if (isset($_GET['eliminar']) || (isset($_GET['action']) && $_GET['action'] === 'delete')) {
    $id = isset($_GET['eliminar']) ? (int)$_GET['eliminar'] : (int)$_GET['id'];
    
    try {
        $grafico = $graficoModel->getById($id);
        
        if (!$grafico) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Gráfico no encontrado']);
                exit;
            }
            $mensaje = 'Gráfico no encontrado';
            $mensaje_tipo = 'danger';
        } else {
            $graficoModel->delete($id);
            logActivity('eliminar_grafico', 'configuracion_graficos', $id, "Eliminado gráfico: {$grafico['titulo']}");
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Gráfico eliminado correctamente']);
                exit;
            }
            
            $mensaje = 'Gráfico eliminado exitosamente.';
            $mensaje_tipo = 'success';
        }
    } catch (Exception $e) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            exit;
        }
        
        $mensaje = 'Error al eliminar gráfico: ' . $e->getMessage();
        $mensaje_tipo = 'danger';
    }
}

// ========================================
// DUPLICAR GRÁFICO
// ========================================
if (isset($_GET['duplicar'])) {
    try {
        $id = (int)$_GET['duplicar'];
        $nuevo_id = $graficoModel->duplicar($id, $_SESSION['user_id']);
        
        $mensaje = 'Gráfico duplicado exitosamente.';
        $mensaje_tipo = 'success';
    } catch (Exception $e) {
        $mensaje = 'Error al duplicar gráfico: ' . $e->getMessage();
        $mensaje_tipo = 'danger';
    }
}

// Obtener gráficos del área
$graficos = $graficoModel->getByArea($area_id, false, 'admin');

// Obtener métricas del área
$metricas = $metricaModel->getByArea($area_id);

// Tipos de gráficos disponibles (desde ChartRegistry)
$tipos_graficos = ChartRegistry::getMetadata();

$page_title = 'Configurar Gráficos - ' . $area['nombre'];
$is_admin = true;
?>

<?php include '../partials/header.php'; ?>

<div class="page-body">
    <div class="container-xl">
        
        <div class="mb-3">
            <a href="<?php echo BASE_URL; ?>/index.php?area=<?php echo $area_id; ?>" class="btn btn-ghost-secondary">
                <i class="ti ti-arrow-left me-1"></i>
                Volver al Dashboard
            </a>
        </div>
        
        <div class="page-header mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-chart-dots me-2" style="color: <?php echo $area['color']; ?>"></i>
                        Configurar Gráficos
                    </h2>
                    <div class="text-muted mt-1"><?php echo $area['nombre']; ?></div>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modal-crear">
                        <i class="ti ti-plus me-2"></i>
                        Nuevo Gráfico
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Selector de área -->
        <div class="card mb-4">
            <div class="card-body">
                <label class="form-label">Cambiar área</label>
                <select class="form-select" onchange="window.location.href='?area=' + this.value">
                    <?php foreach ($areas as $a): ?>
                        <option value="<?php echo $a['id']; ?>" <?php echo ($a['id'] == $area_id) ? 'selected' : ''; ?>>
                            <?php echo $a['nombre']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $mensaje_tipo; ?> alert-dismissible fade show">
                <i class="ti ti-<?php echo $mensaje_tipo === 'success' ? 'check' : 'alert-circle'; ?> me-2"></i>
                <?php echo $mensaje; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Tabla de gráficos -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-list me-2"></i>
                    Gráficos de <?php echo $area['nombre']; ?>
                </h3>
                <div class="card-actions">
                    <span class="badge bg-primary"><?php echo count($graficos); ?> gráficos</span>
                </div>
            </div>
            
            <?php if (empty($graficos)): ?>
                <div class="card-body">
                    <div class="empty">
                        <div class="empty-icon"><i class="ti ti-chart-line-off"></i></div>
                        <p class="empty-title">No hay gráficos configurados</p>
                        <p class="empty-subtitle">Crea el primer gráfico para esta área.</p>
                        <div class="empty-action">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-crear">
                                <i class="ti ti-plus me-1"></i>
                                Crear Primer Gráfico
                            </button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-hover">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Tipo</th>
                                <th>Tamaño</th>
                                <th>Permisos</th>
                                <th class="w-1">Estado</th>
                                <th class="w-1 text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($graficos as $g): ?>
                                <?php
                                $tipo_info = $tipos_graficos[$g['tipo']] ?? ['nombre' => $g['tipo'], 'icono' => 'chart-bar'];
                                
                                $tamanio_texto = match((int)$g['grid_w']) {
                                    4 => 'Pequeño (33%)',
                                    6 => 'Mediano (50%)',
                                    8 => 'Grande (66%)',
                                    12 => 'Extra Grande (100%)',
                                    default => $g['grid_w'] . ' cols'
                                };
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2" style="background-color: <?php echo $area['color']; ?>20;">
                                                <i class="ti ti-<?php echo $tipo_info['icono']; ?>" style="color: <?php echo $area['color']; ?>"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($g['titulo']); ?></div>
                                                <?php if ($g['descripcion']): ?>
                                                    <div class="text-muted small"><?php echo htmlspecialchars(substr($g['descripcion'], 0, 60)); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-indigo-lt"><?php echo $tipo_info['nombre']; ?></span></td>
                                    <td><?php echo $tamanio_texto; ?></td>
                                    <td>
                                        <?php if ($g['permisos_visualizacion'] === 'admin'): ?>
                                            <span class="badge bg-red-lt">Solo admin</span>
                                        <?php else: ?>
                                            <span class="badge bg-green-lt">Todos</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($g['activo']): ?>
                                            <span class="status status-green">
                                                <span class="status-dot"></span>
                                                Activo
                                            </span>
                                        <?php else: ?>
                                            <span class="status status-secondary">
                                                <span class="status-dot"></span>
                                                Inactivo
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-primary" 
                                                    onclick='editarGrafico(<?php echo json_encode($g, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'
                                                    title="Editar">
                                                <i class="ti ti-edit"></i>
                                            </button>
                                            <a href="?area=<?php echo $area_id; ?>&duplicar=<?php echo $g['id']; ?>" 
                                               class="btn btn-sm btn-info"
                                               title="Duplicar">
                                                <i class="ti ti-copy"></i>
                                            </a>
                                            <button class="btn btn-sm btn-danger" 
                                                    onclick="confirmarEliminar(<?php echo $g['id']; ?>, '<?php echo addslashes($g['titulo']); ?>')"
                                                    title="Eliminar">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
    </div>
</div>

<!-- Modal: Crear Gráfico -->
<div class="modal modal-blur fade" id="modal-crear" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" id="form-crear">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-plus me-2"></i>
                        Nuevo Gráfico
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php 
                    $form_mode = 'crear';
                    include 'partials/grafico-form.php'; 
                    ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">
                        <i class="ti ti-x me-1"></i>
                        Cancelar
                    </button>
                    <button type="submit" name="crear_grafico" class="btn btn-primary">
                        <i class="ti ti-check me-1"></i>
                        Crear Gráfico
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Editar Gráfico -->
<div class="modal modal-blur fade" id="modal-editar" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" id="form-editar">
                <input type="hidden" name="grafico_id" id="edit-grafico-id">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-edit me-2"></i>
                        Editar Gráfico
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php 
                    $form_mode = 'editar';
                    include 'partials/grafico-form.php'; 
                    ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">
                        <i class="ti ti-x me-1"></i>
                        Cancelar
                    </button>
                    <button type="submit" name="editar_grafico" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const metricasDelArea = <?php echo json_encode($metricas); ?>;
const tiposGraficos = <?php echo json_encode($tipos_graficos); ?>;
const chartConfigLoaders = {};

// Cargar funciones de configuración desde ChartRegistry
<?php foreach (ChartRegistry::getAll() as $tipo_id => $chart): ?>
    <?php if (isset($chart['load_config_js'])): ?>
        chartConfigLoaders['<?php echo $tipo_id; ?>'] = <?php echo $chart['load_config_js']; ?>;
    <?php endif; ?>
<?php endforeach; ?>

function editarGrafico(grafico) {
    const form = document.getElementById('form-editar');
    
    document.getElementById('edit-grafico-id').value = grafico.id;
    
    const config = typeof grafico.configuracion === 'string' 
        ? JSON.parse(grafico.configuracion) 
        : grafico.configuracion;
    
    const campos = {
        'titulo': grafico.titulo,
        'descripcion': grafico.descripcion || '',
        'tipo': grafico.tipo,
        'grid_w': grafico.grid_w,
        'grid_h': grafico.grid_h,
        'permisos_visualizacion': grafico.permisos_visualizacion
    };
    
    for (const [campo, valor] of Object.entries(campos)) {
        const input = form.querySelector(`[name="${campo}"]`);
        if (input) input.value = valor;
    }
    
    const checkboxActivo = form.querySelector('[name="activo"]');
    if (checkboxActivo) checkboxActivo.checked = grafico.activo == 1;
    
    cambiarTipoGrafico(grafico.tipo, form, config);
    
    const modalElement = document.getElementById('modal-editar');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}

function confirmarEliminar(id, titulo) {
    if (confirm(`¿Eliminar el gráfico "${titulo}"?\n\nEsta acción no se puede deshacer.`)) {
        window.location.href = `?area=<?php echo $area_id; ?>&eliminar=${id}`;
    }
}
</script>

<style>
.modal-dialog-scrollable .modal-body {
    max-height: calc(100vh - 200px);
    overflow-y: auto;
}

.modal-body::-webkit-scrollbar {
    width: 8px;
}

.modal-body::-webkit-scrollbar-track {
    background: rgba(15, 23, 42, 0.5);
    border-radius: 4px;
}

.modal-body::-webkit-scrollbar-thumb {
    background: rgba(59, 130, 246, 0.5);
    border-radius: 4px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
    background: rgba(59, 130, 246, 0.7);
}

.modal-body .row {
    margin-bottom: 1rem;
}

#configuracion-container {
    min-height: 150px;
}
</style>


<?php include '../partials/footer.php'; ?>