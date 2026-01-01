# Changelog

Todas las cambios notables en el Sistema de Hojas de Servicio se documentan en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto se adhiere a [Semantic Versioning](https://semver.org/lang/es/).

## [0.0.1-beta2] - 2025-12-31

### Agregado
- ✨ Tablas de base de datos para gestión de clientes, proyectos y contactos
  - `clientes`: Información de clientes con contacto principal
  - `proyectos`: Proyectos organizados por cliente
  - `contactos`: Contactos de proyectos con cargos específicos
- ✨ Columnas nuevas en `intervenciones`:
  - `hora`: Hora de la intervención (DEFAULT 09:00:00)
  - `cliente_id`: FOREIGN KEY a tabla clientes
  - `proyecto_id`: FOREIGN KEY a tabla proyectos
  - `contacto_id`: FOREIGN KEY a tabla contactos
- ✨ Datos demo realistas y completos:
  - 4 clientes (Acme Corporation, Tech Solutions, Innovatech Labs, GlobalBank)
  - 5 proyectos organizados por cliente
  - 6 contactos con cargos específicos
  - 5 intervenciones con todos los campos relacionados
- ✨ Relaciones FOREIGN KEY con cascada de eliminación
- ✨ Índices optimizados para búsquedas

### Cambiado
- 🔄 Actualización de versión: `0.0.1-beta` → `0.0.1-beta2`
- 🔄 Nombre de imagen Docker: `localhost/forms_app:latest` → `odt/forms_app:0.0.1-beta2`
- 🔄 Estructura de intervenciones para soportar relaciones many-to-one con clientes/proyectos/contactos
- 🔄 Actualización de documentación (README, README-DOCKER, etc.)

### Corregido
- 🐛 Advertencias de array key undefined en `view_pdf.php` al acceder a `cliente_id`, `proyecto_id`, `contacto_id`
- 🐛 Falta de sincronización entre estructura de BD y código de la aplicación
- 🐛 Campos del formulario no se guardaban correctamente (cliente_id, proyecto_id, contacto_id, hora)
- 🐛 Datos demo incompletos sin información de relaciones
- 🐛 Error de conexión al guardar firma del técnico en contenedor Docker (faltaba `credentials: 'same-origin'` en fetch)

### Seguridad
- 🔐 Validación mejorada de acceso a arrays con isset() checks
- 🔐 Uso correcto de prepared statements en todas las consultas relacionadas
- 🔐 Validación de relaciones (FOREIGN KEYs) a nivel de base de datos

## [0.0.1-beta] - 2025-12-30

### Agregado
- ✨ Sistema Docker/Podman standalone
- ✨ Containerización completa con docker-compose
- ✨ Script de entrypoint automático para inicialización
- ✨ Autenticación de usuarios (Trabajadores y Administradores)
- ✨ Registro y gestión de intervenciones
- ✨ Generación automática de PDFs
- ✨ Firma digital de clientes (SignaturePad.js)
- ✨ Búsqueda y filtrado de intervenciones
- ✨ Exportación de datos (CSV, JSON)
- ✨ Panel de administración para:
  - Gestión de usuarios
  - Configuración de branding
  - Gestión de clientes, proyectos y contactos
- ✨ Interfaz responsive (Mobile, Tablet, Desktop)
- ✨ Sistema de roles y permisos (Trabajador, Admin)
- ✨ Validación de entrada en todos los formularios
- ✨ Protección contra SQL injection
- ✨ Sesiones seguras con timeout

### Características de Base de Datos
- ✨ MariaDB 11.8 en contenedor
- ✨ Tabla de usuarios con roles
- ✨ Tabla de intervenciones
- ✨ Tabla de configuración de branding
- ✨ Caracteres UTF-8 multibyte (utf8mb4_unicode_ci)
- ✨ Datos demo con usuarios de prueba

### Documentación
- 📖 README.md - Guía general
- 📖 README-DOCKER.md - Guía de Docker/Podman
- 📖 INSTALL.md - Instalación manual
- 📖 QUICK_START.md - Inicio rápido
- 📖 .env.example - Plantilla de configuración

### Infraestructura
- 🐳 Dockerfile optimizado (PHP 8.4.11-apache)
- 🐳 docker-compose.yml con servicios app y db
- 🐳 Volúmenes persistentes para PDFs y BD
- 🐳 Red bridge para comunicación entre contenedores
- 🐳 Health checks para MariaDB
- 🐳 Soporte para Docker y Podman

---

## Notas de Versión

### v0.0.1-beta2 (Actual)
- Sistema completamente funcional con estructura de BD completa
- Todas las relaciones entre tablas implementadas
- Datos demo realistas y consistentes
- Listo para pruebas comprensivas

### v0.0.1-beta
- Versión inicial con funcionalidad básica
- Docker/Podman funcionando
- Interfaz web operativa
- Estructura base lista

## Próximas Versiones Planeadas

### v0.1.0
- [ ] Mejoras en UI/UX
- [ ] Más validaciones
- [ ] Reportes avanzados
- [ ] Sistema de notificaciones

### v0.2.0
- [ ] API REST
- [ ] Integración con webhooks
- [ ] Backup automático
- [ ] Sincronización multi-dispositivo

### v1.0.0
- [ ] Estabilidad de producción
- [ ] Performance optimizado
- [ ] Documentación completa
- [ ] Suite de pruebas completa
