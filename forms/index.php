<?php
session_start();

// Incluir configuración
require_once 'config/database.php';
require_once 'php/auth.php';

$error = '';
$show_demo = false;

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Por favor complete todos los campos';
    } else {
        if (login($username, $password, $pdo)) {
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos';
            $show_demo = true;
        }
    }
}

// Mostrar mensaje si fue redirigido
$redirect = $_GET['redirect'] ?? '';
if ($redirect && !$error) {
    $show_demo = true;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Hojas de Servicio - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0284C7;
            --secondary-color: #14B8A6;
            --danger-color: #EF4444;
            --success-color: #10B981;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        
        .login-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
            margin: 20px;
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .login-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .login-header p {
            font-size: 14px;
            opacity: 0.9;
            margin: 0;
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #1F2937;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #E5E7EB;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
        }
        
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(2, 132, 199, 0.3);
        }
        
        .alert {
            margin-bottom: 20px;
            border-radius: 6px;
            border: none;
            padding: 15px;
            font-size: 14px;
        }
        
        .alert-danger {
            background-color: #FEE2E2;
            color: #991B1B;
        }
        
        .alert-info {
            background-color: #DBEAFE;
            color: #1E40AF;
        }
        
        .demo-info {
            background-color: #F0F9FF;
            border-left: 4px solid var(--primary-color);
            padding: 15px;
            border-radius: 6px;
            margin-top: 25px;
            font-size: 13px;
            color: #064E78;
        }
        
        .demo-info h5 {
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .demo-info p {
            margin: 5px 0;
            display: flex;
            align-items: center;
        }
        
        .demo-info strong {
            display: inline-block;
            min-width: 80px;
        }
        
        .icon-lock {
            font-size: 48px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 480px) {
            .login-container {
                margin: 10px;
            }
            
            .login-header {
                padding: 30px 20px;
            }
            
            .login-header h1 {
                font-size: 24px;
            }
            
            .login-body {
                padding: 25px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="icon-lock">
                <i class="fas fa-lock"></i>
            </div>
            <h1>Sistema de Hojas de Servicio</h1>
            <p>Acceso de Trabajadores</p>
        </div>
        
        <div class="login-body">
            <?php
            if ($error) {
                echo '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ' . htmlspecialchars($error) . '</div>';
            }
            ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i> Usuario
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="Ingresa tu usuario"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-key"></i> Contraseña
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Ingresa tu contraseña"
                        required
                    >
                </div>
                
                <button type="submit" name="login" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Ingresar
                </button>
            </form>
            
            <?php if ($show_demo): ?>
            <div class="demo-info">
                <h5><i class="fas fa-info-circle"></i> Credenciales de Prueba</h5>
                <p>
                    <strong>Usuario:</strong> admin<br>
                    <strong>Contraseña:</strong> admin123
                </p>
                <p style="margin-top: 10px; border-top: 1px solid #BFDBFE; padding-top: 10px;">
                    <strong>Usuario:</strong> juan<br>
                    <strong>Contraseña:</strong> juan123
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
