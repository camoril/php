# Versión 0.0.1 Beta 2 - Notas de Lanzamiento

**Fecha**: 31 de Diciembre 2025  
**Versión**: 0.0.1-beta2  
**Nombre de Imagen**: `odt/forms_app:0.0.1-beta2`

---

## 📋 Resumen de Cambios

Esta versión beta2 representa la consolidación completa de la infraestructura de base de datos y la alineación total entre el código, la estructura de BD, y los datos demo. Es la versión más estable y funcional hasta el momento.

## ✨ Cambios Principales

### 1. Estructura Completa de Base de Datos

Se agregaron **3 nuevas tablas** que faltaban en la versión anterior:

#### Tabla `clientes`
- Información de empresas clientes
- Campos: id, nombre (UNIQUE), email, telefono, direccion, contacto_principal
- Índices optimizados para búsqueda

#### Tabla `proyectos`
- Proyectos organizados por cliente
- Relación: cliente_id (FOREIGN KEY)
- Campos: id, cliente_id, nombre, descripcion, estado (enum: activo/inactivo)
- Eliminación en cascada: si se elimina cliente, se eliminan proyectos

#### Tabla `contactos`
- Contactos específicos de cada proyecto
- Relación: proyecto_id (FOREIGN KEY)
- Campos: id, proyecto_id, nombre, cargo, email, telefono, activo
- Eliminación en cascada: si se elimina proyecto, se eliminan contactos

### 2. Ampliación de Tabla `intervenciones`

Se agregaron **4 columnas nuevas** para completar la estructura:

- `hora`: TIME DEFAULT '09:00:00' - Hora de la intervención
- `cliente_id`: INT FOREIGN KEY - Relación a cliente específico
- `proyecto_id`: INT FOREIGN KEY - Relación a proyecto específico
- `contacto_id`: INT FOREIGN KEY - Relación a contacto específico

**Total de columnas ahora**: 18 (antes: 14)

### 3. Datos Demo Realistas

Se regeneraron **completamente los datos demo** con información coherente:

**Clientes (4 registros)**:
1. Acme Corporation - Empresa de tecnología grande
2. Tech Solutions S.A. - Consultora de TI
3. Innovatech Labs - Laboratorio de investigación
4. GlobalBank S.A. - Institución financiera

**Proyectos (5 registros)**:
1. Acme → Infraestructura de Red - Sede Principal
2. Acme → Seguridad Perimetral
3. Tech Solutions → Mantenimiento de Switches
4. Innovatech Labs → Infraestructura WiFi 6
5. GlobalBank → Auditoría de Seguridad Integral

**Contactos (6 registros)**:
- Cada proyecto tiene contactos específicos con cargos
- Ejemplo: Carlos Mendez (Gerente de TI), Diana Rodríguez (Coordinadora de Red)

**Intervenciones (5 registros)**:
- Todas completamente rellenas con fechas, horas, clientes, proyectos, contactos
- Descripciones técnicas realistas
- Estados variados (pendiente/firmado)
- Horas realistas (1.5 - 5.5 horas)

### 4. Correcciones de Advertencias

**Problema**: view_pdf.php generaba 3 advertencias:
```
Undefined array key "cliente_id" on line 53
Undefined array key "proyecto_id" on line 62
Undefined array key "contacto_id" on line 71
```

**Causa**: El código intentaba acceder a columnas que no existían en la tabla.

**Solución**: 
- Agregadas las columnas a la tabla intervenciones
- Implementados isset() checks en view_pdf.php para compatibilidad futura
- Verificadas todas las referencias en el código

## 🔄 Versión de Imagen Docker

### Cambio de Nombre
- **Anterior**: `localhost/forms_app:latest`
- **Actual**: `odt/forms_app:0.0.1-beta2`

**Ventajas del nuevo nombre**:
- Namespace consistente (odt = Open Development Tools)
- Versionamiento explícito
- Preparación para registros (Docker Hub, GitHub Container Registry)
- Distinción clara entre versiones

### Docker-compose.yml Actualizado
```yaml
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    image: odt/forms_app:0.0.1-beta2
    container_name: forms-app
```

## 📊 Verificación Técnica

### Base de Datos
- ✅ 6 tablas creadas correctamente
- ✅ 18 columnas en intervenciones
- ✅ 5 FOREIGN KEYs configuradas
- ✅ Cascada de eliminación funcional
- ✅ 4 clientes + 5 proyectos + 6 contactos + 5 intervenciones cargados

### PHP & Sintaxis
- ✅ dashboard.php: Sin errores
- ✅ view_pdf.php: Sin errores
- ✅ edit_intervention.php: Sin errores
- ✅ manage_clientes.php: Sin errores
- ✅ interventions.php: Sin errores

### Contenedores
- ✅ forms-app: UP
- ✅ forms-db: UP (healthy)
- ✅ Relaciones entre contenedores: Funcionales
- ✅ Volúmenes: Persistentes

### Datos
```sql
SELECT COUNT(*) FROM clientes;      -- 4
SELECT COUNT(*) FROM proyectos;     -- 5
SELECT COUNT(*) FROM contactos;     -- 6
SELECT COUNT(*) FROM intervenciones; -- 5
```

## 📖 Documentación Actualizada

- **README.md** - Versión 0.0.1-beta2
- **README-DOCKER.md** - Versión 0.0.1-beta2
- **Dockerfile** - LABEL version actualizado
- **CHANGELOG.md** - Historial completo
- **INSTALACION-NUEVA-2025-12-31.md** - Referencias actualizadas
- **CAMBIOS-ESTRUCTURA-DB-2025-12-31.md** - Documentación detallada

## 🚀 Cómo Actualizar desde Beta 1

Si estás en v0.0.1-beta y quieres actualizar a v0.0.1-beta2:

### Opción 1: Instalación Limpia (Recomendado)
```bash
cd /path/to/forms
podman-compose down -v
podman-compose up -d
```

### Opción 2: Aplicar Cambios de BD
Si prefieres preservar datos:
```bash
# Ejecutar SQL de nuevas tablas
podman exec forms-db mariadb -u forms_user -p < setup/database.sql

# O manualmente copiar las definiciones de:
# CREATE TABLE clientes...
# CREATE TABLE proyectos...
# CREATE TABLE contactos...
```

## 🔐 Seguridad

Cambios de seguridad en esta versión:

- ✅ Implementado isset() checks en todos los accesos a arrays
- ✅ Validación de relaciones a nivel de BD
- ✅ FOREIGN KEYs para integridad referencial
- ✅ Prepared statements en todas las consultas
- ✅ Validación de entrada en formularios

## 📋 Checklist de Pruebas

- [x] Creación de nueva intervención
- [x] Carga de clientes/proyectos/contactos en cascada
- [x] Visualización de intervención
- [x] Generación de PDF sin advertencias
- [x] Firma digital
- [x] Edición de intervención
- [x] Eliminación de intervención
- [x] Listado con filtros
- [x] Exportación de datos
- [x] Login de usuarios

## 🐛 Problemas Conocidos

Ninguno identificado en esta versión. Sistema estable para uso en desarrollo/testing.

## 📝 Notas Importantes

1. **Base de Datos**: Se ha realizado instalación desde cero. Los datos anteriores no se preservan.

2. **Imagen Docker**: El cambio de nombre `localhost/forms_app` → `odt/forms_app:0.0.1-beta2` requiere eliminar la imagen anterior:
   ```bash
   podman image rm localhost/forms_app:latest
   ```

3. **Credenciales Demo**:
   - Usuario: `admin` / Contraseña: `admin123`
   - Usuario: `juan` / Contraseña: `juan123`

4. **Producción**: Cambiar todas las contraseñas antes de producción.

## 🎯 Próximas Prioridades

Para versiones futuras:

1. **v0.1.0**: Mejoras de UX, validaciones adicionales, reportes
2. **v0.2.0**: API REST, webhooks, integración externa
3. **v1.0.0**: Estabilidad de producción, performance, full suite de pruebas

## 📞 Contacto & Soporte

- **Repositorio**: https://github.com/camoril/php
- **Rama**: main (formas)
- **Maintainer**: Ernesto Pineda

---

**Versión**: 0.0.1-beta2  
**Estado**: Estable para testing/desarrollo  
**Última actualización**: 31 de Diciembre 2025
