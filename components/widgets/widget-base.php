<?php
/**
 * Widget Base - Template HTML para GridStack
 * Wrapper común para todos los widgets del dashboard
 * 
 * Variables esperadas:
 * @var int $grafico_id
 * @var string $titulo
 * @var int $grid_x, $grid_y, $grid_w, $grid_h
 * @var bool $is_edit_mode
 * @var string $widget_content (HTML del widget específico)
 */

$grafico_id = $grafico_id ?? 0;
$titulo = $titulo ?? 'Widget';
$grid_x = $grid_x ?? 0;
$grid_y = $grid_y ?? 0;
$grid_w = $grid_w ?? 6;
$grid_h = $grid_h ?? 4;
$is_edit_mode = $is_edit_mode ?? false;
?>

<div class="grid-stack-item" 
     gs-id="<?php echo $grafico_id; ?>"
     gs-x="<?php echo $grid_x; ?>" 
     gs-y="<?php echo $grid_y; ?>" 
     gs-w="<?php echo $grid_w; ?>" 
     gs-h="<?php echo $grid_h; ?>">
    
    <div class="grid-stack-item-content">
        <div class="widget-card">
            
            <!-- Header del Widget -->
            <div class="widget-header">
                <h3 class="widget-title">
                    <?php if ($is_edit_mode): ?>
                        <i class="ti ti-grip-vertical drag-handle"></i>
                    <?php endif; ?>
                    <?php echo htmlspecialchars($titulo); ?>
                </h3>
                
                <?php if ($is_edit_mode): ?>
                    <div class="widget-actions">
                        <button type="button" 
                                class="btn btn-sm btn-ghost-secondary" 
                                onclick="configurarGrafico(<?php echo $grafico_id; ?>)"
                                title="Configurar">
                            <i class="ti ti-settings"></i>
                        </button>
                        <button type="button" 
                                class="btn btn-sm btn-ghost-danger" 
                                onclick="eliminarGrafico(<?php echo $grafico_id; ?>)"
                                title="Eliminar">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Cuerpo del Widget -->
            <div class="widget-body">
                <?php echo $widget_content; ?>
            </div>
            
        </div>
    </div>
</div>