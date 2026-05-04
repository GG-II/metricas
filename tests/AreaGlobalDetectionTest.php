<?php
/**
 * Test: Detección de áreas globales
 *
 * Verifica que el servicio MetricaCalculadaService detecte correctamente
 * las áreas globales basándose en el tipo de departamento
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\MetricaCalculadaService;
use App\Models\Area;
use App\Models\Departamento;

echo "🧪 Test: Detección de Áreas Globales\n";
echo "=====================================\n\n";

try {
    $service = new MetricaCalculadaService();
    $areaModel = new Area();
    $deptModel = new Departamento();

    // Test 1: Verificar que áreas normales NO son detectadas como globales
    echo "[TEST 1] Áreas normales NO deben ser globales...\n";

    // Obtener áreas de departamentos corporativos
    $corporativos = $deptModel->getCorporativos();

    if (!empty($corporativos)) {
        $primer_corp = $corporativos[0];
        $areas_corp = $areaModel->getByDepartamento($primer_corp['id']);

        if (!empty($areas_corp)) {
            $area_test = $areas_corp[0];
            $es_global = $service->isAreaGlobal($area_test['id']);

            if ($es_global) {
                throw new Exception("ERROR: Área '{$area_test['nombre']}' de departamento corporativo fue detectada como global");
            }

            echo "✓ Área '{$area_test['nombre']}' correctamente detectada como NO global\n";
        } else {
            echo "⚠️  No hay áreas en departamentos corporativos para probar\n";
        }
    } else {
        echo "⚠️  No hay departamentos corporativos para probar\n";
    }
    echo "\n";

    // Test 2: Verificar que áreas de departamento Global SÍ son detectadas como globales
    echo "[TEST 2] Áreas del departamento Global SÍ deben ser globales...\n";

    $global = $deptModel->getGlobal();

    if ($global) {
        $areas_globales = $areaModel->getByDepartamento($global['id']);

        if (!empty($areas_globales)) {
            foreach ($areas_globales as $area_global) {
                $es_global = $service->isAreaGlobal($area_global['id']);

                if (!$es_global) {
                    throw new Exception("ERROR: Área '{$area_global['nombre']}' del departamento Global NO fue detectada como global");
                }

                echo "✓ Área '{$area_global['nombre']}' correctamente detectada como global\n";
            }
        } else {
            echo "⚠️  El departamento Global no tiene áreas para probar\n";
            echo "   Esto es normal si aún no se han creado áreas globales\n";
        }
    } else {
        echo "⚠️  No existe departamento Global\n";
    }
    echo "\n";

    // Test 3: Verificar que áreas de agencias NO son globales
    echo "[TEST 3] Áreas de agencias NO deben ser globales...\n";

    $agencias = $deptModel->getAgencias();

    if (!empty($agencias)) {
        $primer_agencia = $agencias[0];
        $areas_agencia = $areaModel->getByDepartamento($primer_agencia['id']);

        if (!empty($areas_agencia)) {
            $area_test = $areas_agencia[0];
            $es_global = $service->isAreaGlobal($area_test['id']);

            if ($es_global) {
                throw new Exception("ERROR: Área '{$area_test['nombre']}' de agencia fue detectada como global");
            }

            echo "✓ Área '{$area_test['nombre']}' de agencia correctamente detectada como NO global\n";
        } else {
            echo "⚠️  No hay áreas en agencias para probar\n";
        }
    } else {
        echo "⚠️  No hay agencias para probar\n";
    }
    echo "\n";

    echo "=====================================\n";
    echo "✅ TESTS DE DETECCIÓN GLOBAL PASARON\n";
    echo "=====================================\n";

} catch (Exception $e) {
    echo "\n❌ TEST FALLÓ:\n";
    echo "   " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n\n";
    exit(1);
}
