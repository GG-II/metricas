<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Services\PermissionService;
use App\Models\Reporte;
use App\Models\Departamento;

AuthMiddleware::handle();

$user = getCurrentUser();
$reporteModel = new Reporte();

// Modo: crear o editar
$reporte_id = $_GET['id'] ?? null;
$modo = $reporte_id ? 'editar' : 'crear';

$reporte = null;
if ($modo === 'editar') {
    $reporte = $reporteModel->findWithFullDetails($reporte_id);

    if (!$reporte) {
        die('Reporte no encontrado');
    }

    // Verificar permisos
    if ($user['rol'] !== 'super_admin' &&
        !($user['rol'] === 'dept_admin' && $user['departamento_id'] == $reporte['departamento_id'])) {
        die('No tienes permiso para editar este reporte');
    }
} else {
    // Modo crear: obtener parámetros
    $departamento_id = $_GET['departamento_id'] ?? $user['departamento_id'] ?? null;
    $mes = $_GET['mes'] ?? date('n');
    $anio = $_GET['anio'] ?? date('Y');

    if (!$departamento_id) {
        die('Departamento no especificado');
    }

    // Verificar si ya existe reporte para este departamento/mes/año
    if ($reporteModel->existeReporte($departamento_id, $mes, $anio)) {
        die('Ya existe un reporte para este departamento, mes y año. <a href="reportes.php">Volver</a>');
    }

    // Cargar datos del departamento y áreas
    $departamentoModel = new Departamento();
    $departamento = $departamentoModel->find($departamento_id);

    $reporte = [
        'departamento_id' => $departamento_id,
        'departamento_nombre' => $departamento['nombre'],
        'departamento_color' => $departamento['color'],
        'departamento_icono' => $departamento['icono'],
        'mes' => $mes,
        'anio' => $anio,
        'titulo' => '',
        'descripcion' => '',
        'resumen_ejecutivo' => '',
        'estado' => 'borrador',
        'areas' => []
    ];

    // Obtener áreas con gráficos
    $reporte['areas'] = $reporteModel->getAreasConGraficos($departamento_id);
}

$meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
         'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

$pageTitle = ($modo === 'crear' ? 'Crear Reporte Consolidado' : 'Editar Reporte');
require_once __DIR__ . '/../../views/layouts/header.php';
?>

<style>
.editor-container {
    max-width: 1400px;
    margin: 0 auto;
}

.preview-section {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 2rem;
    min-height: 400px;
}

[data-bs-theme="dark"] .preview-section {
    background: #1e293b;
    border-color: #334155;
}

.area-section {
    margin-bottom: 3rem;
    padding: 1.5rem;
    background: #f8fafc;
    border-radius: 8px;
    border-left: 4px solid #3b82f6;
}

[data-bs-theme="dark"] .area-section {
    background: #0f172a;
}

.grafico-preview {
    margin: 1.5rem 0;
    padding: 1rem;
    background: white;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
}

[data-bs-theme="dark"] .grafico-preview {
    background: #1e293b;
    border-color: #334155;
}
</style>

<div class="page-wrapper">
    <div class="page-body">
        <div class="container-xl editor-container">

            <!-- Header -->
            <div class="page-header mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo baseUrl('/admin/index.php'); ?>">Administración</a></li>
                                <li class="breadcrumb-item"><a href="<?php echo baseUrl('/admin/reportes.php'); ?>">Reportes</a></li>
                                <li class="breadcrumb-item active"><?php echo $modo === 'crear' ? 'Crear' : 'Editar'; ?></li>
                            </ol>
                        </nav>
                        <h2 class="page-title">
                            <i class="ti ti-file-text me-2"></i>
                            <?php echo $pageTitle; ?>
                        </h2>
                        <div class="text-muted mt-1">
                            <?php echo htmlspecialchars($reporte['departamento_nombre']); ?> -
                            <?php echo $meses[$reporte['mes']]; ?> <?php echo $reporte['anio']; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <a href="reportes.php" class="btn btn-secondary">
                            <i class="ti ti-arrow-left me-1"></i>
                            Volver
                        </a>
                    </div>
                </div>
            </div>

            <form id="form-reporte" method="POST" action="reportes-save.php">
                <input type="hidden" name="id" value="<?php echo $reporte['id'] ?? ''; ?>">
                <input type="hidden" name="departamento_id" value="<?php echo $reporte['departamento_id']; ?>">
                <input type="hidden" name="mes" value="<?php echo $reporte['mes']; ?>">
                <input type="hidden" name="anio" value="<?php echo $reporte['anio']; ?>">
                <input type="hidden" name="modo" value="<?php echo $modo; ?>">

                <div class="row">
                    <!-- Panel Izquierdo: Edición -->
                    <div class="col-lg-5">
                        <div class="card sticky-top" style="top: 1rem;">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="ti ti-edit me-2"></i>
                                    Información del Reporte
                                </h3>
                            </div>
                            <div class="card-body">

                                <!-- Título -->
                                <div class="mb-3">
                                    <label class="form-label required">Título del Reporte</label>
                                    <input type="text" class="form-control" name="titulo"
                                           value="<?php echo htmlspecialchars($reporte['titulo'] ?? ''); ?>"
                                           placeholder="Ej: Reporte Mensual de Operaciones" required>
                                </div>

                                <!-- Descripción Breve -->
                                <div class="mb-3">
                                    <label class="form-label">Descripción Breve</label>
                                    <textarea class="form-control" name="descripcion" rows="2"
                                              placeholder="Resumen en una línea..."><?php echo htmlspecialchars($reporte['descripcion'] ?? ''); ?></textarea>
                                </div>

                                <!-- Resumen Ejecutivo -->
                                <div class="mb-3">
                                    <label class="form-label">Resumen Ejecutivo</label>
                                    <textarea class="form-control" name="resumen_ejecutivo" rows="12"
                                              placeholder="Escribe aquí el resumen ejecutivo del reporte... (máximo 2-3 párrafos)"><?php echo htmlspecialchars($reporte['resumen_ejecutivo'] ?? ''); ?></textarea>
                                    <div class="form-hint">
                                        <i class="ti ti-info-circle me-1"></i>
                                        Este es el único contenido editable. Las secciones de áreas y gráficos se generan automáticamente.
                                    </div>
                                </div>

                                <!-- Estado -->
                                <div class="mb-3">
                                    <label class="form-label required">Estado</label>
                                    <select class="form-select" name="estado" required>
                                        <option value="borrador" <?php echo ($reporte['estado'] ?? 'borrador') === 'borrador' ? 'selected' : ''; ?>>Borrador</option>
                                        <option value="revision" <?php echo ($reporte['estado'] ?? '') === 'revision' ? 'selected' : ''; ?>>En Revisión</option>
                                        <option value="publicado" <?php echo ($reporte['estado'] ?? '') === 'publicado' ? 'selected' : ''; ?>>Publicado</option>
                                    </select>
                                </div>

                                <!-- Botones -->
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-device-floppy me-1"></i>
                                        Guardar Reporte
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="window.location.href='reportes.php'">
                                        Cancelar
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Panel Derecho: Previsualización -->
                    <div class="col-lg-7">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="ti ti-eye me-2"></i>
                                    Previsualización del Reporte
                                </h3>
                            </div>
                            <div class="card-body preview-section">

                                <h3 class="mb-4"><?php echo htmlspecialchars($reporte['departamento_nombre']); ?></h3>
                                <h4 class="text-muted mb-4">
                                    Reporte <?php echo $meses[$reporte['mes']]; ?> <?php echo $reporte['anio']; ?>
                                </h4>

                                <hr class="my-4">

                                <h5 class="mb-3">Resumen Ejecutivo</h5>
                                <div id="preview-resumen" class="text-muted mb-5" style="white-space: pre-wrap;">
                                    <?php echo htmlspecialchars($reporte['resumen_ejecutivo'] ?? 'El resumen ejecutivo aparecerá aquí...'); ?>
                                </div>

                                <hr class="my-4">

                                <h5 class="mb-4">Áreas Incluidas (<?php echo count($reporte['areas'] ?? []); ?>)</h5>

                                <?php if (empty($reporte['areas'])): ?>
                                    <div class="alert alert-info">
                                        <i class="ti ti-info-circle me-2"></i>
                                        No hay áreas configuradas para este departamento
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($reporte['areas'] as $area): ?>
                                    <div class="area-section">
                                        <h6 class="mb-3">
                                            <span class="avatar avatar-xs me-2" style="background-color: <?php echo $area['color'] ?? '#3b82f6'; ?>">
                                                <i class="ti ti-<?php echo $area['icono'] ?? 'folder'; ?>"></i>
                                            </span>
                                            <?php echo htmlspecialchars($area['nombre']); ?>
                                        </h6>

                                        <?php if (!empty($area['descripcion'])): ?>
                                        <p class="text-muted small mb-3"><?php echo htmlspecialchars($area['descripcion']); ?></p>
                                        <?php endif; ?>

                                        <div class="small text-muted">
                                            <i class="ti ti-chart-bar me-1"></i>
                                            <?php echo count($area['graficos']); ?> gráfico(s) configurado(s)
                                        </div>

                                        <?php if (!empty($area['graficos'])): ?>
                                        <div class="mt-3">
                                            <?php foreach ($area['graficos'] as $grafico): ?>
                                            <div class="grafico-preview">
                                                <div class="d-flex align-items-center">
                                                    <i class="ti ti-chart-line me-2 text-primary"></i>
                                                    <strong><?php echo htmlspecialchars($grafico['titulo']); ?></strong>
                                                    <span class="badge bg-secondary-lt ms-auto"><?php echo htmlspecialchars($grafico['tipo']); ?></span>
                                                </div>
                                                <div class="small text-muted mt-1">
                                                    Se generará automáticamente al visualizar o exportar el reporte
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
// Preview en tiempo real del resumen ejecutivo
document.querySelector('[name="resumen_ejecutivo"]')?.addEventListener('input', function() {
    const preview = document.getElementById('preview-resumen');
    preview.textContent = this.value || 'El resumen ejecutivo aparecerá aquí...';
});

// Manejar envío del formulario
document.getElementById('form-reporte')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const btn = this.querySelector('button[type="submit"]');
    const btnText = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...';

    fetch('reportes-save.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar mensaje de éxito
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show';
            alert.innerHTML = `
                <i class="ti ti-check me-2"></i>
                ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.page-body .container-xl').prepend(alert);

            // Redirigir después de 1 segundo
            setTimeout(() => {
                window.location.href = data.redirect || 'reportes.php';
            }, 1000);
        } else {
            throw new Error(data.error || 'Error al guardar');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al guardar el reporte: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = btnText;
    });
});
</script>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
