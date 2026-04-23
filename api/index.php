<?php
/**
 * API REST - Entry Point
 * Enrutador principal de la API
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\ApiAuthService;

// Obtener configuración
$config = require __DIR__ . '/../config/database.php';
$dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
$db = new PDO($dsn, $config['username'], $config['password'], $config['options']);

// Parsear ruta
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/metricas/api';
$path = str_replace($base_path, '', parse_url($request_uri, PHP_URL_PATH));
$path = trim($path, '/');
$segments = explode('/', $path);

$method = $_SERVER['REQUEST_METHOD'];

// Rutas públicas (sin autenticación)
$public_routes = [
    '' => true, // Documentación
    'health' => true,
    'auth/login' => true
];

$is_public = isset($public_routes[$path]);

// Autenticación
$user = null;
if (!$is_public) {
    $authService = new ApiAuthService($db);
    $token = ApiAuthService::extractTokenFromHeader();

    $user = $authService->validateToken($token);

    if (!$user) {
        http_response_code(401);
        echo json_encode([
            'error' => 'Unauthorized',
            'message' => 'Token de API inválido o expirado'
        ]);
        exit;
    }
}

// Router
try {
    // Documentación
    if ($path === '') {
        require __DIR__ . '/docs.php';
        exit;
    }

    // Health check
    if ($path === 'health') {
        echo json_encode([
            'status' => 'ok',
            'version' => '1.0',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }

    // Rutas de recursos
    $resource = $segments[0] ?? '';

    switch ($resource) {
        case 'metricas':
            require __DIR__ . '/endpoints/metricas.php';
            break;

        case 'valores':
            require __DIR__ . '/endpoints/valores.php';
            break;

        case 'periodos':
            require __DIR__ . '/endpoints/periodos.php';
            break;

        case 'areas':
            require __DIR__ . '/endpoints/areas.php';
            break;

        case 'departamentos':
            require __DIR__ . '/endpoints/departamentos.php';
            break;

        case 'metas':
            require __DIR__ . '/endpoints/metas.php';
            break;

        default:
            http_response_code(404);
            echo json_encode([
                'error' => 'Not Found',
                'message' => "Recurso '$resource' no encontrado"
            ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal Server Error',
        'message' => $e->getMessage()
    ]);
}
