<?php
/**
 * GRÁFICO: Bullet Chart
 * Gráfico compacto que muestra valor actual vs meta con rangos de rendimiento
 */

return [
    // ==========================================
    // METADATA
    // ==========================================
    'meta' => [
        'id' => 'bullet',
        'nombre' => 'Bullet Chart',
        'descripcion' => 'Valor vs meta con rangos de rendimiento',
        'icono' => 'layout-distribute-horizontal',
        'requiere_metricas' => 1,
        'version' => '1.0'
    ],

    // ==========================================
    // FORMULARIO DE CONFIGURACIÓN
    // ==========================================
    'form' => function() {
        return <<<'HTML'
<div class="row g-3">
    <div class="col-12">
        <div class="card-hint" style="padding: 0.5rem; background-color: rgba(59, 130, 246, 0.1); border-left: 3px solid #3b82f6; border-radius: 4px; color: #3b82f6;">
            <i class="ti ti-bulb me-1"></i>
            Compacto y eficiente para comparar valor actual vs meta con contexto de rangos
        </div>
    </div>

    <div class="col-12">
        <label class="form-label required">Métrica</label>
        <select name="metrica_id" class="form-select" required>
            <option value="">Seleccionar métrica...</option>
        </select>
        <div class="form-hint">Solo métricas con metas definidas</div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Período</label>
        <select name="periodo_id" class="form-select" required>
            <option value="">Seleccionar período...</option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Altura (px)</label>
        <input type="number" name="altura" class="form-control" value="100" min="80" max="200">
    </div>

    <div class="col-md-4">
        <label class="form-label">Rango bajo (%)</label>
        <input type="number" name="rango_bajo" class="form-control" value="60" min="0" max="100">
        <div class="form-hint text-danger">Malo</div>
    </div>

    <div class="col-md-4">
        <label class="form-label">Rango medio (%)</label>
        <input type="number" name="rango_medio" class="form-control" value="80" min="0" max="100">
        <div class="form-hint text-warning">Regular</div>
    </div>

    <div class="col-md-4">
        <label class="form-label">Rango alto (%)</label>
        <input type="number" name="rango_alto" class="form-control" value="100" min="0" max="150">
        <div class="form-hint text-success">Bueno</div>
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
            'periodo_id' => (int)$post['periodo_id'],
            'altura' => (int)($post['altura'] ?? 100),
            'rango_bajo' => (int)($post['rango_bajo'] ?? 60),
            'rango_medio' => (int)($post['rango_medio'] ?? 80),
            'rango_alto' => (int)($post['rango_alto'] ?? 100)
        ];
    },

    // ==========================================
    // CARGAR CONFIGURACIÓN (para edición)
    // ==========================================
    'load_config_js' => <<<'JS'
function(form, config) {
    form.querySelector('[name="metrica_id"]').value = config.metrica_id;
    form.querySelector('[name="periodo_id"]').value = config.periodo_id;
    form.querySelector('[name="altura"]').value = config.altura;
    form.querySelector('[name="rango_bajo"]').value = config.rango_bajo;
    form.querySelector('[name="rango_medio"]').value = config.rango_medio;
    form.querySelector('[name="rango_alto"]').value = config.rango_alto;
}
JS,

    // ==========================================
    // RENDERIZAR WIDGET
    // ==========================================
    'render' => function($config, $metrica_data, $area_color) {
        if (!isset($config['metrica_id']) || !isset($config['periodo_id'])) {
            return '<div class="alert alert-warning m-3">Configura la métrica y período</div>';
        }

        $valorMetricaModel = new \App\Models\ValorMetrica();
        $metaModel = new \App\Models\Meta();
        $metricaModel = new \App\Models\Metrica();
        $periodoModel = new \App\Models\Periodo();

        $metrica_id = $config['metrica_id'];
        $periodo_id = $config['periodo_id'];
        $altura = (int)($config['altura'] ?? 100);
        $rango_bajo = (int)($config['rango_bajo'] ?? 60);
        $rango_medio = (int)($config['rango_medio'] ?? 80);
        $rango_alto = (int)($config['rango_alto'] ?? 100);

        // Obtener información
        $metrica = $metricaModel->find($metrica_id);
        $periodo = $periodoModel->find($periodo_id);

        if (!$metrica || !$periodo) {
            return '<div class="alert alert-danger m-3">Métrica o período no encontrado</div>';
        }

        // Obtener valor actual
        $valor = $valorMetricaModel->getValor($metrica_id, $periodo_id);
        $valor_real = $valor ? ($metrica['tipo_valor'] === 'decimal' ? (float)$valor['valor_decimal'] : (int)$valor['valor_numero']) : 0;

        // Obtener meta
        $meta = $metaModel->getMetaAplicable($metrica_id, $periodo_id);

        if (!$meta) {
            return '<div class="alert alert-info m-3">No hay meta definida</div>';
        }

        $valor_objetivo = (float)$meta['valor_objetivo'];
        $cumplimiento = $metaModel->calcularCumplimiento($valor_real, $valor_objetivo, $meta['tipo_comparacion']);

        // Calcular valores de rangos
        $val_bajo = ($rango_bajo / 100) * $valor_objetivo;
        $val_medio = ($rango_medio / 100) * $valor_objetivo;
        $val_alto = ($rango_alto / 100) * $valor_objetivo;

        $chart_id = 'chart_' . uniqid();

        // Determinar color según cumplimiento
        if ($cumplimiento >= $rango_alto) {
            $color_valor = '#10b981';
            $estado = 'Excelente';
        } elseif ($cumplimiento >= $rango_medio) {
            $color_valor = '#f59e0b';
            $estado = 'Regular';
        } else {
            $color_valor = '#ef4444';
            $estado = 'Bajo';
        }

        $valor_formateado = $metrica['tipo_valor'] === 'decimal' ? number_format($valor_real, 2) : number_format($valor_real);
        $meta_formateada = $metrica['tipo_valor'] === 'decimal' ? number_format($valor_objetivo, 2) : number_format($valor_objetivo);

        return <<<HTML
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="text-muted small">{$metrica['nombre']}</div>
            <div class="badge bg-secondary">{$estado}</div>
        </div>

        <div id="{$chart_id}" style="height: {$altura}px;"></div>

        <div class="d-flex justify-content-between mt-2 small text-muted">
            <span>Actual: <strong>{$valor_formateado}</strong> {$metrica['unidad']}</span>
            <span>Meta: <strong>{$meta_formateada}</strong> {$metrica['unidad']}</span>
        </div>
    </div>
</div>
<script>
(function() {
    const options = {
        series: [{
            data: [{
                x: 'Rendimiento',
                y: {$valor_real},
                goals: [{
                    name: 'Meta',
                    value: {$valor_objetivo},
                    strokeWidth: 3,
                    strokeColor: '#94a3b8'
                }]
            }]
        }],
        chart: {
            type: 'bar',
            height: {$altura},
            sparkline: {
                enabled: true
            }
        },
        plotOptions: {
            bar: {
                horizontal: true,
                barHeight: '50%'
            }
        },
        colors: ['{$color_valor}'],
        annotations: {
            xaxis: [
                {
                    x: 0,
                    x2: {$val_bajo},
                    fillColor: '#fee2e2',
                    opacity: 0.3
                },
                {
                    x: {$val_bajo},
                    x2: {$val_medio},
                    fillColor: '#fef3c7',
                    opacity: 0.3
                },
                {
                    x: {$val_medio},
                    x2: {$val_alto},
                    fillColor: '#d1fae5',
                    opacity: 0.3
                }
            ]
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + ' {$metrica['unidad']}';
                }
            }
        }
    };

    const chart = new ApexCharts(document.querySelector('#{$chart_id}'), options);
    chart.render();
})();
</script>
HTML;
    }
];
