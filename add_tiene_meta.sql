-- Agregar columna tiene_meta a metricas
ALTER TABLE metricas ADD COLUMN tiene_meta TINYINT(1) DEFAULT 0 AFTER descripcion;

-- Verificar que se agregó
DESCRIBE metricas;
