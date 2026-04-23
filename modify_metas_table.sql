-- Modificar tabla metas_metricas para metas anuales

-- Paso 1: Agregar columnas
ALTER TABLE metas_metricas ADD COLUMN tipo_meta ENUM('mensual', 'anual') DEFAULT 'mensual' AFTER metrica_id;
ALTER TABLE metas_metricas ADD COLUMN ejercicio INT DEFAULT NULL AFTER tipo_meta;
ALTER TABLE metas_metricas MODIFY COLUMN periodo_id INT DEFAULT NULL;

-- Paso 2: Agregar índice para ejercicio
ALTER TABLE metas_metricas ADD INDEX idx_ejercicio (ejercicio);

-- Paso 3: Agregar índice único compuesto
ALTER TABLE metas_metricas ADD UNIQUE KEY uk_metrica_tipo_periodo (metrica_id, tipo_meta, ejercicio, periodo_id);

-- Verificar
DESCRIBE metas_metricas;
SHOW INDEX FROM metas_metricas;
