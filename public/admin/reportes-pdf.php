<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
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

// Por ahora, simplemente redirigir a la vista normal
// TODO: Implementar generación de PDF real con TCPDF o similar
header('Location: reportes-view.php?id=' . $reporte_id);
exit;

// NOTA: Para implementación futura de PDF:
// 1. Usar TCPDF o mPDF
// 2. Generar portada con logo de cooperativa
// 3. Incluir resumen ejecutivo
// 4. Capturar gráficos como imágenes (usar ApexCharts export)
// 5. Agregar pie de página con número de página
?>
