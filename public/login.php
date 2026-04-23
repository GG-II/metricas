<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Models\Usuario;

// Cargar funciones (incluye CsrfProtection)
require_once __DIR__ . '/../config.php';

// Si ya está logueado, redirigir al dashboard
AuthMiddleware::guest();

$error = '';
$success = '';

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Por favor completa todos los campos';
    } else {
        $usuarioModel = new Usuario();
        $user = $usuarioModel->authenticate($username, $password);

        if ($user) {
            // Login exitoso
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['rol'];
            $_SESSION['user_name'] = $user['nombre'];

            // ✅ SEGURIDAD: Regenerar token CSRF después de login
            // Previene ataques de session fixation
            CsrfProtection::regenerateToken();

            redirect('/public/index.php');
        } else {
            $error = 'Usuario o contraseña incorrectos';
        }
    }
}

$pageTitle = 'Iniciar Sesión - Sistema de Métricas';
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>

    <!-- Tabler CSS -->
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?php echo baseUrl('/public/assets/css/custom.css'); ?>" rel="stylesheet">
</head>
<body class="login-container">
    <div class="container container-tight py-4">
        <div class="card card-md login-card">
            <div class="login-header">
                <div class="login-brand-icon">
                    <i class="ti ti-chart-bar"></i>
                </div>
                <h2 class="mb-1">Sistema de Métricas</h2>
                <p class="mb-0 opacity-75">Multi-Departamento</p>
            </div>

            <div class="card-body p-4">
                <h3 class="card-title text-center mb-4">Iniciar Sesión</h3>

                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-alert-circle me-2"></i>
                        <div><?php echo e($error); ?></div>
                    </div>
                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
                <?php endif; ?>

                <form method="POST" autocomplete="off">
                    <div class="mb-3">
                        <label class="form-label">Usuario</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="ti ti-user"></i>
                            </span>
                            <input type="text" name="username" class="form-control" placeholder="Ingresa tu usuario" autocomplete="off" required autofocus value="<?php echo e($_POST['username'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="ti ti-lock"></i>
                            </span>
                            <input type="password" name="password" class="form-control" placeholder="Ingresa tu contraseña" autocomplete="off" required>
                        </div>
                    </div>
                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-login me-2"></i>
                            Iniciar Sesión
                        </button>
                    </div>
                </form>

                <hr class="my-4">

                <div class="text-center">
                    <h4 class="mb-3">Usuarios de Prueba</h4>
                    <div class="row g-2">
                        <div class="col-12">
                            <div class="d-flex align-items-center p-2 border rounded">
                                <span class="avatar avatar-sm me-3" style="background-color: #ef4444;">SA</span>
                                <div class="flex-fill text-start">
                                    <div class="fw-bold"><code>superadmin</code></div>
                                    <div class="text-muted small">Super Admin - Ve TODO</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center p-2 border rounded">
                                <span class="avatar avatar-sm me-3" style="background-color: #f59e0b;">AT</span>
                                <div class="flex-fill text-start">
                                    <div class="fw-bold"><code>admin_ti</code></div>
                                    <div class="text-muted small">Admin TI Corporativo</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center p-2 border rounded">
                                <span class="avatar avatar-sm me-3" style="background-color: #3b82f6;">VS</span>
                                <div class="flex-fill text-start">
                                    <div class="fw-bold"><code>viewer_software</code></div>
                                    <div class="text-muted small">Viewer - Solo lectura</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="ti ti-info-circle me-1"></i>
                            Contraseña para todos: <code>password</code>
                        </small>
                    </div>
                </div>
            </div>

            <div class="card-footer text-center py-3">
                <small class="text-muted">Sistema de Métricas v2.0 &copy; 2026</small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/js/tabler.min.js"></script>
</body>
</html>
