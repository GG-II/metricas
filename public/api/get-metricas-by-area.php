<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Services\PermissionService;
use App\Services\MetricaCalculadaService;
use App\Models\Metrica;
use App\Models\Area;

// Verificar autenticación
AuthMiddleware::handle();

header('Content-Type: application/json');

$user = getCurrentUser();
$metricaModel = new Metrica();
$areaModel = new Area();
$calculadaService = new MetricaCalculadaService();

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

// Verificar si es área global
$area = $areaModel->find($area_id);
$es_area_global = ($area && $area['slug'] === 'metricas-consolidadas');

// Si es área global, solo super_admin puede acceder
if ($es_area_global && $user['rol'] !== 'super_admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Solo super_admin puede acceder a métricas globales']);
    exit;
}

// Obtener métricas disponibles (usa el servicio que ya detecta si es global)
$metricas = $calculadaService->getMetricasDisponibles($area_id);

echo json_encode([
    'success' => true,
    'data' => $metricas,
    'is_global' => $es_area_global
]);
