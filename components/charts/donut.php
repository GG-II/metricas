<?php
/**
 * GRÁFICO: Donut/Pie Chart (Dona)
 * Muestra distribución porcentual
 */

return [
    'meta' => [
        'id' => 'donut',
        'nombre' => 'Gráfico de Dona',
        'descripcion' => 'Distribución porcentual de métricas',
        'icono' => 'chart-donut',
        'requiere_metricas' => 2,
        'version' => '1.0'
    ],
    
    'form' => function() {
        return <<<'HTML'
<div class="row g-3">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="ti ti-chart-donut me-2"></i>
            Ideal para 2-6 métricas que sumen un total
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
        <label class="form-label">Métrica 3 (opcional)</label>
        <select name="metrica_3_id" class="form-select">
            <option value="">No usar</option>
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
        <label class="form-label">Altura (px)</label>
        <input type="number" name="altura" class="form-control" value="350" min="250" max="500">
    </div>
    
    <div class="col-md-6">
        <label class="form-check form-switch mt-4">
            <input type="checkbox" name="mostrar_porcentaje" class="form-check-input" checked>
            <span class="form-check-label">Mostrar porcentajes</span>
        </label>
    </div>
</div>
HTML;
    },
    
    'process' => function($post) {
        $data = [
            'metricas' => [],
            'altura' => (int)($post['altura'] ?? 350),
            'mostrar_porcentaje' => isset($post['mostrar_porcentaje'])
        ];
        
        for ($i = 1; $i <= 4; $i++) {
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
    
    if (config.altura) {
        const alturaInput = form.querySelector('[name="altura"]');
        if (alturaInput) alturaInput.value = config.altura;
    }
    
    if (config.hasOwnProperty('mostrar_porcentaje')) {
        const checkbox = form.querySelector('[name="mostrar_porcentaje"]');
        if (checkbox) checkbox.checked = config.mostrar_porcentaje;
    }
}
JS,
    
    'render' => function($config, $metrica_data, $area_color) {
        if (!isset($config['metricas']) || count($config['metricas']) < 2) {
            return '<div class="alert alert-warning m-3">Configura al menos 2 métricas</div>';
        }
        
        global $valorMetricaModel, $metricaModel, $periodo;
        require_once BASE_PATH . '/models/ValorMetrica.php';
        require_once BASE_PATH . '/models/Metrica.php';
        
        $valorMetricaModel = new ValorMetrica();
        $metricaModel = new Metrica();
        
        $altura = (int)($config['altura'] ?? 350);
        $mostrar_porcentaje = $config['mostrar_porcentaje'] ?? true;
        
        $labels = [];
        $values = [];
        $colors = [];
        
        foreach ($config['metricas'] as $metrica_config) {
            $metrica_id = $metrica_config['id'];
            $color = $metrica_config['color'];
            
            $metrica_info = $metricaModel->getById($metrica_id);
            $datos = $valorMetricaModel->getByMetricaYPeriodo($metrica_id, $periodo['id']);
            
            if (!$datos) continue;
            
            $labels[] = $metrica_info['nombre'];
            $values[] = (float)$datos['valor'];
            $colors[] = $color;
        }
        
        if (empty($values)) {
            return '<div class="alert alert-info m-3">No hay datos para el período actual</div>';
        }
        
        $chart_id = 'donut-' . uniqid();
        
        ob_start();
        ?>
        <div class="donut-chart p-3">
            <div id="<?php echo $chart_id; ?>" style="height: <?php echo $altura; ?>px;"></div>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const options = {
                series: <?php echo json_encode($values); ?>,
                chart: {
                    type: 'donut',
                    height: <?php echo $altura; ?>,
                    background: 'transparent'
                },
                labels: <?php echo json_encode($labels); ?>,
                colors: <?php echo json_encode($colors); ?>,
                dataLabels: {
                    enabled: <?php echo $mostrar_porcentaje ? 'true' : 'false'; ?>,
                    formatter: function(val) { return val.toFixed(1) + '%'; },
                    style: { fontSize: '14px', colors: ['#fff'] }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    fontSize: '16px',
                                    color: '#94a3b8',
                                    formatter: () => <?php echo array_sum($values); ?>.toFixed(0)
                                }
                            }
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    labels: { colors: '#94a3b8' }
                },
                tooltip: {
                    theme: 'dark',
                    y: { formatter: val => val.toFixed(0) }
                }
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