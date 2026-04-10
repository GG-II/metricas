<?php
/**
 * Panel de administración - Editar métricas
 * Solo accesible por administradores
 */

require_once '../config.php';

// Proteger ruta (solo admin)
requireAdmin();

// Cargar modelos
require_once '../models/Area.php';
require_once '../models/Periodo.php';
require_once '../models/Metrica.php';
require_once '../models/ValorMetrica.php';

// Obtener área y período de la URL
$area_id = isset($_GET['area']) ? (int)$_GET['area'] : AREA_SOFTWARE;
$periodo_str = $_GET['periodo'] ?? getCurrentPeriod();
list($ejercicio, $periodo_mes) = explode('-', $periodo_str);

// Instanciar modelos
$areaModel = new Area();
$periodoModel = new Periodo();
$metricaModel = new Metrica();
$valorModel = new ValorMetrica();

// Obtener área
$area = $areaModel->getById($area_id);
if (!$area) {
    redirect('/index.php');
}

// Obtener período
$periodo = $periodoModel->getByEjercicioPeriodo($ejercicio, $periodo_mes);
if (!$periodo) {
    redirect('/index.php');
}

// Obtener todas las áreas para selector
$areas = $areaModel->getActivas();

// Obtener períodos para selector
$periodos = $periodoModel->getRecientes(12);

// Obtener métricas del área con valores
$metricas = $metricaModel->getByAreaConValores($area_id, $periodo['id']);

// Procesar formulario si se envía
$mensaje = '';
$mensaje_tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_metricas'])) {
    try {
        $db = getDB();
        $db->beginTransaction();
        
        $cambios_realizados = 0;
        
        foreach ($_POST['metricas'] as $metrica_id => $datos) {
            $metrica_id = (int)$metrica_id;
            $valor = trim($datos['valor']);
            $nota = trim($datos['nota'] ?? '');
            
            // Solo guardar si hay un valor
            if ($valor !== '') {
                $valorModel->guardarValor(
                    $metrica_id,
                    $periodo['id'],
                    $valor,
                    $nota ?: null,
                    $_SESSION['user_id']
                );
                $cambios_realizados++;
            }
        }
        
        $db->commit();
        
        // Registrar en log
        logActivity(
            'actualizar_metricas',
            'valores_metricas',
            $periodo['id'],
            "Actualizadas $cambios_realizados métricas para {$area['nombre']} - {$periodo['nombre']}"
        );
        
        $mensaje = "Se guardaron correctamente $cambios_realizados métricas.";
        $mensaje_tipo = 'success';
        
        // Recargar métricas actualizadas
        $metricas = $metricaModel->getByAreaConValores($area_id, $periodo['id']);
        
    } catch (Exception $e) {
        $db->rollBack();
        $mensaje = 'Error al guardar las métricas: ' . $e->getMessage();
        $mensaje_tipo = 'danger';
        error_log("Error guardando métricas: " . $e->getMessage());
    }
}

// Variables para header
$page_title = 'Administrar Métricas - ' . $area['nombre'] . ' - ' . $periodo['nombre'];
$is_admin = true;
?>

<?php include '../partials/header.php'; ?>

<!-- Contenido -->
<div class="page-body">
    <div class="container-xl">
        
        <!-- Breadcrumb / Volver -->
        <div class="mb-3">
            <a href="<?php echo BASE_URL; ?>/index.php?area=<?php echo $area_id; ?>&periodo=<?php echo $periodo_str; ?>" 
               class="btn btn-ghost-secondary">
                <i class="ti ti-arrow-left me-1"></i>
                Volver al Dashboard
            </a>
        </div>
        
        <!-- Título -->
        <div class="page-header mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-settings me-2" style="color: <?php echo $area['color']; ?>"></i>
                        Administrar Métricas
                    </h2>
                    <div class="text-muted mt-1">
                        <?php echo $area['nombre']; ?> - <?php echo $periodo['nombre']; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Selectores de área, período y botón configurar -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Área</label>
                        <select class="form-select" id="area-selector" onchange="cambiarArea(this.value)">
                            <?php foreach ($areas as $a): ?>
                                <option value="<?php echo $a['id']; ?>" <?php echo ($a['id'] == $area_id) ? 'selected' : ''; ?>>
                                    <?php echo $a['nombre']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Período</label>
                        <select class="form-select" id="periodo-selector" onchange="cambiarPeriodo(this.value)">
                            <?php foreach ($periodos as $p): ?>
                                <option value="<?php echo $p['ejercicio'] . '-' . $p['periodo']; ?>"
                                        <?php echo ($p['ejercicio'] == $ejercicio && $p['periodo'] == $periodo_mes) ? 'selected' : ''; ?>>
                                    <?php echo $p['nombre']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <a href="<?php echo BASE_URL; ?>/admin/configurar-metricas.php?area=<?php echo $area_id; ?>" 
                           class="btn btn-outline-primary w-100">
                            <i class="ti ti-adjustments me-1"></i>
                            Configurar
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mensaje de éxito/error -->
        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $mensaje_tipo; ?> alert-dismissible fade show" role="alert">
                <i class="ti ti-<?php echo $mensaje_tipo === 'success' ? 'check' : 'alert-circle'; ?> me-2"></i>
                <?php echo $mensaje; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Formulario de métricas -->
        <form method="POST" action="">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Métricas de <?php echo $area['nombre']; ?></h3>
                    <div class="card-actions">
                        <span class="text-muted">Última actualización: 
                            <?php 
                            $ultima_mod = null;
                            foreach ($metricas as $m) {
                                // Buscar la fecha de modificación más reciente
                            }
                            echo $ultima_mod ? formatDateTime($ultima_mod) : 'Nunca';
                            ?>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    
                    <?php if (empty($metricas)): ?>
                        <div class="empty">
                            <div class="empty-icon">
                                <i class="ti ti-database-off"></i>
                            </div>
                            <p class="empty-title">No hay métricas configuradas</p>
                            <p class="empty-subtitle text-muted">
                                Esta área no tiene métricas definidas.
                            </p>
                        </div>
                    <?php else: ?>
                        
                        <div class="row g-4">
                            <?php foreach ($metricas as $metrica): ?>
                                <div class="col-md-6">
                                    <div class="metric-form-item">
                                        <div class="d-flex align-items-start mb-2">
                                            <div class="metric-icon-small me-3" style="background: <?php echo $area['color']; ?>20;">
                                                <i class="ti ti-<?php echo $metrica['icono'] ?? 'chart-bar'; ?>" 
                                                   style="color: <?php echo $area['color']; ?>"></i>
                                            </div>
                                            <div class="flex-fill">
                                                <label class="form-label mb-1">
                                                    <strong><?php echo htmlspecialchars($metrica['nombre']); ?></strong>
                                                    <?php if ($metrica['unidad']): ?>
                                                        <span class="text-muted">(<?php echo $metrica['unidad']; ?>)</span>
                                                    <?php endif; ?>
                                                </label>
                                                <?php if ($metrica['descripcion']): ?>
                                                    <div class="text-muted small mb-2"><?php echo htmlspecialchars($metrica['descripcion']); ?></div>
                                                <?php endif; ?>
                                                
                                                <input type="number" 
                                                       step="<?php echo ($metrica['tipo_valor'] === 'decimal' || $metrica['tipo_valor'] === 'porcentaje') ? '0.01' : '1'; ?>"
                                                       class="form-control mb-2" 
                                                       name="metricas[<?php echo $metrica['id']; ?>][valor]"
                                                       value="<?php echo $metrica['valor'] ?? ''; ?>"
                                                       placeholder="Ingresa el valor">
                                                
                                                <input type="text" 
                                                       class="form-control form-control-sm" 
                                                       name="metricas[<?php echo $metrica['id']; ?>][nota]"
                                                       value="<?php echo htmlspecialchars($metrica['nota'] ?? ''); ?>"
                                                       placeholder="Nota opcional (ej: Incluye 3 proyectos críticos)">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                    <?php endif; ?>
                    
                </div>
                
                <?php if (!empty($metricas)): ?>
                    <div class="card-footer text-end">
                        <button type="submit" name="guardar_metricas" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>
                            Guardar Cambios
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </form>
        
    </div>
</div>

<script>
function cambiarArea(areaId) {
    const periodo = document.getElementById('periodo-selector').value;
    window.location.href = `?area=${areaId}&periodo=${periodo}`;
}

function cambiarPeriodo(periodo) {
    const area = document.getElementById('area-selector').value;
    window.location.href = `?area=${area}&periodo=${periodo}`;
}
</script>

<style>
.metric-form-item {
    background: rgba(30, 41, 59, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 16px;
    transition: all 0.3s;
}

.metric-form-item:hover {
    background: rgba(30, 41, 59, 0.5);
    border-color: rgba(255, 255, 255, 0.2);
}

.metric-icon-small {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
</style>

<?php include '../partials/footer.php'; ?>