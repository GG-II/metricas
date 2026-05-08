-- =====================================================
-- ROLLBACK: Restaurar tabla reportes a estado original
-- Fecha: 2026-05-08
-- =====================================================

-- Eliminar tabla actual
DROP TABLE IF EXISTS reportes;

-- Restaurar desde backup
CREATE TABLE reportes LIKE reportes_backup_20260508;
INSERT INTO reportes SELECT * FROM reportes_backup_20260508;

-- Recrear foreign keys originales
ALTER TABLE reportes
  ADD CONSTRAINT reportes_ibfk_1
  FOREIGN KEY (area_id) REFERENCES areas(id);

ALTER TABLE reportes
  ADD CONSTRAINT reportes_ibfk_2
  FOREIGN KEY (periodo_id) REFERENCES periodos(id) ON DELETE SET NULL;

ALTER TABLE reportes
  ADD CONSTRAINT reportes_ibfk_3
  FOREIGN KEY (usuario_creacion_id) REFERENCES usuarios(id);

ALTER TABLE reportes
  ADD CONSTRAINT reportes_ibfk_4
  FOREIGN KEY (usuario_modificacion_id) REFERENCES usuarios(id) ON DELETE SET NULL;

ALTER TABLE reportes
  ADD CONSTRAINT reportes_ibfk_5
  FOREIGN KEY (usuario_publicacion_id) REFERENCES usuarios(id) ON DELETE SET NULL;

-- Recrear índice único original
ALTER TABLE reportes
  ADD UNIQUE KEY uk_reporte_unico (area_id, periodo_id, anio, tipo_reporte, version);

-- Recrear índices originales
ALTER TABLE reportes
  ADD INDEX idx_area (area_id),
  ADD INDEX idx_periodo (periodo_id);

SELECT 'Tabla reportes restaurada correctamente desde backup' as Resultado;
