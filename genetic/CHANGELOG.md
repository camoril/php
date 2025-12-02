# Registro de Cambios (Changelog)

Todos los cambios notables en el proyecto "Genetic Algorithm" serán documentados en este archivo.

## [2025-12-02] - Optimización y UI Interactiva

### Añadido
- **Interfaz de Usuario**: Formulario HTML/CSS en `index.php` que permite al usuario configurar:
  - Texto objetivo.
  - Tamaño de la población.
  - Tasa de mutación.
  - Nivel de elitismo.
- **Soporte de Caracteres**: Se añadió el espacio (" ") al diccionario de caracteres permitidos.
- **Validación**: Sanitización de entradas para evitar caracteres inválidos en el algoritmo.

### Cambiado
- **Núcleo del Algoritmo (`gac.php`)**:
  - Reemplazo de recursividad por un bucle `while` para evitar `Fatal error: Maximum function nesting level reached`.
  - Implementación de `__construct` y propiedades privadas (modernización de PHP).
  - Optimización de rendimiento: Uso de `mt_rand` y reducción de operaciones de I/O (solo imprime el mejor de cada generación).
- **Lógica de Selección**: Implementación clara de Elitismo (los N mejores pasan intactos) + Torneo/Ruleta simplificada para el resto.

### Optimización
- Reducción drástica del uso de memoria y tiempo de ejecución al eliminar la impresión de todos los individuos por generación.
