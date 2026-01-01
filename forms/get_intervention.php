<?php
/**
 * get_intervention.php
 * Endpoint para obtener datos de una intervención (JSON)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
require_once 'php/auth.php';
require_once 'php/interventions.php';

requireAuth();

header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    die(json_encode(['error' => 'ID inválido']));
}

$intervention = getIntervention($id, $pdo);

if (!$intervention) {
    die(json_encode(['error' => 'Intervención no encontrada']));
}

// Verificar permisos
if ($intervention['usuario_id'] != $_SESSION['user_id'] && $_SESSION['tipo'] !== 'admin') {
    die(json_encode(['error' => 'No tienes permiso']));
}

// Devolver datos
echo json_encode($intervention);
?>
