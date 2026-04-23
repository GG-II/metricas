<?php
require_once __DIR__ . '/vendor/autoload.php';

$config = require __DIR__ . '/config/database.php';
$dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
$db = new PDO($dsn, $config['username'], $config['password'], $config['options']);

echo "<h1>Verificación de Migración de Metas</h1>";
echo "<hr>";

// 1. Verificar columna tiene_meta en metricas
echo "<h2>1. Tabla metricas - Campo tiene_meta</h2>";
$stmt = $db->query("SHOW COLUMNS FROM metricas LIKE 'tiene_meta'");
$result = $stmt->fetch();
if ($result) {
    echo "<div style='color: green; font-weight: bold;'>✓ Campo tiene_meta existe</div>";
    echo "<pre>" . print_r($result, true) . "</pre>";
} else {
    echo "<div style='color: red; font-weight: bold;'>✗ Campo tiene_meta NO existe</div>";
    echo "<p><strong>Ejecuta:</strong> <code>add_tiene_meta.sql</code></p>";
}

echo "<hr>";

// 2. Verificar columnas de metas_metricas
echo "<h2>2. Tabla metas_metricas - Columnas</h2>";
$stmt = $db->query("DESCRIBE metas_metricas");
$columns = $stmt->fetchAll();

$required = ['tipo_meta', 'ejercicio'];
$found = [];
foreach ($columns as $col) {
    if (in_array($col['Field'], $required)) {
        $found[] = $col['Field'];
    }
}

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Columna</th><th>Tipo</th><th>Null</th><th>Default</th></tr>";
foreach ($columns as $col) {
    $highlight = in_array($col['Field'], $required) ? ' style="background-color: #d4edda;"' : '';
    echo "<tr{$highlight}>";
    echo "<td>{$col['Field']}</td>";
    echo "<td>{$col['Type']}</td>";
    echo "<td>{$col['Null']}</td>";
    echo "<td>{$col['Default']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<p>";
foreach ($required as $req) {
    if (in_array($req, $found)) {
        echo "<div style='color: green;'>✓ Columna <strong>$req</strong> existe</div>";
    } else {
        echo "<div style='color: red;'>✗ Columna <strong>$req</strong> NO existe</div>";
    }
}
echo "</p>";

if (count($found) < count($required)) {
    echo "<p><strong>Ejecuta:</strong> <code>modify_metas_table.sql</code></p>";
}

echo "<hr>";

// 3. Verificar índices
echo "<h2>3. Índices de metas_metricas</h2>";
$stmt = $db->query("SHOW INDEX FROM metas_metricas");
$indexes = $stmt->fetchAll();

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Nombre</th><th>Columna</th><th>Único</th></tr>";
foreach ($indexes as $idx) {
    echo "<tr>";
    echo "<td>{$idx['Key_name']}</td>";
    echo "<td>{$idx['Column_name']}</td>";
    echo "<td>" . ($idx['Non_unique'] == 0 ? 'Sí' : 'No') . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<h2>Resumen</h2>";

$allGood = true;
if (!$result) {
    echo "<div style='color: red;'>❌ Falta campo tiene_meta en metricas</div>";
    $allGood = false;
}
if (count($found) < count($required)) {
    echo "<div style='color: red;'>❌ Faltan columnas en metas_metricas</div>";
    $allGood = false;
}

if ($allGood) {
    echo "<div style='color: green; font-size: 1.5em; font-weight: bold;'>✅ ¡Migración completada correctamente!</div>";
    echo "<p>Puedes usar el módulo de Metas ahora.</p>";
} else {
    echo "<div style='color: orange; font-size: 1.5em; font-weight: bold;'>⚠ Migración incompleta</div>";
    echo "<p>Ejecuta los archivos SQL indicados arriba.</p>";
}
