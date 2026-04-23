-- =============================================
-- MIGRACIÓN: Soporte para Metas (Versión sin errores)
-- =============================================

-- 1. Agregar campo tiene_meta a metricas (si no existe)
ALTER TABLE metricas
ADD COLUMN IF NOT EXISTS tiene_meta TINYINT(1) DEFAULT 0 AFTER descripcion;

-- 2. Modificar tabla metas_metricas para soportar metas anuales
ALTER TABLE metas_metricas
ADD COLUMN IF NOT EXISTS tipo_meta ENUM('mensual', 'anual') DEFAULT 'mensual' AFTER metrica_id;

ALTER TABLE metas_metricas
ADD COLUMN IF NOT EXISTS ejercicio INT DEFAULT NULL AFTER tipo_meta;

ALTER TABLE metas_metricas
MODIFY COLUMN periodo_id INT DEFAULT NULL;

-- 3. Intentar eliminar constraint único anterior (ignorar si no existe)
-- Nota: Este paso puede fallar si el índice no existe, es normal
SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.statistics
     WHERE table_schema = DATABASE()
     AND table_name = 'metas_metricas'
     AND index_name = 'uk_metrica_periodo') > 0,
    'ALTER TABLE metas_metricas DROP INDEX uk_metrica_periodo',
    'SELECT "Index uk_metrica_periodo does not exist, skipping" as message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Agregar nuevo constraint único (si no existe)
SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.statistics
     WHERE table_schema = DATABASE()
     AND table_name = 'metas_metricas'
     AND index_name = 'uk_metrica_tipo_periodo') = 0,
    'ALTER TABLE metas_metricas ADD UNIQUE KEY uk_metrica_tipo_periodo (metrica_id, tipo_meta, ejercicio, periodo_id)',
    'SELECT "Index uk_metrica_tipo_periodo already exists, skipping" as message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5. Agregar índice para búsquedas por ejercicio (si no existe)
SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.statistics
     WHERE table_schema = DATABASE()
     AND table_name = 'metas_metricas'
     AND index_name = 'idx_ejercicio') = 0,
    'ALTER TABLE metas_metricas ADD INDEX idx_ejercicio (ejercicio)',
    'SELECT "Index idx_ejercicio already exists, skipping" as message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 6. Actualizar métricas existentes: marcar como "tiene_meta" si ya tienen metas
UPDATE metricas m
SET tiene_meta = 1
WHERE EXISTS (
    SELECT 1 FROM metas_metricas mm
    WHERE mm.metrica_id = m.id
);

-- Verificación final
SELECT
    'Migración completada correctamente' as status,
    COUNT(*) as metricas_con_tiene_meta
FROM metricas
WHERE tiene_meta = 1;
