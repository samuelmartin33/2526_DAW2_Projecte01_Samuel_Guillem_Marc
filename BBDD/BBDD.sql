-- ==========================================
--   BASE DE DATOS: restaurante_db
--   Autor: Marc Guillem, Samuel Martínez
--   Fecha: Noviembre 2025
--   Descripción: Modelo para TPV con salas, mesas, camareros y ocupaciones
-- ==========================================

DROP DATABASE IF EXISTS restaurante_db;
CREATE DATABASE restaurante_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE restaurante_db;

-- ==========================================
--   TABLA: users
-- ==========================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol INT DEFAULT 1,  -- 1=camarero, 2=admin, 3=cliente, etc.
    fecha_alta DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_baja DATETIME NULL
);

-- ==========================================
--   TABLA: salas
-- ==========================================
CREATE TABLE IF NOT EXISTS salas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255) NULL,
    num_mesas INT DEFAULT 0
);

-- ==========================================
--   TABLA: mesas
-- ==========================================
CREATE TABLE IF NOT EXISTS mesas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_sala INT NOT NULL,
    nombre VARCHAR(20) NOT NULL,
    sillas INT NOT NULL,
    estado INT DEFAULT 1,  -- 1=libre, 2=ocupada, 3=reservada
    asignado_por INT NULL, -- ID del camarero que asignó la mesa (NULL si ninguna asignación aún)
    FOREIGN KEY (id_sala) REFERENCES salas(id) ON DELETE CASCADE,
    FOREIGN KEY (asignado_por) REFERENCES users(id) ON DELETE SET NULL
);

-- ==========================================
--   TABLA: reservas
-- ==========================================
CREATE TABLE IF NOT EXISTS reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario_reserva INT NOT NULL,
    id_mesa INT NOT NULL,
    fecha_reserva DATETIME NOT NULL,
    num_comensales INT,
    nombre_cliente VARCHAR(150),
    estado INT DEFAULT 1,  -- 1=pendiente, 2=confirmada, 3=cancelada, 4=finalizada
    id_ocupacion INT NULL,
    FOREIGN KEY (id_usuario_reserva) REFERENCES users(id),
    FOREIGN KEY (id_mesa) REFERENCES mesas(id)
);

-- ==========================================
--   TABLA: ocupaciones
-- ==========================================
CREATE TABLE IF NOT EXISTS ocupaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_camarero INT NOT NULL,
    id_sala INT NOT NULL,
    id_mesa INT NOT NULL,
    inicio_ocupacion DATETIME NOT NULL,
    final_ocupacion DATETIME NULL,
    num_comensales INT,
    duracion_segundos BIGINT AS (TIMESTAMPDIFF(SECOND, inicio_ocupacion, final_ocupacion)) STORED,
    id_reserva INT NULL,
    FOREIGN KEY (id_camarero) REFERENCES users(id),
    FOREIGN KEY (id_sala) REFERENCES salas(id),
    FOREIGN KEY (id_mesa) REFERENCES mesas(id),
    FOREIGN KEY (id_reserva) REFERENCES reservas(id)
);

-- ==========================================
--   INSERTS: USERS (3 camareros)
-- ==========================================
INSERT INTO users (username, nombre, apellido, email, password_hash, rol)
VALUES
('camarero1', 'Camarero', 'Uno', 'camarero1@restaurante.com', '$2y$10$VPM7.4cja7LDxoYO6YaOLuZgW.moRul/o5VnNuYUIupyjdko/sB5m', 1),
('camarero2', 'Camarero', 'Dos', 'camarero2@restaurante.com', '$2y$10$VPM7.4cja7LDxoYO6YaOLuZgW.moRul/o5VnNuYUIupyjdko/sB5m', 1),
('camarero3', 'Camarero', 'Tres', 'camarero3@restaurante.com', '$2y$10$VPM7.4cja7LDxoYO6YaOLuZgW.moRul/o5VnNuYUIupyjdko/sB5m', 1);

-- ==========================================
--   INSERTS: SALAS
-- ==========================================
INSERT INTO salas (nombre, descripcion, num_mesas)
VALUES
('Terraza 1', 'Terraza exterior con vistas al parque', 4),
('Terraza 2', 'Terraza cubierta', 4),
('Terraza 3', 'Terraza principal', 4),
('Comedor 1', 'Comedor principal interior', 4),
('Comedor 2', 'Comedor pequeño interior', 4),
('Privada 1', 'Sala privada para eventos', 1),
('Privada 2', 'Sala privada con decoración clásica', 1),
('Privada 3', 'Sala privada moderna', 1),
('Privada 4', 'Sala privada VIP', 1);

-- ==========================================
--   INSERTS: MESAS
-- ==========================================
INSERT INTO mesas (id_sala, nombre, sillas) VALUES
-- Terraza 1
(1, 'Mesa T1-1', 4), (1, 'Mesa T1-2', 4), (1, 'Mesa T1-3', 6), (1, 'Mesa T1-4', 2),
-- Terraza 2
(2, 'Mesa T2-1', 4), (2, 'Mesa T2-2', 6), (2, 'Mesa T2-3', 4), (2, 'Mesa T2-4', 2),
-- Terraza 3
(3, 'Mesa T3-1', 4), (3, 'Mesa T3-2', 4), (3, 'Mesa T3-3', 6), (3, 'Mesa T3-4', 4),
-- Comedor 1
(4, 'Mesa C1-1', 6), (4, 'Mesa C1-2', 6), (4, 'Mesa C1-3', 4), (4, 'Mesa C1-4', 4),
-- Comedor 2
(5, 'Mesa C2-1', 4), (5, 'Mesa C2-2', 4), (5, 'Mesa C2-3', 6), (5, 'Mesa C2-4', 2),
-- Privadas (una mesa por sala)
(6, 'Mesa P1', 10),
(7, 'Mesa P2', 15),
(8, 'Mesa P3', 20),
(9, 'Mesa P4', 30);

-- ==========================================
--   INSERTS: RESERVAS (ejemplo)
-- ==========================================
INSERT INTO reservas (id_usuario_reserva, id_mesa, fecha_reserva, num_comensales, nombre_cliente, estado)
VALUES
(1, 1, '2025-11-07 13:00:00', 4, 'Juan Pérez', 2),
(2, 5, '2025-11-07 14:30:00', 2, 'Lucía Fernández', 1),
(3, 10, '2025-11-07 15:00:00', 3, 'Carlos Martínez', 2);

-- ==========================================
--   INSERTS: OCUPACIONES (corregido)
-- ==========================================
INSERT INTO ocupaciones (id_camarero, id_sala, id_mesa, inicio_ocupacion, final_ocupacion, num_comensales, id_reserva)
VALUES
(1, 1, 1, '2025-11-07 13:05:00', '2025-11-07 14:00:00', 4, 1),
(2, 2, 5, '2025-11-07 14:35:00', '2025-11-07 15:30:00', 2, 2),
(3, 4, 13, '2025-11-07 15:10:00', '2025-11-07 16:45:00', 3, 3),
(1, 3, 9, '2025-11-07 17:00:00', '2025-11-07 18:20:00', 4, NULL),
(2, 7, 22, '2025-11-07 19:00:00', '2025-11-07 21:30:00', 10, NULL);
