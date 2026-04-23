<?php
/**
 * API Endpoint: Valores de Métricas
 */

use App\Models\ValorMetrica;
use App\Models\Metrica;
use App\Services\PermissionService;

$valorModel = new ValorMetrica();
$metricaModel = new Metrica();

// GET /valores/historico - Obtener histórico
if ($method === 'GET' && ($segments[1] ?? '') === 'historico') {
    $metrica_id = $_GET['metrica_id'] ?? null;
    $periodos = (int)($_GET['periodos'] ?? 12);

    if (!$metrica_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Bad Request', 'message' => 'metrica_id requerido']);
        exit;
    }

    // Verificar permisos
    $metrica = $metricaModel->find($metrica_id);
    if (!$metrica || !PermissionService::canViewArea($user, $metrica['area_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }

    $historico = $valorModel->getHistorico($metrica_id, $periodos);

    echo json_encode(['success' => true, 'data' => $historico]);
    exit;
}

// GET /valores?metrica_id=X&periodo_id=Y - Obtener valores
if ($method === 'GET') {
    $metrica_id = $_GET['metrica_id'] ?? null;
    $periodo_id = $_GET['periodo_id'] ?? null;

    if ($metrica_id && $periodo_id) {
        // Un valor específico
        $metrica = $metricaModel->find($metrica_id);
        if (!$metrica || !PermissionService::canViewArea($user, $metrica['area_id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }

        $valor = $valorModel->getValor($metrica_id, $periodo_id);

        echo json_encode(['success' => true, 'data' => $valor]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Bad Request', 'message' => 'metrica_id y periodo_id requeridos']);
    }
    exit;
}

// POST /valores - Crear/Actualizar valor
if ($method === 'POST') {
    // Solo admins pueden modificar valores
    if (!in_array($user['rol'], ['super_admin', 'dept_admin'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden', 'message' => 'Se requiere rol admin']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    $required = ['metrica_id', 'periodo_id', 'valor'];
    foreach ($required as $field) {
        if (!isset($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => 'Bad Request', 'message' => "Campo '$field' requerido"]);
            exit;
        }
    }

    $metrica = $metricaModel->find($input['metrica_id']);
    if (!$metrica) {
        http_response_code(404);
        echo json_encode(['error' => 'Not Found', 'message' => 'Métrica no encontrada']);
        exit;
    }

    if (!PermissionService::canEditArea($user, $metrica['area_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }

    // Guardar valor
    $data = [
        'metrica_id' => $input['metrica_id'],
        'periodo_id' => $input['periodo_id'],
        'nota' => $input['nota'] ?? null,
        'usuario_registro_id' => $user['id']
    ];

    if ($metrica['tipo_valor'] === 'decimal') {
        $data['valor_decimal'] = (float)$input['valor'];
        $data['valor_numero'] = null;
    } else {
        $data['valor_numero'] = (int)$input['valor'];
        $data['valor_decimal'] = null;
    }

    $id = $valorModel->guardar(
        $input['metrica_id'],
        $input['periodo_id'],
        $data
    );

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'data' => ['id' => $id],
        'message' => 'Valor guardado correctamente'
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method Not Allowed']);
