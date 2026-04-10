<?php
/**
 * Navbar - Navegación por áreas
 * Variables esperadas: $areas, $area_actual, $periodo_seleccionado (opcional)
 */

if (!isset($areas) || empty($areas)) {
    $areaModel = new Area();
    $areas = $areaModel->getActivas();
}

$area_actual = $area_actual ?? AREA_SOFTWARE;
$periodo_param = isset($periodo_seleccionado) ? '&periodo=' . $periodo_seleccionado : '';
?>

<!-- Navegación por áreas -->
<div class="area-tabs">
    <div class="container-xl">
        <div class="d-flex">
            <?php foreach ($areas as $a): ?>
                <a href="<?php echo BASE_URL; ?>/index.php?area=<?php echo $a['id']; ?><?php echo $periodo_param; ?>" 
                   class="area-tab <?php echo ($a['id'] == $area_actual) ? 'active' : ''; ?>"
                   data-area-color="<?php echo $a['color']; ?>">
                    <i class="ti ti-<?php echo $a['icono']; ?>"></i>
                    <?php echo $a['nombre']; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>