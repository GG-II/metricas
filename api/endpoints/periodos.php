<?php
/**
 * API Endpoint: Períodos
 */

use App\Models\Periodo;

$periodoModel = new Periodo();
$id = $segments[1] ?? null;

// GET /periodos/actual - Período actual
if ($method === 'GET' && $id === 'actual') {
    $actual = $periodoModel->getPeriodoActual();

    if (!$actual) {
        http_response_code(404);
        echo json_encode(['error' => 'Not Found', 'message' => 'No hay período actual configurado']);
        exit;
    }

    echo json_encode(['success' => true, 'data' => $actual]);
    exit;
}

// GET /periodos - Listar
if ($method === 'GET' && !$id) {
    $ejercicio = $_GET['ejercicio'] ?? null;
    $activos = $_GET['activos'] ?? '1';

    $stmt = $db->prepare("
        SELECT * FROM periodos
        WHERE activo = ?
        " . ($ejercicio ? "AND ejercicio = $ejercicio" : "") . "
        ORDER BY ejercicio DESC, periodo DESC
        LIMIT 100
    ");
    $stmt->execute([$activos]);
    $periodos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $periodos]);
    exit;
}

// GET /periodos/{id} - Obtener uno
if ($method === 'GET' && $id) {
    $periodo = $periodoModel->find($id);

    if (!$periodo) {
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
        exit;
    }

    echo json_encode(['success' => true, 'data' => $periodo]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method Not Allowed']);
