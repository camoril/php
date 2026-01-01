# PHP Projects Collection

Este repositorio contiene una colección de aplicaciones y scripts en PHP desarrollados por **Ernesto Pineda B.**, que van desde utilidades financieras hasta algoritmos de inteligencia artificial. Todos los proyectos han sido modernizados para utilizar estándares actuales de PHP 8 y Tailwind CSS.

## 📂 Proyectos

### 📋 [Forms - Sistema de Hojas de Servicio](/forms)
Sistema web profesional para gestión integral de hojas de servicio (intervenciones) con firma digital de clientes.
- **Objetivo**: Registrar y documentar intervenciones técnicas con validación del cliente mediante firma digital, generando PDFs personalizables y automáticos.
- **Características principales**:
  - 🔐 Autenticación de usuarios con roles diferenciados (Trabajador/Administrador).
  - 🏢 Gestión jerárquica de clientes, proyectos y contactos.
  - 📝 Registro completo de intervenciones con múltiples campos configurables.
  - 📄 Generación automática de PDFs con branding personalizado.
  - ✍️ Firma digital de clientes (compatibilidad touch/mouse/tablet).
  - 🎨 Panel de administración para branding (logos, colores corporativos, información de empresa).
  - 👥 Gestión de usuarios con panel administrativo completo.
  - 🔍 Búsqueda y filtrado avanzado por múltiples criterios.
  - 📊 Exportación de datos (CSV, JSON).
  - 🌐 Diseño responsive (móvil, tablet, desktop).
  - ⏰ Registro automático de timestamps en cada intervención.
- **Stack Tecnológico**: PHP 8.4+, MariaDB 11.8+, Bootstrap 5.3, SignaturePad.js, PDO (Prepared Statements).
- **Licencia**: GPLv3 (Software Libre).
- **Documentación**: 
  - [README detallado](forms/README.md) con instrucciones completas.
  - [INSTALL.md](forms/INSTALL.md) - Guías de instalación (Docker, tradicional, cPanel).
  - [CHANGELOG.md](forms/CHANGELOG.md) - Historial de cambios.
- **Instalación rápida**: 
  ```bash
  cd forms
  docker-compose up -d
  # Acceder: http://localhost:8080
  # Usuario: admin / Contraseña: admin123
  ```
- **Estado**: v0.0.1 Beta 2 - En desarrollo activo con soporte Docker/Podman completo.

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
- **Sistemas de Amortización**:
  - **Francés**: Cuota constante (Interés decreciente, capital creciente).
  - **Alemán**: Amortización de capital constante (Cuota decreciente).
  - **Americano**: Pago de intereses periódicos y devolución del capital al final.
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

- **PHP**: 8.0 o superior (8.4+ recomendado para Forms).
- **Servidor Web**: Apache, Nginx, o PHP Built-in Server.
- **Base de Datos**: MySQL/MariaDB (Requerido para `forms` y `tarifas`).

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
