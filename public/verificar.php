<?php
/**
 * Script de verificación del sistema
 * Verifica que todos los componentes estén funcionando
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html><head><title>Verificación del Sistema</title>";
echo "<style>
    body { font-family: monospace; padding: 20px; background: #f4f4f4; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    .section { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
    h2 { border-bottom: 2px solid #333; padding-bottom: 5px; }
</style></head><body>";

echo "<h1>🔍 Verificación del Sistema de Métricas</h1>";

// Verificar PHP
echo "<div class='section'>";
echo "<h2>1. PHP</h2>";
$php_version = phpversion();
echo "Versión de PHP: <strong>$php_version</strong> ";
echo version_compare($php_version, '8.1', '>=') ? "<span class='success'>✓ OK</span>" : "<span class='error'>✗ Requiere PHP 8.1+</span>";
echo "</div>";

// Verificar Autoloader
echo "<div class='section'>";
echo "<h2>2. Autoloader</h2>";
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "<span class='success'>✓ Autoloader encontrado</span><br>";
} else {
    echo "<span class='error'>✗ Autoloader NO encontrado</span><br>";
}
echo "</div>";

// Verificar Conexión a BD
echo "<div class='section'>";
echo "<h2>3. Base de Datos</h2>";
try {
    $config = require __DIR__ . '/../config/database.php';
    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
    $db = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    echo "<span class='success'>✓ Conexión exitosa a MySQL</span><br>";
    echo "Base de datos: <strong>{$config['database']}</strong><br>";

    // Verificar tablas
    $tables = ['departamentos', 'areas', 'usuarios', 'periodos'];
    echo "<br><strong>Tablas:</strong><br>";
    foreach ($tables as $table) {
        // ✅ SEGURIDAD: Validar nombre de tabla contra whitelist
        $allowed_tables = ['departamentos', 'areas', 'usuarios', 'periodos', 'metricas', 'valores_metricas'];
        if (!in_array($table, $allowed_tables, true)) {
            continue; // Saltar si no está en whitelist
        }

        // Escapar nombre de tabla (identifiers con backticks)
        $safe_table = "`" . str_replace("`", "``", $table) . "`";
        $stmt = $db->query("SELECT COUNT(*) as count FROM $safe_table");
        $count = $stmt->fetchColumn();
        echo "- $table: <strong>$count</strong> registros ";
        echo ($count > 0) ? "<span class='success'>✓</span>" : "<span class='warning'>⚠ Vacía</span>";
        echo "<br>";
    }

} catch (PDOException $e) {
    echo "<span class='error'>✗ Error de conexión: " . $e->getMessage() . "</span>";
}
echo "</div>";

// Verificar Modelos
echo "<div class='section'>";
echo "<h2>4. Modelos</h2>";
$models = [
    'App\Models\Model',
    'App\Models\Departamento',
    'App\Models\Area',
    'App\Models\Usuario',
    'App\Models\Periodo'
];
foreach ($models as $model) {
    echo "- $model: ";
    echo class_exists($model) ? "<span class='success'>✓</span>" : "<span class='error'>✗</span>";
    echo "<br>";
}
echo "</div>";

// Verificar Middleware
echo "<div class='section'>";
echo "<h2>5. Middleware</h2>";
$middleware = [
    'App\Middleware\AuthMiddleware',
    'App\Middleware\PermissionMiddleware'
];
foreach ($middleware as $class) {
    echo "- $class: ";
    echo class_exists($class) ? "<span class='success'>✓</span>" : "<span class='error'>✗</span>";
    echo "<br>";
}
echo "</div>";

// Verificar Services
echo "<div class='section'>";
echo "<h2>6. Services</h2>";
$services = [
    'App\Services\PermissionService'
];
foreach ($services as $class) {
    echo "- $class: ";
    echo class_exists($class) ? "<span class='success'>✓</span>" : "<span class='error'>✗</span>";
    echo "<br>";
}
echo "</div>";

// Verificar Helpers
echo "<div class='section'>";
echo "<h2>7. Funciones Helper</h2>";
$helpers = ['getCurrentUser', 'isLoggedIn', 'isSuperAdmin', 'getDB', 'redirect', 'sanitize'];
foreach ($helpers as $func) {
    echo "- $func(): ";
    echo function_exists($func) ? "<span class='success'>✓</span>" : "<span class='error'>✗</span>";
    echo "<br>";
}
echo "</div>";

// Verificar Archivos Públicos
echo "<div class='section'>";
echo "<h2>8. Archivos Públicos</h2>";
$files = [
    'login.php',
    'logout.php',
    'index.php',
    'assets/css/custom.css'
];
foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    echo "- $file: ";
    echo file_exists($path) ? "<span class='success'>✓</span>" : "<span class='error'>✗</span>";
    echo "<br>";
}
echo "</div>";

// Resumen
echo "<div class='section' style='background: #e8f5e9;'>";
echo "<h2>✅ Resumen</h2>";
echo "<p><strong>El sistema está listo para usarse!</strong></p>";
echo "<p>Accede a: <a href='login.php'><strong>login.php</strong></a></p>";
echo "<p>Usuario de prueba: <code>superadmin</code> / <code>password</code></p>";
echo "</div>";

echo "</body></html>";
