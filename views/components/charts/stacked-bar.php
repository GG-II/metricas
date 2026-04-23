<?php
/**
 * GRÁFICO: Stacked Bar (Barras Apiladas)
 * Muestra barras con segmentos apilados para composición total
 */

return [
    'meta' => [
        'id' => 'stacked_bar',
        'nombre' => 'Barras Apiladas',
        'descripcion' => 'Barras con segmentos mostrando composición',
        'icono' => 'chart-bar',
        'requiere_metricas' => 2,
        'version' => '1.0'
    ],

    'form' => function() {
        return <<<'HTML'
<div class="row g-3">
    <div class="col-12">
        <div class="card-hint" style="padding: 0.5rem; background-color: rgba(59, 130, 246, 0.1); border-left: 3px solid #3b82f6; border-radius: 4px; color: #3b82f6;">
            <i class="ti ti-bulb me-1"></i>
            Visualiza cómo diferentes partes conforman el total en cada período
        </div>
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
        <label class="form-label">Métrica 3 (opcional)</label>
        <select name="metrica_3" class="form-select">
            <option value="">Ninguna</option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Métrica 4 (opcional)</label>
        <select name="metrica_4" class="form-select">
            <option value="">Ninguna</option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Períodos a mostrar</label>
        <select name="periodos" class="form-select">
            <option value="6" selected>Últimos 6 meses</option>
            <option value="12">Últimos 12 meses</option>
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
        $metricas = [];
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($post["metrica_$i"])) {
                $metricas[] = (int)$post["metrica_$i"];
            }
        }

        return [
            'metricas' => $metricas,
            'periodos' => (int)($post['periodos'] ?? 6),
            'altura' => (int)($post['altura'] ?? 400)
        ];
    },

    'load_config_js' => <<<'JS'
function(form, config) {
    for (let i = 0; i < config.metricas.length; i++) {
        const select = form.querySelector(`[name="metrica_${i+1}"]`);
        if (select) select.value = config.metricas[i];
    }
    form.querySelector('[name="periodos"]').value = config.periodos;
    form.querySelector('[name="altura"]').value = config.altura;
}
JS,

    'render' => function($config, $metrica_data, $area_color) {
        if (empty($config['metricas']) || count($config['metricas']) < 2) {
            return '<div class="alert alert-warning m-3">Selecciona al menos 2 métricas</div>';
        }

        $valorMetricaModel = new \App\Models\ValorMetrica();
        $metricaModel = new \App\Models\Metrica();

        $periodos = (int)($config['periodos'] ?? 6);
        $altura = (int)($config['altura'] ?? 400);

        $colores = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6'];

        $series = [];
        $categorias = [];

        $mes_nombre = [
            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
        ];

        foreach ($config['metricas'] as $metrica_id) {
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
                'data' => $valores
            ];
        }

        if (empty($series)) {
            return '<div class="alert alert-info m-3">No hay datos disponibles</div>';
        }

        $chart_id = 'chart_' . uniqid();
        $series_json = json_encode($series);
        $categorias_json = json_encode($categorias);
        $colores_json = json_encode(array_slice($colores, 0, count($series)));

        return <<<HTML
<div id="{$chart_id}" style="height: {$altura}px;"></div>
<script>
(function() {
    const options = {
        series: {$series_json},
        chart: {
            type: 'bar',
            height: {$altura},
            fontFamily: 'inherit',
            stacked: true,
            toolbar: { show: true }
        },
        colors: {$colores_json},
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '60%'
            }
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
            intersect: false,
            y: {
                formatter: function(val) {
                    return Math.round(val);
                }
            }
        },
        fill: {
            opacity: 1
        }
    };

    const chart = new ApexCharts(document.querySelector('#{$chart_id}'), options);
    chart.render();
})();
</script>
HTML;
    }
];
