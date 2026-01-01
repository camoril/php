# 🐳 Guía Docker/Podman - Sistema de Hojas de Servicio

Esta guía proporciona información completa sobre cómo ejecutar el Sistema de Hojas de Servicio usando Docker o Podman.

## 📋 Tabla de Contenidos

- [Requisitos](#requisitos)
- [Inicio Rápido](#inicio-rápido)
- [Arquitectura](#arquitectura)
- [Volúmenes](#volúmenes)
- [Variables de Entorno](#variables-de-entorno)
- [Comandos Útiles](#comandos-útiles)
- [Troubleshooting](#troubleshooting)
- [Desarrollo](#desarrollo)

---

## Requisitos

### Opción 1: Docker
- Docker 20.10+
- Docker Compose 2.0+

### Opción 2: Podman (Recomendado para Linux)
- Podman 4.0+
- Podman Compose 1.0+

---

## Inicio Rápido

### Con Docker

```bash
# 1. Clonar repositorio
git clone https://github.com/camoril/php.git
cd php/forms

# 2. Levantar contenedores
docker-compose up -d

# 3. Acceder
http://localhost:8080
```

### Con Podman

```bash
# 1. Clonar repositorio
git clone https://github.com/camoril/php.git
cd php/forms

# 2. Levantar contenedores
podman-compose up -d

# 3. Acceder
http://localhost:8080
```

### Credenciales por Defecto

```
Usuario: admin
Contraseña: admin123
```

---

## Arquitectura

### Contenedores

| Nombre | Imagen | Puerto | Descripción |
|--------|--------|--------|-------------|
| `forms-app` | `odtforms_app:0.0.1-beta2` | 8080:80 | Aplicación PHP + Apache |
| `forms-db` | `mariadb:11.8` | - | Base de datos MariaDB |

### Red

- **Nombre**: `forms-network`
- **Driver**: `bridge`
- **Comunicación interna**: Los contenedores se comunican por nombre de host

### Imagen de la Aplicación

**Nombre**: `localhost/odtforms_app:0.0.1-beta2`

**Base**: `php:8.4.11-apache`

**Extensiones PHP instaladas**:
- PDO
- PDO MySQL
- MySQLi

**Módulos Apache habilitados**:
- mod_rewrite
- mod_deflate

---

## Volúmenes

### 1. Volumen de Base de Datos

**Nombre**: `db-data`

**Propósito**: Persistir datos de MariaDB

**Montaje**: `/var/lib/mysql` (dentro del contenedor)

**Comandos útiles**:

```bash
# Ver información del volumen
docker volume inspect db-data
# O con Podman
podman volume inspect db-data

# Backup del volumen
docker run --rm -v db-data:/data -v $(pwd):/backup alpine tar czf /backup/db-backup.tar.gz /data

# Restaurar backup
docker run --rm -v db-data:/data -v $(pwd):/backup alpine tar xzf /backup/db-backup.tar.gz -C /
```

### 2. Directorio de PDFs (Opcional)

Si necesitas persistir PDFs generados fuera del contenedor:

```yaml
services:
  app:
    volumes:
      - ./pdfs:/var/www/html/pdfs
```

### 3. Directorio de Sesiones (Opcional)

Para persistir sesiones PHP:

```yaml
services:
  app:
    volumes:
      - ./sessions:/var/lib/php/sessions
```

---

## Variables de Entorno

### Aplicación (forms-app)

| Variable | Valor por Defecto | Descripción |
|----------|-------------------|-------------|
| `DB_HOST` | `db` | Host de base de datos |
| `DB_NAME` | `forms_db` | Nombre de base de datos |
| `DB_USER` | `forms_user` | Usuario de BD |
| `DB_PASS` | `forms_password` | Contraseña de BD |

### Base de Datos (forms-db)

| Variable | Valor por Defecto | Descripción |
|----------|-------------------|-------------|
| `MYSQL_ROOT_PASSWORD` | `root_password_2025` | Contraseña root |
| `MYSQL_DATABASE` | `forms_db` | BD inicial |
| `MYSQL_USER` | `forms_user` | Usuario de aplicación |
| `MYSQL_PASSWORD` | `forms_password` | Contraseña de usuario |

### Personalización

Edita `docker-compose.yml`:

```yaml
services:
  app:
    environment:
      - DB_HOST=db
      - DB_NAME=mi_base_datos
      - DB_USER=mi_usuario
      - DB_PASS=mi_contraseña_segura
```

---

## Comandos Útiles

### Gestión de Contenedores

```bash
# Iniciar contenedores
docker-compose up -d

# Detener contenedores
docker-compose down

# Detener y eliminar volúmenes
docker-compose down -v

# Ver logs en tiempo real
docker-compose logs -f

# Ver logs de un servicio específico
docker-compose logs -f app
docker-compose logs -f db

# Reiniciar servicios
docker-compose restart

# Ver estado
docker-compose ps
```

### Acceso a Contenedores

```bash
# Entrar al contenedor de la aplicación
docker exec -it forms-app bash

# Entrar al contenedor de base de datos
docker exec -it forms-db bash

# Ejecutar comando MySQL
docker exec -it forms-db mysql -u forms_user -pforms_password forms_db
```

### Gestión de Base de Datos

```bash
# Ver tablas
docker exec forms-db mysql -u forms_user -pforms_password forms_db -e "SHOW TABLES;"

# Backup de base de datos
docker exec forms-db mysqldump -u forms_user -pforms_password forms_db > backup.sql

# Restaurar backup
docker exec -i forms-db mysql -u forms_user -pforms_password forms_db < backup.sql

# Ejecutar consulta SQL
docker exec forms-db mysql -u forms_user -pforms_password forms_db -e "SELECT * FROM usuarios;"
```

### Limpieza

```bash
# Eliminar contenedores detenidos
docker container prune

# Eliminar imágenes no usadas
docker image prune

# Eliminar volúmenes no usados
docker volume prune

# Limpieza completa del sistema
docker system prune -a --volumes
```

### Reconstruir Imagen

```bash
# Reconstruir imagen sin cache
docker-compose build --no-cache

# Reconstruir y reiniciar
docker-compose up -d --build
```

---

## Troubleshooting

### Problema: Puerto 8080 ya está en uso

**Solución**: Cambiar puerto en `docker-compose.yml`:

```yaml
services:
  app:
    ports:
      - "8081:80"  # Cambiar 8080 a 8081
```

### Problema: Contenedor de BD no inicia

**Diagnóstico**:
```bash
docker-compose logs db
```

**Posibles causas**:
- Puerto 3306 en uso
- Volumen corrupto
- Permisos incorrectos

**Solución**: Eliminar volumen y recrear:
```bash
docker-compose down -v
docker-compose up -d
```

### Problema: Error "Access denied" en BD

**Verificar credenciales**:
```bash
docker exec forms-db mysql -u forms_user -pforms_password -e "SELECT 1;"
```

**Solución**: Verificar variables de entorno en `docker-compose.yml`

### Problema: Cambios en código no se reflejan

**Causa**: Código copiado durante build de imagen

**Solución 1**: Reconstruir imagen:
```bash
docker-compose up -d --build
```

**Solución 2**: Montar directorio como volumen (desarrollo):
```yaml
services:
  app:
    volumes:
      - ./:/var/www/html
```

### Problema: PDFs no se generan

**Verificar permisos**:
```bash
docker exec forms-app ls -la /var/www/html/pdfs
```

**Solución**:
```bash
docker exec forms-app chown -R www-data:www-data /var/www/html/pdfs
docker exec forms-app chmod -R 755 /var/www/html/pdfs
```

### Problema: Sesiones se pierden

**Verificar directorio de sesiones**:
```bash
docker exec forms-app ls -la /var/lib/php/sessions
```

**Solución**:
```bash
docker exec forms-app chown -R www-data:www-data /var/lib/php/sessions
docker exec forms-app chmod -R 755 /var/lib/php/sessions
```

---

## Desarrollo

### Modo Desarrollo con Hot Reload

Edita `docker-compose.yml`:

```yaml
services:
  app:
    volumes:
      - ./:/var/www/html
    environment:
      - PHP_DISPLAY_ERRORS=On
      - PHP_ERROR_REPORTING=E_ALL
```

### Debug de PHP

Agregar a Dockerfile:

```dockerfile
RUN docker-php-ext-install xdebug
RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
```

### Ejecutar Tests

```bash
# Instalar PHPUnit en contenedor
docker exec forms-app composer require --dev phpunit/phpunit

# Ejecutar tests
docker exec forms-app vendor/bin/phpunit tests/
```

### Monitorear Logs de Apache

```bash
# Error log
docker exec forms-app tail -f /var/log/apache2/error.log

# Access log
docker exec forms-app tail -f /var/log/apache2/access.log
```

### Inspeccionar PHP Info

```bash
docker exec forms-app php -i
```

---

## Configuración Avanzada

### Límites de Recursos

```yaml
services:
  app:
    deploy:
      resources:
        limits:
          cpus: '1'
          memory: 512M
        reservations:
          cpus: '0.5'
          memory: 256M
```

### Health Checks

```yaml
services:
  app:
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost/"]
      interval: 30s
      timeout: 10s
      retries: 3
      start_period: 40s
```

### Networking Avanzado

```yaml
networks:
  forms-network:
    driver: bridge
    ipam:
      config:
        - subnet: 172.20.0.0/16
```

---

## Seguridad en Producción

### 1. Cambiar Credenciales

**CRÍTICO**: Cambiar contraseñas en `docker-compose.yml`:

```yaml
environment:
  - MYSQL_ROOT_PASSWORD=TU_CONTRASEÑA_SEGURA_AQUI
  - MYSQL_PASSWORD=TU_CONTRASEÑA_DE_APP_AQUI
```

### 2. No Exponer Puerto de BD

Eliminar sección `ports` en servicio `db`:

```yaml
services:
  db:
    # ports:  ← Comentar o eliminar
    #   - "3306:3306"
```

### 3. Usar Secrets (Docker Swarm)

```yaml
services:
  db:
    secrets:
      - db_root_password
      - db_password

secrets:
  db_root_password:
    file: ./secrets/db_root_password.txt
  db_password:
    file: ./secrets/db_password.txt
```

### 4. HTTPS con Reverse Proxy

Usar Traefik o Nginx como proxy:

```yaml
services:
  app:
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.forms.rule=Host(`forms.tudominio.com`)"
      - "traefik.http.routers.forms.tls=true"
```

---

## Referencias

- **Documentación Docker**: https://docs.docker.com/
- **Documentación Podman**: https://docs.podman.io/
- **Docker Compose**: https://docs.docker.com/compose/
- **MariaDB en Docker**: https://hub.docker.com/_/mariadb
- **PHP en Docker**: https://hub.docker.com/_/php

---

## Soporte

Para problemas o preguntas:
- Repositorio: https://github.com/camoril/php
- Issues: https://github.com/camoril/php/issues

---

**Última actualización**: 31 de Diciembre de 2025  
**Versión**: 0.0.1 Beta 2
