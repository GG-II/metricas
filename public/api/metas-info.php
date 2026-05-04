<?php
/**
 * API para obtener información de metas (valor sugerido, máximo permitido, etc.)
 */

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Services\MetaValidatorService;

// Verificar autenticación
AuthMiddleware::handle();

header('Content-Type: application/json');

$metrica_id = $_GET['metrica_id'] ?? null;
$ejercicio = $_GET['ejercicio'] ?? null;
$tipo_meta = $_GET['tipo_meta'] ?? 'mensual';

if (!$metrica_id || !$ejercicio) {
    http_response_code(400);
    echo json_encode(['error' => 'metrica_id y ejercicio son requeridos']);
    exit;
}

try {
    $validator = new MetaValidatorService();

    if ($tipo_meta === 'anual') {
        // Para meta anual, solo verificar si ya existe
        $meta_anual = $validator->getMetaAnual($metrica_id, $ejercicio);
        $suma_mensual = $validator->getSumaMensual($metrica_id, $ejercicio);

        echo json_encode([
            'success' => true,
            'data' => [
                'existe_meta_anual' => (bool)$meta_anual,
                'suma_mensual' => $suma_mensual,
                'advertencia' => $suma_mensual > 0 ?
                    "Ya existen metas mensuales que suman $suma_mensual. La meta anual debe ser mayor o igual." :
                    null
            ]
        ]);
    } else {
        // Para meta mensual, obtener información completa
        $info = $validator->getInfoCompletaMetas($metrica_id, $ejercicio);

        echo json_encode([
            'success' => true,
            'data' => $info
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
