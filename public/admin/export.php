<?php
/**
 * Controlador de Exportación
 */

session_start();
require_once '../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Services\ExportService;
use App\Services\PermissionService;

AuthMiddleware::handle();

$user = getCurrentUser();
$db = getDB();
$exportService = new ExportService($db);

$formato = $_GET['formato'] ?? 'csv'; // csv, html
$metrica_ids = isset($_GET['metricas']) ? explode(',', $_GET['metricas']) : [];
$area_id = $_GET['area_id'] ?? null;
$periodos = (int)($_GET['periodos'] ?? 12);

// Validaciones
if (empty($metrica_ids)) {
    die('Error: No se especificaron métricas para exportar');
}

// Verificar permisos sobre las métricas
foreach ($metrica_ids as $metrica_id) {
    $stmt = $db->prepare("SELECT area_id FROM metricas WHERE id = ?");
    $stmt->execute([$metrica_id]);
    $metrica = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$metrica || !PermissionService::canViewArea($user, $metrica['area_id'])) {
        die('Error: No tienes permiso para exportar una o más métricas seleccionadas');
    }
}

// Exportar
try {
    if ($formato === 'csv') {
        $exportService->exportToCSV($metrica_ids, $periodos, $area_id);
    } elseif ($formato === 'html') {
        $html = $exportService->exportToPrintableHTML($metrica_ids, $periodos, $area_id);
        echo $html;
    } else {
        die('Error: Formato no soportado');
    }
} catch (Exception $e) {
    die('Error al exportar: ' . $e->getMessage());
}
