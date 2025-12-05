# Calculadora de Préstamos y Amortización

Esta es una aplicación web ligera y autónoma para calcular pagos de préstamos y generar tablas de amortización detalladas. Diseñada para ser simple, rápida y fácil de desplegar.

## 🚀 Características

- **Cálculo Financiero Preciso**: Utiliza la fórmula estándar de anualidades para determinar los pagos periódicos.
- **Flexibilidad de Periodos**: Soporta múltiples frecuencias de pago:
  - Semanal (52 pagos/año)
  - Quincenal (26 pagos/año)
  - Mensual (12 pagos/año)
  - Bimestral, Trimestral, Semestral, Anual.
- **Tabla de Amortización**: Genera un desglose completo periodo por periodo mostrando:
  - Interés pagado.
  - Capital amortizado.
  - Saldo restante.
- **Interfaz Moderna**: Construida con **Tailwind CSS** para un diseño limpio y responsivo que funciona en móviles y escritorio.
- **Sin Dependencias**: No requiere base de datos ni instalación de librerías externas (Composer, npm, etc.).

## 🛠️ Requisitos

- **PHP**: 7.4 o superior (Recomendado PHP 8.0+).
- **Conexión a Internet**: Necesaria para cargar Tailwind CSS desde CDN (o se puede descargar localmente si se requiere uso offline).

## 📦 Instalación y Uso

Al ser una aplicación de archivo único, la instalación es trivial:

1.  **Copiar el archivo**:
    Simplemente coloca el archivo `index.php` en cualquier directorio accesible por tu servidor web.

2.  **Ejecutar con PHP Built-in Server**:
    Si tienes PHP instalado en tu computadora, puedes probarlo inmediatamente sin configurar Apache o Nginx:

    ```bash
    cd php/prestamos
    php -S localhost:8000
    ```

3.  **Acceder**:
    Abre tu navegador en `http://localhost:8000`.

## 🧮 Fórmulas Utilizadas

El cálculo del pago periódico ($P$) se realiza utilizando la fórmula de amortización francesa:

$$ P = L \cdot \frac{r}{1 - (1 + r)^{-n}} $$

Donde:
- $L$: Monto del préstamo (Loan Amount).
- $r$: Tasa de interés periódica (Tasa Anual / Frecuencia).
- $n$: Número total de pagos (Años $\times$ Frecuencia).

## 📝 Historial de Cambios

Consulta el archivo [CHANGELOG.md](CHANGELOG.md) para ver el historial de actualizaciones y modernizaciones del proyecto.
