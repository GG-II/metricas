-- =============================================
-- SEED: Áreas
-- =============================================

-- Áreas de TI Corporativo (dept_id = 1)
INSERT INTO areas (departamento_id, nombre, slug, descripcion, color, icono, orden) VALUES
(1, 'Software Factory', 'software', 'Desarrollo de software', '#3b82f6', 'code', 1),
(1, 'Infraestructura', 'infraestructura', 'Servidores y redes', '#8b5cf6', 'server', 2),
(1, 'Ciberseguridad', 'ciberseguridad', 'Seguridad informática', '#ef4444', 'shield', 3);

-- Áreas de Servicios (dept_id = 2)
INSERT INTO areas (departamento_id, nombre, slug, descripcion, color, icono, orden) VALUES
(2, 'Soporte', 'soporte', 'Mesa de ayuda', '#10b981', 'headset', 1),
(2, 'Medios Digitales', 'medios', 'Marketing digital', '#f59e0b', 'photo', 2);
