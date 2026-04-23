<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Services\PermissionService;
use App\Models\Reporte;
use App\Models\Departamento;
use App\Models\Area;

AuthMiddleware::handle();
PermissionMiddleware::requireAdmin();

$user = getCurrentUser();
$reporteModel = new Reporte();

// Filtros
$departamento_filter = $_GET['departamento'] ?? null;
$area_filter = $_GET['area'] ?? null;
$estado_filter = $_GET['estado'] ?? null;
$search = $_GET['search'] ?? '';

// Obtener reportes según permisos
if ($user['rol'] === 'super_admin') {
    if ($area_filter) {
        $reportes = $reporteModel->getByArea($area_filter, $estado_filter);
    } elseif ($departamento_filter) {
        $reportes = $reporteModel->getByDepartamento($departamento_filter, $estado_filter);
    } else {
        $reportes = $reporteModel->getAllWithDetails();
    }
} elseif ($user['rol'] === 'dept_admin') {
    if ($area_filter) {
        // Verificar que el área pertenece a su departamento
        if (PermissionService::canViewArea($user, $area_filter)) {
            $reportes = $reporteModel->getByArea($area_filter, $estado_filter);
        } else {
            die('No tienes permiso para ver reportes de esta área');
        }
    } else {
        $reportes = $reporteModel->getByDepartamento($user['departamento_id'], $estado_filter);
    }
}

// Búsqueda
if ($search) {
    $reportes = $reporteModel->search($search, $area_filter);
}

// Obtener departamentos y áreas para filtros
$departamentos = PermissionService::getDepartamentosPermitidos($user);

$pageTitle = 'Gestión de Reportes';
require_once __DIR__ . '/../../views/layouts/header.php';
?>

<div class="page-wrapper">
    <div class="page-body">
        <div class="container-xl">

            <!-- Header -->
            <div class="page-header mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h2 class="page-title">
                            <i class="ti ti-file-text me-2"></i>
                            Reportes Escritos
                        </h2>
                        <div class="text-muted mt-1">Gestiona tus reportes narrativos con gráficas</div>
                    </div>
                    <div class="col-auto ms-auto">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-nuevo-reporte">
                            <i class="ti ti-plus me-1"></i>
                            Crear Nuevo Reporte
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <!-- Búsqueda -->
                        <div class="col-md-4">
                            <label class="form-label">Buscar</label>
                            <input type="text" name="search" class="form-control"
                                   placeholder="Título o contenido..."
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>

                        <!-- Departamento (Solo Super Admin) -->
                        <?php if ($user['rol'] === 'super_admin'): ?>
                        <div class="col-md-3">
                            <label class="form-label">Departamento</label>
                            <select name="departamento" class="form-select" id="filtro-departamento">
                                <option value="">Todos</option>
                                <?php foreach ($departamentos as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>"
                                            <?php echo $departamento_filter == $dept['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dept['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <!-- Área -->
                        <div class="col-md-3">
                            <label class="form-label">Área</label>
                            <select name="area" class="form-select" id="filtro-area">
                                <option value="">Todas</option>
                                <?php
                                $areas = PermissionService::getAreasPermitidas($user, $departamento_filter);
                                foreach ($areas as $area):
                                ?>
                                    <option value="<?php echo $area['id']; ?>"
                                            <?php echo $area_filter == $area['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($area['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Estado -->
                        <div class="col-md-2">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="">Todos</option>
                                <option value="borrador" <?php echo $estado_filter === 'borrador' ? 'selected' : ''; ?>>Borrador</option>
                                <option value="revision" <?php echo $estado_filter === 'revision' ? 'selected' : ''; ?>>En Revisión</option>
                                <option value="publicado" <?php echo $estado_filter === 'publicado' ? 'selected' : ''; ?>>Publicado</option>
                                <option value="archivado" <?php echo $estado_filter === 'archivado' ? 'selected' : ''; ?>>Archivado</option>
                            </select>
                        </div>

                        <!-- Botones -->
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-filter me-1"></i> Filtrar
                            </button>
                            <a href="reportes.php" class="btn btn-outline-secondary">
                                <i class="ti ti-x me-1"></i> Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de reportes -->
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-striped">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Área</th>
                                <th>Período/Año</th>
                                <th>Estado</th>
                                <th>Autor</th>
                                <th>Última Modificación</th>
                                <th class="w-1">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reportes)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="ti ti-file-text" style="font-size: 3rem; opacity: 0.3;"></i>
                                        <div class="mt-2">No hay reportes disponibles</div>
                                        <div class="small mt-1">Usa el botón "Crear Nuevo Reporte" arriba para empezar</div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reportes as $reporte): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="avatar avatar-sm me-2" style="background-color: <?php echo $reporte['area_color'] ?? '#3b82f6'; ?>">
                                                <i class="ti ti-<?php echo $reporte['area_icono'] ?? 'file-text'; ?>"></i>
                                            </span>
                                            <div>
                                                <strong><?php echo htmlspecialchars($reporte['titulo']); ?></strong>
                                                <?php if ($reporte['descripcion']): ?>
                                                    <div class="small text-muted">
                                                        <?php echo htmlspecialchars(substr($reporte['descripcion'], 0, 60)); ?>
                                                        <?php echo strlen($reporte['descripcion']) > 60 ? '...' : ''; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-azure-lt">
                                            <?php echo htmlspecialchars($reporte['area_nombre']); ?>
                                        </span>
                                        <div class="small text-muted">
                                            <?php echo htmlspecialchars($reporte['departamento_nombre']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($reporte['periodo_nombre']): ?>
                                            <?php echo htmlspecialchars($reporte['periodo_nombre']); ?>
                                        <?php else: ?>
                                            <span class="badge bg-purple-lt">Anual <?php echo $reporte['anio']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $estadoClass = [
                                            'borrador' => 'bg-gray-lt',
                                            'revision' => 'bg-yellow-lt',
                                            'publicado' => 'bg-green-lt',
                                            'archivado' => 'bg-secondary-lt'
                                        ];
                                        $estadoIcon = [
                                            'borrador' => 'pencil',
                                            'revision' => 'eye-check',
                                            'publicado' => 'check',
                                            'archivado' => 'archive'
                                        ];
                                        ?>
                                        <span class="badge <?php echo $estadoClass[$reporte['estado']] ?? 'bg-secondary-lt'; ?>">
                                            <i class="ti ti-<?php echo $estadoIcon[$reporte['estado']] ?? 'circle'; ?> me-1"></i>
                                            <?php echo ucfirst($reporte['estado']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <?php echo htmlspecialchars($reporte['autor_nombre'] ?? 'N/A'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <?php echo date('d/m/Y H:i', strtotime($reporte['updated_at'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-list">
                                            <!-- Ver Reporte -->
                                            <a href="reportes-view.php?id=<?php echo $reporte['id']; ?>"
                                               class="btn btn-sm btn-cyan" title="Ver Reporte">
                                                <i class="ti ti-eye"></i>
                                            </a>

                                            <!-- Editar -->
                                            <a href="reportes-editor.php?id=<?php echo $reporte['id']; ?>"
                                               class="btn btn-sm btn-primary" title="Editar">
                                                <i class="ti ti-edit"></i>
                                            </a>

                                            <!-- Descargar PDF -->
                                            <a href="reportes-pdf.php?id=<?php echo $reporte['id']; ?>"
                                               class="btn btn-sm btn-danger"
                                               target="_blank"
                                               title="Descargar PDF">
                                                <i class="ti ti-file-type-pdf"></i>
                                            </a>

                                            <!-- Imprimir -->
                                            <a href="reportes-pdf.php?id=<?php echo $reporte['id']; ?>"
                                               class="btn btn-sm btn-secondary"
                                               target="_blank"
                                               title="Imprimir">
                                                <i class="ti ti-printer"></i>
                                            </a>

                                            <!-- Eliminar -->
                                            <?php if (PermissionService::canEditArea($user, $reporte['area_id'])): ?>
                                            <button class="btn btn-sm btn-danger"
                                                    onclick="eliminarReporte(<?php echo $reporte['id']; ?>)"
                                                    title="Eliminar">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal: Nuevo Reporte -->
<div class="modal fade" id="modal-nuevo-reporte" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti ti-file-plus me-2"></i>
                    Crear Nuevo Reporte
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-nuevo-reporte">
                    <p class="text-muted mb-3">Selecciona el departamento y área para el reporte:</p>

                    <?php if ($user['rol'] === 'super_admin'): ?>
                    <!-- Departamento -->
                    <div class="mb-3">
                        <label class="form-label required">Departamento</label>
                        <select class="form-select" id="modal-departamento" name="departamento" required>
                            <option value="">Seleccionar departamento...</option>
                            <?php foreach ($departamentos as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>">
                                    <?php echo htmlspecialchars($dept['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <!-- Área -->
                    <div class="mb-3">
                        <label class="form-label required">Área</label>
                        <select class="form-select" id="modal-area" name="area" required>
                            <option value="">Seleccionar área...</option>
                            <?php
                            // Para dept_admin, cargar sus áreas directamente
                            if ($user['rol'] === 'dept_admin'):
                                $areas = PermissionService::getAreasPermitidas($user, $user['departamento_id']);
                                foreach ($areas as $area):
                            ?>
                                <option value="<?php echo $area['id']; ?>">
                                    <?php echo htmlspecialchars($area['nombre']); ?>
                                </option>
                            <?php
                                endforeach;
                            endif;
                            ?>
                        </select>
                    </div>

                    <!-- Tipo de Reporte -->
                    <div class="mb-3">
                        <label class="form-label required">Tipo de Reporte</label>
                        <select class="form-select" id="modal-tipo-reporte" name="tipo_reporte" required>
                            <option value="mensual">Mensual</option>
                            <option value="trimestral">Trimestral</option>
                            <option value="semestral">Semestral</option>
                            <option value="anual">Anual</option>
                        </select>
                    </div>

                    <!-- Período (solo para mensual/trimestral/semestral) -->
                    <div class="mb-3" id="campo-periodo">
                        <label class="form-label required">Período</label>
                        <select class="form-select" id="modal-periodo" name="periodo_id">
                            <option value="">Seleccionar período...</option>
                            <?php
                            $periodoModel = new App\Models\Periodo();
                            $periodos = $periodoModel->getAll();
                            foreach ($periodos as $periodo):
                            ?>
                                <option value="<?php echo $periodo['id']; ?>">
                                    <?php echo htmlspecialchars($periodo['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Año -->
                    <div class="mb-3">
                        <label class="form-label required">Año</label>
                        <input type="number" class="form-control" id="modal-anio" name="anio"
                               value="<?php echo date('Y'); ?>" min="2000" max="2100" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-continuar-reporte">
                    <i class="ti ti-arrow-right me-1"></i>
                    Continuar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Eliminar reporte
function eliminarReporte(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este reporte? Esta acción no se puede deshacer.')) {
        window.location.href = 'reportes-delete.php?id=' + id;
    }
}

// Actualizar áreas al cambiar departamento
<?php if ($user['rol'] === 'super_admin'): ?>
document.getElementById('filtro-departamento')?.addEventListener('change', function() {
    document.querySelector('form').submit();
});

// En el modal: cargar áreas al seleccionar departamento
document.getElementById('modal-departamento')?.addEventListener('change', function() {
    const departamentoId = this.value;
    const areaSelect = document.getElementById('modal-area');

    if (!departamentoId) {
        areaSelect.innerHTML = '<option value="">Seleccionar área...</option>';
        return;
    }

    // Hacer petición AJAX para obtener áreas del departamento
    fetch(`../api/get-areas-by-departamento.php?departamento_id=${departamentoId}`)
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
            console.error('Error al cargar áreas:', error);
            alert('Error al cargar las áreas');
        });
});
<?php endif; ?>

// Mostrar/ocultar campo de período según tipo de reporte
document.getElementById('modal-tipo-reporte')?.addEventListener('change', function() {
    const tipo = this.value;
    const campoPeriodo = document.getElementById('campo-periodo');
    const selectPeriodo = document.getElementById('modal-periodo');

    if (tipo === 'anual') {
        campoPeriodo.style.display = 'none';
        selectPeriodo.required = false;
        selectPeriodo.value = '';
    } else {
        campoPeriodo.style.display = 'block';
        selectPeriodo.required = true;
    }
});

// Continuar al editor
document.getElementById('btn-continuar-reporte')?.addEventListener('click', function() {
    const areaId = document.getElementById('modal-area').value;
    const tipoReporte = document.getElementById('modal-tipo-reporte').value;
    const periodoId = document.getElementById('modal-periodo').value;
    const anio = document.getElementById('modal-anio').value;

    if (!areaId) {
        alert('Por favor selecciona un área');
        return;
    }

    if (tipoReporte !== 'anual' && !periodoId) {
        alert('Por favor selecciona un período');
        return;
    }

    if (!anio) {
        alert('Por favor ingresa un año');
        return;
    }

    // Redirigir al editor con todos los parámetros
    let url = `reportes-editor.php?area=${areaId}&tipo_reporte=${tipoReporte}&anio=${anio}`;
    if (periodoId) {
        url += `&periodo_id=${periodoId}`;
    }

    window.location.href = url;
});
</script>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
