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
use App\Models\Grafico;

header('Content-Type: application/json');

try {
    AuthMiddleware::handle();
    $user = getCurrentUser();

    $area_id = $_GET['area_id'] ?? null;

    if (!$area_id) {
        throw new Exception('area_id es requerido');
    }

    // Verificar permisos
    if (!PermissionService::canViewArea($user, $area_id)) {
        throw new Exception('No tienes permiso para ver las gráficas de esta área');
    }

    // Obtener gráficas del área
    $graficoModel = new Grafico();
    $graficos = $graficoModel->getByArea($area_id);

    echo json_encode([
        'success' => true,
        'graficos' => $graficos
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
