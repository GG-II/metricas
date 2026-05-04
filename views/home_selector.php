<?php
/**
 * Vista: Selector de Departamentos/Áreas organizados por Tipo
 *
 * Muestra pestañas para:
 * - Red de Agencias (tipo='agencia')
 * - Corporativo (tipo='corporativo')
 * - Global (tipo='global', solo super_admin)
 */

$pageTitle = 'Sistema de Métricas - Cooperativa';
require_once __DIR__ . '/layouts/header.php';

// Obtener departamentos por tipo usando los nuevos métodos
$agencias = $deptModel->getAgencias();
$corporativos = $deptModel->getCorporativos();
$global = $deptModel->getGlobal();

// Obtener áreas de cada tipo
$areas_agencias = [];
$areas_corporativos = [];
$areas_global = [];

foreach ($agencias as $agencia) {
    $areas_agencias[$agencia['id']] = $areaModel->getByDepartamento($agencia['id']);
}

foreach ($corporativos as $corp) {
    $areas_corporativos[$corp['id']] = $areaModel->getByDepartamento($corp['id']);
}

if ($global) {
    $areas_global = $areaModel->getByDepartamento($global['id']);
}
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    <i class="ti ti-dashboard me-2"></i>
                    Sistema de Métricas
                </h2>
                <div class="text-muted mt-1">Seleccione el área que desea visualizar</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="d-flex">
                    <a href="admin/" class="btn btn-primary">
                        <i class="ti ti-settings me-2"></i>
                        Administración
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        <!-- Pestañas de Navegación por Tipo -->
        <ul class="nav nav-tabs nav-tabs-lg mb-4" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active d-flex align-items-center"
                        data-bs-toggle="tab"
                        data-bs-target="#tab-agencias"
                        type="button"
                        role="tab">
                    <i class="ti ti-building-bank me-2"></i>
                    Red de Agencias
                    <span class="badge bg-success ms-2"><?= count($agencias) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link d-flex align-items-center"
                        data-bs-toggle="tab"
                        data-bs-target="#tab-corporativo"
                        type="button"
                        role="tab">
                    <i class="ti ti-building me-2"></i>
                    Corporativo
                    <span class="badge bg-blue ms-2"><?= count($corporativos) ?></span>
                </button>
            </li>
            <?php if ($user['rol'] === 'super_admin' && $global): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link d-flex align-items-center"
                        data-bs-toggle="tab"
                        data-bs-target="#tab-global"
                        type="button"
                        role="tab">
                    <i class="ti ti-world me-2"></i>
                    Global
                    <span class="badge bg-purple ms-2">Super Admin</span>
                </button>
            </li>
            <?php endif; ?>
        </ul>

        <!-- Contenido de las Pestañas -->
        <div class="tab-content">

            <!-- TAB: Red de Agencias -->
            <div class="tab-pane fade show active" id="tab-agencias" role="tabpanel">
                <?php if (empty($agencias)): ?>
                    <div class="empty">
                        <div class="empty-icon">
                            <i class="ti ti-building-bank"></i>
                        </div>
                        <p class="empty-title">No hay agencias registradas</p>
                        <p class="empty-subtitle text-muted">
                            Crea departamentos tipo "agencia" para organizaciones distribuidas geográficamente.
                        </p>
                        <div class="empty-action">
                            <a href="admin/departamentos.php" class="btn btn-primary">
                                <i class="ti ti-plus me-2"></i>
                                Crear Primera Agencia
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row row-cards">
                        <?php foreach ($agencias as $agencia): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card card-link-pop hover-shadow-sm">
                                    <div class="card-status-top" style="background-color: <?= e($agencia['color'] ?? '#10b981') ?>;"></div>
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <span class="avatar avatar-lg me-3" style="background-color: <?= e($agencia['color'] ?? '#10b981') ?>;">
                                                <i class="ti ti-<?= e($agencia['icono'] ?? 'building-bank') ?>"></i>
                                            </span>
                                            <div>
                                                <h3 class="card-title mb-0"><?= e($agencia['nombre']) ?></h3>
                                                <small class="text-muted">Agencia</small>
                                            </div>
                                        </div>

                                        <?php if (isset($areas_agencias[$agencia['id']]) && !empty($areas_agencias[$agencia['id']])): ?>
                                            <div class="list-group list-group-flush">
                                                <?php foreach ($areas_agencias[$agencia['id']] as $area): ?>
                                                    <a href="?dept=<?= $agencia['id'] ?>&area=<?= $area['id'] ?>"
                                                       class="list-group-item list-group-item-action d-flex align-items-center">
                                                        <i class="ti ti-<?= e($area['icono'] ?? 'chart-bar') ?> me-2"
                                                           style="color: <?= e($area['color'] ?? '#206bc4') ?>;"></i>
                                                        <span><?= e($area['nombre']) ?></span>
                                                        <i class="ti ti-chevron-right ms-auto"></i>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-muted small">Sin áreas configuradas</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- TAB: Corporativo -->
            <div class="tab-pane fade" id="tab-corporativo" role="tabpanel">
                <?php if (empty($corporativos)): ?>
                    <div class="empty">
                        <div class="empty-icon">
                            <i class="ti ti-building"></i>
                        </div>
                        <p class="empty-title">No hay departamentos corporativos</p>
                        <p class="empty-subtitle text-muted">
                            Los departamentos corporativos son áreas de soporte como TI, Mercadeo, RRHH.
                        </p>
                    </div>
                <?php else: ?>
                    <div class="row row-cards">
                        <?php foreach ($corporativos as $depto): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card card-link-pop hover-shadow-sm">
                                    <div class="card-status-top" style="background-color: <?= e($depto['color'] ?? '#3b82f6') ?>;"></div>
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <span class="avatar avatar-lg me-3" style="background-color: <?= e($depto['color'] ?? '#3b82f6') ?>;">
                                                <i class="ti ti-<?= e($depto['icono'] ?? 'building') ?>"></i>
                                            </span>
                                            <div>
                                                <h3 class="card-title mb-0"><?= e($depto['nombre']) ?></h3>
                                                <small class="text-muted">Departamento</small>
                                            </div>
                                        </div>

                                        <?php if (isset($areas_corporativos[$depto['id']]) && !empty($areas_corporativos[$depto['id']])): ?>
                                            <div class="list-group list-group-flush">
                                                <?php foreach ($areas_corporativos[$depto['id']] as $area): ?>
                                                    <a href="?dept=<?= $depto['id'] ?>&area=<?= $area['id'] ?>"
                                                       class="list-group-item list-group-item-action d-flex align-items-center">
                                                        <i class="ti ti-<?= e($area['icono'] ?? 'chart-bar') ?> me-2"
                                                           style="color: <?= e($area['color'] ?? '#206bc4') ?>;"></i>
                                                        <span><?= e($area['nombre']) ?></span>
                                                        <i class="ti ti-chevron-right ms-auto"></i>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-muted small">Sin áreas configuradas</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- TAB: Global (Solo Super Admin) -->
            <?php if ($user['rol'] === 'super_admin' && $global): ?>
            <div class="tab-pane fade" id="tab-global" role="tabpanel">
                <div class="alert alert-purple d-flex align-items-center mb-4">
                    <i class="ti ti-shield-lock me-3 fs-1"></i>
                    <div>
                        <h4 class="alert-title mb-1">Área Global - Solo Super Administrador</h4>
                        <div class="text-muted">
                            Métricas consolidadas de toda la organización.
                            Permite crear métricas calculadas que suman valores de cualquier departamento o agencia.
                        </div>
                    </div>
                </div>

                <?php if (empty($areas_global)): ?>
                    <div class="empty">
                        <div class="empty-icon">
                            <i class="ti ti-world"></i>
                        </div>
                        <p class="empty-title">No hay áreas globales configuradas</p>
                        <p class="empty-subtitle text-muted">
                            Crea áreas dentro del departamento Global para organizar métricas consolidadas.<br>
                            Ejemplos: Métricas Financieras, Métricas Comerciales, KPIs Estratégicos.
                        </p>
                        <div class="empty-action">
                            <a href="admin/areas.php?dept=<?= $global['id'] ?>" class="btn btn-purple">
                                <i class="ti ti-plus me-2"></i>
                                Crear Área Global
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row row-cards">
                        <?php foreach ($areas_global as $area): ?>
                            <div class="col-md-6 col-lg-4">
                                <a href="?dept=<?= $global['id'] ?>&area=<?= $area['id'] ?>"
                                   class="card card-link-pop hover-shadow-sm text-decoration-none">
                                    <div class="card-status-top bg-purple"></div>
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <span class="avatar avatar-lg me-3 bg-purple">
                                                <i class="ti ti-<?= e($area['icono'] ?? 'chart-dots') ?>"></i>
                                            </span>
                                            <div>
                                                <h3 class="card-title mb-1"><?= e($area['nombre']) ?></h3>
                                                <div class="text-muted small">Métricas Consolidadas</div>
                                            </div>
                                            <i class="ti ti-chevron-right ms-auto text-muted"></i>
                                        </div>
                                        <?php if (isset($area['descripcion']) && $area['descripcion']): ?>
                                            <div class="mt-3 text-muted small">
                                                <?= e($area['descripcion']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<style>
.card-link-pop {
    transition: all 0.2s ease;
    cursor: pointer;
}

.card-link-pop:hover {
    transform: translateY(-2px);
}

.hover-shadow-sm:hover {
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15) !important;
}

.nav-tabs-lg .nav-link {
    padding: 0.75rem 1.25rem;
    font-size: 1rem;
}

.list-group-item-action:hover {
    background-color: rgba(32, 107, 196, 0.05);
}

/* Estilo para alert purple */
.alert-purple {
    color: #5b21b6;
    background-color: #f3e8ff;
    border-color: #ddd6fe;
}

.btn-purple {
    color: #fff;
    background-color: #8b5cf6;
    border-color: #8b5cf6;
}

.btn-purple:hover {
    color: #fff;
    background-color: #7c3aed;
    border-color: #7c3aed;
}

.bg-purple {
    background-color: #8b5cf6 !important;
}
</style>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
