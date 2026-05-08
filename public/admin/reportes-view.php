<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Services\PermissionService;
use App\Models\Reporte;

AuthMiddleware::handle();

$user = getCurrentUser();
$reporteModel = new Reporte();

$reporte_id = $_GET['id'] ?? null;

if (!$reporte_id) {
    die('ID de reporte no especificado');
}

$reporte = $reporteModel->findWithFullDetails($reporte_id);

if (!$reporte) {
    die('Reporte no encontrado');
}

// Verificar permisos
if ($user['rol'] !== 'super_admin' &&
    !($user['rol'] === 'dept_admin' && $user['departamento_id'] == $reporte['departamento_id']) &&
    !($user['rol'] === 'dept_viewer' && $user['departamento_id'] == $reporte['departamento_id'])) {
    die('No tienes permiso para ver este reporte');
}

$meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
         'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

$pageTitle = $reporte['titulo'];
?>

<!DOCTYPE html>
<html lang="es" data-bs-theme="<?php echo $user['tema'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - Sistema de Métricas</title>

    <!-- Tabler CSS -->
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet">

    <!-- ApexCharts para gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        @media print {
            .no-print { display: none !important; }
            .page-wrapper { margin: 0; padding: 0; }
        }

        .reporte-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 3rem;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        [data-bs-theme="dark"] .reporte-container {
            background: #1e293b;
        }

        .reporte-portada {
            text-align: center;
            padding: 4rem 2rem;
            border-bottom: 3px solid #3b82f6;
            margin-bottom: 3rem;
        }

        .reporte-section {
            margin-bottom: 3rem;
        }

        .area-section {
            margin-bottom: 2.5rem;
            padding: 2rem;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 4px solid #3b82f6;
        }

        [data-bs-theme="dark"] .area-section {
            background: #0f172a;
        }

        .grafico-container {
            margin: 2rem 0;
            padding: 1.5rem;
            background: white;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }

        [data-bs-theme="dark"] .grafico-container {
            background: #1e293b;
            border-color: #334155;
        }

        .resumen-ejecutivo {
            line-height: 1.8;
            font-size: 1.05rem;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>

<div class="page-wrapper">
    <!-- Toolbar superior -->
    <div class="navbar navbar-light sticky-top no-print" style="background: white; border-bottom: 1px solid #e2e8f0;">
        <div class="container-xl">
            <div class="navbar-nav flex-row">
                <a href="reportes.php" class="btn btn-ghost-secondary">
                    <i class="ti ti-arrow-left me-1"></i>
                    Volver
                </a>
            </div>
            <div class="navbar-nav flex-row ms-auto">
                <a href="reportes-editor.php?id=<?php echo $reporte['id']; ?>" class="btn btn-ghost-primary me-2">
                    <i class="ti ti-edit me-1"></i>
                    Editar
                </a>
                <a href="reportes-pdf.php?id=<?php echo $reporte['id']; ?>" class="btn btn-primary" target="_blank">
                    <i class="ti ti-file-type-pdf me-1"></i>
                    Exportar PDF
                </a>
                <button onclick="window.print()" class="btn btn-secondary ms-2">
                    <i class="ti ti-printer me-1"></i>
                    Imprimir
                </button>
            </div>
        </div>
    </div>

    <div class="page-body py-4">
        <div class="container-xl">
            <div class="reporte-container">

                <!-- PORTADA -->
                <div class="reporte-portada">
                    <div class="mb-4">
                        <span class="avatar avatar-xl" style="background-color: <?php echo $reporte['departamento_color'] ?? '#3b82f6'; ?>">
                            <i class="ti ti-<?php echo $reporte['departamento_icono'] ?? 'building'; ?>" style="font-size: 2rem;"></i>
                        </span>
                    </div>
                    <h1 class="display-5 mb-3"><?php echo htmlspecialchars($reporte['titulo']); ?></h1>
                    <h3 class="text-muted mb-2"><?php echo htmlspecialchars($reporte['departamento_nombre']); ?></h3>
                    <h4 class="text-muted">
                        <?php echo $meses[$reporte['mes']]; ?> <?php echo $reporte['anio']; ?>
                    </h4>
                    <?php if ($reporte['descripcion']): ?>
                    <p class="text-muted mt-3"><?php echo htmlspecialchars($reporte['descripcion']); ?></p>
                    <?php endif; ?>

                    <div class="mt-4 d-flex justify-content-center gap-3">
                        <?php
                        $estadoClass = [
                            'borrador' => 'bg-gray-lt',
                            'revision' => 'bg-yellow-lt',
                            'publicado' => 'bg-green-lt',
                            'archivado' => 'bg-secondary-lt'
                        ];
                        ?>
                        <span class="badge <?php echo $estadoClass[$reporte['estado']] ?? 'bg-secondary-lt'; ?> fs-5 px-3 py-2">
                            <?php echo ucfirst($reporte['estado']); ?>
                        </span>
                    </div>
                </div>

                <!-- RESUMEN EJECUTIVO -->
                <?php if (!empty($reporte['resumen_ejecutivo'])): ?>
                <div class="reporte-section">
                    <h2 class="mb-4">Resumen Ejecutivo</h2>
                    <div class="resumen-ejecutivo">
                        <?php echo nl2br(htmlspecialchars($reporte['resumen_ejecutivo'])); ?>
                    </div>
                </div>
                <?php endif; ?>

                <hr class="my-5">

                <!-- SECCIONES POR ÁREA -->
                <div class="reporte-section">
                    <h2 class="mb-4">Detalle por Área</h2>

                    <?php if (empty($reporte['areas'])): ?>
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>
                            No hay áreas configuradas para este departamento
                        </div>
                    <?php else: ?>
                        <?php foreach ($reporte['areas'] as $area): ?>
                        <div class="area-section">
                            <h3 class="mb-3">
                                <span class="avatar avatar-sm me-2" style="background-color: <?php echo $area['color'] ?? '#3b82f6'; ?>">
                                    <i class="ti ti-<?php echo $area['icono'] ?? 'folder'; ?>"></i>
                                </span>
                                <?php echo htmlspecialchars($area['nombre']); ?>
                            </h3>

                            <?php if (!empty($area['descripcion'])): ?>
                            <p class="text-muted"><?php echo htmlspecialchars($area['descripcion']); ?></p>
                            <?php endif; ?>

                            <?php if (empty($area['graficos'])): ?>
                                <p class="text-muted small">
                                    <i class="ti ti-info-circle me-1"></i>
                                    No hay gráficos configurados para esta área
                                </p>
                            <?php else: ?>
                                <?php foreach ($area['graficos'] as $grafico): ?>
                                <div class="grafico-container">
                                    <h5 class="mb-3"><?php echo htmlspecialchars($grafico['titulo']); ?></h5>

                                    <?php
                                    // Cargar y renderizar el gráfico
                                    $graficoPath = __DIR__ . '/../../views/components/charts/' . $grafico['tipo'] . '.php';
                                    if (file_exists($graficoPath)) {
                                        $chartComponent = require $graficoPath;
                                        $config = json_decode($grafico['configuracion'], true);

                                        if (isset($chartComponent['render']) && is_callable($chartComponent['render'])) {
                                            echo $chartComponent['render']($config, null, $area['color'] ?? '#3b82f6');
                                        } else {
                                            echo '<div class="alert alert-warning">Gráfico no disponible</div>';
                                        }
                                    } else {
                                        echo '<div class="alert alert-warning">Tipo de gráfico no encontrado: ' . htmlspecialchars($grafico['tipo']) . '</div>';
                                    }
                                    ?>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- PIE DE PÁGINA -->
                <div class="text-center text-muted mt-5 pt-4 border-top">
                    <small>
                        Generado por: <?php echo htmlspecialchars($reporte['autor_nombre'] ?? 'N/A'); ?><br>
                        Fecha de generación: <?php echo date('d/m/Y H:i', strtotime($reporte['created_at'])); ?>
                        <?php if ($reporte['updated_at'] != $reporte['created_at']): ?>
                        <br>Última actualización: <?php echo date('d/m/Y H:i', strtotime($reporte['updated_at'])); ?>
                        <?php endif; ?>
                    </small>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
