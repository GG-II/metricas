<?php
session_start();
require_once __DIR__ . '/../../config.php';
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
$estado_filter = $_GET['estado'] ?? null;
$search = $_GET['search'] ?? '';

// Obtener reportes según permisos
if ($user['rol'] === 'super_admin') {
    if ($departamento_filter) {
        $reportes = $reporteModel->getByDepartamento($departamento_filter, $estado_filter);
    } else {
        $reportes = $reporteModel->getAllWithDetails();
    }
} elseif ($user['rol'] === 'dept_admin' || $user['rol'] === 'dept_viewer') {
    $reportes = $reporteModel->getByDepartamento($user['departamento_id'], $estado_filter);
} elseif ($user['rol'] === 'area_admin') {
    // area_admin puede ver reportes de su departamento
    $reportes = $reporteModel->getByDepartamento($user['departamento_id'], $estado_filter);
}

// Búsqueda
if ($search) {
    $reportes = $reporteModel->search($search, $departamento_filter);
}

// Obtener departamentos y áreas para filtros
$departamentos = PermissionService::getDepartamentosPermitidos($user);

$pageTitle = 'Gestión de Reportes';
require_once __DIR__ . '/../../views/layouts/header.php';
?>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* Estilos para tabla en modo oscuro */
[data-bs-theme="dark"] .table-striped > tbody > tr:nth-of-type(odd) > * {
    --tblr-table-accent-bg: rgba(255, 255, 255, 0.05) !important;
    color: var(--tblr-body-color);
}

[data-bs-theme="dark"] .table-striped > tbody > tr:nth-of-type(even) > * {
    background-color: transparent !important;
}

[data-bs-theme="dark"] .table {
    --tblr-table-bg: transparent;
    --tblr-table-striped-bg: rgba(255, 255, 255, 0.05);
    color: var(--tblr-body-color);
}
</style>

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
                                <li class="breadcrumb-item active">Reportes</li>
                            </ol>
                        </nav>
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
                        <div class="col-md-4">
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
                                <th>Departamento</th>
                                <th>Mes/Año</th>
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
                                            <span class="avatar avatar-sm me-2" style="background-color: <?php echo $reporte['departamento_color'] ?? '#3b82f6'; ?>">
                                                <i class="ti ti-<?php echo $reporte['departamento_icono'] ?? 'building'; ?>"></i>
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
                                            <?php echo htmlspecialchars($reporte['departamento_nombre']); ?>
                                        </span>
                                        <?php if (isset($reporte['departamento_tipo'])): ?>
                                            <div class="small text-muted text-capitalize">
                                                <?php echo htmlspecialchars($reporte['departamento_tipo']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                                                 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                                        ?>
                                        <span class="badge bg-purple-lt">
                                            <?php echo $meses[$reporte['mes']] ?? 'Mes ' . $reporte['mes']; ?> <?php echo $reporte['anio']; ?>
                                        </span>
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
                                            <?php if ($user['rol'] === 'super_admin' ||
                                                     ($user['rol'] === 'dept_admin' && $user['departamento_id'] == $reporte['departamento_id'])): ?>
                                            <button class="btn btn-sm btn-danger"
                                                    onclick="eliminarReporte(<?php echo $reporte['id']; ?>, '<?php echo addslashes(htmlspecialchars($reporte['titulo'])); ?>')"
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
                    <p class="text-muted mb-3">
                        <i class="ti ti-info-circle me-1"></i>
                        Crea un reporte consolidado que incluye automáticamente todas las áreas del departamento.
                    </p>

                    <?php if ($user['rol'] === 'super_admin'): ?>
                    <!-- Departamento -->
                    <div class="mb-3">
                        <label class="form-label required">Departamento</label>
                        <select class="form-select" id="modal-departamento" name="departamento_id" required>
                            <option value="">Seleccionar departamento...</option>
                            <?php foreach ($departamentos as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>">
                                    <?php echo htmlspecialchars($dept['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-hint">El reporte incluirá todas las áreas de este departamento</div>
                    </div>
                    <?php else: ?>
                    <input type="hidden" id="modal-departamento" name="departamento_id" value="<?php echo $user['departamento_id']; ?>">
                    <div class="alert alert-info mb-3">
                        <i class="ti ti-info-circle me-2"></i>
                        Creando reporte para: <strong><?php echo htmlspecialchars($user['departamento_nombre'] ?? 'tu departamento'); ?></strong>
                    </div>
                    <?php endif; ?>

                    <!-- Mes -->
                    <div class="mb-3">
                        <label class="form-label required">Mes</label>
                        <select class="form-select" id="modal-mes" name="mes" required>
                            <option value="">Seleccionar mes...</option>
                            <?php
                            $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                                     'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                            foreach ($meses as $num => $nombre):
                                $mesNum = $num + 1;
                            ?>
                                <option value="<?php echo $mesNum; ?>" <?php echo (int)date('n') == $mesNum ? 'selected' : ''; ?>>
                                    <?php echo $nombre; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Año -->
                    <div class="mb-3">
                        <label class="form-label required">Año</label>
                        <input type="number" class="form-control" id="modal-anio" name="anio"
                               value="<?php echo date('Y'); ?>" min="2020" max="2100" required>
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
// Mostrar mensajes de éxito/error con SweetAlert2
<?php if (isset($_SESSION['success_message'])): ?>
Swal.fire({
    icon: 'success',
    title: '¡Éxito!',
    text: '<?php echo addslashes($_SESSION['success_message']); ?>',
    showConfirmButton: true,
    confirmButtonText: 'Entendido',
    confirmButtonColor: '#16a34a',
    timer: 3000,
    timerProgressBar: true
});
<?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
Swal.fire({
    icon: 'error',
    title: 'Error',
    text: '<?php echo addslashes($_SESSION['error_message']); ?>',
    showConfirmButton: true,
    confirmButtonText: 'Cerrar',
    confirmButtonColor: '#dc2626'
});
<?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

// Eliminar reporte con SweetAlert2
function eliminarReporte(id, titulo) {
    Swal.fire({
        title: '¿Eliminar este reporte?',
        html: `
            <div style="text-align: left; padding: 10px;">
                <p style="margin-bottom: 15px;"><strong>📄 Reporte:</strong></p>
                <p style="background: #f3f4f6; padding: 10px; border-radius: 4px; margin-bottom: 15px;">${titulo}</p>
                <p style="color: #dc2626; margin-bottom: 5px;"><strong>⚠️ Advertencia:</strong></p>
                <ul style="color: #666; font-size: 14px; margin-left: 20px;">
                    <li>Esta acción no se puede deshacer</li>
                    <li>Se perderán todos los datos del reporte</li>
                    <li>No se podrá recuperar la información</li>
                </ul>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="ti ti-trash me-1"></i> Sí, eliminar',
        cancelButtonText: '<i class="ti ti-x me-1"></i> Cancelar',
        reverseButtons: true,
        focusCancel: true,
        customClass: {
            confirmButton: 'btn btn-danger',
            cancelButton: 'btn btn-secondary'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Eliminando...',
                text: 'Por favor espera',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            // Redirigir a eliminar
            window.location.href = 'reportes-delete.php?id=' + id;
        }
    });
}

// Actualizar lista al cambiar departamento en filtro
<?php if ($user['rol'] === 'super_admin'): ?>
document.getElementById('filtro-departamento')?.addEventListener('change', function() {
    document.querySelector('form').submit();
});
<?php endif; ?>

// Continuar al editor
document.getElementById('btn-continuar-reporte')?.addEventListener('click', function() {
    const departamentoId = document.getElementById('modal-departamento').value;
    const mes = document.getElementById('modal-mes').value;
    const anio = document.getElementById('modal-anio').value;

    if (!departamentoId) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Por favor selecciona un departamento',
            confirmButtonColor: '#3b82f6'
        });
        return;
    }

    if (!mes) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Por favor selecciona un mes',
            confirmButtonColor: '#3b82f6'
        });
        return;
    }

    if (!anio) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Por favor ingresa un año',
            confirmButtonColor: '#3b82f6'
        });
        return;
    }

    // Redirigir al editor con los parámetros
    window.location.href = `reportes-editor.php?departamento_id=${departamentoId}&mes=${mes}&anio=${anio}`;
});
</script>

<!-- Bootstrap 5 JS (incluye Dropdown) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Tabler JS -->
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/js/tabler.min.js"></script>
<script src="<?php echo baseUrl('/assets/js/theme-toggle.js'); ?>"></script>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
