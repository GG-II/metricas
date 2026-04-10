<?php
/**
 * Página de login
 */

require_once 'config.php';

// Si ya está logueado, redirigir al dashboard
if (isLoggedIn()) {
    redirect('/index.php');
}

// Procesar el formulario de login
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Por favor, completa todos los campos.';
    } else {
        try {
            $db = getDB();
            
            // Buscar usuario
            $stmt = $db->prepare("
                SELECT id, username, password, nombre, email, rol, activo 
                FROM usuarios 
                WHERE username = ? AND activo = 1
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && verifyPassword($password, $user['password'])) {
                // Login exitoso
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_name'] = $user['nombre'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['rol'];
                
                // Actualizar último acceso
                $stmt = $db->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);
                
                // Registrar actividad
                logActivity('login');
                
                // Redirigir al dashboard
                redirect('/index.php');
            } else {
                $error = 'Usuario o contraseña incorrectos.';
                
                // Log de intento fallido
                error_log("Failed login attempt for username: $username from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'Error al procesar el login. Por favor, intenta nuevamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }
        .login-card {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        .logo {
            width: 60px;
            height: 60px;
            background: #3b82f6;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            color: white;
            margin: 0 auto 20px;
        }
        .login-title {
            color: white;
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
            font-weight: 600;
        }
        .form-label {
            color: #94a3b8;
            margin-bottom: 8px;
        }
        .form-control {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
        }
        .form-control:focus {
            background: rgba(15, 23, 42, 0.7);
            border-color: #3b82f6;
            color: white;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .btn-login {
            background: #3b82f6;
            border: none;
            padding: 12px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }
        .alert {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .version {
            text-align: center;
            color: #64748b;
            margin-top: 20px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">IT</div>
            <h1 class="login-title"><?php echo APP_NAME; ?></h1>
            
            <?php if ($error): ?>
                <div class="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Usuario</label>
                    <input type="text" name="username" class="form-control" 
                           placeholder="Ingresa tu usuario" 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                           required autofocus>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" 
                           placeholder="Ingresa tu contraseña" 
                           required>
                </div>
                
                <button type="submit" class="btn-login">
                    Iniciar Sesión
                </button>
            </form>
            
            <div class="version">
                Versión <?php echo APP_VERSION; ?>
            </div>
        </div>
    </div>
</body>
</html>