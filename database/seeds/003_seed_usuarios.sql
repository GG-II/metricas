-- =============================================
-- SEED: Usuarios de prueba
-- Todos los usuarios tienen password: password
-- Hash generado con: password_hash('password', PASSWORD_DEFAULT)
-- =============================================

-- Super Admin (ve TODO el sistema)
INSERT INTO usuarios (username, password, nombre, email, rol, departamento_id, area_id) VALUES
('superadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Administrador', 'superadmin@metricas.com', 'super_admin', NULL, NULL);

-- Admin TI Corporativo (ve y edita todo TI Corporativo)
INSERT INTO usuarios (username, password, nombre, email, rol, departamento_id, area_id) VALUES
('admin_ti', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin TI', 'admin_ti@metricas.com', 'dept_admin', 1, NULL);

-- Admin Servicios (ve y edita todo Servicios)
INSERT INTO usuarios (username, password, nombre, email, rol, departamento_id, area_id) VALUES
('admin_servicios', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Servicios', 'admin_servicios@metricas.com', 'dept_admin', 2, NULL);

-- Viewer de Software Factory (solo ve Software Factory - read only)
INSERT INTO usuarios (username, password, nombre, email, rol, departamento_id, area_id) VALUES
('viewer_software', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Viewer Software', 'viewer_software@metricas.com', 'dept_viewer', 1, 1);

-- Viewer de Soporte (solo ve Soporte - read only)
INSERT INTO usuarios (username, password, nombre, email, rol, departamento_id, area_id) VALUES
('viewer_soporte', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Viewer Soporte', 'viewer_soporte@metricas.com', 'dept_viewer', 2, 4);
