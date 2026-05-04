-- =============================================
-- Migración 008: Agregar tipo a departamentos
-- Fecha: 2026-04-28
-- Descripción: Distinción entre Agencias, Corporativo y Global
-- =============================================

-- 1. Agregar campo tipo
ALTER TABLE departamentos
ADD COLUMN tipo ENUM('agencia', 'corporativo', 'global')
DEFAULT 'corporativo'
AFTER descripcion;

-- 2. Agregar índice para filtrado eficiente
ALTER TABLE departamentos
ADD INDEX idx_tipo (tipo);

-- 3. Marcar departamento Global existente (si existe)
UPDATE departamentos
SET tipo = 'global'
WHERE nombre = 'Global';

-- 4. Verificación - Mostrar estructura actualizada
SELECT 'Estructura de tabla departamentos:' as Info;
DESCRIBE departamentos;

-- 5. Verificación - Mostrar departamentos con su tipo
SELECT 'Departamentos actuales con tipo:' as Info;
SELECT id, nombre, tipo, activo, orden
FROM departamentos
ORDER BY tipo, orden, nombre;

-- =============================================
-- FIN DE MIGRACIÓN 008
-- =============================================
