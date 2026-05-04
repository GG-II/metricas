<?php
/**
 * GRÁFICO: Horizontal Bar Comparison
 * Barras horizontales para comparar múltiples métricas
 */

return [
    'meta' => [
        'id' => 'horizontal_bar',
        'nombre' => 'Barras Horizontales',
        'descripcion' => 'Compara métricas con barras horizontales',
        'icono' => 'layout-distribute-vertical',
        'requiere_metricas' => 2,
        'version' => '1.0'
    ],

    'form' => function() {
        return <<<'HTML'
<div class="row g-3">
    <div class="col-12">
        <div class="card-hint" style="padding: 0.5rem; background-color: rgba(59, 130, 246, 0.1); border-left: 3px solid #3b82f6; border-radius: 4px; color: #3b82f6;">
            <i class="ti ti-bulb me-1"></i>
            Ideal para comparar múltiples métricas del mismo período o rankings. El período se toma del dashboard.
        </div>
    </div>

    <div class="col-md-12">
        <label class="form-label">Altura (px)</label>
        <input type="number" name="altura" class="form-control" value="400" min="250" max="800">
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
        <label class="form-label">Métrica 5 (opcional)</label>
        <select name="metrica_5" class="form-select">
            <option value="">Ninguna</option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Métrica 6 (opcional)</label>
        <select name="metrica_6" class="form-select">
            <option value="">Ninguna</option>
        </select>
    </div>
</div>
HTML;
    },

    'process' => function($post) {
        $metricas = [];
        for ($i = 1; $i <= 6; $i++) {
            if (!empty($post["metrica_$i"])) {
                $metricas[] = (int)$post["metrica_$i"];
            }
        }

        return [
            'metricas' => $metricas,
            'altura' => (int)($post['altura'] ?? 400)
        ];
    },

    'load_config_js' => <<<'JS'
function(form, config) {
    form.querySelector('[name="altura"]').value = config.altura;
    for (let i = 0; i < config.metricas.length; i++) {
        const select = form.querySelector(`[name="metrica_${i+1}"]`);
        if (select) select.value = config.metricas[i];
    }
}
JS,

    'render' => function($config, $metrica_data, $area_color, $periodo = null) {
        if (empty($config['metricas']) || count($config['metricas']) < 2) {
            return '<div class="alert alert-warning m-3">Selecciona al menos 2 métricas</div>';
        }

        if (!$periodo) {
            return '<div class="alert alert-warning m-3">Selecciona un período en el dashboard</div>';
        }

        $valorMetricaModel = new \App\Models\ValorMetrica();
        $metricaModel = new \App\Models\Metrica();

        $periodo_id = $periodo['id'];
        $altura = (int)($config['altura'] ?? 400);

        $categorias = [];
        $valores = [];

        foreach ($config['metricas'] as $metrica_id) {
            $metrica = $metricaModel->find($metrica_id);
            if (!$metrica) continue;

            $valor = $valorMetricaModel->getValor($metrica_id, $periodo_id);
            $valor_num = $valor ? ($metrica['tipo_valor'] === 'decimal' ? (float)$valor['valor_decimal'] : (int)$valor['valor_numero']) : 0;

            $categorias[] = $metrica['nombre'];
            $valores[] = $valor_num;
        }

        if (empty($categorias)) {
            return '<div class="alert alert-info m-3">No hay datos disponibles</div>';
        }

        $chart_id = 'chart_' . uniqid();
        $categorias_json = json_encode($categorias);
        $valores_json = json_encode($valores);

        return <<<HTML
<div class="mb-2 text-muted small text-center">Período: {$periodo['nombre']}</div>
<div id="{$chart_id}" style="height: {$altura}px;"></div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const container = document.getElementById('{$chart_id}');
        if (!container || container.hasAttribute('data-chart-rendered')) return;

        const options = {
            series: [{
                name: 'Valor',
                data: {$valores_json}
            }],
            chart: {
                type: 'bar',
                height: {$altura},
                fontFamily: 'inherit',
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    barHeight: '70%',
                    distributed: true
                }
            },
            colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'],
            dataLabels: {
                enabled: true,
                formatter: function(val) {
                    return Math.round(val);
                }
            },
            xaxis: {
                categories: {$categorias_json}
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '12px'
                    }
                }
            },
            legend: {
                show: false
            },
            grid: {
                borderColor: '#e2e8f0',
                xaxis: {
                    lines: {
                        show: true
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return Math.round(val);
                    }
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
