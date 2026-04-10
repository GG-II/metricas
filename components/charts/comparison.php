<?php
/**
 * GRÁFICO: Comparación
 * Compara 2 métricas con barras agrupadas
 */

return [
    // ==========================================
    // METADATA
    // ==========================================
    'meta' => [
        'id' => 'comparison',
        'nombre' => 'Comparación',
        'descripcion' => 'Compara dos métricas lado a lado',
        'icono' => 'arrows-left-right',
        'requiere_metricas' => 2,
        'version' => '1.0'
    ],
    
    // ==========================================
    // FORMULARIO DE CONFIGURACIÓN
    // ==========================================
    'form' => function() {
        return <<<'HTML'
<div class="row g-3">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="ti ti-info-circle me-2"></i>
            Selecciona dos métricas para comparar visualmente
        </div>
    </div>
    
    <div class="col-md-6">
        <label class="form-label required">Primera métrica</label>
        <select name="metrica_1_id" class="form-select" required>
            <option value="">Seleccionar métrica...</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Color primera métrica</label>
        <input type="color" name="color_1" class="form-control form-control-color" value="#3b82f6">
    </div>
    
    <div class="col-md-6">
        <label class="form-label required">Segunda métrica</label>
        <select name="metrica_2_id" class="form-select" required>
            <option value="">Seleccionar métrica...</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Color segunda métrica</label>
        <input type="color" name="color_2" class="form-control form-control-color" value="#10b981">
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
        <label class="form-label">Altura del gráfico (px)</label>
        <input type="number" name="altura" class="form-control" value="350" min="200" max="600">
    </div>
    
    <div class="col-md-12">
        <label class="form-check form-switch">
            <input type="checkbox" name="mostrar_valores" class="form-check-input" checked>
            <span class="form-check-label">Mostrar valores sobre las barras</span>
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
            'metrica_1_id' => (int)$post['metrica_1_id'],
            'metrica_2_id' => (int)$post['metrica_2_id'],
            'color_1' => sanitize($post['color_1'] ?? '#3b82f6'),
            'color_2' => sanitize($post['color_2'] ?? '#10b981'),
            'periodos' => (int)($post['periodos'] ?? 6),
            'altura' => (int)($post['altura'] ?? 350),
            'mostrar_valores' => isset($post['mostrar_valores'])
        ];
    },
    
    // ==========================================
    // CARGAR CONFIGURACIÓN EXISTENTE (JS)
    // ==========================================
    'load_config_js' => <<<'JS'
function(form, config) {
    const metrica1Select = form.querySelector('[name="metrica_1_id"]');
    if (metrica1Select && config.metrica_1_id) {
        metrica1Select.value = config.metrica_1_id;
    }
    
    const metrica2Select = form.querySelector('[name="metrica_2_id"]');
    if (metrica2Select && config.metrica_2_id) {
        metrica2Select.value = config.metrica_2_id;
    }
    
    if (config.color_1) {
        const color1Input = form.querySelector('[name="color_1"]');
        if (color1Input) color1Input.value = config.color_1;
    }
    
    if (config.color_2) {
        const color2Input = form.querySelector('[name="color_2"]');
        if (color2Input) color2Input.value = config.color_2;
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
    
    // ==========================================
    // RENDERIZAR WIDGET
    // ==========================================
    'render' => function($config, $metrica_data, $area_color) {
        global $valorMetricaModel, $metricaModel;
        
        // Validar que tengamos las 2 métricas
        if (!isset($config['metrica_1_id']) || !isset($config['metrica_2_id'])) {
            return '<div class="alert alert-warning m-3">Configura las 2 métricas a comparar</div>';
        }
        
        require_once BASE_PATH . '/models/ValorMetrica.php';
        require_once BASE_PATH . '/models/Metrica.php';
        
        $valorMetricaModel = new ValorMetrica();
        $metricaModel = new Metrica();
        
        $metrica_1_id = $config['metrica_1_id'];
        $metrica_2_id = $config['metrica_2_id'];
        $periodos = (int)($config['periodos'] ?? 6);
        $color_1 = $config['color_1'] ?? '#3b82f6';
        $color_2 = $config['color_2'] ?? '#10b981';
        $altura = (int)($config['altura'] ?? 350);
        $mostrar_valores = $config['mostrar_valores'] ?? true;
        
        // Obtener históricos
        $historico_1 = $valorMetricaModel->getHistorico($metrica_1_id, $periodos);
        $historico_2 = $valorMetricaModel->getHistorico($metrica_2_id, $periodos);
        
        if (empty($historico_1) && empty($historico_2)) {
            return '<div class="alert alert-info m-3">No hay datos históricos disponibles</div>';
        }
        
        // Obtener nombres de métricas
        $metrica_1_info = $metricaModel->getById($metrica_1_id);
        $metrica_2_info = $metricaModel->getById($metrica_2_id);
        
        $nombre_1 = $metrica_1_info['nombre'] ?? 'Métrica 1';
        $nombre_2 = $metrica_2_info['nombre'] ?? 'Métrica 2';
        
        // Preparar datos (NO usar array_reverse - ya viene ordenado)
        $categorias = [];
        $valores_1 = [];
        $valores_2 = [];
        
        // Crear un mapa de períodos para sincronizar ambas métricas
        $periodos_map = [];
        
        foreach ($historico_1 as $dato) {
            $key = $dato['ejercicio'] . '-' . $dato['periodo'];
            $periodos_map[$key] = true;
        }
        
        foreach ($historico_2 as $dato) {
            $key = $dato['ejercicio'] . '-' . $dato['periodo'];
            $periodos_map[$key] = true;
        }
        
        // Ordenar períodos
        ksort($periodos_map);
        
        $mes_nombre = [
            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
        ];
        
        // Crear arrays indexados por período
        $map_1 = [];
        $map_2 = [];
        
        foreach ($historico_1 as $dato) {
            $key = $dato['ejercicio'] . '-' . $dato['periodo'];
            $map_1[$key] = (float)$dato['valor'];
        }
        
        foreach ($historico_2 as $dato) {
            $key = $dato['ejercicio'] . '-' . $dato['periodo'];
            $map_2[$key] = (float)$dato['valor'];
        }
        
        // Construir series
        foreach (array_keys($periodos_map) as $key) {
            list($ejercicio, $periodo) = explode('-', $key);
            
            $categorias[] = $mes_nombre[(int)$periodo] . ' ' . $ejercicio;
            $valores_1[] = $map_1[$key] ?? 0;
            $valores_2[] = $map_2[$key] ?? 0;
        }
        
        // Ajustar ancho según cantidad de datos (LECCIÓN APRENDIDA)
        $columnWidth = '60%';
        if (count($categorias) <= 3) {
            $columnWidth = '40%';
        } elseif (count($categorias) <= 6) {
            $columnWidth = '55%';
        }
        
        $chart_id = 'comparison-chart-' . uniqid();
        
        ob_start();
        ?>
        <div class="comparison-chart-widget p-3">
            <div id="<?php echo $chart_id; ?>" style="height: <?php echo $altura; ?>px;"></div>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const options = {
                series: [
                    {
                        name: '<?php echo htmlspecialchars($nombre_1); ?>',
                        data: <?php echo json_encode($valores_1); ?>
                    },
                    {
                        name: '<?php echo htmlspecialchars($nombre_2); ?>',
                        data: <?php echo json_encode($valores_2); ?>
                    }
                ],
                chart: {
                    type: 'bar',
                    height: <?php echo $altura; ?>,
                    toolbar: {
                        show: false
                    },
                    background: 'transparent'
                },
                plotOptions: {
                    bar: {
                        borderRadius: 8,
                        dataLabels: {
                            position: 'top'
                        },
                        columnWidth: '<?php echo $columnWidth; ?>'
                    }
                },
                dataLabels: {
                    enabled: <?php echo $mostrar_valores ? 'true' : 'false'; ?>,
                    formatter: function (val) {
                        return val.toFixed(0);
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '11px',
                        colors: ['#94a3b8']
                    }
                },
                xaxis: {
                    categories: <?php echo json_encode($categorias); ?>,
                    labels: {
                        style: {
                            colors: '#94a3b8',
                            fontSize: '12px'
                        }
                    },
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#94a3b8',
                            fontSize: '12px'
                        }
                    }
                },
                colors: ['<?php echo $color_1; ?>', '<?php echo $color_2; ?>'],
                grid: {
                    borderColor: 'rgba(148, 163, 184, 0.1)',
                    strokeDashArray: 4,
                    xaxis: {
                        lines: {
                            show: false
                        }
                    },
                    yaxis: {
                        lines: {
                            show: true
                        }
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left',
                    labels: {
                        colors: '#94a3b8'
                    },
                    markers: {
                        width: 12,
                        height: 12,
                        radius: 3
                    }
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function (val) {
                            return val.toFixed(0);
                        }
                    }
                }
            };
            
            const chartElement = document.querySelector("#<?php echo $chart_id; ?>");
            const chart = new ApexCharts(chartElement, options);
            chart.render();
            
            // LECCIÓN APRENDIDA: Auto-resize con ResizeObserver
            const resizeObserver = new ResizeObserver(() => {
                chart.updateOptions({}, true, true);
            });
            resizeObserver.observe(chartElement.closest('.grid-stack-item'));
        });
        </script>
        
        <style>
        .comparison-chart-widget {
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        </style>
        <?php
        return ob_get_clean();
    }
];