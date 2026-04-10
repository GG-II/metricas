<?php
/**
 * AJAX: Obtener formulario HTML de un tipo de gráfico
 */

require_once '../../config.php';
requireAdmin();

require_once '../../models/ChartRegistry.php';

$tipo = $_GET['tipo'] ?? '';

if (!$tipo) {
    echo '<div class="alert alert-danger">Tipo no especificado</div>';
    exit;
}

ChartRegistry::load();
$html = ChartRegistry::renderForm($tipo);

if (!$html) {
    echo '<div class="alert alert-warning">Formulario no disponible para este tipo</div>';
    exit;
}

echo $html;