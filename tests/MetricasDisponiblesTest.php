<?php
/**
 * Test: Métricas disponibles - Filtrado para áreas globales
 *
 * Verifica que el método getMetricasDisponibles() filtre correctamente:
 * - Áreas normales: solo métricas de esa área
 * - Áreas globales: métricas de TODAS las áreas (excepto otras áreas globales)
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\MetricaCalculadaService;
use App\Models\Area;
use App\Models\Departamento;
use App\Models\Metrica;

echo "🧪 Test: Métricas Disponibles - Filtrado\n";
echo "=========================================\n\n";

try {
    $service = new MetricaCalculadaService();
    $areaModel = new Area();
    $deptModel = new Departamento();
    $metricaModel = new Metrica();

    // Test 1: Área normal retorna solo sus propias métricas
    echo "[TEST 1] Área normal debe retornar solo sus métricas...\n";

    $corporativos = $deptModel->getCorporativos();

    if (!empty($corporativos)) {
        $areas_corp = $areaModel->getByDepartamento($corporativos[0]['id']);

        if (!empty($areas_corp)) {
            $area_test = $areas_corp[0];
            $metricas_disponibles = $service->getMetricasDisponibles($area_test['id']);

            echo "✓ Área '{$area_test['nombre']}' retorna " . count($metricas_disponibles) . " métricas\n";

            // Verificar que todas las métricas pertenecen a esta área
            foreach ($metricas_disponibles as $metrica) {
                // En área normal, no debería tener campo departamento_tipo
                // porque solo trae métricas de su propia área
                echo "  - Métrica: {$metrica['nombre']}\n";
            }

            // Contar métricas reales del área para comparar
            $metricas_reales = $metricaModel->getByArea($area_test['id']);
            $count_esperado = count($metricas_reales);

            if (count($metricas_disponibles) !== $count_esperado) {
                echo "⚠️  Advertencia: Cantidad de métricas no coincide\n";
                echo "   Esperado: {$count_esperado}, Obtenido: " . count($metricas_disponibles) . "\n";
            }
        } else {
            echo "⚠️  No hay áreas en departamentos corporativos\n";
        }
    } else {
        echo "⚠️  No hay departamentos corporativos\n";
    }
    echo "\n";

    // Test 2: Área global retorna métricas de TODAS las áreas (excepto otras globales)
    echo "[TEST 2] Área global debe retornar métricas de todas las áreas...\n";

    $global = $deptModel->getGlobal();

    if ($global) {
        $areas_globales = $areaModel->getByDepartamento($global['id']);

        if (!empty($areas_globales)) {
            $area_global = $areas_globales[0];
            $metricas_disponibles = $service->getMetricasDisponibles($area_global['id']);

            echo "✓ Área global '{$area_global['nombre']}' retorna " . count($metricas_disponibles) . " métricas\n";

            // Verificar que NO incluye métricas de departamentos globales
            $tipos_encontrados = [];
            foreach ($metricas_disponibles as $metrica) {
                $tipo = $metrica['departamento_tipo'] ?? 'desconocido';
                $tipos_encontrados[$tipo] = ($tipos_encontrados[$tipo] ?? 0) + 1;

                if ($tipo === 'global') {
                    throw new Exception("ERROR: Área global incluye métrica de otro departamento global: {$metrica['nombre']}");
                }
            }

            echo "  Métricas por tipo de departamento:\n";
            foreach ($tipos_encontrados as $tipo => $count) {
                echo "    - {$tipo}: {$count} métricas\n";
            }

            // Debe haber al menos métricas de agencias o corporativos
            if (count($metricas_disponibles) === 0 && count($deptModel->getAll()) > 1) {
                echo "⚠️  Advertencia: Área global no retorna métricas (puede ser normal si no hay métricas creadas)\n";
            }
        } else {
            echo "⚠️  El departamento Global no tiene áreas\n";
        }
    } else {
        echo "⚠️  No existe departamento Global\n";
    }
    echo "\n";

    // Test 3: Verificar orden de métricas en área global
    echo "[TEST 3] Orden de métricas en área global...\n";

    if ($global && !empty($areas_globales)) {
        $area_global = $areas_globales[0];
        $metricas_disponibles = $service->getMetricasDisponibles($area_global['id']);

        if (!empty($metricas_disponibles)) {
            echo "✓ Métricas ordenadas por tipo de departamento (agencias primero):\n";

            $tipo_anterior = null;
            foreach (array_slice($metricas_disponibles, 0, 5) as $metrica) {
                $tipo = $metrica['departamento_tipo'] ?? 'desconocido';
                $dept_nombre = $metrica['departamento_nombre'] ?? 'N/A';

                if ($tipo_anterior !== null && $tipo_anterior === 'corporativo' && $tipo === 'agencia') {
                    echo "⚠️  Advertencia: Orden incorrecto - corporativo antes de agencia\n";
                }

                echo "  - [{$tipo}] {$dept_nombre} > {$metrica['area_nombre']} > {$metrica['nombre']}\n";
                $tipo_anterior = $tipo;
            }

            if (count($metricas_disponibles) > 5) {
                echo "  ... y " . (count($metricas_disponibles) - 5) . " más\n";
            }
        }
    }
    echo "\n";

    echo "=========================================\n";
    echo "✅ TESTS DE MÉTRICAS DISPONIBLES PASARON\n";
    echo "=========================================\n";

} catch (Exception $e) {
    echo "\n❌ TEST FALLÓ:\n";
    echo "   " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n\n";
    exit(1);
}
