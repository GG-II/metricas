<?php
/**
 * Mi Perfil
 * Usuario puede editar su propia información
 */

require_once 'config.php';
requireLogin();

require_once 'models/Usuario.php';

$usuarioModel = new Usuario();
$usuario = $usuarioModel->getById($_SESSION['user_id']);

$mensaje = '';
$mensaje_tipo = '';

// ACTUALIZAR PERFIL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_perfil'])) {
    try {
        // Validar email único (excluyendo el usuario actual)
        if ($usuarioModel->emailExists($_POST['email'], $_SESSION['user_id'])) {
            throw new Exception('El email ya está registrado por otro usuario.');
        }
        
        // Procesar nueva foto de perfil
        $foto_perfil = null;
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            // Eliminar foto anterior
if ($usuario['foto_perfil']) {
    eliminarFotoPerfil($usuario['foto_perfil']);
}
            $foto_perfil = procesarUploadFoto($_FILES['foto_perfil']);
        }
        
        $data = [
            'nombre' => sanitize($_POST['nombre']),
            'email' => sanitize($_POST['email'])
        ];
        
        // Actualizar foto si se subió una nueva
        if ($foto_perfil) {
            $data['foto_perfil'] = $foto_perfil;
        }
        
        // Actualizar contraseña solo si se proporcionó
        if (!empty($_POST['password_nueva'])) {
            // Verificar contraseña actual
            if (!verifyPassword($_POST['password_actual'], $usuario['password'])) {
                throw new Exception('La contraseña actual es incorrecta.');
            }
            
            // Validar que las nuevas coincidan
            if ($_POST['password_nueva'] !== $_POST['password_confirmar']) {
                throw new Exception('Las contraseñas nuevas no coinciden.');
            }
            
            $data['password'] = hashPassword($_POST['password_nueva']);
        }
        
        $usuarioModel->update($_SESSION['user_id'], $data);
        
        // Actualizar sesión
        $_SESSION['user_name'] = $data['nombre'];
        $_SESSION['user_email'] = $data['email'];
        
        logActivity('actualizar_perfil', 'usuarios', $_SESSION['user_id'], 'Perfil actualizado');
        
        $mensaje = 'Perfil actualizado exitosamente.';
        $mensaje_tipo = 'success';
        
        // Recargar datos
        $usuario = $usuarioModel->getById($_SESSION['user_id']);
        
    } catch (Exception $e) {
        $mensaje = 'Error: ' . $e->getMessage();
        $mensaje_tipo = 'danger';
    }
}

$page_title = 'Mi Perfil';
$is_admin = isAdmin();
?>

<?php include 'partials/header.php'; ?>

<div class="page-body">
    <div class="container-xl">
        
        <div class="mb-3">
            <a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-ghost-secondary">
                <i class="ti ti-arrow-left me-1"></i>
                Volver al Dashboard
            </a>
        </div>
        
        <div class="page-header mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-user me-2"></i>
                        Mi Perfil
                    </h2>
                    <div class="text-muted mt-1">Actualiza tu información personal</div>
                </div>
            </div>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $mensaje_tipo; ?> alert-dismissible fade show">
                <i class="ti ti-<?php echo $mensaje_tipo === 'success' ? 'check' : 'alert-circle'; ?> me-2"></i>
                <?php echo $mensaje; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-8 mx-auto">
                <form method="POST" enctype="multipart/form-data">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Información Personal</h3>
                        </div>
                        <div class="card-body">
                            
                            <!-- Avatar -->
                            <div class="text-center mb-4">
                                <div class="avatar-upload-container">
                                    <?php if ($usuario['foto_perfil']): ?>
                                        <div class="avatar-preview-large" id="avatar-preview" 
                                             style="background-image: url('<?php echo BASE_URL; ?>/uploads/avatars/<?php echo $usuario['foto_perfil']; ?>')"></div>
                                    <?php else: ?>
                                        <div class="avatar-preview-large" id="avatar-preview">
                                            <?php echo strtoupper(substr($usuario['nombre'], 0, 2)); ?>
                                        </div>
                                    <?php endif; ?>
                                    <label for="foto-perfil-input" class="avatar-upload-btn">
                                        <i class="ti ti-camera"></i>
                                    </label>
                                </div>
                                <input type="file" 
                                       id="foto-perfil-input" 
                                       name="foto_perfil" 
                                       accept="image/*"
                                       style="display: none;"
                                       onchange="previewAvatar(this)">
                                <div class="text-muted small mt-2">
                                    Click en la cámara para cambiar tu foto<br>
                                    <small>JPG, PNG o GIF - Máximo 2MB</small>
                                </div>
                            </div>
                            
                            <div class="row g-3">
                                <!-- Nombre -->
                                <div class="col-12">
                                    <label class="form-label required">Nombre completo</label>
                                    <input type="text" name="nombre" class="form-control" required 
                                           value="<?php echo htmlspecialchars($usuario['nombre']); ?>">
                                </div>
                                
                                <!-- Email -->
                                <div class="col-md-6">
                                    <label class="form-label required">Email</label>
                                    <input type="email" name="email" class="form-control" required 
                                           value="<?php echo htmlspecialchars($usuario['email']); ?>">
                                </div>
                                
                                <!-- Username (solo lectura) -->
                                <div class="col-md-6">
                                    <label class="form-label">Usuario</label>
                                    <input type="text" class="form-control" readonly 
                                           value="<?php echo $usuario['username']; ?>">
                                    <small class="form-hint">El nombre de usuario no se puede cambiar</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Cambiar Contraseña -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3 class="card-title">Cambiar Contraseña</h3>
                            <div class="card-subtitle">Déjalo en blanco si no quieres cambiarla</div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Contraseña actual</label>
                                    <input type="password" name="password_actual" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nueva contraseña</label>
                                    <input type="password" name="password_nueva" class="form-control" minlength="8">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirmar nueva contraseña</label>
                                    <input type="password" name="password_confirmar" class="form-control" minlength="8">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" name="actualizar_perfil" class="btn btn-primary btn-lg">
                            <i class="ti ti-device-floppy me-2"></i>
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
    </div>
</div>

<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const preview = document.getElementById('avatar-preview');
            preview.style.backgroundImage = `url('${e.target.result}')`;
            preview.innerHTML = '';
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<style>
.avatar-upload-container {
    position: relative;
    display: inline-block;
}

.avatar-preview-large {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 60px;
    font-weight: 600;
    color: white;
    background-size: cover;
    background-position: center;
    box-shadow: 0 8px 16px rgba(59, 130, 246, 0.3);
    position: relative;
    overflow: hidden;
}

.avatar-upload-btn {
    position: absolute;
    bottom: 10px;
    right: 10px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #3b82f6;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: 3px solid white;
    transition: all 0.2s;
    font-size: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.avatar-upload-btn:hover {
    background: #2563eb;
    transform: scale(1.1);
}
</style>

<?php include 'partials/footer.php'; ?>