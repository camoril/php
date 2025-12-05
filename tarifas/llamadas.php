<?php
/*
 * llamadas.php
 * Modernizado con PDO y Seguridad Mejorada
 */

require_once 'config.php';

$error = null;
$results = [];
$type = $_POST['tipo'] ?? null;
$ext = $_POST['extension'] ?? '';
$dial = $_POST['marcado'] ?? '';
$mes = $_POST['mes'] ?? '';

// Validación de seguridad para el nombre de la tabla (Mes)
// Solo permitir letras, números y guiones bajos para evitar SQL Injection en el nombre de la tabla
if (!preg_match('/^[a-zA-Z0-9_]+$/', $mes)) {
    die("Error: Nombre de mes/tabla inválido.");
}

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos."); // No mostrar detalles del error en producción
}

// --- Lógica para Exportar CSV (Tipo 3) ---
// Esto debe ejecutarse antes de enviar cualquier HTML
if ($type == 3) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=detalle_' . $ext . '.csv');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Fecha', 'Extension', 'Numero', 'Tiempo']);

    $sql = "SELECT 
                `CallStart` as 'Fecha',
                `Caller` as 'Extension',
                `CalledNumber` as 'Numero',
                IFNULL(`ConnectedTime`, '00:00:00') as 'Tiempo'
            FROM `$mes` 
            WHERE `CalledNumber` LIKE :dial 
            AND `Caller` = :ext 
            AND `Caller` IS NOT NULL
            ORDER BY `CallStart` ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':dial' => $dial . '%', ':ext' => $ext]);
    
    while ($row = $stmt->fetch()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

// --- Lógica para Consultas HTML (Tipo 1 y 2) ---
if ($type == 1 || $type == 2) {
    if ($type == 1) {
        // Detalle de Llamadas
        $sql = "SELECT 
                    `CallStart` as 'Fecha',
                    `Caller` as 'Extension',
                    `CalledNumber` as 'Numero',
                    IFNULL(`ConnectedTime`, '00:00:00') as 'Tiempo'
                FROM `$mes` 
                WHERE `CalledNumber` LIKE :dial 
                AND `Caller` = :ext 
                AND `Caller` IS NOT NULL
                ORDER BY `CallStart` ASC";
    } else {
        // Total de Tiempo
        // Nota: WITH ROLLUP puede comportarse diferente en PDO dependiendo de la versión, 
        // pero la consulta SQL es estándar.
        $sql = "SELECT 
                    IFNULL(`Caller`, 'Total') as 'Extension',
                    SEC_TO_TIME(SUM(TIME_TO_SEC(`ConnectedTime`))) as 'Total'
                FROM `$mes` 
                WHERE `CalledNumber` LIKE :dial 
                AND `Caller` = :ext 
                GROUP BY `Caller` WITH ROLLUP";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':dial' => $dial . '%', ':ext' => $ext]);
    $results = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Llamadas</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center py-10">
    
    <div class="w-full max-w-4xl bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="bg-blue-600 p-4">
            <h1 class="text-white text-2xl font-bold text-center"><a href="./" class="hover:underline">Reporte de Llamadas</a></h1>
        </div>

        <div class="p-6">
            <?php if (empty($results)): ?>
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
                    <p class="font-bold">Sin resultados</p>
                    <p>No se encontraron registros para la extensión <strong><?= htmlspecialchars($ext) ?></strong> en el periodo seleccionado.</p>
                </div>
                <div class="text-center">
                    <a href="./" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Volver</a>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead class="bg-gray-200">
                            <tr>
                                <?php if ($type == 1): ?>
                                    <th class="px-4 py-2 text-left text-gray-600 font-bold">Fecha</th>
                                    <th class="px-4 py-2 text-left text-gray-600 font-bold">Extensión</th>
                                    <th class="px-4 py-2 text-left text-gray-600 font-bold">Número</th>
                                    <th class="px-4 py-2 text-left text-gray-600 font-bold">Tiempo</th>
                                <?php else: ?>
                                    <th class="px-4 py-2 text-left text-gray-600 font-bold">Extensión</th>
                                    <th class="px-4 py-2 text-left text-gray-600 font-bold">Tiempo Total</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $row): ?>
                                <tr class="border-b hover:bg-gray-50 text-sm text-gray-700">
                                    <?php if ($type == 1): ?>
                                        <td class="px-4 py-2"><?= htmlspecialchars($row['Fecha']) ?></td>
                                        <td class="px-4 py-2"><?= htmlspecialchars($row['Extension']) ?></td>
                                        <td class="px-4 py-2"><?= htmlspecialchars($row['Numero']) ?></td>
                                        <td class="px-4 py-2"><?= htmlspecialchars($row['Tiempo']) ?></td>
                                    <?php else: ?>
                                        <td class="px-4 py-2 font-bold"><?= htmlspecialchars($row['Extension']) ?></td>
                                        <td class="px-4 py-2"><?= htmlspecialchars($row['Total']) ?></td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-6 text-center">
                    <a href="./" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Realizar otra consulta</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>