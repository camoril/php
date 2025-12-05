<?php
require_once 'config.php';

$csvFile = '2025.11.csv';
$tableName = 'noviembre_2025';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "Conectado a la base de datos.\n";

    // 1. Crear la tabla si no existe
    $sqlCreateTable = "CREATE TABLE IF NOT EXISTS `$tableName` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `CallStart` DATETIME,
        `ConnectedTime` TIME,
        `RingTime` INT,
        `Caller` VARCHAR(50),
        `Direction` VARCHAR(10),
        `CalledNumber` VARCHAR(50),
        `DialledNumber` VARCHAR(50),
        `Account` VARCHAR(50),
        `IsInternal` TINYINT,
        `CallID` VARCHAR(50),
        `Continuation` TINYINT,
        `Party1Device` VARCHAR(50),
        `Party1Name` VARCHAR(100),
        `Party2Device` VARCHAR(50),
        `Party2Name` VARCHAR(100),
        `HoldTime` INT,
        `ParkTime` INT,
        `AuthValid` TINYINT,
        `AuthCode` VARCHAR(50),
        `UserCharged` VARCHAR(50),
        `CallCharge` DECIMAL(10,2),
        `Currency` VARCHAR(10),
        `AccountLastUserChange` VARCHAR(50),
        `CallUnits` INT,
        `UnitsLastUserChange` INT,
        `CostPerUnit` DECIMAL(10,2),
        `MarkUp` DECIMAL(10,2),
        `ExternalTargetingCause` VARCHAR(100),
        `ExternalTargeterId` VARCHAR(100),
        `ExternalTargetedNumber` VARCHAR(100)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sqlCreateTable);
    echo "Tabla '$tableName' verificada/creada.\n";

    // 2. Leer CSV e Insertar
    if (!file_exists($csvFile)) {
        die("Error: Archivo CSV no encontrado.\n");
    }

    $handle = fopen($csvFile, "r");
    
    // Leer cabecera para saltarla
    $header = fgetcsv($handle);
    
    $stmt = $pdo->prepare("INSERT INTO `$tableName` (
        `CallStart`, `ConnectedTime`, `RingTime`, `Caller`, `Direction`, `CalledNumber`, 
        `DialledNumber`, `Account`, `IsInternal`, `CallID`, `Continuation`, `Party1Device`, 
        `Party1Name`, `Party2Device`, `Party2Name`, `HoldTime`, `ParkTime`, `AuthValid`, 
        `AuthCode`, `UserCharged`, `CallCharge`, `Currency`, `AccountLastUserChange`, 
        `CallUnits`, `UnitsLastUserChange`, `CostPerUnit`, `MarkUp`, `ExternalTargetingCause`, 
        `ExternalTargeterId`, `ExternalTargetedNumber`
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
    )");

    $inserted = 0;
    $pdo->beginTransaction();

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        // Mapeo y limpieza de datos
        // Call Start: 2025/11/01 00:00:47 -> 2025-11-01 00:00:47
        $callStart = str_replace('/', '-', $data[0]);
        
        $params = [
            $callStart, // CallStart
            $data[1], // ConnectedTime
            $data[2], // RingTime
            $data[3], // Caller
            $data[4], // Direction
            $data[5], // CalledNumber
            $data[6], // DialledNumber
            $data[7], // Account
            $data[8], // IsInternal
            $data[9], // CallID
            $data[10], // Continuation
            $data[11], // Party1Device
            $data[12], // Party1Name
            $data[13], // Party2Device
            $data[14], // Party2Name
            $data[15], // HoldTime
            $data[16], // ParkTime
            $data[17], // AuthValid
            $data[18], // AuthCode
            $data[19], // UserCharged
            $data[20], // CallCharge
            $data[21], // Currency
            $data[22], // AccountLastUserChange
            $data[23], // CallUnits
            $data[24], // UnitsLastUserChange
            $data[25], // CostPerUnit
            $data[26], // MarkUp
            $data[27], // ExternalTargetingCause
            $data[28], // ExternalTargeterId
            $data[29]  // ExternalTargetedNumber
        ];

        // Convertir vacíos a NULL para campos numéricos si es necesario, 
        // pero PDO suele manejar strings vacíos como 0 en campos INT dependiendo del modo.
        // Para mayor seguridad, podríamos limpiar. Por ahora probaremos directo.
        
        try {
            $stmt->execute($params);
            $inserted++;
        } catch (Exception $e) {
            echo "Error insertando fila: " . print_r($data, true) . "\nError: " . $e->getMessage() . "\n";
        }
    }

    $pdo->commit();
    fclose($handle);
    
    echo "Importación completada. Se insertaron $inserted registros en '$tableName'.\n";

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Error de base de datos: " . $e->getMessage() . "\n");
}
?>
