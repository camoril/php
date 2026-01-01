<?php
/**
 * manage_users.php
 * API endpoint para crear, editar y eliminar usuarios
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
require_once 'php/auth.php';

requireAdmin();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    if ($method === 'POST' && $action === 'create') {
        // Crear usuario
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $tipo = trim($_POST['tipo'] ?? 'trabajador');

        $errors = [];

        if (empty($username) || strlen($username) < 3) {
            $errors[] = 'Usuario debe tener al menos 3 caracteres';
        }

        if (empty($password) || strlen($password) < 6) {
            $errors[] = 'Contraseña debe tener al menos 6 caracteres';
        }

        if (empty($nombre)) {
            $errors[] = 'Nombre es requerido';
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido';
        }

        if (!in_array($tipo, ['trabajador', 'admin'])) {
            $errors[] = 'Tipo de usuario inválido';
        }

        // Verificar usuario único
        $checkStmt = $pdo->prepare("SELECT id FROM usuarios WHERE username = ?");
        $checkStmt->execute([$username]);
        if ($checkStmt->fetchColumn()) {
            $errors[] = 'El usuario ya existe';
        }

        if (!empty($errors)) {
            die(json_encode(['error' => implode(', ', $errors)]));
        }

        // Crear usuario
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (username, password, nombre, email, tipo, activo)
            VALUES (?, ?, ?, ?, ?, 1)
        ");

        if ($stmt->execute([$username, $hashedPassword, $nombre, $email, $tipo])) {
            die(json_encode(['success' => true, 'message' => 'Usuario creado correctamente', 'id' => $pdo->lastInsertId()]));
        } else {
            die(json_encode(['error' => 'Error al crear usuario']));
        }

    } elseif ($method === 'POST' && $action === 'edit') {
        // Editar usuario
        $id = (int)($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $tipo = trim($_POST['tipo'] ?? '');
        $activo = isset($_POST['activo']) ? 1 : 0;

        $errors = [];

        if (!$id) {
            $errors[] = 'ID de usuario inválido';
        }

        if (empty($nombre)) {
            $errors[] = 'Nombre es requerido';
        }

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido';
        }

        if (!in_array($tipo, ['trabajador', 'admin'])) {
            $errors[] = 'Tipo de usuario inválido';
        }

        if (!empty($errors)) {
            die(json_encode(['error' => implode(', ', $errors)]));
        }

        // Actualizar usuario
        $stmt = $pdo->prepare("
            UPDATE usuarios SET nombre = ?, email = ?, tipo = ?, activo = ?
            WHERE id = ?
        ");

        if ($stmt->execute([$nombre, $email, $tipo, $activo, $id])) {
            die(json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente']));
        } else {
            die(json_encode(['error' => 'Error al actualizar usuario']));
        }

    } elseif ($method === 'POST' && $action === 'reset_password') {
        // Resetear contraseña
        $id = (int)($_POST['id'] ?? 0);
        $newPassword = trim($_POST['password'] ?? '');

        $errors = [];

        if (!$id) {
            $errors[] = 'ID de usuario inválido';
        }

        if (empty($newPassword) || strlen($newPassword) < 6) {
            $errors[] = 'Nueva contraseña debe tener al menos 6 caracteres';
        }

        if (!empty($errors)) {
            die(json_encode(['error' => implode(', ', $errors)]));
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");

        if ($stmt->execute([$hashedPassword, $id])) {
            die(json_encode(['success' => true, 'message' => 'Contraseña reseteada correctamente']));
        } else {
            die(json_encode(['error' => 'Error al resetear contraseña']));
        }

    } elseif ($method === 'POST' && $action === 'toggle_status') {
        // Activar/Desactivar usuario
        $id = (int)($_POST['id'] ?? 0);
        $estado = (int)($_POST['estado'] ?? 0);

        if (!$id) {
            die(json_encode(['error' => 'ID de usuario inválido']));
        }

        $stmt = $pdo->prepare("UPDATE usuarios SET activo = ? WHERE id = ?");

        if ($stmt->execute([$estado, $id])) {
            $mensaje = $estado ? 'Usuario activado correctamente' : 'Usuario desactivado correctamente';
            die(json_encode(['success' => true, 'message' => $mensaje]));
        } else {
            die(json_encode(['error' => 'Error al cambiar estado del usuario']));
        }

    } elseif ($method === 'POST' && $action === 'delete_permanent') {
        // Eliminar usuario permanentemente
        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            die(json_encode(['error' => 'ID de usuario inválido']));
        }

        // No permitir eliminar al usuario con id=1 (admin principal)
        if ($id === 1) {
            die(json_encode(['error' => 'No se puede eliminar al administrador principal']));
        }

        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");

        if ($stmt->execute([$id])) {
            die(json_encode(['success' => true, 'message' => 'Usuario eliminado permanentemente']));
        } else {
            die(json_encode(['error' => 'Error al eliminar usuario']));
        }

    } elseif ($method === 'POST' && $action === 'delete') {
        // Deshabilitar usuario (compatibilidad con código antiguo)
        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            die(json_encode(['error' => 'ID de usuario inválido']));
        }

        $stmt = $pdo->prepare("UPDATE usuarios SET activo = 0 WHERE id = ?");

        if ($stmt->execute([$id])) {
            die(json_encode(['success' => true, 'message' => 'Usuario deshabilitado correctamente']));
        } else {
            die(json_encode(['error' => 'Error al deshabilitar usuario']));
        }

    } else {
        die(json_encode(['error' => 'Acción no válida']));
    }

} catch (Exception $e) {
    die(json_encode(['error' => 'Error del servidor: ' . $e->getMessage()]));
}
?>
