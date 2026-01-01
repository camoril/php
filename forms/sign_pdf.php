<?php
/**
 * sign_pdf.php
 * Capturar firma digital y guardar en BD
 * POST: id, firma_base64
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
require_once 'php/auth.php';
require_once 'php/interventions.php';

requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    die(json_encode(['error' => 'Método no permitido']));
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$firma = isset($_POST['firma_base64']) ? $_POST['firma_base64'] : '';
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : 'cliente'; // 'cliente' o 'tecnico'

if (!$id || empty($firma)) {
    http_response_code(400);
    die(json_encode(['error' => 'Parámetros inválidos']));
}

if (!in_array($tipo, ['cliente', 'tecnico'])) {
    http_response_code(400);
    die(json_encode(['error' => 'Tipo de firma inválido']));
}

// Verificar que la firma sea válida (data URL)
if (strpos($firma, 'data:image') === false) {
    http_response_code(400);
    die(json_encode(['error' => 'Formato de firma inválido']));
}

// Obtener intervención
$intervention = getIntervention($id, $pdo);
if (!$intervention) {
    http_response_code(404);
    die(json_encode(['error' => 'Intervención no encontrada']));
}

// Verificar permisos (propietario o admin)
if ($intervention['usuario_id'] != $_SESSION['user_id'] && $_SESSION['tipo'] !== 'admin') {
    http_response_code(403);
    die(json_encode(['error' => 'No tienes permiso para firmar esta intervención']));
}

// Guardar firma según tipo
if ($tipo === 'tecnico') {
    $success = saveFirmaTecnico($id, $firma, $pdo);
    $message = 'Firma del técnico guardada correctamente';
} else {
    $success = saveFirma($id, $firma, $pdo);
    $message = 'Firma del cliente guardada correctamente';
}

if ($success) {
    http_response_code(200);
    die(json_encode([
        'success' => true,
        'message' => $message,
        'id' => $id,
        'tipo' => $tipo,
        'redirect' => 'view_pdf.php?id=' . $id
    ]));
} else {
    http_response_code(500);
    die(json_encode(['error' => 'Error al guardar la firma']));
}
?>
