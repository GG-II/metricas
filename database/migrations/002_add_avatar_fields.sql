-- Agregar campos de avatar personalizado a usuarios
ALTER TABLE usuarios
ADD COLUMN avatar_icono VARCHAR(50) DEFAULT 'user' AFTER tema,
ADD COLUMN avatar_color VARCHAR(7) DEFAULT '#3b82f6' AFTER avatar_icono;

-- Actualizar usuarios existentes con iconos por defecto
UPDATE usuarios SET avatar_icono = 'user', avatar_color = '#3b82f6' WHERE avatar_icono IS NULL;
