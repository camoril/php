# Registro de Cambios

Todos los cambios notables en este proyecto serán documentados en este archivo.

## [1.0.0] - 2025-12-04

### Añadido
- Soporte para el Sistema de Amortización Alemán (Amortización de Capital Constante).
- Soporte para el Sistema de Amortización Americano (Solo Intereses).
- Columna de "Pago Total" en la tabla de amortización.
- Interfaz de usuario modernizada usando Tailwind CSS vía CDN.
- Diseño responsivo para móviles y escritorio.
- Declaraciones de tipo estricto en la lógica PHP.
- Archivo `CHANGELOG.md`.

### Cambiado
- Refactorizado `index.php` para incluir tanto la lógica como la vista, eliminando la necesidad de un motor de plantillas personalizado.
- Reemplazado el diseño basado en tablas con CSS Grid y Flexbox.
- Mejorada la validación de entrada y el manejo de errores.
- Actualizada la lógica de cálculo de amortización para una mejor legibilidad y manejo de precisión.

### Eliminado
- `loan-calculator.tpl` (Archivo de plantilla heredado).
- Funciones personalizadas de análisis de plantillas (`load_template`, `replace_vars`, `glb`, `strip`).
- Uso de `extract()` y `GLOBALS` para la gestión de variables.
