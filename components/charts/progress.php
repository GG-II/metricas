<?php
/**
 * GRÁFICO: Progress Bar
 * Barra de progreso horizontal
 */

return [
    // ==========================================
    // METADATA
    // ==========================================
    'meta' => [
        'id' => 'progress',
        'nombre' => 'Barra de Progreso',
        'descripcion' => 'Progreso horizontal con porcentaje',
        'icono' => 'progress',
        'requiere_metricas' => 1,
        'version' => '1.0'
    ],
    
    // ==========================================
    // FORMULARIO DE CONFIGURACIÓN
    // ==========================================
    'form' => function() {
        return <<<'HTML'
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label required">Métrica a mostrar</label>
        <select name="metrica_id" class="form-select" required>
            <option value="">Seleccionar métrica...</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Color de la barra</label>
        <input type="color" name="color" class="form-control form-control-color" value="#3b82f6">
    </div>
    <div class="col-md-6">
        <label class="form-label">Objetivo (meta a alcanzar)</label>
        <input type="number" name="objetivo" class="form-control" value="100" min="1">
        <small class="form-hint">Valor máximo para calcular el porcentaje</small>
    </div>
    <div class="col-md-6">
        <label class="form-label">Altura de la barra (px)</label>
        <input type="number" name="altura" class="form-control" value="40" min="20" max="100">
    </div>
    <div class="col-md-12">
        <label class="form-check form-switch">
            <input type="checkbox" name="mostrar_valor" class="form-check-input" checked>
            <span class="form-check-label">Mostrar valor numérico</span>
        </label>
    </div>
</div>
HTML;
    },
    
    // ==========================================
    // PROCESAR FORMULARIO
    // ==========================================
    'process' => function($post) {
        return [
            'metrica_id' => (int)$post['metrica_id'],
            'color' => sanitize($post['color'] ?? '#3b82f6'),
            'objetivo' => (int)($post['objetivo'] ?? 100),
            'altura' => (int)($post['altura'] ?? 40),
            'mostrar_valor' => isset($post['mostrar_valor'])
        ];
    },
    
    // ==========================================
    // CARGAR CONFIGURACIÓN EXISTENTE (JS)
    // ==========================================
    'load_config_js' => <<<'JS'
function(form, config) {
    const metricaSelect = form.querySelector('[name="metrica_id"]');
    if (metricaSelect && config.metrica_id) {
        metricaSelect.value = config.metrica_id;
    }
    
    if (config.color) {
        const colorInput = form.querySelector('[name="color"]');
        if (colorInput) colorInput.value = config.color;
    }
    
    if (config.objetivo) {
        const objetivoInput = form.querySelector('[name="objetivo"]');
        if (objetivoInput) objetivoInput.value = config.objetivo;
    }
    
    if (config.altura) {
        const alturaInput = form.querySelector('[name="altura"]');
        if (alturaInput) alturaInput.value = config.altura;
    }
    
    if (config.hasOwnProperty('mostrar_valor')) {
        const checkbox = form.querySelector('[name="mostrar_valor"]');
        if (checkbox) checkbox.checked = config.mostrar_valor;
    }
}
JS,
    
    // ==========================================
    // RENDERIZAR WIDGET
    // ==========================================
    'render' => function($config, $metrica_data, $area_color) {
        if (!$metrica_data) {
            return '<div class="alert alert-warning m-3">No hay datos disponibles</div>';
        }
        
        $nombre = $metrica_data['nombre'] ?? 'Métrica';
        $valor_actual = (float)($metrica_data['valor'] ?? 0);
        $tipo_valor = $metrica_data['tipo_valor'] ?? 'numero';
        
        $color = $config['color'] ?? '#3b82f6';
        $objetivo = (float)($config['objetivo'] ?? 100);
        $altura = (int)($config['altura'] ?? 40);
        $mostrar_valor = $config['mostrar_valor'] ?? true;
        
        // Calcular porcentaje
        if ($tipo_valor === 'porcentaje') {
            $porcentaje = min($valor_actual, 100);
        } else {
            $porcentaje = $objetivo > 0 ? min(($valor_actual / $objetivo) * 100, 100) : 0;
        }
        
        // Determinar color según progreso
        $color_barra = $color;
        if ($porcentaje >= 100) {
            $color_barra = '#10b981';
        } else if ($porcentaje >= 75) {
            $color_barra = '#3b82f6';
        } else if ($porcentaje >= 50) {
            $color_barra = '#f59e0b';
        } else {
            $color_barra = '#ef4444';
        }
        
        ob_start();
        ?>
        <div class="progress-widget p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <?php if ($mostrar_valor): ?>
                        <div class="progress-value" style="color: <?php echo $color_barra; ?>;">
                            <?php 
                            if ($tipo_valor === 'porcentaje') {
                                echo number_format($valor_actual, 1) . '%';
                            } else {
                                echo number_format($valor_actual) . ' / ' . number_format($objetivo);
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="progress-percentage" style="color: <?php echo $color_barra; ?>;">
                    <?php echo number_format($porcentaje, 1); ?>%
                </div>
            </div>
            
            <div class="progress" style="height: <?php echo $altura; ?>px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                     role="progressbar" 
                     style="width: <?php echo $porcentaje; ?>%; background-color: <?php echo $color_barra; ?>;"
                     aria-valuenow="<?php echo $porcentaje; ?>" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                </div>
            </div>
            
            <?php if ($objetivo > 0 && $tipo_valor !== 'porcentaje'): ?>
                <div class="text-muted small mt-2 text-center">
                    Objetivo: <?php echo number_format($objetivo); ?>
                </div>
            <?php endif; ?>
        </div>
        
        <style>
        .progress-widget {
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .progress-value {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .progress-percentage {
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .progress {
            background-color: rgba(30, 41, 59, 0.5);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .progress-bar {
            transition: width 0.6s ease;
        }
        </style>
        <?php
        return ob_get_clean();
    }
];