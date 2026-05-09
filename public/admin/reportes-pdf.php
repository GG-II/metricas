<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Models\Reporte;
use App\Models\Periodo;
use Dompdf\Dompdf;
use Dompdf\Options;

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

// Configurar Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');
$options->set('defaultMediaType', 'print');
$options->set('isFontSubsettingEnabled', true);

$dompdf = new Dompdf($options);

// Generar HTML para el PDF
ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><?php echo htmlspecialchars($reporte['titulo']); ?></title>
    <style>
        @page {
            margin: 2cm 1.5cm;
            size: letter;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.5;
            color: #1a1a1a;
        }

        .portada {
            text-align: center;
            padding: 3cm 2cm;
            background: #1e40af;
            color: white;
            border-radius: 8px;
            margin-bottom: 2cm;
        }

        .portada-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1cm;
            border: 3px solid rgba(255, 255, 255, 0.4);
            font-size: 32pt;
        }

        .portada h1 {
            font-size: 18pt;
            margin: 0 0 0.5cm 0;
            font-weight: bold;
            color: white;
        }

        .portada h2 {
            font-size: 14pt;
            margin: 0 0 0.3cm 0;
            font-weight: normal;
            color: white;
        }

        .portada h3 {
            font-size: 12pt;
            margin: 0 0 0.5cm 0;
            color: white;
        }

        .portada .descripcion {
            font-size: 9pt;
            opacity: 0.9;
            margin-top: 0.5cm;
        }

        .badge {
            display: inline-block;
            padding: 0.3cm 0.6cm;
            background: rgba(255, 255, 255, 0.25);
            color: white;
            border-radius: 4px;
            font-size: 9pt;
            font-weight: bold;
            margin-top: 0.5cm;
        }

        h2 {
            font-size: 14pt;
            margin: 1.5cm 0 0.5cm 0;
            color: #1e40af;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 0.2cm;
        }

        h3 {
            font-size: 12pt;
            margin: 1cm 0 0.3cm 0;
            color: #334155;
        }

        .resumen-ejecutivo {
            background: #f8fafc;
            padding: 1cm;
            border-left: 4px solid #1e40af;
            border-radius: 4px;
            margin-bottom: 1cm;
            white-space: pre-line;
            line-height: 1.6;
        }

        .area-section {
            margin-bottom: 1.5cm;
            padding: 0.8cm;
            background: #f8fafc;
            border-left: 4px solid #3b82f6;
            border-radius: 4px;
            page-break-inside: avoid;
        }

        .area-header {
            margin-bottom: 0.5cm;
            padding-bottom: 0.3cm;
            border-bottom: 1px solid #cbd5e1;
        }

        .area-header h3 {
            margin: 0;
            font-size: 12pt;
            color: #1e293b;
        }

        .grafico-placeholder {
            padding: 1cm;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            margin: 0.5cm 0;
            text-align: center;
            color: #64748b;
            font-style: italic;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1cm;
            text-align: center;
            font-size: 8pt;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 0.2cm;
        }

        .page-number:before {
            content: "Página " counter(page);
        }
    </style>
</head>
<body>

<!-- PORTADA -->
<div class="portada">
    <div class="portada-icon">📊</div>
    <h1><?php echo htmlspecialchars($reporte['titulo']); ?></h1>
    <h2><?php echo htmlspecialchars($reporte['departamento_nombre']); ?></h2>
    <h3><?php echo $meses[$reporte['mes']] . ' ' . $reporte['anio']; ?></h3>
    <?php if ($reporte['descripcion']): ?>
    <p class="descripcion"><?php echo htmlspecialchars($reporte['descripcion']); ?></p>
    <?php endif; ?>
    <span class="badge"><?php echo ucfirst($reporte['estado']); ?></span>
</div>

<!-- RESUMEN EJECUTIVO -->
<?php if (!empty($reporte['resumen_ejecutivo'])): ?>
<h2>Resumen Ejecutivo</h2>
<div class="resumen-ejecutivo"><?php
    $texto = $reporte['resumen_ejecutivo'];
    $texto = preg_replace('/\n{3,}/', "\n\n", $texto);
    echo htmlspecialchars($texto);
?></div>
<?php endif; ?>

<!-- DETALLE POR ÁREA -->
<h2>Detalle por Área</h2>
<p style="text-align: center; color: #64748b; font-size: 9pt; margin-bottom: 1cm;">
    Datos al cierre de <?php echo $meses[$reporte['mes']] . ' ' . $reporte['anio']; ?>
</p>

<?php if (!empty($reporte['areas'])): ?>
    <?php foreach ($reporte['areas'] as $area): ?>
    <div class="area-section">
        <div class="area-header">
            <h3><?php echo htmlspecialchars($area['nombre']); ?></h3>
            <?php if (!empty($area['descripcion'])): ?>
            <p style="color: #64748b; font-size: 9pt; margin: 0.2cm 0 0 0;">
                <?php echo htmlspecialchars($area['descripcion']); ?>
            </p>
            <?php endif; ?>
        </div>

        <?php if (!empty($area['graficos'])): ?>
            <?php foreach ($area['graficos'] as $idx => $grafico): ?>
            <div class="grafico-placeholder">
                <strong><?php echo htmlspecialchars($grafico['titulo']); ?></strong><br>
                <small>Gráfico interactivo - Consulte la versión web para visualización completa</small>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #64748b; font-style: italic;">No hay gráficos configurados para esta área</p>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <p style="text-align: center; color: #64748b; font-style: italic;">
        No hay áreas configuradas para este departamento
    </p>
<?php endif; ?>

<!-- PIE DE PÁGINA -->
<div class="footer">
    <div>
        Generado por: <?php echo htmlspecialchars($reporte['autor_nombre'] ?? 'N/A'); ?> |
        Fecha: <?php echo date('d/m/Y H:i', strtotime($reporte['created_at'])); ?> |
        <span class="page-number"></span>
    </div>
</div>

</body>
</html>
<?php
$html = ob_get_clean();

// Cargar HTML en Dompdf
$dompdf->loadHtml($html);

// Configurar papel
$dompdf->setPaper('letter', 'portrait');

// Renderizar PDF
$dompdf->render();

// Nombre del archivo
$filename = preg_replace('/[^A-Za-z0-9-]+/', '-', $reporte['titulo']);
$filename = strtolower(trim($filename, '-'));
$filename = substr($filename, 0, 50);
$filename = $filename . '_' . $reporte['anio'] . '_' . $reporte['mes'] . '.pdf';

// Enviar al navegador
$dompdf->stream($filename, [
    'Attachment' => true, // true = descargar, false = ver en navegador
    'compress' => true
]);

exit;
?>
