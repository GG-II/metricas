<?php
/**
 * Test: Verificar funcionalidad de tipos de departamento
 *
 * Verifica que los métodos del modelo Departamento filtren correctamente por tipo
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Departamento;

echo "🧪 Test: Modelo Departamento - Métodos por Tipo\n";
echo "================================================\n\n";

try {
    $deptModel = new Departamento();

    // Test 1: Obtener agencias
    echo "[TEST 1] Obtener agencias...\n";
    $agencias = $deptModel->getAgencias();
    echo "✓ Obtenidas " . count($agencias) . " agencias\n";

    foreach ($agencias as $ag) {
        if ($ag['tipo'] !== 'agencia') {
            throw new Exception("ERROR: Departamento '{$ag['nombre']}' tiene tipo '{$ag['tipo']}' en lugar de 'agencia'");
        }
        echo "  - {$ag['nombre']} (tipo: {$ag['tipo']}) ✓\n";
    }
    echo "\n";

    // Test 2: Obtener corporativos
    echo "[TEST 2] Obtener departamentos corporativos...\n";
    $corporativos = $deptModel->getCorporativos();
    echo "✓ Obtenidos " . count($corporativos) . " departamentos corporativos\n";

    foreach ($corporativos as $corp) {
        if ($corp['tipo'] !== 'corporativo') {
            throw new Exception("ERROR: Departamento '{$corp['nombre']}' tiene tipo '{$corp['tipo']}' en lugar de 'corporativo'");
        }
        echo "  - {$corp['nombre']} (tipo: {$corp['tipo']}) ✓\n";
    }
    echo "\n";

    // Test 3: Obtener departamento global
    echo "[TEST 3] Obtener departamento Global...\n";
    $global = $deptModel->getGlobal();

    if ($global === false) {
        echo "⚠️  No existe departamento global (es opcional)\n";
    } else {
        if ($global['tipo'] !== 'global') {
            throw new Exception("ERROR: Departamento Global tiene tipo '{$global['tipo']}' en lugar de 'global'");
        }
        echo "✓ Departamento global: {$global['nombre']} (ID: {$global['id']})\n";
    }
    echo "\n";

    // Test 4: Obtener estadísticas por tipo
    echo "[TEST 4] Obtener estadísticas por tipo...\n";
    $stats = $deptModel->getStatsByTipo();

    if (empty($stats)) {
        throw new Exception("ERROR: getStatsByTipo() retornó vacío");
    }

    echo "✓ Estadísticas obtenidas:\n";
    foreach ($stats as $stat) {
        echo "  - Tipo '{$stat['tipo']}': {$stat['total']} total, {$stat['activos']} activos, {$stat['inactivos']} inactivos\n";
    }
    echo "\n";

    // Test 5: Verificar que getAll() sigue funcionando
    echo "[TEST 5] Verificar retrocompatibilidad de getAll()...\n";
    $todos = $deptModel->getAll();
    $total_esperado = count($agencias) + count($corporativos) + ($global ? 1 : 0);

    echo "✓ getAll() retorna " . count($todos) . " departamentos\n";
    if (count($todos) < $total_esperado) {
        echo "⚠️  Advertencia: getAll() retorna menos departamentos de lo esperado\n";
        echo "   Esperado: {$total_esperado}, Obtenido: " . count($todos) . "\n";
    }
    echo "\n";

    echo "================================================\n";
    echo "✅ TODOS LOS TESTS DE DEPARTAMENTO PASARON\n";
    echo "================================================\n";

} catch (Exception $e) {
    echo "\n❌ TEST FALLÓ:\n";
    echo "   " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n\n";
    exit(1);
}
