<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Models\Usuario;

// Debe estar autenticado
if (!isset($_SESSION['user_id'])) {
    redirect('/login.php');
}

$usuarioModel = new Usuario();
$user = $usuarioModel->find($_SESSION['user_id']);

// Si no tiene contraseña débil, redirigir al dashboard
if (!isset($_SESSION['debe_cambiar_password'])) {
    redirect('/index.php');
}

$error = '';
$success = '';

// Procesar cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();

    $nueva = $_POST['password_nueva'] ?? '';
    $confirmar = $_POST['password_confirmar'] ?? '';

    // Validaciones
    if (empty($nueva) || empty($confirmar)) {
        $error = 'Por favor completa todos los campos';
    } elseif (strlen($nueva) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    } elseif ($nueva !== $confirmar) {
        $error = 'Las contraseñas no coinciden';
    } elseif (strtolower($nueva) === 'password') {
        $error = 'No puedes usar "password" como contraseña. Elige una más segura';
    } else {
        // Actualizar contraseña
        if ($usuarioModel->update($user['id'], ['password' => password_hash($nueva, PASSWORD_DEFAULT)])) {
            // Limpiar flag de cambio forzado
            unset($_SESSION['debe_cambiar_password']);

            setFlash('success', 'Contraseña actualizada exitosamente. Ahora puedes usar el sistema.');
            redirect('/index.php');
        } else {
            $error = 'Error al actualizar la contraseña. Intenta nuevamente';
        }
    }
}

$pageTitle = 'Cambiar Contraseña - Sistema de Métricas';
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?php echo baseUrl('/assets/favicon/favicon.svg'); ?>">
    <link rel="icon" type="image/png" sizes="96x96" href="<?php echo baseUrl('/assets/favicon/favicon-96x96.png'); ?>">
    <link rel="shortcut icon" href="<?php echo baseUrl('/assets/favicon/favicon.ico'); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo baseUrl('/assets/favicon/apple-touch-icon.png'); ?>">
    <meta name="theme-color" content="#1e40af">

    <!-- Tabler CSS -->
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?php echo baseUrl('/assets/css/custom.css'); ?>" rel="stylesheet">
</head>
<body class="login-container">
    <div class="container container-tight py-4">
        <div class="card card-md login-card">
            <div class="login-header">
                <div class="login-brand-logo">
                    <img src="<?php echo baseUrl('/assets/images/logo_cooperativa.png'); ?>" alt="Logo Cooperativa La Inmaculada" class="login-logo-img">
                </div>
                <h2 class="mb-1">Sistema de Métricas</h2>
                <p class="mb-0 opacity-75">Multi-Departamento</p>
            </div>

            <div class="card-body p-4">
                <div class="alert alert-warning mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-alert-triangle me-2"></i>
                        <div>
                            <h4 class="alert-title">Cambio de contraseña requerido</h4>
                            <div class="text-muted">Por seguridad, debes cambiar tu contraseña antes de continuar.</div>
                        </div>
                    </div>
                </div>

                <h3 class="card-title text-center mb-4">Nueva Contraseña</h3>

                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-alert-circle me-2"></i>
                        <div><?php echo e($error); ?></div>
                    </div>
                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <?php csrf_field(); ?>

                    <div class="mb-3">
                        <label class="form-label required">Nueva Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="ti ti-lock"></i>
                            </span>
                            <input type="password" name="password_nueva" class="form-control"
                                   placeholder="Ingresa tu nueva contraseña" required minlength="6" autofocus>
                        </div>
                        <small class="form-hint">Mínimo 6 caracteres. No uses "password".</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label required">Confirmar Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="ti ti-lock-check"></i>
                            </span>
                            <input type="password" name="password_confirmar" class="form-control"
                                   placeholder="Confirma tu nueva contraseña" required minlength="6">
                        </div>
                    </div>

                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-check me-2"></i>
                            Cambiar Contraseña
                        </button>
                    </div>
                </form>

                <div class="mt-3 text-center">
                    <a href="<?php echo baseUrl('/logout.php'); ?>" class="text-muted">
                        <i class="ti ti-logout me-1"></i>
                        Cerrar Sesión
                    </a>
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
