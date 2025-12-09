-- =============================================
--   MODIFICACIONES PARA GESTIÓN DE SALAS
--   Añadir campos de imágenes a la tabla salas
-- =============================================

-- Añadir columnas de imágenes a la tabla salas
-- NOTA: Si las columnas ya existen, comentar o eliminar las líneas correspondientes
ALTER TABLE salas 
ADD COLUMN imagen_fondo VARCHAR(255) NULL AFTER nombre,
ADD COLUMN imagen_mesa VARCHAR(255) NULL AFTER imagen_fondo;

-- Verificar la estructura actualizada
DESCRIBE salas;
