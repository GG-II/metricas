<?php
/**
 * GRÁFICO: Gauge con Meta
 * Medidor circular mostrando porcentaje de cumplimiento de meta
 */

return [
    // ==========================================
    // METADATA
    // ==========================================
    'meta' => [
        'id' => 'gauge_with_goal',
        'nombre' => 'Gauge con Meta',
        'descripcion' => 'Medidor circular de cumplimiento',
        'icono' => 'gauge',
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
            Muestra el cumplimiento de meta del período actual en un medidor circular
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
        <input type="number" name="altura" class="form-control" value="300" min="200" max="600">
    </div>

    <div class="col-md-6">
        <label class="form-label">Color verde (cumple)</label>
        <input type="color" name="color_ok" class="form-control form-control-color" value="#10b981">
    </div>

    <div class="col-md-6">
        <label class="form-label">Color rojo (no cumple)</label>
        <input type="color" name="color_fail" class="form-control form-control-color" value="#ef4444">
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
            'altura' => (int)($post['altura'] ?? 300),
            'color_ok' => sanitize($post['color_ok'] ?? '#10b981'),
            'color_fail' => sanitize($post['color_fail'] ?? '#ef4444')
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
    form.querySelector('[name="color_ok"]').value = config.color_ok;
    form.querySelector('[name="color_fail"]').value = config.color_fail;
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
        $altura = (int)($config['altura'] ?? 300);
        $color_ok = $config['color_ok'] ?? '#10b981';
        $color_fail = $config['color_fail'] ?? '#ef4444';

        // Obtener información de métrica y período
        $metrica = $metricaModel->find($metrica_id);
        $periodo = $periodoModel->find($periodo_id);

        if (!$metrica || !$periodo) {
            return '<div class="alert alert-danger m-3">Métrica o período no encontrado</div>';
        }

        // Obtener valor actual
        $valor = $valorMetricaModel->getValor($metrica_id, $periodo_id);
        $valor_real = $valor ? ($metrica['tipo_valor'] === 'decimal' ? (float)$valor['valor_decimal'] : (int)$valor['valor_numero']) : 0;

        // Obtener meta aplicable
        $meta = $metaModel->getMetaAplicable($metrica_id, $periodo_id);

        if (!$meta) {
            return '<div class="alert alert-info m-3">No hay meta definida para esta métrica en este período</div>';
        }

        $valor_objetivo = (float)$meta['valor_objetivo'];

        // Calcular cumplimiento
        $cumplimiento = $metaModel->calcularCumplimiento($valor_real, $valor_objetivo, $meta['tipo_comparacion']);
        $cumplimiento_display = round($cumplimiento, 1);

        // Determinar color según cumplimiento
        $color_gauge = $cumplimiento >= 100 ? $color_ok : $color_fail;

        $chart_id = 'chart_' . uniqid();

        return <<<HTML
<div id="{$chart_id}" style="height: {$altura}px;"></div>
<div class="text-center mt-2">
    <div class="h3 mb-0">{$valor_real} <span class="text-muted">/ {$valor_objetivo}</span></div>
    <div class="text-muted">{$metrica['unidad']}</div>
</div>
<script>
(function() {
    const options = {
        series: [{$cumplimiento_display}],
        chart: {
            type: 'radialBar',
            height: {$altura},
            fontFamily: 'inherit'
        },
        plotOptions: {
            radialBar: {
                hollow: {
                    size: '65%'
                },
                dataLabels: {
                    name: {
                        fontSize: '16px',
                        color: '#64748b',
                        offsetY: -10
                    },
                    value: {
                        fontSize: '36px',
                        fontWeight: 600,
                        color: '{$color_gauge}',
                        formatter: function(val) {
                            return val.toFixed(1) + '%';
                        }
                    }
                }
            }
        },
        colors: ['{$color_gauge}'],
        labels: ['Cumplimiento']
    };

    const chart = new ApexCharts(document.querySelector('#{$chart_id}'), options);
    chart.render();
})();
</script>
HTML;
    }
];
