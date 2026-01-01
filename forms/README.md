# Sistema de Hojas de Servicio

![Version](https://img.shields.io/badge/version-0.0.1--beta2-blue)
![PHP](https://img.shields.io/badge/PHP-8.4%2B-777BB4?logo=php)
![MariaDB](https://img.shields.io/badge/MariaDB-11.8%2B-003545?logo=mariadb)
![License](https://img.shields.io/badge/license-GPLv3-blue)

Sistema web para gestionar y registrar hojas de servicio (intervenciones) con firma digital de clientes. Diseñado para empresas de servicios técnicos que necesitan documentar sus intervenciones con validación del cliente.

## ✨ Características Principales

- 🔐 **Autenticación de usuarios** (Trabajadores y Administradores)
- 📝 **Registro de intervenciones** con toda la información del servicio
- 🏢 **Gestión de clientes, proyectos y contactos** (jerarquía organizada)
- 📄 **Generación automática de PDFs** personalizables
- ✍️ **Firma digital** de clientes (touch/mouse)
- 🔍 **Búsqueda y filtrado** avanzado por múltiples criterios
- 📊 **Exportación de datos** (CSV, JSON)
- 🎨 **Branding personalizable** (logos, colores, información de empresa)
- 👥 **Gestión de usuarios** (panel de administración)
- ⏰ **Registro de hora de creación** para cada intervención
- 🌐 **Responsive design** (funciona en móvil/tablet/desktop)

## 🛠️ Stack Tecnológico

- **Backend**: PHP 8.4+
- **Base de Datos**: MySQL 8.0+ / MariaDB 11.8+
- **Frontend**: Bootstrap 5.3, Vanilla JavaScript
- **Servidor**: Apache 2.4+ (con mod_rewrite)
- **Dependencias**: SignaturePad.js (firma digital)

## 📁 Estructura del Proyecto

```
/forms/
├── index.php                  # Página de login
├── dashboard.php              # Panel principal (SPA)
├── view_pdf.php               # Visualizador de PDF
├── sign_pdf.php               # Endpoint para firma digital
├── edit_intervention.php      # Editar intervención
├── delete_intervention.php    # Eliminar intervención
├── get_intervention.php       # Obtener datos de intervención
├── export_interventions.php   # Exportar datos
├── manage_users.php           # Gestión de usuarios (admin)
├── manage_branding.php        # Gestión de marca (admin)
├── manage_clientes.php        # Gestión de clientes (admin)
├── install.sh                 # Script de instalación automática
├── .env.example               # Plantilla de configuración
├── config/
│   └── database.php           # Configuración de base de datos
├── php/
│   ├── auth.php               # Funciones de autenticación
│   ├── interventions.php      # Funciones de intervenciones
│   └── logout.php             # Cerrar sesión
├── setup/
│   └── database.sql           # Script SQL inicial
├── assets/
│   ├── img/                   # Imágenes
│   └── logos/                 # Logos de branding
└── pdfs/                      # PDFs generados (gitignored)
```

## 🚀 Instalación Rápida

### Con Docker/Podman (Recomendado)

```bash
git clone https://github.com/camoril/php.git
cd php/forms
docker-compose up -d
# Acceder: http://localhost:8080
# Usuario: admin / Contraseña: admin123
```

### Instalación Tradicional

```bash
git clone https://github.com/camoril/php.git
cd php/forms
sudo bash install.sh
```

📚 **Para más opciones de instalación, consulta [INSTALL.md](INSTALL.md)**

## 📖 Documentación

- **[INSTALL.md](INSTALL.md)** - Guía completa de instalación (Docker, tradicional, cPanel)
- **[DOCKER.md](DOCKER.md)** - Guía detallada de Docker/Podman (volúmenes, comandos, troubleshooting)
- **[CHANGELOG.md](CHANGELOG.md)** - Historial de cambios y versiones

## 🔧 Configuración

### Branding (Logo y Colores)

1. Iniciar sesión como **admin**
2. Ir a **Administración → Branding**
3. Subir logo y configurar colores corporativos
4. Los cambios se reflejan inmediatamente en los PDFs

### Base de Datos

La base de datos se configura automáticamente con Docker. Para instalación manual, consulta [INSTALL.md](INSTALL.md).

## 👥 Roles de Usuario

### Trabajador
- Crear y editar sus propias intervenciones
- Ver lista de sus intervenciones
- Generar y descargar PDFs
- Ver clientes, proyectos y contactos

### Administrador
- Todo lo que puede hacer un trabajador
- Gestionar usuarios (crear, editar, eliminar)
- Gestionar clientes, proyectos y contactos
- Configurar branding (logos, colores)
- Ver todas las intervenciones del sistema
- Eliminar intervenciones de cualquier usuario

## 🔐 Seguridad

- ✅ Contraseñas hasheadas con bcrypt
- ✅ Sesiones seguras con timeout
- ✅ Validación de entrada en todos los formularios
- ✅ Prepared statements (PDO) para prevenir SQL injection
- ✅ Verificación de permisos en cada endpoint
- ✅ Protección de archivos sensibles con .htaccess

## 📦 Requisitos del Sistema

### Mínimos
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Apache 2.4+ con mod_rewrite
- 100 MB espacio en disco
- 256 MB RAM

### Recomendados
- PHP 8.4+
- MariaDB 11.8+
- Apache 2.4+ con mod_rewrite
- 1 GB espacio en disco
- 512 MB RAM



## 🤝 Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

**Nota**: Todas las contribuciones se distribuyen bajo la licencia GPLv3.

## 📝 Licencia

Este proyecto está bajo la Licencia Pública General GNU v3 (GPLv3).
Consulta el archivo [LICENSE](LICENSE) para más detalles.

## 🙏 Agradecimientos

- [Bootstrap 5.3](https://getbootstrap.com/) - Framework CSS
- [SignaturePad.js](https://github.com/szimek/signature_pad) - Firma digital
- [Font Awesome](https://fontawesome.com/) - Iconos
- Comunidad de PHP y MariaDB

## 🔄 Flujo de Trabajo

1. **Trabajador** inicia sesión en el sistema
2. **Registra** nueva intervención con datos del servicio
3. **Sistema** genera PDF automáticamente
4. **Cliente** firma digitalmente desde cualquier dispositivo
5. **PDF final** con firma se almacena y puede descargarse

## 🐛 Solución de Problemas

Para problemas comunes y soluciones, consulta:
- **Docker/Podman**: [DOCKER.md - Sección Troubleshooting](DOCKER.md#troubleshooting)
- **Instalación tradicional**: [INSTALL.md - Sección Troubleshooting](INSTALL.md#troubleshooting)

## 📞 Soporte

- **Repositorio**: https://github.com/camoril/php
- **Issues**: https://github.com/camoril/php/issues

---

**Versión**: 0.0.1 Beta 2  
**Última actualización**: 31 de Diciembre 2025
