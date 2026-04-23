-- =============================================
-- MIGRACIÓN 006: Sistema de Reportes Escritos
-- Fecha: 2026-04-22
-- Descripción: Reportes narrativos tipo Word con inserción de gráficos
-- =============================================

-- =============================================
-- TABLA: reportes
-- Almacena reportes escritos por área y período
-- =============================================
CREATE TABLE IF NOT EXISTS reportes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    area_id INT NOT NULL,
    periodo_id INT DEFAULT NULL COMMENT 'NULL para reportes anuales',
    anio INT NOT NULL COMMENT 'Año del reporte (2026, 2027, etc)',
    tipo_reporte ENUM('mensual', 'trimestral', 'semestral', 'anual') NOT NULL DEFAULT 'mensual',

    -- Metadata del reporte
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT COMMENT 'Descripción breve/resumen ejecutivo',

    -- Contenido HTML del editor
    contenido LONGTEXT NOT NULL COMMENT 'HTML generado por TinyMCE',

    -- Estado y workflow
    estado ENUM('borrador', 'revision', 'publicado', 'archivado') NOT NULL DEFAULT 'borrador',
    fecha_publicacion DATETIME DEFAULT NULL,

    -- Control de versiones (simple)
    version INT DEFAULT 1,

    -- Auditoría
    usuario_creacion_id INT NOT NULL,
    usuario_modificacion_id INT DEFAULT NULL,
    usuario_publicacion_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Foreign Keys
    FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE RESTRICT,
    FOREIGN KEY (periodo_id) REFERENCES periodos(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_creacion_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_modificacion_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_publicacion_id) REFERENCES usuarios(id) ON DELETE SET NULL,

    -- Índices
    INDEX idx_area (area_id),
    INDEX idx_periodo (periodo_id),
    INDEX idx_anio (anio),
    INDEX idx_estado (estado),
    INDEX idx_tipo (tipo_reporte),
    INDEX idx_fecha_pub (fecha_publicacion),

    -- Constraint: No duplicar reportes del mismo tipo para misma área/período
    UNIQUE KEY uk_reporte_unico (area_id, periodo_id, anio, tipo_reporte, version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: reportes_graficos
-- Vincula reportes con gráficos insertados
-- =============================================
CREATE TABLE IF NOT EXISTS reportes_graficos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reporte_id INT NOT NULL,
    grafico_id INT NOT NULL COMMENT 'ID del gráfico original en configuracion_graficos',

    -- Snapshot del gráfico
    imagen_path VARCHAR(500) NOT NULL COMMENT 'Ruta de la captura PNG del gráfico',
    imagen_thumbnail VARCHAR(500) DEFAULT NULL COMMENT 'Miniatura 150x150 para galería',
    imagen_width INT DEFAULT NULL COMMENT 'Ancho original en px',
    imagen_height INT DEFAULT NULL COMMENT 'Alto original en px',

    -- Metadata del momento de captura
    periodo_captura_id INT DEFAULT NULL COMMENT 'Período cuando se capturó',
    titulo_grafico VARCHAR(255) NOT NULL COMMENT 'Título del gráfico en ese momento',

    -- Configuración de inserción en el reporte
    posicion_en_reporte INT DEFAULT 1 COMMENT 'Orden de aparición (1, 2, 3...)',
    alineacion ENUM('left', 'center', 'right', 'justify') DEFAULT 'center',
    ajuste_texto ENUM('inline', 'wrap', 'square', 'tight', 'through', 'top-bottom', 'behind', 'front') DEFAULT 'inline',
    ancho_display INT DEFAULT NULL COMMENT 'Ancho en px cuando se inserta (puede ser diferente al original)',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Foreign Keys
    FOREIGN KEY (reporte_id) REFERENCES reportes(id) ON DELETE CASCADE,
    FOREIGN KEY (grafico_id) REFERENCES configuracion_graficos(id) ON DELETE RESTRICT,
    FOREIGN KEY (periodo_captura_id) REFERENCES periodos(id) ON DELETE SET NULL,

    -- Índices
    INDEX idx_reporte (reporte_id),
    INDEX idx_grafico (grafico_id),
    INDEX idx_posicion (posicion_en_reporte)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: reportes_adjuntos (OPCIONAL - Fase 2)
-- Para adjuntar archivos adicionales
-- =============================================
CREATE TABLE IF NOT EXISTS reportes_adjuntos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reporte_id INT NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    archivo_path VARCHAR(500) NOT NULL,
    tipo_mime VARCHAR(100) NOT NULL,
    tamano_bytes INT NOT NULL,
    usuario_subida_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (reporte_id) REFERENCES reportes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_subida_id) REFERENCES usuarios(id) ON DELETE RESTRICT,

    INDEX idx_reporte (reporte_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: reportes_comentarios (OPCIONAL - Fase 2)
-- Sistema de comentarios/revisiones
-- =============================================
CREATE TABLE IF NOT EXISTS reportes_comentarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reporte_id INT NOT NULL,
    usuario_id INT NOT NULL,
    comentario TEXT NOT NULL,
    estado_reporte_momento ENUM('borrador', 'revision', 'publicado', 'archivado') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (reporte_id) REFERENCES reportes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,

    INDEX idx_reporte (reporte_id),
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: reportes_versiones (OPCIONAL - Fase 2)
-- Historial de versiones del reporte
-- =============================================
CREATE TABLE IF NOT EXISTS reportes_versiones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reporte_id INT NOT NULL,
    version INT NOT NULL,
    contenido LONGTEXT NOT NULL COMMENT 'Copia del HTML en esa versión',
    usuario_id INT NOT NULL,
    nota_version TEXT COMMENT 'Nota explicando el cambio',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (reporte_id) REFERENCES reportes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,

    INDEX idx_reporte (reporte_id),
    INDEX idx_version (version),
    UNIQUE KEY uk_reporte_version (reporte_id, version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Crear directorio de uploads (comentado, se hace en PHP)
-- =============================================
-- mkdir public/uploads/reportes/
-- mkdir public/uploads/reportes/graficos/
-- mkdir public/uploads/reportes/thumbnails/
-- mkdir public/uploads/reportes/adjuntos/

COMMIT;
