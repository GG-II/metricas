-- =============================================
-- SISTEMA DE MÉTRICAS MULTI-DEPARTAMENTO
-- Schema Completo - Versión 2.0
-- =============================================

-- Configuración de la base de datos
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- =============================================
-- TABLA: departamentos (NUEVA)
-- Nivel superior de la jerarquía organizacional
-- =============================================
CREATE TABLE departamentos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    color VARCHAR(7) DEFAULT '#3b82f6',
    icono VARCHAR(50) DEFAULT 'building',
    activo TINYINT(1) DEFAULT 1,
    orden INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_activo (activo),
    INDEX idx_orden (orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: areas (MODIFICADA - agregar departamento_id)
-- Áreas específicas dentro de cada departamento
-- =============================================
CREATE TABLE areas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    departamento_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    color VARCHAR(7) DEFAULT '#3b82f6',
    icono VARCHAR(50) DEFAULT 'chart-bar',
    activo TINYINT(1) DEFAULT 1,
    orden INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE RESTRICT,
    INDEX idx_departamento (departamento_id),
    INDEX idx_activo (activo),
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: usuarios (MODIFICADA - nuevos roles y scope)
-- Usuarios del sistema con permisos jerárquicos
-- =============================================
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    rol ENUM('super_admin', 'dept_admin', 'dept_viewer') NOT NULL DEFAULT 'dept_viewer',
    departamento_id INT DEFAULT NULL,
    area_id INT DEFAULT NULL,
    foto_perfil VARCHAR(255) DEFAULT NULL,
    activo TINYINT(1) DEFAULT 1,
    ultimo_acceso DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE SET NULL,
    FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE SET NULL,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_rol (rol),
    INDEX idx_departamento (departamento_id),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: periodos (SIN CAMBIOS - del legacy)
-- Períodos de tiempo para las métricas
-- =============================================
CREATE TABLE periodos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ejercicio INT NOT NULL,
    periodo INT NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    fecha_inicio DATE,
    fecha_fin DATE,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uk_ejercicio_periodo (ejercicio, periodo),
    INDEX idx_activo (activo),
    INDEX idx_ejercicio (ejercicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: metricas (SIN CAMBIOS - del legacy)
-- Definición de métricas por área
-- =============================================
CREATE TABLE metricas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    area_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    descripcion TEXT,
    unidad VARCHAR(50),
    tipo_valor ENUM('numero', 'porcentaje', 'tiempo', 'decimal') DEFAULT 'numero',
    icono VARCHAR(50) DEFAULT 'chart-line',
    es_calculada TINYINT(1) DEFAULT 0,
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE CASCADE,
    UNIQUE KEY uk_area_slug (area_id, slug),
    INDEX idx_area (area_id),
    INDEX idx_activo (activo),
    INDEX idx_es_calculada (es_calculada)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: metricas_componentes (SIN CAMBIOS)
-- Para métricas calculadas (compuestas)
-- =============================================
CREATE TABLE metricas_componentes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    metrica_calculada_id INT NOT NULL,
    metrica_componente_id INT NOT NULL,
    operacion ENUM('suma', 'resta', 'promedio') DEFAULT 'suma',
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,

    FOREIGN KEY (metrica_calculada_id) REFERENCES metricas(id) ON DELETE CASCADE,
    FOREIGN KEY (metrica_componente_id) REFERENCES metricas(id) ON DELETE CASCADE,
    INDEX idx_calculada (metrica_calculada_id),
    INDEX idx_componente (metrica_componente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: valores_metricas (SIN CAMBIOS)
-- Valores reales de las métricas por período
-- =============================================
CREATE TABLE valores_metricas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    metrica_id INT NOT NULL,
    periodo_id INT NOT NULL,
    valor_numero INT DEFAULT NULL,
    valor_decimal DECIMAL(10,2) DEFAULT NULL,
    nota TEXT,
    usuario_registro_id INT DEFAULT NULL,
    usuario_modificacion_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (metrica_id) REFERENCES metricas(id) ON DELETE CASCADE,
    FOREIGN KEY (periodo_id) REFERENCES periodos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_registro_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_modificacion_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    UNIQUE KEY uk_metrica_periodo (metrica_id, periodo_id),
    INDEX idx_metrica (metrica_id),
    INDEX idx_periodo (periodo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: configuracion_graficos (SIN CAMBIOS)
-- Configuración de gráficos del dashboard
-- =============================================
CREATE TABLE configuracion_graficos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    area_id INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    configuracion JSON NOT NULL,
    grid_x INT DEFAULT 0,
    grid_y INT DEFAULT 0,
    grid_w INT DEFAULT 4,
    grid_h INT DEFAULT 3,
    visible_para ENUM('admin', 'viewer', 'todos') DEFAULT 'todos',
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE CASCADE,
    INDEX idx_area (area_id),
    INDEX idx_tipo (tipo),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: metas_metricas (DEL LEGACY)
-- Metas u objetivos para las métricas
-- =============================================
CREATE TABLE metas_metricas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    metrica_id INT NOT NULL,
    periodo_id INT NOT NULL,
    valor_objetivo DECIMAL(10,2) NOT NULL,
    tipo_comparacion ENUM('mayor_igual', 'menor_igual', 'igual', 'rango') DEFAULT 'mayor_igual',
    valor_min DECIMAL(10,2) DEFAULT NULL,
    valor_max DECIMAL(10,2) DEFAULT NULL,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (metrica_id) REFERENCES metricas(id) ON DELETE CASCADE,
    FOREIGN KEY (periodo_id) REFERENCES periodos(id) ON DELETE CASCADE,
    UNIQUE KEY uk_metrica_periodo (metrica_id, periodo_id),
    INDEX idx_metrica (metrica_id),
    INDEX idx_periodo (periodo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: audit_log (NUEVA - Auditoría)
-- Registro de todas las acciones importantes
-- =============================================
CREATE TABLE audit_log (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    departamento_id INT DEFAULT NULL,
    accion ENUM('view', 'create', 'update', 'delete', 'login', 'logout') NOT NULL,
    tabla_afectada VARCHAR(100),
    registro_id INT,
    descripcion TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE SET NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_departamento (departamento_id),
    INDEX idx_accion (accion),
    INDEX idx_created_at (created_at),
    INDEX idx_tabla (tabla_afectada)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: log_actividad (DEL LEGACY - opcional)
-- Log de actividad del sistema (alternativa a audit_log)
-- =============================================
CREATE TABLE log_actividad (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT,
    accion VARCHAR(100),
    tabla_afectada VARCHAR(100),
    registro_id INT,
    descripcion TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_created_at (created_at),
    INDEX idx_tabla (tabla_afectada)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: comentarios_metricas (OPCIONAL)
-- Comentarios en valores de métricas
-- =============================================
CREATE TABLE comentarios_metricas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    valor_metrica_id INT NOT NULL,
    usuario_id INT NOT NULL,
    comentario TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (valor_metrica_id) REFERENCES valores_metricas(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_valor_metrica (valor_metrica_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: timeline_eventos (OPCIONAL)
-- Timeline de eventos importantes por área
-- =============================================
CREATE TABLE timeline_eventos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    area_id INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    tipo ENUM('release', 'incidente', 'mejora', 'otro') DEFAULT 'otro',
    fecha_evento DATE NOT NULL,
    usuario_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_area (area_id),
    INDEX idx_fecha (fecha_evento),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- VISTAS ÚTILES
-- =============================================

-- Vista: métricas con información completa
CREATE OR REPLACE VIEW v_metricas_completas AS
SELECT
    m.id,
    m.nombre,
    m.slug,
    m.descripcion,
    m.unidad,
    m.tipo_valor,
    m.es_calculada,
    a.id as area_id,
    a.nombre as area_nombre,
    a.slug as area_slug,
    d.id as departamento_id,
    d.nombre as departamento_nombre
FROM metricas m
JOIN areas a ON m.area_id = a.id
JOIN departamentos d ON a.departamento_id = d.id
WHERE m.activo = 1 AND a.activo = 1 AND d.activo = 1;

-- Vista: usuarios con información completa
CREATE OR REPLACE VIEW v_usuarios_completos AS
SELECT
    u.id,
    u.username,
    u.nombre,
    u.email,
    u.rol,
    u.activo,
    u.ultimo_acceso,
    d.id as departamento_id,
    d.nombre as departamento_nombre,
    a.id as area_id,
    a.nombre as area_nombre
FROM usuarios u
LEFT JOIN departamentos d ON u.departamento_id = d.id
LEFT JOIN areas a ON u.area_id = a.id;

-- =============================================
-- DATOS INICIALES (OPCIONAL)
-- =============================================

-- Comentado por defecto, descomentar si se desea poblar con datos iniciales
-- INSERT INTO departamentos (nombre, descripcion, color, icono, orden) VALUES
-- ('TI Corporativo', 'Departamento de Tecnologías de la Información', '#3b82f6', 'server', 1),
-- ('Servicios', 'Departamento de Atención al Cliente', '#10b981', 'headset', 2);

-- =============================================
-- FIN DEL SCHEMA
-- =============================================
