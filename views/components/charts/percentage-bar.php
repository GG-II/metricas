<?php
/**
 * GRÁFICO: Percentage Bar (100% Stacked)
 * Barras mostrando distribución porcentual que cambia en el tiempo
 */

return [
    'meta' => [
        'id' => 'percentage_bar',
        'nombre' => 'Barras Porcentuales',
        'descripcion' => 'Distribución porcentual (100% apilado)',
        'icono' => 'percentage',
        'requiere_metricas' => 2,
        'version' => '1.0'
    ],

    'form' => function() {
        return <<<'HTML'
<div class="row g-3">
    <div class="col-12">
        <div class="card-hint" style="padding: 0.5rem; background-color: rgba(59, 130, 246, 0.1); border-left: 3px solid #3b82f6; border-radius: 4px; color: #3b82f6;">
            <i class="ti ti-bulb me-1"></i>
            Muestra cómo cambia la distribución porcentual a lo largo del tiempo
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

        foreach ($config['metricas'] as $metrica_config) {
            $metrica_id = is_array($metrica_config) ? $metrica_config['id'] : $metrica_config;
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
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const container = document.getElementById('{$chart_id}');
        if (!container || container.hasAttribute('data-chart-rendered')) return;

        const options = {
            series: {$series_json},
            chart: {
                type: 'bar',
                height: {$altura},
                fontFamily: 'inherit',
                stacked: true,
                stackType: '100%',
                toolbar: { show: true }
            },
            colors: {$colores_json},
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '70%'
                }
            },
            xaxis: {
                categories: {$categorias_json}
            },
            yaxis: {
                labels: {
                    formatter: function(val) {
                        return val.toFixed(0) + '%';
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
                    formatter: function(val, { seriesIndex, dataPointIndex, w }) {
                        const series = w.globals.initialSeries;
                        let total = 0;
                        series.forEach(s => {
                            total += s.data[dataPointIndex];
                        });
                        const percentage = ((val / total) * 100).toFixed(1);
                        return val + ' (' + percentage + '%)';
                    }
                }
            },
            fill: {
                opacity: 1
            },
            dataLabels: {
                enabled: true,
                formatter: function(val) {
                    return val > 5 ? val.toFixed(0) + '%' : '';
                },
                style: {
                    colors: ['#fff']
                }
            }
        };

        const chart = new ApexCharts(container, options);
        chart.render();
        container.setAttribute('data-chart-rendered', 'true');
    }, 200);
});
</script>
HTML;
    }
];
