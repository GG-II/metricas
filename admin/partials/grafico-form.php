<?php
/**
 * Formulario reutilizable para crear/editar gráficos
 * Sistema auto-descubierto con ChartRegistry
 */
$form_mode = $form_mode ?? 'crear';
$form_id = $form_mode === 'crear' ? 'form-crear' : 'form-editar';
?>

<div class="row g-3">
    
    <!-- Información Básica -->
    <div class="col-12">
        <h4 class="mb-3">Información Básica</h4>
    </div>
    
    <!-- Título -->
    <div class="col-md-8">
        <label class="form-label required">Título del gráfico</label>
        <input type="text" name="titulo" class="form-control" required 
               placeholder="Ej: Proyectos Activos">
    </div>
    
    <!-- Tamaño -->
    <div class="col-md-4">
        <label class="form-label required">Tamaño</label>
        <select name="grid_w" class="form-select" required>
            <option value="4">Pequeño (33%)</option>
            <option value="6" selected>Mediano (50%)</option>
            <option value="8">Grande (66%)</option>
            <option value="12">Extra Grande (100%)</option>
        </select>
        <small class="form-hint">Ancho del widget</small>
    </div>
    
    <!-- Descripción -->
    <div class="col-12">
        <label class="form-label">Descripción (opcional)</label>
        <textarea name="descripcion" class="form-control" rows="2" 
                  placeholder="Descripción que aparecerá como tooltip"></textarea>
    </div>
    
    <!-- Tipo de Gráfico -->
    <div class="col-md-6">
        <label class="form-label required">Tipo de gráfico</label>
        <select name="tipo" class="form-select" required onchange="cambiarTipoGrafico(this.value, this.closest('form'))">
            <option value="">Seleccionar tipo...</option>
            <?php foreach ($tipos_graficos as $tipo_key => $tipo_info): ?>
                <option value="<?php echo $tipo_key; ?>">
                    <?php echo $tipo_info['nombre']; ?> - <?php echo $tipo_info['descripcion']; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <!-- Alto del Widget -->
    <div class="col-md-6">
        <label class="form-label required">Alto del widget</label>
        <select name="grid_h" class="form-select" required>
            <option value="2">Extra Bajo (160px)</option>
            <option value="3">Bajo (240px)</option>
            <option value="4" selected>Normal (320px)</option>
            <option value="5">Alto (400px)</option>
            <option value="6">Extra Alto (480px)</option>
            <option value="8">Muy Alto (640px)</option>
        </select>
        <small class="form-hint">Altura en filas</small>
    </div>
    
    <!-- Divisor -->
    <div class="col-12">
        <hr class="my-3">
        <h4 class="mb-3">Configuración del Gráfico</h4>
        <div id="tipo-ayuda" class="alert alert-info mb-3" style="display: none;">
            <i class="ti ti-info-circle me-2"></i>
            <span id="tipo-ayuda-texto"></span>
        </div>
    </div>
    
    <!-- Contenedor dinámico según tipo de gráfico -->
    <div id="configuracion-container" class="col-12">
        <div class="alert alert-secondary">
            <i class="ti ti-arrow-up me-2"></i>
            Selecciona un tipo de gráfico para ver las opciones de configuración
        </div>
    </div>
    
    <!-- Divisor -->
    <div class="col-12">
        <hr class="my-3">
        <h4 class="mb-3">Opciones Avanzadas</h4>
    </div>
    
    <!-- Permisos de visualización -->
    <div class="col-md-6">
        <label class="form-label required">¿Quién puede ver este gráfico?</label>
        <select name="permisos_visualizacion" class="form-select" required>
            <option value="todos" selected>Todos los usuarios</option>
            <option value="admin">Solo administradores</option>
        </select>
    </div>
    
    <!-- Estado -->
    <div class="col-md-6">
        <label class="form-label">Estado</label>
        <label class="form-check form-switch mt-2">
            <input type="checkbox" name="activo" class="form-check-input" checked>
            <span class="form-check-label">Gráfico activo (visible en dashboard)</span>
        </label>
    </div>
    
</div>

<script>
function cambiarTipoGrafico(tipo, form, configExistente = null) {
    const container = form.querySelector('#configuracion-container');
    const ayudaDiv = form.querySelector('#tipo-ayuda');
    const ayudaTexto = form.querySelector('#tipo-ayuda-texto');
    
    if (!tipo) {
        container.innerHTML = `
            <div class="alert alert-secondary">
                <i class="ti ti-arrow-up me-2"></i>
                Selecciona un tipo de gráfico para ver las opciones de configuración
            </div>
        `;
        ayudaDiv.style.display = 'none';
        return;
    }
    
    // Obtener info del tipo
    const tipoInfo = tiposGraficos[tipo];
    
    if (!tipoInfo) {
        container.innerHTML = '<div class="alert alert-warning">Tipo de gráfico no disponible</div>';
        ayudaDiv.style.display = 'none';
        return;
    }
    
    // Mostrar ayuda
    ayudaTexto.textContent = tipoInfo.descripcion;
    ayudaDiv.style.display = 'block';
    
    // Cargar formulario específico vía AJAX
    fetch('<?php echo BASE_URL; ?>/admin/ajax/obtener-formulario-grafico.php?tipo=' + tipo)
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
            
            // Llenar selectores de métricas
            const selectoresMetricas = container.querySelectorAll('select[name^="metrica"]');
            selectoresMetricas.forEach(select => {
                // Limpiar opciones existentes excepto la primera
                while (select.options.length > 1) {
                    select.remove(1);
                }
                
                // Agregar métricas
                metricasDelArea.forEach(metrica => {
                    const option = document.createElement('option');
                    option.value = metrica.id;
                    option.textContent = `${metrica.nombre} (${metrica.unidad || 'sin unidad'})`;
                    select.appendChild(option);
                });
            });
            
            // Si hay configuración existente (modo edición), cargarla
            if (configExistente && chartConfigLoaders[tipo]) {
                chartConfigLoaders[tipo](form, configExistente);
            }
        })
        .catch(error => {
            console.error('Error cargando formulario:', error);
            container.innerHTML = '<div class="alert alert-danger">Error al cargar formulario</div>';
        });
}
</script>

<style>
#tipo-ayuda {
    border-left: 4px solid #0ea5e9;
}

#configuracion-container {
    min-height: 150px;
}
</style>