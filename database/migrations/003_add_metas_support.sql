-- =============================================
-- MIGRACIÓN: Soporte para Metas Anuales y Mensuales
-- =============================================

-- 1. Agregar campo tiene_meta a metricas
ALTER TABLE metricas
ADD COLUMN tiene_meta TINYINT(1) DEFAULT 0 AFTER descripcion;

-- 2. Modificar tabla metas_metricas para soportar metas anuales
ALTER TABLE metas_metricas
ADD COLUMN tipo_meta ENUM('mensual', 'anual') DEFAULT 'mensual' AFTER metrica_id,
ADD COLUMN ejercicio INT DEFAULT NULL AFTER tipo_meta,
MODIFY COLUMN periodo_id INT DEFAULT NULL;

-- 3. Eliminar constraint único anterior
ALTER TABLE metas_metricas
DROP INDEX uk_metrica_periodo;

-- 4. Agregar nuevo constraint único que considera el tipo de meta
ALTER TABLE metas_metricas
ADD UNIQUE KEY uk_metrica_tipo_periodo (metrica_id, tipo_meta, ejercicio, periodo_id);

-- 5. Agregar índice para búsquedas por ejercicio
ALTER TABLE metas_metricas
ADD INDEX idx_ejercicio (ejercicio);

-- 6. Actualizar métricas existentes: marcar algunas como "tiene_meta" si ya tienen metas definidas
UPDATE metricas m
SET tiene_meta = 1
WHERE EXISTS (
    SELECT 1 FROM metas_metricas mm
    WHERE mm.metrica_id = m.id
);
