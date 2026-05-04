<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Models\Usuario;

AuthMiddleware::handle();

$user = getCurrentUser();
$usuarioModel = new Usuario();

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ✅ PROTECCIÓN CSRF
    csrf_validate();

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'actualizar_perfil':
                $data = [
                    'nombre' => sanitize($_POST['nombre']),
                    'email' => sanitize($_POST['email'] ?? ''),
                    'avatar_icono' => sanitize($_POST['avatar_icono'] ?? 'user'),
                    'avatar_color' => sanitize($_POST['avatar_color'] ?? '#3b82f6')
                ];

                if ($usuarioModel->update($user['id'], $data)) {
                    // Actualizar sesión
                    $_SESSION['user']['nombre'] = $data['nombre'];
                    $_SESSION['user']['email'] = $data['email'];
                    $_SESSION['user']['avatar_icono'] = $data['avatar_icono'];
                    $_SESSION['user']['avatar_color'] = $data['avatar_color'];

                    setFlash('success', 'Perfil actualizado exitosamente');
                } else {
                    setFlash('error', 'Error al actualizar el perfil');
                }
                redirect('/perfil.php');
                break;

            case 'cambiar_contrasena':
                $actual = $_POST['password_actual'];
                $nueva = $_POST['password_nueva'];
                $confirmar = $_POST['password_confirmar'];

                // Verificar contraseña actual
                $userDB = $usuarioModel->find($user['id']);
                if (!password_verify($actual, $userDB['password'])) {
                    setFlash('error', 'La contraseña actual es incorrecta');
                    redirect('/perfil.php');
                    break;
                }

                // Verificar que las nuevas contraseñas coincidan
                if ($nueva !== $confirmar) {
                    setFlash('error', 'Las contraseñas nuevas no coinciden');
                    redirect('/perfil.php');
                    break;
                }

                // Actualizar contraseña
                if ($usuarioModel->update($user['id'], ['password' => password_hash($nueva, PASSWORD_DEFAULT)])) {
                    setFlash('success', 'Contraseña actualizada exitosamente');
                } else {
                    setFlash('error', 'Error al actualizar la contraseña');
                }
                redirect('/perfil.php');
                break;

            case 'cambiar_tema':
                $tema = $_POST['tema'] === 'dark' ? 'dark' : 'light';

                if ($usuarioModel->update($user['id'], ['tema' => $tema])) {
                    $_SESSION['user']['tema'] = $tema;
                    setFlash('success', 'Tema actualizado exitosamente');
                } else {
                    setFlash('error', 'Error al actualizar el tema');
                }
                redirect('/perfil.php');
                break;
        }
    }
}

// Recargar datos del usuario
$user = $usuarioModel->findWithRelations($user['id']);

$pageTitle = 'Mi Perfil';
require_once __DIR__ . '/../views/layouts/header.php';
?>

<div class="page-wrapper">
    <div class="page-body">
        <div class="container-xl">

            <!-- Header -->
            <div class="page-header mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h2 class="page-title">
                            <i class="ti ti-user me-2"></i>
                            Mi Perfil
                        </h2>
                        <div class="text-muted mt-1">Gestiona tu información personal y preferencias</div>
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
            <?php if (isset($_SESSION['flash']) && isset($_SESSION['flash']['type']) && isset($_SESSION['flash']['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['flash']['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo e($_SESSION['flash']['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <div class="row">
                <!-- Card de Perfil -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <span class="avatar avatar-xl mb-3" style="background-color: <?php echo e($user['avatar_color'] ?? '#3b82f6'); ?>; width: 100px; height: 100px;">
                                <i class="ti ti-<?php echo e($user['avatar_icono'] ?? 'user'); ?>" style="font-size: 3.5rem;"></i>
                            </span>
                            <h3 class="mb-1"><?php echo e($user['nombre']); ?></h3>
                            <div class="text-muted mb-2">@<?php echo e($user['username']); ?></div>
                            <?php if ($user['email']): ?>
                                <div class="small text-muted mb-3">
                                    <i class="ti ti-mail me-1"></i>
                                    <?php echo e($user['email']); ?>
                                </div>
                            <?php endif; ?>

                            <?php
                            $roles = [
                                'super_admin' => ['nombre' => 'Super Admin', 'class' => 'bg-red'],
                                'dept_admin' => ['nombre' => 'Admin Departamento', 'class' => 'bg-blue'],
                                'dept_viewer' => ['nombre' => 'Visualizador', 'class' => 'bg-green']
                            ];
                            $rol_info = $roles[$user['rol']] ?? ['nombre' => $user['rol'], 'class' => 'bg-secondary'];
                            ?>
                            <span class="badge <?php echo $rol_info['class']; ?> mb-3">
                                <?php echo $rol_info['nombre']; ?>
                            </span>

                            <?php if ($user['departamento_nombre']): ?>
                                <div class="mt-3 pt-3 border-top">
                                    <div class="row">
                                        <div class="col">
                                            <div class="small text-muted">Departamento</div>
                                            <div class="fw-bold"><?php echo e($user['departamento_nombre']); ?></div>
                                        </div>
                                        <?php if ($user['area_nombre']): ?>
                                        <div class="col">
                                            <div class="small text-muted">Área</div>
                                            <div class="fw-bold"><?php echo e($user['area_nombre']); ?></div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Card de Tema -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Apariencia</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="cambiar_tema">
                                <div class="form-label">Tema</div>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="tema" id="tema-light" value="light"
                                           <?php echo ($user['tema'] ?? 'light') === 'light' ? 'checked' : ''; ?>
                                           onchange="this.form.submit()">
                                    <label class="btn" for="tema-light">
                                        <i class="ti ti-sun me-1"></i> Claro
                                    </label>

                                    <input type="radio" class="btn-check" name="tema" id="tema-dark" value="dark"
                                           <?php echo ($user['tema'] ?? 'light') === 'dark' ? 'checked' : ''; ?>
                                           onchange="this.form.submit()">
                                    <label class="btn" for="tema-dark">
                                        <i class="ti ti-moon me-1"></i> Oscuro
                                    </label>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Formularios -->
                <div class="col-md-8">
                    <!-- Información Personal -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Información Personal</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="actualizar_perfil">

                                <div class="mb-3">
                                    <label class="form-label required">Nombre Completo</label>
                                    <input type="text" name="nombre" class="form-control"
                                           value="<?php echo e($user['nombre']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control"
                                           value="<?php echo e($user['email'] ?? ''); ?>">
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <?php
                                        $input_name = 'avatar_icono';
                                        $selected_icon = $user['avatar_icono'] ?? 'user';
                                        $label = 'Icono de Avatar';
                                        include __DIR__ . '/../views/components/icon-picker.php';
                                        ?>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label required">Color de Avatar</label>
                                        <div class="input-group">
                                            <input type="color" name="avatar_color" class="form-control form-control-color"
                                                   value="<?php echo e($user['avatar_color'] ?? '#3b82f6'); ?>" required>
                                            <input type="text" class="form-control" id="avatar-color-hex"
                                                   value="<?php echo e($user['avatar_color'] ?? '#3b82f6'); ?>"
                                                   pattern="^#[0-9A-Fa-f]{6}$" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-check me-1"></i>
                                        Guardar Cambios
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Cambiar Contraseña -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Cambiar Contraseña</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="cambiar_contrasena">

                                <div class="mb-3">
                                    <label class="form-label required">Contraseña Actual</label>
                                    <input type="password" name="password_actual" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required">Nueva Contraseña</label>
                                    <input type="password" name="password_nueva" class="form-control" required minlength="6">
                                    <div class="form-hint">Mínimo 6 caracteres</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required">Confirmar Nueva Contraseña</label>
                                    <input type="password" name="password_confirmar" class="form-control" required minlength="6">
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="ti ti-lock me-1"></i>
                                        Cambiar Contraseña
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
// Sincronizar color picker con input de texto
document.addEventListener('DOMContentLoaded', function() {
    const colorPicker = document.querySelector('input[name="avatar_color"]');
    const colorHex = document.getElementById('avatar-color-hex');

    if (colorPicker && colorHex) {
        colorPicker.addEventListener('input', function() {
            colorHex.value = this.value.toUpperCase();
        });
    }
});
</script>

<!-- Tabler JS -->
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/js/tabler.min.js"></script>
<script src="<?php echo baseUrl('/assets/js/theme-toggle.js'); ?>"></script>

<?php require_once __DIR__ . '/../views/layouts/footer.php'; ?>
