<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Services\PermissionService;
use App\Models\Reporte;
use App\Utils\Parsedown;

AuthMiddleware::handle();

$user = getCurrentUser();
$reporteModel = new Reporte();
$parsedown = new Parsedown();

$reporte_id = $_GET['id'] ?? null;

if (!$reporte_id) {
    die('ID de reporte no especificado');
}

$reporte = $reporteModel->findWithFullDetails($reporte_id);

if (!$reporte) {
    die('Reporte no encontrado');
}

// Convertir Markdown a HTML
$contenidoHTML = $parsedown->text($reporte['contenido'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($reporte['titulo']); ?></title>

    <!-- GitHub Markdown CSS -->
    <link href="https://cdn.jsdelivr.net/npm/github-markdown-css@5.5.0/github-markdown-light.min.css" rel="stylesheet">

    <style>
        @page {
            size: A4;
            margin: 2cm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 20px;
            font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
            background: white;
            color: black;
        }

        .pdf-container {
            max-width: 21cm;
            margin: 0 auto;
            background: white;
            padding: 40px;
        }

        .pdf-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e1e4e8;
        }

        .pdf-title {
            font-size: 28pt;
            font-weight: 700;
            margin: 0 0 15px 0;
            color: #24292f;
        }

        .pdf-meta {
            font-size: 11pt;
            color: #57606a;
            line-height: 1.8;
        }

        .pdf-meta strong {
            color: #24292f;
        }

        .markdown-body {
            font-size: 11pt;
            line-height: 1.6;
            color: #24292f;
        }

        .markdown-body h1 {
            font-size: 20pt;
            margin-top: 24px;
            margin-bottom: 16px;
            border-bottom: 1px solid #e1e4e8;
            padding-bottom: 8px;
        }

        .markdown-body h2 {
            font-size: 16pt;
            margin-top: 20px;
            margin-bottom: 12px;
        }

        .markdown-body h3 {
            font-size: 14pt;
            margin-top: 16px;
            margin-bottom: 8px;
        }

        .markdown-body p {
            margin-bottom: 12px;
            text-align: justify;
        }

        .markdown-body ul, .markdown-body ol {
            margin-bottom: 12px;
            padding-left: 30px;
        }

        .markdown-body li {
            margin-bottom: 4px;
        }

        .markdown-body code {
            background: #f6f8fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10pt;
        }

        .markdown-body pre {
            background: #f6f8fa;
            padding: 16px;
            border-radius: 6px;
            overflow-x: auto;
            margin: 16px 0;
        }

        .markdown-body blockquote {
            border-left: 4px solid #0969da;
            padding-left: 16px;
            margin: 16px 0;
            color: #57606a;
        }

        .markdown-body table {
            border-collapse: collapse;
            width: 100%;
            margin: 16px 0;
        }

        .markdown-body table th,
        .markdown-body table td {
            border: 1px solid #d0d7de;
            padding: 8px 12px;
            text-align: left;
        }

        .markdown-body table th {
            background: #f6f8fa;
            font-weight: 600;
        }

        .pdf-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e1e4e8;
            text-align: center;
            font-size: 9pt;
            color: #57606a;
        }

        .no-print {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #0969da;
            color: white;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
        }

        .no-print:hover {
            background: #0860ca;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                padding: 0;
            }

            .pdf-container {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()">
        📄 Guardar como PDF
    </button>

    <div class="pdf-container">
        <div class="pdf-header">
            <h1 class="pdf-title"><?php echo e($reporte['titulo']); ?></h1>

            <div class="pdf-meta">
                <strong><?php echo e($reporte['departamento_nombre']); ?></strong> -
                <?php echo e($reporte['area_nombre']); ?>
                <br>

                <?php if ($reporte['periodo_nombre']): ?>
                    Período: <?php echo e($reporte['periodo_nombre']); ?> <?php echo $reporte['anio']; ?>
                    <br>
                <?php endif; ?>

                <?php if ($reporte['autor_nombre']): ?>
                    Autor: <?php echo e($reporte['autor_nombre']); ?>
                    <br>
                <?php endif; ?>

                Generado: <?php echo date('d/m/Y H:i'); ?>
            </div>
        </div>

        <div class="markdown-body">
            <?php echo $contenidoHTML; ?>
        </div>

        <div class="pdf-footer">
            <small>
                <?php echo e($reporte['titulo']); ?> -
                <?php echo e($reporte['area_nombre']); ?> -
                Sistema de Métricas
            </small>
        </div>
    </div>

    <script>
        // Auto-abrir diálogo de impresión
        // window.onload = function() {
        //     setTimeout(() => window.print(), 500);
        // };
    </script>
</body>
</html>
