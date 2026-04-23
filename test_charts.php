<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Utils\ChartRegistry;

echo "=== PRUEBA DE CARGA DE GRÁFICOS ===\n\n";

try {
    ChartRegistry::load();
    $charts = ChartRegistry::getAll();

    echo "Gráficos cargados: " . count($charts) . "\n\n";

    foreach ($charts as $id => $chart) {
        $meta = $chart['meta'] ?? [];
        echo "✓ {$id}\n";
        echo "  Nombre: " . ($meta['nombre'] ?? 'N/A') . "\n";
        echo "  Descripción: " . ($meta['descripcion'] ?? 'N/A') . "\n";
        echo "  Requiere métricas: " . ($meta['requiere_metricas'] ?? 0) . "\n";
        echo "\n";
    }

    echo "=== PRUEBA ESPECÍFICA: line-with-goal ===\n\n";

    $lineGoal = $charts['line_with_goal'] ?? null;
    if ($lineGoal) {
        echo "✓ Gráfico encontrado\n";
        echo "  Metadata: OK\n";
        echo "  Tiene form: " . (isset($lineGoal['form']) ? "SÍ" : "NO") . "\n";
        echo "  Tiene process: " . (isset($lineGoal['process']) ? "SÍ" : "NO") . "\n";
        echo "  Tiene render: " . (isset($lineGoal['render']) ? "SÍ" : "NO") . "\n";
        echo "  Tiene load_config_js: " . (isset($lineGoal['load_config_js']) ? "SÍ" : "NO") . "\n";
    } else {
        echo "✗ Gráfico line_with_goal NO encontrado\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n=== FIN ===\n";
