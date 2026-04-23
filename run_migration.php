<?php
require_once __DIR__ . '/vendor/autoload.php';

$config = require __DIR__ . '/config/database.php';
$dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
$db = new PDO($dsn, $config['username'], $config['password'], $config['options']);

echo "<h1>🚀 Ejecutando Migraciones de Metas</h1>";
echo "<hr>";

$errors = [];
$success = [];

// Migración 1: Agregar campo tiene_meta a metricas
echo "<h2>1. Agregando campo tiene_meta a metricas...</h2>";
try {
    // Verificar si ya existe
    $stmt = $db->query("SHOW COLUMNS FROM metricas LIKE 'tiene_meta'");
    if ($stmt->fetch()) {
        echo "<div style='color: orange;'>⚠ Campo tiene_meta ya existe, saltando...</div>";
        $success[] = "Campo tiene_meta ya existe";
    } else {
        $db->exec("ALTER TABLE metricas ADD COLUMN tiene_meta TINYINT(1) DEFAULT 0 AFTER descripcion");
        echo "<div style='color: green; font-weight: bold;'>✓ Campo tiene_meta agregado exitosamente</div>";
        $success[] = "Campo tiene_meta agregado";
    }
} catch (PDOException $e) {
    echo "<div style='color: red;'>✗ Error: " . $e->getMessage() . "</div>";
    $errors[] = "tiene_meta: " . $e->getMessage();
}

echo "<hr>";

// Migración 2: Agregar columnas a metas_metricas
echo "<h2>2. Modificando tabla metas_metricas...</h2>";

// 2.1 Agregar tipo_meta
echo "<p><strong>2.1</strong> Agregando columna tipo_meta...</p>";
try {
    $stmt = $db->query("SHOW COLUMNS FROM metas_metricas LIKE 'tipo_meta'");
    if ($stmt->fetch()) {
        echo "<div style='color: orange;'>⚠ Columna tipo_meta ya existe</div>";
        $success[] = "tipo_meta ya existe";
    } else {
        $db->exec("ALTER TABLE metas_metricas ADD COLUMN tipo_meta ENUM('mensual', 'anual') DEFAULT 'mensual' AFTER metrica_id");
        echo "<div style='color: green;'>✓ Columna tipo_meta agregada</div>";
        $success[] = "tipo_meta agregada";
    }
} catch (PDOException $e) {
    echo "<div style='color: red;'>✗ Error: " . $e->getMessage() . "</div>";
    $errors[] = "tipo_meta: " . $e->getMessage();
}

// 2.2 Agregar ejercicio
echo "<p><strong>2.2</strong> Agregando columna ejercicio...</p>";
try {
    $stmt = $db->query("SHOW COLUMNS FROM metas_metricas LIKE 'ejercicio'");
    if ($stmt->fetch()) {
        echo "<div style='color: orange;'>⚠ Columna ejercicio ya existe</div>";
        $success[] = "ejercicio ya existe";
    } else {
        $db->exec("ALTER TABLE metas_metricas ADD COLUMN ejercicio INT DEFAULT NULL AFTER tipo_meta");
        echo "<div style='color: green;'>✓ Columna ejercicio agregada</div>";
        $success[] = "ejercicio agregada";
    }
} catch (PDOException $e) {
    echo "<div style='color: red;'>✗ Error: " . $e->getMessage() . "</div>";
    $errors[] = "ejercicio: " . $e->getMessage();
}

// 2.3 Modificar periodo_id para permitir NULL
echo "<p><strong>2.3</strong> Modificando columna periodo_id...</p>";
try {
    $db->exec("ALTER TABLE metas_metricas MODIFY COLUMN periodo_id INT DEFAULT NULL");
    echo "<div style='color: green;'>✓ Columna periodo_id modificada (permite NULL)</div>";
    $success[] = "periodo_id modificada";
} catch (PDOException $e) {
    echo "<div style='color: red;'>✗ Error: " . $e->getMessage() . "</div>";
    $errors[] = "periodo_id: " . $e->getMessage();
}

echo "<hr>";

// Migración 3: Agregar índices
echo "<h2>3. Agregando índices...</h2>";

// 3.1 Índice para ejercicio
echo "<p><strong>3.1</strong> Agregando índice idx_ejercicio...</p>";
try {
    $stmt = $db->query("SHOW INDEX FROM metas_metricas WHERE Key_name = 'idx_ejercicio'");
    if ($stmt->fetch()) {
        echo "<div style='color: orange;'>⚠ Índice idx_ejercicio ya existe</div>";
        $success[] = "idx_ejercicio ya existe";
    } else {
        $db->exec("ALTER TABLE metas_metricas ADD INDEX idx_ejercicio (ejercicio)");
        echo "<div style='color: green;'>✓ Índice idx_ejercicio agregado</div>";
        $success[] = "idx_ejercicio agregado";
    }
} catch (PDOException $e) {
    echo "<div style='color: red;'>✗ Error: " . $e->getMessage() . "</div>";
    $errors[] = "idx_ejercicio: " . $e->getMessage();
}

// 3.2 Índice único compuesto
echo "<p><strong>3.2</strong> Agregando índice único uk_metrica_tipo_periodo...</p>";
try {
    $stmt = $db->query("SHOW INDEX FROM metas_metricas WHERE Key_name = 'uk_metrica_tipo_periodo'");
    if ($stmt->fetch()) {
        echo "<div style='color: orange;'>⚠ Índice uk_metrica_tipo_periodo ya existe</div>";
        $success[] = "uk_metrica_tipo_periodo ya existe";
    } else {
        // Intentar eliminar el índice antiguo primero si existe
        try {
            $stmt = $db->query("SHOW INDEX FROM metas_metricas WHERE Key_name = 'uk_metrica_periodo'");
            if ($stmt->fetch()) {
                $db->exec("ALTER TABLE metas_metricas DROP INDEX uk_metrica_periodo");
                echo "<div style='color: blue;'>ℹ Índice antiguo uk_metrica_periodo eliminado</div>";
            }
        } catch (PDOException $e) {
            // Ignorar si no existe
        }

        $db->exec("ALTER TABLE metas_metricas ADD UNIQUE KEY uk_metrica_tipo_periodo (metrica_id, tipo_meta, ejercicio, periodo_id)");
        echo "<div style='color: green;'>✓ Índice único uk_metrica_tipo_periodo agregado</div>";
        $success[] = "uk_metrica_tipo_periodo agregado";
    }
} catch (PDOException $e) {
    echo "<div style='color: red;'>✗ Error: " . $e->getMessage() . "</div>";
    $errors[] = "uk_metrica_tipo_periodo: " . $e->getMessage();
}

echo "<hr>";

// Migración 4: Actualizar métricas existentes
echo "<h2>4. Actualizando métricas existentes...</h2>";
try {
    $stmt = $db->query("
        UPDATE metricas m
        SET tiene_meta = 1
        WHERE EXISTS (
            SELECT 1 FROM metas_metricas mm
            WHERE mm.metrica_id = m.id
        )
    ");
    $affected = $stmt->rowCount();
    echo "<div style='color: green;'>✓ $affected métricas marcadas con tiene_meta = 1</div>";
    $success[] = "$affected métricas actualizadas";
} catch (PDOException $e) {
    echo "<div style='color: red;'>✗ Error: " . $e->getMessage() . "</div>";
    $errors[] = "actualizar métricas: " . $e->getMessage();
}

echo "<hr>";

// Resumen final
echo "<h2>📊 Resumen de Migración</h2>";

if (count($errors) > 0) {
    echo "<div style='background-color: #f8d7da; padding: 15px; border-left: 4px solid #dc3545;'>";
    echo "<h3 style='color: #721c24;'>❌ Errores encontrados:</h3>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li style='color: #721c24;'>$error</li>";
    }
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div style='background-color: #d4edda; padding: 15px; border-left: 4px solid #28a745;'>";
    echo "<h3 style='color: #155724;'>✅ ¡Migración completada exitosamente!</h3>";
    echo "<ul>";
    foreach ($success as $item) {
        echo "<li style='color: #155724;'>$item</li>";
    }
    echo "</ul>";
    echo "</div>";
}

echo "<hr>";
echo "<div style='text-align: center; margin-top: 30px;'>";
echo "<a href='verify_migration.php' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>🔍 Verificar Migración</a>";
echo "&nbsp;&nbsp;";
echo "<a href='public/admin/metricas.php' style='display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px;'>📊 Ir a Métricas</a>";
echo "&nbsp;&nbsp;";
echo "<a href='public/admin/metas.php' style='display: inline-block; padding: 10px 20px; background-color: #17a2b8; color: white; text-decoration: none; border-radius: 5px;'>🎯 Ir a Metas</a>";
echo "</div>";
?>
