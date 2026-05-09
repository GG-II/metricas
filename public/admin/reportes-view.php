<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Services\PermissionService;
use App\Models\Reporte;
use App\Models\Periodo;
use App\Models\ValorMetrica;

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

// Buscar el período correspondiente al mes/año del reporte
$periodoModel = new Periodo();
$periodo = $periodoModel->findByEjercicioAndPeriodo($reporte['anio'], $reporte['mes']);

// Helper para obtener datos de métricas (igual que en dashboard)
function obtenerDatosMetrica($metrica_id, $periodo_id) {
    $valorMetricaModel = new ValorMetrica();
    return $valorMetricaModel->getByMetricaYPeriodo($metrica_id, $periodo_id);
}

$pageTitle = $reporte['titulo'];
?>

<!DOCTYPE html>
<html lang="es" data-bs-theme="<?php echo $user['tema'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - Sistema de Métricas</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?php echo baseUrl('/assets/favicon/favicon.svg'); ?>">
    <link rel="icon" type="image/png" sizes="96x96" href="<?php echo baseUrl('/assets/favicon/favicon-96x96.png'); ?>">
    <link rel="shortcut icon" href="<?php echo baseUrl('/assets/favicon/favicon.ico'); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo baseUrl('/assets/favicon/apple-touch-icon.png'); ?>">
    <meta name="theme-color" content="#1e40af">

    <!-- Tabler CSS -->
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet">

    <!-- ApexCharts para gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        @media print {
            .no-print { display: none !important; }
            .page-wrapper { margin: 0 !important; padding: 0 !important; }
            .reporte-container {
                box-shadow: none !important;
                padding: 1cm !important;
                margin: 0 !important;
            }
            .reporte-portada {
                padding: 1.5cm 1cm !important;
                margin-bottom: 1cm !important;
                page-break-after: avoid;
            }
            .area-section {
                padding: 0.8cm !important;
                margin-bottom: 0.8cm !important;
                page-break-inside: avoid;
            }
            .grafico-container {
                padding: 0.5cm !important;
                margin: 0.5cm 0 !important;
                page-break-inside: avoid;
            }
            h1 { font-size: 1.8rem !important; }
            h2 { font-size: 1.4rem !important; }
            h3 { font-size: 1.2rem !important; }
        }

        .reporte-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        [data-bs-theme="dark"] .reporte-container {
            background: #1e293b;
        }

        .reporte-portada {
            text-align: center;
            padding: 1.75rem 1.25rem;
            background: #1e40af;
            color: white;
            border-radius: 4px;
            margin-bottom: 1.5rem;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        .portada-icon {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.75rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .portada-icon i {
            font-size: 1.75rem;
            color: white;
        }

        .reporte-portada h1,
        .reporte-portada h3,
        .reporte-portada h4,
        .reporte-portada p {
            color: white;
            line-height: 1.3;
        }

        .reporte-portada h1 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .reporte-portada h3 {
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
        }

        .reporte-portada h4 {
            font-size: 1rem;
        }

        .reporte-section {
            margin-bottom: 1.5rem;
        }

        .reporte-section h2 {
            font-size: 1.3rem;
            margin-bottom: 0.75rem;
        }

        .area-section {
            margin-bottom: 1.25rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 4px;
            border-left: 4px solid #1e40af;
        }

        [data-bs-theme="dark"] .area-section {
            background: #0f172a;
            border-color: #3b82f6;
        }

        .area-header {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
        }

        [data-bs-theme="dark"] .area-header {
            border-bottom-color: #334155;
        }

        .area-header h3 {
            font-size: 1.1rem;
            margin-bottom: 0;
            line-height: 1.2;
        }

        .area-header p {
            font-size: 0.85rem;
            margin-bottom: 0;
            line-height: 1.3;
        }

        .area-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.65rem;
            font-size: 1.1rem;
            color: white;
            flex-shrink: 0;
        }

        .grafico-container {
            margin: 0.75rem 0;
            padding: 0.85rem;
            background: white;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
        }

        [data-bs-theme="dark"] .grafico-container {
            background: #1e293b;
            border-color: #334155;
        }

        .grafico-container h5 {
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
            color: #334155;
            line-height: 1.2;
        }

        [data-bs-theme="dark"] .grafico-container h5 {
            color: #cbd5e1;
        }

        .resumen-ejecutivo {
            line-height: 1.6;
            font-size: 0.95rem;
            background: #f8fafc;
            padding: 1.25rem;
            border-radius: 4px;
            border-left: 3px solid #1e40af;
            white-space: pre-line;
        }

        [data-bs-theme="dark"] .resumen-ejecutivo {
            background: #0f172a;
        }

        .badge {
            font-size: 0.8rem;
            padding: 0.35rem 0.75rem;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .area-icon {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .area-section {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
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
                    <div class="portada-icon">
                        <i class="ti ti-<?php echo $reporte['departamento_icono'] ?? 'building'; ?>"></i>
                    </div>
                    <h1 class="mb-2"><?php echo htmlspecialchars($reporte['titulo']); ?></h1>
                    <h3 class="mb-2"><?php echo htmlspecialchars($reporte['departamento_nombre']); ?></h3>
                    <h4 class="mb-2">
                        <?php echo $meses[$reporte['mes']]; ?> <?php echo $reporte['anio']; ?>
                    </h4>
                    <?php if ($reporte['descripcion']): ?>
                    <p class="mt-2 mb-0" style="font-size: 0.95rem; opacity: 0.9;"><?php echo htmlspecialchars($reporte['descripcion']); ?></p>
                    <?php endif; ?>

                    <div class="mt-3 d-flex justify-content-center gap-2">
                        <?php
                        $estadoClass = [
                            'borrador' => 'bg-warning',
                            'revision' => 'bg-info',
                            'publicado' => 'bg-success',
                            'archivado' => 'bg-secondary'
                        ];
                        ?>
                        <span class="badge <?php echo $estadoClass[$reporte['estado']] ?? 'bg-secondary'; ?>">
                            <i class="ti ti-circle-check me-1"></i>
                            <?php echo ucfirst($reporte['estado']); ?>
                        </span>
                    </div>
                </div>

                <!-- RESUMEN EJECUTIVO -->
                <?php if (!empty($reporte['resumen_ejecutivo'])): ?>
                <div class="reporte-section">
                    <h2 class="mb-4">Resumen Ejecutivo</h2>
                    <div class="resumen-ejecutivo">
                        <?php
                        // Preservar saltos de línea pero limitar exceso de espacios en blanco
                        $texto = $reporte['resumen_ejecutivo'];
                        // Colapsar 3+ saltos de línea a solo 2 (máximo un espacio en blanco visible)
                        $texto = preg_replace('/\n{3,}/', "\n\n", $texto);
                        // Mostrar con formato preservado
                        echo htmlspecialchars($texto);
                        ?>
                    </div>
                </div>
                <?php endif; ?>

                <hr class="my-5">

                <!-- SECCIONES POR ÁREA -->
                <div class="reporte-section">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0">Detalle por Área</h2>
                        <span class="badge bg-info-lt" style="font-size: 0.875rem;">
                            <i class="ti ti-calendar me-1"></i>
                            Datos al cierre de <?php echo $meses[$reporte['mes']]; ?> <?php echo $reporte['anio']; ?>
                        </span>
                    </div>

                    <?php if (empty($reporte['areas'])): ?>
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>
                            No hay áreas configuradas para este departamento
                        </div>
                    <?php else: ?>
                        <?php foreach ($reporte['areas'] as $area): ?>
                        <div class="area-section">
                            <div class="area-header">
                                <div class="area-icon" style="background-color: <?php echo $area['color'] ?? '#3b82f6'; ?>">
                                    <i class="ti ti-<?php echo $area['icono'] ?? 'folder'; ?>"></i>
                                </div>
                                <div>
                                    <h3 class="mb-1"><?php echo htmlspecialchars($area['nombre']); ?></h3>
                                    <?php if (!empty($area['descripcion'])): ?>
                                    <p class="text-muted mb-0 small"><?php echo htmlspecialchars($area['descripcion']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

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
                                    // Normalizar nombre: convertir guión bajo a guión (kpi_card -> kpi-card)
                                    $tipoNormalizado = str_replace('_', '-', $grafico['tipo']);
                                    $graficoPath = __DIR__ . '/../../views/components/charts/' . $tipoNormalizado . '.php';
                                    if (file_exists($graficoPath)) {
                                        $chartComponent = require $graficoPath;
                                        $config = json_decode($grafico['configuracion'], true);

                                        // IMPORTANTE: Pasar período límite del reporte a los gráficos
                                        // para que no muestren datos posteriores al mes del reporte
                                        $config['_periodo_limite'] = [
                                            'anio' => $reporte['anio'],
                                            'mes' => $reporte['mes'],
                                            'periodo_id' => $periodo ? $periodo['id'] : null
                                        ];

                                        // Obtener datos de la métrica si el gráfico la usa (igual que dashboard)
                                        $metrica_data = null;
                                        if (isset($config['metrica_id']) && $periodo) {
                                            $metrica_data = obtenerDatosMetrica($config['metrica_id'], $periodo['id']);
                                        }

                                        if (isset($chartComponent['render']) && is_callable($chartComponent['render'])) {
                                            // Algunos componentes (como donut) necesitan el período como 4to parámetro
                                            echo $chartComponent['render']($config, $metrica_data, $area['color'] ?? '#3b82f6', $periodo);
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

<script>
// Esperar a que todos los gráficos ApexCharts se rendericen antes de imprimir
window.addEventListener('load', function() {
    // Dar tiempo extra para que ApexCharts termine de renderizar
    setTimeout(function() {
        console.log('Página lista para imprimir');

        // Marcar como lista para sistemas que usen automation
        document.body.classList.add('ready-to-print');
    }, 2000);
});

// Mejorar el comportamiento de window.print()
window.addEventListener('beforeprint', function() {
    console.log('Preparando para imprimir...');

    // Forzar re-render de gráficos si es necesario
    if (window.Apex && window.Apex.chart) {
        try {
            // ApexCharts tiene issues con print, forzar redraw
            const charts = document.querySelectorAll('.apexcharts-canvas');
            charts.forEach(chart => {
                if (chart.style) {
                    chart.style.display = 'block';
                }
            });
        } catch (e) {
            console.warn('Error al preparar gráficos:', e);
        }
    }
});

window.addEventListener('afterprint', function() {
    console.log('Impresión completada');
});
</script>

</body>
</html>
