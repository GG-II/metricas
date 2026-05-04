<?php
/**
 * Suite de Tests - Tipos de Departamento
 *
 * Ejecuta todos los tests relacionados con la feature de tipos de departamento
 */

echo "\n";
echo "╔════════════════════════════════════════════════╗\n";
echo "║  SUITE DE TESTS - TIPOS DE DEPARTAMENTO       ║\n";
echo "║  Feature: Agencias, Corporativo y Global      ║\n";
echo "╚════════════════════════════════════════════════╝\n";
echo "\n";

$tests = [
    'DepartamentoTipoTest.php' => 'Modelo Departamento - Métodos por Tipo',
    'AreaGlobalDetectionTest.php' => 'Detección de Áreas Globales',
    'MetricasDisponiblesTest.php' => 'Métricas Disponibles - Filtrado',
];

$passed = 0;
$failed = 0;
$warnings = 0;

foreach ($tests as $file => $description) {
    echo "┌────────────────────────────────────────────────┐\n";
    echo "│ Test: $description\n";
    echo "│ Archivo: $file\n";
    echo "└────────────────────────────────────────────────┘\n\n";

    $test_path = __DIR__ . '/' . $file;

    if (!file_exists($test_path)) {
        echo "❌ ERROR: Archivo de test no encontrado: $file\n\n";
        $failed++;
        continue;
    }

    ob_start();
    $exit_code = 0;

    try {
        include $test_path;
    } catch (Exception $e) {
        $output = ob_get_clean();
        echo $output;
        echo "\n❌ FALLÓ CON EXCEPCIÓN:\n";
        echo "   " . $e->getMessage() . "\n";
        echo "   Archivo: " . $e->getFile() . "\n";
        echo "   Línea: " . $e->getLine() . "\n\n";
        $failed++;
        continue;
    }

    $output = ob_get_clean();
    echo $output;

    // Contar warnings
    $warning_count = substr_count($output, '⚠️');
    if ($warning_count > 0) {
        $warnings += $warning_count;
    }

    // Verificar si pasó
    if (strpos($output, '✅') !== false && strpos($output, 'PASARON') !== false) {
        $passed++;
        echo "✅ Test completado exitosamente\n";
    } else if (strpos($output, '❌') !== false) {
        $failed++;
        echo "❌ Test falló\n";
    } else {
        $passed++;
        echo "✓ Test completado\n";
    }

    echo "\n";
}

echo "╔════════════════════════════════════════════════╗\n";
echo "║              RESUMEN DE TESTS                  ║\n";
echo "╚════════════════════════════════════════════════╝\n\n";

echo "Total de tests: " . count($tests) . "\n";
echo "✅ Pasaron: $passed\n";
echo "❌ Fallaron: $failed\n";

if ($warnings > 0) {
    echo "⚠️  Advertencias: $warnings\n";
}

echo "\n";

if ($failed === 0) {
    echo "╔════════════════════════════════════════════════╗\n";
    echo "║         🎉 TODOS LOS TESTS PASARON 🎉        ║\n";
    echo "╚════════════════════════════════════════════════╝\n";
    echo "\n";
    exit(0);
} else {
    echo "╔════════════════════════════════════════════════╗\n";
    echo "║          ❌ ALGUNOS TESTS FALLARON ❌        ║\n";
    echo "╚════════════════════════════════════════════════╝\n";
    echo "\n";
    exit(1);
}
