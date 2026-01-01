<?php
/**
 * delete_intervention.php
 * Eliminar una intervención
 */

// Forzar respuesta JSON
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
require_once 'php/auth.php';

requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    die(json_encode(['error' => 'Método no permitido']));
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if (!$id) {
    http_response_code(400);
    die(json_encode(['error' => 'ID inválido']));
}

// Obtener intervención
$stmt = $pdo->prepare("SELECT usuario_id FROM intervenciones WHERE id = ?");
$stmt->execute([$id]);
$intervention = $stmt->fetch();

if (!$intervention) {
    http_response_code(404);
    die(json_encode(['error' => 'Intervención no encontrada']));
}

// Verificar permisos (propietario o admin)
if ($intervention['usuario_id'] != $_SESSION['user_id'] && ($_SESSION['tipo'] ?? '') !== 'admin') {
    http_response_code(403);
    die(json_encode(['error' => 'No tienes permiso para eliminar esta intervención']));
}

// Eliminar intervención
$stmt = $pdo->prepare("DELETE FROM intervenciones WHERE id = ?");
if ($stmt->execute([$id])) {
    // Log de eliminación
    error_log("Intervención eliminada: ID=$id, Usuario=" . $_SESSION['user_id']);
    
    http_response_code(200);
    die(json_encode([
        'success' => true,
        'message' => 'Intervención eliminada correctamente',
        'redirect' => 'dashboard.php'
    ]));
} else {
    error_log("Error al eliminar intervención: ID=$id, Error=" . implode(", ", $stmt->errorInfo()));
    http_response_code(500);
    die(json_encode(['error' => 'Error al eliminar la intervención']));
}
?>
