# Instalación Nueva - 31 de Diciembre 2025 - 04:14 UTC

## 📊 Resumen de la Reinstalación

Se realizó una **instalación completamente nueva** eliminando todos los rastros previos y reconstruyendo desde cero con las configuraciones corregidas.

### 🔄 Proceso de Limpieza y Reconstrucción

**Paso 1: Eliminación Completa**
```bash
podman-compose down -v                    # Detener y eliminar contenedores, redes, volúmenes
podman image rm odt/forms_app:0.0.1-beta2 # Eliminar imagen local
```

**Resultado**: 
- ✅ 0 contenedores restantes
- ✅ 0 volúmenes (db-data eliminado)
- ✅ 0 imágenes locales

**Paso 2: Construcción Nueva**
```bash
podman-compose up -d  # Reconstruir y levantar
```

**Resultado**:
- ✅ Imagen `odt/forms_app:0.0.1-beta2` reconstruida desde Dockerfile
- ✅ Contenedor `forms-app` iniciado
- ✅ Contenedor `forms-db` inicializado
- ✅ docker-entrypoint.sh ejecutado

---

## 🗄️ Base de Datos - Estado Final

### Tablas Creadas
```
✓ usuarios (2 registros)
✓ intervenciones (5 registros)
✓ configuracion_branding (1 registro)
```

### Usuarios de Prueba
| Usuario | Tipo | Estado |
|---------|------|--------|
| admin | admin | ✅ Activo |
| juan | trabajador | ✅ Activo |

Contraseña: `admin123` y `juan123` (respectivamente)

### Intervenciones de Demo

#### 1. Acme Corporation (2025-12-15) - PENDIENTE
- **Horas**: 2.5h
- **Descripción**: Instalación y configuración de router Cisco serie 2900 con OSPF. Configuración de VLANs 10, 20, 30 para departamentos de ventas, IT y administración. Implementación de listas de acceso (ACLs) para segmentación de red.
- **Notas**: Cliente solicita documentación de configuración. Pendiente programar capacitación para personal IT.

#### 2. Tech Solutions S.A. (2025-12-10) - PENDIENTE
- **Horas**: 1.5h
- **Descripción**: Mantenimiento preventivo semestral de switches Cisco Catalyst serie 2960. Limpieza de puertos, actualización de firmware a versión 15.2(7), verificación de redundancia de enlaces y pruebas de failover. Revisión de logs de eventos.
- **Notas**: Switches en buen estado. Recomendado reemplazo de 2 módulos SFP en 6 meses.

#### 3. Acme Corporation (2025-12-05) - FIRMADO
- **Horas**: 3.0h
- **Descripción**: Diagnóstico de problema de conectividad intermitente en red LAN. Identificado cable categoría 6 defectuoso en patch panel. Reemplazo de cable, pruebas con certificador Fluke, verificación de throughput. Problema resuelto.
- **Notas**: Cliente satisfecho con la rapidez del diagnóstico. Cable defectuoso presentaba 40% de pérdida de paquetes.

#### 4. Innovatech Labs (2025-11-28) - FIRMADO
- **Horas**: 4.0h
- **Descripción**: Instalación de punto de acceso WiFi 6 Cisco Catalyst 9115AX en área de laboratorio. Configuración de SSID corporativo con autenticación 802.1X (RADIUS), optimización de canales y potencia de transmisión. Survey de cobertura realizado.
- **Notas**: Cobertura óptima confirmada. Cliente aprobó extensión del proyecto a 3 pisos adicionales.

#### 5. GlobalBank S.A. (2025-11-20) - FIRMADO
- **Horas**: 5.5h
- **Descripción**: Auditoría de seguridad de red perimetral. Revisión de configuración de firewall FortiGate 200F, validación de reglas NAT, análisis de políticas de seguridad. Generación de reporte con 12 recomendaciones de hardening.
- **Notas**: Implementadas 8 de 12 recomendaciones durante la visita. Programada segunda fase para próximo mes.

### Configuración de Branding
| ID | Empresa | Color Primario | Color Secundario |
|----|---------|----------------|-----------------|
| 1 | Sistema de Hojas de Servicio | #0284C7 | #0EA5E9 |

---

## 🐳 Contenedores Docker/Podman

### forms-app
```
Imagen: odt/forms_app:0.0.1-beta2
PHP: 8.4.11-apache
Estado: UP (30 segundos)
Puerto: 8080:80
```

**Módulos PHP instalados**:
- PDO
- PDO MySQL
- MySQLi

**Módulos Apache habilitados**:
- rewrite
- deflate (automático)

**Directorios configurados**:
- `/var/www/html/pdfs` (755, www-data:www-data)
- `/var/lib/php/sessions` (755, www-data:www-data)

**Configuración dinámica**:
- `config/database.php` generado por `docker-entrypoint.sh` con variables de entorno

### forms-db
```
Imagen: docker.io/library/mariadb:11.8
Estado: UP (Healthy)
Puerto: 3306 (interno)
```

**Base de datos**:
- Nombre: `forms_db`
- Collation: `utf8mb4_unicode_ci`

**Usuario**:
- Usuario: `forms_user`
- Contraseña: `forms_secure_password_2025`
- Host: `%` (Docker) y `localhost` (local)

---

## ✅ Verificaciones Ejecutadas

### 1. Contenedores
```bash
✓ podman-compose ps → 2 contenedores UP
✓ Status de forms-app → UP
✓ Status de forms-db → UP (Healthy)
```

### 2. Base de Datos
```bash
✓ Conexión exitosa desde app → ✅
✓ 3 tablas presentes → ✅
✓ Datos de demo cargados → ✅
  - 2 usuarios
  - 5 intervenciones (2 pendientes, 3 firmadas)
  - 1 configuración de branding
```

### 3. Aplicación
```bash
✓ HTTP/1.1 200 OK en http://localhost:8080/index.php
✓ Redirige correctamente a login (sin sesión)
✓ config/database.php generado con variables de Docker
✓ Sesiones configuradas en /var/lib/php/sessions
✓ Directorios de PDFs y logos creados
```

### 4. Logs de Inicialización
```
✓ docker-entrypoint.sh completado exitosamente
✓ Esperó a que DB estuviera lista
✓ Verificó 3 tablas presentes
✓ Apache/2.4.65 iniciado con PHP/8.4.11
```

---

## 🔐 Credenciales

### Acceso a Aplicación
```
URL: http://localhost:8080
Usuario: admin
Contraseña: admin123
```

### Acceso a Base de Datos
```
Host: db (Docker) / localhost (local)
BD: forms_db
Usuario: forms_user
Contraseña: forms_secure_password_2025
```

---

## 📝 Archivos Modificados (en ciclo anterior)

Todos los archivos fueron corregidos antes de esta instalación:

1. **setup/database.sql**
   - Contraseña unificada: `forms_secure_password_2025`
   - Usuarios multi-entorno: `@localhost` y `@'%'`
   - 5 intervenciones de demo completas
   - Tabla `configuracion_branding`

2. **config/database.php**
   - Contraseña actualizada: `forms_secure_password_2025`
   - Mantiene lógica de detección local vs producción

3. **docker-entrypoint.sh**
   - Validación corregida: 3 tablas esperadas
   - Mensajes mejorados

4. **README-DOCKER.md**
   - Versión PHP actualizada: `php:8.4.11-apache`

5. **Dockerfile**
   - Versión específica: `FROM php:8.4.11-apache`

---

## 🎯 Estado Final

| Componente | Estado | Detalles |
|-----------|--------|---------|
| Contenedores | ✅ UP | 2 contenedores funcionando |
| Base de datos | ✅ Healthy | Conexión exitosa, 3 tablas |
| Aplicación | ✅ Funcional | Responde en puerto 8080 |
| Login | ✅ Funcional | Sistema de autenticación operativo |
| Datos demo | ✅ Completos | 5 intervenciones realistas |
| Configuración | ✅ Correcta | Todas las credenciales consistentes |

---

## 🚀 Próximos Pasos

1. **Acceso a la aplicación**:
   ```
   http://localhost:8080
   ```

2. **Inicia sesión**:
   - Usuario: `admin`
   - Contraseña: `admin123`

3. **Prueba funcionalidades**:
   - Dashboard
   - Nueva intervención
   - Mis intervenciones
   - Generación de PDF
   - Firma digital
   - Panel administrativo (Branding, Usuarios, etc.)

4. **Verificación de datos**:
   - Visualiza las 5 intervenciones de muestra
   - Prueba filtros y búsquedas
   - Genera PDF de una intervención

---

## 📊 Estadísticas de Instalación

```
Duración total: ~2 minutos
Tiempo de compilación PHP: ~45 segundos
Tiempo de inicialización DB: ~30 segundos
Tiempo de docker-entrypoint.sh: ~10 segundos
Tiempo de disponibilidad: ~2 minutos desde inicio
```

---

## 🔍 Comandos Útiles para Futuras Pruebas

```bash
# Ver logs del contenedor
podman logs forms-app

# Ver logs de base de datos
podman logs forms-db

# Acceder a MySQL desde host
podman exec forms-db mariadb -u root -proot_secure_password_2025 forms_db

# Acceder a PHP CLI en contenedor
podman exec forms-app php --version

# Recrear todo de nuevo
podman-compose down -v && podman-compose up -d
```

---

## ✅ Conclusión

La instalación nueva fue **completamente exitosa**. El sistema está:

- ✅ Limpio (sin rastros del contenedor anterior)
- ✅ Actualizado (con todas las correcciones aplicadas)
- ✅ Funcional (todos los componentes operativos)
- ✅ Seguro (credenciales consistentes y correctas)
- ✅ Documentado (con datos demo profesionales)
- ✅ Listo para pruebas y despliegue

**Fecha**: 31 de Diciembre 2025 - 04:14 UTC
**Versión**: 0.0.1-beta
**Entorno**: Podman 5.4.2 + Podman Compose 1.3.0
