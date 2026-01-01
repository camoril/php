<?php
/**
 * export_interventions.php
 * Exporta intervenciones a CSV con filtros
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
require_once 'php/auth.php';
require_once 'php/interventions.php';

requireAuth();

// Recopilar filtros de búsqueda
$filters = [];

if (!empty($_GET['cliente'])) {
    $filters['cliente'] = trim($_GET['cliente']);
}

if (!empty($_GET['fecha_inicio'])) {
    $filters['fecha_inicio'] = $_GET['fecha_inicio'];
}

if (!empty($_GET['fecha_fin'])) {
    $filters['fecha_fin'] = $_GET['fecha_fin'];
}

if (!empty($_GET['estado'])) {
    $filters['estado'] = trim($_GET['estado']);
}

// Si no es admin, filtrar por usuario actual
if ($_SESSION['tipo'] !== 'admin') {
    $filters['usuario_id'] = $_SESSION['user_id'];
}

// Obtener intervenciones
$interventions = getAllInterventions($pdo, $filters);

// Generar CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="intervenciones_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

// BOM para UTF-8 en Excel
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Encabezados
$headers = ['ID', 'Fecha', 'Cliente', 'Trabajador', 'Responsable (Trabajador)', 'Responsable (Cliente)', 'Descripción', 'Horas', 'Estado', 'Notas', 'Creado'];
fputcsv($output, $headers, ',');

// Datos
foreach ($interventions as $row) {
    $line = [
        $row['id'],
        $row['fecha'],
        $row['cliente'],
        $row['usuario_nombre'] ?? 'N/A',
        $row['responsable_trabajador'],
        $row['responsable_cliente'] ?? '',
        str_replace(["\r", "\n"], ' ', $row['descripcion']),
        $row['horas_ocupadas'],
        $row['estado'],
        str_replace(["\r", "\n"], ' ', $row['notas_adicionales'] ?? ''),
        $row['fecha_creacion']
    ];
    fputcsv($output, $line, ',');
}

fclose($output);
exit;
?>
