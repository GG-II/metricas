<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Grafico;

AuthMiddleware::handle();
PermissionMiddleware::requireAdmin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['area_id']) || !isset($data['items'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$area_id = (int)$data['area_id'];
$items = $data['items'];

// Verificar permiso para editar el área
$user = getCurrentUser();
if (!App\Services\PermissionService::canEditArea($user, $area_id)) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit;
}

try {
    $graficoModel = new Grafico();
    $actualizados = $graficoModel->guardarPosiciones($area_id, $items);

    echo json_encode([
        'success' => true,
        'message' => "$actualizados gráficos actualizados",
        'count' => $actualizados
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
