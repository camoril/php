<?php
/**
 * manage_branding.php
 * Gestionar configuración de branding del sistema
 */

// Forzar respuesta JSON
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
require_once 'php/auth.php';

requireAuth();

// Solo admin puede cambiar branding
if (!isAdmin()) {
    http_response_code(403);
    die(json_encode(['error' => 'No tienes permiso para acceder']));
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    if ($method === 'GET' && $action === 'get_config') {
        // Obtener configuración actual
        $stmt = $pdo->query("SELECT * FROM configuracion_branding WHERE id = 1");
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        die(json_encode($config ?: ['error' => 'Configuración no encontrada']));

    } elseif ($method === 'POST' && $action === 'update_config') {
        // Actualizar configuración de branding
        $nombre_empresa = trim($_POST['nombre_empresa'] ?? '');
        $email_empresa = trim($_POST['email_empresa'] ?? '');
        $telefono_empresa = trim($_POST['telefono_empresa'] ?? '');
        $direccion_empresa = trim($_POST['direccion_empresa'] ?? '');
        $color_primario = trim($_POST['color_primario'] ?? '#0284C7');
        $color_secundario = trim($_POST['color_secundario'] ?? '#0EA5E9');
        $mostrar_logo_pdf = isset($_POST['mostrar_logo_pdf']) ? 1 : 0;
        $mostrar_firma_tecnico = isset($_POST['mostrar_firma_tecnico']) ? 1 : 0;

        if (!$nombre_empresa) {
            die(json_encode(['error' => 'El nombre de la empresa es requerido']));
        }

        $stmt = $pdo->prepare("
            UPDATE configuracion_branding 
            SET nombre_empresa = ?, email_empresa = ?, telefono_empresa = ?, 
                direccion_empresa = ?, color_primario = ?, color_secundario = ?,
                mostrar_logo_pdf = ?, mostrar_firma_tecnico = ?
            WHERE id = 1
        ");

        if ($stmt->execute([$nombre_empresa, $email_empresa, $telefono_empresa, $direccion_empresa, 
                           $color_primario, $color_secundario, $mostrar_logo_pdf, $mostrar_firma_tecnico])) {
            die(json_encode(['success' => true, 'message' => 'Configuración actualizada correctamente']));
        } else {
            die(json_encode(['error' => 'Error al actualizar la configuración']));
        }

    } elseif ($method === 'POST' && $action === 'upload_logo') {
        // Subir logo
        if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
            die(json_encode(['error' => 'Error al subir el archivo']));
        }

        $file = $_FILES['logo'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $allowed_ext = ['svg', 'png', 'jpg', 'jpeg', 'gif'];

        if (!in_array(strtolower($ext), $allowed_ext)) {
            die(json_encode(['error' => 'Formato de archivo no permitido. Use: ' . implode(', ', $allowed_ext)]));
        }

        if ($file['size'] > 5 * 1024 * 1024) { // 5MB
            die(json_encode(['error' => 'El archivo es muy grande (máximo 5MB)']));
        }

        // Obtener logo anterior ANTES de crear el nuevo
        $stmt = $pdo->query("SELECT logo_path FROM configuracion_branding WHERE id = 1");
        $old_logo = $stmt->fetchColumn();
        $old_logo_path = null;
        
        if ($old_logo && file_exists(__DIR__ . '/' . $old_logo) && strpos($old_logo, 'logo_') !== false) {
            $old_logo_path = __DIR__ . '/' . $old_logo;
        }

        // Crear nombre único para el nuevo archivo (usar microsegundos)
        $filename = 'logo_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
        $filepath = 'assets/logos/' . $filename;
        $fullpath = __DIR__ . '/' . $filepath;

        // Verificar que la carpeta existe
        $logoDir = dirname($fullpath);
        if (!is_dir($logoDir)) {
            mkdir($logoDir, 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $fullpath)) {
            // Ahora eliminar el logo anterior si es diferente
            if ($old_logo_path && $old_logo_path !== $fullpath) {
                @unlink($old_logo_path);
            }

            // Actualizar path en la base de datos
            $stmt = $pdo->prepare("UPDATE configuracion_branding SET logo_path = ? WHERE id = 1");
            $stmt->execute([$filepath]);

            die(json_encode(['success' => true, 'message' => 'Logo subido correctamente', 'path' => $filepath]));
        } else {
            die(json_encode(['error' => 'Error al guardar el archivo. Verifica permisos de la carpeta.']));
        }

    } else {
        die(json_encode(['error' => 'Acción no válida']));
    }

} catch (Exception $e) {
    die(json_encode(['error' => 'Error del servidor: ' . $e->getMessage()]));
}
?>
