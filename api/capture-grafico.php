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
            'error' => 'Error fatal de PHP: ' . $error['message'] . ' en ' . $error['file'] . ':' . $error['line']
        ]);
    }
});

session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Services\PermissionService;
use App\Models\Grafico;
use App\Models\ValorMetrica;

header('Content-Type: application/json');

try {
    AuthMiddleware::handle();
    $user = getCurrentUser();

    $grafico_id = $_GET['grafico_id'] ?? null;

    if (!$grafico_id) {
        throw new Exception('grafico_id es requerido');
    }

    // Obtener gráfico
    $graficoModel = new Grafico();
    $grafico = $graficoModel->find($grafico_id);

    if (!$grafico) {
        throw new Exception('Gráfico no encontrado');
    }

    // Verificar permisos
    if (!PermissionService::canViewArea($user, $grafico['area_id'])) {
        throw new Exception('No tienes permiso para ver este gráfico');
    }

    // Parsear configuración
    $config = json_decode($grafico['configuracion'], true);

    if (!$config) {
        throw new Exception('Configuración de gráfico inválida');
    }

    // Manejar diferentes formatos de configuración
    $metricas = [];

    if (isset($config['metricas']) && is_array($config['metricas'])) {
        // Multi-métrica: array de objetos con id y color
        $metricas = array_map(function($m) {
            return is_array($m) ? $m['id'] : $m;
        }, $config['metricas']);
    } elseif (isset($config['metrica_id'])) {
        // Métrica única
        $metricas = [(int)$config['metrica_id']];
    }

    if (empty($metricas)) {
        throw new Exception('El gráfico no tiene métricas configuradas');
    }

    // Obtener datos de las métricas
    $valorMetricaModel = new ValorMetrica();
    $series = [];
    $categories = [];

    foreach ($metricas as $metrica_id) {
        // Obtener valores de la métrica
        $db = getDB();
        $stmt = $db->prepare("
            SELECT
                COALESCE(vm.valor_numero, vm.valor_decimal) as valor,
                m.nombre as metrica_nombre,
                p.nombre as periodo_nombre
            FROM valores_metricas vm
            INNER JOIN metricas m ON m.id = vm.metrica_id
            INNER JOIN periodos p ON p.id = vm.periodo_id
            WHERE vm.metrica_id = ?
            ORDER BY p.ejercicio, p.periodo
            LIMIT 12
        ");
        $stmt->execute([$metrica_id]);
        $valores = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($valores)) {
            $metricaNombre = $valores[0]['metrica_nombre'] ?? 'Métrica';
            $data = array_map(function($v) {
                return (float)($v['valor'] ?? 0);
            }, $valores);

            if (empty($categories)) {
                $categories = array_column($valores, 'periodo_nombre');
            }

            $series[] = [
                'name' => $metricaNombre,
                'data' => $data
            ];
        }
    }

    // Si no hay datos, devolver datos de ejemplo
    if (empty($series)) {
        $series = [[
            'name' => 'Sin datos',
            'data' => [0, 0, 0]
        ]];
        $categories = ['Ene', 'Feb', 'Mar'];
    }

    // Devolver configuración para renderizar en cliente
    echo json_encode([
        'success' => true,
        'chartConfig' => [
            'type' => $grafico['tipo_grafico'] ?? 'line',
            'series' => $series,
            'categories' => $categories,
            'title' => $grafico['nombre'] ?? 'Gráfico'
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
