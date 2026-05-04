<?php
/**
 * GRÁFICO: Comparación de Períodos
 * Compara valores de una métrica entre dos períodos
 */

return [
    'meta' => [
        'id' => 'period_comparison',
        'nombre' => 'Comparación de Períodos',
        'descripcion' => 'Compara valores entre dos períodos específicos',
        'icono' => 'arrows-left-right',
        'requiere_metricas' => 1,
        'version' => '1.0'
    ],

    'form' => function() {
        return <<<'HTML'
<div class="row g-3">
    <div class="col-12">
        <div class="card-hint" style="padding: 0.5rem; background-color: rgba(59, 130, 246, 0.1); border-left: 3px solid #3b82f6; border-radius: 4px; color: #3b82f6;">
            <i class="ti ti-bulb me-1"></i>
            Compara el rendimiento de una métrica entre dos períodos (ej: este mes vs mes anterior)
        </div>
    </div>

    <div class="col-12">
        <label class="form-label required">Métrica a comparar</label>
        <select name="metrica_id" class="form-select" required>
            <option value="">Seleccionar métrica...</option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label required">Período 1 (base)</label>
        <select name="periodo_1" class="form-select" required>
            <option value="">Seleccionar período...</option>
        </select>
        <div class="form-hint">Período de referencia</div>
    </div>

    <div class="col-md-6">
        <label class="form-label required">Período 2 (comparación)</label>
        <select name="periodo_2" class="form-select" required>
            <option value="">Seleccionar período...</option>
        </select>
        <div class="form-hint">Período a comparar</div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Estilo de visualización</label>
        <select name="estilo" class="form-select">
            <option value="cards">Tarjetas lado a lado</option>
            <option value="bars">Barras comparativas</option>
            <option value="gauge">Medidor de cambio</option>
        </select>
    </div>

    <div class="col-md-6 d-flex align-items-end">
        <label class="form-check mt-3">
            <input type="checkbox" name="mostrar_meta" class="form-check-input" checked>
            <span class="form-check-label">Mostrar meta si existe</span>
        </label>
    </div>
</div>
HTML;
    },

    'process' => function($post) {
        return [
            'metrica_id' => (int)$post['metrica_id'],
            'periodo_1' => (int)$post['periodo_1'],
            'periodo_2' => (int)$post['periodo_2'],
            'estilo' => sanitize($post['estilo'] ?? 'cards'),
            'mostrar_meta' => isset($post['mostrar_meta'])
        ];
    },

    'load_config_js' => <<<'JS'
function(form, config) {
    form.querySelector('[name="metrica_id"]').value = config.metrica_id;
    form.querySelector('[name="periodo_1"]').value = config.periodo_1;
    form.querySelector('[name="periodo_2"]').value = config.periodo_2;
    form.querySelector('[name="estilo"]').value = config.estilo;
    const checkbox = form.querySelector('[name="mostrar_meta"]');
    if (checkbox) checkbox.checked = config.mostrar_meta;
}
JS,

    'render' => function($config, $metrica_data, $area_color) {
        if (!isset($config['metrica_id']) || !isset($config['periodo_1']) || !isset($config['periodo_2'])) {
            return '<div class="alert alert-warning m-3">Configura la métrica y los períodos</div>';
        }

        $valorMetricaModel = new \App\Models\ValorMetrica();
        $metricaModel = new \App\Models\Metrica();
        $periodoModel = new \App\Models\Periodo();
        $metaModel = new \App\Models\Meta();

        $metrica_id = $config['metrica_id'];
        $periodo_1_id = $config['periodo_1'];
        $periodo_2_id = $config['periodo_2'];
        $estilo = $config['estilo'] ?? 'cards';
        $mostrar_meta = $config['mostrar_meta'] ?? true;

        $metrica = $metricaModel->find($metrica_id);
        $periodo_1 = $periodoModel->find($periodo_1_id);
        $periodo_2 = $periodoModel->find($periodo_2_id);

        if (!$metrica || !$periodo_1 || !$periodo_2) {
            return '<div class="alert alert-danger m-3">Datos no encontrados</div>';
        }

        $valor_1 = $valorMetricaModel->getValor($metrica_id, $periodo_1_id);
        $valor_2 = $valorMetricaModel->getValor($metrica_id, $periodo_2_id);

        $val_1 = $valor_1 ? ($metrica['tipo_valor'] === 'decimal' ? (float)$valor_1['valor_decimal'] : (int)$valor_1['valor_numero']) : 0;
        $val_2 = $valor_2 ? ($metrica['tipo_valor'] === 'decimal' ? (float)$valor_2['valor_decimal'] : (int)$valor_2['valor_numero']) : 0;

        // Calcular diferencia y porcentaje
        $diferencia = $val_2 - $val_1;
        $porcentaje = $val_1 != 0 ? (($diferencia / $val_1) * 100) : 0;
        $porcentaje_abs = number_format(abs($porcentaje), 2);

        // Determinar dirección y color
        if ($diferencia > 0) {
            $icono = 'trending-up';
            $color = '#10b981';
            $badge_class = 'bg-success';
            $signo = '+';
        } elseif ($diferencia < 0) {
            $icono = 'trending-down';
            $color = '#ef4444';
            $badge_class = 'bg-danger';
            $signo = '';
        } else {
            $icono = 'minus';
            $color = '#64748b';
            $badge_class = 'bg-secondary';
            $signo = '';
        }

        // Formatear valores
        $val_1_fmt = $metrica['tipo_valor'] === 'decimal' ? number_format($val_1, 2) : number_format($val_1);
        $val_2_fmt = $metrica['tipo_valor'] === 'decimal' ? number_format($val_2, 2) : number_format($val_2);
        $dif_fmt = $metrica['tipo_valor'] === 'decimal' ? number_format(abs($diferencia), 2) : number_format(abs($diferencia));

        // Obtener meta si existe
        $meta_html = '';
        if ($mostrar_meta) {
            $meta = $metaModel->getMetaAplicable($metrica_id, $periodo_2_id);
            if ($meta) {
                $valor_objetivo = (float)$meta['valor_objetivo'];
                $cumplimiento = $metaModel->calcularCumplimiento($val_2, $valor_objetivo, $meta['tipo_comparacion']);
                $meta_fmt = $metrica['tipo_valor'] === 'decimal' ? number_format($valor_objetivo, 2) : number_format($valor_objetivo);

                $meta_badge = $cumplimiento >= 100 ? 'success' : ($cumplimiento >= 80 ? 'warning' : 'danger');
                $meta_html = '<div class="mt-2 pt-2 border-top small">
                    <span class="text-muted">Meta ' . htmlspecialchars($periodo_2['nombre']) . ':</span>
                    <strong>' . $meta_fmt . ' ' . htmlspecialchars($metrica['unidad']) . '</strong>
                    <span class="badge bg-' . $meta_badge . ' ms-2">' . round($cumplimiento, 1) . '% cumplimiento</span>
                </div>';
            }
        }

        $chart_id = 'chart_' . uniqid();

        // Renderizar según estilo
        if ($estilo === 'cards') {
            return <<<HTML
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{$metrica['nombre']}</h3>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-5">
                <div class="text-muted small mb-1">{$periodo_1['nombre']}</div>
                <div class="h2 mb-0">{$val_1_fmt} <span class="text-muted fs-4">{$metrica['unidad']}</span></div>
                <div class="small text-muted">Período base</div>
            </div>
            <div class="col-md-2 d-flex align-items-center justify-content-center">
                <div class="text-center">
                    <i class="ti ti-{$icono}" style="font-size: 2rem; color: {$color};"></i>
                    <div class="small fw-bold" style="color: {$color};">{$signo}{$porcentaje_abs}%</div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="text-muted small mb-1">{$periodo_2['nombre']}</div>
                <div class="h2 mb-0">{$val_2_fmt} <span class="text-muted fs-4">{$metrica['unidad']}</span></div>
                <div class="small">
                    <span class="badge {$badge_class}">{$signo}{$dif_fmt} {$metrica['unidad']}</span>
                </div>
            </div>
        </div>
        {$meta_html}
    </div>
</div>
HTML;

        } elseif ($estilo === 'bars') {
            $max_val = max($val_1, $val_2);
            $bar_1_width = $max_val > 0 ? ($val_1 / $max_val * 100) : 0;
            $bar_2_width = $max_val > 0 ? ($val_2 / $max_val * 100) : 0;

            return <<<HTML
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{$metrica['nombre']}</h3>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
                <span class="small">{$periodo_1['nombre']}</span>
                <span class="small fw-bold">{$val_1_fmt} {$metrica['unidad']}</span>
            </div>
            <div class="progress" style="height: 20px;">
                <div class="progress-bar bg-blue" style="width: {$bar_1_width}%"></div>
            </div>
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
                <span class="small">{$periodo_2['nombre']}</span>
                <span class="small fw-bold">{$val_2_fmt} {$metrica['unidad']}</span>
            </div>
            <div class="progress" style="height: 20px;">
                <div class="progress-bar" style="width: {$bar_2_width}%; background-color: {$color};"></div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 mt-3">
            <i class="ti ti-{$icono}" style="color: {$color};"></i>
            <span style="color: {$color}; font-weight: 600;">{$signo}{$porcentaje_abs}% de cambio</span>
            <span class="text-muted">({$signo}{$dif_fmt} {$metrica['unidad']})</span>
        </div>
        {$meta_html}
    </div>
</div>
HTML;

        } else { // gauge
            $cambio_normalizado = min(abs($porcentaje), 100);

            return <<<HTML
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{$metrica['nombre']}</h3>
    </div>
    <div class="card-body text-center">
        <div id="{$chart_id}" style="height: 200px;"></div>

        <div class="row mt-3">
            <div class="col-6">
                <div class="text-muted small">{$periodo_1['nombre']}</div>
                <div class="h4">{$val_1_fmt}</div>
            </div>
            <div class="col-6">
                <div class="text-muted small">{$periodo_2['nombre']}</div>
                <div class="h4">{$val_2_fmt}</div>
            </div>
        </div>
        {$meta_html}
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const container = document.getElementById('{$chart_id}');
        if (!container || container.hasAttribute('data-chart-rendered')) return;

        const options = {
            series: [{$cambio_normalizado}],
            chart: {
                type: 'radialBar',
                height: 200
            },
            plotOptions: {
                radialBar: {
                    hollow: {
                        size: '60%'
                    },
                    dataLabels: {
                        name: {
                            fontSize: '14px',
                            color: '#64748b',
                            offsetY: -10
                        },
                        value: {
                            fontSize: '24px',
                            fontWeight: 600,
                            color: '{$color}',
                            formatter: function(val) {
                                return '{$signo}' + val.toFixed(1) + '%';
                            }
                        }
                    }
                }
            },
            colors: ['{$color}'],
            labels: ['Cambio']
        };

        const chart = new ApexCharts(container, options);
        chart.render();
        container.setAttribute('data-chart-rendered', 'true');
    }, 200);
});
</script>
HTML;
        }
    }
];
