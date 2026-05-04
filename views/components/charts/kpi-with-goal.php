<?php
/**
 * GRÁFICO: KPI Card con Meta
 * Tarjeta grande mostrando valor actual vs meta con badge de cumplimiento
 */

return [
    // ==========================================
    // METADATA
    // ==========================================
    'meta' => [
        'id' => 'kpi_with_goal',
        'nombre' => 'KPI con Meta',
        'descripcion' => 'Tarjeta de KPI con indicador de cumplimiento',
        'icono' => 'target',
        'requiere_metricas' => 1,
        'requiere_metas' => true,
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
            Muestra el valor actual de una métrica con indicador visual de cumplimiento de meta
        </div>
    </div>

    <div class="col-12">
        <label class="form-label required">Métrica</label>
        <select name="metrica_id" class="form-select" required>
            <option value="">Seleccionar métrica...</option>
        </select>
        <div class="form-hint">Solo métricas con metas definidas. El período se toma del dashboard.</div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Color de acento</label>
        <input type="color" name="color" class="form-control form-control-color" value="#3b82f6">
    </div>

    <div class="col-md-6">
        <label class="form-label">Icono</label>
        <input type="text" name="icono" class="form-control" value="chart-line" placeholder="Nombre del icono Tabler">
        <div class="form-hint">Ej: chart-line, target, trophy</div>
    </div>

    <div class="col-12">
        <label class="form-check">
            <input type="checkbox" name="mostrar_tendencia" class="form-check-input" checked>
            <span class="form-check-label">Mostrar tendencia vs mes anterior</span>
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
            'icono' => sanitize($post['icono'] ?? 'chart-line'),
            'mostrar_tendencia' => isset($post['mostrar_tendencia'])
        ];
    },

    // ==========================================
    // CARGAR CONFIGURACIÓN (para edición)
    // ==========================================
    'load_config_js' => <<<'JS'
function(form, config) {
    form.querySelector('[name="metrica_id"]').value = config.metrica_id;
    form.querySelector('[name="color"]').value = config.color;
    form.querySelector('[name="icono"]').value = config.icono;
    const checkbox = form.querySelector('[name="mostrar_tendencia"]');
    if (checkbox) checkbox.checked = config.mostrar_tendencia;
}
JS,

    // ==========================================
    // RENDERIZAR WIDGET
    // ==========================================
    'render' => function($config, $metrica_data, $area_color, $periodo = null) {
        if (!isset($config['metrica_id'])) {
            return '<div class="alert alert-warning m-3">Configura la métrica</div>';
        }

        if (!$periodo) {
            return '<div class="alert alert-warning m-3">Selecciona un período en el dashboard</div>';
        }

        $valorMetricaModel = new \App\Models\ValorMetrica();
        $metaModel = new \App\Models\Meta();
        $metricaModel = new \App\Models\Metrica();
        $periodoModel = new \App\Models\Periodo();

        $metrica_id = $config['metrica_id'];
        $periodo_id = $periodo['id'];
        $color = $config['color'] ?? '#3b82f6';
        $icono = $config['icono'] ?? 'chart-line';
        $mostrar_tendencia = $config['mostrar_tendencia'] ?? true;

        // Obtener información de métrica
        $metrica = $metricaModel->find($metrica_id);

        if (!$metrica) {
            return '<div class="alert alert-danger m-3">Métrica no encontrada</div>';
        }

        // Obtener valor actual
        $valor = $valorMetricaModel->getValor($metrica_id, $periodo_id);
        $valor_real = $valor ? ($metrica['tipo_valor'] === 'decimal' ? (float)$valor['valor_decimal'] : (int)$valor['valor_numero']) : 0;

        // Obtener meta (primero mensual, si no existe usar anual promediada)
        $meta = $metaModel->getMetaAplicable($metrica_id, $periodo_id);

        if (!$meta) {
            // Si no hay meta mensual, buscar meta anual y promediarla
            $metaAnual = $metaModel->getMetaAnual($metrica_id, $periodo['ejercicio']);
            if ($metaAnual) {
                // Promediar la meta anual entre 12 meses
                $valor_objetivo = (float)$metaAnual['valor_objetivo'] / 12;
                $tipo_comparacion = $metaAnual['tipo_comparacion'];
            } else {
                return '<div class="alert alert-info m-3">No hay meta definida</div>';
            }
        } else {
            $valor_objetivo = (float)$meta['valor_objetivo'];
            $tipo_comparacion = $meta['tipo_comparacion'];
        }

        // Calcular cumplimiento
        $cumplimiento = $metaModel->calcularCumplimiento($valor_real, $valor_objetivo, $tipo_comparacion);
        $cumplimiento_display = round($cumplimiento, 1);

        // Determinar estado
        if ($cumplimiento >= 100) {
            $estado = 'success';
            $badge_class = 'bg-success';
            $badge_text = '✓ Cumplido';
        } elseif ($cumplimiento >= 80) {
            $estado = 'warning';
            $badge_class = 'bg-warning';
            $badge_text = '⚠ Casi';
        } else {
            $estado = 'danger';
            $badge_class = 'bg-danger';
            $badge_text = '✗ No cumple';
        }

        // Calcular tendencia si está habilitada
        $tendencia_html = '';
        if ($mostrar_tendencia) {
            // Obtener período anterior
            $periodo_anterior = $periodoModel->getPeriodoAnterior($periodo['ejercicio'], $periodo['periodo']);
            if ($periodo_anterior) {
                $valor_anterior = $valorMetricaModel->getValor($metrica_id, $periodo_anterior['id']);
                if ($valor_anterior) {
                    $valor_ant = $metrica['tipo_valor'] === 'decimal' ? (float)$valor_anterior['valor_decimal'] : (int)$valor_anterior['valor_numero'];
                    if ($valor_ant > 0) {
                        $cambio = (($valor_real - $valor_ant) / $valor_ant) * 100;
                        $cambio_abs = abs($cambio);
                        $cambio_display = number_format($cambio_abs, 1);

                        if ($cambio > 0) {
                            $tendencia_html = '<span class="text-success"><i class="ti ti-trending-up"></i> +' . $cambio_display . '%</span>';
                        } elseif ($cambio < 0) {
                            $tendencia_html = '<span class="text-danger"><i class="ti ti-trending-down"></i> -' . $cambio_display . '%</span>';
                        } else {
                            $tendencia_html = '<span class="text-muted"><i class="ti ti-minus"></i> Sin cambio</span>';
                        }
                    }
                }
            }
        }

        // Formatear valores
        $valor_formateado = $metrica['tipo_valor'] === 'decimal' ? number_format($valor_real, 2) : number_format($valor_real);
        $meta_formateada = $metrica['tipo_valor'] === 'decimal' ? number_format($valor_objetivo, 2) : number_format($valor_objetivo);

        // Preparar tendencia display
        $tendencia_display = $tendencia_html ? '<div class="mt-2 small">' . $tendencia_html . ' vs mes anterior</div>' : '';

        return <<<HTML
<div class="card">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center">
                <div class="me-3" style="background-color: rgba(59, 130, 246, 0.1); width: 48px; height: 48px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <i class="ti ti-{$icono}" style="font-size: 24px; color: {$color};"></i>
                </div>
                <div>
                    <div class="text-muted small">{$metrica['nombre']}</div>
                    <div class="h2 mb-0">{$valor_formateado} <span class="text-muted fs-4">{$metrica['unidad']}</span></div>
                </div>
            </div>
            <div>
                <span class="badge {$badge_class} badge-pill">{$badge_text}</span>
            </div>
        </div>

        <div class="progress mb-2" style="height: 8px;">
            <div class="progress-bar bg-{$estado}" role="progressbar" style="width: {$cumplimiento_display}%" aria-valuenow="{$cumplimiento_display}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <div class="small text-muted">
                Meta: <strong>{$meta_formateada}</strong> {$metrica['unidad']}
            </div>
            <div class="small">
                <strong>{$cumplimiento_display}%</strong> cumplimiento
            </div>
        </div>

        {$tendencia_display}
    </div>
</div>
HTML;
    }
];
