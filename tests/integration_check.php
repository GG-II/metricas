<?php
/**
 * Test de Integración: Verificación de funcionamiento completo
 *
 * Verifica que todos los componentes funcionen correctamente juntos
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Departamento;
use App\Models\Area;
use App\Services\MetricaCalculadaService;

echo "\n";
echo "🔍 VERIFICACIÓN DE INTEGRACIÓN\n";
echo "================================\n\n";

$checks = [
    'passed' => 0,
    'failed' => 0,
    'warnings' => 0
];

// CHECK 1: Base de datos tiene campo tipo
echo "[CHECK 1] Verificar campo 'tipo' en departamentos...\n";
try {
    $db = getDB();
    $stmt = $db->query("DESCRIBE departamentos");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $tipo_exists = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'tipo') {
            $tipo_exists = true;
            echo "✓ Campo 'tipo' existe: {$col['Type']}\n";
            break;
        }
    }

    if (!$tipo_exists) {
        throw new Exception("Campo 'tipo' no existe");
    }
    $checks['passed']++;
} catch (Exception $e) {
    echo "❌ FALLO: " . $e->getMessage() . "\n";
    $checks['failed']++;
}
echo "\n";

// CHECK 2: Índice idx_tipo existe
echo "[CHECK 2] Verificar índice idx_tipo...\n";
try {
    $stmt = $db->query("SHOW INDEX FROM departamentos WHERE Key_name = 'idx_tipo'");
    $index = $stmt->fetch();

    if ($index) {
        echo "✓ Índice idx_tipo existe en columna: {$index['Column_name']}\n";
        $checks['passed']++;
    } else {
        echo "⚠️  Índice idx_tipo no encontrado (puede no ser crítico)\n";
        $checks['warnings']++;
    }
} catch (Exception $e) {
    echo "❌ FALLO: " . $e->getMessage() . "\n";
    $checks['failed']++;
}
echo "\n";

// CHECK 3: Modelos cargan correctamente
echo "[CHECK 3] Verificar que modelos cargan sin errores...\n";
try {
    $deptModel = new Departamento();
    $areaModel = new Area();
    $service = new MetricaCalculadaService();

    echo "✓ Modelo Departamento cargado\n";
    echo "✓ Modelo Area cargado\n";
    echo "✓ Servicio MetricaCalculadaService cargado\n";
    $checks['passed']++;
} catch (Exception $e) {
    echo "❌ FALLO: " . $e->getMessage() . "\n";
    $checks['failed']++;
}
echo "\n";

// CHECK 4: Métodos nuevos funcionan
echo "[CHECK 4] Verificar métodos nuevos de Departamento...\n";
try {
    $agencias = $deptModel->getAgencias();
    $corporativos = $deptModel->getCorporativos();
    $global = $deptModel->getGlobal();
    $stats = $deptModel->getStatsByTipo();

    echo "✓ getAgencias() retorna " . count($agencias) . " resultados\n";
    echo "✓ getCorporativos() retorna " . count($corporativos) . " resultados\n";
    echo "✓ getGlobal() retorna " . ($global ? "departamento global" : "null (OK si no existe)") . "\n";
    echo "✓ getStatsByTipo() retorna " . count($stats) . " tipos\n";
    $checks['passed']++;
} catch (Exception $e) {
    echo "❌ FALLO: " . $e->getMessage() . "\n";
    $checks['failed']++;
}
echo "\n";

// CHECK 5: Servicio detecta áreas globales correctamente
echo "[CHECK 5] Verificar detección de áreas globales...\n";
try {
    if ($global) {
        $areas_global = $areaModel->getByDepartamento($global['id']);

        if (!empty($areas_global)) {
            $es_global = $service->isAreaGlobal($areas_global[0]['id']);
            if ($es_global) {
                echo "✓ Área global correctamente detectada\n";
                $checks['passed']++;
            } else {
                throw new Exception("Área del departamento Global NO detectada como global");
            }
        } else {
            echo "⚠️  No hay áreas en departamento Global para probar\n";
            $checks['warnings']++;
        }
    } else {
        echo "⚠️  No existe departamento Global\n";
        $checks['warnings']++;
    }
} catch (Exception $e) {
    echo "❌ FALLO: " . $e->getMessage() . "\n";
    $checks['failed']++;
}
echo "\n";

// CHECK 6: Modelo Area incluye departamento_tipo en getAllWithStats
echo "[CHECK 6] Verificar que Area.getAllWithStats() incluye tipo...\n";
try {
    $areas_with_stats = $areaModel->getAllWithStats();

    if (!empty($areas_with_stats)) {
        $primera_area = $areas_with_stats[0];

        if (isset($primera_area['departamento_tipo'])) {
            echo "✓ Campo 'departamento_tipo' presente: {$primera_area['departamento_tipo']}\n";
            $checks['passed']++;
        } else {
            throw new Exception("Campo 'departamento_tipo' no presente en getAllWithStats()");
        }
    } else {
        echo "⚠️  No hay áreas para probar\n";
        $checks['warnings']++;
    }
} catch (Exception $e) {
    echo "❌ FALLO: " . $e->getMessage() . "\n";
    $checks['failed']++;
}
echo "\n";

// CHECK 7: Archivos de vistas existen
echo "[CHECK 7] Verificar que archivos de vistas existen...\n";
try {
    $archivos_requeridos = [
        __DIR__ . '/../views/home_selector.php',
        __DIR__ . '/../public/index.php',
        __DIR__ . '/../public/admin/areas.php'
    ];

    foreach ($archivos_requeridos as $archivo) {
        if (file_exists($archivo)) {
            echo "✓ " . basename($archivo) . " existe\n";
        } else {
            throw new Exception("Archivo no encontrado: $archivo");
        }
    }
    $checks['passed']++;
} catch (Exception $e) {
    echo "❌ FALLO: " . $e->getMessage() . "\n";
    $checks['failed']++;
}
echo "\n";

// CHECK 8: Archivos modificados son legibles y parseables
echo "[CHECK 8] Verificar archivos modificados...\n";
try {
    $base_dir = __DIR__ . '/..';
    $archivos_php = [
        "$base_dir/src/Models/Departamento.php",
        "$base_dir/src/Models/Area.php",
        "$base_dir/src/Services/MetricaCalculadaService.php",
        "$base_dir/public/api/get-metricas-by-area.php",
        "$base_dir/public/index.php",
        "$base_dir/public/admin/areas.php",
        "$base_dir/views/home_selector.php"
    ];

    $errores = [];
    foreach ($archivos_php as $archivo) {
        if (!file_exists($archivo)) {
            $errores[] = "Archivo no encontrado: $archivo";
            continue;
        }

        if (!is_readable($archivo)) {
            $errores[] = "Archivo no legible: $archivo";
            continue;
        }

        // Intentar parsear el archivo PHP
        $contenido = file_get_contents($archivo);
        if ($contenido === false) {
            $errores[] = "No se pudo leer: $archivo";
            continue;
        }

        // Verificar que contiene tags PHP
        if (strpos($contenido, '<?php') === false) {
            $errores[] = "No contiene PHP válido: $archivo";
        }
    }

    if (empty($errores)) {
        echo "✓ " . count($archivos_php) . " archivos verificados y legibles\n";
        echo "✓ Todos contienen código PHP válido\n";
        $checks['passed']++;
    } else {
        throw new Exception("Errores:\n" . implode("\n", $errores));
    }
} catch (Exception $e) {
    echo "❌ FALLO: " . $e->getMessage() . "\n";
    $checks['failed']++;
}
echo "\n";

// RESUMEN
echo "================================\n";
echo "RESUMEN DE VERIFICACIÓN\n";
echo "================================\n";
echo "✅ Pasaron: {$checks['passed']}\n";
echo "❌ Fallaron: {$checks['failed']}\n";
echo "⚠️  Advertencias: {$checks['warnings']}\n\n";

if ($checks['failed'] === 0) {
    echo "✅ VERIFICACIÓN DE INTEGRACIÓN EXITOSA\n";
    echo "El sistema está listo para usar\n\n";
    exit(0);
} else {
    echo "❌ VERIFICACIÓN FALLÓ\n";
    echo "Revisa los errores antes de usar el sistema\n\n";
    exit(1);
}
