<?php
/**
 * edit_intervention.php
 * Endpoint para editar una intervención
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
require_once 'php/auth.php';
require_once 'php/interventions.php';

requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['error' => 'Método no permitido']));
}

$id = (int)($_POST['id'] ?? 0);

if (!$id) {
    die(json_encode(['error' => 'ID inválido']));
}

// Obtener intervención
$intervention = getIntervention($id, $pdo);

if (!$intervention) {
    die(json_encode(['error' => 'Intervención no encontrada']));
}

// Verificar permisos (solo dueño o admin)
if ($intervention['usuario_id'] != $_SESSION['user_id'] && $_SESSION['tipo'] !== 'admin') {
    die(json_encode(['error' => 'No tienes permisos para editar esta intervención']));
}

// No permitir edición si está firmada
if ($intervention['estado'] === 'firmado') {
    die(json_encode(['error' => 'No puedes editar una intervención que ya ha sido firmada']));
}

// Recopilar datos
$data = [
    'fecha' => $_POST['fecha'] ?? '',
    'hora' => !empty($_POST['hora']) ? $_POST['hora'] : null,
    'cliente_id' => (int)($_POST['cliente_id'] ?? 0),
    'proyecto_id' => (int)($_POST['proyecto_id'] ?? 0),
    'contacto_id' => (int)($_POST['contacto_id'] ?? 0),
    'descripcion' => trim($_POST['descripcion'] ?? ''),
    'responsable_trabajador' => trim($_POST['responsable_trabajador'] ?? ''),
    'responsable_cliente' => trim($_POST['responsable_cliente'] ?? ''),
    'horas_ocupadas' => $_POST['horas_ocupadas'] ?? '',
    'notas_adicionales' => trim($_POST['notas_adicionales'] ?? ''),
];

// Validar
$errors = validateIntervention($data);

if (!empty($errors)) {
    die(json_encode(['error' => implode(', ', $errors)]));
}

// Actualizar
if (updateIntervention($id, $data, $pdo)) {
    die(json_encode(['success' => true, 'message' => 'Intervención actualizada correctamente']));
} else {
    die(json_encode(['error' => 'Error al actualizar la intervención']));
}
?>
