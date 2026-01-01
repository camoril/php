#!/bin/bash
set -e

echo "🚀 Iniciando Sistema de Hojas de Servicio..."

# Esperar a que la base de datos esté lista
echo "⏳ Esperando conexión a base de datos..."
until php -r "new PDO('mysql:host=${DB_HOST};dbname=${DB_NAME}', '${DB_USER}', '${DB_PASS}');" 2>/dev/null; do
    echo "   Base de datos no disponible, reintentando en 3 segundos..."
    sleep 3
done

echo "✅ Conexión a base de datos establecida"

# Crear configuración de base de datos dinámica
cat > /var/www/html/config/database.php << EOF
<?php
/**
 * config/database.php
 * Configuración de base de datos para Docker/Podman
 * Generado automáticamente por docker-entrypoint.sh
 */

// Configuración desde variables de entorno
define('DB_HOST', '${DB_HOST}');
define('DB_NAME', '${DB_NAME}');
define('DB_USER', '${DB_USER}');
define('DB_PASS', '${DB_PASS}');
define('DB_PORT', ${DB_PORT});
define('APP_NAME', 'Sistema de Hojas de Servicio');
define('APP_URL', 'http://localhost:8080');

// Configuración general
define('SESSION_TIMEOUT', 3600);
define('MAX_UPLOAD_SIZE', 10485760);
define('PDF_UPLOAD_DIR', __DIR__ . '/../pdfs/');

// Crear conexión
try {
    \$pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=" . DB_PORT . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException \$e) {
    die('Error de conexión a la base de datos: ' . \$e->getMessage());
}
?>
EOF

echo "✅ Configuración de base de datos generada"

# Verificar que existan los directorios necesarios
mkdir -p /var/www/html/pdfs
mkdir -p /var/www/html/assets/logos
chown -R www-data:www-data /var/www/html/pdfs
chown -R www-data:www-data /var/www/html/assets/logos

echo "✅ Directorios configurados"

# Verificar que las tablas existan
TABLES_COUNT=$(php -r "
    require '/var/www/html/config/database.php';
    \$stmt = \$pdo->query('SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = \"${DB_NAME}\"');
    echo \$stmt->fetch()['count'];
" 2>/dev/null || echo "0")

echo "📊 Tablas encontradas: ${TABLES_COUNT}"

if [ "$TABLES_COUNT" -lt "5" ]; then
    echo "⚠️  Advertencia: Algunas tablas pueden no estar creadas correctamente"
fi

echo "
═══════════════════════════════════════════════════════════════
✅ Sistema de Hojas de Servicio - INICIADO
═══════════════════════════════════════════════════════════════

📍 URL: http://localhost:8080
👤 Usuario: admin
🔑 Contraseña: admin123

🗄️  Base de Datos: ${DB_NAME}@${DB_HOST}
🐳 Contenedor: forms-app

═══════════════════════════════════════════════════════════════
"

# Ejecutar el comando original de Apache
exec "$@"
