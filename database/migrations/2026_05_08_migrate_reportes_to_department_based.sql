-- =====================================================
-- MIGRACIÓN: Reportes de Área a Reportes por Departamento
-- Fecha: 2026-05-08
-- Descripción: Convierte el sistema de reportes para generar
--              reportes consolidados por departamento en lugar de
--              reportes individuales por área
-- =====================================================

-- Paso 1: Respaldar datos existentes
CREATE TABLE IF NOT EXISTS reportes_backup_20260508 AS SELECT * FROM reportes;

-- Paso 2: Eliminar restricciones existentes
ALTER TABLE reportes
  DROP FOREIGN KEY reportes_ibfk_1,
  DROP FOREIGN KEY reportes_ibfk_2,
  DROP FOREIGN KEY reportes_ibfk_3,
  DROP FOREIGN KEY reportes_ibfk_4,
  DROP FOREIGN KEY reportes_ibfk_5;

-- Eliminar índices existentes
ALTER TABLE reportes
  DROP INDEX uk_reporte_unico,
  DROP INDEX idx_area,
  DROP INDEX idx_periodo;

-- Vaciar tabla (los datos están respaldados)
DELETE FROM reportes;

-- Paso 3: Modificar estructura de la tabla
-- IMPORTANTE: Primero eliminar area_id y periodo_id
ALTER TABLE reportes
  DROP COLUMN area_id;

ALTER TABLE reportes
  DROP COLUMN periodo_id;

-- Agregar departamento_id como NULL primero (lo haremos NOT NULL después del FK)
ALTER TABLE reportes
  ADD COLUMN departamento_id INT NULL AFTER id;

-- Agregar campo mes como NULL primero
ALTER TABLE reportes
  ADD COLUMN mes INT NULL COMMENT '1=Enero, 2=Febrero, ..., 12=Diciembre' AFTER departamento_id;

-- Renombrar contenido a resumen_ejecutivo (ahora solo guarda el resumen)
ALTER TABLE reportes
  CHANGE COLUMN contenido resumen_ejecutivo LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Paso 4: Recrear restricciones con los nombres originales
ALTER TABLE reportes
  ADD CONSTRAINT reportes_ibfk_1
  FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE CASCADE;

ALTER TABLE reportes
  ADD CONSTRAINT reportes_ibfk_3
  FOREIGN KEY (usuario_creacion_id) REFERENCES usuarios(id);

ALTER TABLE reportes
  ADD CONSTRAINT reportes_ibfk_4
  FOREIGN KEY (usuario_modificacion_id) REFERENCES usuarios(id) ON DELETE SET NULL;

ALTER TABLE reportes
  ADD CONSTRAINT reportes_ibfk_5
  FOREIGN KEY (usuario_publicacion_id) REFERENCES usuarios(id) ON DELETE SET NULL;

-- Paso 5: Crear nueva restricción de unicidad
-- Un solo reporte por departamento, mes y año (versiones controladas por version)
ALTER TABLE reportes
  ADD CONSTRAINT uk_reporte_departamento_unico
  UNIQUE KEY (departamento_id, mes, anio, version);

-- Paso 6: Agregar validación de mes
ALTER TABLE reportes
  ADD CONSTRAINT chk_mes_valido
  CHECK (mes BETWEEN 1 AND 12);

-- Paso 7: Ahora que los FK están creados, hacer las columnas NOT NULL
ALTER TABLE reportes
  MODIFY COLUMN departamento_id INT NOT NULL,
  MODIFY COLUMN mes INT NOT NULL;

-- Paso 8: Crear índices para optimizar consultas
ALTER TABLE reportes
  ADD INDEX idx_departamento (departamento_id),
  ADD INDEX idx_mes (mes),
  ADD INDEX idx_departamento_fecha (departamento_id, anio, mes);

-- =====================================================
-- NOTAS DE MIGRACIÓN
-- =====================================================
-- 1. Los reportes antiguos quedan respaldados en reportes_backup_20260508
-- 2. La tabla reportes queda vacía después de esta migración
-- 3. El nuevo sistema genera reportes consolidados automáticamente
--    incluyendo todas las áreas del departamento
-- 4. La estructura es ahora: Portada → Resumen Ejecutivo → Secciones por Área
-- 5. El campo resumen_ejecutivo es lo único editable por el usuario
-- 6. Las gráficas se auto-incluyen desde configuracion_graficos de cada área
-- =====================================================
