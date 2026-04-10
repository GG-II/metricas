<?php
/**
 * Formulario reutilizable para crear/editar métricas
 */
?>

<div class="row g-3">
    <!-- Nombre -->
    <div class="col-md-8">
        <label class="form-label required">Nombre de la métrica</label>
        <input type="text" name="nombre" class="form-control" required 
               placeholder="Ej: Proyectos activos">
    </div>
    
    <!-- Orden -->
    <div class="col-md-4">
        <label class="form-label required">Orden</label>
        <input type="number" name="orden" class="form-control" required value="1" min="1">
        <small class="form-hint">Orden de visualización</small>
    </div>
    
    <!-- Slug -->
    <div class="col-md-6">
        <label class="form-label required">Slug (identificador único)</label>
        <input type="text" name="slug" class="form-control" required 
               placeholder="proyectos_activos" pattern="[a-z0-9_]+">
        <small class="form-hint">Solo letras minúsculas, números y guiones bajos</small>
    </div>
    
    <!-- Tipo de valor -->
    <div class="col-md-6">
        <label class="form-label required">Tipo de valor</label>
        <select name="tipo_valor" class="form-select" required>
            <option value="numero">Número entero</option>
            <option value="decimal">Decimal</option>
            <option value="porcentaje">Porcentaje</option>
        </select>
    </div>
    
    <!-- Unidad -->
    <div class="col-md-6">
        <label class="form-label">Unidad (opcional)</label>
        <input type="text" name="unidad" class="form-control" 
               placeholder="Ej: proyectos, bugs, %, horas">
    </div>
    
    <!-- Tipo de gráfico -->
    <div class="col-md-6">
        <label class="form-label required">Tipo de gráfico preferido</label>
        <select name="tipo_grafico" class="form-select" required>
            <option value="bar">Barras</option>
            <option value="line">Líneas</option>
            <option value="area">Área</option>
            <option value="number">Solo número (card)</option>
            <option value="progress">Barra de progreso</option>
        </select>
    </div>
    
    <!-- Grupo -->
    <div class="col-12">
        <label class="form-label">Grupo (opcional)</label>
        <input type="text" name="grupo" class="form-control" 
               placeholder="Ej: desarrollo, qa, infraestructura">
        <small class="form-hint">Agrupa métricas relacionadas</small>
    </div>
    
    <!-- Descripción -->
    <div class="col-12">
        <label class="form-label">Descripción (opcional)</label>
        <textarea name="descripcion" class="form-control" rows="2" 
                  placeholder="Descripción breve de qué mide esta métrica"></textarea>
    </div>
    
    <!-- Selector de ícono -->
    <div class="col-12">
        <label class="form-label required">Ícono</label>
        
        <!-- Preview del ícono -->
        <div class="text-center mb-3">
            <div class="icon-preview-large">
                <i class="ti ti-chart-bar"></i>
            </div>
        </div>
        
        <!-- Selector dropdown -->
        <select name="icono" class="form-select mb-3" required onchange="actualizarIconoPreview(this)">
            <?php foreach ($iconos_disponibles as $icono): ?>
                <option value="<?php echo $icono; ?>"><?php echo ucfirst(str_replace('-', ' ', $icono)); ?></option>
            <?php endforeach; ?>
        </select>
        
        <!-- Grid de íconos -->
        <details class="icon-selector-details">
            <summary class="icon-selector-toggle">
                <i class="ti ti-apps me-2"></i>
                Ver catálogo visual de íconos
            </summary>
            <div class="icon-grid-container">
                <div class="icon-grid">
                    <?php foreach ($iconos_disponibles as $icono): ?>
                        <div class="icon-option" 
                             data-icon="<?php echo $icono; ?>" 
                             onclick="seleccionarIcono(this, '<?php echo $icono; ?>')"
                             title="<?php echo ucfirst(str_replace('-', ' ', $icono)); ?>">
                            <i class="ti ti-<?php echo $icono; ?>"></i>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </details>
    </div>
</div>

<style>
/* Preview del ícono grande */
.icon-preview-large {
    width: 80px;
    height: 80px;
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    box-shadow: 0 8px 16px rgba(59, 130, 246, 0.3);
}

/* Toggle del selector de íconos */
.icon-selector-details {
    margin-top: 12px;
}

.icon-selector-toggle {
    cursor: pointer;
    padding: 12px 16px;
    background: rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.3);
    border-radius: 8px;
    color: #3b82f6;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    user-select: none;
}

.icon-selector-toggle:hover {
    background: rgba(59, 130, 246, 0.2);
    border-color: rgba(59, 130, 246, 0.5);
}

/* Contenedor del grid */
.icon-grid-container {
    margin-top: 16px;
    padding: 16px;
    background: rgba(15, 23, 42, 0.5);
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Grid de íconos en mosaico */
.icon-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(60px, 1fr));
    gap: 8px;
    max-height: 320px;
    overflow-y: auto;
    padding: 4px;
}

/* Scrollbar del grid */
.icon-grid::-webkit-scrollbar {
    width: 8px;
}

.icon-grid::-webkit-scrollbar-track {
    background: rgba(15, 23, 42, 0.5);
    border-radius: 4px;
}

.icon-grid::-webkit-scrollbar-thumb {
    background: rgba(59, 130, 246, 0.5);
    border-radius: 4px;
}

.icon-grid::-webkit-scrollbar-thumb:hover {
    background: rgba(59, 130, 246, 0.7);
}

/* Cada opción de ícono */
.icon-option {
    aspect-ratio: 1;
    min-height: 60px;
    border: 2px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    background: rgba(30, 41, 59, 0.5);
    position: relative;
}

.icon-option:hover {
    border-color: #3b82f6;
    background: rgba(59, 130, 246, 0.15);
    transform: scale(1.05);
}

.icon-option.selected {
    border-color: #3b82f6;
    background: rgba(59, 130, 246, 0.25);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

.icon-option.selected::after {
    content: '✓';
    position: absolute;
    top: 2px;
    right: 2px;
    width: 18px;
    height: 18px;
    background: #3b82f6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    color: white;
    font-weight: bold;
}

.icon-option i {
    font-size: 28px;
    color: #94a3b8;
    transition: color 0.2s;
}

.icon-option:hover i,
.icon-option.selected i {
    color: #3b82f6;
}
</style>

<script>
function actualizarIconoPreview(selectElement) {
    const icono = selectElement.value;
    const form = selectElement.closest('form');
    const preview = form.querySelector('.icon-preview-large i');
    
    if (preview) {
        preview.className = `ti ti-${icono}`;
    }
    
    // Actualizar selección en el grid
    const iconOptions = form.querySelectorAll('.icon-option');
    iconOptions.forEach(el => {
        el.classList.toggle('selected', el.getAttribute('data-icon') === icono);
    });
}

function seleccionarIcono(element, icono) {
    const form = element.closest('form');
    
    // Actualizar select
    const select = form.querySelector('[name="icono"]');
    if (select) {
        select.value = icono;
    }
    
    // Actualizar preview
    const preview = form.querySelector('.icon-preview-large i');
    if (preview) {
        preview.className = `ti ti-${icono}`;
    }
    
    // Actualizar selección visual
    const iconOptions = form.querySelectorAll('.icon-option');
    iconOptions.forEach(el => {
        el.classList.toggle('selected', el.getAttribute('data-icon') === icono);
    });
}
</script>