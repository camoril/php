-- ========================================
-- Base de Datos: Sistema de Hojas de Servicio
-- ========================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS forms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE forms_db;

-- Crear usuarios de base de datos
-- IMPORTANTE: En producción cPanel, cambiar 'forms_secure_password_2025' por una contraseña segura
-- Usuario para acceso local (instalación directa)
CREATE USER IF NOT EXISTS 'forms_user'@'localhost' IDENTIFIED BY 'forms_secure_password_2025';
GRANT ALL PRIVILEGES ON forms_db.* TO 'forms_user'@'localhost';
-- Usuario para Docker/Podman (acceso desde contenedor)
CREATE USER IF NOT EXISTS 'forms_user'@'%' IDENTIFIED BY 'forms_secure_password_2025';
GRANT ALL PRIVILEGES ON forms_db.* TO 'forms_user'@'%';
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
INSERT IGNORE INTO intervenciones (fecha, cliente, descripcion, responsable_trabajador, responsable_cliente, horas_ocupadas, estado, notas_adicionales, usuario_id) VALUES
('2025-12-15', 'Acme Corporation', 'Instalación y configuración de router Cisco serie 2900 con OSPF. Configuración de VLANs 10, 20, 30 para departamentos de ventas, IT y administración. Implementación de listas de acceso (ACLs) para segmentación de red.', 'Juan García Pérez', 'Carlos Mendez Flores', 2.5, 'pendiente', 'Cliente solicita documentación de configuración. Pendiente programar capacitación para personal IT.', 2),
('2025-12-10', 'Tech Solutions S.A.', 'Mantenimiento preventivo semestral de switches Cisco Catalyst serie 2960. Limpieza de puertos, actualización de firmware a versión 15.2(7), verificación de redundancia de enlaces y pruebas de failover. Revisión de logs de eventos.', 'Juan García Pérez', 'María López García', 1.5, 'pendiente', 'Switches en buen estado. Recomendado reemplazo de 2 módulos SFP en 6 meses.', 2),
('2025-12-05', 'Acme Corporation', 'Diagnóstico de problema de conectividad intermitente en red LAN. Identificado cable categoría 6 defectuoso en patch panel. Reemplazo de cable, pruebas con certificador Fluke, verificación de throughput. Problema resuelto.', 'Juan García Pérez', 'Carlos Mendez Flores', 3.0, 'firmado', 'Cliente satisfecho con la rapidez del diagnóstico. Cable defectuoso presentaba 40% de pérdida de paquetes.', 2),
('2025-11-28', 'Innovatech Labs', 'Instalación de punto de acceso WiFi 6 Cisco Catalyst 9115AX en área de laboratorio. Configuración de SSID corporativo con autenticación 802.1X (RADIUS), optimización de canales y potencia de transmisión. Survey de cobertura realizado.', 'Juan García Pérez', 'Roberto Sánchez Torres', 4.0, 'firmado', 'Cobertura óptima confirmada. Cliente aprobó extensión del proyecto a 3 pisos adicionales.', 2),
('2025-11-20', 'GlobalBank S.A.', 'Auditoría de seguridad de red perimetral. Revisión de configuración de firewall FortiGate 200F, validación de reglas NAT, análisis de políticas de seguridad. Generación de reporte con 12 recomendaciones de hardening.', 'Juan García Pérez', 'Ana Patricia Ruiz', 5.5, 'firmado', 'Implementadas 8 de 12 recomendaciones durante la visita. Programada segunda fase para próximo mes.', 2);

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
