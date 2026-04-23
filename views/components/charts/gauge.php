<?php
/**
 * GRÁFICO: Gauge/Medidor
 * Muestra progreso/porcentaje tipo velocímetro
 */

return [
    'meta' => [
        'id' => 'gauge',
        'nombre' => 'Medidor/Gauge',
        'descripcion' => 'Velocímetro para mostrar % de cumplimiento',
        'icono' => 'gauge',
        'requiere_metricas' => 1,
        'version' => '1.0'
    ],
    
    'form' => function() {
        return <<<'HTML'
<div class="row g-3">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="ti ti-gauge me-2"></i>
            Ideal para mostrar porcentajes: SLA, disponibilidad, cumplimiento de objetivos
        </div>
    </div>
    
    <div class="col-md-6">
        <label class="form-label required">Métrica principal</label>
        <select name="metrica_id" class="form-select" required>
            <option value="">Seleccionar métrica...</option>
        </select>
    </div>
    
    <div class="col-md-6">
        <label class="form-label required">Valor objetivo/meta</label>
        <input type="number" name="objetivo" class="form-control" value="100" min="0" step="0.1" required>
        <small class="form-hint">El 100% del gauge</small>
    </div>
    
    <div class="col-md-6">
        <label class="form-label">Color del medidor</label>
        <select name="color_scheme" class="form-select">
            <option value="blue">Azul (Neutral)</option>
            <option value="green" selected>Verde (Éxito)</option>
            <option value="red">Rojo (Alerta)</option>
            <option value="gradient">Degradado (Bajo→Alto)</option>
        </select>
    </div>
    
    <div class="col-md-6">
        <label class="form-label">Altura (px)</label>
        <input type="number" name="altura" class="form-control" value="280" min="200" max="400">
    </div>
    
    <div class="col-md-12">
        <label class="form-label">Texto descriptivo (opcional)</label>
        <input type="text" name="subtitulo" class="form-control" placeholder="Ej: Cumplimiento mensual">
    </div>
    
    <div class="col-md-6">
        <label class="form-check form-switch mt-3">
            <input type="checkbox" name="mostrar_porcentaje" class="form-check-input" checked>
            <span class="form-check-label">Mostrar porcentaje</span>
        </label>
    </div>
    
    <div class="col-md-6">
        <label class="form-check form-switch mt-3">
            <input type="checkbox" name="animar" class="form-check-input" checked>
            <span class="form-check-label">Animación al cargar</span>
        </label>
    </div>
</div>
HTML;
    },
    
    'process' => function($post) {
        return [
            'metrica_id' => (int)$post['metrica_id'],
            'objetivo' => (float)$post['objetivo'],
            'color_scheme' => sanitize($post['color_scheme'] ?? 'green'),
            'altura' => (int)($post['altura'] ?? 280),
            'subtitulo' => sanitize($post['subtitulo'] ?? ''),
            'mostrar_porcentaje' => isset($post['mostrar_porcentaje']),
            'animar' => isset($post['animar'])
        ];
    },
    
    'load_config_js' => <<<'JS'
function(form, config) {
    if (config.metrica_id) {
        const select = form.querySelector('[name="metrica_id"]');
        if (select) select.value = config.metrica_id;
    }
    
    if (config.objetivo) {
        const input = form.querySelector('[name="objetivo"]');
        if (input) input.value = config.objetivo;
    }
    
    if (config.color_scheme) {
        const select = form.querySelector('[name="color_scheme"]');
        if (select) select.value = config.color_scheme;
    }
    
    if (config.altura) {
        const input = form.querySelector('[name="altura"]');
        if (input) input.value = config.altura;
    }
    
    if (config.subtitulo) {
        const input = form.querySelector('[name="subtitulo"]');
        if (input) input.value = config.subtitulo;
    }
    
    if (config.hasOwnProperty('mostrar_porcentaje')) {
        const checkbox = form.querySelector('[name="mostrar_porcentaje"]');
        if (checkbox) checkbox.checked = config.mostrar_porcentaje;
    }
    
    if (config.hasOwnProperty('animar')) {
        const checkbox = form.querySelector('[name="animar"]');
        if (checkbox) checkbox.checked = config.animar;
    }
}
JS,
    
    'render' => function($config, $metrica_data, $area_color) {
        if (!$metrica_data) {
            return '<div class="alert alert-warning m-3">No hay datos disponibles</div>';
        }
        
        $valor_actual = (float)$metrica_data['valor'];
        $objetivo = (float)($config['objetivo'] ?? 100);
        $porcentaje = ($objetivo > 0) ? ($valor_actual / $objetivo) * 100 : 0;
        $porcentaje = min(100, max(0, $porcentaje)); // Limitar entre 0-100
        
        $altura = (int)($config['altura'] ?? 280);
        $subtitulo = $config['subtitulo'] ?? '';
        $mostrar_porcentaje = $config['mostrar_porcentaje'] ?? true;
        $animar = $config['animar'] ?? true;
        
        // Colores según esquema
        $color_schemes = [
            'blue' => '#3b82f6',
            'green' => '#10b981',
            'red' => '#ef4444',
            'gradient' => null // Se maneja diferente
        ];
        
        $color_scheme = $config['color_scheme'] ?? 'green';
        $color = $color_schemes[$color_scheme] ?? '#3b82f6';
        
        $chart_id = 'gauge-' . uniqid();
        
        ob_start();
        ?>
        <div class="gauge-widget p-3">
            <div id="<?php echo $chart_id; ?>" style="height: <?php echo $altura; ?>px;"></div>
            <?php if ($subtitulo): ?>
                <div class="text-center mt-2 text-muted">
                    <small><?php echo htmlspecialchars($subtitulo); ?></small>
                </div>
            <?php endif; ?>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const options = {
                series: [<?php echo number_format($porcentaje, 1); ?>],
                chart: {
                    type: 'radialBar',
                    height: <?php echo $altura; ?>,
                    background: 'transparent',
                    animations: {
                        enabled: <?php echo $animar ? 'true' : 'false'; ?>,
                        speed: 1500,
                        animateGradually: {
                            enabled: true,
                            delay: 150
                        }
                    }
                },
                plotOptions: {
                    radialBar: {
                        startAngle: -135,
                        endAngle: 135,
                        hollow: {
                            margin: 0,
                            size: '70%',
                            background: 'transparent'
                        },
                        track: {
                            background: 'rgba(148, 163, 184, 0.1)',
                            strokeWidth: '100%',
                            margin: 5
                        },
                        dataLabels: {
                            show: true,
                            name: {
                                show: true,
                                fontSize: '16px',
                                fontWeight: 600,
                                color: '#94a3b8',
                                offsetY: -10
                            },
                            value: {
                                show: <?php echo $mostrar_porcentaje ? 'true' : 'false'; ?>,
                                fontSize: '36px',
                                fontWeight: 700,
                                color: '#fff',
                                offsetY: 10,
                                formatter: function(val) {
                                    return val.toFixed(1) + '%';
                                }
                            }
                        }
                    }
                },
                fill: {
                    <?php if ($color_scheme === 'gradient'): ?>
                    type: 'gradient',
                    gradient: {
                        shade: 'dark',
                        type: 'horizontal',
                        shadeIntensity: 0.5,
                        gradientToColors: ['#10b981'],
                        inverseColors: false,
                        opacityFrom: 1,
                        opacityTo: 1,
                        stops: [0, 100],
                        colorStops: [
                            { offset: 0, color: '#ef4444', opacity: 1 },
                            { offset: 50, color: '#f59e0b', opacity: 1 },
                            { offset: 100, color: '#10b981', opacity: 1 }
                        ]
                    }
                    <?php else: ?>
                    colors: ['<?php echo $color; ?>']
                    <?php endif; ?>
                },
                stroke: {
                    lineCap: 'round'
                },
                labels: ['<?php echo htmlspecialchars($metrica_data['nombre']); ?>']
            };
            
            const chartEl = document.querySelector("#<?php echo $chart_id; ?>");
            const chart = new ApexCharts(chartEl, options);
            chart.render();
            
            const resizeObserver = new ResizeObserver(() => chart.updateOptions({}, true, true));
            resizeObserver.observe(chartEl.closest('.grid-stack-item'));
        });
        </script>
        
        <style>
        .gauge-widget {
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