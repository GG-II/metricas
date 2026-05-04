-- =============================================
-- Seed: Usuario de prueba area_admin
-- Crea un usuario con rol area_admin para testing
-- =============================================

-- Obtener IDs
SET @dept_sj = (SELECT id FROM departamentos WHERE nombre = 'Agencia San José' LIMIT 1);
SET @area_creditos_sj = (SELECT id FROM areas WHERE slug = 'creditos-sj' LIMIT 1);

-- Crear usuario area_admin asignado al área de Créditos San José
INSERT INTO usuarios (username, password, nombre, email, rol, departamento_id, area_id, activo)
VALUES (
    'area_admin_test',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'Usuario Prueba - Admin Área',
    'area_admin@test.com',
    'area_admin',
    @dept_sj,
    @area_creditos_sj,
    1
)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    rol = 'area_admin',
    departamento_id = @dept_sj,
    area_id = @area_creditos_sj,
    activo = 1;

-- Verificar
SELECT 'Usuario area_admin creado:' as Info;

SELECT
    u.username,
    u.nombre,
    u.rol,
    d.nombre as departamento,
    a.nombre as area
FROM usuarios u
LEFT JOIN departamentos d ON u.departamento_id = d.id
LEFT JOIN areas a ON u.area_id = a.id
WHERE u.username = 'area_admin_test';

-- =============================================
-- CREDENCIALES DE PRUEBA
-- =============================================

SELECT '✅ Usuario de prueba creado' as Status;
SELECT '' as '';
SELECT 'CREDENCIALES DE ACCESO:' as Info;
SELECT 'Username: area_admin_test' as Credencial;
SELECT 'Password: password' as Password;
SELECT 'Área asignada: Créditos - Agencia San José' as Area;
SELECT 'Permisos: Puede ver y editar solo el área de Créditos SJ' as Permisos;

-- =============================================
-- FIN DEL SEED
-- =============================================
