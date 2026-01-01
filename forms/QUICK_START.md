# 🚀 Guía Rápida - Sistema de Hojas de Servicio

## ⚡ Inicio en 3 Pasos

### 1. Instalación
```bash
cd /var/www/html/forms
sudo bash install.sh
```

### 2. Acceder
```
http://localhost/forms
```

### 3. Login
```
Usuario: admin
Contraseña: admin123
```

---

## 📍 Ubicaciones Importantes

| Elemento | Ubicación |
|----------|-----------|
| **Aplicación** | `/var/www/html/forms/` |
| **Configuración BD** | `config/database.php` |
| **Usuarios/Login** | `index.php` |
| **Panel Principal** | `dashboard.php` |
| **Base de Datos** | `forms_db` |
| **PDFs Generados** | `/pdfs/` |
| **Funciones PHP** | `php/auth.php`, `php/interventions.php` |

---

## 🔐 Usuario Inicial

| Usuario | Contraseña | Rol |
|---------|-----------|-----|
| admin | admin123 | Administrador |

> ⚠️ **IMPORTANTE**: Cambiar contraseña en producción y crear usuarios adicionales desde el panel de administración

---

## 🗄️ Base de Datos Rápida

### Conectar
```bash
mysql -u forms_user -p forms_db
Contraseña: your_secure_password
```

### Tablas
```sql
-- Ver estructura
DESCRIBE usuarios;
DESCRIBE intervenciones;

-- Ver datos
SELECT * FROM usuarios;
SELECT * FROM intervenciones;

-- Agregar usuario
INSERT INTO usuarios VALUES (NULL, 'nuevo_user', 'hash_bcrypt', 'Nombre', NULL, 'trabajador', 1, NOW(), NOW());
```

---

## 🔧 Operaciones Comunes

### Crear Nuevo Usuario (SQL)

```sql
-- Primero genera hash con PHP:
-- password_hash('contraseña123', PASSWORD_BCRYPT);

INSERT INTO usuarios (username, password, nombre, tipo) VALUES
('carlos', '$2y$10$...hash...', 'Carlos López', 'trabajador');
```

### Ver Intervenciones Pendientes

```sql
SELECT * FROM intervenciones WHERE estado = 'pendiente' ORDER BY fecha DESC;
```

### Estadísticas por Cliente

```sql
SELECT cliente, COUNT(*) as total, SUM(horas_ocupadas) as horas 
FROM intervenciones 
GROUP BY cliente 
ORDER BY total DESC;
```

### Limpiar Sesión de Usuario

```sql
-- Simplemente el usuario debe hacer logout o limpiar cookies
-- No hay tabla de sesiones en BD
```

---

## 🛠️ Configuración cPanel

Cuando muevas a cPanel:

```php
// En config/database.php cambiar:
define('DB_PASS', 'tu_contraseña_cpanel');
define('APP_URL', 'https://tudominio.com.mx/forms');
```

---

## 📊 Estado del Proyecto

### Completado (Fase 1)
✅ Autenticación  
✅ Base de datos  
✅ Dashboard básico  
✅ Funciones CRUD  
✅ Seguridad básica  

### Próximo (Fase 2)
📋 Formulario de intervenciones  
📋 Generación de PDF  
📋 Firma digital  
📋 Gestión de registros  

---

## 🔗 Archivos Clave

### Para Desarrolladores
- `php/auth.php` - Funciones de autenticación
- `php/interventions.php` - Lógica de BD
- `config/database.php` - Conexión
- `dashboard.php` - Panel principal

### Para Usuarios Finales
- `index.php` - Login
- `dashboard.php` - Interfaz principal

### Documentación
- `README.md` - Resumen
- `INSTALL.md` - Instalación completa
- `PROJECT.md` - Arquitectura
- `CHECKLIST.md` - Verificación

---

## 🐛 Problemas Comunes

| Problema | Solución |
|----------|----------|
| "Access denied" BD | Verificar contraseña en `config/database.php` |
| PDFs no se guardan | `sudo chmod 777 /var/www/html/forms/pdfs` |
| 404 en dashboard | Activar mod_rewrite: `sudo a2enmod rewrite` |
| Sesión no persiste | Verificar cookies habilitadas en navegador |
| Contraseña incorrecta | Usuario de prueba es `admin/admin123` |

---

## 📞 Referencia Rápida de SQL

### Cambiar Contraseña de Usuario
```sql
UPDATE usuarios SET password = 'nuevo_hash_bcrypt' WHERE username = 'admin';
```

### Desactivar Usuario
```sql
UPDATE usuarios SET activo = 0 WHERE username = 'juan';
```

### Reactivar Usuario
```sql
UPDATE usuarios SET activo = 1 WHERE username = 'juan';
```

### Resetear Base de Datos (CUIDADO)
```sql
DROP DATABASE forms_db;
-- Luego reimportar setup/database.sql
```

---

## 💡 Tips Útiles

1. **Generar hash bcrypt en PHP:**
   ```php
   $hash = password_hash('micontraseña', PASSWORD_BCRYPT);
   echo $hash;
   ```

2. **Verificar conexión BD:**
   ```bash
   mysql -u forms_user -p -e "SELECT 1"
   ```

3. **Ver error log de Apache:**
   ```bash
   tail -f /var/log/apache2/error.log
   ```

4. **Limpiar PDFs antiguos:**
   ```bash
   find /var/www/html/forms/pdfs -mtime +30 -delete  # Archivos > 30 días
   ```

---

## 🔐 Checklist de Seguridad

- [ ] Cambiar contraseñas de usuarios de prueba
- [ ] Usar HTTPS en producción
- [ ] Hacer backups semanales de BD
- [ ] Revisar logs de error regularmente
- [ ] No compartir credenciales de BD
- [ ] Actualizar PHP regularmente
- [ ] Usar .htaccess para proteger config/

---

## 📚 Más Información

- Instalación completa: `INSTALL.md`
- Arquitectura del proyecto: `PROJECT.md`
- Checklist de verificación: `CHECKLIST.md`
- Documentación general: `README.md`

---

**Versión:** 1.0.0 | **Última actualización:** 31/12/2025
