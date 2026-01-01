# 🐳 Sistema de Hojas de Servicio - Docker/Podman

Versión containerizada (0.0.1-beta2) del Sistema de Hojas de Servicio para instalación rápida y portable.

## 📋 Requisitos

### Docker
- **Docker Engine**: 20.10+
- **Docker Compose**: 2.0+

### Podman (Alternativa a Docker)
- **Podman**: 4.0+
- **Podman Compose**: 1.0+

## 🚀 Instalación Rápida

### Con Docker

```bash
# 1. Clonar repositorio
git clone https://github.com/camoril/php.git
cd php/forms

# 2. Levantar contenedores
docker-compose up -d

# 3. Ver logs (opcional)
docker-compose logs -f

# 4. Acceder
http://localhost:8080
```

### Con Podman

```bash
# 1. Clonar repositorio
git clone https://github.com/camoril/php.git
cd php/forms

# 2. Levantar contenedores
podman-compose up -d

# 3. Ver logs (opcional)
podman-compose logs -f

# 4. Acceder
http://localhost:8080
```

## 🔐 Credenciales Iniciales

```
Usuario: admin
Contraseña: admin123
```

> ⚠️ **Importante**: Cambiar contraseña después del primer acceso.

## 🗂️ Arquitectura

### Contenedores

| Contenedor | Imagen | Puerto | Descripción |
|------------|--------|--------|-------------|
| **forms-app** | php:8.4.11-apache | 8080 | Aplicación web |
| **forms-db** | mariadb:11.8 | 3306 | Base de datos |

### Volúmenes

| Volumen | Propósito |
|---------|-----------|
| `db-data` | Datos persistentes de MariaDB |
| `./pdfs` | PDFs generados (bind mount) |
| `./assets/logos` | Logos de branding (bind mount) |

### Red

- **forms-network**: Red bridge para comunicación entre contenedores

## 📦 Estructura de Archivos Docker

```
/forms/
├── Dockerfile              # Imagen de la aplicación
├── docker-compose.yml      # Orquestación de contenedores
├── docker-entrypoint.sh    # Script de inicialización
├── .dockerignore           # Archivos excluidos de la imagen
└── README-DOCKER.md        # Esta documentación
```

## 🛠️ Comandos Útiles

### Docker

```bash
# Iniciar contenedores
docker-compose up -d

# Detener contenedores
docker-compose down

# Ver logs en tiempo real
docker-compose logs -f

# Reiniciar servicios
docker-compose restart

# Ver estado
docker-compose ps

# Acceder al contenedor de la app
docker exec -it forms-app bash

# Acceder a la base de datos
docker exec -it forms-db mariadb -u forms_user -p forms_db
# Contraseña: forms_secure_password_2025

# Eliminar todo (incluye volúmenes)
docker-compose down -v
```

### Podman

```bash
# Iniciar contenedores
podman-compose up -d

# Detener contenedores
podman-compose down

# Ver logs
podman-compose logs -f

# Ver estado
podman-compose ps

# Acceder al contenedor
podman exec -it forms-app bash

# Eliminar todo
podman-compose down -v
```

## 🔧 Configuración

### Variables de Entorno

Puedes personalizar las credenciales editando `docker-compose.yml`:

```yaml
environment:
  - DB_HOST=db
  - DB_NAME=forms_db
  - DB_USER=forms_user
  - DB_PASS=tu_contraseña_segura  # Cambiar aquí
```

### Puerto Personalizado

Para usar otro puerto diferente al 8080:

```yaml
ports:
  - "9090:80"  # Cambiar 8080 a tu puerto preferido
```

Luego acceder en `http://localhost:9090`

## 🔄 Actualizaciones

```bash
# 1. Detener contenedores
docker-compose down

# 2. Actualizar código
git pull origin main

# 3. Reconstruir imagen
docker-compose build --no-cache

# 4. Iniciar nuevamente
docker-compose up -d
```

## 💾 Backup y Restauración

### Backup de Base de Datos

```bash
# Crear backup
docker exec forms-db mariadb-dump -u forms_user -pforms_secure_password_2025 forms_db > backup_$(date +%Y%m%d_%H%M%S).sql

# O usando docker-compose
docker-compose exec db mariadb-dump -u forms_user -pforms_secure_password_2025 forms_db > backup.sql
```

### Restaurar Base de Datos

```bash
# Restaurar desde backup
docker exec -i forms-db mariadb -u forms_user -pforms_secure_password_2025 forms_db < backup.sql

# O usando docker-compose
docker-compose exec -T db mariadb -u forms_user -pforms_secure_password_2025 forms_db < backup.sql
```

### Backup de PDFs y Logos

Los PDFs y logos se guardan en el sistema de archivos local (bind mount), así que puedes hacer backup normal:

```bash
tar -czf backup_files_$(date +%Y%m%d).tar.gz pdfs/ assets/logos/
```

## 🐛 Solución de Problemas

### La aplicación no inicia

```bash
# Ver logs detallados
docker-compose logs app

# Verificar estado de contenedores
docker-compose ps

# Reintentar
docker-compose restart
```

### Error de conexión a base de datos

```bash
# Verificar que la BD esté corriendo
docker-compose ps db

# Ver logs de la BD
docker-compose logs db

# Reiniciar servicio de BD
docker-compose restart db
```

### Puerto 8080 ya en uso

```bash
# Detener contenedores
docker-compose down

# Cambiar puerto en docker-compose.yml
# Editar: ports: - "9090:80"

# Iniciar con nuevo puerto
docker-compose up -d
```

### Permisos en carpeta pdfs/

```bash
# Si hay problemas de permisos
docker exec -it forms-app chown -R www-data:www-data /var/www/html/pdfs
docker exec -it forms-app chmod -R 755 /var/www/html/pdfs
```

### Resetear todo y empezar de cero

```bash
# Detener y eliminar todo
docker-compose down -v

# Eliminar archivos generados localmente
rm -rf pdfs/*.pdf

# Iniciar limpio
docker-compose up -d
```

## 🔒 Seguridad

### Para Producción

1. **Cambiar contraseñas** en `docker-compose.yml`:
   - `MYSQL_ROOT_PASSWORD`
   - `MYSQL_PASSWORD` / `DB_PASS`

2. **Usar .env file** en lugar de hardcodear:

```bash
# Crear archivo .env
cat > .env << EOF
DB_PASSWORD=tu_contraseña_super_segura
MYSQL_ROOT_PASSWORD=otra_contraseña_segura
EOF

# Actualizar docker-compose.yml
environment:
  - DB_PASS=${DB_PASSWORD}
```

3. **No exponer puerto de base de datos**:

```yaml
# Comentar o eliminar en docker-compose.yml
# ports:
#   - "3306:3306"
```

4. **Usar red específica** y firewall.

## 📊 Monitoreo

### Recursos Utilizados

```bash
# Ver uso de recursos
docker stats forms-app forms-db

# Espacio en disco de volúmenes
docker system df -v
```

### Salud de Contenedores

```bash
# Estado de salud
docker inspect --format='{{.State.Health.Status}}' forms-app
docker inspect --format='{{.State.Health.Status}}' forms-db
```

## 🤝 Soporte

- **Issues**: https://github.com/camoril/php/issues
- **Documentación General**: [README.md](README.md)
- **Instalación Tradicional**: [INSTALL.md](INSTALL.md)

## 📝 Notas

- Los datos persisten incluso si detienes los contenedores (volumen `db-data`)
- Los PDFs se guardan en `./pdfs/` en tu sistema local
- La configuración de branding se guarda en la base de datos
- El healthcheck de MariaDB asegura que la app espere a que la BD esté lista

---

**Versión Docker**: 0.0.1 Beta  
**Compatible con**: Docker 20.10+, Podman 4.0+  
**Última actualización**: 31 de Diciembre 2025
