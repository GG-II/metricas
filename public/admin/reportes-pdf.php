<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
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

// Buscar el período correspondiente
$periodoModel = new Periodo();
$periodo = $periodoModel->findByEjercicioAndPeriodo($reporte['anio'], $reporte['mes']);

// Helper para obtener datos de métricas
function obtenerDatosMetrica($metrica_id, $periodo_id) {
    $valorMetricaModel = new ValorMetrica();
    return $valorMetricaModel->getByMetricaYPeriodo($metrica_id, $periodo_id);
}

$pageTitle = $reporte['titulo'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - PDF</title>

    <!-- Tabler CSS -->
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet">

    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        @page {
            size: letter;
            margin: 1.5cm 1cm;
        }

        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none !important; }
            .reporte-container {
                box-shadow: none !important;
                padding: 0.5cm !important;
                margin: 0 !important;
            }
            .reporte-portada {
                padding: 1.2cm 1cm !important;
                margin-bottom: 0.8cm !important;
                page-break-after: avoid;
            }
            .area-section {
                padding: 0.6cm !important;
                margin-bottom: 0.6cm !important;
                page-break-inside: avoid;
            }
            .grafico-container {
                padding: 0.4cm !important;
                margin: 0.4cm 0 !important;
                page-break-inside: avoid;
            }
            h1 { font-size: 1.5rem !important; }
            h2 { font-size: 1.2rem !important; }
            h3 { font-size: 1rem !important; }
        }

        body {
            background: #f5f5f5;
        }

        .reporte-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .reporte-portada {
            text-align: center;
            padding: 2rem 1.5rem;
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
            margin: 0 auto 1rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .portada-icon i { font-size: 2rem; color: white; }

        .reporte-portada h1,
        .reporte-portada h3,
        .reporte-portada h4,
        .reporte-portada p {
            color: white;
            line-height: 1.3;
        }

        .reporte-portada h1 { font-size: 1.5rem; margin-bottom: 0.5rem; }
        .reporte-portada h3 { font-size: 1.1rem; margin-bottom: 0.25rem; }
        .reporte-portada h4 { font-size: 1rem; }

        .reporte-section { margin-bottom: 1.5rem; }
        .reporte-section h2 { font-size: 1.3rem; margin-bottom: 0.75rem; }

        .area-section {
            margin-bottom: 1.25rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 4px;
            border-left: 4px solid #1e40af;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .area-header {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .area-header h3 { font-size: 1.1rem; margin-bottom: 0; line-height: 1.2; }
        .area-header p { font-size: 0.85rem; margin-bottom: 0; line-height: 1.3; }

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
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .grafico-container {
            margin: 0.75rem 0;
            padding: 0.85rem;
            background: white;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
        }

        .grafico-container h5 {
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
            color: #334155;
            line-height: 1.2;
        }

        .resumen-ejecutivo {
            line-height: 1.6;
            font-size: 0.95rem;
            background: #f8fafc;
            padding: 1.25rem;
            border-radius: 4px;
            border-left: 3px solid #1e40af;
            white-space: pre-line;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .badge {
            font-size: 0.8rem;
            padding: 0.35rem 0.75rem;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Botón flotante para guardar PDF */
        .pdf-button {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            background: #dc2626;
            color: white;
            padding: 15px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
            border: none;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: pulse 2s infinite;
        }

        .pdf-button:hover {
            background: #b91c1c;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(220, 38, 38, 0.5);
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .pdf-instructions {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9998;
            background: #fef3c7;
            border: 2px solid #fbbf24;
            border-radius: 8px;
            padding: 15px;
            max-width: 300px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .pdf-instructions h4 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #92400e;
        }

        .pdf-instructions ol {
            margin: 0;
            padding-left: 20px;
            font-size: 13px;
            color: #78350f;
        }

        .pdf-instructions li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

<!-- Botón flotante para guardar PDF -->
<button class="pdf-button no-print" onclick="window.print()">
    <i class="ti ti-file-type-pdf"></i>
    💾 Guardar como PDF
</button>

<!-- Instrucciones -->
<div class="pdf-instructions no-print">
    <h4>📄 Cómo guardar como PDF:</h4>
    <ol>
        <li>Espera 3 segundos (cargan gráficos)</li>
        <li>Clic en el botón rojo arriba</li>
        <li>Selecciona "Guardar como PDF"</li>
        <li>Clic en "Guardar"</li>
    </ol>
</div>

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
            $texto = $reporte['resumen_ejecutivo'];
            $texto = preg_replace('/\n{3,}/', "\n\n", $texto);
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
                        $tipoNormalizado = str_replace('_', '-', $grafico['tipo']);
                        $graficoPath = __DIR__ . '/../../views/components/charts/' . $tipoNormalizado . '.php';
                        if (file_exists($graficoPath)) {
                            $chartComponent = require $graficoPath;
                            $config = json_decode($grafico['configuracion'], true);

                            $config['_periodo_limite'] = [
                                'anio' => $reporte['anio'],
                                'mes' => $reporte['mes'],
                                'periodo_id' => $periodo ? $periodo['id'] : null
                            ];

                            $metrica_data = null;
                            if (isset($config['metrica_id']) && $periodo) {
                                $metrica_data = obtenerDatosMetrica($config['metrica_id'], $periodo['id']);
                            }

                            if (isset($chartComponent['render']) && is_callable($chartComponent['render'])) {
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Esperar a que todo cargue (especialmente gráficos)
let isReady = false;

window.addEventListener('load', function() {
    setTimeout(function() {
        isReady = true;
        console.log('✅ PDF listo para guardar');

        // Cambiar color del botón a verde cuando esté listo
        const btn = document.querySelector('.pdf-button');
        if (btn) {
            btn.style.background = '#16a34a';
            btn.innerHTML = '<i class="ti ti-check"></i> ✅ Listo - Guardar PDF';
        }
    }, 3000);
});

// Mejorar impresión de gráficos
window.addEventListener('beforeprint', function() {
    console.log('📄 Preparando PDF...');

    if (!isReady) {
        alert('⏳ Espera 3 segundos para que los gráficos terminen de cargar');
        return false;
    }
});

window.addEventListener('afterprint', function() {
    console.log('✅ PDF guardado');
});
</script>

</body>
</html>
