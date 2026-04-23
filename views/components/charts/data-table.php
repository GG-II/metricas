<?php
/**
 * GRÁFICO: Tabla de Datos
 * Muestra datos tabulares con ordenamiento
 */

return [
    'meta' => [
        'id' => 'data_table',
        'nombre' => 'Tabla de Datos',
        'descripcion' => 'Tabla con múltiples métricas y comparaciones',
        'icono' => 'table',
        'requiere_metricas' => 1,
        'version' => '1.0'
    ],
    
    'form' => function() {
        return <<<'HTML'
<div class="row g-3">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="ti ti-table me-2"></i>
            Muestra datos de múltiples métricas en formato tabla con comparación
        </div>
    </div>
    
    <div class="col-md-6">
        <label class="form-label required">Métrica 1</label>
        <select name="metrica_1_id" class="form-select" required>
            <option value="">Seleccionar...</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Métrica 2 (opcional)</label>
        <select name="metrica_2_id" class="form-select">
            <option value="">No usar</option>
        </select>
    </div>
    
    <div class="col-md-6">
        <label class="form-label">Métrica 3 (opcional)</label>
        <select name="metrica_3_id" class="form-select">
            <option value="">No usar</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Métrica 4 (opcional)</label>
        <select name="metrica_4_id" class="form-select">
            <option value="">No usar</option>
        </select>
    </div>
    
    <div class="col-md-6">
        <label class="form-label">Períodos a mostrar</label>
        <select name="periodos" class="form-select">
            <option value="3">Últimos 3 meses</option>
            <option value="6" selected>Últimos 6 meses</option>
            <option value="12">Últimos 12 meses</option>
        </select>
    </div>
    
    <div class="col-md-6">
        <label class="form-check form-switch mt-4">
            <input type="checkbox" name="mostrar_variacion" class="form-check-input" checked>
            <span class="form-check-label">Mostrar % de variación</span>
        </label>
    </div>
    
    <div class="col-md-12">
        <label class="form-check form-switch">
            <input type="checkbox" name="destacar_max" class="form-check-input" checked>
            <span class="form-check-label">Destacar valores máximos</span>
        </label>
    </div>
</div>
HTML;
    },
    
    'process' => function($post) {
        $metricas = [];
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($post["metrica_{$i}_id"])) {
                $metricas[] = (int)$post["metrica_{$i}_id"];
            }
        }
        
        return [
            'metricas' => $metricas,
            'periodos' => (int)($post['periodos'] ?? 6),
            'mostrar_variacion' => isset($post['mostrar_variacion']),
            'destacar_max' => isset($post['destacar_max'])
        ];
    },
    
    'load_config_js' => <<<'JS'
function(form, config) {
    if (config.metricas && Array.isArray(config.metricas)) {
        config.metricas.forEach((id, index) => {
            const num = index + 1;
            const select = form.querySelector(`[name="metrica_${num}_id"]`);
            if (select) select.value = id;
        });
    }
    
    if (config.periodos) {
        const select = form.querySelector('[name="periodos"]');
        if (select) select.value = config.periodos;
    }
    
    if (config.hasOwnProperty('mostrar_variacion')) {
        const checkbox = form.querySelector('[name="mostrar_variacion"]');
        if (checkbox) checkbox.checked = config.mostrar_variacion;
    }
    
    if (config.hasOwnProperty('destacar_max')) {
        const checkbox = form.querySelector('[name="destacar_max"]');
        if (checkbox) checkbox.checked = config.destacar_max;
    }
}
JS,
    
    'render' => function($config, $metrica_data, $area_color) {
        if (empty($config['metricas'])) {
            return '<div class="alert alert-warning m-3">Configura al menos una métrica</div>';
        }

        // Instanciar modelos con namespace correcto
        $valorMetricaModel = new \App\Models\ValorMetrica();
        $metricaModel = new \App\Models\Metrica();
        
        $periodos = (int)($config['periodos'] ?? 6);
        $mostrar_variacion = $config['mostrar_variacion'] ?? true;
        $destacar_max = $config['destacar_max'] ?? true;
        
        // Obtener datos de todas las métricas
        $datos_tabla = [];
        $metricas_info = [];
        
        foreach ($config['metricas'] as $metrica_id) {
            $metrica_info = $metricaModel->find($metrica_id);
            if (!$metrica_info) continue;
            
            $metricas_info[] = $metrica_info;
            $historico = $valorMetricaModel->getHistorico($metrica_id, $periodos);
            
            foreach ($historico as $dato) {
                $key = $dato['ejercicio'] . '-' . $dato['periodo'];
                
                if (!isset($datos_tabla[$key])) {
                    $mes_nombre = [1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun',
                                  7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'];
                    
                    $datos_tabla[$key] = [
                        'periodo' => $mes_nombre[$dato['periodo']] . ' ' . $dato['ejercicio'],
                        'valores' => []
                    ];
                }
                
                $datos_tabla[$key]['valores'][$metrica_id] = (float)$dato['valor'];
            }
        }
        
        // Ordenar por período
        ksort($datos_tabla);
        
        if (empty($datos_tabla)) {
            return '<div class="alert alert-info m-3">No hay datos históricos</div>';
        }
        
        // Calcular máximos por métrica
        $maximos = [];
        if ($destacar_max) {
            foreach ($config['metricas'] as $metrica_id) {
                $valores = array_column(array_column($datos_tabla, 'valores'), $metrica_id);
                $maximos[$metrica_id] = max(array_filter($valores));
            }
        }
        
        $table_id = 'table-' . uniqid();
        
        ob_start();
        ?>
        <div class="data-table-widget p-3">
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-vcenter table-hover" id="<?php echo $table_id; ?>">
                    <thead class="sticky-top bg-dark">
                        <tr>
                            <th class="text-muted">Período</th>
                            <?php foreach ($metricas_info as $metrica): ?>
                                <th class="text-end text-muted">
                                    <?php echo htmlspecialchars($metrica['nombre']); ?>
                                    <?php if ($metrica['unidad']): ?>
                                        <small class="text-muted">(<?php echo htmlspecialchars($metrica['unidad']); ?>)</small>
                                    <?php endif; ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $datos_array = array_values($datos_tabla);
                        for ($i = count($datos_array) - 1; $i >= 0; $i--): 
                            $fila = $datos_array[$i];
                            $fila_anterior = $i < count($datos_array) - 1 ? $datos_array[$i + 1] : null;
                        ?>
                            <tr>
                                <td class="text-muted fw-bold"><?php echo $fila['periodo']; ?></td>
                                <?php foreach ($config['metricas'] as $metrica_id): ?>
                                    <td class="text-end">
                                        <?php 
                                        $valor = $fila['valores'][$metrica_id] ?? 0;
                                        $es_maximo = $destacar_max && isset($maximos[$metrica_id]) && $valor == $maximos[$metrica_id] && $valor > 0;
                                        ?>
                                        
                                        <span class="<?php echo $es_maximo ? 'badge bg-success' : ''; ?>">
                                            <?php echo number_format($valor, 0); ?>
                                        </span>
                                        
                                        <?php if ($mostrar_variacion && $fila_anterior): ?>
                                            <?php 
                                            $valor_anterior = $fila_anterior['valores'][$metrica_id] ?? 0;
                                            if ($valor_anterior > 0) {
                                                $variacion = (($valor - $valor_anterior) / $valor_anterior) * 100;
                                                $color = $variacion >= 0 ? 'success' : 'danger';
                                                $icono = $variacion >= 0 ? 'trending-up' : 'trending-down';
                                            ?>
                                                <br>
                                                <small class="text-<?php echo $color; ?>">
                                                    <i class="ti ti-<?php echo $icono; ?>"></i>
                                                    <?php echo number_format(abs($variacion), 1); ?>%
                                                </small>
                                            <?php } ?>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <style>
        .data-table-widget .sticky-top {
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .data-table-widget .table-responsive::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        .data-table-widget .table-responsive::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.5);
            border-radius: 4px;
        }
        
        .data-table-widget .table-responsive::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.5);
            border-radius: 4px;
        }
        
        .data-table-widget .table-responsive::-webkit-scrollbar-thumb:hover {
            background: rgba(59, 130, 246, 0.7);
        }
        </style>
        <?php
        return ob_get_clean();
    }
];