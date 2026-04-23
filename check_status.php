<?php
require_once __DIR__ . '/vendor/autoload.php';

$config = require __DIR__ . '/config/database.php';
$dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
$db = new PDO($dsn, $config['username'], $config['password'], $config['options']);

echo "=== VERIFICACIÓN RÁPIDA DEL SISTEMA ===\n\n";

// 1. Verificar columna tiene_meta
echo "1. Campo tiene_meta en metricas: ";
$stmt = $db->query("SHOW COLUMNS FROM metricas LIKE 'tiene_meta'");
echo $stmt->fetch() ? "✓ EXISTE\n" : "✗ NO EXISTE\n";

// 2. Verificar columnas de metas_metricas
echo "\n2. Columnas de metas_metricas:\n";
$stmt = $db->query("DESCRIBE metas_metricas");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
foreach (['tipo_meta', 'ejercicio'] as $col) {
    echo "   - $col: " . (in_array($col, $columns) ? "✓" : "✗") . "\n";
}

// 3. Verificar modelos
echo "\n3. Modelos en src/Models/:\n";
$models = glob(__DIR__ . '/src/Models/*.php');
foreach ($models as $model) {
    echo "   ✓ " . basename($model) . "\n";
}

// 4. Verificar autoload
echo "\n4. Autoload de Composer: ";
try {
    $meta = new \App\Models\Meta();
    echo "✓ FUNCIONA\n";
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
}

// 5. Verificar datos de prueba
echo "\n5. Datos existentes:\n";
$stmt = $db->query("SELECT COUNT(*) as c FROM departamentos");
echo "   - Departamentos: " . $stmt->fetch()['c'] . "\n";
$stmt = $db->query("SELECT COUNT(*) as c FROM areas");
echo "   - Áreas: " . $stmt->fetch()['c'] . "\n";
$stmt = $db->query("SELECT COUNT(*) as c FROM metricas");
echo "   - Métricas: " . $stmt->fetch()['c'] . "\n";
$stmt = $db->query("SELECT COUNT(*) as c FROM metas_metricas");
echo "   - Metas: " . $stmt->fetch()['c'] . "\n";

echo "\n=== FIN ===\n";
