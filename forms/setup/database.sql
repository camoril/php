-- ========================================
-- Base de Datos: Sistema de Hojas de Servicio
-- ========================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS forms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE forms_db;

-- Crear usuario de base de datos
-- IMPORTANTE: Cambiar 'your_secure_password' por una contraseña segura
CREATE USER IF NOT EXISTS 'forms_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON forms_db.* TO 'forms_user'@'localhost';
FLUSH PRIVILEGES;

-- ========================================
-- TABLA: Usuarios (Trabajadores)
-- ========================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    tipo ENUM('trabajador', 'admin') DEFAULT 'trabajador',
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABLA: Intervenciones (Hojas de Servicio)
-- ========================================
CREATE TABLE IF NOT EXISTS intervenciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fecha DATE NOT NULL,
    cliente VARCHAR(150) NOT NULL,
    descripcion TEXT NOT NULL,
    responsable_trabajador VARCHAR(100) NOT NULL,
    responsable_cliente VARCHAR(100),
    horas_ocupadas DECIMAL(6,2) NOT NULL,
    estado ENUM('pendiente', 'firmado') DEFAULT 'pendiente',
    pdf_path VARCHAR(255),
    firma_base64 LONGTEXT,
    notas_adicionales TEXT,
    usuario_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_cliente (cliente),
    INDEX idx_fecha (fecha),
    INDEX idx_estado (estado),
    INDEX idx_usuario_id (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- INSERTS: Usuarios de Ejemplo
-- ========================================
-- Usuario: admin | Contraseña: admin123
-- Usuario: juan | Contraseña: juan123
INSERT IGNORE INTO usuarios (username, password, nombre, tipo) VALUES
('admin', '$2y$12$Gz5i6QvkuCfAzrb.iq4PweukGZ3QP1wM0jsSwO89ziFgbj.UAEide', 'Administrador Sistema', 'admin'),
('juan', '$2y$12$z4LnD31PQh7C6dck5VDaR.Zw2I8Fr/XYUuuh9OCpWbuZpkH8UZtYq', 'Juan García Pérez', 'trabajador');

-- ========================================
-- INSERTS: Intervenciones de Ejemplo
-- ========================================
INSERT IGNORE INTO intervenciones (fecha, cliente, descripcion, responsable_trabajador, responsable_cliente, horas_ocupadas, estado, usuario_id) VALUES
('2025-12-15', 'Acme Corporation', 'Instalación de router Cisco serie 2900', 'Juan García Pérez', 'Carlos Mendez Flores', 2.5, 'pendiente', 2),
('2025-12-10', 'Tech Solutions S.A.', 'Mantenimiento preventivo de switches Cisco', 'Juan García Pérez', 'María López García', 1.5, 'pendiente', 2),
('2025-12-05', 'Acme Corporation', 'Diagnóstico de conectividad de red', 'Juan García Pérez', 'Carlos Mendez Flores', 3.0, 'firmado', 2);

-- ========================================
-- TABLA: Configuración de Branding
-- ========================================
CREATE TABLE IF NOT EXISTS configuracion_branding (
    id INT PRIMARY KEY AUTO_INCREMENT,
    logo_path VARCHAR(255),
    nombre_empresa VARCHAR(150),
    email_empresa VARCHAR(100),
    telefono_empresa VARCHAR(50),
    direccion_empresa TEXT,
    color_primario VARCHAR(7) DEFAULT '#0284C7',
    color_secundario VARCHAR(7) DEFAULT '#0EA5E9',
    mostrar_logo_pdf TINYINT(1) DEFAULT 0,
    mostrar_firma_tecnico TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- INSERTS: Configuración por defecto
-- ========================================
INSERT IGNORE INTO configuracion_branding (id, nombre_empresa, color_primario, color_secundario) VALUES
(1, 'Sistema de Hojas de Servicio', '#0284C7', '#0EA5E9');
