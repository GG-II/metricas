<?php
/**
 * GRÁFICO: Área
 * Gráfico de área para visualizar volumen acumulado a lo largo del tiempo
 */

return [
    // ==========================================
    // METADATA
    // ==========================================
    'meta' => [
        'id' => 'area',
        'nombre' => 'Gráfico de Área',
        'descripcion' => 'Visualiza volumen acumulado con área sombreada',
        'icono' => 'chart-area',
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
            Ideal para mostrar volumen total acumulado a lo largo del tiempo
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label required">Métrica a mostrar</label>
        <select name="metrica_id" class="form-select" required>
            <option value="">Seleccionar métrica...</option>
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
        <label class="form-label">Color del área</label>
        <input type="color" name="color" class="form-control form-control-color" value="#3b82f6">
    </div>

    <div class="col-md-6">
        <label class="form-label">Altura (px)</label>
        <input type="number" name="altura" class="form-control" value="350" min="200" max="800">
    </div>

    <div class="col-md-6 d-flex align-items-end">
        <label class="form-check mt-3">
            <input type="checkbox" name="mostrar_puntos" class="form-check-input">
            <span class="form-check-label">Mostrar puntos de datos</span>
        </label>
    </div>

    <div class="col-md-6 d-flex align-items-end">
        <label class="form-check mt-3">
            <input type="checkbox" name="suave" class="form-check-input" checked>
            <span class="form-check-label">Curva suave</span>
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
            'periodos' => (int)($post['periodos'] ?? 12),
            'color' => sanitize($post['color'] ?? '#3b82f6'),
            'altura' => (int)($post['altura'] ?? 350),
            'mostrar_puntos' => isset($post['mostrar_puntos']),
            'suave' => isset($post['suave'])
        ];
    },

    // ==========================================
    // CARGAR CONFIGURACIÓN (para edición)
    // ==========================================
    'load_config_js' => <<<'JS'
function(form, config) {
    form.querySelector('[name="metrica_id"]').value = config.metrica_id;
    form.querySelector('[name="periodos"]').value = config.periodos;
    form.querySelector('[name="color"]').value = config.color;
    form.querySelector('[name="altura"]').value = config.altura;
    const checkPuntos = form.querySelector('[name="mostrar_puntos"]');
    if (checkPuntos) checkPuntos.checked = config.mostrar_puntos;
    const checkSuave = form.querySelector('[name="suave"]');
    if (checkSuave) checkSuave.checked = config.suave;
}
JS,

    // ==========================================
    // RENDERIZAR WIDGET
    // ==========================================
    'render' => function($config, $metrica_data, $area_color) {
        if (!isset($config['metrica_id'])) {
            return '<div class="alert alert-warning m-3">Configura la métrica</div>';
        }

        $valorMetricaModel = new \App\Models\ValorMetrica();
        $metricaModel = new \App\Models\Metrica();

        $metrica_id = $config['metrica_id'];
        $periodos = (int)($config['periodos'] ?? 12);
        $color = $config['color'] ?? '#3b82f6';
        $altura = (int)($config['altura'] ?? 350);
        $mostrar_puntos = $config['mostrar_puntos'] ?? false;
        $suave = $config['suave'] ?? true;

        // Obtener histórico
        $historico = $valorMetricaModel->getHistorico($metrica_id, $periodos);

        if (empty($historico)) {
            return '<div class="alert alert-info m-3">No hay datos históricos</div>';
        }

        // Obtener información de métrica
        $metrica = $metricaModel->find($metrica_id);

        // Preparar datos
        $categorias = [];
        $valores = [];

        $mes_nombre = [
            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
        ];

        foreach ($historico as $dato) {
            $label = $mes_nombre[(int)$dato['periodo']] . ' ' . substr($dato['ejercicio'], 2);
            $categorias[] = $label;
            $valores[] = (float)$dato['valor'];
        }

        $chart_id = 'chart_' . uniqid();

        $categorias_json = json_encode($categorias);
        $valores_json = json_encode($valores);
        $curve = $suave ? 'smooth' : 'straight';
        $marker_size = $mostrar_puntos ? 5 : 0;

        return <<<HTML
<div id="{$chart_id}" style="height: {$altura}px;"></div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const container = document.getElementById('{$chart_id}');
        if (!container || container.hasAttribute('data-chart-rendered')) return;

        const options = {
            series: [{
                name: '{$metrica['nombre']}',
                data: {$valores_json}
            }],
            chart: {
                type: 'area',
                height: {$altura},
                fontFamily: 'inherit',
                toolbar: { show: false },
                zoom: { enabled: false }
            },
            colors: ['{$color}'],
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: '{$curve}',
                width: 2
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            markers: {
                size: {$marker_size},
                hover: {
                    size: 7
                }
            },
            xaxis: {
                categories: {$categorias_json},
                labels: {
                    style: {
                        colors: '#64748b'
                    }
                }
            },
            yaxis: {
                title: {
                    text: '{$metrica['unidad']}'
                },
                labels: {
                    style: {
                        colors: '#64748b'
                    }
                }
            },
            grid: {
                borderColor: '#e2e8f0',
                strokeDashArray: 4
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + ' {$metrica['unidad']}';
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
