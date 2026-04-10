<?php
/**
 * Configurar métricas - CRUD completo
 * Versión mejorada con mejor UI
 */

require_once '../config.php';
requireAdmin();

require_once '../models/Area.php';
require_once '../models/Metrica.php';

$areaModel = new Area();
$metricaModel = new Metrica();

$area_id = isset($_GET['area']) ? (int)$_GET['area'] : AREA_SOFTWARE;
$area = $areaModel->getById($area_id);
if (!$area) redirect('/index.php');

$areas = $areaModel->getActivas();
$mensaje = '';
$mensaje_tipo = '';

// CREAR métrica
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_metrica'])) {
    try {
        $data = [
            'area_id' => $area_id,
            'nombre' => sanitize($_POST['nombre']),
            'slug' => sanitize($_POST['slug']),
            'icono' => sanitize($_POST['icono']),
            'tipo_valor' => $_POST['tipo_valor'],
            'unidad' => sanitize($_POST['unidad']) ?: null,
            'descripcion' => sanitize($_POST['descripcion']) ?: null,
            'tipo_grafico' => $_POST['tipo_grafico'],
            'orden' => (int)$_POST['orden'],
            'grupo' => sanitize($_POST['grupo']) ?: null,
            'activo' => 1
        ];
        
        $id = $metricaModel->create($data);
        logActivity('crear_metrica', 'metricas', $id, "Creada métrica: {$data['nombre']}");
        
        $mensaje = 'Métrica creada exitosamente.';
        $mensaje_tipo = 'success';
    } catch (Exception $e) {
        $mensaje = 'Error al crear métrica: ' . $e->getMessage();
        $mensaje_tipo = 'danger';
    }
}

// EDITAR métrica
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_metrica'])) {
    try {
        $id = (int)$_POST['metrica_id'];
        
        $data = [
            'nombre' => sanitize($_POST['nombre']),
            'slug' => sanitize($_POST['slug']),
            'icono' => sanitize($_POST['icono']),
            'tipo_valor' => $_POST['tipo_valor'],
            'unidad' => sanitize($_POST['unidad']) ?: null,
            'descripcion' => sanitize($_POST['descripcion']) ?: null,
            'tipo_grafico' => $_POST['tipo_grafico'],
            'orden' => (int)$_POST['orden'],
            'grupo' => sanitize($_POST['grupo']) ?: null
        ];
        
        $metricaModel->update($id, $data);
        logActivity('editar_metrica', 'metricas', $id, "Editada métrica: {$data['nombre']}");
        
        $mensaje = 'Métrica actualizada exitosamente.';
        $mensaje_tipo = 'success';
    } catch (Exception $e) {
        $mensaje = 'Error al actualizar métrica: ' . $e->getMessage();
        $mensaje_tipo = 'danger';
    }
}

// ELIMINAR métrica
if (isset($_GET['eliminar'])) {
    try {
        $id = (int)$_GET['eliminar'];
        $metrica = $metricaModel->getById($id);
        
        if ($metrica) {
            $metricaModel->delete($id);
            logActivity('eliminar_metrica', 'metricas', $id, "Eliminada métrica: {$metrica['nombre']}");
            $mensaje = 'Métrica eliminada exitosamente.';
            $mensaje_tipo = 'success';
        }
    } catch (Exception $e) {
        $mensaje = 'Error al eliminar métrica: ' . $e->getMessage();
        $mensaje_tipo = 'danger';
    }
}

$metricas = $metricaModel->getByArea($area_id, false);

$page_title = 'Configurar Métricas - ' . $area['nombre'];
$is_admin = true;

// Catálogo de íconos
$iconos_disponibles = [
    'chart-bar', 'chart-line', 'chart-area', 'chart-pie', 'chart-donut',
    'code', 'bug', 'git-branch', 'git-commit', 'rocket', 'terminal',
    'folder', 'folders', 'briefcase', 'checkbox', 'checklist',
    'check', 'circle-check', 'square-check', 'x', 'alert-triangle', 'alert-circle',
    'server', 'database', 'cloud', 'cpu', 'device-desktop', 'devices',
    'shield', 'shield-check', 'shield-lock', 'shield-x', 'lock', 'lock-open',
    'headset', 'inbox', 'mail', 'message', 'phone', 'ticket',
    'clock', 'clock-hour-4', 'calendar', 'hourglass',
    'user', 'users', 'user-check', 'user-plus',
    'tool', 'settings', 'adjustments', 'wrench',
    'activity', 'heartbeat', 'pulse', 'trending-up', 'trending-down',
    'star', 'flame', 'robot', 'packages', 'plug'
];
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
                        <i class="ti ti-adjustments me-2" style="color: <?php echo $area['color']; ?>"></i>
                        Configurar Métricas
                    </h2>
                    <div class="text-muted mt-1"><?php echo $area['nombre']; ?></div>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modal-crear">
                        <i class="ti ti-plus me-2"></i>
                        Nueva Métrica
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
        
        <!-- Tabla de métricas mejorada -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-list me-2"></i>
                    Métricas de <?php echo $area['nombre']; ?>
                </h3>
                <div class="card-actions">
                    <span class="badge bg-primary"><?php echo count($metricas); ?> métricas</span>
                </div>
            </div>
            
            <?php if (empty($metricas)): ?>
                <div class="card-body">
                    <div class="empty">
                        <div class="empty-icon"><i class="ti ti-database-off"></i></div>
                        <p class="empty-title">No hay métricas configuradas</p>
                        <p class="empty-subtitle">Crea la primera métrica para esta área.</p>
                        <div class="empty-action">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-crear">
                                <i class="ti ti-plus me-1"></i>
                                Crear Primera Métrica
                            </button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-hover">
                        <thead>
                            <tr>
                                <th class="w-1">Orden</th>
                                <th class="w-1">Ícono</th>
                                <th>Nombre</th>
                                <th>Slug</th>
                                <th>Tipo</th>
                                <th>Unidad</th>
                                <th>Gráfico</th>
                                <th class="w-1">Estado</th>
                                <th class="w-1 text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($metricas as $m): ?>
                                <tr>
                                    <td><span class="badge bg-secondary"><?php echo $m['orden']; ?></span></td>
                                    <td>
                                        <div class="avatar avatar-sm" style="background-color: <?php echo $area['color']; ?>20;">
                                            <i class="ti ti-<?php echo $m['icono']; ?>" style="color: <?php echo $area['color']; ?>"></i>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($m['nombre']); ?></div>
                                        <?php if ($m['descripcion']): ?>
                                            <div class="text-muted small"><?php echo htmlspecialchars(substr($m['descripcion'], 0, 50)); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><code class="small"><?php echo $m['slug']; ?></code></td>
                                    <td><span class="badge bg-azure-lt"><?php echo $m['tipo_valor']; ?></span></td>
                                    <td><?php echo $m['unidad'] ?: '<span class="text-muted">-</span>'; ?></td>
                                    <td><span class="badge bg-indigo-lt"><?php echo $m['tipo_grafico']; ?></span></td>
                                    <td>
                                        <?php if ($m['activo']): ?>
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
                                                    onclick='editarMetrica(<?php echo json_encode($m, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'
                                                    title="Editar">
                                                <i class="ti ti-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" 
                                                    onclick="confirmarEliminar(<?php echo $m['id']; ?>, '<?php echo addslashes($m['nombre']); ?>')"
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

<!-- Modal: Crear Métrica (MEJORADO) -->
<div class="modal modal-blur fade" id="modal-crear" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" id="form-crear">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-plus me-2"></i>
                        Nueva Métrica
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php $form_mode = 'crear'; include 'partials/metrica-form.php'; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">
                        <i class="ti ti-x me-1"></i>
                        Cancelar
                    </button>
                    <button type="submit" name="crear_metrica" class="btn btn-primary">
                        <i class="ti ti-check me-1"></i>
                        Crear Métrica
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Editar Métrica (MEJORADO) -->
<div class="modal modal-blur fade" id="modal-editar" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" id="form-editar">
                <input type="hidden" name="metrica_id" id="edit-metrica-id">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-edit me-2"></i>
                        Editar Métrica
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php $form_mode = 'editar'; include 'partials/metrica-form.php'; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">
                        <i class="ti ti-x me-1"></i>
                        Cancelar
                    </button>
                    <button type="submit" name="editar_metrica" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editarMetrica(metrica) {
    const form = document.getElementById('form-editar');
    
    document.getElementById('edit-metrica-id').value = metrica.id;
    
    const campos = {
        'nombre': metrica.nombre,
        'slug': metrica.slug,
        'icono': metrica.icono,
        'tipo_valor': metrica.tipo_valor,
        'unidad': metrica.unidad || '',
        'descripcion': metrica.descripcion || '',
        'tipo_grafico': metrica.tipo_grafico,
        'orden': metrica.orden,
        'grupo': metrica.grupo || ''
    };
    
    for (const [campo, valor] of Object.entries(campos)) {
        const input = form.querySelector(`[name="${campo}"]`);
        if (input) input.value = valor;
    }
    
    // Actualizar preview del ícono
    const preview = form.querySelector('.icon-preview-large i');
    if (preview) preview.className = `ti ti-${metrica.icono}`;
    
    // Marcar ícono seleccionado
    const iconOptions = form.querySelectorAll('.icon-option');
    iconOptions.forEach(el => {
        el.classList.toggle('selected', el.getAttribute('data-icon') === metrica.icono);
    });
    
    // Mostrar modal manipulando clases directamente
    const modalElement = document.getElementById('modal-editar');
    modalElement.classList.add('show');
    modalElement.style.display = 'block';
    modalElement.setAttribute('aria-modal', 'true');
    modalElement.removeAttribute('aria-hidden');
    
    // Agregar backdrop
    const backdrop = document.createElement('div');
    backdrop.className = 'modal-backdrop fade show';
    backdrop.id = 'modal-backdrop-temp';
    document.body.appendChild(backdrop);
    document.body.classList.add('modal-open');
    
    // Cerrar modal al hacer click en X o Cancelar
    const closeButtons = modalElement.querySelectorAll('[data-bs-dismiss="modal"]');
    closeButtons.forEach(btn => {
        btn.onclick = function() {
            cerrarModal('modal-editar');
        };
    });
}

function cerrarModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.remove('show');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    modal.removeAttribute('aria-modal');
    
    const backdrop = document.getElementById('modal-backdrop-temp');
    if (backdrop) backdrop.remove();
    
    document.body.classList.remove('modal-open');
}

function confirmarEliminar(id, nombre) {
    if (confirm(`¿Eliminar la métrica "${nombre}"?\n\nEsto eliminará también todos sus valores históricos.`)) {
        window.location.href = `?area=<?php echo $area_id; ?>&eliminar=${id}`;
    }
}
</script>

<style>
.avatar {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.table-hover tbody tr:hover {
    background-color: rgba(255, 255, 255, 0.02);
}

.modal-blur .modal-content {
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-dialog-scrollable .modal-body {
    max-height: calc(100vh - 250px);
}
</style>

<?php include '../partials/footer.php'; ?>