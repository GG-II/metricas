-- =============================================
-- Crear Departamento y Área Global
-- Solo para Super Admin - Métricas Consolidadas
-- =============================================

-- Insertar departamento Global (si no existe)
INSERT INTO departamentos (nombre, descripcion, color, icono, activo, orden)
SELECT 'Global', 'Métricas consolidadas de toda la organización', '#8b5cf6', 'world', 1, 999
WHERE NOT EXISTS (
    SELECT 1 FROM departamentos WHERE nombre = 'Global'
);

-- Obtener ID del departamento Global
SET @dept_global_id = (SELECT id FROM departamentos WHERE nombre = 'Global' LIMIT 1);

-- Insertar área de Métricas Consolidadas (si no existe)
INSERT INTO areas (departamento_id, nombre, slug, descripcion, color, icono, activo, orden)
SELECT @dept_global_id, 'Métricas Consolidadas', 'metricas-consolidadas',
       'Métricas calculadas globales de toda la organización', '#8b5cf6', 'chart-dots', 1, 1
WHERE NOT EXISTS (
    SELECT 1 FROM areas WHERE slug = 'metricas-consolidadas'
);

-- Verificar que se crearon correctamente
SELECT
    d.id as dept_id,
    d.nombre as departamento,
    a.id as area_id,
    a.nombre as area
FROM departamentos d
LEFT JOIN areas a ON d.id = a.departamento_id
WHERE d.nombre = 'Global';
