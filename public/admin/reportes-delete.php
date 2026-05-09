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

try {
    $reporte_id = $_GET['id'] ?? null;

    if (!$reporte_id) {
        throw new Exception('ID de reporte no especificado');
    }

    // Obtener reporte
    $reporte = $reporteModel->find($reporte_id);

    if (!$reporte) {
        throw new Exception('Reporte no encontrado');
    }

    // Verificar permisos (los reportes son por departamento, no por área)
    $canDelete = false;

    if ($user['rol'] === 'super_admin') {
        $canDelete = true;
    } elseif ($user['rol'] === 'dept_admin' && $user['departamento_id'] == $reporte['departamento_id']) {
        $canDelete = true;
    }

    if (!$canDelete) {
        throw new Exception('No tienes permiso para eliminar este reporte');
    }

    // Eliminar reporte
    $reporteModel->delete($reporte_id);

    // Redirigir con mensaje de éxito
    $_SESSION['success_message'] = 'Reporte eliminado correctamente';
    header('Location: reportes.php');
    exit;

} catch (Exception $e) {
    // Redirigir con mensaje de error
    $_SESSION['error_message'] = $e->getMessage();
    header('Location: reportes.php');
    exit;
}
