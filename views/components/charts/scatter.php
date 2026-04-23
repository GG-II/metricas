<?php
/**
 * GRÁFICO: Scatter (Dispersión)
 * Analiza correlación entre dos métricas
 */

return [
    'meta' => [
        'id' => 'scatter',
        'nombre' => 'Dispersión',
        'descripcion' => 'Analiza correlación entre dos métricas',
        'icono' => 'chart-dots',
        'requiere_metricas' => 2,
        'version' => '1.0'
    ],

    'form' => function() {
        return <<<'HTML'
<div class="row g-3">
    <div class="col-12">
        <div class="card-hint" style="padding: 0.5rem; background-color: rgba(59, 130, 246, 0.1); border-left: 3px solid #3b82f6; border-radius: 4px; color: #3b82f6;">
            <i class="ti ti-bulb me-1"></i>
            Descubre relaciones entre dos métricas. Cada punto representa un período.
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label required">Métrica Eje X</label>
        <select name="metrica_x" class="form-select" required>
            <option value="">Seleccionar...</option>
        </select>
        <div class="form-hint">Variable independiente</div>
    </div>

    <div class="col-md-6">
        <label class="form-label required">Métrica Eje Y</label>
        <select name="metrica_y" class="form-select" required>
            <option value="">Seleccionar...</option>
        </select>
        <div class="form-hint">Variable dependiente</div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Períodos a analizar</label>
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

    <div class="col-md-6">
        <label class="form-label">Color de puntos</label>
        <input type="color" name="color" class="form-control form-control-color" value="#3b82f6">
    </div>

    <div class="col-md-6 d-flex align-items-end">
        <label class="form-check mt-3">
            <input type="checkbox" name="mostrar_linea" class="form-check-input" checked>
            <span class="form-check-label">Mostrar línea de tendencia</span>
        </label>
    </div>
</div>
HTML;
    },

    'process' => function($post) {
        return [
            'metrica_x' => (int)$post['metrica_x'],
            'metrica_y' => (int)$post['metrica_y'],
            'periodos' => (int)($post['periodos'] ?? 12),
            'altura' => (int)($post['altura'] ?? 400),
            'color' => sanitize($post['color'] ?? '#3b82f6'),
            'mostrar_linea' => isset($post['mostrar_linea'])
        ];
    },

    'load_config_js' => <<<'JS'
function(form, config) {
    form.querySelector('[name="metrica_x"]').value = config.metrica_x;
    form.querySelector('[name="metrica_y"]').value = config.metrica_y;
    form.querySelector('[name="periodos"]').value = config.periodos;
    form.querySelector('[name="altura"]').value = config.altura;
    form.querySelector('[name="color"]').value = config.color;
    const checkbox = form.querySelector('[name="mostrar_linea"]');
    if (checkbox) checkbox.checked = config.mostrar_linea;
}
JS,

    'render' => function($config, $metrica_data, $area_color) {
        if (!isset($config['metrica_x']) || !isset($config['metrica_y'])) {
            return '<div class="alert alert-warning m-3">Configura las dos métricas</div>';
        }

        $valorMetricaModel = new \App\Models\ValorMetrica();
        $metricaModel = new \App\Models\Metrica();

        $metrica_x_id = $config['metrica_x'];
        $metrica_y_id = $config['metrica_y'];
        $periodos = (int)($config['periodos'] ?? 12);
        $altura = (int)($config['altura'] ?? 400);
        $color = $config['color'] ?? '#3b82f6';
        $mostrar_linea = $config['mostrar_linea'] ?? true;

        $metrica_x = $metricaModel->find($metrica_x_id);
        $metrica_y = $metricaModel->find($metrica_y_id);

        if (!$metrica_x || !$metrica_y) {
            return '<div class="alert alert-danger m-3">Métricas no encontradas</div>';
        }

        $historico_x = $valorMetricaModel->getHistorico($metrica_x_id, $periodos);
        $historico_y = $valorMetricaModel->getHistorico($metrica_y_id, $periodos);

        if (empty($historico_x) || empty($historico_y)) {
            return '<div class="alert alert-info m-3">No hay suficientes datos</div>';
        }

        // Crear mapa de períodos para matching
        $datos = [];
        $valores_x_map = [];
        foreach ($historico_x as $dato) {
            $key = $dato['ejercicio'] . '-' . $dato['periodo'];
            $valores_x_map[$key] = (float)$dato['valor'];
        }

        foreach ($historico_y as $dato) {
            $key = $dato['ejercicio'] . '-' . $dato['periodo'];
            if (isset($valores_x_map[$key])) {
                $datos[] = [$valores_x_map[$key], (float)$dato['valor']];
            }
        }

        if (empty($datos)) {
            return '<div class="alert alert-info m-3">No hay períodos coincidentes</div>';
        }

        $chart_id = 'chart_' . uniqid();
        $datos_json = json_encode($datos);

        return <<<HTML
<div id="{$chart_id}" style="height: {$altura}px;"></div>
<script>
(function() {
    const options = {
        series: [{
            name: '{$metrica_y['nombre']} vs {$metrica_x['nombre']}',
            data: {$datos_json}
        }],
        chart: {
            type: 'scatter',
            height: {$altura},
            fontFamily: 'inherit',
            toolbar: { show: true },
            zoom: {
                enabled: true,
                type: 'xy'
            }
        },
        colors: ['{$color}'],
        markers: {
            size: 6,
            hover: {
                size: 8
            }
        },
        xaxis: {
            title: {
                text: '{$metrica_x['nombre']} ({$metrica_x['unidad']})'
            },
            tickAmount: 10
        },
        yaxis: {
            title: {
                text: '{$metrica_y['nombre']} ({$metrica_y['unidad']})'
            },
            tickAmount: 7
        },
        grid: {
            borderColor: '#e2e8f0',
            xaxis: {
                lines: {
                    show: true
                }
            },
            yaxis: {
                lines: {
                    show: true
                }
            }
        },
        tooltip: {
            custom: function({seriesIndex, dataPointIndex, w}) {
                const data = w.globals.initialSeries[seriesIndex].data[dataPointIndex];
                return '<div class="p-2">' +
                    '<div><strong>{$metrica_x['nombre']}:</strong> ' + data[0] + ' {$metrica_x['unidad']}</div>' +
                    '<div><strong>{$metrica_y['nombre']}:</strong> ' + data[1] + ' {$metrica_y['unidad']}</div>' +
                    '</div>';
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
