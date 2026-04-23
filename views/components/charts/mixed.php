<?php
/**
 * GRÁFICO: Mixed Chart (Combinado)
 * Combina líneas y barras para comparar diferentes tipos de métricas
 */

return [
    'meta' => [
        'id' => 'mixed',
        'nombre' => 'Gráfico Mixto',
        'descripcion' => 'Combina líneas y barras en un mismo gráfico',
        'icono' => 'chart-dots-3',
        'requiere_metricas' => 2,
        'version' => '1.0'
    ],

    'form' => function() {
        return <<<'HTML'
<div class="row g-3">
    <div class="col-12">
        <div class="card-hint" style="padding: 0.5rem; background-color: rgba(59, 130, 246, 0.1); border-left: 3px solid #3b82f6; border-radius: 4px; color: #3b82f6;">
            <i class="ti ti-bulb me-1"></i>
            Combina barras (valores puntuales) con líneas (tendencias o acumulados)
        </div>
    </div>

    <div class="col-12"><h4>Métricas en Barras</h4></div>

    <div class="col-md-6">
        <label class="form-label required">Métrica Barra 1</label>
        <select name="metrica_bar_1" class="form-select" required>
            <option value="">Seleccionar...</option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Métrica Barra 2 (opcional)</label>
        <select name="metrica_bar_2" class="form-select">
            <option value="">Ninguna</option>
        </select>
    </div>

    <div class="col-12 mt-3"><h4>Métricas en Línea</h4></div>

    <div class="col-md-6">
        <label class="form-label required">Métrica Línea 1</label>
        <select name="metrica_line_1" class="form-select" required>
            <option value="">Seleccionar...</option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Métrica Línea 2 (opcional)</label>
        <select name="metrica_line_2" class="form-select">
            <option value="">Ninguna</option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Períodos a mostrar</label>
        <select name="periodos" class="form-select">
            <option value="6">Últimos 6 meses</option>
            <option value="12" selected>Últimos 12 meses</option>
            <option value="24">Últimos 24 meses</option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Altura (px)</label>
        <input type="number" name="altura" class="form-control" value="400" min="250" max="800">
    </div>
</div>
HTML;
    },

    'process' => function($post) {
        $metricas_bar = [];
        $metricas_line = [];

        for ($i = 1; $i <= 2; $i++) {
            if (!empty($post["metrica_bar_$i"])) {
                $metricas_bar[] = (int)$post["metrica_bar_$i"];
            }
            if (!empty($post["metrica_line_$i"])) {
                $metricas_line[] = (int)$post["metrica_line_$i"];
            }
        }

        return [
            'metricas_bar' => $metricas_bar,
            'metricas_line' => $metricas_line,
            'periodos' => (int)($post['periodos'] ?? 12),
            'altura' => (int)($post['altura'] ?? 400)
        ];
    },

    'load_config_js' => <<<'JS'
function(form, config) {
    for (let i = 0; i < config.metricas_bar.length; i++) {
        const select = form.querySelector(`[name="metrica_bar_${i+1}"]`);
        if (select) select.value = config.metricas_bar[i];
    }
    for (let i = 0; i < config.metricas_line.length; i++) {
        const select = form.querySelector(`[name="metrica_line_${i+1}"]`);
        if (select) select.value = config.metricas_line[i];
    }
    form.querySelector('[name="periodos"]').value = config.periodos;
    form.querySelector('[name="altura"]').value = config.altura;
}
JS,

    'render' => function($config, $metrica_data, $area_color) {
        if (empty($config['metricas_bar']) || empty($config['metricas_line'])) {
            return '<div class="alert alert-warning m-3">Selecciona al menos 1 métrica para barras y 1 para líneas</div>';
        }

        $valorMetricaModel = new \App\Models\ValorMetrica();
        $metricaModel = new \App\Models\Metrica();

        $periodos = (int)($config['periodos'] ?? 12);
        $altura = (int)($config['altura'] ?? 400);

        $series = [];
        $categorias = [];

        $mes_nombre = [
            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
        ];

        // Procesar métricas de barras
        foreach ($config['metricas_bar'] as $metrica_id) {
            $metrica = $metricaModel->find($metrica_id);
            if (!$metrica) continue;

            $historico = $valorMetricaModel->getHistorico($metrica_id, $periodos);

            if (empty($categorias)) {
                foreach ($historico as $dato) {
                    $categorias[] = $mes_nombre[(int)$dato['periodo']] . ' ' . substr($dato['ejercicio'], 2);
                }
            }

            $valores = [];
            foreach ($historico as $dato) {
                $valores[] = (float)$dato['valor'];
            }

            $series[] = [
                'name' => $metrica['nombre'],
                'type' => 'column',
                'data' => $valores
            ];
        }

        // Procesar métricas de líneas
        foreach ($config['metricas_line'] as $metrica_id) {
            $metrica = $metricaModel->find($metrica_id);
            if (!$metrica) continue;

            $historico = $valorMetricaModel->getHistorico($metrica_id, $periodos);

            $valores = [];
            foreach ($historico as $dato) {
                $valores[] = (float)$dato['valor'];
            }

            $series[] = [
                'name' => $metrica['nombre'],
                'type' => 'line',
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
            height: {$altura},
            fontFamily: 'inherit',
            toolbar: { show: true }
        },
        colors: ['#3b82f6', '#60a5fa', '#10b981', '#34d399'],
        stroke: {
            width: [0, 0, 3, 3],
            curve: 'smooth'
        },
        plotOptions: {
            bar: {
                columnWidth: '50%'
            }
        },
        markers: {
            size: [0, 0, 4, 4]
        },
        xaxis: {
            categories: {$categorias_json}
        },
        yaxis: {
            labels: {
                formatter: function(val) {
                    return Math.round(val);
                }
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'left'
        },
        grid: {
            borderColor: '#e2e8f0'
        },
        tooltip: {
            shared: true,
            intersect: false
        }
    };

    const chart = new ApexCharts(document.querySelector('#{$chart_id}'), options);
    chart.render();
})();
</script>
HTML;
    }
];
