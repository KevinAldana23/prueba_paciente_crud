-- Crear base de datos
CREATE DATABASE IF NOT EXISTS sihos_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sihos_db;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de tipos de documento
CREATE TABLE IF NOT EXISTS tipos_documento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de géneros
CREATE TABLE IF NOT EXISTS genero (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de departamentos
CREATE TABLE IF NOT EXISTS departamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de municipios
CREATE TABLE IF NOT EXISTS municipios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    departamento_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE CASCADE
);

-- Tabla de pacientes
CREATE TABLE IF NOT EXISTS paciente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_documento_id INT NOT NULL,
    numero_documento VARCHAR(20) UNIQUE NOT NULL,
    nombre1 VARCHAR(100) NOT NULL,
    nombre2 VARCHAR(100) NULL,
    apellido1 VARCHAR(100) NOT NULL,
    apellido2 VARCHAR(100) NULL,
    genero_id INT NOT NULL,
    departamento_id INT NOT NULL,
    municipio_id INT NOT NULL,
    correo VARCHAR(255) NULL,
    foto VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tipo_documento_id) REFERENCES tipos_documento(id),
    FOREIGN KEY (genero_id) REFERENCES genero(id),
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id),
    FOREIGN KEY (municipio_id) REFERENCES municipios(id)
);

-- Insertar datos de prueba

-- Tipos de documento
INSERT INTO tipos_documento (nombre) VALUES 
('Cédula de Ciudadanía'),
('Tarjeta de Identidad');

-- Géneros
INSERT INTO genero (nombre) VALUES 
('Masculino'),
('Femenino'),
('Otro');

-- Departamentos
INSERT INTO departamentos (nombre) VALUES 
('Huila'),
('Cundinamarca'),
('Antioquia'),
('Valle del Cauca'),
('Santander');

-- Municipios (2 por cada departamento)
INSERT INTO municipios (departamento_id, nombre) VALUES 
(1, 'Neiva'),
(1, 'Pitalito'),
(2, 'Bogotá'),
(2, 'Soacha'),
(3, 'Medellín'),
(3, 'Bello'),
(4, 'Cali'),
(4, 'Palmira'),
(5, 'Bucaramanga'),
(5, 'Floridablanca');

-- Usuario administrador (contraseña: 1234567890)
-- Hash generado con password_hash('1234567890', PASSWORD_DEFAULT)
INSERT INTO users (username, password, role) VALUES 
('admin', '$2y$10$hASahVF1Tr/Md/bbni4WQ.cyr7vNiySNhetuW413qiqmB.zj9zqru', 'admin');

-- Pacientes de prueba
INSERT INTO paciente (tipo_documento_id, numero_documento, nombre1, nombre2, apellido1, apellido2, genero_id, departamento_id, municipio_id, correo) VALUES 
(1, '1193524405', 'Kevin', 'Camilo', 'Aldana', 'Ferrer', 1, 1, 1, 'kevin.aldana@email.com'),
(1, '1234567890', 'María', 'José', 'García', 'López', 2, 2, 3, 'maria.garcia@email.com'),
(1, '9876543210', 'Carlos', 'Andrés', 'Rodríguez', 'Martínez', 1, 3, 5, 'carlos.rodriguez@email.com'),
(2, '1122334455', 'Ana', 'Sofía', 'Hernández', 'González', 2, 4, 7, 'ana.hernandez@email.com'),
(1, '5566778899', 'Luis', 'Fernando', 'Pérez', 'Díaz', 1, 5, 9, 'luis.perez@email.com'); 