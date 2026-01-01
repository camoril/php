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
-- TABLA: Clientes
-- ========================================
CREATE TABLE IF NOT EXISTS clientes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(150) NOT NULL UNIQUE,
    email VARCHAR(100),
    telefono VARCHAR(20),
    direccion TEXT,
    contacto_principal VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABLA: Proyectos
-- ========================================
CREATE TABLE IF NOT EXISTS proyectos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cliente_id INT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    INDEX idx_cliente_id (cliente_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABLA: Contactos
-- ========================================
CREATE TABLE IF NOT EXISTS contactos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    proyecto_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    cargo VARCHAR(100),
    email VARCHAR(100),
    telefono VARCHAR(20),
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE,
    INDEX idx_proyecto_id (proyecto_id),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABLA: Intervenciones (Hojas de Servicio)
-- ========================================
CREATE TABLE IF NOT EXISTS intervenciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fecha DATE NOT NULL,
    hora TIME DEFAULT '09:00:00',
    cliente VARCHAR(150) NOT NULL,
    cliente_id INT,
    proyecto_id INT,
    contacto_id INT,
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
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE SET NULL,
    FOREIGN KEY (contacto_id) REFERENCES contactos(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_cliente (cliente),
    INDEX idx_cliente_id (cliente_id),
    INDEX idx_proyecto_id (proyecto_id),
    INDEX idx_contacto_id (contacto_id),
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
-- INSERTS: Clientes de Ejemplo
-- ========================================
INSERT IGNORE INTO clientes (id, nombre, email, telefono, direccion, contacto_principal) VALUES
(1, 'Acme Corporation', 'contacto@acme.com', '+52-555-1234', 'Av. Paseo de la Reforma 505, México City', 'Carlos Mendez Flores'),
(2, 'Tech Solutions S.A.', 'info@techsolutions.com', '+52-555-5678', 'Calle Tecnológica 123, Monterrey', 'María López García'),
(3, 'Innovatech Labs', 'admin@innovatech.mx', '+52-555-9012', 'Parque Científico 456, Guadalajara', 'Roberto Sánchez Torres'),
(4, 'GlobalBank S.A.', 'operations@globalbank.com', '+52-555-3456', 'Centro Financiero, Piso 25, México City', 'Ana Patricia Ruiz');

-- ========================================
-- INSERTS: Proyectos de Ejemplo
-- ========================================
INSERT IGNORE INTO proyectos (id, cliente_id, nombre, descripcion, estado) VALUES
(1, 1, 'Infraestructura de Red - Sede Principal', 'Diseño, instalación y configuración de infraestructura de red corporativa con redundancia', 'activo'),
(2, 1, 'Seguridad Perimetral', 'Implementación de firewalls y sistemas de detección de intrusos', 'activo'),
(3, 2, 'Mantenimiento de Switches', 'Mantenimiento preventivo y correctivo de equipos de conmutación', 'activo'),
(4, 3, 'Infraestructura WiFi 6', 'Instalación de puntos de acceso WiFi 6 en todas las áreas', 'activo'),
(5, 4, 'Auditoría de Seguridad Integral', 'Evaluación completa de políticas y configuraciones de seguridad', 'activo');

-- ========================================
-- INSERTS: Contactos de Ejemplo
-- ========================================
INSERT IGNORE INTO contactos (id, proyecto_id, nombre, cargo, email, telefono, activo) VALUES
(1, 1, 'Carlos Mendez Flores', 'Gerente de TI', 'carlos.mendez@acme.com', '+52-555-1111', 1),
(2, 1, 'Diana Rodríguez López', 'Coordinadora de Red', 'diana.rodriguez@acme.com', '+52-555-1112', 1),
(3, 2, 'Luis Fernando Jiménez', 'Jefe de Seguridad', 'luis.jimenez@acme.com', '+52-555-1113', 1),
(4, 3, 'María López García', 'Supervisora de Infraestructura', 'maria.lopez@techsolutions.com', '+52-555-2222', 1),
(5, 4, 'Roberto Sánchez Torres', 'Director Técnico', 'roberto.sanchez@innovatech.mx', '+52-555-3333', 1),
(6, 5, 'Ana Patricia Ruiz', 'Oficial de Cumplimiento', 'ana.ruiz@globalbank.com', '+52-555-4444', 1);

-- ========================================
-- INSERTS: Intervenciones de Ejemplo
-- ========================================
INSERT IGNORE INTO intervenciones (fecha, hora, cliente, cliente_id, proyecto_id, contacto_id, descripcion, responsable_trabajador, responsable_cliente, horas_ocupadas, estado, notas_adicionales, usuario_id) VALUES
('2025-12-15', '09:30:00', 'Acme Corporation', 1, 1, 1, 'Instalación y configuración de router Cisco serie 2900 con OSPF. Configuración de VLANs 10, 20, 30 para departamentos de ventas, IT y administración. Implementación de listas de acceso (ACLs) para segmentación de red. Pruebas de conectividad exitosas entre todas las VLANs.', 'Juan García Pérez', 'Carlos Mendez Flores', 2.5, 'pendiente', 'Cliente solicita documentación completa de la configuración OSPF. Pendiente programar capacitación para personal IT. Se entregará manual de operación del router.', 2),

('2025-12-10', '10:00:00', 'Tech Solutions S.A.', 2, 3, 4, 'Mantenimiento preventivo semestral de switches Cisco Catalyst serie 2960. Limpieza física de puertos, actualización de firmware a versión 15.2(7), verificación de redundancia de enlaces y pruebas de failover entre switches. Revisión completa de logs de eventos y contadores de interfaz.', 'Juan García Pérez', 'María López García', 1.5, 'pendiente', 'Equipos en excelente estado operativo. Recomendado planificar reemplazo de 2 módulos SFP defectuosos dentro de 6 meses. Se generó reporte de desempeño detallado.', 2),

('2025-12-05', '14:15:00', 'Acme Corporation', 1, 1, 2, 'Diagnóstico de problema de conectividad intermitente en red LAN. Identificado cable categoría 6 defectuoso en patch panel ubicado en Data Center. Reemplazo completo del cable, pruebas con certificador Fluke Networks, verificación de velocidad y throughput. Problema resuelto completamente.', 'Juan García Pérez', 'Carlos Mendez Flores', 3.0, 'firmado', 'Cliente muy satisfecho con la rapidez del diagnóstico. Cable defectuoso presentaba 40% de pérdida de paquetes. Nuevo cable cumple con certificación CAT6A. Se guardó certificado de prueba en base de datos.', 2),

('2025-11-28', '08:00:00', 'Innovatech Labs', 3, 4, 5, 'Instalación de punto de acceso WiFi 6 Cisco Catalyst 9115AX en área de laboratorio. Configuración de SSID corporativo con autenticación 802.1X (RADIUS), optimización de canales (2.4GHz y 5GHz), ajuste de potencia de transmisión. Survey de cobertura realizado con aplicación Ekahau. Todas las áreas cubiertas con señal >-67 dBm.', 'Juan García Pérez', 'Roberto Sánchez Torres', 4.0, 'firmado', 'Cobertura óptima confirmada por cliente y equipo técnico interno. Cliente autorizó extensión del proyecto a 3 pisos adicionales para Q1 2026. Se documentaron coordenadas GPS de AP y planos de cobertura.', 2),

('2025-11-20', '11:00:00', 'GlobalBank S.A.', 4, 5, 6, 'Auditoría completa de seguridad de red perimetral. Revisión detallada de configuración de firewall FortiGate 200F, validación de reglas NAT, análisis de políticas de seguridad, revisión de logs de acceso remoto. Generación de reporte ejecutivo con 12 recomendaciones de hardening y medidas correctivas prioritarias.', 'Juan García Pérez', 'Ana Patricia Ruiz', 5.5, 'firmado', 'Implementadas 8 de 12 recomendaciones durante la visita. Programada segunda fase para próximo mes. Documento de remediación compartido con área de Cumplimiento Normativo. Score de seguridad mejorado de 6.2 a 8.1 de 10.', 2);

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
