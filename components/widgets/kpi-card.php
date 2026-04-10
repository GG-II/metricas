<?php
/**
 * Widget: KPI Card
 * Muestra un valor destacado con ícono y comparación
 * 
 * Variables esperadas:
 * @var array $config - Configuración del widget
 * @var array $metrica_data - Datos de la métrica
 * @var string $area_color - Color del área
 */

// Validar datos
if (!isset($metrica_data) || empty($metrica_data)) {
    echo '<div class="alert alert-warning m-3">
            <i class="ti ti-alert-circle me-2"></i>
            No hay datos disponibles para esta métrica
          </div>';
    return;
}

// Extraer datos
$nombre = $metrica_data['nombre'] ?? 'Métrica';
$valor_actual = $metrica_data['valor'] ?? 0;
$valor_anterior = $metrica_data['valor_anterior'] ?? null;
$unidad = $metrica_data['unidad'] ?? '';
$icono = $metrica_data['icono'] ?? 'chart-bar';
$tipo_valor = $metrica_data['tipo_valor'] ?? 'numero';

// Configuración
$color = $config['color'] ?? $area_color ?? '#3b82f6';
$mostrar_comparacion = $config['mostrar_comparacion'] ?? true;

// Calcular cambio
$cambio = null;
$cambio_porcentaje = 0;
if ($valor_anterior !== null && $valor_anterior > 0) {
    $cambio = $valor_actual - $valor_anterior;
    $cambio_porcentaje = (($valor_actual - $valor_anterior) / $valor_anterior) * 100;
}

// Formatear valor
$valor_formateado = number_format(
    $valor_actual, 
    ($tipo_valor === 'decimal' || $tipo_valor === 'porcentaje') ? 2 : 0
);

if ($tipo_valor === 'porcentaje') {
    $valor_formateado .= '%';
}
?>

<div class="kpi-card-widget">
    <div class="kpi-header">
        <div class="kpi-icon" style="background-color: <?php echo $color; ?>20;">
            <i class="ti ti-<?php echo $icono; ?>" style="color: <?php echo $color; ?>;"></i>
        </div>
    </div>
    
    <div class="kpi-body">
        <div class="kpi-value" style="color: <?php echo $color; ?>;">
            <?php echo $valor_formateado; ?>
            <?php if ($unidad && $tipo_valor !== 'porcentaje'): ?>
                <small class="kpi-unit"><?php echo htmlspecialchars($unidad); ?></small>
            <?php endif; ?>
        </div>
        
        <?php if ($mostrar_comparacion && $cambio !== null): ?>
            <div class="kpi-comparison">
                <span class="badge bg-<?php echo $cambio >= 0 ? 'success' : 'danger'; ?>-lt">
                    <i class="ti ti-<?php echo $cambio >= 0 ? 'trending-up' : 'trending-down'; ?> me-1"></i>
                    <?php echo $cambio >= 0 ? '+' : ''; ?><?php echo number_format(abs($cambio_porcentaje), 1); ?>%
                </span>
                <span class="text-muted small ms-2">vs anterior</span>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.kpi-card-widget {
    padding: 1.5rem;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.kpi-header {
    margin-bottom: 1rem;
}

.kpi-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
}

.kpi-value {
    font-size: 2.5rem;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 0.5rem;
}

.kpi-unit {
    font-size: 1rem;
    font-weight: 400;
    color: #94a3b8;
    margin-left: 0.25rem;
}

.kpi-comparison {
    margin-top: 0.75rem;
}
</style>