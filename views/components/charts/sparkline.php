<?php
/**
 * GRÁFICO: Sparkline
 * Mini-gráfico de línea compacto para mostrar tendencias rápidas
 */

return [
    // ==========================================
    // METADATA
    // ==========================================
    'meta' => [
        'id' => 'sparkline',
        'nombre' => 'Sparkline',
        'descripcion' => 'Mini-gráfico de tendencia compacto',
        'icono' => 'chart-dots',
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
            Gráfico compacto perfecto para tablas o dashboards densos
        </div>
    </div>

    <div class="col-12">
        <label class="form-label required">Métrica a mostrar</label>
        <select name="metrica_id" class="form-select" required>
            <option value="">Seleccionar métrica...</option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Períodos</label>
        <select name="periodos" class="form-select">
            <option value="6" selected>Últimos 6 meses</option>
            <option value="12">Últimos 12 meses</option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Color</label>
        <input type="color" name="color" class="form-control form-control-color" value="#3b82f6">
    </div>

    <div class="col-md-6">
        <label class="form-label">Altura (px)</label>
        <input type="number" name="altura" class="form-control" value="80" min="50" max="150">
    </div>

    <div class="col-md-6 d-flex align-items-end">
        <label class="form-check mt-3">
            <input type="checkbox" name="mostrar_valor" class="form-check-input" checked>
            <span class="form-check-label">Mostrar valor actual</span>
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
            'periodos' => (int)($post['periodos'] ?? 6),
            'color' => sanitize($post['color'] ?? '#3b82f6'),
            'altura' => (int)($post['altura'] ?? 80),
            'mostrar_valor' => isset($post['mostrar_valor'])
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
    const checkbox = form.querySelector('[name="mostrar_valor"]');
    if (checkbox) checkbox.checked = config.mostrar_valor;
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
        $periodos = (int)($config['periodos'] ?? 6);
        $color = $config['color'] ?? '#3b82f6';
        $altura = (int)($config['altura'] ?? 80);
        $mostrar_valor = $config['mostrar_valor'] ?? true;

        // Obtener histórico
        $historico = $valorMetricaModel->getHistorico($metrica_id, $periodos);

        if (empty($historico)) {
            return '<div class="alert alert-info m-3">No hay datos</div>';
        }

        // Obtener información de métrica
        $metrica = $metricaModel->find($metrica_id);

        // Preparar datos
        $valores = [];
        foreach ($historico as $dato) {
            $valores[] = (float)$dato['valor'];
        }

        $valor_actual = end($valores);
        $chart_id = 'chart_' . uniqid();
        $valores_json = json_encode($valores);

        // Determinar tendencia
        if (count($valores) >= 2) {
            $primer_valor = $valores[0];
            $ultimo_valor = end($valores);
            if ($ultimo_valor > $primer_valor) {
                $tendencia_icono = 'trending-up';
                $tendencia_color = '#10b981';
            } elseif ($ultimo_valor < $primer_valor) {
                $tendencia_icono = 'trending-down';
                $tendencia_color = '#ef4444';
            } else {
                $tendencia_icono = 'minus';
                $tendencia_color = '#64748b';
            }
        } else {
            $tendencia_icono = 'minus';
            $tendencia_color = '#64748b';
        }

        $valor_display = $mostrar_valor ? '<div class="d-flex align-items-center gap-2 mb-2">
            <i class="ti ti-' . $tendencia_icono . '" style="color: ' . $tendencia_color . ';"></i>
            <span class="h3 mb-0">' . number_format($valor_actual) . '</span>
            <span class="text-muted">' . htmlspecialchars($metrica['unidad']) . '</span>
        </div>' : '';

        return <<<HTML
<div class="text-center">
    {$valor_display}
    <div id="{$chart_id}" style="height: {$altura}px;"></div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const container = document.getElementById('{$chart_id}');
        if (!container || container.hasAttribute('data-chart-rendered')) return;

        const options = {
            series: [{
                data: {$valores_json}
            }],
            chart: {
                type: 'line',
                height: {$altura},
                sparkline: {
                    enabled: true
                }
            },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            colors: ['{$color}'],
            tooltip: {
                fixed: {
                    enabled: false
                },
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
