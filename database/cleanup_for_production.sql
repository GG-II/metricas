-- =====================================================
-- SCRIPT DE LIMPIEZA PARA DESPLIEGUE EN PRODUCCIÓN
-- Sistema de Métricas - Base de datos limpia
-- =====================================================
-- IMPORTANTE: Este script elimina TODOS los datos de prueba
-- y deja solo lo esencial para empezar en producción
-- =====================================================

-- =====================================================
-- PASO 1: ELIMINAR DATOS DE PRUEBA
-- =====================================================

-- 1.1 Reportes y relaciones (en orden para respetar foreign keys)
DELETE FROM reportes_versiones;
DELETE FROM reportes_comentarios;
DELETE FROM reportes_graficos;
DELETE FROM reportes_adjuntos;
DELETE FROM reportes;

-- 1.2 Valores y métricas
DELETE FROM valores_metricas;
DELETE FROM comentarios_metricas;
DELETE FROM metas_metricas;
DELETE FROM metricas_componentes;
DELETE FROM metricas;

-- 1.3 Gráficos
DELETE FROM configuracion_graficos;

-- 1.4 Logs y eventos
DELETE FROM timeline_eventos;
DELETE FROM log_actividad;
DELETE FROM audit_log;

-- 1.5 Períodos (el admin los genera con "Generar Año Completo")
DELETE FROM periodos;

-- 1.6 Tokens API
DELETE FROM api_tokens;

-- 1.7 Áreas de prueba (mantener solo las del departamento Global si existen)
DELETE FROM areas
WHERE departamento_id NOT IN (
    SELECT id FROM departamentos WHERE tipo = 'global'
);

-- 1.8 Usuarios de prueba (mantener solo super_admin)
DELETE FROM usuarios
WHERE rol != 'super_admin';

-- 1.9 Departamentos de prueba (mantener solo Global)
DELETE FROM departamentos
WHERE tipo != 'global';

-- 1.10 Reiniciar auto_increment de las tablas principales
ALTER TABLE metricas AUTO_INCREMENT = 1;
ALTER TABLE areas AUTO_INCREMENT = 1;
ALTER TABLE periodos AUTO_INCREMENT = 1;
ALTER TABLE valores_metricas AUTO_INCREMENT = 1;
ALTER TABLE configuracion_graficos AUTO_INCREMENT = 1;
ALTER TABLE reportes AUTO_INCREMENT = 1;

-- =====================================================
-- PASO 2: ASEGURAR DATOS FUNDAMENTALES EXISTEN
-- =====================================================

-- 2.1 Asegurar que existe departamento Global
INSERT INTO departamentos (nombre, descripcion, icono, color, tipo, activo, created_at)
SELECT
    'Global',
    'Departamento especial para métricas consolidadas de toda la organización',
    'world',
    '#6366f1',
    'global',
    1,
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM departamentos WHERE tipo = 'global'
);

-- 2.2 Asegurar que existe usuario super_admin
INSERT INTO usuarios (nombre, email, password, rol, activo, tema, created_at)
SELECT
    'Administrador',
    'admin@metricas.local',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: 'password'
    'super_admin',
    1,
    'light',
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM usuarios WHERE rol = 'super_admin'
);

-- =====================================================
-- PASO 3: VERIFICACIÓN FINAL
-- =====================================================

-- Mostrar datos que quedaron
SELECT '=== VERIFICACIÓN DE DATOS ESENCIALES ===' as '';

SELECT
    CONCAT('✓ Usuario Super Admin: ', nombre, ' (', email, ')') as 'Usuario'
FROM usuarios
WHERE rol = 'super_admin'
LIMIT 1;

SELECT
    CONCAT('✓ Departamento Global: ', nombre, ' (tipo: ', tipo, ')') as 'Departamento'
FROM departamentos
WHERE tipo = 'global'
LIMIT 1;

-- Contar registros restantes
SELECT '=== RESUMEN DE TABLAS ===' as '';

SELECT
    (SELECT COUNT(*) FROM usuarios) as 'Usuarios (debe ser 1)',
    (SELECT COUNT(*) FROM departamentos) as 'Departamentos (debe ser 1)',
    (SELECT COUNT(*) FROM areas) as 'Áreas (debe ser 0)',
    (SELECT COUNT(*) FROM metricas) as 'Métricas (debe ser 0)',
    (SELECT COUNT(*) FROM periodos) as 'Períodos (debe ser 0)',
    (SELECT COUNT(*) FROM valores_metricas) as 'Valores (debe ser 0)',
    (SELECT COUNT(*) FROM configuracion_graficos) as 'Gráficos (debe ser 0)',
    (SELECT COUNT(*) FROM reportes) as 'Reportes (debe ser 0)';

-- =====================================================
-- COMPLETADO
-- =====================================================

SELECT '
✅ BASE DE DATOS LIMPIADA PARA PRODUCCIÓN

Datos esenciales presentes:
  • 1 usuario super_admin (email: admin@metricas.local)
  • 1 departamento Global (tipo: global)

IMPORTANTE:
  ⚠️  Cambia la contraseña del super_admin antes de desplegar
  ⚠️  Cambia el email del super_admin si es necesario

Próximos pasos al desplegar:
  1. Cambiar credenciales del super_admin
  2. Crear períodos (usar "Generar Año Completo")
  3. Crear departamentos de tu organización
  4. Crear áreas por departamento
  5. Crear usuarios y asignarles roles

' as 'RESUMEN';
