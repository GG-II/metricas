<?php
/**
 * Componente: Selector de Iconos
 * Catálogo visual de iconos Tabler
 */

$input_name = $input_name ?? 'icono';
$selected_icon = $selected_icon ?? 'chart-bar';
$label = $label ?? 'Icono';

// Catálogo extenso de iconos Tabler (200+ iconos)
$iconos = [
    // General y Navegación
    'home', 'dashboard', 'layout-dashboard', 'layout-grid', 'layout-list', 'layout-cards', 'layout-kanban',
    'settings', 'tool', 'adjustments', 'adjustments-horizontal', 'menu', 'menu-2', 'dots', 'dots-vertical',
    'grid-dots', 'apps', 'square', 'circle', 'triangle', 'box', 'cube', 'hexagon',

    // Flechas y Direcciones
    'arrow-right', 'arrow-left', 'arrow-up', 'arrow-down', 'arrow-up-right', 'arrow-down-left',
    'chevron-right', 'chevron-left', 'chevron-up', 'chevron-down', 'corner-down-right', 'corner-up-left',
    'arrows-maximize', 'arrows-minimize', 'arrows-diagonal', 'arrows-vertical', 'arrows-horizontal',

    // Gráficos y Analytics
    'chart-line', 'chart-bar', 'chart-area', 'chart-pie', 'chart-donut', 'chart-dots',
    'chart-arrows', 'chart-bubble', 'chart-candle', 'chart-infographic', 'chart-radar', 'chart-pie-2',
    'chart-arcs', 'chart-arcs-3', 'chart-treemap', 'chart-sankey', 'presentation', 'presentation-analytics',

    // Negocios y Edificios
    'building', 'building-store', 'building-warehouse', 'building-factory', 'building-bank', 'building-hospital',
    'building-community', 'building-skyscraper', 'building-carousel', 'building-fortress',
    'office', 'briefcase', 'businessplan', 'report', 'report-analytics', 'report-money', 'report-search',

    // Tecnología e IT
    'server', 'server-2', 'database', 'database-import', 'database-export', 'cloud', 'cloud-computing',
    'cloud-upload', 'cloud-download', 'cpu', 'cpu-2', 'device-desktop', 'device-laptop', 'device-tablet',
    'device-mobile', 'devices', 'devices-pc', 'router', 'network', 'wifi', 'api', 'code', 'terminal',
    'terminal-2', 'bug', 'shield', 'shield-check', 'shield-lock', 'lock', 'lock-open', 'key',

    // Personas y Usuarios
    'user', 'users', 'user-plus', 'user-minus', 'user-check', 'user-x', 'user-star', 'user-circle',
    'user-exclamation', 'id', 'id-badge', 'id-badge-2', 'headset', 'friends', 'mood-smile', 'mood-happy',

    // Comunicación
    'phone', 'phone-call', 'phone-incoming', 'phone-outgoing', 'mail', 'mail-opened', 'message',
    'message-circle', 'message-dots', 'messages', 'send', 'share', 'at', 'hash',

    // Finanzas
    'cash', 'coin', 'currency-dollar', 'currency-euro', 'currency-pound', 'currency-yen',
    'wallet', 'credit-card', 'calculator', 'receipt', 'receipt-2', 'moneybag', 'piggy-bank',

    // Documentos y Archivos
    'file', 'file-text', 'file-plus', 'file-minus', 'file-check', 'file-x', 'file-download', 'file-upload',
    'files', 'folder', 'folder-plus', 'folder-open', 'folders', 'clipboard', 'clipboard-check',
    'clipboard-list', 'note', 'notes', 'notebook', 'book', 'book-2', 'bookmarks',

    // Acciones y Controles
    'check', 'x', 'plus', 'minus', 'equal', 'edit', 'trash', 'copy', 'cut', 'clipboard-copy',
    'download', 'upload', 'refresh', 'rotate', 'reload', 'search', 'zoom-in', 'zoom-out',
    'filter', 'sort-ascending', 'sort-descending', 'star', 'star-filled', 'heart', 'heart-filled',

    // Estado e Indicadores
    'circle-check', 'circle-x', 'circle-plus', 'circle-minus', 'alert-circle', 'alert-triangle',
    'info-circle', 'help', 'question-mark', 'bell', 'bell-ringing', 'flag', 'flag-filled',
    'eye', 'eye-off', 'toggle-left', 'toggle-right',

    // Tiempo y Calendario
    'calendar', 'calendar-event', 'calendar-time', 'calendar-stats', 'clock', 'hourglass',
    'hourglass-empty', 'alarm', 'timeline', 'history',

    // Redes Sociales
    'brand-facebook', 'brand-instagram', 'brand-twitter', 'brand-linkedin', 'brand-github',
    'brand-google', 'brand-youtube', 'brand-slack', 'brand-whatsapp', 'brand-telegram',

    // Multimedia
    'photo', 'camera', 'video', 'microphone', 'microphone-off', 'play', 'pause', 'stop',
    'music', 'volume', 'volume-2', 'volume-3',

    // Ubicación y Mapas
    'map-pin', 'map-pin-filled', 'map', 'map-2', 'world', 'compass', 'target', 'location',
    'route', 'directions',

    // Educación
    'certificate', 'school', 'bookmark', 'bookmark-filled', 'books', 'pencil', 'eraser',

    // Salud y Fitness
    'heart-rate-monitor', 'activity', 'pulse', 'first-aid-kit', 'pill', 'vaccine',

    // Compras y E-commerce
    'shopping-cart', 'shopping-bag', 'package', 'truck-delivery', 'gift', 'barcode', 'qrcode',
    'tag', 'tags', 'discount',

    // Clima
    'sun', 'moon', 'cloud', 'cloud-rain', 'cloud-snow', 'bolt', 'umbrella', 'temperature',

    // Otros
    'rocket', 'plane', 'car', 'bike', 'medal', 'trophy', 'flame', 'leaf', 'tree', 'flower',
    'palette', 'paint', 'brush', 'color-swatch', 'sparkles', 'diamond', 'infinity',
    'atom', 'atom-2', 'chemistry', 'telescope', 'battery', 'battery-charging', 'plug',
    'power', 'bulb', 'bulb-off', 'sun-high', 'moon-stars', 'pepper', 'pizza', 'coffee'
];

sort($iconos);
?>

<div class="mb-3">
    <label class="form-label"><?php echo e($label); ?></label>
    <input type="hidden" name="<?php echo e($input_name); ?>" id="icon-input-<?php echo $input_name; ?>" value="<?php echo e($selected_icon); ?>">

    <div class="card">
        <div class="card-body p-3">
            <div class="d-flex align-items-center mb-3">
                <span class="avatar avatar-lg me-3" id="icon-preview-<?php echo $input_name; ?>" style="background-color: rgba(59, 130, 246, 0.1);">
                    <i class="ti ti-<?php echo e($selected_icon); ?>" style="font-size: 2rem; color: #3b82f6;"></i>
                </span>
                <div class="flex-fill">
                    <div class="fw-bold" id="icon-name-<?php echo $input_name; ?>"><?php echo e($selected_icon); ?></div>
                    <div class="small text-muted">Icono seleccionado</div>
                </div>
            </div>

            <input type="text" class="form-control mb-3" id="icon-search-<?php echo $input_name; ?>" placeholder="🔍 Buscar icono...">

            <div class="row g-2" id="icon-grid-<?php echo $input_name; ?>" style="max-height: 350px; overflow-y: auto;">
                <?php foreach ($iconos as $icon): ?>
                <div class="col-2 text-center icon-item" data-icon="<?php echo e($icon); ?>" data-target="<?php echo $input_name; ?>">
                    <div class="icon-box p-2 border rounded d-flex align-items-center justify-content-center"
                         onclick="selectIconInline('<?php echo e($icon); ?>', '<?php echo $input_name; ?>')"
                         style="height: 50px;" title="<?php echo e($icon); ?>">
                        <i class="ti ti-<?php echo e($icon); ?>" style="font-size: 1.75rem;"></i>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div id="no-results-<?php echo $input_name; ?>" class="text-center py-4" style="display: none;">
                <i class="ti ti-search" style="font-size: 2rem; opacity: 0.3;"></i>
                <p class="text-muted mt-2 mb-0 small">No se encontraron iconos</p>
            </div>
        </div>
    </div>
</div>


<style>
.icon-box {
    transition: all 0.15s;
    cursor: pointer;
}

.icon-box:hover {
    background-color: rgba(59, 130, 246, 0.1) !important;
    border-color: #3b82f6 !important;
    transform: scale(1.1);
}

.icon-box.selected {
    background-color: rgba(59, 130, 246, 0.2) !important;
    border-color: #3b82f6 !important;
    font-weight: bold;
}
</style>

<script>
function selectIconInline(iconName, targetInput) {
    // Actualizar input hidden
    document.getElementById('icon-input-' + targetInput).value = iconName;

    // Actualizar preview
    document.getElementById('icon-preview-' + targetInput).innerHTML =
        '<i class="ti ti-' + iconName + '" style="font-size: 2rem; color: #3b82f6;"></i>';

    // Actualizar nombre
    document.getElementById('icon-name-' + targetInput).textContent = iconName;

    // Marcar como seleccionado
    const container = document.getElementById('icon-grid-' + targetInput);
    container.querySelectorAll('.icon-box').forEach(box => box.classList.remove('selected'));
    event.target.closest('.icon-box').classList.add('selected');
}

// Búsqueda en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    // Para cada icon-picker en la página
    document.querySelectorAll('[id^="icon-search-"]').forEach(searchInput => {
        const targetInput = searchInput.id.replace('icon-search-', '');
        const gridContainer = document.getElementById('icon-grid-' + targetInput);
        const noResults = document.getElementById('no-results-' + targetInput);

        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const items = gridContainer.querySelectorAll('.icon-item');
            let visibleCount = 0;

            items.forEach(item => {
                const iconName = item.getAttribute('data-icon');
                if (iconName.includes(query)) {
                    item.style.display = '';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            // Mostrar/ocultar mensaje de no resultados
            if (noResults) {
                if (visibleCount === 0) {
                    gridContainer.style.display = 'none';
                    noResults.style.display = 'block';
                } else {
                    gridContainer.style.display = '';
                    noResults.style.display = 'none';
                }
            }
        });

        // Marcar icono seleccionado al cargar
        const currentIcon = document.getElementById('icon-input-' + targetInput).value;
        items = gridContainer.querySelectorAll('.icon-item');
        items.forEach(item => {
            if (item.getAttribute('data-icon') === currentIcon) {
                item.querySelector('.icon-box').classList.add('selected');
            }
        });
    });
});
</script>
