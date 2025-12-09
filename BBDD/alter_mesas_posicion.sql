-- =============================================
--   MODIFICACIONES PARA GESTIÓN DE MESAS
--   Añadir campos de posicionamiento
-- =============================================

-- Añadir columnas de posición a la tabla mesas
ALTER TABLE mesas 
ADD COLUMN posicion_top VARCHAR(10) DEFAULT '50%' AFTER sillas,
ADD COLUMN posicion_left VARCHAR(10) DEFAULT '50%' AFTER posicion_top;

-- Verificar la estructura actualizada
DESCRIBE mesas;
