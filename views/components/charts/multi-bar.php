<?php
/**
 * GRÁFICO: Multi-Bar (Barras Múltiples)
 * Compara 3-6 métricas simultáneamente
 */

return [
    'meta' => [
        'id' => 'multi_bar',
        'nombre' => 'Barras Múltiples',
        'descripcion' => 'Compara 3 a 6 métricas simultáneamente',
        'icono' => 'chart-bar-off',
        'requiere_metricas' => 3,
        'version' => '1.0'
    ],
    
    'form' => function() {
        return <<<'HTML'
<div class="row g-3">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="ti ti-info-circle me-2"></i>
            Selecciona entre 3 y 6 métricas para comparar
        </div>
    </div>
    
    <div class="col-md-6">
        <label class="form-label required">Métrica 1</label>
        <select name="metrica_1_id" class="form-select" required>
            <option value="">Seleccionar...</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Color</label>
        <input type="color" name="color_1" class="form-control form-control-color" value="#3b82f6">
    </div>
    
    <div class="col-md-6">
        <label class="form-label required">Métrica 2</label>
        <select name="metrica_2_id" class="form-select" required>
            <option value="">Seleccionar...</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Color</label>
        <input type="color" name="color_2" class="form-control form-control-color" value="#10b981">
    </div>
    
    <div class="col-md-6">
        <label class="form-label required">Métrica 3</label>
        <select name="metrica_3_id" class="form-select" required>
            <option value="">Seleccionar...</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Color</label>
        <input type="color" name="color_3" class="form-control form-control-color" value="#f59e0b">
    </div>
    
    <div class="col-md-6">
        <label class="form-label">Métrica 4 (opcional)</label>
        <select name="metrica_4_id" class="form-select">
            <option value="">No usar</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Color</label>
        <input type="color" name="color_4" class="form-control form-control-color" value="#ef4444">
    </div>
    
    <div class="col-md-6">
        <label class="form-label">Métrica 5 (opcional)</label>
        <select name="metrica_5_id" class="form-select">
            <option value="">No usar</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Color</label>
        <input type="color" name="color_5" class="form-control form-control-color" value="#8b5cf6">
    </div>
    
    <div class="col-md-6">
        <label class="form-label">Métrica 6 (opcional)</label>
        <select name="metrica_6_id" class="form-select">
            <option value="">No usar</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Color</label>
        <input type="color" name="color_6" class="form-control form-control-color" value="#ec4899">
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
        <label class="form-label">Altura (px)</label>
        <input type="number" name="altura" class="form-control" value="400" min="250" max="600">
    </div>
    
    <div class="col-md-12">
        <label class="form-check form-switch">
            <input type="checkbox" name="mostrar_valores" class="form-check-input" checked>
            <span class="form-check-label">Mostrar valores sobre barras</span>
        </label>
    </div>
</div>
HTML;
    },
    
    'process' => function($post) {
        $data = [
            'metricas' => [],
            'periodos' => (int)($post['periodos'] ?? 6),
            'altura' => (int)($post['altura'] ?? 400),
            'mostrar_valores' => isset($post['mostrar_valores'])
        ];
        
        for ($i = 1; $i <= 6; $i++) {
            if (!empty($post["metrica_{$i}_id"])) {
                $data['metricas'][] = [
                    'id' => (int)$post["metrica_{$i}_id"],
                    'color' => sanitize($post["color_{$i}"] ?? '#3b82f6')
                ];
            }
        }
        
        return $data;
    },
    
    'load_config_js' => <<<'JS'
function(form, config) {
    if (config.metricas && Array.isArray(config.metricas)) {
        config.metricas.forEach((metrica, index) => {
            const num = index + 1;
            const selectMetrica = form.querySelector(`[name="metrica_${num}_id"]`);
            const inputColor = form.querySelector(`[name="color_${num}"]`);
            
            if (selectMetrica && metrica.id) selectMetrica.value = metrica.id;
            if (inputColor && metrica.color) inputColor.value = metrica.color;
        });
    }
    
    if (config.periodos) {
        const periodosSelect = form.querySelector('[name="periodos"]');
        if (periodosSelect) periodosSelect.value = config.periodos;
    }
    
    if (config.altura) {
        const alturaInput = form.querySelector('[name="altura"]');
        if (alturaInput) alturaInput.value = config.altura;
    }
    
    if (config.hasOwnProperty('mostrar_valores')) {
        const checkbox = form.querySelector('[name="mostrar_valores"]');
        if (checkbox) checkbox.checked = config.mostrar_valores;
    }
}
JS,
    
    'render' => function($config, $metrica_data, $area_color) {
        if (!isset($config['metricas']) || count($config['metricas']) < 3) {
            return '<div class="alert alert-warning m-3">Configura al menos 3 métricas</div>';
        }

        // Instanciar modelos con namespace correcto
        $valorMetricaModel = new \App\Models\ValorMetrica();
        $metricaModel = new \App\Models\Metrica();
        
        $periodos = (int)($config['periodos'] ?? 6);
        $altura = (int)($config['altura'] ?? 400);
        $mostrar_valores = $config['mostrar_valores'] ?? true;
        
        $series = [];
        $categorias = [];
        $colores = [];
        
        foreach ($config['metricas'] as $metrica_config) {
            $metrica_id = $metrica_config['id'];
            $color = $metrica_config['color'];
            
            $metrica_info = $metricaModel->find($metrica_id);
            $historico = $valorMetricaModel->getHistorico($metrica_id, $periodos);
            
            if (empty($historico)) continue;
            
            $valores = [];
            $mes_nombre = [1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun', 
                          7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'];
            
            if (empty($categorias)) {
                foreach ($historico as $dato) {
                    $categorias[] = $mes_nombre[$dato['periodo']] . ' ' . $dato['ejercicio'];
                }
            }
            
            foreach ($historico as $dato) {
                $valores[] = (float)$dato['valor'];
            }
            
            $series[] = [
                'name' => $metrica_info['nombre'],
                'data' => $valores
            ];
            
            $colores[] = $color;
        }
        
        if (empty($series)) {
            return '<div class="alert alert-info m-3">No hay datos históricos</div>';
        }
        
        $columnWidth = '70%';
        if (count($categorias) <= 3) $columnWidth = '50%';
        elseif (count($categorias) <= 6) $columnWidth = '60%';
        
        $chart_id = 'multi-bar-' . uniqid();
        
        ob_start();
        ?>
        <div class="multi-bar-chart p-3">
            <div id="<?php echo $chart_id; ?>" style="height: <?php echo $altura; ?>px;"></div>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const options = {
                series: <?php echo json_encode($series); ?>,
                chart: {
                    type: 'bar',
                    height: <?php echo $altura; ?>,
                    toolbar: { show: false },
                    background: 'transparent'
                },
                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        columnWidth: '<?php echo $columnWidth; ?>',
                        dataLabels: { position: 'top' }
                    }
                },
                dataLabels: {
                    enabled: <?php echo $mostrar_valores ? 'true' : 'false'; ?>,
                    formatter: val => val.toFixed(0),
                    offsetY: -20,
                    style: { fontSize: '10px', colors: ['#94a3b8'] }
                },
                xaxis: {
                    categories: <?php echo json_encode($categorias); ?>,
                    labels: { style: { colors: '#94a3b8', fontSize: '12px' } }
                },
                yaxis: {
                    labels: { style: { colors: '#94a3b8', fontSize: '12px' } }
                },
                colors: <?php echo json_encode($colores); ?>,
                grid: {
                    borderColor: 'rgba(148,163,184,0.1)',
                    strokeDashArray: 4
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left',
                    labels: { colors: '#94a3b8' }
                },
                tooltip: { theme: 'dark' }
            };
            
            const chartEl = document.querySelector("#<?php echo $chart_id; ?>");
            const chart = new ApexCharts(chartEl, options);
            chart.render();
            
            const resizeObserver = new ResizeObserver(() => chart.updateOptions({}, true, true));
            resizeObserver.observe(chartEl.closest('.grid-stack-item'));
        });
        </script>
        <?php
        return ob_get_clean();
    }
];