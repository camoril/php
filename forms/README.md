# Sistema de Hojas de Servicio

![Version](https://img.shields.io/badge/version-0.0.1--beta-blue)
![PHP](https://img.shields.io/badge/PHP-8.4%2B-777BB4?logo=php)
![MariaDB](https://img.shields.io/badge/MariaDB-11.8%2B-003545?logo=mariadb)
![License](https://img.shields.io/badge/license-MIT-green)

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

### Opción 1: Docker/Podman (Más Rápido) 🐳

```bash
# 1. Clonar el repositorio
git clone https://github.com/camoril/php.git
cd php/forms

# 2. Levantar contenedores (con Docker)
docker-compose up -d

# O con Podman
podman-compose up -d

# 3. Acceder en http://localhost:8080
# Usuario: admin / Contraseña: admin123
```

📚 **Documentación completa**: [README-DOCKER.md](README-DOCKER.md)

### Opción 2: Script Automático

```bash
# 1. Clonar el repositorio
git clone https://github.com/tu-usuario/forms.git
cd forms

# 2. Ejecutar instalador
sudo bash install.sh

# 3. Abrir en navegador
http://localhost/forms
```

### Opción 3: Manual

Ver [INSTALL.md](INSTALL.md) para instrucciones detalladas.

### Credenciales Iniciales

```
Usuario: admin
Contraseña: admin123
```

> ⚠️ **IMPORTANTE**: Cambiar contraseña en producción

## 📖 Documentación

- **[README.md](README.md)** - Información general (este archivo)
- **[README-DOCKER.md](README-DOCKER.md)** - Guía de Docker/Podman
- **[INSTALL.md](INSTALL.md)** - Guía completa de instalación
- **[QUICK_START.md](QUICK_START.md)** - Inicio rápido y referencia
- **[.env.example](.env.example)** - Configuración de variables de entorno

## 🔧 Configuración

### Base de Datos

Editar `config/database.php` con tus credenciales:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'forms_db');
define('DB_USER', 'forms_user');
define('DB_PASS', 'your_secure_password');
```

### Branding (Logo y Colores)

1. Iniciar sesión como administrador
2. Ir a **Administración → Branding**
3. Subir logo y configurar colores corporativos
4. Los cambios se reflejan inmediatamente en los PDFs

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

## 🐛 Solución de Problemas

Ver la sección de **Troubleshooting** en [INSTALL.md](INSTALL.md) para problemas comunes.

## 🤝 Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📝 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 👨‍💻 Autor

Desarrollado con ❤️ por [Tu Nombre]

## 🙏 Agradecimientos

- Bootstrap por el framework CSS
- SignaturePad.js por la firma digital
- Font Awesome por los iconos
- La comunidad de PHP por las mejores prácticas

---

**Versión**: 0.0.1 Beta  
**Estado**: En desarrollo activo  
**Última actualización**: 31 de Diciembre 2025

### 5. Acceder a la Aplicación

- **URL**: `http://localhost/forms`
- **Usuario**: admin / admin123
- **Usuario Trabajador**: juan / juan123

## 🔐 Usuarios por Defecto

| Usuario | Contraseña | Rol |
|---------|-----------|-----|
| admin | admin123 | Administrador |
| juan | juan123 | Trabajador |

> ⚠️ **IMPORTANTE**: Cambiar contraseñas en producción

## 📦 Instalación en cPanel

### 1. Preparación

1. Subir carpeta `/forms` a `public_html`
2. Crear BD en cPanel:
   - Nombre: `forms_db`
   - Usuario: `forms_user`
3. Importar `setup/database.sql`

### 2. Actualizar Configuración

Editar `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'forms_db');
define('DB_USER', 'forms_user');
define('DB_PASS', 'TU_CONTRASEÑA_CPANEL');
define('APP_URL', 'https://tudominio.com.mx/forms');
```

### 3. Configurar .htaccess

Crear `.htaccess` en `/forms/`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /forms/
    
    # Permitir acceso a archivos y directorios reales
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    
    # Redirigir todo a index.php si no existe el archivo
    RewriteRule ^(.*)$ index.php [L]
</IfModule>
```

## 🔄 Flujo de Uso

1. **Trabajador** inicia sesión
2. **Registra** nueva intervención con datos
3. **Sistema** genera PDF preformato
4. **Cliente** firma el documento desde celular
5. **Firma** se guarda en BD y se asocia a PDF
6. **Reportes** se pueden consultar por cliente/fecha

## 📝 Notas Importantes

- Las firmas digitales NO son legalmente vinculantes (se requeriría e.firma para eso)
- Se recomienda agregar checkbox de aceptación de términos
- Los PDFs se almacenan en `/pdfs/`
- Las firmas se guardan en base64 en la BD

## 🐛 Troubleshooting

### Error: "Access denied for user 'forms_user'"

```bash
# Verificar usuario existe
sudo mariadb -u root -e "SELECT user FROM mysql.user LIKE 'forms_%';"

# Recrear usuario
sudo mariadb -u root < setup/database.sql
```

### Error: "PDFs directory not writable"

```bash
sudo chown -R www-data:www-data /var/www/html/forms/pdfs
sudo chmod 777 /var/www/html/forms/pdfs
```

### Error: "Cannot connect to database"

Verificar credenciales en `config/database.php` y que MariaDB esté corriendo:

```bash
sudo systemctl status mariadb
```

## 📞 Soporte

Para preguntas o problemas, contactar al administrador del sistema.

## 📄 Licencia

Desarrollo interno - No redistribuible

---

**Versión**: 1.0.0  
**Última actualización**: 31 de Diciembre de 2025
