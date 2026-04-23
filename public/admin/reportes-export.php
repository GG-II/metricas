<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Services\PermissionService;
use App\Models\Reporte;
use App\Services\ReporteExportService;

AuthMiddleware::handle();

$user = getCurrentUser();
$reporteModel = new Reporte();

try {
    // Obtener parámetros
    $reporte_id = $_GET['id'] ?? null;
    $format = $_GET['format'] ?? 'pdf';

    if (!$reporte_id) {
        throw new Exception('ID de reporte no especificado');
    }

    // Validar formato
    $formatos_permitidos = ['pdf', 'docx'];
    if (!in_array($format, $formatos_permitidos)) {
        throw new Exception('Formato no soportado. Use: pdf o docx');
    }

    // Obtener reporte con todos los detalles
    $reporte = $reporteModel->findWithFullDetails($reporte_id);

    if (!$reporte) {
        throw new Exception('Reporte no encontrado');
    }

    // Verificar permisos
    if (!PermissionService::canViewArea($user, $reporte['area_id'])) {
        throw new Exception('No tienes permiso para ver este reporte');
    }

    // Crear servicio de exportación
    $exportService = new ReporteExportService($reporte);

    // Exportar según el formato
    switch ($format) {
        case 'pdf':
            $result = $exportService->exportToPDFSimple(); // Usar Dompdf en lugar de TCPDF
            break;

        case 'docx':
            $result = $exportService->exportToDocx();
            break;

        default:
            throw new Exception('Formato no soportado');
    }

    // Descargar archivo
    ReporteExportService::download(
        $result['filepath'],
        $result['filename'],
        $result['mime']
    );

} catch (Exception $e) {
    // Si hay error, mostrar mensaje
    http_response_code(500);
    echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error de Exportación</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
        }
        .error-box {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 500px;
        }
        .error-box h1 {
            color: #dc3545;
            margin-top: 0;
        }
        .error-box p {
            color: #666;
        }
        .error-box a {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .error-box a:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="error-box">
        <h1>⚠️ Error de Exportación</h1>
        <p>' . htmlspecialchars($e->getMessage()) . '</p>
        <a href="javascript:history.back()">← Volver</a>
    </div>
</body>
</html>';
}
