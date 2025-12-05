# Sistema de Tarifas y Reportes de Llamadas

Esta aplicación es una herramienta web moderna para la consulta, visualización y exportación de registros de llamadas telefónicas (CDR - Call Detail Records). Permite a los administradores y usuarios auditar el uso telefónico por extensión y periodo.

## 🚀 Características Principales

- **Consulta Dinámica**: Filtrado de llamadas por:
  - Mes (Selección de tabla de base de datos).
  - Extensión de origen (`Caller`).
  - Número marcado (`CalledNumber`).
- **Exportación a CSV**: Capacidad de descargar los reportes detallados en formato CSV para análisis externo (Excel, etc.).
- **Interfaz Moderna**: Diseño limpio y responsivo utilizando **Tailwind CSS**.
- **Seguridad**: Implementación de **PDO** con sentencias preparadas para prevenir inyecciones SQL.

## 🛠️ Requisitos del Sistema

- **PHP**: 8.0 o superior.
- **Base de Datos**: MySQL o MariaDB.
- **Extensiones PHP**: `pdo`, `pdo_mysql`.

## ⚙️ Configuración

1.  **Base de Datos**:
    La aplicación espera una base de datos (definida en `config.php`) que contenga tablas por mes (ej. `enero`, `febrero`, `2023_10`).
    
    **Esquema esperado de las tablas:**
    ```sql
    CREATE TABLE `nombre_del_mes` (
      `CallStart` datetime DEFAULT NULL,
      `Caller` varchar(255) DEFAULT NULL,
      `CalledNumber` varchar(255) DEFAULT NULL,
      `ConnectedTime` time DEFAULT NULL,
      -- Otras columnas opcionales...
    );
    ```

2.  **Archivo de Configuración**:
    Edita el archivo `config.php` con tus credenciales:
    ```php
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'zadmin_tarifas');
    define('DB_USER', 'tu_usuario');
    define('DB_PASS', 'tu_contraseña');
    ```

## 📦 Estructura del Proyecto

- `index.php`: Formulario principal de búsqueda y contenedor de la interfaz.
- `llamadas.php`: Lógica del backend. Procesa las búsquedas, genera las tablas HTML y maneja la descarga de CSV.
- `config.php`: Variables de entorno y credenciales de base de datos.

## 🔒 Seguridad

Esta versión ha sido refactorizada para eliminar vulnerabilidades presentes en versiones anteriores (v0.3):
- Se eliminó el uso de `mysqli` en favor de `PDO`.
- Se validan estrictamente los nombres de las tablas (meses) para evitar inyección SQL en identificadores.
- Se escapan las salidas HTML con `htmlspecialchars`.
