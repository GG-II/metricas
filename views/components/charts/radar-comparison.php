<?php
/**
 * GRÁFICO: Radar Comparison
 * Compara múltiples métricas en formato radar para 1-2 períodos
 */

return [
    'meta' => [
        'id' => 'radar_comparison',
        'nombre' => 'Radar de Comparación',
        'descripcion' => 'Compara perfiles de métricas en formato radar',
        'icono' => 'chart-radar',
        'requiere_metricas' => 3,
        'version' => '1.0'
    ],

    'form' => function() {
        return <<<'HTML'
<div class="row g-3">
    <div class="col-12">
        <div class="card-hint" style="padding: 0.5rem; background-color: rgba(59, 130, 246, 0.1); border-left: 3px solid #3b82f6; border-radius: 4px; color: #3b82f6;">
            <i class="ti ti-bulb me-1"></i>
            Compara el perfil de múltiples métricas entre 1 o 2 períodos
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label required">Período 1</label>
        <select name="periodo_1" class="form-select" required>
            <option value="">Seleccionar...</option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Período 2 (opcional)</label>
        <select name="periodo_2" class="form-select">
            <option value="">Ninguno</option>
        </select>
        <div class="form-hint">Para comparar 2 períodos</div>
    </div>

    <div class="col-md-6">
        <label class="form-label required">Métrica 1</label>
        <select name="metrica_1" class="form-select" required>
            <option value="">Seleccionar...</option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label required">Métrica 2</label>
        <select name="metrica_2" class="form-select" required>
            <option value="">Seleccionar...</option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label required">Métrica 3</label>
        <select name="metrica_3" class="form-select" required>
            <option value="">Seleccionar...</option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Métrica 4 (opcional)</label>
        <select name="metrica_4" class="form-select">
            <option value="">Ninguna</option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Métrica 5 (opcional)</label>
        <select name="metrica_5" class="form-select">
            <option value="">Ninguna</option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Altura (px)</label>
        <input type="number" name="altura" class="form-control" value="400" min="300" max="600">
    </div>
</div>
HTML;
    },

    'process' => function($post) {
        $metricas = [];
        for ($i = 1; $i <= 5; $i++) {
            if (!empty($post["metrica_$i"])) {
                $metricas[] = (int)$post["metrica_$i"];
            }
        }

        $periodos = [(int)$post['periodo_1']];
        if (!empty($post['periodo_2'])) {
            $periodos[] = (int)$post['periodo_2'];
        }

        return [
            'metricas' => $metricas,
            'periodos' => $periodos,
            'altura' => (int)($post['altura'] ?? 400)
        ];
    },

    'load_config_js' => <<<'JS'
function(form, config) {
    form.querySelector('[name="periodo_1"]').value = config.periodos[0];
    if (config.periodos.length > 1) {
        form.querySelector('[name="periodo_2"]').value = config.periodos[1];
    }
    for (let i = 0; i < config.metricas.length; i++) {
        const select = form.querySelector(`[name="metrica_${i+1}"]`);
        if (select) select.value = config.metricas[i];
    }
    form.querySelector('[name="altura"]').value = config.altura;
}
JS,

    'render' => function($config, $metrica_data, $area_color) {
        if (empty($config['metricas']) || count($config['metricas']) < 3) {
            return '<div class="alert alert-warning m-3">Selecciona al menos 3 métricas</div>';
        }

        if (empty($config['periodos'])) {
            return '<div class="alert alert-warning m-3">Selecciona al menos 1 período</div>';
        }

        $valorMetricaModel = new \App\Models\ValorMetrica();
        $metricaModel = new \App\Models\Metrica();
        $periodoModel = new \App\Models\Periodo();

        $altura = (int)($config['altura'] ?? 400);

        $categorias = [];
        $series = [];

        // Obtener nombres de métricas para categorías
        foreach ($config['metricas'] as $metrica_id) {
            $metrica = $metricaModel->find($metrica_id);
            if ($metrica) {
                $categorias[] = $metrica['nombre'];
            }
        }

        // Obtener datos por período
        foreach ($config['periodos'] as $periodo_id) {
            $periodo = $periodoModel->find($periodo_id);
            if (!$periodo) continue;

            $valores = [];
            foreach ($config['metricas'] as $metrica_id) {
                $metrica = $metricaModel->find($metrica_id);
                $valor = $valorMetricaModel->getValor($metrica_id, $periodo_id);
                $valor_num = $valor ? ($metrica['tipo_valor'] === 'decimal' ? (float)$valor['valor_decimal'] : (int)$valor['valor_numero']) : 0;
                $valores[] = $valor_num;
            }

            $series[] = [
                'name' => $periodo['nombre'],
                'data' => $valores
            ];
        }

        if (empty($series)) {
            return '<div class="alert alert-info m-3">No hay datos disponibles</div>';
        }

        $chart_id = 'chart_' . uniqid();
        $series_json = json_encode($series);
        $categorias_json = json_encode($categorias);

        return <<<HTML
<div id="{$chart_id}" style="height: {$altura}px;"></div>
<script>
(function() {
    const options = {
        series: {$series_json},
        chart: {
            type: 'radar',
            height: {$altura},
            fontFamily: 'inherit',
            toolbar: { show: false }
        },
        colors: ['#3b82f6', '#10b981'],
        stroke: {
            width: 2
        },
        fill: {
            opacity: 0.2
        },
        markers: {
            size: 4
        },
        xaxis: {
            categories: {$categorias_json}
        },
        yaxis: {
            show: false
        },
        legend: {
            position: 'top',
            horizontalAlign: 'left'
        }
    };

    const chart = new ApexCharts(document.querySelector('#{$chart_id}'), options);
    chart.render();
})();
</script>
HTML;
    }
];
