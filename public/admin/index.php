<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;

AuthMiddleware::handle();
PermissionMiddleware::requireAdmin();

$user = getCurrentUser();
$pageTitle = 'Panel de Administración';
require_once __DIR__ . '/../../views/layouts/header.php';
?>

<div class="page-wrapper">
    <div class="page-body">
        <div class="container-xl">

            <div class="page-header mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h2 class="page-title">
                            <i class="ti ti-settings me-2"></i>
                            Panel de Administración
                        </h2>
                        <div class="text-muted mt-1">Gestión completa del sistema</div>
                    </div>
                </div>
            </div>

            <!-- Cards de Administración -->
            <div class="row row-cards">

                <!-- Departamentos (Solo Super Admin) -->
                <?php if ($user['rol'] === 'super_admin'): ?>
                <div class="col-md-4">
                    <div class="card card-link">
                        <a href="<?php echo baseUrl('/public/admin/departamentos.php'); ?>" class="text-decoration-none">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="avatar avatar-lg" style="background-color: #3b82f6;">
                                            <i class="ti ti-building" style="font-size: 2rem;"></i>
                                        </span>
                                    </div>
                                    <div class="col">
                                        <h3 class="card-title mb-1">Departamentos</h3>
                                        <div class="text-muted">Gestionar departamentos del sistema</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Áreas -->
                <div class="col-md-4">
                    <div class="card card-link">
                        <a href="<?php echo baseUrl('/public/admin/areas.php'); ?><?php echo $user['rol'] === 'dept_admin' ? '?departamento=' . $user['departamento_id'] : ''; ?>" class="text-decoration-none">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="avatar avatar-lg" style="background-color: #10b981;">
                                            <i class="ti ti-layout-grid" style="font-size: 2rem;"></i>
                                        </span>
                                    </div>
                                    <div class="col">
                                        <h3 class="card-title mb-1">Áreas</h3>
                                        <div class="text-muted">Gestionar áreas por departamento</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Usuarios (Solo Super Admin) -->
                <?php if ($user['rol'] === 'super_admin'): ?>
                <div class="col-md-4">
                    <div class="card card-link">
                        <a href="<?php echo baseUrl('/public/admin/usuarios.php'); ?>" class="text-decoration-none">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="avatar avatar-lg" style="background-color: #f59e0b;">
                                            <i class="ti ti-users" style="font-size: 2rem;"></i>
                                        </span>
                                    </div>
                                    <div class="col">
                                        <h3 class="card-title mb-1">Usuarios</h3>
                                        <div class="text-muted">Gestionar usuarios y permisos</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Métricas -->
                <div class="col-md-4">
                    <div class="card card-link">
                        <a href="<?php echo baseUrl('/public/admin/metricas.php'); ?>" class="text-decoration-none">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="avatar avatar-lg" style="background-color: #8b5cf6;">
                                            <i class="ti ti-chart-line" style="font-size: 2rem;"></i>
                                        </span>
                                    </div>
                                    <div class="col">
                                        <h3 class="card-title mb-1">Métricas</h3>
                                        <div class="text-muted">Configurar métricas por área</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Metas -->
                <div class="col-md-4">
                    <div class="card card-link">
                        <a href="<?php echo baseUrl('/public/admin/metas.php'); ?>" class="text-decoration-none">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="avatar avatar-lg" style="background-color: #06b6d4;">
                                            <i class="ti ti-target" style="font-size: 2rem;"></i>
                                        </span>
                                    </div>
                                    <div class="col">
                                        <h3 class="card-title mb-1">Metas</h3>
                                        <div class="text-muted">Definir objetivos y metas</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Gráficos -->
                <div class="col-md-4">
                    <div class="card card-link">
                        <a href="<?php echo baseUrl('/public/admin/graficos.php'); ?>" class="text-decoration-none">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="avatar avatar-lg" style="background-color: #ef4444;">
                                            <i class="ti ti-chart-bar" style="font-size: 2rem;"></i>
                                        </span>
                                    </div>
                                    <div class="col">
                                        <h3 class="card-title mb-1">Gráficos</h3>
                                        <div class="text-muted">Configurar dashboards</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Reportes -->
                <div class="col-md-4">
                    <div class="card card-link">
                        <a href="<?php echo baseUrl('/public/admin/reportes.php'); ?>" class="text-decoration-none">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="avatar avatar-lg" style="background-color: #ec4899;">
                                            <i class="ti ti-file-text" style="font-size: 2rem;"></i>
                                        </span>
                                    </div>
                                    <div class="col">
                                        <h3 class="card-title mb-1">Reportes</h3>
                                        <div class="text-muted">Crear reportes escritos con gráficas</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Períodos (Solo Super Admin) -->
                <?php if ($user['rol'] === 'super_admin'): ?>
                <div class="col-md-4">
                    <div class="card card-link">
                        <a href="<?php echo baseUrl('/public/admin/periodos.php'); ?>" class="text-decoration-none">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="avatar avatar-lg" style="background-color: #06b6d4;">
                                            <i class="ti ti-calendar" style="font-size: 2rem;"></i>
                                        </span>
                                    </div>
                                    <div class="col">
                                        <h3 class="card-title mb-1">Períodos</h3>
                                        <div class="text-muted">Gestionar períodos de tiempo</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Captura de Valores -->
                <div class="col-md-4">
                    <div class="card card-link">
                        <a href="<?php echo baseUrl('/public/captura-valores.php'); ?>" class="text-decoration-none">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="avatar avatar-lg" style="background-color: #84cc16;">
                                            <i class="ti ti-edit" style="font-size: 2rem;"></i>
                                        </span>
                                    </div>
                                    <div class="col">
                                        <h3 class="card-title mb-1">Captura de Valores</h3>
                                        <div class="text-muted">Registrar valores de métricas</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<style>
.card-link {
    transition: all 0.2s;
}

.card-link:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}
</style>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
