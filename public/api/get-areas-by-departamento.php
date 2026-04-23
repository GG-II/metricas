<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Models\Area;

AuthMiddleware::handle();

header('Content-Type: application/json');

if (!isset($_GET['departamento_id'])) {
    echo json_encode(['success' => false, 'message' => 'Departamento no especificado']);
    exit;
}

$departamento_id = (int)$_GET['departamento_id'];

try {
    $areaModel = new Area();
    $areas = $areaModel->getByDepartamento($departamento_id);

    echo json_encode([
        'success' => true,
        'areas' => $areas
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
