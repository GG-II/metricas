<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Services\PermissionService;
use App\Models\Metrica;

// Verificar autenticación
AuthMiddleware::handle();

header('Content-Type: application/json');

$user = getCurrentUser();
$metricaModel = new Metrica();

$area_id = $_GET['area_id'] ?? null;

if (!$area_id) {
    echo json_encode(['success' => false, 'error' => 'area_id requerido']);
    exit;
}

// Verificar permisos
if (!PermissionService::canViewArea($user, $area_id)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No tienes acceso a esta área']);
    exit;
}

// Obtener métricas del área (activas)
$metricas = $metricaModel->getByArea($area_id, true);

echo json_encode(['success' => true, 'data' => $metricas]);
