-- =============================================
-- Migración: Agregar rol area_admin
-- Fecha: 2026-04-28
-- Versión: 2.2
-- =============================================

-- Descripción:
-- Agrega el rol 'area_admin' que permite asignar usuarios
-- a un área específica para que solo puedan ver y editar
-- métricas de esa área en particular.

-- =============================================
-- MODIFICAR ENUM DE ROL
-- =============================================

ALTER TABLE usuarios
MODIFY COLUMN rol ENUM(
    'super_admin',
    'dept_admin',
    'area_admin',
    'dept_viewer'
) NOT NULL DEFAULT 'dept_viewer';

-- =============================================
-- VERIFICACIÓN
-- =============================================

-- Mostrar nuevo esquema
SELECT 'Estructura actualizada de columna rol:' as Info;
SHOW COLUMNS FROM usuarios LIKE 'rol';

-- Verificar que area_id existe (ya debería existir)
SELECT 'Campo area_id disponible:' as Info;
SHOW COLUMNS FROM usuarios LIKE 'area_id';

SELECT '✅ Migración completada - Rol area_admin agregado' as Status;

-- =============================================
-- NOTAS
-- =============================================

-- El campo area_id ya existe en la tabla usuarios
--
-- Reglas de negocio para area_admin:
-- 1. Debe tener area_id asignado (obligatorio)
-- 2. Solo puede ver/editar su área asignada
-- 3. No puede navegar a otras áreas
-- 4. Puede ingresar valores, crear gráficos de su área
-- 5. No puede gestionar usuarios ni crear áreas

-- =============================================
-- FIN DE MIGRACIÓN
-- =============================================
