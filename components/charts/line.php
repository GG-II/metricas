<?php
/**
 * GRÁFICO: Líneas
 * Gráfico de tendencia con ApexCharts
 */

return [
    // ==========================================
    // METADATA
    // ==========================================
    'meta' => [
        'id' => 'line',
        'nombre' => 'Gráfico de Líneas',
        'descripcion' => 'Tendencia temporal con línea',
        'icono' => 'chart-line',
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
        <label class="form-label">Color de la línea</label>
        <input type="color" name="color" class="form-control form-control-color" value="#3b82f6">
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
        <input type="number" name="altura" class="form-control" value="300" min="200" max="600">
    </div>
    <div class="col-md-6">
        <label class="form-check form-switch mt-4">
            <input type="checkbox" name="mostrar_area" class="form-check-input" checked>
            <span class="form-check-label">Rellenar área bajo la línea</span>
        </label>
    </div>
    <div class="col-md-6">
        <label class="form-check form-switch mt-4">
            <input type="checkbox" name="mostrar_puntos" class="form-check-input" checked>
            <span class="form-check-label">Mostrar puntos en la línea</span>
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
            'periodos' => (int)($post['periodos'] ?? 6),
            'altura' => (int)($post['altura'] ?? 300),
            'mostrar_area' => isset($post['mostrar_area']),
            'mostrar_puntos' => isset($post['mostrar_puntos'])
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
    
    if (config.periodos) {
        const periodosSelect = form.querySelector('[name="periodos"]');
        if (periodosSelect) periodosSelect.value = config.periodos;
    }
    
    if (config.altura) {
        const alturaInput = form.querySelector('[name="altura"]');
        if (alturaInput) alturaInput.value = config.altura;
    }
    
    if (config.hasOwnProperty('mostrar_area')) {
        const checkbox = form.querySelector('[name="mostrar_area"]');
        if (checkbox) checkbox.checked = config.mostrar_area;
    }
    
    if (config.hasOwnProperty('mostrar_puntos')) {
        const checkbox = form.querySelector('[name="mostrar_puntos"]');
        if (checkbox) checkbox.checked = config.mostrar_puntos;
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
        
        global $valorMetricaModel;
        
        $metrica_id = $config['metrica_id'];
        $periodos = (int)($config['periodos'] ?? 6);
        $color = $config['color'] ?? '#3b82f6';
        $altura = (int)($config['altura'] ?? 300);
        $mostrar_area = $config['mostrar_area'] ?? true;
        $mostrar_puntos = $config['mostrar_puntos'] ?? true;
        
        // Obtener histórico
        require_once BASE_PATH . '/models/ValorMetrica.php';
        $valorMetricaModel = new ValorMetrica();
        $historico = $valorMetricaModel->getHistorico($metrica_id, $periodos);
        
        if (empty($historico)) {
            return '<div class="alert alert-info m-3">No hay datos históricos disponibles</div>';
        }
        
        // Preparar datos
        $categorias = [];
        $valores = [];
        
        // NO invertir - el historico ya viene ordenado correctamente
        foreach ($historico as $dato) {
            $mes_nombre = [
                1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
                5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
                9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
            ];
            
            $categorias[] = $mes_nombre[$dato['periodo']] . ' ' . $dato['ejercicio'];
            $valores[] = (float)$dato['valor'];
        }
        
        $chart_id = 'line-chart-' . uniqid();
        
        ob_start();
        ?>
        <div class="line-chart-widget p-3">
            <div id="<?php echo $chart_id; ?>" style="height: <?php echo $altura; ?>px;"></div>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const options = {
                series: [{
                    name: '<?php echo htmlspecialchars($metrica_data['nombre']); ?>',
                    data: <?php echo json_encode($valores); ?>
                }],
                chart: {
                    type: '<?php echo $mostrar_area ? 'area' : 'line'; ?>',
                    height: <?php echo $altura; ?>,
                    toolbar: {
                        show: false
                    },
                    background: 'transparent',
                    zoom: {
                        enabled: false
                    }
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                markers: {
                    size: <?php echo $mostrar_puntos ? '5' : '0'; ?>,
                    colors: ['<?php echo $color; ?>'],
                    strokeColors: '#1e293b',
                    strokeWidth: 2,
                    hover: {
                        size: 7
                    }
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'dark',
                        type: 'vertical',
                        opacityFrom: 0.5,
                        opacityTo: 0.1,
                        stops: [0, 100]
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
                colors: ['<?php echo $color; ?>'],
                grid: {
                    borderColor: 'rgba(148, 163, 184, 0.1)',
                    strokeDashArray: 4
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function (val) {
                            return val.toFixed(0) + ' <?php echo htmlspecialchars($metrica_data['unidad'] ?? ''); ?>';
                        }
                    }
                }
            };
            
            const chart = new ApexCharts(document.querySelector("#<?php echo $chart_id; ?>"), options);
            chart.render();
        });
        </script>
        
        <style>
        .line-chart-widget {
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