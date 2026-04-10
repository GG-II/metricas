<?php
/**
 * GRÁFICO: Barras Verticales
 * Gráfico de barras con ApexCharts
 */

return [
    // ==========================================
    // METADATA
    // ==========================================
    'meta' => [
        'id' => 'bar',
        'nombre' => 'Gráfico de Barras',
        'descripcion' => 'Barras verticales para comparar valores',
        'icono' => 'chart-bar',
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
        <label class="form-label">Color de las barras</label>
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
            'metrica_id' => (int)$post['metrica_id'],
            'color' => sanitize($post['color'] ?? '#3b82f6'),
            'periodos' => (int)($post['periodos'] ?? 6),
            'altura' => (int)($post['altura'] ?? 300),
            'mostrar_valores' => isset($post['mostrar_valores'])
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
        if (!$metrica_data) {
            return '<div class="alert alert-warning m-3">No hay datos disponibles</div>';
        }
        
        global $valorMetricaModel;
        
        $metrica_id = $config['metrica_id'];
        $periodos = (int)($config['periodos'] ?? 6);
        $color = $config['color'] ?? '#3b82f6';
        $altura = (int)($config['altura'] ?? 300);
        $mostrar_valores = $config['mostrar_valores'] ?? true;
        
        // Obtener histórico
        require_once BASE_PATH . '/models/ValorMetrica.php';
        $valorMetricaModel = new ValorMetrica();
        $historico = $valorMetricaModel->getHistorico($metrica_id, $periodos);
        
        if (empty($historico)) {
            return '<div class="alert alert-info m-3">No hay datos históricos disponibles</div>';
        }
        
        // Preparar datos para ApexCharts
        $categorias = [];
        $valores = [];
        
        // NO invertir - el historico ya viene ordenado correctamente (más antiguo primero)
        foreach ($historico as $dato) {
            $mes_nombre = [
                1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
                5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
                9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
            ];
            
            $categorias[] = $mes_nombre[$dato['periodo']] . ' ' . $dato['ejercicio'];
            $valores[] = (float)$dato['valor'];
        }
        
        // Ajustar ancho de columnas según cantidad de datos
        $columnWidth = '60%';
        if (count($valores) <= 3) {
            $columnWidth = '30%';
        } elseif (count($valores) <= 6) {
            $columnWidth = '50%';
        }
        
        $chart_id = 'bar-chart-' . uniqid();
        
        ob_start();
        ?>
        <div class="bar-chart-widget p-3">
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
                        fontSize: '12px',
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
                colors: ['<?php echo $color; ?>'],
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
        .bar-chart-widget {
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