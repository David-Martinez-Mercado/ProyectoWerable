-- Base de Datos Privada
CREATE DATABASE IF NOT EXISTS monitoreo_privada;
USE monitoreo_privada;

-- Tabla de Usuarios
CREATE TABLE USUARIOS (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('familiar', 'cuidador', 'medico') DEFAULT 'familiar',
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de Pacientes/Dispositivos
CREATE TABLE Pacientes (
    codigo VARCHAR(50) PRIMARY KEY,
    id_usuario INT NOT NULL,
    nombre_paciente VARCHAR(100) NOT NULL,
    edad INT NOT NULL,
    enfermedades_cronicas TEXT,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_lectura TIMESTAMP NULL,
    FOREIGN KEY (id_usuario) REFERENCES USUARIOS(id) ON DELETE CASCADE
);

-- Tabla de Lecturas
CREATE TABLE Lecturas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_dispositivo VARCHAR(50) NOT NULL,
    lectura_FC DECIMAL(5,2),
    lectura_SpO2 DECIMAL(5,2),
    lectura_temperatura DECIMAL(5,2),
    gps_lat DECIMAL(10,8),
    gps_lon DECIMAL(11,8),
    fecha_lectura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_dispositivo) REFERENCES Pacientes(codigo) ON DELETE CASCADE,
    INDEX idx_dispositivo_fecha (id_dispositivo, fecha_lectura)
);

-- Tabla de Umbrales de Alerta
CREATE TABLE Umbrales_Alerta (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_dispositivo VARCHAR(50) NOT NULL,
    umbral_FC_min INT DEFAULT 60,
    umbral_FC_max INT DEFAULT 100,
    umbral_SpO2_min INT DEFAULT 90,
    umbral_temperatura_min DECIMAL(4,2) DEFAULT 35.5,
    umbral_temperatura_max DECIMAL(4,2) DEFAULT 37.5,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_dispositivo) REFERENCES Pacientes(codigo) ON DELETE CASCADE
);

-- Tabla de Log de Alertas
CREATE TABLE Log_Alertas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_dispositivo VARCHAR(50) NOT NULL,
    tipo_alerta ENUM('medica', 'extravio') NOT NULL,
    descripcion TEXT,
    ubicacion_lat DECIMAL(10,8),
    ubicacion_lon DECIMAL(11,8),
    estado ENUM('PENDIENTE', 'EN PROCESO', 'EN LUGAR', 'RESUELTA', 'CANCELADA') DEFAULT 'PENDIENTE',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_dispositivo) REFERENCES Pacientes(codigo) ON DELETE CASCADE
);

-- RECREAR COMPLETAMENTE LA BD PÚBLICA (OPCIONAL)
DROP DATABASE IF EXISTS vital_monitor_public;
CREATE DATABASE vital_monitor_public;
USE vital_monitor_public;

-- Tabla de Usuarios C5
CREATE TABLE Usuarios_C5 (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    rol ENUM('operador', 'supervisor', 'admin') DEFAULT 'operador',
    turno ENUM('matutino', 'vespertino', 'nocturno') DEFAULT 'matutino',
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de Alertas para Servicios de Emergencia
CREATE TABLE Alertas_C5 (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_alerta_privada INT NOT NULL,
    id_dispositivo VARCHAR(50) NOT NULL,
    tipo_emergencia ENUM('Emergencia Médica', 'Persona Extraviada') NOT NULL,
    nombre_paciente VARCHAR(100),
    edad INT,
    enfermedades_cronicas TEXT,
    contacto_emergencia VARCHAR(200),
    direccion_residencia TEXT,
    direccion_recoleccion TEXT,
    ubicacion_lat DECIMAL(10,8),
    ubicacion_lon DECIMAL(11,8),
    estado ENUM('PENDIENTE', 'EN PROCESO', 'EN LUGAR', 'RESUELTA', 'CANCELADA') DEFAULT 'PENDIENTE',
    fecha_reporte TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP NULL,
    tiempo_respuesta INT NULL,
    unidades_asignadas VARCHAR(255),
    notas_actualizacion TEXT,
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha_reporte),
    INDEX idx_tipo (tipo_emergencia)
);

-- Tabla de Estadísticas de Tiempos de Respuesta
CREATE TABLE Estadisticas_Tiempos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tipo_emergencia VARCHAR(50) NOT NULL,
    tiempo_promedio INT NOT NULL,
    tiempo_minimo INT NOT NULL,
    tiempo_maximo INT NOT NULL,
    total_casos INT DEFAULT 0,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de Unidades de Emergencia
CREATE TABLE Unidades_Emergencia (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero_unidad VARCHAR(20) UNIQUE NOT NULL,
    tipo_unidad ENUM('ambulancia', 'patrulla', 'bomberos', 'rescate') NOT NULL,
    estado ENUM('disponible', 'asignada', 'en_camino', 'en_lugar', 'regreso') DEFAULT 'disponible',
    ubicacion_lat DECIMAL(10,8),
    ubicacion_lon DECIMAL(11,8),
    operador_asignado VARCHAR(100),
    ultima_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertar datos de prueba
INSERT INTO Usuarios_C5 (username, password, nombre, rol, turno) VALUES 
('operador1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ana García', 'operador', 'matutino'),
('supervisor1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carlos López', 'supervisor', 'matutino'),
('admin_c5', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador C5', 'admin', 'matutino');

INSERT INTO Unidades_Emergencia (numero_unidad, tipo_unidad, estado, ubicacion_lat, ubicacion_lon, operador_asignado) VALUES 
('AMB-001', 'ambulancia', 'disponible', 19.4326, -99.1332, 'Juan Pérez'),
('AMB-002', 'ambulancia', 'disponible', 19.4340, -99.1345, 'María González'),
('PAT-001', 'patrulla', 'disponible', 19.4330, -99.1320, 'Roberto Sánchez'),
('PAT-002', 'patrulla', 'disponible', 19.4315, -99.1350, 'Laura Martínez');

INSERT INTO Estadisticas_Tiempos (tipo_emergencia, tiempo_promedio, tiempo_minimo, tiempo_maximo, total_casos) VALUES 
('Emergencia Médica', 8, 3, 15, 0),
('Persona Extraviada', 12, 5, 25, 0);

-- Insertar datos de prueba
USE monitoreo_privada;

INSERT INTO USUARIOS (nombre, email, password, tipo_usuario) VALUES 
('Administrador', 'admin@hospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'medico'),
('Juan Pérez', 'juan@familia.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'familiar');

INSERT INTO Pacientes (codigo, id_usuario, nombre_paciente, edad, enfermedades_cronicas) VALUES 
('ESP32-001', 1, 'María González', 72, 'Hipertensión, Diabetes'),
('ESP32-002', 2, 'Carlos López', 68, 'Problemas cardíacos');

INSERT INTO Umbrales_Alerta (id_dispositivo) VALUES 
('ESP32-001'),
('ESP32-002');

-- Insertar algunas lecturas de prueba
INSERT INTO Lecturas (id_dispositivo, lectura_FC, lectura_SpO2, lectura_temperatura, gps_lat, gps_lon, fecha_lectura) VALUES 
('ESP32-001', 75, 98, 36.5, 19.432607, -99.133208, NOW() - INTERVAL 1 HOUR),
('ESP32-001', 78, 97, 36.6, 19.432607, -99.133208, NOW() - INTERVAL 45 MINUTE),
('ESP32-001', 82, 96, 36.7, 19.432607, -99.133208, NOW() - INTERVAL 30 MINUTE),
('ESP32-001', 85, 95, 36.8, 19.432607, -99.133208, NOW() - INTERVAL 15 MINUTE);