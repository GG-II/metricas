<?php
session_start();
require_once __DIR__ . '/../../config.php';
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

            <!-- Header -->
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

            <!-- Mensaje de bienvenida cuando no hay áreas -->
            <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'sin_areas'): ?>
            <div class="hint-box hint-box-blue mb-3" style="padding: 1.25rem;">
                <div class="d-flex align-items-start">
                    <div class="me-3">
                        <i class="ti ti-info-circle" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-2" style="font-weight: 600;">¡Bienvenido al sistema de métricas!</h4>
                        <p class="mb-2">
                            No hay áreas configuradas aún. Para comenzar a usar el sistema, sigue estos pasos en orden:
                        </p>
                        <ol class="mb-0" style="padding-left: 1.25rem;">
                            <li class="mb-1"><strong>Crea departamentos</strong> (agencias, corporativos o global)</li>
                            <li class="mb-1"><strong>Crea áreas</strong> dentro de cada departamento</li>
                            <li class="mb-1"><strong>Genera períodos</strong> usando "Generar Año Completo"</li>
                            <li><strong>Define métricas</strong> para cada área</li>
                        </ol>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Tabs Navigation -->
            <div class="card mb-3">
                <div class="card-header p-0">
                    <ul class="nav nav-tabs nav-tabs-alt" role="tablist">
                        <?php if ($user['rol'] !== 'area_admin'): ?>
                        <li class="nav-item" role="presentation">
                            <a href="#tab-configuracion" class="nav-link active" data-bs-toggle="tab" role="tab">
                                <i class="ti ti-settings me-2"></i>
                                Configuración Inicial
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item" role="presentation">
                            <a href="#tab-metricas" class="nav-link <?php echo $user['rol'] === 'area_admin' ? 'active' : ''; ?>" data-bs-toggle="tab" role="tab">
                                <i class="ti ti-chart-line me-2"></i>
                                Configuración de Métricas
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a href="#tab-operacion" class="nav-link" data-bs-toggle="tab" role="tab">
                                <i class="ti ti-chart-bar me-2"></i>
                                Operación Diaria
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Tabs Content -->
            <div class="tab-content">

                <!-- Tab 1: Configuración Inicial -->
                <?php if ($user['rol'] !== 'area_admin'): ?>
                <div class="tab-pane fade show active" id="tab-configuracion" role="tabpanel">
                    <div class="tab-info-header tab-info-blue mb-4">
                        <div class="d-flex">
                            <div>
                                <i class="ti ti-info-circle me-2"></i>
                            </div>
                            <div>
                                <h4 class="tab-info-title">Configuración Inicial</h4>
                                <div class="text-muted">Configure la estructura base de su sistema. Complete estos módulos al configurar el sistema por primera vez.</div>
                            </div>
                        </div>
                    </div>

                    <div class="admin-cards-cascade">

                        <!-- Departamentos -->
                        <?php if ($user['rol'] === 'super_admin'): ?>
                        <div class="card card-link admin-card-cascade">
                            <a href="<?php echo baseUrl('/admin/departamentos.php'); ?>" class="text-decoration-none">
                                <div class="card-body">
                                    <div class="row align-items-start">
                                        <div class="col-auto">
                                            <span class="avatar avatar-xl" style="background-color: #3b82f6;">
                                                <i class="ti ti-building" style="font-size: 2.5rem;"></i>
                                            </span>
                                        </div>
                                        <div class="col">
                                            <h3 class="card-title mb-2">
                                                <i class="ti ti-building me-2"></i>
                                                Departamentos
                                            </h3>
                                            <p class="text-muted mb-3">Gestionar departamentos del sistema</p>
                                            <p class="mb-3">
                                                Defina la estructura organizacional de su cooperativa: agencias físicas (sucursales),
                                                departamentos corporativos (TI, RRHH, etc.) y el departamento especial Global para métricas consolidadas.
                                            </p>
                                            <div class="hint-box hint-box-blue">
                                                <div class="d-flex align-items-start">
                                                    <div class="me-2">💡</div>
                                                    <div>
                                                        <strong>Hágalo primero:</strong> Sin departamentos no puede crear áreas ni asignar usuarios.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto align-self-center">
                                            <i class="ti ti-chevron-right text-muted" style="font-size: 1.5rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php endif; ?>

                        <!-- Áreas -->
                        <div class="card card-link admin-card-cascade">
                            <a href="<?php echo baseUrl('/admin/areas.php'); ?><?php echo $user['rol'] === 'dept_admin' ? '?departamento=' . $user['departamento_id'] : ''; ?>" class="text-decoration-none">
                                <div class="card-body">
                                    <div class="row align-items-start">
                                        <div class="col-auto">
                                            <span class="avatar avatar-xl" style="background-color: #10b981;">
                                                <i class="ti ti-layout-grid" style="font-size: 2.5rem;"></i>
                                            </span>
                                        </div>
                                        <div class="col">
                                            <h3 class="card-title mb-2">
                                                <i class="ti ti-layout-grid me-2"></i>
                                                Áreas
                                            </h3>
                                            <p class="text-muted mb-3">Gestionar áreas por departamento</p>
                                            <p class="mb-3">
                                                Cree las áreas operativas dentro de cada departamento. Por ejemplo: en una agencia puede
                                                tener Caja, Créditos, Cuentas Nuevas. En TI puede tener Backend, Frontend, Infraestructura.
                                            </p>
                                            <div class="hint-box hint-box-blue">
                                                <div class="d-flex align-items-start">
                                                    <div class="me-2">💡</div>
                                                    <div>
                                                        <strong>Requiere:</strong> Al menos un departamento creado. Las métricas se definirán dentro de las áreas.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto align-self-center">
                                            <i class="ti ti-chevron-right text-muted" style="font-size: 1.5rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Períodos -->
                        <?php if ($user['rol'] === 'super_admin'): ?>
                        <div class="card card-link admin-card-cascade">
                            <a href="<?php echo baseUrl('/admin/periodos.php'); ?>" class="text-decoration-none">
                                <div class="card-body">
                                    <div class="row align-items-start">
                                        <div class="col-auto">
                                            <span class="avatar avatar-xl" style="background-color: #06b6d4;">
                                                <i class="ti ti-calendar" style="font-size: 2.5rem;"></i>
                                            </span>
                                        </div>
                                        <div class="col">
                                            <h3 class="card-title mb-2">
                                                <i class="ti ti-calendar me-2"></i>
                                                Períodos
                                            </h3>
                                            <p class="text-muted mb-3">Gestionar períodos de tiempo</p>
                                            <p class="mb-3">
                                                Configure el calendario operativo del sistema: ejercicios fiscales (años) y períodos mensuales.
                                                Los valores de métricas se registran por período.
                                            </p>
                                            <div class="hint-box hint-box-blue mb-0">
                                                <div class="d-flex align-items-start">
                                                    <div class="me-2">💡</div>
                                                    <div>
                                                        <strong>Configure el año actual:</strong> El sistema permite trabajar con múltiples ejercicios para análisis histórico.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto align-self-center">
                                            <i class="ti ti-chevron-right text-muted" style="font-size: 1.5rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php endif; ?>

                        <!-- Usuarios -->
                        <?php if ($user['rol'] === 'super_admin'): ?>
                        <div class="card card-link admin-card-cascade">
                            <a href="<?php echo baseUrl('/admin/usuarios.php'); ?>" class="text-decoration-none">
                                <div class="card-body">
                                    <div class="row align-items-start">
                                        <div class="col-auto">
                                            <span class="avatar avatar-xl" style="background-color: #f59e0b;">
                                                <i class="ti ti-users" style="font-size: 2.5rem;"></i>
                                            </span>
                                        </div>
                                        <div class="col">
                                            <h3 class="card-title mb-2">
                                                <i class="ti ti-users me-2"></i>
                                                Usuarios
                                            </h3>
                                            <p class="text-muted mb-3">Gestionar usuarios y permisos</p>
                                            <p class="mb-3">
                                                Cree cuentas de acceso y asigne roles según responsabilidades:<br>
                                                • <strong>Super Admin:</strong> acceso total al sistema<br>
                                                • <strong>Admin de Departamento:</strong> gestiona su departamento<br>
                                                • <strong>Admin de Área:</strong> gestiona solo un área específica<br>
                                                • <strong>Visualizador:</strong> consulta sin editar
                                            </p>
                                            <div class="hint-box hint-box-blue">
                                                <div class="d-flex align-items-start">
                                                    <div class="me-2">💡</div>
                                                    <div>
                                                        <strong>Requiere:</strong> Departamentos y áreas creados para asignar usuarios correctamente.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto align-self-center">
                                            <i class="ti ti-chevron-right text-muted" style="font-size: 1.5rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>
                <?php endif; ?>

                <!-- Tab 2: Configuración de Métricas -->
                <div class="tab-pane fade <?php echo $user['rol'] === 'area_admin' ? 'show active' : ''; ?>" id="tab-metricas" role="tabpanel">
                    <div class="tab-info-header tab-info-purple mb-4">
                        <div class="d-flex">
                            <div>
                                <i class="ti ti-info-circle me-2"></i>
                            </div>
                            <div>
                                <h4 class="tab-info-title">Configuración de Métricas</h4>
                                <div class="text-muted">Defina qué medir, los objetivos a alcanzar y cómo visualizar la información. Configure estos módulos después de tener su estructura base lista.</div>
                            </div>
                        </div>
                    </div>

                    <div class="admin-cards-cascade">

                        <!-- Métricas -->
                        <div class="card card-link admin-card-cascade">
                            <a href="<?php echo baseUrl('/admin/metricas.php'); ?>" class="text-decoration-none">
                                <div class="card-body">
                                    <div class="row align-items-start">
                                        <div class="col-auto">
                                            <span class="avatar avatar-xl" style="background-color: #8b5cf6;">
                                                <i class="ti ti-chart-line" style="font-size: 2.5rem;"></i>
                                            </span>
                                        </div>
                                        <div class="col">
                                            <h3 class="card-title mb-2">
                                                <i class="ti ti-chart-line me-2"></i>
                                                Métricas
                                            </h3>
                                            <p class="text-muted mb-3">Configurar métricas por área</p>
                                            <p class="mb-3">
                                                Defina los indicadores que medirá en cada área. Por ejemplo: Colocación de créditos, Asociados nuevos,
                                                Tarjetas emitidas, Tickets resueltos.<br><br>
                                                <strong>Tipos de métricas:</strong><br>
                                                • <strong>Simples:</strong> valores que se ingresan manualmente<br>
                                                • <strong>Calculadas:</strong> suman o promedian otras métricas<br>
                                                • <strong>Globales:</strong> consolidan datos de varias áreas
                                            </p>
                                            <div class="hint-box hint-box-purple">
                                                <div class="d-flex align-items-start">
                                                    <div class="me-2">💡</div>
                                                    <div>
                                                        <strong>Requiere:</strong> Áreas creadas donde definir las métricas. Sin métricas no hay datos que medir.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto align-self-center">
                                            <i class="ti ti-chevron-right text-muted" style="font-size: 1.5rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Metas -->
                        <div class="card card-link admin-card-cascade">
                            <a href="<?php echo baseUrl('/admin/metas.php'); ?>" class="text-decoration-none">
                                <div class="card-body">
                                    <div class="row align-items-start">
                                        <div class="col-auto">
                                            <span class="avatar avatar-xl" style="background-color: #06b6d4;">
                                                <i class="ti ti-target" style="font-size: 2.5rem;"></i>
                                            </span>
                                        </div>
                                        <div class="col">
                                            <h3 class="card-title mb-2">
                                                <i class="ti ti-target me-2"></i>
                                                Metas
                                            </h3>
                                            <p class="text-muted mb-3">Definir objetivos y metas</p>
                                            <p class="mb-3">
                                                Establezca los objetivos que desea alcanzar para cada métrica. Puede definir metas mensuales,
                                                trimestrales o anuales.<br><br>
                                                <strong>El sistema calcula automáticamente:</strong><br>
                                                • Porcentaje de cumplimiento<br>
                                                • Semáforos (verde/amarillo/rojo)<br>
                                                • Proyecciones de cumplimiento anual
                                            </p>
                                            <div class="hint-box hint-box-purple">
                                                <div class="d-flex align-items-start">
                                                    <div class="me-2">💡</div>
                                                    <div>
                                                        <strong>Requiere:</strong> Métricas definidas. Las metas son opcionales pero altamente recomendadas para medir desempeño.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto align-self-center">
                                            <i class="ti ti-chevron-right text-muted" style="font-size: 1.5rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Gráficos -->
                        <div class="card card-link admin-card-cascade">
                            <a href="<?php echo baseUrl('/admin/graficos.php'); ?>" class="text-decoration-none">
                                <div class="card-body">
                                    <div class="row align-items-start">
                                        <div class="col-auto">
                                            <span class="avatar avatar-xl" style="background-color: #ef4444;">
                                                <i class="ti ti-chart-bar" style="font-size: 2.5rem;"></i>
                                            </span>
                                        </div>
                                        <div class="col">
                                            <h3 class="card-title mb-2">
                                                <i class="ti ti-chart-bar me-2"></i>
                                                Gráficos
                                            </h3>
                                            <p class="text-muted mb-3">Configurar dashboards</p>
                                            <p class="mb-3">
                                                Diseñe los tableros de control (dashboards) para cada área. Elija entre 24+ tipos de gráficos:
                                                KPIs, líneas, barras, donuts, gauges, radares, comparaciones, y más.<br><br>
                                                <strong>Personalice:</strong><br>
                                                • Posición y tamaño de cada gráfico<br>
                                                • Colores y estilos visuales<br>
                                                • Qué métricas mostrar y cómo
                                            </p>
                                            <div class="hint-box hint-box-purple">
                                                <div class="d-flex align-items-start">
                                                    <div class="me-2">💡</div>
                                                    <div>
                                                        <strong>Requiere:</strong> Métricas con valores. Los gráficos visualizan los datos ingresados.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto align-self-center">
                                            <i class="ti ti-chevron-right text-muted" style="font-size: 1.5rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                    </div>
                </div>

                <!-- Tab 3: Operación Diaria -->
                <div class="tab-pane fade" id="tab-operacion" role="tabpanel">
                    <div class="tab-info-header tab-info-green mb-4">
                        <div class="d-flex">
                            <div>
                                <i class="ti ti-info-circle me-2"></i>
                            </div>
                            <div>
                                <h4 class="tab-info-title">Operación Diaria</h4>
                                <div class="text-muted">Módulos para el uso continuo del sistema. Use estos diariamente o según la frecuencia de actualización de sus métricas.</div>
                            </div>
                        </div>
                    </div>

                    <div class="admin-cards-cascade">

                        <!-- Captura de Valores -->
                        <div class="card card-link admin-card-cascade">
                            <a href="<?php echo baseUrl('/captura-valores.php'); ?>" class="text-decoration-none">
                                <div class="card-body">
                                    <div class="row align-items-start">
                                        <div class="col-auto">
                                            <span class="avatar avatar-xl" style="background-color: #10b981;">
                                                <i class="ti ti-edit" style="font-size: 2.5rem;"></i>
                                            </span>
                                        </div>
                                        <div class="col">
                                            <h3 class="card-title mb-2">
                                                <i class="ti ti-edit me-2"></i>
                                                Captura de Valores
                                            </h3>
                                            <p class="text-muted mb-3">Registrar valores de métricas</p>
                                            <p class="mb-3">
                                                Ingrese los valores reales de sus métricas para cada período. Por ejemplo: registre la colocación
                                                de créditos del mes, los asociados nuevos captados, las tarjetas emitidas, etc.<br><br>
                                                <strong>Características:</strong><br>
                                                • Ingreso rápido por área y período<br>
                                                • Validación automática vs metas<br>
                                                • Historial de cambios<br>
                                                • Notas y comentarios por valor
                                            </p>
                                            <div class="hint-box hint-box-green">
                                                <div class="d-flex align-items-start">
                                                    <div class="me-2">💡</div>
                                                    <div>
                                                        <strong>Úselo frecuentemente:</strong> Actualice según su calendario (diario, semanal o mensual). Los dashboards se actualizan automáticamente.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto align-self-center">
                                            <i class="ti ti-chevron-right text-muted" style="font-size: 1.5rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Reportes -->
                        <div class="card card-link admin-card-cascade">
                            <a href="<?php echo baseUrl('/admin/reportes.php'); ?>" class="text-decoration-none">
                                <div class="card-body">
                                    <div class="row align-items-start">
                                        <div class="col-auto">
                                            <span class="avatar avatar-xl" style="background-color: #ec4899;">
                                                <i class="ti ti-file-text" style="font-size: 2.5rem;"></i>
                                            </span>
                                        </div>
                                        <div class="col">
                                            <h3 class="card-title mb-2">
                                                <i class="ti ti-file-text me-2"></i>
                                                Reportes
                                            </h3>
                                            <p class="text-muted mb-3">Crear reportes escritos con gráficas</p>
                                            <p class="mb-3">
                                                Genere informes profesionales combinando datos y gráficos. Exporte a PDF o Excel para presentar
                                                en juntas directivas o reuniones gerenciales.<br><br>
                                                <strong>Opciones de reporte:</strong><br>
                                                • <strong>Por área:</strong> desempeño de un área específica<br>
                                                • <strong>Por departamento:</strong> todas las áreas consolidadas<br>
                                                • <strong>Comparativos:</strong> entre áreas o períodos<br>
                                                • <strong>Ejecutivos:</strong> resumen de KPIs principales
                                            </p>
                                            <div class="hint-box hint-box-green">
                                                <div class="d-flex align-items-start">
                                                    <div class="me-2">💡</div>
                                                    <div>
                                                        <strong>Úselo cuando necesite:</strong> Presentaciones, análisis mensuales, reportes anuales o evaluaciones de desempeño.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto align-self-center">
                                            <i class="ti ti-chevron-right text-muted" style="font-size: 1.5rem;"></i>
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
</div>

<style>
/* Tabs personalizados */
.nav-tabs-alt {
    border-bottom: 2px solid #e2e8f0;
}

.nav-tabs-alt .nav-link {
    padding: 1rem 1.5rem;
    font-weight: 500;
    color: #64748b;
    border: none;
    border-bottom: 3px solid transparent;
    transition: all 0.2s;
}

.nav-tabs-alt .nav-link:hover {
    color: #3b82f6;
    background-color: #f8fafc;
    border-bottom-color: #cbd5e1;
}

.nav-tabs-alt .nav-link.active {
    color: #3b82f6;
    background-color: transparent;
    border-bottom-color: #3b82f6;
}

/* Cards en cascada */
.admin-cards-cascade {
    max-width: 900px;
    width: 100%;
    margin: 0 auto;
}

.admin-card-cascade {
    width: 100%;
    max-width: 100%;
    margin: 0 0 1.5rem 0 !important;
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
    display: flex;
    flex-direction: column;
    box-sizing: border-box;
    padding: 0;
}

.admin-card-cascade > a {
    display: block;
    width: 100%;
    height: 100%;
    flex: 1;
}

.admin-card-cascade:hover {
    transform: translateX(8px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    border-left-color: #3b82f6;
}

.admin-card-cascade .card-body {
    padding: 2rem;
    flex: 1;
    display: flex;
    align-items: stretch;
}

.admin-card-cascade .row {
    flex: 1;
}

.admin-card-cascade .card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1e293b;
}

/* Títulos en modo oscuro */
[data-bs-theme="dark"] .admin-card-cascade .card-title {
    color: #f1f5f9;
}

.admin-card-cascade p {
    line-height: 1.7;
}

.admin-card-cascade .avatar-xl {
    width: 80px;
    height: 80px;
    flex-shrink: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .admin-cards-cascade {
        max-width: 100%;
    }

    .admin-card-cascade .card-body {
        padding: 1.25rem;
    }

    .admin-card-cascade .col-auto:last-child {
        display: none;
    }

    .nav-tabs-alt .nav-link {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
    }
}


/* Hint boxes permanentes */
.hint-box {
    padding: 0.75rem 1rem;
    border-radius: 0.375rem;
    border: 1px solid;
    margin-bottom: 0;
}

.hint-box-blue {
    background-color: #eff6ff;
    border-color: #bfdbfe;
    color: #1e40af;
}

.hint-box-purple {
    background-color: #f5f3ff;
    border-color: #ddd6fe;
    color: #6d28d9;
}

.hint-box-green {
    background-color: #f0fdf4;
    border-color: #bbf7d0;
    color: #15803d;
}

/* Hint boxes en modo oscuro */
[data-bs-theme="dark"] .hint-box-blue {
    background-color: #1e3a5f;
    border-color: #2563eb;
    color: #93c5fd;
}

[data-bs-theme="dark"] .hint-box-purple {
    background-color: #2e1f47;
    border-color: #7c3aed;
    color: #c4b5fd;
}

[data-bs-theme="dark"] .hint-box-green {
    background-color: #1a3a2e;
    border-color: #16a34a;
    color: #86efac;
}

/* Tab info headers permanentes */
.tab-info-header {
    padding: 1rem 1.25rem;
    border-radius: 0.5rem;
    border: 1px solid;
    max-width: 900px;
    width: 100%;
    margin-left: auto;
    margin-right: auto;
}

.tab-info-blue {
    background-color: #dbeafe;
    border-color: #93c5fd;
    color: #1e40af;
}

.tab-info-purple {
    background-color: #f5f3ff;
    border-color: #ddd6fe;
    color: #6d28d9;
}

.tab-info-green {
    background-color: #f0fdf4;
    border-color: #bbf7d0;
    color: #15803d;
}

.tab-info-title {
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: inherit;
}

/* Tab info headers en modo oscuro */
[data-bs-theme="dark"] .tab-info-blue {
    background-color: #1e3a5f;
    border-color: #2563eb;
    color: #93c5fd;
}

[data-bs-theme="dark"] .tab-info-purple {
    background-color: #2e1f47;
    border-color: #7c3aed;
    color: #c4b5fd;
}

[data-bs-theme="dark"] .tab-info-green {
    background-color: #1a3a2e;
    border-color: #16a34a;
    color: #86efac;
}
</style>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
