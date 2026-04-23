<?php
/**
 * Script para ejecutar migración de área global
 * Ejecutar: php run_global_migration.php
 */

$config = require __DIR__ . '/config/database.php';

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);

    echo "Ejecutando migración de área global...\n\n";

    // Leer archivo SQL
    $sql = file_get_contents(__DIR__ . '/database/migrations/007_create_global_area.sql');

    // Separar por punto y coma (statements individuales)
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) &&
                   !preg_match('/^--/', $stmt) &&
                   !preg_match('/^SET @/', $stmt);
        }
    );

    // Ejecutar manualmente
    echo "1. Insertando departamento Global...\n";
    $pdo->exec("
        INSERT INTO departamentos (nombre, descripcion, color, icono, activo, orden)
        SELECT 'Global', 'Métricas consolidadas de toda la organización', '#8b5cf6', 'world', 1, 999
        WHERE NOT EXISTS (
            SELECT 1 FROM departamentos WHERE nombre = 'Global'
        )
    ");

    echo "2. Obteniendo ID del departamento...\n";
    $dept_id = $pdo->query("SELECT id FROM departamentos WHERE nombre = 'Global' LIMIT 1")->fetchColumn();

    if (!$dept_id) {
        throw new Exception("No se pudo obtener ID del departamento Global");
    }

    echo "   Departamento Global ID: {$dept_id}\n";

    echo "3. Insertando área Métricas Consolidadas...\n";
    $pdo->exec("
        INSERT INTO areas (departamento_id, nombre, slug, descripcion, color, icono, activo, orden)
        SELECT {$dept_id}, 'Métricas Consolidadas', 'metricas-consolidadas',
               'Métricas calculadas globales de toda la organización', '#8b5cf6', 'chart-dots', 1, 1
        WHERE NOT EXISTS (
            SELECT 1 FROM areas WHERE slug = 'metricas-consolidadas'
        )
    ");

    echo "4. Verificando creación...\n";
    $result = $pdo->query("
        SELECT
            d.id as dept_id,
            d.nombre as departamento,
            a.id as area_id,
            a.nombre as area
        FROM departamentos d
        LEFT JOIN areas a ON d.id = a.departamento_id
        WHERE d.nombre = 'Global'
    ")->fetch(PDO::FETCH_ASSOC);

    echo "\n✅ Migración completada exitosamente!\n\n";
    echo "Departamento: {$result['departamento']} (ID: {$result['dept_id']})\n";
    echo "Área: {$result['area']} (ID: {$result['area_id']})\n";

} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
