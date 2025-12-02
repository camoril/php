# Last.fm Recent Tracks Visualizer

Este proyecto es una visualización web simple que muestra las últimas 10 canciones escuchadas por el usuario `camoril` en Last.fm.

## Funcionalidad

- **Grid Visual**: Muestra una cuadrícula de 5x2 con las portadas de las canciones recientes.
- **Actualización en Tiempo Real**: La página consulta la API de Last.fm cada 15 segundos para mantener la lista actualizada.
- **Fallback Inteligente de Imágenes**:
  - Intenta mostrar la portada del álbum primero.
  - Si el álbum no tiene imagen, consulta automáticamente la API para obtener la imagen del artista.
  - Si no hay imagen de artista, usa un placeholder genérico.
- **Información al Hover**: Al pasar el cursor sobre una imagen, se despliega el nombre del artista y la canción.

## Cómo funciona

El proyecto consta de un único archivo `index.html` que utiliza tecnologías web estándar (HTML, CSS, JS) sin dependencias externas ni frameworks pesados.

1.  **API de Last.fm**: Se conecta a `ws.audioscrobbler.com` usando `user.getrecenttracks` para obtener el historial y `artist.getinfo` para las imágenes de respaldo.
2.  **JavaScript Asíncrono**: Utiliza `async/await` y `Promise.all` para procesar las 10 pistas en paralelo, asegurando que la interfaz cargue rápidamente incluso cuando se deben buscar múltiples imágenes de artistas.
3.  **Caché Local**: Implementa un sistema de caché simple en memoria para las imágenes de artistas, evitando llamadas redundantes a la API.
4.  **CSS Grid**: Utiliza CSS Grid para un diseño responsivo y limpio que ocupa el 100% de la pantalla.
