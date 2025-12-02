# Algoritmo Genético en PHP

Este proyecto es una implementación didáctica y optimizada de un **Algoritmo Genético (Genetic Algorithm)** en PHP. Su objetivo es demostrar cómo los principios de la evolución biológica (selección natural, cruza y mutación) pueden aplicarse para resolver problemas de búsqueda y optimización computacional.

En este caso específico, el algoritmo "evoluciona" una población de cadenas de texto aleatorias hasta que una de ellas coincide exactamente con una frase objetivo definida por el usuario.

## 🚀 Características

- **Interfaz Web Interactiva**: Permite configurar parámetros en tiempo real (tamaño de población, tasa de mutación, elitismo).
- **Optimizado**:
  - Sin recursividad (evita desbordamientos de pila).
  - Uso de `mt_rand` para mejor generación de números aleatorios.
  - Bajo consumo de memoria.
- **Configurable**: Soporta frases con letras (a-z) y espacios.

## ⚙️ Funcionamiento Técnico

El algoritmo sigue el ciclo clásico de la computación evolutiva:

1.  **Inicialización**: Se genera una "población" inicial de individuos (cadenas de texto) con letras totalmente aleatorias.
2.  **Evaluación (Fitness)**: A cada individuo se le asigna una puntuación basada en qué tan similar es a la frase objetivo.
    - *Ejemplo*: Si el objetivo es "hola" y el individuo es "hola", fitness = 1.0 (100%). Si es "hxlx", fitness = 0.5 (50%).
3.  **Selección**: Se ordenan los individuos por su aptitud (fitness).
4.  **Elitismo**: Los mejores individuos (configurables) pasan intactos a la siguiente generación para asegurar que la calidad de la solución no disminuya.
5.  **Cruza (Crossover)**: El resto de la nueva población se crea combinando el ADN (letras) de dos "padres" seleccionados al azar de la mitad superior de la población anterior.
6.  **Mutación**: Con una probabilidad baja (ej. 5%), algunos genes (letras) cambian aleatoriamente. Esto introduce diversidad y evita que el algoritmo se estanque en máximos locales.
7.  **Bucle**: Los pasos 2-6 se repiten hasta encontrar la solución (Fitness = 1.0) o alcanzar un límite de seguridad.

## 🛠️ Estructura del Código

- **`index.php`**: Punto de entrada. Contiene el formulario HTML/CSS y la lógica para instanciar la clase `Gac` con los parámetros del usuario.
- **`gac.php`**: Núcleo del algoritmo.
  - `__construct()`: Configura el objetivo y parámetros.
  - `execute()`: Bucle principal `while` que gestiona las generaciones.
  - `calculate_fitness()`: Función de evaluación.
  - `crossover()` y `mutate()`: Operadores genéticos.

## 📋 Uso

1.  Asegúrate de tener un servidor PHP corriendo (ej. `php -S localhost:8000`).
2.  Navega a `http://localhost:8000/genetic/`.
3.  Ingresa una frase objetivo (ej. "hola mundo").
4.  Ajusta los parámetros si lo deseas y pulsa "Evolucionar".

## 📝 Historial de Cambios

Consulta el archivo [CHANGELOG.md](CHANGELOG.md) para ver el historial de actualizaciones y optimizaciones.
