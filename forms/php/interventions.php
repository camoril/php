<?php
/**
 * php/interventions.php
 * Funciones para manejar intervenciones (hojas de servicio)
 */

/**
 * Crear nueva intervención
 */
function createIntervention($data, $pdo) {
    $stmt = $pdo->prepare("
        INSERT INTO intervenciones 
        (fecha, hora, cliente, cliente_id, proyecto_id, contacto_id, descripcion, 
         responsable_trabajador, responsable_cliente, horas_ocupadas, 
         notas_adicionales, usuario_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    return $stmt->execute([
        $data['fecha'],
        $data['hora'] ?? null,
        $data['cliente'] ?? '',
        $data['cliente_id'] ?? null,
        $data['proyecto_id'] ?? null,
        $data['contacto_id'] ?? null,
        $data['descripcion'],
        $data['responsable_trabajador'],
        $data['responsable_cliente'] ?? null,
        $data['horas_ocupadas'],
        $data['notas_adicionales'] ?? null,
        $data['usuario_id']
    ]);
}

/**
 * Obtener intervención por ID
 */
function getIntervention($id, $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM intervenciones WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Obtener todas las intervenciones con filtros
 */
function getAllInterventions($filters = [], $pdo) {
    $query = "SELECT i.*, u.nombre as usuario_nombre FROM intervenciones i 
              LEFT JOIN usuarios u ON i.usuario_id = u.id 
              WHERE 1=1";
    $params = [];
    
    if (!empty($filters['usuario_id'])) {
        $query .= " AND i.usuario_id = ?";
        $params[] = $filters['usuario_id'];
    }
    
    if (!empty($filters['cliente'])) {
        $query .= " AND i.cliente LIKE ?";
        $params[] = '%' . $filters['cliente'] . '%';
    }
    
    if (!empty($filters['fecha_inicio'])) {
        $query .= " AND i.fecha >= ?";
        $params[] = $filters['fecha_inicio'];
    }
    
    if (!empty($filters['fecha_fin'])) {
        $query .= " AND i.fecha <= ?";
        $params[] = $filters['fecha_fin'];
    }
    
    if (!empty($filters['estado'])) {
        $query .= " AND i.estado = ?";
        $params[] = $filters['estado'];
    }
    
    $query .= " ORDER BY i.fecha DESC, i.id DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Obtener intervenciones por cliente
 */
function getInterventionsByClient($client, $pdo) {
    $stmt = $pdo->prepare("
        SELECT * FROM intervenciones 
        WHERE cliente = ? 
        ORDER BY fecha DESC
    ");
    $stmt->execute([$client]);
    return $stmt->fetchAll();
}

/**
 * Obtener intervenciones por rango de fechas
 */
function getInterventionsByDateRange($startDate, $endDate, $pdo) {
    $stmt = $pdo->prepare("
        SELECT * FROM intervenciones 
        WHERE fecha BETWEEN ? AND ? 
        ORDER BY fecha DESC
    ");
    $stmt->execute([$startDate, $endDate]);
    return $stmt->fetchAll();
}

/**
 * Actualizar intervención
 */
function updateIntervention($id, $data, $pdo) {
    $fields = [];
    $values = [];
    
    foreach ($data as $key => $value) {
        if (in_array($key, ['fecha', 'hora', 'cliente', 'cliente_id', 'proyecto_id', 'contacto_id', 
                           'descripcion', 'responsable_trabajador', 'responsable_cliente', 
                           'horas_ocupadas', 'notas_adicionales', 'estado'])) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
    }
    
    if (empty($fields)) {
        return false;
    }
    
    $values[] = $id;
    $query = "UPDATE intervenciones SET " . implode(', ', $fields) . " WHERE id = ?";
    
    $stmt = $pdo->prepare($query);
    return $stmt->execute($values);
}

/**
 * Guardar firma de intervención
 */
function saveFirma($id, $firmaBase64, $pdo) {
    $stmt = $pdo->prepare("
        UPDATE intervenciones 
        SET firma_base64 = ?, estado = 'firmado', updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
    return $stmt->execute([$firmaBase64, $id]);
}

function saveFirmaTecnico($id, $firmaBase64, $pdo) {
    $stmt = $pdo->prepare("
        UPDATE intervenciones 
        SET firma_tecnico_base64 = ?, updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
    return $stmt->execute([$firmaBase64, $id]);
}

/**
 * Obtener estadísticas
 */
function getStatistics($pdo) {
    $stats = [];
    
    // Total intervenciones
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM intervenciones");
    $stats['total'] = $stmt->fetch()['total'];
    
    // Total firmadas
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM intervenciones WHERE estado = 'firmado'");
    $stats['firmadas'] = $stmt->fetch()['total'];
    
    // Total pendientes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM intervenciones WHERE estado = 'pendiente'");
    $stats['pendientes'] = $stmt->fetch()['total'];
    
    // Total horas
    $stmt = $pdo->query("SELECT SUM(horas_ocupadas) as total FROM intervenciones");
    $result = $stmt->fetch();
    $stats['horas_totales'] = $result['total'] ?? 0;
    
    // Clientes únicos
    $stmt = $pdo->query("SELECT COUNT(DISTINCT cliente) as total FROM intervenciones");
    $stats['clientes_unicos'] = $stmt->fetch()['total'];
    
    return $stats;
}

/**
 * Validar datos de intervención
 */
function validateIntervention($data) {
    $errors = [];
    
    if (empty($data['fecha'])) {
        $errors[] = 'La fecha es requerida';
    } else if (!validateDate($data['fecha'])) {
        $errors[] = 'Formato de fecha inválido (use YYYY-MM-DD)';
    }
    
    // Cliente_id debe ser un número válido (nuevo sistema)
    // Si no viene cliente_id, se puede usar cliente como fallback
    if (empty($data['cliente_id']) && empty($data['cliente'])) {
        $errors[] = 'El cliente es requerido';
    }
    
    if (empty($data['descripcion'])) {
        $errors[] = 'La descripción es requerida';
    }
    
    if (empty($data['responsable_trabajador'])) {
        $errors[] = 'El responsable del trabajador es requerido';
    }
    
    // Validar que al menos uno de los dos campos exista: contacto_id O responsable_cliente
    $tieneContacto = !empty($data['contacto_id']) && $data['contacto_id'] > 0;
    $tieneResponsable = !empty(trim($data['responsable_cliente'] ?? ''));
    
    if (!$tieneContacto && !$tieneResponsable) {
        $errors[] = 'Debe seleccionar un contacto o ingresar un responsable del cliente';
    }
    
    if (empty($data['horas_ocupadas']) || !is_numeric($data['horas_ocupadas'])) {
        $errors[] = 'Las horas ocupadas deben ser un número válido';
    } else if ($data['horas_ocupadas'] <= 0 || $data['horas_ocupadas'] > 24) {
        $errors[] = 'Las horas deben estar entre 0.5 y 24';
    }
    
    return $errors;
}

/**
 * Validar formato de fecha
 */
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}
?>
