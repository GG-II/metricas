-- =====================================================
-- MIGRACIÓN SIMPLIFICADA: Reportes por Departamento
-- Fecha: 2026-05-08
-- Versión: 2 (método simple - DROP/CREATE)
-- =====================================================

-- Paso 1: Respaldar datos existentes
CREATE TABLE IF NOT EXISTS reportes_backup_20260508 AS SELECT * FROM reportes;

-- Paso 2: Eliminar tabla actual
DROP TABLE IF EXISTS reportes;

-- Paso 3: Crear nueva estructura
CREATE TABLE reportes (
  id INT NOT NULL AUTO_INCREMENT,
  departamento_id INT NOT NULL,
  mes INT NOT NULL COMMENT '1=Enero, 2=Febrero, ..., 12=Diciembre',
  anio INT NOT NULL COMMENT 'Año del reporte (2026, 2027, etc)',
  tipo_reporte ENUM('mensual','trimestral','semestral','anual') NOT NULL DEFAULT 'mensual',
  titulo VARCHAR(255) NOT NULL,
  descripcion TEXT COMMENT 'Descripción breve',
  resumen_ejecutivo LONGTEXT COMMENT 'Resumen ejecutivo editable por el usuario',
  estado ENUM('borrador','revision','publicado','archivado') NOT NULL DEFAULT 'borrador',
  fecha_publicacion DATETIME DEFAULT NULL,
  version INT DEFAULT 1,
  usuario_creacion_id INT NOT NULL,
  usuario_modificacion_id INT DEFAULT NULL,
  usuario_publicacion_id INT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),

  -- Índices
  INDEX idx_departamento (departamento_id),
  INDEX idx_mes (mes),
  INDEX idx_anio (anio),
  INDEX idx_estado (estado),
  INDEX idx_tipo (tipo_reporte),
  INDEX idx_fecha_pub (fecha_publicacion),
  INDEX idx_departamento_fecha (departamento_id, anio, mes),
  INDEX usuario_creacion_id (usuario_creacion_id),
  INDEX usuario_modificacion_id (usuario_modificacion_id),
  INDEX usuario_publicacion_id (usuario_publicacion_id),

  -- Constraint único: un reporte por departamento/mes/año
  UNIQUE KEY uk_reporte_departamento_unico (departamento_id, mes, anio, version),

  -- Validación de mes
  CONSTRAINT chk_mes_valido CHECK (mes BETWEEN 1 AND 12),

  -- Foreign keys
  CONSTRAINT reportes_ibfk_1
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE CASCADE,
  CONSTRAINT reportes_ibfk_3
    FOREIGN KEY (usuario_creacion_id) REFERENCES usuarios(id),
  CONSTRAINT reportes_ibfk_4
    FOREIGN KEY (usuario_modificacion_id) REFERENCES usuarios(id) ON DELETE SET NULL,
  CONSTRAINT reportes_ibfk_5
    FOREIGN KEY (usuario_publicacion_id) REFERENCES usuarios(id) ON DELETE SET NULL

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- NOTAS DE MIGRACIÓN
-- =====================================================
-- 1. Los reportes antiguos están respaldados en reportes_backup_20260508
-- 2. La tabla reportes se recreó con la nueva estructura
-- 3. Para restaurar: ejecutar 2026_05_08_rollback_reportes.sql
-- =====================================================

SELECT 'Migración completada. Tabla reportes recreada exitosamente.' as Resultado;
