<?php
/**
 * manage_clientes.php
 * Gestionar clientes, proyectos y contactos
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
require_once 'php/auth.php';

requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Acciones de solo lectura (todos los usuarios autenticados)
$readOnlyActions = ['list_clientes', 'list_proyectos', 'list_contactos'];

// Si no es acción de solo lectura, verificar que sea admin
if (!in_array($action, $readOnlyActions) && !isAdmin()) {
    http_response_code(403);
    die(json_encode(['error' => 'No tienes permiso para realizar esta acción']));
}

try {
    // ==================== CLIENTES ====================
    if ($action === 'list_clientes') {
        $stmt = $pdo->query("SELECT * FROM clientes ORDER BY nombre ASC");
        die(json_encode(['clientes' => $stmt->fetchAll(PDO::FETCH_ASSOC)]));

    } elseif ($method === 'POST' && $action === 'create_cliente') {
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $contacto_principal = trim($_POST['contacto_principal'] ?? '');

        if (!$nombre) {
            die(json_encode(['error' => 'El nombre del cliente es requerido']));
        }

        $stmt = $pdo->prepare("
            INSERT INTO clientes (nombre, email, telefono, direccion, contacto_principal)
            VALUES (?, ?, ?, ?, ?)
        ");

        if ($stmt->execute([$nombre, $email, $telefono, $direccion, $contacto_principal])) {
            die(json_encode(['success' => true, 'message' => 'Cliente creado correctamente', 'id' => $pdo->lastInsertId()]));
        } else {
            die(json_encode(['error' => 'Error al crear cliente']));
        }

    } elseif ($method === 'POST' && $action === 'update_cliente') {
        $id = (int)($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $contacto_principal = trim($_POST['contacto_principal'] ?? '');

        if (!$id || !$nombre) {
            die(json_encode(['error' => 'Datos inválidos']));
        }

        $stmt = $pdo->prepare("
            UPDATE clientes 
            SET nombre = ?, email = ?, telefono = ?, direccion = ?, contacto_principal = ?
            WHERE id = ?
        ");

        if ($stmt->execute([$nombre, $email, $telefono, $direccion, $contacto_principal, $id])) {
            die(json_encode(['success' => true, 'message' => 'Cliente actualizado correctamente']));
        } else {
            die(json_encode(['error' => 'Error al actualizar cliente']));
        }

    } elseif ($method === 'POST' && $action === 'delete_cliente') {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            die(json_encode(['error' => 'ID inválido']));
        }

        $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = ?");
        if ($stmt->execute([$id])) {
            die(json_encode(['success' => true, 'message' => 'Cliente eliminado correctamente']));
        } else {
            die(json_encode(['error' => 'Error al eliminar cliente']));
        }

    // ==================== PROYECTOS ====================
    } elseif ($action === 'list_proyectos') {
        $cliente_id = (int)($_GET['cliente_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM proyectos WHERE cliente_id = ? AND estado = 'activo' ORDER BY nombre ASC");
        $stmt->execute([$cliente_id]);
        die(json_encode(['proyectos' => $stmt->fetchAll(PDO::FETCH_ASSOC)]));

    } elseif ($method === 'POST' && $action === 'create_proyecto') {
        $cliente_id = (int)($_POST['cliente_id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if (!$cliente_id || !$nombre) {
            die(json_encode(['error' => 'Datos requeridos incompletos']));
        }

        $stmt = $pdo->prepare("
            INSERT INTO proyectos (cliente_id, nombre, descripcion)
            VALUES (?, ?, ?)
        ");

        if ($stmt->execute([$cliente_id, $nombre, $descripcion])) {
            die(json_encode(['success' => true, 'message' => 'Proyecto creado correctamente', 'id' => $pdo->lastInsertId()]));
        } else {
            die(json_encode(['error' => 'Error al crear proyecto']));
        }

    } elseif ($method === 'POST' && $action === 'update_proyecto') {
        $id = (int)($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $estado = $_POST['estado'] ?? 'activo';

        if (!$id || !$nombre) {
            die(json_encode(['error' => 'Datos inválidos']));
        }

        $stmt = $pdo->prepare("UPDATE proyectos SET nombre = ?, descripcion = ?, estado = ? WHERE id = ?");

        if ($stmt->execute([$nombre, $descripcion, $estado, $id])) {
            die(json_encode(['success' => true, 'message' => 'Proyecto actualizado correctamente']));
        } else {
            die(json_encode(['error' => 'Error al actualizar proyecto']));
        }

    } elseif ($method === 'POST' && $action === 'delete_proyecto') {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            die(json_encode(['error' => 'ID inválido']));
        }

        $stmt = $pdo->prepare("DELETE FROM proyectos WHERE id = ?");
        if ($stmt->execute([$id])) {
            die(json_encode(['success' => true, 'message' => 'Proyecto eliminado correctamente']));
        } else {
            die(json_encode(['error' => 'Error al eliminar proyecto']));
        }

    // ==================== CONTACTOS ====================
    } elseif ($action === 'list_contactos') {
        $proyecto_id = (int)($_GET['proyecto_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM contactos WHERE proyecto_id = ? AND activo = 1 ORDER BY nombre ASC");
        $stmt->execute([$proyecto_id]);
        die(json_encode(['contactos' => $stmt->fetchAll(PDO::FETCH_ASSOC)]));

    } elseif ($method === 'POST' && $action === 'create_contacto') {
        $proyecto_id = (int)($_POST['proyecto_id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $cargo = trim($_POST['cargo'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        if (!$proyecto_id || !$nombre) {
            die(json_encode(['error' => 'Datos requeridos incompletos']));
        }

        $stmt = $pdo->prepare("
            INSERT INTO contactos (proyecto_id, nombre, cargo, email, telefono)
            VALUES (?, ?, ?, ?, ?)
        ");

        if ($stmt->execute([$proyecto_id, $nombre, $cargo, $email, $telefono])) {
            die(json_encode(['success' => true, 'message' => 'Contacto creado correctamente', 'id' => $pdo->lastInsertId()]));
        } else {
            die(json_encode(['error' => 'Error al crear contacto']));
        }

    } elseif ($method === 'POST' && $action === 'update_contacto') {
        $id = (int)($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $cargo = trim($_POST['cargo'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        if (!$id || !$nombre) {
            die(json_encode(['error' => 'Datos inválidos']));
        }

        $stmt = $pdo->prepare("UPDATE contactos SET nombre = ?, cargo = ?, email = ?, telefono = ? WHERE id = ?");

        if ($stmt->execute([$nombre, $cargo, $email, $telefono, $id])) {
            die(json_encode(['success' => true, 'message' => 'Contacto actualizado correctamente']));
        } else {
            die(json_encode(['error' => 'Error al actualizar contacto']));
        }

    } elseif ($method === 'POST' && $action === 'delete_contacto') {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            die(json_encode(['error' => 'ID inválido']));
        }

        $stmt = $pdo->prepare("DELETE FROM contactos WHERE id = ?");
        if ($stmt->execute([$id])) {
            die(json_encode(['success' => true, 'message' => 'Contacto eliminado correctamente']));
        } else {
            die(json_encode(['error' => 'Error al eliminar contacto']));
        }

    } else {
        die(json_encode(['error' => 'Acción no válida']));
    }

} catch (Exception $e) {
    die(json_encode(['error' => 'Error del servidor: ' . $e->getMessage()]));
}
?>
