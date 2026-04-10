<?php
/**
 * Formulario reutilizable para crear/editar usuarios
 */
$es_edicion = $es_edicion ?? false;
?>

<div class="row g-3">
    
    <!-- Preview de Avatar -->
    <div class="col-12">
        <div class="text-center mb-3">
            <div class="avatar-upload-container">
                <div class="avatar-preview" id="avatar-preview">
                    <i class="ti ti-user"></i>
                </div>
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
                Click en la cámara para subir una foto<br>
                <small>JPG, PNG o GIF - Máximo 2MB</small>
            </div>
        </div>
    </div>
    
    <!-- Nombre completo -->
    <div class="col-12">
        <label class="form-label required">Nombre completo</label>
        <input type="text" name="nombre" class="form-control" required 
               placeholder="Ej: Juan Pérez">
    </div>
    
    <!-- Email -->
    <div class="col-md-6">
        <label class="form-label required">Email</label>
        <input type="email" name="email" class="form-control" required 
               placeholder="juan@empresa.com">
    </div>
    
    <!-- Username -->
    <div class="col-md-6">
        <label class="form-label required">Nombre de usuario</label>
        <input type="text" name="username" class="form-control" required 
               placeholder="jperez" pattern="[a-zA-Z0-9_]+">
        <small class="form-hint">Solo letras, números y guiones bajos</small>
    </div>
    
    <!-- Contraseña -->
    <div class="col-md-6">
        <label class="form-label <?php echo $es_edicion ? '' : 'required'; ?>">
            Contraseña
            <?php if ($es_edicion): ?>
                <span class="text-muted">(dejar vacío para no cambiar)</span>
            <?php endif; ?>
        </label>
        <input type="password" name="password" class="form-control" 
               <?php echo $es_edicion ? '' : 'required'; ?>
               minlength="8"
               placeholder="Mínimo 8 caracteres">
    </div>
    
    <!-- Rol -->
    <div class="col-md-6">
        <label class="form-label required">Rol</label>
        <select name="rol" class="form-select" required>
            <option value="viewer">Visualizador</option>
            <option value="admin">Administrador</option>
        </select>
        <small class="form-hint">
            <strong>Admin:</strong> puede editar métricas y usuarios<br>
            <strong>Visualizador:</strong> solo puede ver dashboards
        </small>
    </div>
    
    <!-- Estado (solo en edición) -->
    <?php if ($es_edicion): ?>
        <div class="col-12">
            <label class="form-check form-switch">
                <input type="checkbox" name="activo" class="form-check-input" checked>
                <span class="form-check-label">Usuario activo</span>
            </label>
            <small class="form-hint">Los usuarios inactivos no pueden iniciar sesión</small>
        </div>
    <?php endif; ?>
    
</div>

<style>
.avatar-upload-container {
    position: relative;
    display: inline-block;
}

.avatar-preview {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    color: white;
    background-size: cover;
    background-position: center;
    box-shadow: 0 8px 16px rgba(59, 130, 246, 0.3);
    position: relative;
    overflow: hidden;
}

.avatar-upload-btn {
    position: absolute;
    bottom: 5px;
    right: 5px;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #3b82f6;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: 3px solid #0f172a;
    transition: all 0.2s;
    font-size: 18px;
}

.avatar-upload-btn:hover {
    background: #2563eb;
    transform: scale(1.1);
}
</style>

<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const preview = document.getElementById('avatar-preview');
            preview.style.backgroundImage = `url('${e.target.result}')`;
            preview.innerHTML = ''; // Quitar el ícono
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>