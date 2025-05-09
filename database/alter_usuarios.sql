-- Agregar columna activo a la tabla usuarios
ALTER TABLE usuarios ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1;

-- Actualizar usuarios existentes para que estén activos
UPDATE usuarios SET activo = 1; 