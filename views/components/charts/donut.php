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
    
    'render' => function($config, $metrica_data, $area_color, $periodo = null) {
        // Debug: verificar configuración
        if (!isset($config['metricas'])) {
            return '<div class="alert alert-danger m-3">Error: No se encontró configuración de métricas</div>';
        }

        if (!is_array($config['metricas']) || count($config['metricas']) < 2) {
            return '<div class="alert alert-warning m-3">Configura al menos 2 métricas. Actualmente hay ' . (is_array($config['metricas']) ? count($config['metricas']) : '0') . ' métrica(s).</div>';
        }

        if (!$periodo) {
            return '<div class="alert alert-warning m-3">Selecciona un período en el dashboard</div>';
        }

        // Instanciar modelos con namespace correcto
        $valorMetricaModel = new \App\Models\ValorMetrica();
        $metricaModel = new \App\Models\Metrica();

        $altura = (int)($config['altura'] ?? 350);
        $mostrar_porcentaje = $config['mostrar_porcentaje'] ?? true;

        $labels = [];
        $values = [];
        $colors = [];
        $errores = [];

        foreach ($config['metricas'] as $idx => $metrica_config) {
            if (!isset($metrica_config['id'])) {
                $errores[] = "Métrica #" . ($idx + 1) . ": sin ID";
                continue;
            }

            $metrica_id = $metrica_config['id'];
            $color = $metrica_config['color'] ?? '#3b82f6';

            $metrica_info = $metricaModel->find($metrica_id);
            if (!$metrica_info) {
                $errores[] = "Métrica ID {$metrica_id}: no encontrada";
                continue;
            }

            $datos = $valorMetricaModel->getValor($metrica_id, $periodo['id']);

            if (!$datos) {
                $errores[] = "{$metrica_info['nombre']}: sin datos para este período";
                continue;
            }

            $valor = $metrica_info['tipo_valor'] === 'decimal'
                ? (float)$datos['valor_decimal']
                : (float)$datos['valor_numero'];

            $labels[] = $metrica_info['nombre'];
            $values[] = $valor;
            $colors[] = $color;
        }

        if (empty($values)) {
            $msg = 'No hay datos para mostrar.';
            if (!empty($errores)) {
                $msg .= '<br><small>' . implode('<br>', $errores) . '</small>';
            }
            return '<div class="alert alert-info m-3">' . $msg . '</div>';
        }
        
        $chart_id = 'donut-' . uniqid();
        
        $chart_id = 'donut-' . uniqid();
        $valores_json = json_encode($values);
        $labels_json = json_encode($labels);
        $colors_json = json_encode($colors);
        $total = array_sum($values);

        ob_start();
        ?>
        <div class="donut-chart-widget p-3">
            <div id="<?php echo $chart_id; ?>" style="height: <?php echo $altura; ?>px;"></div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const options = {
                series: <?php echo $valores_json; ?>,
                chart: {
                    type: 'donut',
                    height: <?php echo $altura; ?>,
                    toolbar: { show: false },
                    background: 'transparent'
                },
                labels: <?php echo $labels_json; ?>,
                colors: <?php echo $colors_json; ?>,
                dataLabels: {
                    enabled: <?php echo $mostrar_porcentaje ? 'true' : 'false'; ?>,
                    formatter: function(val) {
                        return val.toFixed(1) + '%';
                    },
                    style: {
                        fontSize: '14px',
                        colors: ['#fff']
                    }
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
                                    formatter: function() {
                                        return <?php echo $total; ?>;
                                    }
                                }
                            }
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    labels: {
                        colors: '#94a3b8'
                    }
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function(val) {
                            return val.toFixed(0);
                        }
                    }
                }
            };

            const chartEl = document.querySelector('#<?php echo $chart_id; ?>');
            const chart = new ApexCharts(chartEl, options);
            chart.render();
        });
        </script>
        <?php
        return ob_get_clean();
    }
];