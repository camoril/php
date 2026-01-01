# 📦 Instalación - Sistema de Hojas de Servicio

## 🚀 Instalación Rápida (Con Script)

### Requisitos Previos
- PHP 8.4+
- MariaDB 11.8+
- Apache 2.4+ con mod_rewrite
- Permisos de sudo (para ejecutar instalador)

### Pasos

```bash
# 1. Ir al directorio de la aplicación
cd /var/www/html/forms

# 2. Ejecutar el script de instalación
sudo bash install.sh

# 3. Esperar a que se complete (2-3 minutos)

# 4. Abrir en navegador
# http://localhost/forms
```

### Credenciales Iniciales

```
Usuario: admin
Contraseña: admin123
```

> ⚠️ **IMPORTANTE**: Cambiar contraseñas en producción

---

## 🔧 Instalación Manual

Si el script automatizado no funciona, seguir estos pasos:

### 1. Crear Base de Datos

**Opción A: Desde línea de comandos**

```bash
# Ejecutar como root
sudo mysql < /var/www/html/forms/setup/database.sql
```

**Opción B: Desde cliente MySQL/MariaDB**

```bash
# Conectar como usuario root
mysql -u root -p

# Pegar el contenido de setup/database.sql y ejecutar
```

### 2. Verificar Creación de BD

```bash
# Conectar como nuevo usuario
mysql -u forms_user -p forms_db

# Contraseña: your_secure_password

# Verificar tablas
SHOW TABLES;
```

Deberías ver:
```
+------------------------------+
| Tables_in_forms_db |
+------------------------------+
| intervenciones               |
| usuarios                     |
+------------------------------+
```

### 3. Configurar Permisos de Directorios

```bash
# Cambiar propietario a Apache
sudo chown -R www-data:www-data /var/www/html/forms

# Asignar permisos
sudo chmod -R 755 /var/www/html/forms
sudo chmod 777 /var/www/html/forms/pdfs

# Proteger archivos de configuración
sudo chmod 640 /var/www/html/forms/config/*.php
```

### 4. Verificar Permisos de Apache

```bash
# Habilitarmod_rewrite si no está activo
sudo a2enmod rewrite

# Reiniciar Apache
sudo systemctl restart apache2
```

### 5. Probar Acceso

Abrir en navegador:
```
http://localhost/forms
```

---

## ⚙️ Configuración para cPanel

### Paso 1: Subir Archivos

1. Conectar por SFTP o File Manager en cPanel
2. Subir contenido de `/var/www/html/forms` a:
   ```
   public_html/forms/
   ```

### Paso 2: Crear Base de Datos en cPanel

1. **Acceder a cPanel**
2. **MySQL Databases**
3. **Crear nueva base de datos**
   - Nombre: `forms_db`
4. **Crear nuevo usuario**
   - Usuario: `forms_user`
   - Contraseña: (genera una fuerte)
5. **Asignar usuario a BD**
   - Otorgar ALL PRIVILEGES

### Paso 3: Importar Base de Datos

En cPanel **phpMyAdmin**:
1. Crear BD vacía: `forms_db`
2. Seleccionar BD
3. Ir a pestaña **Import**
4. Seleccionar archivo: `/setup/database.sql`
5. Click **Import**

### Paso 4: Actualizar Configuración

Editar `/forms/config/database.php`:

```php
define('DB_PASS', 'TU_CONTRASEÑA_CPANEL'); // Cambiar
define('APP_URL', 'https://tudominio.com.mx/forms'); // Cambiar
```

### Paso 5: Asegurar Permisos en cPanel

**Vía File Manager:**
1. Seleccionar carpeta `pdfs`
2. **Change Permissions**: `755`
3. Recurse into subdirectories: ✅

---

## ✅ Verificación Post-Instalación

### Checklist

- [ ] Base de datos creada
- [ ] Usuario `forms_user` existe
- [ ] Directorio `/pdfs` tiene permisos de escritura
- [ ] Apache mod_rewrite está habilitado
- [ ] `.htaccess` está en lugar correcto
- [ ] Puede acceder a `http://localhost/forms`
- [ ] Login funciona con `admin/admin123`
- [ ] Dashboard carga correctamente

### Prueba Rápida

```bash
# Verificar BD
mysql -u forms_user -p -e "USE forms_db; SELECT COUNT(*) as usuarios FROM usuarios;"

# Deberías ver: 2 usuarios
```

---

## 🐛 Troubleshooting

### Error: "Access denied for user 'forms_user'"

**Causa:** BD no creada o usuario incorrecto

**Solución:**
```bash
sudo mysql < /var/www/html/forms/setup/database.sql
```

### Error: "PDFs directory not writable"

**Causa:** Permisos incorrectos

**Solución:**
```bash
sudo chown -R www-data:www-data /var/www/html/forms/pdfs
sudo chmod 777 /var/www/html/forms/pdfs
```

### Error: "404 Not Found"

**Causa:** mod_rewrite no está habilitado o .htaccess no funciona

**Solución:**
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2

# O en cPanel: habilitar .htaccess en configuración
```

### Error: "Fatal error: Call to undefined function password_verify()"

**Causa:** PHP < 5.5.0 (muy antiguo)

**Solución:** Actualizar PHP (debe estar en 8.4+)

### Error: "Class PDO not found"

**Causa:** PDO no está habilitado en PHP

**Solución:**
```bash
# En Ubuntu/Debian
sudo apt-get install php-mysql
sudo systemctl restart apache2
```

---

## 📝 Notas Importantes

1. **Contraseña por Defecto**: Cambiar en producción
2. **HTTPS en Producción**: Modificar `APP_URL` a `https://`
3. **Backups**: Hacer backups regulares de BD
4. **Seguridad**: Proteger `config/database.php` con .htaccess
5. **Logs**: Revisar logs de Apache en `/var/log/apache2/error.log`

---

## 📞 Soporte

Para problemas:
1. Revisar logs de Apache
2. Verificar BD con phpMyAdmin
3. Probar conexión PHP a BD manualmente
4. Contactar administrador del sistema

---

**Última actualización:** 31 de Diciembre de 2025
**Versión:** 1.0.0
