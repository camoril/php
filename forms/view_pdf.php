<?php
/**
 * view_pdf.php
 * Visualizar intervención en formato PDF profesional (imprimible)
 * Uso: view_pdf.php?id=123
 * 
 * Características:
 * - Diseño profesional tipo formulario oficial
 * - Firma digital integrada
 * - Compatible con impresión A4
 * - Responsive y printable
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurar zona horaria para Ciudad de México
date_default_timezone_set('America/Mexico_City');

// Configurar locale a español
setlocale(LC_TIME, 'es_MX.UTF-8', 'es_MX', 'spanish');

require_once 'config/database.php';
require_once 'php/auth.php';

requireAuth();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    die('ID de intervención inválido');
}

$intervention = null;
$stmt = $pdo->prepare("
    SELECT i.*, u.nombre as trabajador_nombre 
    FROM intervenciones i
    LEFT JOIN usuarios u ON i.usuario_id = u.id
    WHERE i.id = ?
");
$stmt->execute([$id]);
$intervention = $stmt->fetch();

if (!$intervention) {
    die('Intervención no encontrada');
}

// Obtener información del cliente, proyecto y contacto si existen
$cliente_nombre = $intervention['cliente'];
$proyecto_nombre = '';
$contacto_nombre = '';

// Verificar si existen las columnas antes de acceder (compatible con versiones anteriores)
if (isset($intervention['cliente_id']) && $intervention['cliente_id']) {
    $stmt = $pdo->prepare("SELECT nombre FROM clientes WHERE id = ?");
    $stmt->execute([$intervention['cliente_id']]);
    $cliente_data = $stmt->fetch();
    if ($cliente_data) {
        $cliente_nombre = $cliente_data['nombre'];
    }
}

if (isset($intervention['proyecto_id']) && $intervention['proyecto_id']) {
    $stmt = $pdo->prepare("SELECT nombre FROM proyectos WHERE id = ?");
    $stmt->execute([$intervention['proyecto_id']]);
    $proyecto_data = $stmt->fetch();
    if ($proyecto_data) {
        $proyecto_nombre = $proyecto_data['nombre'];
    }
}

if (isset($intervention['contacto_id']) && $intervention['contacto_id']) {
    $stmt = $pdo->prepare("SELECT nombre FROM contactos WHERE id = ?");
    $stmt->execute([$intervention['contacto_id']]);
    $contacto_data = $stmt->fetch();
    if ($contacto_data) {
        $contacto_nombre = $contacto_data['nombre'];
    }
}

// Determinar responsable del cliente (prioridad: campo manual > contacto)
$responsable_cliente_display = '';
if (!empty($intervention['responsable_cliente'])) {
    // Prioridad 1: Campo manual "Responsable (cliente)"
    $responsable_cliente_display = $intervention['responsable_cliente'];
} elseif (!empty($contacto_nombre)) {
    // Prioridad 2: Nombre del contacto seleccionado
    $responsable_cliente_display = $contacto_nombre;
} else {
    // Sin información
    $responsable_cliente_display = '(No especificado)';
}

// Verificar que el usuario sea el propietario o admin
if ($intervention['usuario_id'] != $_SESSION['user_id'] && $_SESSION['tipo'] !== 'admin') {
    die('No tienes permiso para ver esta intervención');
}

// Obtener configuración de branding
$stmt = $pdo->query("SELECT * FROM configuracion_branding WHERE id = 1");
$branding = $stmt->fetch(PDO::FETCH_ASSOC);

// Valores por defecto si no hay configuración
$branding = $branding ?: [
    'logo_path' => null,
    'nombre_empresa' => 'Sistema de Hojas de Servicio',
    'email_empresa' => '',
    'telefono_empresa' => '',
    'direccion_empresa' => '',
    'color_primario' => '#0284C7',
    'color_secundario' => '#0EA5E9',
    'mostrar_logo_pdf' => 1
];

// Variables de color para usar en el CSS
$colorPrimario = $branding['color_primario'];
$colorSecundario = $branding['color_secundario'] ?? '#0EA5E9';

// Configurar fechas con zona horaria de México
$fecha = new DateTime($intervention['fecha'], new DateTimeZone('America/Mexico_City'));

// Meses en español
$meses_es = [
    1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
    5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
    9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
];

// Formatear: "31 de diciembre de 2025"
$dia = $fecha->format('d');
$mes = $meses_es[(int)$fecha->format('n')];
$anio = $fecha->format('Y');
$fecha_formateada = "$dia de $mes de $anio";

// Hora de creación (si existe)
$hora_creacion = '';
if (!empty($intervention['hora'])) {
    $hora_creacion = ' a las ' . date('H:i', strtotime($intervention['hora']));
}
$fecha_completa = $fecha_formateada . $hora_creacion;

// Fecha/hora actual en México
$fecha_actual = (new DateTime('now', new DateTimeZone('America/Mexico_City')))->format('d/m/Y H:i');

// Determine signature status
$tiene_firma = !empty($intervention['firma_base64']) && $intervention['firma_base64'] !== '';
$tiene_firma_tecnico = !empty($intervention['firma_tecnico_base64']) && $intervention['firma_tecnico_base64'] !== '';
$estado = $intervention['estado'] ?? 'pendiente';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoja de Servicio #<?php echo $id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #2d3748;
            line-height: 1.5;
            background: #f7fafc;
            padding: 20px;
        }

        /* Print styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .pdf-container {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }

        .pdf-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 50px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            min-height: 100vh;
        }

        /* Encabezado Profesional */
        .header {
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 30px;
            margin-bottom: 35px;
            padding-bottom: 20px;
            border-bottom: 3px solid <?php echo $colorPrimario; ?>;
        }

        .company-logo {
            width: auto;
            max-width: 150px;
            height: 80px;
            background: transparent;
            border-radius: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            font-weight: bold;
            flex-shrink: 0;
            overflow: visible;
        }

        .company-logo img {
            width: auto;
            height: 100%;
            object-fit: contain;
            padding: 0;
        }

        .header-title {
            text-align: center;
        }

        .header-title h1 {
            font-size: 24px;
            font-weight: 700;
            color: <?php echo $colorPrimario; ?>;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header-title p {
            font-size: 13px;
            color: #666;
            margin: 5px 0 0 0;
        }

        .doc-number {
            text-align: right;
        }

        .doc-number-label {
            font-size: 11px;
            color: #999;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .doc-number-value {
            font-size: 18px;
            font-weight: 700;
            color: <?php echo $colorPrimario; ?>;
        }

        /* Secciones de información */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            margin-bottom: 30px;
            padding: 20px;
            background: #f0f9ff;
            border-radius: 8px;
            border-left: 4px solid <?php echo $colorPrimario; ?>;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-item-label {
            font-size: 11px;
            color: <?php echo $colorPrimario; ?>;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .info-item-value {
            font-size: 15px;
            color: #2d3748;
            font-weight: 600;
            line-height: 1.4;
        }

        /* Secciones principales */
        .section {
            margin-bottom: 28px;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid <?php echo $colorPrimario; ?>;
        }

        .section-header i {
            color: <?php echo $colorPrimario; ?>;
            font-size: 16px;
        }

        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: <?php echo $colorPrimario; ?>;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .section-content {
            padding: 18px;
            background: #fafafa;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }

        /* Campos */
        .field-group {
            margin-bottom: 16px;
        }

        .field-group.full {
            grid-column: 1 / -1;
        }

        .field-label {
            font-size: 11px;
            color: <?php echo $colorPrimario; ?>;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
            display: block;
        }

        .field-value {
            font-size: 14px;
            color: #2d3748;
            line-height: 1.6;
            word-break: break-word;
            padding: 10px 0;
            border-bottom: 1px solid #cbd5e0;
            min-height: 24px;
        }

        .field-value.empty {
            color: #cbd5e0;
            font-style: italic;
        }

        /* Estado Badge */
        .status-section {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: #f0f9ff;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pendiente {
            background: #FEF3C7;
            color: #92400E;
        }

        .status-firmado {
            background: #D1FAE5;
            color: #065F46;
        }

        .status-info {
            flex: 1;
            font-size: 12px;
            color: #666;
        }

        /* Firma */
        .signature-section {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #e2e8f0;
        }

        .signature-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 30px;
        }

        .signature-box {
            display: flex;
            flex-direction: column;
        }

        .signature-label {
            font-size: 11px;
            color: <?php echo $colorPrimario; ?>;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 15px;
        }

        .signature-canvas {
            border: 2px solid #cbd5e0;
            border-radius: 4px;
            cursor: crosshair;
            background: white;
            height: 120px;
            display: block;
            width: 100%;
        }

        .signature-placeholder {
            border: 2px dashed #cbd5e0;
            border-radius: 4px;
            background: #fafafa;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #cbd5e0;
            font-size: 12px;
        }

        .signature-image {
            border: 2px solid #cbd5e0;
            border-radius: 4px;
            background: #fafafa;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
        }

        .signature-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .signature-text {
            font-size: 11px;
            color: #666;
            text-align: center;
            margin-top: 8px;
            font-style: italic;
        }

        /* Botones */
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            border: none;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, <?php echo $colorPrimario; ?> 0%, <?php echo $colorSecundario; ?> 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #2d3748;
        }

        .btn-secondary:hover {
            background: #cbd5e0;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        /* Modal */
        .modal-signature {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }

        .modal-signature.show {
            display: flex;
        }

        .modal-signature-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            font-size: 18px;
            font-weight: 700;
            color: <?php echo $colorPrimario; ?>;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            color: #999;
            cursor: pointer;
        }

        .modal-close:hover {
            color: #2d3748;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .pdf-container {
                padding: 25px;
            }

            .header {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .company-logo {
                justify-self: center;
            }

            .doc-number {
                text-align: center;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .signature-row {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .btn-group {
                justify-content: stretch;
            }

            .btn {
                flex: 1;
            }
        }

        /* Loading state */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(2, 132, 199, 0.3);
            border-radius: 50%;
            border-top-color: <?php echo $colorPrimario; ?>;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 11px;
            color: #999;
        }

        .signature-canvas {
            display: block;
            width: 100%;
            height: 150px;
            margin-bottom: 10px;
        }

        .signature-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 30px;
        }

        .signature-box {
            text-align: center;
        }

        .signature-line {
            border-top: 2px solid #333;
            margin-top: 60px;
            padding-top: 8px;
            min-height: 80px;
            display: flex;
            align-items: flex-start;
            justify-content: center;
        }

        .signature-image {
            max-height: 80px;
            max-width: 100%;
            margin-bottom: 8px;
        }

        .signature-label {
            font-size: 12px;
            font-weight: 600;
            color: #666;
            margin-top: 8px;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 11px;
            color: #999;
        }

        .action-buttons {
            position: sticky;
            bottom: 0;
            background: white;
            padding: 20px 0;
            margin-top: 30px;
            display: flex;
            gap: 10px;
            justify-content: center;
            border-top: 1px solid #ddd;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }

        .btn-primary {
            background: <?php echo $colorPrimario; ?>;
            color: white;
        }

        .btn-primary:hover {
            background: #0369A1;
        }

        .btn-secondary {
            background: #E5E7EB;
            color: #333;
        }

        .btn-secondary:hover {
            background: #D1D5DB;
        }

        @media print {
            body {
                background: white;
            }

            .pdf-container {
                max-width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            .action-buttons,
            .signature-controls {
                display: none !important;
            }

            .btn {
                display: none !important;
            }

            canvas {
                display: none !important;
            }

            a {
                color: <?php echo $colorPrimario; ?>;
                text-decoration: underline;
            }
        }

        @media (max-width: 768px) {
            .pdf-container {
                padding: 20px;
            }

            .doc-info,
            .signature-container {
                grid-template-columns: 1fr;
            }

            .field-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="pdf-container">
        <!-- HEADER PROFESIONAL -->
        <div class="header">
            <!-- LOGO -->
            <div class="company-logo">
                <?php if ($branding['mostrar_logo_pdf'] && $branding['logo_path']): ?>
                    <img src="<?php echo htmlspecialchars($branding['logo_path']); ?>" alt="Logo" style="width: 100%; height: 100%; object-fit: contain; padding: 5px;">
                <?php else: ?>
                    <i class="fas fa-wrench"></i>
                <?php endif; ?>
            </div>

            <!-- INFORMACIÓN DE LA EMPRESA Y TÍTULO -->
            <div class="header-title">
                <h1>Hoja de Servicio Técnico</h1>
                <p><?php echo htmlspecialchars($branding['nombre_empresa']); ?></p>
                <?php if ($branding['email_empresa'] || $branding['telefono_empresa']): ?>
                    <small style="display: block; color: #666; margin-top: 5px; font-size: 11px;">
                        <?php if ($branding['email_empresa']): ?>
                            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($branding['email_empresa']); ?>
                        <?php endif; ?>
                        <?php if ($branding['telefono_empresa']): ?>
                            <?php if ($branding['email_empresa']): ?> | <?php endif; ?>
                            <i class="fas fa-phone"></i> <?php echo htmlspecialchars($branding['telefono_empresa']); ?>
                        <?php endif; ?>
                    </small>
                <?php endif; ?>
            </div>

            <!-- FOLIO -->
            <div class="doc-number">
                <div class="doc-number-label">Folio</div>
                <div class="doc-number-value">#<?php echo str_pad($id, 6, '0', STR_PAD_LEFT); ?></div>
            </div>
        </div>

        <!-- INFORMACIÓN GENERAL -->
        <div class="info-grid">
            <div class="info-item">
                <span class="info-item-label"><i class="fas fa-calendar"></i> Fecha</span>
                <span class="info-item-value"><?php echo htmlspecialchars($fecha_completa); ?></span>
            </div>
            <div class="info-item">
                <span class="info-item-label"><i class="fas fa-building"></i> Cliente</span>
                <span class="info-item-value"><?php echo htmlspecialchars($cliente_nombre); ?></span>
            </div>
            <div class="info-item">
                <span class="info-item-label"><i class="fas fa-badge-check"></i> Estado</span>
                <span class="info-item-value">
                    <span class="status-badge status-<?php echo $intervention['estado']; ?>">
                        <?php echo ucfirst($intervention['estado']); ?>
                    </span>
                </span>
            </div>
        </div>

        <?php if (!empty($proyecto_nombre) || !empty($contacto_nombre)): ?>
        <!-- INFORMACIÓN DE PROYECTO Y CONTACTO -->
        <div class="info-grid">
            <?php if (!empty($proyecto_nombre)): ?>
            <div class="info-item">
                <span class="info-item-label"><i class="fas fa-tasks"></i> Proyecto</span>
                <span class="info-item-value"><?php echo htmlspecialchars($proyecto_nombre); ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($contacto_nombre)): ?>
            <div class="info-item">
                <span class="info-item-label"><i class="fas fa-user-circle"></i> Contacto</span>
                <span class="info-item-value"><?php echo htmlspecialchars($contacto_nombre); ?></span>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- SECCIÓN: INFORMACIÓN DEL PERSONAL -->
        <div class="section">
            <div class="section-header">
                <i class="fas fa-user-tie"></i>
                <h3 class="section-title">Personal Responsable</h3>
            </div>
            <div class="section-content">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="field-group">
                        <label class="field-label"><i class="fas fa-briefcase"></i> Técnico Responsable</label>
                        <div class="field-value"><?php echo htmlspecialchars($intervention['trabajador_nombre'] ?? $intervention['responsable_trabajador']); ?></div>
                    </div>
                    <div class="field-group">
                        <label class="field-label"><i class="fas fa-user-check"></i> Responsable del Cliente</label>
                        <div class="field-value"><?php echo htmlspecialchars($responsable_cliente_display); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN: DESCRIPCIÓN DE TRABAJO -->
        <div class="section">
            <div class="section-header">
                <i class="fas fa-file-alt"></i>
                <h3 class="section-title">Descripción de la Intervención</h3>
            </div>
            <div class="section-content">
                <div class="field-group full">
                    <div class="field-value" style="white-space: pre-wrap; border: none; min-height: 100px; padding: 0;">
                        <?php echo htmlspecialchars($intervention['descripcion']); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN: DETALLES TÉCNICOS -->
        <div class="section">
            <div class="section-header">
                <i class="fas fa-tools"></i>
                <h3 class="section-title">Detalles Técnicos</h3>
            </div>
            <div class="section-content">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="field-group">
                        <label class="field-label"><i class="fas fa-clock"></i> Horas Trabajadas</label>
                        <div class="field-value" style="font-weight: 600; color: <?php echo $colorPrimario; ?>;">
                            <?php echo number_format($intervention['horas_ocupadas'], 2); ?> horas
                        </div>
                    </div>
                    <div class="field-group">
                        <label class="field-label"><i class="fas fa-calendar-check"></i> Fecha Registro</label>
                        <div class="field-value">
                            <?php echo (new DateTime($intervention['created_at']))->format('d/m/Y H:i'); ?>
                        </div>
                    </div>
                </div>
                <?php if (!empty($intervention['notas_adicionales'])): ?>
                <div class="field-group full" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                    <label class="field-label"><i class="fas fa-sticky-note"></i> Notas Adicionales</label>
                    <div class="field-value" style="border: none; white-space: pre-wrap;">
                        <?php echo htmlspecialchars($intervention['notas_adicionales']); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- SECCIÓN: FIRMAS -->
        <div class="signature-section">
            <div class="section-header">
                <i class="fas fa-signature"></i>
                <h3 class="section-title">Autorización y Firmas</h3>
            </div>

            <!-- ÁREA DE FIRMAS EN DOS COLUMNAS -->
            <div class="signature-row" style="grid-template-columns: 1fr 1fr; gap: 30px;">
                
                <!-- FIRMA DEL CLIENTE -->
                <div class="signature-box">
                    <div class="signature-label" style="margin-bottom: 15px; font-size: 13px; font-weight: 700; color: <?php echo $colorPrimario; ?>;">
                        <i class="fas fa-user"></i> Firma de Conformidad del Cliente
                    </div>
                    
                    <?php if ($tiene_firma): ?>
                        <!-- Mostrar firma guardada -->
                        <div class="signature-image" style="border: 2px solid #10b981; border-radius: 6px; padding: 10px; background: white;">
                            <img src="<?php echo htmlspecialchars($intervention['firma_base64']); ?>" alt="Firma del Cliente" style="width: 100%; height: auto;">
                            <div style="text-align: center; margin-top: 10px;">
                                <span class="badge bg-success"><i class="fas fa-check-circle"></i> Firmado</span>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Canvas para firma del cliente -->
                        <div style="background: #f0f9ff; border: 2px dashed <?php echo $colorPrimario; ?>; border-radius: 6px; padding: 15px; margin-bottom: 10px;">
                            <p style="font-size: 12px; color: #666; margin-bottom: 10px; text-align: center;">
                                <i class="fas fa-info-circle"></i> Dibuja tu firma aquí
                            </p>
                            <canvas id="signature-pad-cliente" class="signature-canvas" width="350" height="120" style="border: 1px solid <?php echo $colorPrimario; ?>; border-radius: 4px; background: white; cursor: crosshair; width: 100%;"></canvas>
                            <div style="display: flex; gap: 8px; margin-top: 10px; justify-content: center;">
                                <button type="button" id="clear-signature-cliente" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-eraser"></i> Limpiar
                                </button>
                                <button type="button" id="save-signature-cliente" class="btn btn-sm btn-success">
                                    <i class="fas fa-save"></i> Guardar
                                </button>
                            </div>
                            <div id="signature-status-cliente" style="margin-top: 10px; text-align: center; font-size: 12px;"></div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- FIRMA DEL TÉCNICO -->
                <div class="signature-box">
                    <div class="signature-label" style="margin-bottom: 15px; font-size: 13px; font-weight: 700; color: <?php echo $colorPrimario; ?>;">
                        <i class="fas fa-user-tie"></i> Firma del Técnico Responsable
                    </div>
                    
                    <?php if ($tiene_firma_tecnico): ?>
                        <!-- Mostrar firma guardada -->
                        <div class="signature-image" style="border: 2px solid #10b981; border-radius: 6px; padding: 10px; background: white;">
                            <img src="<?php echo htmlspecialchars($intervention['firma_tecnico_base64']); ?>" alt="Firma del Técnico" style="width: 100%; height: auto;">
                            <div style="text-align: center; margin-top: 10px;">
                                <span class="badge bg-success"><i class="fas fa-check-circle"></i> Firmado</span>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Canvas para firma del técnico -->
                        <div style="background: #f0f9ff; border: 2px dashed <?php echo $colorPrimario; ?>; border-radius: 6px; padding: 15px; margin-bottom: 10px;">
                            <p style="font-size: 12px; color: #666; margin-bottom: 10px; text-align: center;">
                                <i class="fas fa-info-circle"></i> Dibuja tu firma aquí
                            </p>
                            <canvas id="signature-pad-tecnico" class="signature-canvas" width="350" height="120" style="border: 1px solid <?php echo $colorPrimario; ?>; border-radius: 4px; background: white; cursor: crosshair; width: 100%;"></canvas>
                            <div style="display: flex; gap: 8px; margin-top: 10px; justify-content: center;">
                                <button type="button" id="clear-signature-tecnico" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-eraser"></i> Limpiar
                                </button>
                                <button type="button" id="save-signature-tecnico" class="btn btn-sm btn-success">
                                    <i class="fas fa-save"></i> Guardar
                                </button>
                            </div>
                            <div id="signature-status-tecnico" style="margin-top: 10px; text-align: center; font-size: 12px;"></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- INFO ADICIONAL -->
            <?php if (!$tiene_firma || !$tiene_firma_tecnico): ?>
            <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; border-radius: 6px; margin-top: 20px;">
                <p style="font-size: 12px; color: #92400e; margin: 0;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Nota:</strong> Se requieren ambas firmas (cliente y técnico) para completar el documento oficialmente.
                </p>
            </div>
            <?php endif; ?>
        </div>

        <!-- FOOTER -->
        <div class="footer no-print">
            <p><strong>Documento oficial generado automáticamente</strong></p>
            <p style="font-size: 10px;">
                Generado por: <?php echo htmlspecialchars($branding['nombre_empresa']); ?> | 
                Fecha: <?php echo date('d/m/Y H:i:s'); ?> | 
                Folio: #<?php echo str_pad($id, 6, '0', STR_PAD_LEFT); ?>
                <?php if ($branding['direccion_empresa']): ?>
                    | Dirección: <?php echo htmlspecialchars($branding['direccion_empresa']); ?>
                <?php endif; ?>
            </p>
        </div>

        <!-- BOTONES DE ACCIÓN -->
        <div class="btn-group no-print" style="position: sticky; bottom: 0; background: white; padding: 20px 0; border-top: 1px solid #e2e8f0; margin-top: 40px;">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimir / Guardar PDF
            </button>
            <button class="btn btn-secondary" onclick="history.back()">
                <i class="fas fa-arrow-left"></i> Volver
            </button>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        // ==========================
        // INICIALIZAR SIGNATURE PADS
        // ==========================

        // Canvas del Cliente
        const canvasCliente = document.getElementById('signature-pad-cliente');
        let signaturePadCliente = null;
        
        if (canvasCliente) {
            signaturePadCliente = new SignaturePad(canvasCliente, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 0)'
            });

            // Ajustar canvas al contenedor
            function resizeCanvasCliente() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvasCliente.width = canvasCliente.offsetWidth * ratio;
                canvasCliente.height = canvasCliente.offsetHeight * ratio;
                canvasCliente.getContext('2d').scale(ratio, ratio);
                signaturePadCliente.clear();
            }
            window.addEventListener('resize', resizeCanvasCliente);
            resizeCanvasCliente();

            // Botón limpiar cliente
            document.getElementById('clear-signature-cliente')?.addEventListener('click', () => {
                signaturePadCliente.clear();
                document.getElementById('signature-status-cliente').innerHTML = '';
            });

            // Botón guardar firma cliente
            document.getElementById('save-signature-cliente')?.addEventListener('click', () => {
                const statusDiv = document.getElementById('signature-status-cliente');
                
                if (signaturePadCliente.isEmpty()) {
                    statusDiv.innerHTML = '<span style="color: #dc3545;"><i class="fas fa-exclamation-triangle"></i> Dibuja tu firma primero</span>';
                    return;
                }

                statusDiv.innerHTML = '<span style="color: <?php echo $colorPrimario; ?>;"><i class="fas fa-spinner fa-spin"></i> Guardando...</span>';

                const firmaBase64 = signaturePadCliente.toDataURL('image/png');
                const formData = new FormData();
                formData.append('id', <?php echo $id; ?>);
                formData.append('firma_base64', firmaBase64);
                formData.append('tipo', 'cliente');

                fetch('sign_pdf.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        statusDiv.innerHTML = '<span style="color: #10b981;"><i class="fas fa-check-circle"></i> Firma guardada</span>';
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        statusDiv.innerHTML = '<span style="color: #dc3545;"><i class="fas fa-times-circle"></i> ' + (data.error || 'Error al guardar') + '</span>';
                    }
                })
                .catch(error => {
                    statusDiv.innerHTML = '<span style="color: #dc3545;"><i class="fas fa-times-circle"></i> Error de conexión</span>';
                    console.error('Error:', error);
                });
            });
        }

        // Canvas del Técnico
        const canvasTecnico = document.getElementById('signature-pad-tecnico');
        let signaturePadTecnico = null;
        
        if (canvasTecnico) {
            signaturePadTecnico = new SignaturePad(canvasTecnico, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 0)'
            });

            // Ajustar canvas al contenedor
            function resizeCanvasTecnico() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvasTecnico.width = canvasTecnico.offsetWidth * ratio;
                canvasTecnico.height = canvasTecnico.offsetHeight * ratio;
                canvasTecnico.getContext('2d').scale(ratio, ratio);
                signaturePadTecnico.clear();
            }
            window.addEventListener('resize', resizeCanvasTecnico);
            resizeCanvasTecnico();

            // Botón limpiar técnico
            document.getElementById('clear-signature-tecnico')?.addEventListener('click', () => {
                signaturePadTecnico.clear();
                document.getElementById('signature-status-tecnico').innerHTML = '';
            });

            // Botón guardar firma técnico
            document.getElementById('save-signature-tecnico')?.addEventListener('click', () => {
                const statusDiv = document.getElementById('signature-status-tecnico');
                
                if (signaturePadTecnico.isEmpty()) {
                    statusDiv.innerHTML = '<span style="color: #dc3545;"><i class="fas fa-exclamation-triangle"></i> Dibuja tu firma primero</span>';
                    return;
                }

                statusDiv.innerHTML = '<span style="color: <?php echo $colorPrimario; ?>;"><i class="fas fa-spinner fa-spin"></i> Guardando...</span>';

                const firmaBase64 = signaturePadTecnico.toDataURL('image/png');
                const formData = new FormData();
                formData.append('id', <?php echo $id; ?>);
                formData.append('firma_base64', firmaBase64);
                formData.append('tipo', 'tecnico');

                fetch('sign_pdf.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        statusDiv.innerHTML = '<span style="color: #10b981;"><i class="fas fa-check-circle"></i> Firma guardada</span>';
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        statusDiv.innerHTML = '<span style="color: #dc3545;"><i class="fas fa-times-circle"></i> ' + (data.error || 'Error al guardar') + '</span>';
                    }
                })
                .catch(error => {
                    statusDiv.innerHTML = '<span style="color: #dc3545;"><i class="fas fa-times-circle"></i> Error de conexión</span>';
                    console.error('Error:', error);
                });
            });
        }
    </script>
</body>
</html>
