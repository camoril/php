<?php
/**
 * config/database.php
 * Configuración de conexión a base de datos
 * 
 * INSTRUCCIONES:
 * 1. En desarrollo local: Los valores por defecto funcionan con install.sh
 * 2. En producción (cPanel): Cambiar los valores en la sección PRODUCCIÓN
 *    - DB_NAME: nombre_base_datos (asignado en cPanel)
 *    - DB_USER: usuario_base_datos (asignado en cPanel)
 *    - DB_PASS: contraseña_segura (asignada en cPanel)
 *    - APP_URL: https://tudominio.com/ruta-instalacion
 */

// ========================================
// DESARROLLO (Local)
// Incluye CLI, localhost y 127.0.0.1
// ========================================
$host = $_SERVER['HTTP_HOST'] ?? '';
$isCli = php_sapi_name() === 'cli';
$isLocal = in_array($host, ['localhost', 'localhost:80', '127.0.0.1', '::1']);

if ($isCli || $isLocal) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'forms_db');
    define('DB_USER', 'forms_user');
    define('DB_PASS', 'your_secure_password');
    define('DB_PORT', 3306);
    define('APP_NAME', 'Sistema de Hojas de Servicio');
    define('APP_URL', 'http://localhost/forms');
}
// ========================================
// PRODUCCIÓN (cPanel)
// IMPORTANTE: Cambiar estos valores en producción
// ========================================
else {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'nombre_base_datos');           // Cambiar
    define('DB_USER', 'usuario_base_datos');          // Cambiar
    define('DB_PASS', 'CAMBIAR_CONTRASEÑA_SEGURA');   // Cambiar
    define('DB_PORT', 3306);
    define('APP_NAME', 'Sistema de Hojas de Servicio');
    define('APP_URL', 'https://tudominio.com');       // Cambiar
}

// ========================================
// CONFIGURACIÓN GENERAL
// ========================================
define('SESSION_TIMEOUT', 3600); // 1 hora en segundos
define('MAX_UPLOAD_SIZE', 10485760); // 10MB
define('PDF_UPLOAD_DIR', __DIR__ . '/../pdfs/');

// ========================================
// CREAR CONEXIÓN
// ========================================
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=" . DB_PORT . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die('Error de conexión a la base de datos: ' . $e->getMessage());
}
?>
