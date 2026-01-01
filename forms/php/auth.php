<?php
/**
 * php/auth.php
 * Funciones de autenticación
 */

/**
 * Iniciar sesión de usuario
 */
function login($username, $password, $pdo) {
    $stmt = $pdo->prepare("SELECT id, username, password, nombre, tipo FROM usuarios WHERE username = ? AND activo = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nombre'] = $user['nombre'];
        $_SESSION['tipo'] = $user['tipo'];
        return true;
    }
    return false;
}

/**
 * Verificar si el usuario está autenticado
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

/**
 * Verificar si es administrador
 */
function isAdmin() {
    return isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin';
}

/**
 * Cerrar sesión
 */
function logout() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    header('Location: ' . APP_URL . '/');
    exit;
}

/**
 * Obtener usuario actual
 */
function getCurrentUser() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'nombre' => $_SESSION['nombre'] ?? null,
        'tipo' => $_SESSION['tipo'] ?? null,
    ];
}

/**
 * Redirigir si no está autenticado
 */
function requireAuth() {
    if (!isAuthenticated()) {
        $redirect = isset($_SERVER['REQUEST_URI']) ? urlencode($_SERVER['REQUEST_URI']) : '';
        header('Location: ' . APP_URL . '/?redirect=' . $redirect);
        exit;
    }
}

/**
 * Redirigir si no es administrador
 */
function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        header('HTTP/1.0 403 Forbidden');
        die('Acceso denegado. Se requieren permisos de administrador.');
    }
}
?>
