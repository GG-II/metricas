<?php
// Capturar TODOS los errores y devolverlos como JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Error fatal de PHP: ' . $error['message']
        ]);
    }
});

session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Services\PermissionService;
use App\Models\Reporte;

header('Content-Type: application/json');

try {
    AuthMiddleware::handle();
    $user = getCurrentUser();

    $input = json_decode(file_get_contents('php://input'), true);

    $reporte_id = $input['reporte_id'] ?? null;
    $grafico_id = $input['grafico_id'] ?? null;

    if (!$reporte_id || !$grafico_id) {
        throw new Exception('reporte_id y grafico_id son requeridos');
    }

    // Verificar que el reporte existe y el usuario tiene permisos
    $reporteModel = new Reporte();
    $reporte = $reporteModel->find($reporte_id);

    if (!$reporte) {
        throw new Exception('Reporte no encontrado');
    }

    if (!PermissionService::canEditArea($user, $reporte['area_id'])) {
        throw new Exception('No tienes permiso para editar este reporte');
    }

    // Insertar relación
    $reporteModel->insertarGrafico($reporte_id, $grafico_id);

    echo json_encode([
        'success' => true,
        'message' => 'Gráfico vinculado al reporte correctamente'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
