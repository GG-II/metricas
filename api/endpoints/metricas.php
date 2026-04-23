<?php
/**
 * API Endpoint: Métricas
 */

use App\Models\Metrica;
use App\Services\PermissionService;

$metricaModel = new Metrica();
$id = $segments[1] ?? null;

// GET /metricas - Listar
if ($method === 'GET' && !$id) {
    $area_id = $_GET['area_id'] ?? null;

    if ($area_id) {
        // Verificar permisos
        if (!PermissionService::canViewArea($user, $area_id)) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden', 'message' => 'No tienes acceso a esta área']);
            exit;
        }

        $metricas = $metricaModel->getByArea($area_id);
    } else {
        // Obtener métricas según permisos del usuario
        $areas_permitidas = PermissionService::getAreasPermitidas($user);
        $area_ids = array_column($areas_permitidas, 'id');

        if (empty($area_ids)) {
            echo json_encode(['success' => true, 'data' => []]);
            exit;
        }

        $stmt = $db->prepare("
            SELECT * FROM metricas
            WHERE area_id IN (" . implode(',', array_fill(0, count($area_ids), '?')) . ")
            AND activo = 1
            ORDER BY orden, nombre
        ");
        $stmt->execute($area_ids);
        $metricas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode(['success' => true, 'data' => $metricas]);
    exit;
}

// GET /metricas/{id} - Obtener una
if ($method === 'GET' && $id) {
    $metrica = $metricaModel->find($id);

    if (!$metrica) {
        http_response_code(404);
        echo json_encode(['error' => 'Not Found', 'message' => 'Métrica no encontrada']);
        exit;
    }

    // Verificar permisos
    if (!PermissionService::canViewArea($user, $metrica['area_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }

    echo json_encode(['success' => true, 'data' => $metrica]);
    exit;
}

// POST /metricas - Crear (solo admin)
if ($method === 'POST') {
    if (!in_array($user['rol'], ['super_admin', 'dept_admin'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden', 'message' => 'Se requiere rol admin']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    $required = ['nombre', 'area_id', 'unidad'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => 'Bad Request', 'message' => "Campo '$field' requerido"]);
            exit;
        }
    }

    // Verificar permisos sobre el área
    if (!PermissionService::canEditArea($user, $input['area_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }

    $id = $metricaModel->create($input);

    http_response_code(201);
    echo json_encode(['success' => true, 'data' => ['id' => $id]]);
    exit;
}

// PUT /metricas/{id} - Actualizar
if ($method === 'PUT' && $id) {
    if (!in_array($user['rol'], ['super_admin', 'dept_admin'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }

    $metrica = $metricaModel->find($id);
    if (!$metrica) {
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
        exit;
    }

    if (!PermissionService::canEditArea($user, $metrica['area_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $metricaModel->update($id, $input);

    echo json_encode(['success' => true, 'message' => 'Métrica actualizada']);
    exit;
}

// DELETE /metricas/{id} - Eliminar
if ($method === 'DELETE' && $id) {
    if (!in_array($user['rol'], ['super_admin', 'dept_admin'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }

    $metrica = $metricaModel->find($id);
    if (!$metrica) {
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
        exit;
    }

    if (!PermissionService::canEditArea($user, $metrica['area_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }

    $metricaModel->delete($id);

    echo json_encode(['success' => true, 'message' => 'Métrica eliminada']);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method Not Allowed']);
