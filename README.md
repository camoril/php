# PHP Projects Collection

Este repositorio contiene una colección de aplicaciones y scripts en PHP desarrollados por **Camoril**, que van desde utilidades financieras hasta algoritmos de inteligencia artificial. Todos los proyectos han sido modernizados para utilizar estándares actuales de PHP 8 y Tailwind CSS.

## 📂 Proyectos

### 🧬 [Genetic](/genetic)
Una implementación didáctica y optimizada de un **Algoritmo Genético**.
- **Objetivo**: Evolucionar una población de cadenas de texto aleatorias hasta que coincidan con una frase objetivo definida por el usuario.
- **Características**: 
  - Interfaz web interactiva.
  - Parámetros configurables (Tamaño de población, Tasa de mutación, Elitismo).
  - Lógica optimizada no recursiva.

### 💰 [Préstamos](/prestamos)
Calculadora financiera de amortización de préstamos.
- **Funcionalidad**: Permite calcular pagos periódicos y generar tablas de amortización completas.
- **Opciones**: Soporta pagos semanales, quincenales, mensuales, trimestrales, semestrales y anuales.
- **Tecnología**: Aplicación de archivo único (`index.php`) con diseño responsivo en Tailwind CSS.

### 📞 [Tarifas](/tarifas)
Sistema de reporte y tarificación de llamadas telefónicas (Call Accounting).
- **Funcionalidad**: 
  - Búsqueda y filtrado de registros de llamadas.
  - Cálculo de costos por duración.
  - Exportación de reportes a CSV.
- **Tecnología**: Backend con PDO (Sentencias preparadas) y Frontend moderno.

## 🚀 Requisitos Generales

- **PHP**: 8.0 o superior.
- **Servidor Web**: Apache, Nginx, o PHP Built-in Server.
- **Base de Datos**: MySQL/MariaDB (Requerido solo para el proyecto `tarifas`).

## 🛠️ Instalación y Uso

Para probar cualquiera de los proyectos rápidamente usando el servidor integrado de PHP:

1. Clona el repositorio:
   ```bash
   git clone https://github.com/camoril/php.git
   cd php
   ```

2. Navega a la carpeta del proyecto deseado (ej. `prestamos`):
   ```bash
   cd prestamos
   ```

3. Inicia el servidor:
   ```bash
   php -S localhost:8000
   ```

4. Abre tu navegador en `http://localhost:8000`.
