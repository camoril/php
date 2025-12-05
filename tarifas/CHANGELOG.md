# Changelog - Motor Tarificador

## [v1.0.0] - 2025-12-04
### Modernización Completa
Se ha reescrito la aplicación original (v0.3 de 2013) para cumplir con estándares modernos de seguridad y diseño.

### Seguridad
- **PDO & Prepared Statements**: Reemplazo total de `mysqli` por `PDO`. Todas las consultas ahora usan sentencias preparadas para prevenir SQL Injection.
- **Validación de Input**: Se agregó validación estricta (`preg_match`) para los nombres de tablas/meses.
- **Configuración Segura**: Credenciales de base de datos movidas a `config.php` (separación de lógica y configuración).

### Backend
- **Corrección CSV**: Se arregló la exportación a CSV. Ahora se generan los encabezados correctos antes de cualquier salida HTML, evitando archivos corruptos.
- **Manejo de Errores**: Mensajes de estado claros cuando no se encuentran resultados.

### Frontend
- **Tailwind CSS**: Reemplazo de hojas de estilo antiguas (`view.css`) por Tailwind CSS (vía CDN).
- **Diseño Responsivo**: Interfaz adaptada para dispositivos móviles y escritorio.
- **Validación JS**: Script de validación de formularios simplificado y modernizado.

### Limpieza
- Eliminados archivos obsoletos: `view.css`, `view.js`, `iepngfix.htc` y recursos gráficos antiguos.
