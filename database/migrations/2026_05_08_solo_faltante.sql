-- =====================================================
-- SOLO LO QUE FALTA (backup ya existe)
-- =====================================================

-- Eliminar tablas relacionadas primero
DROP TABLE IF EXISTS reportes_graficos;
DROP TABLE IF EXISTS reportes_comentarios;
DROP TABLE IF EXISTS reportes_adjuntos;

-- Ahora sí eliminar tabla reportes
DROP TABLE IF EXISTS reportes;

-- Crear nueva estructura de reportes
CREATE TABLE reportes (
  id INT NOT NULL AUTO_INCREMENT,
  departamento_id INT NOT NULL,
  mes INT NOT NULL COMMENT '1=Enero, 2=Febrero, ..., 12=Diciembre',
  anio INT NOT NULL COMMENT 'Año del reporte',
  tipo_reporte ENUM('mensual','trimestral','semestral','anual') NOT NULL DEFAULT 'mensual',
  titulo VARCHAR(255) NOT NULL,
  descripcion TEXT,
  resumen_ejecutivo LONGTEXT,
  estado ENUM('borrador','revision','publicado','archivado') NOT NULL DEFAULT 'borrador',
  fecha_publicacion DATETIME DEFAULT NULL,
  version INT DEFAULT 1,
  usuario_creacion_id INT NOT NULL,
  usuario_modificacion_id INT DEFAULT NULL,
  usuario_publicacion_id INT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uk_reporte_departamento_unico (departamento_id, mes, anio, version),
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

  CONSTRAINT chk_mes_valido CHECK (mes BETWEEN 1 AND 12),
  CONSTRAINT reportes_ibfk_1 FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE CASCADE,
  CONSTRAINT reportes_ibfk_3 FOREIGN KEY (usuario_creacion_id) REFERENCES usuarios(id),
  CONSTRAINT reportes_ibfk_4 FOREIGN KEY (usuario_modificacion_id) REFERENCES usuarios(id) ON DELETE SET NULL,
  CONSTRAINT reportes_ibfk_5 FOREIGN KEY (usuario_publicacion_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Recrear tablas relacionadas (ajustadas para el nuevo esquema)
CREATE TABLE reportes_adjuntos (
  id INT NOT NULL AUTO_INCREMENT,
  reporte_id INT NOT NULL,
  nombre_archivo VARCHAR(255) NOT NULL,
  archivo_path VARCHAR(500) NOT NULL,
  tipo_mime VARCHAR(100) NOT NULL,
  tamano_bytes INT NOT NULL,
  usuario_subida_id INT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX idx_reporte (reporte_id),
  INDEX usuario_subida_id (usuario_subida_id),
  CONSTRAINT reportes_adjuntos_ibfk_1 FOREIGN KEY (reporte_id) REFERENCES reportes(id) ON DELETE CASCADE,
  CONSTRAINT reportes_adjuntos_ibfk_2 FOREIGN KEY (usuario_subida_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reportes_comentarios (
  id INT NOT NULL AUTO_INCREMENT,
  reporte_id INT NOT NULL,
  usuario_id INT NOT NULL,
  comentario TEXT NOT NULL,
  estado_reporte_momento ENUM('borrador','revision','publicado','archivado') NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX idx_reporte (reporte_id),
  INDEX idx_usuario (usuario_id),
  CONSTRAINT reportes_comentarios_ibfk_1 FOREIGN KEY (reporte_id) REFERENCES reportes(id) ON DELETE CASCADE,
  CONSTRAINT reportes_comentarios_ibfk_2 FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Nota: reportes_graficos YA NO SE USA en el nuevo sistema
-- Los gráficos se auto-generan desde configuracion_graficos de cada área
