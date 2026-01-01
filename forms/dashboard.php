<?php
/**
 * dashboard.php
 * Panel principal de la aplicación
 */

require_once 'config/database.php';
require_once 'php/auth.php';
require_once 'php/interventions.php';

// Configurar zona horaria para Ciudad de México
date_default_timezone_set('America/Mexico_City');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

requireAuth();

$user = getCurrentUser();
$stats = getStatistics($pdo);

// Determinar sección activa
if (isset($_POST['create_intervention'])) {
    $activeSection = 'nueva-intervencion';
} elseif (isset($_GET['search_cliente']) || isset($_GET['search_fecha_inicio']) || isset($_GET['search_fecha_fin']) || isset($_GET['search_estado']) || isset($_GET['seccion'])) {
    $activeSection = $_GET['seccion'] ?? 'consultas';
} else {
    $activeSection = 'inicio';
}

$formErrors = [];
$formSuccess = '';
$lastInsertedId = 0;

// Manejar creación de intervención
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_intervention'])) {
    $data = [
        'fecha' => $_POST['fecha'] ?? '',
        'hora' => !empty($_POST['hora']) ? $_POST['hora'] : date('H:i'),
        'cliente' => trim($_POST['cliente'] ?? ''),
        'cliente_id' => (int)($_POST['cliente_id'] ?? 0),
        'proyecto_id' => (int)($_POST['proyecto_id'] ?? 0),
        'contacto_id' => (int)($_POST['contacto_id'] ?? 0),
        'descripcion' => trim($_POST['descripcion'] ?? ''),
        'responsable_trabajador' => trim($_POST['responsable_trabajador'] ?? ''),
        'responsable_cliente' => trim($_POST['responsable_cliente'] ?? ''),
        'horas_ocupadas' => $_POST['horas_ocupadas'] ?? '',
        'notas_adicionales' => trim($_POST['notas_adicionales'] ?? ''),
        'usuario_id' => $user['id'],
    ];

    $formErrors = validateIntervention($data);

    if (empty($formErrors)) {
        if (createIntervention($data, $pdo)) {
            // Obtener el ID de la intervención recién creada
            $stmt = $pdo->query("SELECT LAST_INSERT_ID() as id");
            $lastInsertedId = $stmt->fetch()['id'];
            $formSuccess = 'Intervención registrada correctamente.';
            // Actualizar stats en caliente
            $stats = getStatistics($pdo);
            $_POST = [];
        } else {
            $formErrors[] = 'No se pudo guardar la intervención. Intente nuevamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo htmlspecialchars(APP_NAME); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0284C7;
            --secondary-color: #14B8A6;
            --danger-color: #EF4444;
            --success-color: #10B981;
            --warning-color: #F59E0B;
        }
        
        body {
            background-color: #F3F4F6;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-bottom: 2px solid var(--primary-color);
        }
        
        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
            font-size: 20px;
        }
        
        .sidebar {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            min-height: calc(100vh - 60px);
            border-right: 1px solid #E5E7EB;
        }
        
        .sidebar .nav-link {
            color: #6B7280;
            padding: 12px 20px;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: #F0F9FF;
            color: var(--primary-color);
            border-left-color: var(--primary-color);
        }
        
        .main-content {
            padding: 30px;
        }
        
        .page-section {
            display: none;
        }
        
        .page-section.active {
            display: block;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            border-left: 4px solid var(--primary-color);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        
        .stat-card.secondary {
            border-left-color: var(--secondary-color);
        }
        
        .stat-card.success {
            border-left-color: var(--success-color);
        }
        
        .stat-card.warning {
            border-left-color: var(--warning-color);
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .stat-label {
            color: #6B7280;
            font-size: 13px;
            margin-top: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(2, 132, 199, 0.3);
            color: white;
        }
        
        .page-title {
            color: #1F2937;
            font-weight: 700;
            margin-bottom: 30px;
            font-size: 28px;
        }
        
        .form-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                border-right: none;
                border-bottom: 1px solid #E5E7EB;
            }
            
            .main-content {
                padding: 15px;
            }
            
            .page-title {
                font-size: 22px;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container-fluid">
            <span class="navbar-brand">
                <i class="fas fa-clipboard-list"></i> <?php echo htmlspecialchars(APP_NAME); ?>
            </span>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link text-muted">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($user['nombre']); ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="php/logout.php">
                            <i class="fas fa-sign-out-alt"></i> Salir
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <nav class="nav flex-column">
                    <a class="nav-link <?php echo $activeSection === 'inicio' ? 'active' : ''; ?>" href="#" onclick="showSection('inicio', event)">
                        <i class="fas fa-home"></i> Inicio
                    </a>
                    <a class="nav-link <?php echo $activeSection === 'nueva-intervencion' ? 'active' : ''; ?>" href="#" onclick="showSection('nueva-intervencion', event)">
                        <i class="fas fa-plus-circle"></i> Nueva Intervención
                    </a>
                    <a class="nav-link <?php echo $activeSection === 'mis-intervenciones' ? 'active' : ''; ?>" href="#" onclick="showSection('mis-intervenciones', event)">
                        <i class="fas fa-list"></i> Mis Intervenciones
                    </a>
                    <a class="nav-link <?php echo $activeSection === 'consultas' ? 'active' : ''; ?>" href="#" onclick="showSection('consultas', event)">
                        <i class="fas fa-search"></i> Consultas
                    </a>
                    <?php if ($user['tipo'] === 'admin'): ?>
                    <hr class="my-2">
                    <a class="nav-link <?php echo $activeSection === 'admin-panel' ? 'active' : ''; ?>" href="#" onclick="showSection('admin-panel', event)">
                        <i class="fas fa-cogs"></i> Panel Admin
                    </a>
                    <?php endif; ?>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- SECCIÓN: INICIO -->
                <div id="inicio" class="page-section <?php echo $activeSection === 'inicio' ? 'active' : ''; ?>">
                    <h1 class="page-title">Bienvenido, <?php echo htmlspecialchars($user['nombre']); ?></h1>
                    
                    <div class="row">
                        <div class="col-md-6 col-lg-3">
                            <div class="stat-card">
                                <div class="stat-value"><?php echo $stats['total']; ?></div>
                                <div class="stat-label">Total Intervenciones</div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="stat-card success">
                                <div class="stat-value"><?php echo $stats['firmadas']; ?></div>
                                <div class="stat-label">Firmadas</div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="stat-card warning">
                                <div class="stat-value"><?php echo $stats['pendientes']; ?></div>
                                <div class="stat-label">Pendientes de Firma</div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="stat-card secondary">
                                <div class="stat-value"><?php echo number_format($stats['horas_totales'], 1); ?></div>
                                <div class="stat-label">Horas Totales</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="form-section">
                                <h5><i class="fas fa-bolt"></i> Acciones Rápidas</h5>
                                <p class="text-muted mt-3">Comienza a registrar tus intervenciones.</p>
                                <a href="#" onclick="showSection('nueva-intervencion')" class="btn btn-primary-custom">
                                    <i class="fas fa-plus"></i> Nueva Intervención
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-section">
                                <h5><i class="fas fa-file-pdf"></i> Información</h5>
                                <p class="text-muted mt-3">Sistema para registrar hojas de servicio con firma digital.</p>
                                <button class="btn btn-outline-primary" disabled>
                                    v0.0.1 Beta
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- SECCIÓN: NUEVA INTERVENCIÓN -->
                <div id="nueva-intervencion" class="page-section <?php echo $activeSection === 'nueva-intervencion' ? 'active' : ''; ?>">
                    <h1 class="page-title"><i class="fas fa-plus-circle"></i> Nueva Intervención</h1>
                    <div class="form-section">
                        <?php if (!empty($formErrors)): ?>
                            <div class="alert alert-danger">
                                <strong>Revisa los campos:</strong>
                                <ul class="mb-0 mt-2">
                                    <?php foreach ($formErrors as $err): ?>
                                        <li><?php echo htmlspecialchars($err); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if ($formSuccess): ?>
                            <div class="alert alert-success d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($formSuccess); ?>
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-success me-2" onclick="abrirPDFModal(<?php echo $lastInsertedId; ?>)">
                                        <i class="fas fa-file-pdf"></i> Ver PDF
                                    </button>
                                    <a href="view_pdf.php?id=<?php echo $lastInsertedId; ?>" class="btn btn-sm btn-secondary" target="_blank">
                                        <i class="fas fa-external-link-alt"></i> Abrir PDF
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <form id="intervention-form" method="POST" action="#" class="row g-3" onsubmit="return validarFormularioIntervencion()">
                            <input type="hidden" name="create_intervention" value="1">

                            <div class="col-md-3">
                                <label class="form-label">Fecha</label>
                                <input type="date" name="fecha" class="form-control" value="<?php echo htmlspecialchars($_POST['fecha'] ?? date('Y-m-d')); ?>" required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Hora de Creación <small class="text-muted">(opcional)</small></label>
                                <input type="time" name="hora" class="form-control" value="<?php echo htmlspecialchars($_POST['hora'] ?? date('H:i')); ?>">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Cliente</label>
                                <select name="cliente_id" id="intervention-cliente" class="form-control" onchange="cargarProyectosIntervention()" required>
                                    <option value="">-- Selecciona cliente --</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Proyecto</label>
                                <select name="proyecto_id" id="intervention-proyecto" class="form-control" onchange="cargarContactosIntervention()" required>
                                    <option value="">-- Selecciona proyecto --</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Contacto</label>
                                <select name="contacto_id" id="intervention-contacto" class="form-control">
                                    <option value="">-- Selecciona contacto --</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Horas ocupadas</label>
                                <input type="number" name="horas_ocupadas" class="form-control" min="0.5" max="24" step="0.5" value="<?php echo htmlspecialchars($_POST['horas_ocupadas'] ?? '1'); ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Responsable (trabajador)</label>
                                <input type="text" name="responsable_trabajador" class="form-control" value="<?php echo htmlspecialchars($_POST['responsable_trabajador'] ?? $user['nombre']); ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Responsable (cliente) <small class="text-muted">(o seleccione contacto)</small></label>
                                <input type="text" id="responsable-cliente-input" name="responsable_cliente" class="form-control" placeholder="Contacto del cliente" value="<?php echo htmlspecialchars($_POST['responsable_cliente'] ?? ''); ?>">
                                <small class="form-text text-muted">Si no completa este campo, se usará el contacto seleccionado</small>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Descripción de la intervención</label>
                                <textarea name="descripcion" class="form-control" rows="4" placeholder="Detalle las actividades realizadas" required><?php echo htmlspecialchars($_POST['descripcion'] ?? ''); ?></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notas adicionales (opcional)</label>
                                <textarea name="notas_adicionales" class="form-control" rows="2" placeholder="Observaciones, materiales, pendientes, etc."><?php echo htmlspecialchars($_POST['notas_adicionales'] ?? ''); ?></textarea>
                            </div>

                            <div class="col-12 d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-outline-secondary" onclick="resetForm('intervention-form')">
                                    <i class="fas fa-eraser"></i> Limpiar
                                </button>
                                <button type="submit" class="btn btn-primary-custom">
                                    <i class="fas fa-save"></i> Guardar intervención
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- SECCIÓN: MIS INTERVENCIONES -->
                <div id="mis-intervenciones" class="page-section <?php echo $activeSection === 'mis-intervenciones' ? 'active' : ''; ?>">
                    <h1 class="page-title"><i class="fas fa-list"></i> Mis Intervenciones</h1>
                    <div class="form-section">
                        <?php
                        // Obtener intervenciones del usuario actual
                        $userInterventions = getAllInterventions($pdo, ['usuario_id' => $user['id']]);
                        
                        // Filtros adicionales si viene del formulario de consultas
                        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search_client'])) {
                            $searchClient = trim($_GET['search_client']);
                            $userInterventions = array_filter($userInterventions, function($item) use ($searchClient) {
                                return stripos($item['cliente'], $searchClient) !== false;
                            });
                        }
                        ?>
                        
                        <?php if (empty($userInterventions)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No tienes intervenciones registradas aún.
                                <a href="#" onclick="showSection('nueva-intervencion', event)" class="alert-link">Crear una nueva</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 10%">#ID</th>
                                            <th style="width: 15%">Fecha</th>
                                            <th style="width: 25%">Cliente</th>
                                            <th style="width: 10%">Horas</th>
                                            <th style="width: 12%">Estado</th>
                                            <th style="width: 28%">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($userInterventions as $intervention): ?>
                                        <tr>
                                            <td>
                                                <strong>#<?php echo $intervention['id']; ?></strong>
                                            </td>
                                            <td>
                                                <?php echo (new DateTime($intervention['fecha']))->format('d/m/Y'); ?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($intervention['cliente']); ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    <?php echo number_format($intervention['horas_ocupadas'], 1); ?>h
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($intervention['estado'] === 'firmado'): ?>
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle"></i> Firmado
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-clock"></i> Pendiente
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" title="Ver/Firmar PDF" onclick="abrirPDFModal(<?php echo $intervention['id']; ?>)">
                                                    <i class="fas fa-file-pdf"></i> Ver PDF
                                                </button>
                                                <a href="view_pdf.php?id=<?php echo $intervention['id']; ?>" target="_blank" class="btn btn-sm btn-secondary" title="Abrir en nueva pestaña">
                                                    <i class="fas fa-external-link-alt"></i> Abrir PDF
                                                </a>
                                                <?php if ($intervention['estado'] !== 'firmado'): ?>
                                                <button class="btn btn-sm btn-warning" title="Editar" onclick="abrirEditarIntervención(<?php echo $intervention['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php endif; ?>
                                                <button class="btn btn-sm btn-danger" title="Eliminar" onclick="deleteIntervention(<?php echo $intervention['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-muted" style="font-size: 13px; margin-top: 15px;">
                                Total: <strong><?php echo count($userInterventions); ?></strong> intervenciones
                                <?php 
                                $firmadas = array_filter($userInterventions, fn($i) => $i['estado'] === 'firmado');
                                echo ' | Firmadas: <strong>' . count($firmadas) . '</strong>';
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- SECCIÓN: CONSULTAS -->
                <div id="consultas" class="page-section <?php echo $activeSection === 'consultas' ? 'active' : ''; ?>">
                    <h1 class="page-title"><i class="fas fa-search"></i> Consultas y Búsquedas</h1>
                    
                    <div class="form-section">
                        <h5 class="mb-4"><i class="fas fa-filter"></i> Filtrar Intervenciones</h5>
                        
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Cliente</label>
                                <input type="text" name="search_cliente" class="form-control" placeholder="Buscar por nombre de cliente" value="<?php echo htmlspecialchars($_GET['search_cliente'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Desde</label>
                                <input type="date" name="search_fecha_inicio" class="form-control" value="<?php echo htmlspecialchars($_GET['search_fecha_inicio'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Hasta</label>
                                <input type="date" name="search_fecha_fin" class="form-control" value="<?php echo htmlspecialchars($_GET['search_fecha_fin'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">Estado</label>
                                <select name="search_estado" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="pendiente" <?php echo ($_GET['search_estado'] ?? '') === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                    <option value="firmado" <?php echo ($_GET['search_estado'] ?? '') === 'firmado' ? 'selected' : ''; ?>>Firmado</option>
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary-custom me-2">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="limpiarBusqueda()">
                                    <i class="fas fa-redo"></i> Limpiar
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Resultados de búsqueda -->
                    <div class="form-section mt-4">
                        <?php
                        $searchFilters = [];
                        $hasSearch = false;
                        
                        if (!empty($_GET['search_cliente'])) {
                            $searchFilters['cliente'] = $_GET['search_cliente'];
                            $hasSearch = true;
                        }
                        
                        if (!empty($_GET['search_fecha_inicio'])) {
                            $searchFilters['fecha_inicio'] = $_GET['search_fecha_inicio'];
                            $hasSearch = true;
                        }
                        
                        if (!empty($_GET['search_fecha_fin'])) {
                            $searchFilters['fecha_fin'] = $_GET['search_fecha_fin'];
                            $hasSearch = true;
                        }
                        
                        if (!empty($_GET['search_estado'])) {
                            $searchFilters['estado'] = $_GET['search_estado'];
                            $hasSearch = true;
                        }
                        
                        // Agregar usuario_id al filtro para que solo muestre los del usuario actual
                        $searchFilters['usuario_id'] = $user['id'];
                        
                        $searchResults = getAllInterventions($pdo, $searchFilters);
                        ?>
                        
                        <?php if ($hasSearch): ?>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">
                                    <i class="fas fa-list"></i> Resultados 
                                    <span class="badge bg-primary"><?php echo count($searchResults); ?></span>
                                </h5>
                                <?php if (!empty($searchResults)): ?>
                                <a href="export_interventions.php?<?php echo http_build_query($_GET); ?>" class="btn btn-success btn-sm" target="_blank">
                                    <i class="fas fa-download"></i> Exportar CSV
                                </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (empty($searchResults) && $hasSearch): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No se encontraron intervenciones que coincidan con los criterios de búsqueda.
                            </div>
                        <?php elseif (!empty($searchResults)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 10%">#ID</th>
                                            <th style="width: 15%">Fecha</th>
                                            <th style="width: 25%">Cliente</th>
                                            <th style="width: 10%">Horas</th>
                                            <th style="width: 12%">Estado</th>
                                            <th style="width: 28%">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($searchResults as $intervention): ?>
                                        <tr>
                                            <td><strong>#<?php echo $intervention['id']; ?></strong></td>
                                            <td><?php echo (new DateTime($intervention['fecha']))->format('d/m/Y'); ?></td>
                                            <td><?php echo htmlspecialchars($intervention['cliente']); ?></td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    <?php echo number_format($intervention['horas_ocupadas'], 1); ?>h
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($intervention['estado'] === 'firmado'): ?>
                                                    <span class="badge bg-success"><i class="fas fa-check-circle"></i> Firmado</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning"><i class="fas fa-clock"></i> Pendiente</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" onclick="abrirPDFModal(<?php echo $intervention['id']; ?>)">
                                                    <i class="fas fa-file-pdf"></i> Ver PDF
                                                </button>
                                                <a href="view_pdf.php?id=<?php echo $intervention['id']; ?>" target="_blank" class="btn btn-sm btn-secondary">
                                                    <i class="fas fa-external-link-alt"></i> Abrir PDF
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php elseif (!$hasSearch): ?>
                            <div class="alert alert-secondary">
                                <i class="fas fa-search"></i> Usa los filtros arriba para buscar intervenciones.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- SECCIÓN: ADMIN -->
                <?php if ($user['tipo'] === 'admin'): ?>
                <div id="admin-panel" class="page-section <?php echo $activeSection === 'admin-panel' ? 'active' : ''; ?>">
                    <h1 class="page-title"><i class="fas fa-cogs"></i> Panel de Administración</h1>
                    
                    <!-- Tabs para Admin -->
                    <ul class="nav nav-tabs mb-4" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="admin-intervenciones-tab" data-bs-toggle="tab" data-bs-target="#admin-intervenciones" type="button" role="tab">
                                <i class="fas fa-list"></i> Todas las Intervenciones
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="admin-usuarios-tab" data-bs-toggle="tab" data-bs-target="#admin-usuarios" type="button" role="tab">
                                <i class="fas fa-users"></i> Gestión de Usuarios
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="admin-estadisticas-tab" data-bs-toggle="tab" data-bs-target="#admin-estadisticas" type="button" role="tab">
                                <i class="fas fa-chart-bar"></i> Estadísticas
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="admin-branding-tab" data-bs-toggle="tab" data-bs-target="#admin-branding" type="button" role="tab">
                                <i class="fas fa-palette"></i> Branding
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="admin-clientes-tab" data-bs-toggle="tab" data-bs-target="#admin-clientes" type="button" role="tab">
                                <i class="fas fa-building"></i> Clientes y Proyectos
                            </button>
                        </li>
                    </ul>

                    <!-- Contenido de Tabs -->
                    <div class="tab-content">
                        <!-- TAB 1: TODAS LAS INTERVENCIONES -->
                        <div class="tab-pane fade show active" id="admin-intervenciones" role="tabpanel">
                            <div class="form-section">
                                <?php
                                $allInterventions = getAllInterventions($pdo, []);
                                ?>
                                <h5 class="mb-3">
                                    <i class="fas fa-list"></i> Todas las Intervenciones
                                    <span class="badge bg-primary"><?php echo count($allInterventions); ?></span>
                                </h5>
                                
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#ID</th>
                                                <th>Fecha</th>
                                                <th>Cliente</th>
                                                <th>Trabajador</th>
                                                <th>Horas</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($allInterventions as $intervention): ?>
                                            <tr>
                                                <td><strong>#<?php echo $intervention['id']; ?></strong></td>
                                                <td><?php echo (new DateTime($intervention['fecha']))->format('d/m/Y'); ?></td>
                                                <td><?php echo htmlspecialchars($intervention['cliente']); ?></td>
                                                <td>
                                                    <small><?php echo htmlspecialchars($intervention['usuario_nombre'] ?? 'N/A'); ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark">
                                                        <?php echo number_format($intervention['horas_ocupadas'], 1); ?>h
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($intervention['estado'] === 'firmado'): ?>
                                                        <span class="badge bg-success"><i class="fas fa-check"></i> Firmado</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning"><i class="fas fa-clock"></i> Pendiente</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-xs btn-primary" style="padding: 3px 6px; font-size: 11px;" title="Ver PDF" onclick="abrirPDFModal(<?php echo $intervention['id']; ?>)">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </button>
                                                    <a href="view_pdf.php?id=<?php echo $intervention['id']; ?>" target="_blank" class="btn btn-xs btn-secondary" style="padding: 3px 6px; font-size: 11px;" title="Abrir en nueva pestaña">
                                                        <i class="fas fa-external-link-alt"></i>
                                                    </a>
                                                    <?php if ($intervention['estado'] !== 'firmado'): ?>
                                                    <button class="btn btn-xs btn-warning" style="padding: 3px 6px; font-size: 11px;" title="Editar" onclick="abrirEditarIntervención(<?php echo $intervention['id']; ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    <button class="btn btn-xs btn-danger" style="padding: 3px 6px; font-size: 11px;" title="Eliminar" onclick="deleteIntervention(<?php echo $intervention['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: GESTIÓN DE USUARIOS -->
                        <div class="tab-pane fade" id="admin-usuarios" role="tabpanel">
                            <div class="form-section">
                                <?php
                                $stmt = $pdo->query("SELECT id, username, nombre, email, tipo, activo, created_at FROM usuarios ORDER BY created_at DESC");
                                $usuarios = $stmt->fetchAll();
                                ?>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">
                                        <i class="fas fa-users"></i> Usuarios del Sistema
                                        <span class="badge bg-primary"><?php echo count($usuarios); ?></span>
                                    </h5>
                                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#crearUsuarioModal">
                                        <i class="fas fa-plus"></i> Crear Usuario
                                    </button>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Usuario</th>
                                                <th>Nombre</th>
                                                <th>Email</th>
                                                <th>Tipo</th>
                                                <th>Estado</th>
                                                <th>Creado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($usuarios as $usr): ?>
                                            <tr>
                                                <td><code><?php echo htmlspecialchars($usr['username']); ?></code></td>
                                                <td><?php echo htmlspecialchars($usr['nombre']); ?></td>
                                                <td><small><?php echo htmlspecialchars($usr['email'] ?? '—'); ?></small></td>
                                                <td>
                                                    <?php if ($usr['tipo'] === 'admin'): ?>
                                                        <span class="badge bg-danger">Admin</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Trabajador</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($usr['activo']): ?>
                                                        <span class="badge bg-success">Activo</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Inactivo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><small><?php echo (new DateTime($usr['created_at']))->format('d/m/Y'); ?></small></td>
                                                <td>
                                                    <button class="btn btn-xs btn-warning" style="padding: 3px 6px; font-size: 11px;" title="Editar" onclick="editarUsuario(<?php echo $usr['id']; ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-xs btn-info" style="padding: 3px 6px; font-size: 11px;" title="Resetear contraseña" onclick="resetearContraseña(<?php echo $usr['id']; ?>, '<?php echo htmlspecialchars($usr['nombre']); ?>')">
                                                        <i class="fas fa-key"></i>
                                                    </button>
                                                    <?php if ($usr['activo']): ?>
                                                    <button class="btn btn-xs btn-secondary" style="padding: 3px 6px; font-size: 11px;" title="Desactivar cuenta" onclick="toggleUsuarioEstado(<?php echo $usr['id']; ?>, '<?php echo htmlspecialchars($usr['nombre']); ?>', 0)">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                    <?php else: ?>
                                                    <button class="btn btn-xs btn-success" style="padding: 3px 6px; font-size: 11px;" title="Activar cuenta" onclick="toggleUsuarioEstado(<?php echo $usr['id']; ?>, '<?php echo htmlspecialchars($usr['nombre']); ?>', 1)">
                                                        <i class="fas fa-check-circle"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    <button class="btn btn-xs btn-danger" style="padding: 3px 6px; font-size: 11px;" title="Eliminar permanentemente" onclick="eliminarUsuario(<?php echo $usr['id']; ?>, '<?php echo htmlspecialchars($usr['nombre']); ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 3: ESTADÍSTICAS -->
                        <div class="tab-pane fade" id="admin-estadisticas" role="tabpanel">
                            <div class="form-section">
                                <?php
                                // Estadísticas globales
                                $stmt = $pdo->query("SELECT COUNT(*) as total_usuarios FROM usuarios");
                                $totalUsuarios = $stmt->fetch()['total_usuarios'];
                                
                                $stmt = $pdo->query("SELECT COUNT(*) as total_intervenciones FROM intervenciones");
                                $totalIntervenciones = $stmt->fetch()['total_intervenciones'];
                                
                                $stmt = $pdo->query("SELECT SUM(horas_ocupadas) as total_horas FROM intervenciones");
                                $totalHoras = $stmt->fetch()['total_horas'] ?? 0;
                                
                                $stmt = $pdo->query("SELECT COUNT(DISTINCT cliente) as clientes_unicos FROM intervenciones");
                                $clientesUnicos = $stmt->fetch()['clientes_unicos'];
                                ?>
                                
                                <h5 class="mb-4"><i class="fas fa-chart-bar"></i> Resumen del Sistema</h5>
                                
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="stat-card">
                                            <div class="stat-value"><?php echo $totalUsuarios; ?></div>
                                            <div class="stat-label">Usuarios Totales</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="stat-card secondary">
                                            <div class="stat-value"><?php echo $totalIntervenciones; ?></div>
                                            <div class="stat-label">Intervenciones</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="stat-card success">
                                            <div class="stat-value"><?php echo number_format($totalHoras, 0); ?></div>
                                            <div class="stat-label">Horas Totales</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="stat-card warning">
                                            <div class="stat-value"><?php echo $clientesUnicos; ?></div>
                                            <div class="stat-label">Clientes Únicos</div>
                                        </div>
                                    </div>
                                </div>

                                <h5 class="mt-5 mb-3"><i class="fas fa-user-tie"></i> Top Trabajadores (por horas)</h5>
                                <?php
                                $stmt = $pdo->query("
                                    SELECT u.nombre, COUNT(i.id) as intervenciones, SUM(i.horas_ocupadas) as total_horas
                                    FROM intervenciones i
                                    LEFT JOIN usuarios u ON i.usuario_id = u.id
                                    GROUP BY u.id
                                    ORDER BY total_horas DESC
                                    LIMIT 5
                                ");
                                $topTrabajadores = $stmt->fetchAll();
                                ?>
                                
                                <div class="list-group">
                                    <?php foreach ($topTrabajadores as $trab): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?php echo htmlspecialchars($trab['nombre'] ?? 'Desconocido'); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo $trab['intervenciones']; ?> intervenciones</small>
                                            </div>
                                            <span class="badge bg-primary"><?php echo number_format($trab['total_horas'], 1); ?>h</span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 4: BRANDING -->
                        <div class="tab-pane fade" id="admin-branding" role="tabpanel">
                            <div class="form-section">
                                <h5 class="mb-4"><i class="fas fa-palette"></i> Configuración de Branding</h5>
                                
                                <?php
                                // Obtener configuración actual
                                $stmt = $pdo->query("SELECT * FROM configuracion_branding WHERE id = 1");
                                $branding = $stmt->fetch(PDO::FETCH_ASSOC);
                                ?>
                                
                                <!-- Logo -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h6 class="mb-3"><i class="fas fa-image"></i> Logo de la Empresa</h6>
                                        <div class="card border-light p-4 text-center" style="background: #f9fafb;">
                                            <?php if ($branding && $branding['logo_path']): ?>
                                                <div style="margin-bottom: 15px;">
                                                    <img id="preview-logo" src="<?php echo htmlspecialchars($branding['logo_path']); ?>" alt="Logo actual" style="max-width: 150px; max-height: 150px;">
                                                    <p style="margin-top: 10px; font-size: 12px; color: #666;">Logo actual</p>
                                                </div>
                                            <?php else: ?>
                                                <div id="preview-logo" style="width: 150px; height: 150px; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; background: #e5e7eb; border-radius: 8px; color: #999;">
                                                    <i class="fas fa-image" style="font-size: 48px;"></i>
                                                </div>
                                                <p style="font-size: 12px; color: #666;">No hay logo configurado</p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mt-3">
                                            <input type="file" id="logo-input" accept=".svg,.png,.jpg,.jpeg,.gif" style="display: none;">
                                            <button class="btn btn-primary btn-sm w-100" onclick="document.getElementById('logo-input').click()">
                                                <i class="fas fa-upload"></i> Subir Logo
                                            </button>
                                            <small class="text-muted d-block mt-2">Formatos: SVG, PNG, JPG, GIF (máx 5MB)</small>
                                            <div id="upload-status" style="margin-top: 10px;"></div>
                                        </div>
                                    </div>

                                    <!-- Información de la Empresa -->
                                    <div class="col-md-6">
                                        <h6 class="mb-3"><i class="fas fa-building"></i> Información de la Empresa</h6>
                                        <form id="branding-form">
                                            <div class="mb-3">
                                                <label class="form-label">Nombre de la Empresa</label>
                                                <input type="text" class="form-control" id="nombre-empresa" name="nombre_empresa" value="<?php echo htmlspecialchars($branding['nombre_empresa'] ?? ''); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" id="email-empresa" name="email_empresa" value="<?php echo htmlspecialchars($branding['email_empresa'] ?? ''); ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Teléfono</label>
                                                <input type="text" class="form-control" id="telefono-empresa" name="telefono_empresa" value="<?php echo htmlspecialchars($branding['telefono_empresa'] ?? ''); ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Dirección</label>
                                                <textarea class="form-control" id="direccion-empresa" name="direccion_empresa" rows="3"><?php echo htmlspecialchars($branding['direccion_empresa'] ?? ''); ?></textarea>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Colores y Opciones -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="mb-3"><i class="fas fa-palette"></i> Colores</h6>
                                        <div class="mb-3">
                                            <label class="form-label">Color Primario</label>
                                            <div class="input-group">
                                                <input type="color" class="form-control form-control-color" id="color-primario" name="color_primario" value="<?php echo htmlspecialchars($branding['color_primario'] ?? '#0284C7'); ?>" style="width: 60px;" onchange="sincronizarColorHex('primario')">
                                                <input type="text" class="form-control" id="color-primario-hex" placeholder="#0284C7" maxlength="7" pattern="#[0-9A-Fa-f]{6}" value="<?php echo htmlspecialchars($branding['color_primario'] ?? '#0284C7'); ?>" oninput="sincronizarHexColor('primario')">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Color Secundario</label>
                                            <div class="input-group">
                                                <input type="color" class="form-control form-control-color" id="color-secundario" name="color_secundario" value="<?php echo htmlspecialchars($branding['color_secundario'] ?? '#0EA5E9'); ?>" style="width: 60px;" onchange="sincronizarColorHex('secundario')">
                                                <input type="text" class="form-control" id="color-secundario-hex" placeholder="#0EA5E9" maxlength="7" pattern="#[0-9A-Fa-f]{6}" value="<?php echo htmlspecialchars($branding['color_secundario'] ?? '#0EA5E9'); ?>" oninput="sincronizarHexColor('secundario')">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <h6 class="mb-3"><i class="fas fa-cog"></i> Opciones</h6>
                                        <div class="form-check mb-3">
                                            <input type="checkbox" class="form-check-input" id="mostrar-logo-pdf" name="mostrar_logo_pdf" <?php echo ($branding['mostrar_logo_pdf'] ?? 0) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="mostrar-logo-pdf">
                                                Mostrar logo en PDF
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="mostrar-firma-tecnico" name="mostrar_firma_tecnico" <?php echo ($branding['mostrar_firma_tecnico'] ?? 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="mostrar-firma-tecnico">
                                                Requerer firma del técnico
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Botones de acción -->
                                <div class="mt-4 pt-4 border-top">
                                    <button class="btn btn-primary" onclick="guardarBranding()">
                                        <i class="fas fa-save"></i> Guardar Cambios
                                    </button>
                                    <div id="branding-status" style="margin-top: 15px;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 5: CLIENTES Y PROYECTOS -->
                        <div class="tab-pane fade" id="admin-clientes" role="tabpanel">
                            <div class="form-section">
                                <h5 class="mb-4"><i class="fas fa-building"></i> Gestión de Clientes y Proyectos</h5>
                                
                                <!-- Nav tabs para subsecciones -->
                                <ul class="nav nav-tabs mb-4" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="clientes-list-tab" data-bs-toggle="tab" data-bs-target="#clientes-list" type="button" role="tab">
                                            <i class="fas fa-list"></i> Clientes
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="proyectos-list-tab" data-bs-toggle="tab" data-bs-target="#proyectos-list" type="button" role="tab">
                                            <i class="fas fa-project-diagram"></i> Proyectos
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="contactos-list-tab" data-bs-toggle="tab" data-bs-target="#contactos-list" type="button" role="tab">
                                            <i class="fas fa-users"></i> Contactos
                                        </button>
                                    </li>
                                </ul>

                                <!-- TAB CONTENT -->
                                <div class="tab-content">
                                    <!-- CLIENTES -->
                                    <div class="tab-pane fade show active" id="clientes-list" role="tabpanel">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="mb-0">Lista de Clientes</h6>
                                            <button class="btn btn-success btn-sm" onclick="abrirFormularioCliente()">
                                                <i class="fas fa-plus"></i> Nuevo Cliente
                                            </button>
                                        </div>
                                        <div id="clientes-table-container" style="font-size: 13px;"></div>
                                    </div>

                                    <!-- PROYECTOS -->
                                    <div class="tab-pane fade" id="proyectos-list" role="tabpanel">
                                        <div class="mb-3">
                                            <label class="form-label">Selecciona un Cliente:</label>
                                            <select class="form-control form-control-sm" id="cliente-filter" onchange="cargarProyectos()">
                                                <option value="">-- Selecciona cliente --</option>
                                            </select>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="mb-0">Proyectos del Cliente</h6>
                                            <button class="btn btn-success btn-sm" id="btn-nuevo-proyecto" style="display:none;" onclick="abrirFormularioProyecto()">
                                                <i class="fas fa-plus"></i> Nuevo Proyecto
                                            </button>
                                        </div>
                                        <div id="proyectos-table-container" style="font-size: 13px;"></div>
                                    </div>

                                    <!-- CONTACTOS -->
                                    <div class="tab-pane fade" id="contactos-list" role="tabpanel">
                                        <div class="mb-3">
                                            <label class="form-label">Selecciona un Cliente:</label>
                                            <select class="form-control form-control-sm" id="cliente-filter-contactos" onchange="cargarProyectosContactos()">
                                                <option value="">-- Selecciona cliente --</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Selecciona un Proyecto:</label>
                                            <select class="form-control form-control-sm" id="proyecto-filter" onchange="cargarContactos()">
                                                <option value="">-- Selecciona proyecto --</option>
                                            </select>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="mb-0">Contactos del Proyecto</h6>
                                            <button class="btn btn-success btn-sm" id="btn-nuevo-contacto" style="display:none;" onclick="abrirFormularioContacto()">
                                                <i class="fas fa-plus"></i> Nuevo Contacto
                                            </button>
                                        </div>
                                        <div id="contactos-table-container" style="font-size: 13px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showSection(sectionId, evt) {
            if (evt) evt.preventDefault();
            // Ocultar todas las secciones
            document.querySelectorAll('.page-section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Mostrar la sección seleccionada
            document.getElementById(sectionId).classList.add('active');
            
            // Actualizar nav-link activo
            document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                link.classList.remove('active');
            });
            if (evt && evt.target.closest('.nav-link')) {
                evt.target.closest('.nav-link').classList.add('active');
            }

            // Guardar la sección activa en sessionStorage
            sessionStorage.setItem('activeSection', sectionId);

            // Cargar clientes si es la sección de nueva intervención
            if (sectionId === 'nueva-intervencion') {
                cargarClientesIntervention();
            }
        }

        function resetForm(formId) {
            const form = document.getElementById(formId) || document.querySelector('form');
            if (form) form.reset();
        }

        function validarFormularioIntervencion() {
            const contactoId = document.getElementById('intervention-contacto').value;
            const responsableCliente = document.getElementById('responsable-cliente-input').value.trim();
            
            // Validar que al menos uno de los dos exista
            if (!contactoId && !responsableCliente) {
                alert('Debe seleccionar un contacto de la lista o ingresar un responsable del cliente manualmente.');
                return false;
            }
            
            return true;
        }

        function abrirPDFModal(interventionId) {
            const iframe = document.getElementById('pdf-iframe');
            iframe.src = 'view_pdf.php?id=' + interventionId;
            const modal = new bootstrap.Modal(document.getElementById('pdfModal'));
            modal.show();
        }

        function cerrarPDFModal() {
            const iframe = document.getElementById('pdf-iframe');
            iframe.src = '';
            const modal = bootstrap.Modal.getInstance(document.getElementById('pdfModal'));
            if (modal) modal.hide();
        }

        function deleteIntervention(id) {
            if (!confirm('¿Estás seguro de que quieres eliminar esta intervención? Esta acción no se puede deshacer.')) {
                return;
            }

            // Guardar la posición actual de scroll antes de eliminar
            sessionStorage.setItem('scrollPosition', window.scrollY);

            const formData = new FormData();
            formData.append('id', id);

            fetch('delete_intervention.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Intervención eliminada correctamente.');
                    sessionStorage.setItem('scrollPosition', window.scrollY);
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'No se pudo eliminar'));
                }
            })
            .catch(error => {
                alert('Error de conexión: ' + error);
            });
        }

        function limpiarBusqueda() {
            // Limpiar todos los campos del formulario de búsqueda
            document.querySelector('input[name="search_cliente"]').value = '';
            document.querySelector('input[name="search_fecha_inicio"]').value = '';
            document.querySelector('input[name="search_fecha_fin"]').value = '';
            document.querySelector('select[name="search_estado"]').value = '';
            
            // Redirigir a dashboard.php con parámetro para mantener sección consultas activa
            window.location.href = '?seccion=consultas';
        }

        function crearUsuario() {
            const username = document.getElementById('crear_username').value.trim();
            const password = document.getElementById('crear_password').value.trim();
            const nombre = document.getElementById('crear_nombre').value.trim();
            const email = document.getElementById('crear_email').value.trim();
            const tipo = document.getElementById('crear_tipo').value;

            const formData = new FormData();
            formData.append('username', username);
            formData.append('password', password);
            formData.append('nombre', nombre);
            formData.append('email', email);
            formData.append('tipo', tipo);

            fetch('manage_users.php?action=create', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Usuario creado correctamente.');
                    sessionStorage.setItem('scrollPosition', window.scrollY);
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'No se pudo crear'));
                }
            })
            .catch(e => alert('Error: ' + e));
        }

        function editarUsuario(id) {
            const nombre = prompt('Ingresa el nuevo nombre:', '');
            if (!nombre) return;

            const email = prompt('Ingresa el nuevo email:', '');
            if (!email) return;

            const tipo = confirm('¿Hacer admin? (Aceptar = Admin, Cancelar = Trabajador)') ? 'admin' : 'trabajador';

            const formData = new FormData();
            formData.append('id', id);
            formData.append('nombre', nombre);
            formData.append('email', email);
            formData.append('tipo', tipo);
            formData.append('activo', 1);

            fetch('manage_users.php?action=edit', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Usuario actualizado correctamente.');
                    sessionStorage.setItem('scrollPosition', window.scrollY);
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'No se pudo editar'));
                }
            })
            .catch(e => alert('Error: ' + e));
        }

        function resetearContraseña(id, nombre) {
            if (!confirm(`¿Resetear contraseña para ${nombre}?`)) return;

            const newPassword = prompt('Ingresa la nueva contraseña:');
            if (!newPassword || newPassword.length < 6) {
                alert('La contraseña debe tener al menos 6 caracteres.');
                return;
            }

            const formData = new FormData();
            formData.append('id', id);
            formData.append('password', newPassword);

            fetch('manage_users.php?action=reset_password', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Contraseña reseteada correctamente.');
                    sessionStorage.setItem('scrollPosition', window.scrollY);
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'No se pudo resetear'));
                }
            })
            .catch(e => alert('Error: ' + e));
        }

        function toggleUsuarioEstado(id, nombre, nuevoEstado) {
            const accion = nuevoEstado ? 'activar' : 'desactivar';
            const mensaje = nuevoEstado 
                ? `¿Activar la cuenta de ${nombre}? Podrá acceder nuevamente al sistema.`
                : `¿Desactivar la cuenta de ${nombre}? No podrá acceder al sistema.`;
            
            if (!confirm(mensaje)) return;

            const formData = new FormData();
            formData.append('id', id);
            formData.append('estado', nuevoEstado);

            fetch('manage_users.php?action=toggle_status', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert(`Usuario ${accion === 'activar' ? 'activado' : 'desactivado'} correctamente.`);
                    sessionStorage.setItem('scrollPosition', window.scrollY);
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || `No se pudo ${accion}`));
                }
            })
            .catch(e => alert('Error: ' + e));
        }

        function eliminarUsuario(id, nombre) {
            if (!confirm(`¡ADVERTENCIA! ¿Eliminar PERMANENTEMENTE al usuario "${nombre}"?\n\nEsta acción NO se puede deshacer y se borrarán todos sus datos.`)) return;
            if (!confirm(`¿Estás SEGURO de eliminar a "${nombre}"? Escribe OK para confirmar.`)) return;

            const formData = new FormData();
            formData.append('id', id);

            fetch('manage_users.php?action=delete_permanent', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Usuario eliminado permanentemente.');
                    sessionStorage.setItem('scrollPosition', window.scrollY);
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'No se pudo eliminar'));
                }
            })
            .catch(e => alert('Error: ' + e));
        }

        // Sincronizar color primario
        document.getElementById('color-primario')?.addEventListener('change', function() {
            document.getElementById('color-primario-hex').value = this.value;
        });

        // Sincronizar color secundario
        document.getElementById('color-secundario')?.addEventListener('change', function() {
            document.getElementById('color-secundario-hex').value = this.value;
        });

        // Manejar subida de logo
        document.getElementById('logo-input')?.addEventListener('change', function(e) {
            if (!this.files.length) return;

            const file = this.files[0];
            const maxSize = 5 * 1024 * 1024; // 5MB
            const allowed = ['image/svg+xml', 'image/png', 'image/jpeg', 'image/gif'];

            if (!allowed.includes(file.type)) {
                alert('Formato no permitido. Use SVG, PNG, JPG o GIF');
                return;
            }

            if (file.size > maxSize) {
                alert('El archivo es muy grande (máximo 5MB)');
                return;
            }

            const formData = new FormData();
            formData.append('logo', file);

            const statusDiv = document.getElementById('upload-status');
            statusDiv.innerHTML = '<span style="color: #0284C7;"><i class="fas fa-spinner fa-spin"></i> Subiendo...</span>';

            fetch('manage_branding.php?action=upload_logo', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    statusDiv.innerHTML = '<span style="color: #10b981;"><i class="fas fa-check-circle"></i> Logo subido correctamente</span>';
                    // Actualizar preview
                    const preview = document.getElementById('preview-logo');
                    const img = new Image();
                    img.onload = function() {
                        preview.innerHTML = '<img src="' + data.path + '?t=' + Date.now() + '" alt="Logo" style="max-width: 150px; max-height: 150px;">';
                    };
                    img.src = data.path + '?t=' + Date.now();
                } else {
                    statusDiv.innerHTML = '<span style="color: #dc3545;"><i class="fas fa-exclamation-circle"></i> ' + data.error + '</span>';
                }
            })
            .catch(e => {
                statusDiv.innerHTML = '<span style="color: #dc3545;"><i class="fas fa-exclamation-circle"></i> Error al subir</span>';
                console.error('Error:', e);
            });

            // Limpiar input
            this.value = '';
        });

        // Sincronizar color picker con campo de texto hex
        function sincronizarColorHex(tipo) {
            const colorPicker = document.getElementById('color-' + tipo);
            const hexInput = document.getElementById('color-' + tipo + '-hex');
            hexInput.value = colorPicker.value.toUpperCase();
        }

        // Sincronizar campo de texto hex con color picker
        function sincronizarHexColor(tipo) {
            const hexInput = document.getElementById('color-' + tipo + '-hex');
            const colorPicker = document.getElementById('color-' + tipo);
            let hex = hexInput.value.trim();
            
            // Agregar # si no lo tiene
            if (hex && !hex.startsWith('#')) {
                hex = '#' + hex;
                hexInput.value = hex;
            }
            
            // Validar formato hexadecimal
            const hexRegex = /^#[0-9A-Fa-f]{6}$/;
            if (hexRegex.test(hex)) {
                colorPicker.value = hex.toUpperCase();
                hexInput.style.borderColor = '';
            } else if (hex.length > 0) {
                hexInput.style.borderColor = '#dc3545';
            } else {
                hexInput.style.borderColor = '';
            }
        }

        function guardarBranding() {
            const nombre = document.getElementById('nombre-empresa').value.trim();
            if (!nombre) {
                alert('El nombre de la empresa es requerido');
                return;
            }

            const formData = new FormData();
            formData.append('nombre_empresa', nombre);
            formData.append('email_empresa', document.getElementById('email-empresa').value.trim());
            formData.append('telefono_empresa', document.getElementById('telefono-empresa').value.trim());
            formData.append('direccion_empresa', document.getElementById('direccion-empresa').value.trim());
            formData.append('color_primario', document.getElementById('color-primario').value);
            formData.append('color_secundario', document.getElementById('color-secundario').value);
            formData.append('mostrar_logo_pdf', document.getElementById('mostrar-logo-pdf').checked ? 1 : 0);
            formData.append('mostrar_firma_tecnico', document.getElementById('mostrar-firma-tecnico').checked ? 1 : 0);

            const statusDiv = document.getElementById('branding-status');
            statusDiv.innerHTML = '<span style="color: #0284C7;"><i class="fas fa-spinner fa-spin"></i> Guardando...</span>';

            fetch('manage_branding.php?action=update_config', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    statusDiv.innerHTML = '<div class="alert alert-success alert-sm"><i class="fas fa-check-circle"></i> ' + data.message + '</div>';
                    setTimeout(() => {
                        statusDiv.innerHTML = '';
                    }, 3000);
                } else {
                    statusDiv.innerHTML = '<div class="alert alert-danger alert-sm"><i class="fas fa-exclamation-circle"></i> ' + (data.error || 'Error al guardar') + '</div>';
                }
            })
            .catch(e => {
                statusDiv.innerHTML = '<div class="alert alert-danger alert-sm"><i class="fas fa-exclamation-circle"></i> Error de conexión</div>';
                console.error('Error:', e);
            });
        }

        // ==================== CLIENTES ====================
        function cargarClientes() {
            fetch('manage_clientes.php?action=list_clientes')
                .then(r => r.json())
                .then(data => {
                    const clientes = data.clientes || [];
                    let html = '<div class="table-responsive"><table class="table table-sm table-hover">';
                    html += '<thead><tr><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Contacto</th><th>Acciones</th></tr></thead>';
                    html += '<tbody>';
                    
                    clientes.forEach(c => {
                        html += `<tr>
                            <td>${c.nombre}</td>
                            <td>${c.email || '-'}</td>
                            <td>${c.telefono || '-'}</td>
                            <td>${c.contacto_principal || '-'}</td>
                            <td>
                                <button class="btn btn-xs btn-warning" onclick="editarCliente(${c.id}, '${c.nombre}')"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-xs btn-danger" onclick="eliminarCliente(${c.id}, '${c.nombre}')"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>`;
                    });
                    
                    html += '</tbody></table></div>';
                    document.getElementById('clientes-table-container').innerHTML = html;
                    
                    // Actualizar select de clientes en proyectos
                    let selectHtml = '<option value="">-- Selecciona cliente --</option>';
                    clientes.forEach(c => {
                        selectHtml += `<option value="${c.id}">${c.nombre}</option>`;
                    });
                    document.getElementById('cliente-filter').innerHTML = selectHtml;
                });
        }

        function abrirFormularioCliente() {
            const nombre = prompt('Ingresa el nombre del cliente:');
            if (!nombre) return;
            const email = prompt('Email (opcional):');
            const telefono = prompt('Teléfono (opcional):');
            const contacto = prompt('Contacto principal (opcional):');

            const formData = new FormData();
            formData.append('nombre', nombre);
            formData.append('email', email || '');
            formData.append('telefono', telefono || '');
            formData.append('contacto_principal', contacto || '');

            fetch('manage_clientes.php?action=create_cliente', {method: 'POST', body: formData})
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert('Cliente creado correctamente');
                        cargarClientes();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
        }

        function editarCliente(id, nombre) {
            const nuevoNombre = prompt('Nuevo nombre:', nombre);
            if (!nuevoNombre) return;
            const email = prompt('Email:');
            const telefono = prompt('Teléfono:');
            const contacto = prompt('Contacto principal:');

            const formData = new FormData();
            formData.append('id', id);
            formData.append('nombre', nuevoNombre);
            formData.append('email', email || '');
            formData.append('telefono', telefono || '');
            formData.append('contacto_principal', contacto || '');

            fetch('manage_clientes.php?action=update_cliente', {method: 'POST', body: formData})
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert('Cliente actualizado');
                        cargarClientes();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
        }

        function eliminarCliente(id, nombre) {
            if (!confirm(`¿Eliminar cliente "${nombre}"?`)) return;

            const formData = new FormData();
            formData.append('id', id);

            fetch('manage_clientes.php?action=delete_cliente', {method: 'POST', body: formData})
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert('Cliente eliminado');
                        cargarClientes();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
        }

        // ==================== PROYECTOS ====================
        function cargarProyectos() {
            const clienteId = document.getElementById('cliente-filter').value;
            if (!clienteId) {
                document.getElementById('proyectos-table-container').innerHTML = '<p class="text-muted">Selecciona un cliente</p>';
                document.getElementById('btn-nuevo-proyecto').style.display = 'none';
                return;
            }

            document.getElementById('btn-nuevo-proyecto').style.display = 'inline-block';

            fetch(`manage_clientes.php?action=list_proyectos&cliente_id=${clienteId}`)
                .then(r => r.json())
                .then(data => {
                    const proyectos = data.proyectos || [];
                    let html = '<div class="table-responsive"><table class="table table-sm table-hover">';
                    html += '<thead><tr><th>Nombre</th><th>Descripción</th><th>Estado</th><th>Acciones</th></tr></thead>';
                    html += '<tbody>';
                    
                    proyectos.forEach(p => {
                        const estadoBadge = p.estado === 'activo' ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">' + p.estado + '</span>';
                        html += `<tr>
                            <td>${p.nombre}</td>
                            <td>${p.descripcion || '-'}</td>
                            <td>${estadoBadge}</td>
                            <td>
                                <button class="btn btn-xs btn-warning" onclick="editarProyecto(${p.id}, '${p.nombre}')"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-xs btn-danger" onclick="eliminarProyecto(${p.id}, '${p.nombre}')"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>`;
                    });
                    
                    html += '</tbody></table></div>';
                    document.getElementById('proyectos-table-container').innerHTML = html;
                });
        }

        function abrirFormularioProyecto() {
            const clienteId = document.getElementById('cliente-filter').value;
            if (!clienteId) {
                alert('Selecciona un cliente primero');
                return;
            }

            const nombre = prompt('Ingresa el nombre del proyecto:');
            if (!nombre) return;
            const descripcion = prompt('Descripción (opcional):');

            const formData = new FormData();
            formData.append('cliente_id', clienteId);
            formData.append('nombre', nombre);
            formData.append('descripcion', descripcion || '');

            fetch('manage_clientes.php?action=create_proyecto', {method: 'POST', body: formData})
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert('Proyecto creado correctamente');
                        cargarProyectos();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
        }

        function editarProyecto(id, nombre) {
            const nuevoNombre = prompt('Nuevo nombre:', nombre);
            if (!nuevoNombre) return;
            const descripcion = prompt('Descripción:');
            const estado = confirm('¿Está activo? (OK=Activo, Cancelar=Inactivo)') ? 'activo' : 'inactivo';

            const formData = new FormData();
            formData.append('id', id);
            formData.append('nombre', nuevoNombre);
            formData.append('descripcion', descripcion || '');
            formData.append('estado', estado);

            fetch('manage_clientes.php?action=update_proyecto', {method: 'POST', body: formData})
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert('Proyecto actualizado');
                        cargarProyectos();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
        }

        function eliminarProyecto(id, nombre) {
            if (!confirm(`¿Eliminar proyecto "${nombre}"?`)) return;

            const formData = new FormData();
            formData.append('id', id);

            fetch('manage_clientes.php?action=delete_proyecto', {method: 'POST', body: formData})
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert('Proyecto eliminado');
                        cargarProyectos();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
        }

        // ==================== CONTACTOS ====================
        function cargarProyectosContactos() {
            const clienteId = document.getElementById('cliente-filter-contactos').value;
            const proyectoSelect = document.getElementById('proyecto-filter');
            
            proyectoSelect.innerHTML = '<option value="">-- Selecciona proyecto --</option>';
            document.getElementById('contactos-table-container').innerHTML = '<p class="text-muted">Selecciona un proyecto</p>';
            document.getElementById('btn-nuevo-contacto').style.display = 'none';
            
            if (!clienteId) return;

            fetch(`manage_clientes.php?action=list_proyectos&cliente_id=${clienteId}`)
                .then(r => r.json())
                .then(data => {
                    if (data.proyectos && data.proyectos.length > 0) {
                        data.proyectos.forEach(p => {
                            const option = document.createElement('option');
                            option.value = p.id;
                            option.textContent = p.nombre;
                            proyectoSelect.appendChild(option);
                        });
                    }
                })
                .catch(e => console.error('Error cargando proyectos:', e));
        }

        function cargarContactos() {
            const proyectoId = document.getElementById('proyecto-filter').value;
            if (!proyectoId) {
                document.getElementById('contactos-table-container').innerHTML = '<p class="text-muted">Selecciona un proyecto</p>';
                document.getElementById('btn-nuevo-contacto').style.display = 'none';
                return;
            }

            document.getElementById('btn-nuevo-contacto').style.display = 'inline-block';

            fetch(`manage_clientes.php?action=list_contactos&proyecto_id=${proyectoId}`)
                .then(r => r.json())
                .then(data => {
                    const contactos = data.contactos || [];
                    let html = '<div class="table-responsive"><table class="table table-sm table-hover">';
                    html += '<thead><tr><th>Nombre</th><th>Cargo</th><th>Email</th><th>Teléfono</th><th>Acciones</th></tr></thead>';
                    html += '<tbody>';
                    
                    contactos.forEach(c => {
                        html += `<tr>
                            <td>${c.nombre}</td>
                            <td>${c.cargo || '-'}</td>
                            <td>${c.email || '-'}</td>
                            <td>${c.telefono || '-'}</td>
                            <td>
                                <button class="btn btn-xs btn-warning" onclick="editarContacto(${c.id}, '${c.nombre}')"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-xs btn-danger" onclick="eliminarContacto(${c.id}, '${c.nombre}')"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>`;
                    });
                    
                    html += '</tbody></table></div>';
                    document.getElementById('contactos-table-container').innerHTML = html;
                });
        }

        function abrirFormularioContacto() {
            const proyectoId = document.getElementById('proyecto-filter').value;
            if (!proyectoId) {
                alert('Selecciona un proyecto primero');
                return;
            }

            const nombre = prompt('Nombre del contacto:');
            if (!nombre) return;
            const cargo = prompt('Cargo (opcional):');
            const email = prompt('Email (opcional):');
            const telefono = prompt('Teléfono (opcional):');

            const formData = new FormData();
            formData.append('proyecto_id', proyectoId);
            formData.append('nombre', nombre);
            formData.append('cargo', cargo || '');
            formData.append('email', email || '');
            formData.append('telefono', telefono || '');

            fetch('manage_clientes.php?action=create_contacto', {method: 'POST', body: formData})
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert('Contacto creado correctamente');
                        cargarContactos();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
        }

        function editarContacto(id, nombre) {
            const nuevoNombre = prompt('Nuevo nombre:', nombre);
            if (!nuevoNombre) return;
            const cargo = prompt('Cargo:');
            const email = prompt('Email:');
            const telefono = prompt('Teléfono:');

            const formData = new FormData();
            formData.append('id', id);
            formData.append('nombre', nuevoNombre);
            formData.append('cargo', cargo || '');
            formData.append('email', email || '');
            formData.append('telefono', telefono || '');

            fetch('manage_clientes.php?action=update_contacto', {method: 'POST', body: formData})
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert('Contacto actualizado');
                        cargarContactos();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
        }

        function eliminarContacto(id, nombre) {
            if (!confirm(`¿Eliminar contacto "${nombre}"?`)) return;

            const formData = new FormData();
            formData.append('id', id);

            fetch('manage_clientes.php?action=delete_contacto', {method: 'POST', body: formData})
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert('Contacto eliminado');
                        cargarContactos();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
        }

        // Cargar clientes al abrir la pestaña
        document.addEventListener('shown.bs.tab', function (e) {
            if (e.target.id === 'admin-clientes-tab') {
                cargarClientes();
            }
        });

        function abrirEditarIntervención(id) {
            // Obtener los datos de la intervención (usando fetch)
            fetch('get_intervention.php?id=' + id)
                .then(r => r.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }
                    mostrarModalEditarIntervención(data);
                })
                .catch(e => alert('Error: ' + e));
        }

        function mostrarModalEditarIntervención(intervention) {
            // Llenar el modal con los datos
            document.getElementById('edit_id').value = intervention.id;
            document.getElementById('edit_fecha').value = intervention.fecha;
            document.getElementById('edit_hora').value = intervention.hora || '';
            document.getElementById('edit_cliente_id').value = intervention.cliente_id || '';
            document.getElementById('edit_proyecto_id').value = intervention.proyecto_id || '';
            document.getElementById('edit_contacto_id').value = intervention.contacto_id || '';
            document.getElementById('edit_descripcion').value = intervention.descripcion;
            document.getElementById('edit_responsable_trabajador').value = intervention.responsable_trabajador;
            document.getElementById('edit_responsable_cliente').value = intervention.responsable_cliente || '';
            document.getElementById('edit_horas_ocupadas').value = intervention.horas_ocupadas;
            document.getElementById('edit_notas_adicionales').value = intervention.notas_adicionales || '';

            // Cargar clientes y setear el seleccionado
            cargarClientesParaEditar(() => {
                if (intervention.cliente_id) {
                    document.getElementById('edit_cliente_id').value = intervention.cliente_id;
                    cargarProyectosEditIntervention(() => {
                        if (intervention.proyecto_id) {
                            document.getElementById('edit_proyecto_id').value = intervention.proyecto_id;
                            cargarContactosEditIntervention();
                        }
                    });
                }
            });

            // Mostrar el modal
            const modal = new bootstrap.Modal(document.getElementById('editarIntervenciónModal'));
            modal.show();
        }

        function guardarEdiciónIntervención() {
            const id = document.getElementById('edit_id').value;
            const contactoId = document.getElementById('edit_contacto_id').value;
            const responsableCliente = document.getElementById('edit_responsable_cliente').value.trim();
            
            // Validar que al menos uno de los dos exista
            if (!contactoId && !responsableCliente) {
                alert('Debe seleccionar un contacto de la lista o ingresar un responsable del cliente manualmente.');
                return;
            }
            
            const formData = new FormData();
            formData.append('id', id);
            formData.append('fecha', document.getElementById('edit_fecha').value);
            formData.append('hora', document.getElementById('edit_hora').value || '');
            formData.append('cliente_id', document.getElementById('edit_cliente_id').value);
            formData.append('proyecto_id', document.getElementById('edit_proyecto_id').value);
            formData.append('contacto_id', contactoId);
            formData.append('descripcion', document.getElementById('edit_descripcion').value);
            formData.append('responsable_trabajador', document.getElementById('edit_responsable_trabajador').value);
            formData.append('responsable_cliente', responsableCliente);
            formData.append('horas_ocupadas', document.getElementById('edit_horas_ocupadas').value);
            formData.append('notas_adicionales', document.getElementById('edit_notas_adicionales').value);

            fetch('edit_intervention.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Intervención actualizada correctamente.');
                    sessionStorage.setItem('scrollPosition', window.scrollY);
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'No se pudo editar'));
                }
            })
            .catch(e => alert('Error: ' + e));
        }

        // === FUNCIONES PARA CARGAR DINÁMICAMENTE LOS SELECTS EN INTERVENCIÓN ===

        // Cargar clientes al abrir la sección de nueva intervención
        function cargarClientesIntervention() {
            const select = document.getElementById('intervention-cliente');
            const selectProyecto = document.getElementById('intervention-proyecto');
            const selectContacto = document.getElementById('intervention-contacto');
            
            fetch('manage_clientes.php?action=list_clientes')
                .then(r => r.json())
                .then(data => {
                    select.innerHTML = '<option value="">-- Selecciona cliente --</option>';
                    selectProyecto.innerHTML = '<option value="">-- Selecciona proyecto --</option>';
                    selectContacto.innerHTML = '<option value="">-- Selecciona contacto --</option>';
                    
                    if (data.clientes && data.clientes.length > 0) {
                        data.clientes.forEach(cliente => {
                            const option = document.createElement('option');
                            option.value = cliente.id;
                            option.textContent = cliente.nombre;
                            select.appendChild(option);
                        });
                    }
                })
                .catch(e => console.error('Error cargando clientes:', e));
        }

        // Cargar proyectos para cliente seleccionado (formulario nueva intervención)
        function cargarProyectosIntervention() {
            const clienteId = document.getElementById('intervention-cliente').value;
            const select = document.getElementById('intervention-proyecto');
            const selectContacto = document.getElementById('intervention-contacto');
            
            select.innerHTML = '<option value="">-- Selecciona proyecto --</option>';
            selectContacto.innerHTML = '<option value="">-- Selecciona contacto --</option>';
            
            if (!clienteId) return;
            
            fetch('manage_clientes.php?action=list_proyectos&cliente_id=' + clienteId)
                .then(r => r.json())
                .then(data => {
                    if (data.proyectos && data.proyectos.length > 0) {
                        data.proyectos.forEach(proyecto => {
                            const option = document.createElement('option');
                            option.value = proyecto.id;
                            option.textContent = proyecto.nombre;
                            select.appendChild(option);
                        });
                    }
                })
                .catch(e => console.error('Error cargando proyectos:', e));
        }

        // Cargar contactos para proyecto seleccionado (formulario nueva intervención)
        function cargarContactosIntervention() {
            const proyectoId = document.getElementById('intervention-proyecto').value;
            const select = document.getElementById('intervention-contacto');
            
            select.innerHTML = '<option value="">-- Selecciona contacto --</option>';
            
            if (!proyectoId) return;
            
            fetch('manage_clientes.php?action=list_contactos&proyecto_id=' + proyectoId)
                .then(r => r.json())
                .then(data => {
                    if (data.contactos && data.contactos.length > 0) {
                        data.contactos.forEach(contacto => {
                            const option = document.createElement('option');
                            option.value = contacto.id;
                            option.textContent = contacto.nombre;
                            select.appendChild(option);
                        });
                    }
                })
                .catch(e => console.error('Error cargando contactos:', e));
        }

        // Cargar clientes para edición (con callback)
        function cargarClientesParaEditar(callback) {
            const select = document.getElementById('edit_cliente_id');
            
            fetch('manage_clientes.php?action=list_clientes')
                .then(r => r.json())
                .then(data => {
                    select.innerHTML = '<option value="">-- Selecciona cliente --</option>';
                    
                    if (data.clientes && data.clientes.length > 0) {
                        data.clientes.forEach(cliente => {
                            const option = document.createElement('option');
                            option.value = cliente.id;
                            option.textContent = cliente.nombre;
                            select.appendChild(option);
                        });
                    }
                    if (callback) callback();
                })
                .catch(e => console.error('Error cargando clientes:', e));
        }

        // Cargar proyectos para cliente seleccionado (edición)
        function cargarProyectosEditIntervention(callback) {
            const clienteId = document.getElementById('edit_cliente_id').value;
            const select = document.getElementById('edit_proyecto_id');
            const selectContacto = document.getElementById('edit_contacto_id');
            
            select.innerHTML = '<option value="">-- Selecciona proyecto --</option>';
            selectContacto.innerHTML = '<option value="">-- Selecciona contacto --</option>';
            
            if (!clienteId) return;
            
            fetch('manage_clientes.php?action=list_proyectos&cliente_id=' + clienteId)
                .then(r => r.json())
                .then(data => {
                    if (data.proyectos && data.proyectos.length > 0) {
                        data.proyectos.forEach(proyecto => {
                            const option = document.createElement('option');
                            option.value = proyecto.id;
                            option.textContent = proyecto.nombre;
                            select.appendChild(option);
                        });
                    }
                    if (callback) callback();
                })
                .catch(e => console.error('Error cargando proyectos:', e));
        }

        // Cargar contactos para proyecto seleccionado (edición)
        function cargarContactosEditIntervention() {
            const proyectoId = document.getElementById('edit_proyecto_id').value;
            const select = document.getElementById('edit_contacto_id');
            
            select.innerHTML = '<option value="">-- Selecciona contacto --</option>';
            
            if (!proyectoId) return;
            
            fetch('manage_clientes.php?action=list_contactos&proyecto_id=' + proyectoId)
                .then(r => r.json())
                .then(data => {
                    if (data.contactos && data.contactos.length > 0) {
                        data.contactos.forEach(contacto => {
                            const option = document.createElement('option');
                            option.value = contacto.id;
                            option.textContent = contacto.nombre;
                            select.appendChild(option);
                        });
                    }
                })
                .catch(e => console.error('Error cargando contactos:', e));
        }

        // Restaurar posición de scroll y sección activa al cargar la página
        window.addEventListener('load', function() {
            // Restaurar scroll
            const scrollPosition = sessionStorage.getItem('scrollPosition');
            if (scrollPosition) {
                window.scrollTo(0, parseInt(scrollPosition));
                sessionStorage.removeItem('scrollPosition');
            }

            // Restaurar sección activa
            const activeSection = sessionStorage.getItem('activeSection');
            if (activeSection) {
                // Ocultar todas las secciones
                document.querySelectorAll('.page-section').forEach(section => {
                    section.classList.remove('active');
                });
                
                // Mostrar la sección guardada
                const section = document.getElementById(activeSection);
                if (section) {
                    section.classList.add('active');
                    
                    // Actualizar nav-link activo
                    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                        link.classList.remove('active');
                        if (link.onclick && link.onclick.toString().includes(activeSection)) {
                            link.classList.add('active');
                        }
                    });
                }
                
                // No remover activeSection para que persista durante la sesión
            }

            // Restaurar tab activa de Bootstrap (si está en admin-panel)
            const activeTab = sessionStorage.getItem('activeTab');
            if (activeTab) {
                const tabElement = document.querySelector(`[data-bs-target="${activeTab}"]`);
                if (tabElement) {
                    const tab = new bootstrap.Tab(tabElement);
                    tab.show();
                }
                sessionStorage.removeItem('activeTab');
            }
        });

        // Guardar tab activa cuando se cambia
        document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(e) {
                sessionStorage.setItem('activeTab', e.target.getAttribute('data-bs-target'));
            });
        });

        // Event listeners específicos para las pestañas del Admin
        const clientesListTab = document.getElementById('clientes-list-tab');
        const proyectosListTab = document.getElementById('proyectos-list-tab');
        const contactosListTab = document.getElementById('contactos-list-tab');

        if (clientesListTab) {
            clientesListTab.addEventListener('shown.bs.tab', function() {
                cargarClientes();
            });
        }

        if (proyectosListTab) {
            proyectosListTab.addEventListener('shown.bs.tab', function() {
                // Cargar clientes en el select de proyectos si está vacío
                const selectClientes = document.getElementById('cliente-filter');
                if (!selectClientes || selectClientes.options.length <= 1) {
                    fetch('manage_clientes.php?action=list_clientes')
                        .then(r => r.json())
                        .then(data => {
                            let selectHtml = '<option value="">-- Selecciona cliente --</option>';
                            if (data && data.clientes && data.clientes.length > 0) {
                                data.clientes.forEach(c => {
                                    selectHtml += `<option value="${c.id}">${c.nombre}</option>`;
                                });
                            }
                            if (selectClientes) selectClientes.innerHTML = selectHtml;
                        })
                        .catch(e => console.error('Error cargando clientes:', e));
                }
            });
        }

        if (contactosListTab) {
            contactosListTab.addEventListener('shown.bs.tab', function() {
                // Cargar clientes en el select de contactos
                fetch('manage_clientes.php?action=list_clientes')
                    .then(r => r.json())
                    .then(data => {
                        let selectHtml = '<option value="">-- Selecciona cliente --</option>';
                        if (data && data.clientes && data.clientes.length > 0) {
                            data.clientes.forEach(c => {
                                selectHtml += `<option value="${c.id}">${c.nombre}</option>`;
                            });
                        }
                        const selectContactos = document.getElementById('cliente-filter-contactos');
                        if (selectContactos) selectContactos.innerHTML = selectHtml;
                    })
                    .catch(e => console.error('Error cargando clientes:', e));
            });
        }
    </script>

    <!-- Modal: Crear Usuario -->
    <div class="modal fade" id="crearUsuarioModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crear Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form onsubmit="crearUsuario(); return false;">
                        <div class="mb-3">
                            <label class="form-label">Usuario (Login)</label>
                            <input type="text" id="crear_username" class="form-control" required minlength="3">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" id="crear_password" class="form-control" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo</label>
                            <input type="text" id="crear_nombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" id="crear_email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo de Usuario</label>
                            <select id="crear_tipo" class="form-select">
                                <option value="trabajador">Trabajador</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Crear Usuario</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Ver PDF -->
    <div class="modal fade" id="pdfModal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-pdf"></i> Hoja de Servicio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="cerrarPDFModal()"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe id="pdf-iframe" style="width: 100%; height: calc(100vh - 60px); border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Editar Intervención -->
    <div class="modal fade" id="editarIntervenciónModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Intervención</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form onsubmit="guardarEdiciónIntervención(); return false;">
                        <input type="hidden" id="edit_id">
                        
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Fecha</label>
                                <input type="date" id="edit_fecha" class="form-control" required>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Hora <small class="text-muted">(opcional)</small></label>
                                <input type="time" id="edit_hora" class="form-control">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Cliente</label>
                                <select id="edit_cliente_id" class="form-control" onchange="cargarProyectosEditIntervention()" required>
                                    <option value="">-- Selecciona cliente --</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Proyecto</label>
                                <select id="edit_proyecto_id" class="form-control" onchange="cargarContactosEditIntervention()" required>
                                    <option value="">-- Selecciona proyecto --</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Contacto</label>
                                <select id="edit_contacto_id" class="form-control">
                                    <option value="">-- Selecciona contacto --</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Horas ocupadas</label>
                                <input type="number" id="edit_horas_ocupadas" class="form-control" min="0.5" max="24" step="0.5" required>
                            </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Responsable (trabajador)</label>
                                <input type="text" id="edit_responsable_trabajador" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Responsable (cliente) <small class="text-muted">(o seleccione contacto)</small></label>
                                <input type="text" id="edit_responsable_cliente" class="form-control">
                                <small class="form-text text-muted">Si no completa este campo, se usará el contacto seleccionado</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea id="edit_descripcion" class="form-control" rows="4" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notas adicionales</label>
                            <textarea id="edit_notas_adicionales" class="form-control" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarEdiciónIntervención()">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
