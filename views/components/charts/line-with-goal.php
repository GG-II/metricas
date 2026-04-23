<?php
/**
 * GRÁFICO: Línea con Meta
 * Muestra línea de valores históricos + línea de meta para comparación
 */

return [
    // ==========================================
    // METADATA
    // ==========================================
    'meta' => [
        'id' => 'line_with_goal',
        'nombre' => 'Línea con Meta',
        'descripcion' => 'Línea de tendencia comparada con meta',
        'icono' => 'chart-line',
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
            Visualiza la evolución de una métrica comparada con su meta
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
        <label class="form-label">Color de línea</label>
        <input type="color" name="color" class="form-control form-control-color" value="#3b82f6">
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
        <input type="number" name="altura" class="form-control" value="350" min="200" max="800">
    </div>

    <div class="col-md-6 d-flex align-items-end">
        <label class="form-check mt-3">
            <input type="checkbox" name="mostrar_area" class="form-check-input" checked>
            <span class="form-check-label">Sombrear área bajo cumplimiento</span>
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
            'color' => sanitize($post['color'] ?? '#3b82f6'),
            'periodos' => (int)($post['periodos'] ?? 12),
            'altura' => (int)($post['altura'] ?? 350),
            'mostrar_area' => isset($post['mostrar_area'])
        ];
    },

    // ==========================================
    // CARGAR CONFIGURACIÓN (para edición)
    // ==========================================
    'load_config_js' => <<<'JS'
function(form, config) {
    form.querySelector('[name="metrica_id"]').value = config.metrica_id;
    form.querySelector('[name="color"]').value = config.color;
    form.querySelector('[name="periodos"]').value = config.periodos;
    form.querySelector('[name="altura"]').value = config.altura;
    const checkbox = form.querySelector('[name="mostrar_area"]');
    if (checkbox) checkbox.checked = config.mostrar_area;
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
        $metaModel = new \App\Models\Meta();
        $metricaModel = new \App\Models\Metrica();

        $metrica_id = $config['metrica_id'];
        $periodos = (int)($config['periodos'] ?? 12);
        $color = $config['color'] ?? '#3b82f6';
        $altura = (int)($config['altura'] ?? 350);
        $mostrar_area = $config['mostrar_area'] ?? true;

        // Obtener histórico de valores
        $historico = $valorMetricaModel->getHistorico($metrica_id, $periodos);

        if (empty($historico)) {
            return '<div class="alert alert-info m-3">No hay datos históricos</div>';
        }

        // Obtener información de métrica
        $metrica = $metricaModel->find($metrica_id);

        // Preparar datos
        $categorias = [];
        $valores = [];
        $metas = [];

        $mes_nombre = [
            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
        ];

        foreach ($historico as $dato) {
            $label = $mes_nombre[(int)$dato['periodo']] . ' ' . substr($dato['ejercicio'], 2);
            $categorias[] = $label;
            $valores[] = (float)$dato['valor'];

            // Obtener meta para este período
            $meta = $metaModel->getMetaAplicable($metrica_id, $dato['periodo_id']);
            $metas[] = $meta ? (float)$meta['valor_objetivo'] : null;
        }

        $chart_id = 'chart_' . uniqid();

        $categorias_json = json_encode($categorias);
        $valores_json = json_encode($valores);
        $metas_json = json_encode($metas);

        return <<<HTML
<div id="{$chart_id}" style="height: {$altura}px;"></div>
<script>
(function() {
    const options = {
        series: [
            {
                name: 'Valor Real',
                data: {$valores_json}
            },
            {
                name: 'Meta',
                data: {$metas_json}
            }
        ],
        chart: {
            type: 'line',
            height: {$altura},
            fontFamily: 'inherit',
            toolbar: { show: false }
        },
        colors: ['{$color}', '#94a3b8'],
        stroke: {
            width: [3, 2],
            curve: 'smooth',
            dashArray: [0, 5]
        },
        markers: {
            size: [5, 0]
        },
        xaxis: {
            categories: {$categorias_json}
        },
        yaxis: {
            title: { text: '{$metrica['unidad']}' }
        },
        legend: {
            position: 'top'
        },
        tooltip: {
            shared: true,
            intersect: false
        },
        fill: {
            type: '{$mostrar_area}' === '1' ? 'gradient' : 'solid',
            gradient: {
                opacityFrom: 0.3,
                opacityTo: 0.1
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
