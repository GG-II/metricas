<?php
/**
 * Gestor de Caché del Sistema
 * Uso CLI: php cache-manager.php [--stats|--clean|--flush]
 * Uso Web: Acceder desde navegador
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Utils\Cache;

$is_cli = php_sapi_name() === 'cli';

if (!$is_cli) {
    echo "<h1>Gestor de Caché del Sistema</h1><hr>";
}

// ✅ SEGURIDAD: Validación estricta de acción
$action_raw = $is_cli ? ($argv[1] ?? '--stats') : ($_GET['action'] ?? 'stats');

// Whitelist de acciones permitidas
$allowed_actions = ['--stats', 'stats', '--clean', 'clean', '--flush', 'flush'];

if (!in_array($action_raw, $allowed_actions, true)) {
    $action = 'stats'; // Default seguro
} else {
    $action = $action_raw;
}

switch ($action) {
    case '--stats':
    case 'stats':
        if (!$is_cli) echo "<h2>Estadísticas del Caché</h2>";

        $stats = Cache::stats();

        if ($is_cli) {
            echo "=== ESTADÍSTICAS DEL CACHÉ ===\n\n";
            foreach ($stats as $key => $value) {
                echo "  " . str_pad(ucfirst(str_replace('_', ' ', $key)), 25) . ": $value\n";
            }
        } else {
            echo "<table class='table'>";
            foreach ($stats as $key => $value) {
                echo "<tr><td><strong>" . ucfirst(str_replace('_', ' ', $key)) . "</strong></td><td>$value</td></tr>";
            }
            echo "</table>";

            echo "<div style='margin-top: 20px;'>";
            echo "<a href='?action=clean' class='btn btn-warning'>Limpiar Expirados</a> ";
            echo "<a href='?action=flush' class='btn btn-danger' onclick='return confirm(\"¿Seguro?\")'>Limpiar TODO</a>";
            echo "</div>";
        }
        break;

    case '--clean':
    case 'clean':
        $deleted = Cache::cleanup();

        if ($is_cli) {
            echo "✓ $deleted entradas expiradas eliminadas\n";
        } else {
            echo "<div class='alert alert-success'>✓ $deleted entradas expiradas eliminadas</div>";
            echo "<a href='?action=stats'>Ver estadísticas</a>";
        }
        break;

    case '--flush':
    case 'flush':
        Cache::flush();

        if ($is_cli) {
            echo "✓ Caché completamente limpiado\n";
        } else {
            echo "<div class='alert alert-success'>✓ Caché completamente limpiado</div>";
            echo "<a href='?action=stats'>Ver estadísticas</a>";
        }
        break;

    default:
        if ($is_cli) {
            echo "Uso: php cache-manager.php [--stats|--clean|--flush]\n";
        } else {
            echo "<div class='alert alert-warning'>Acción no válida</div>";
        }
}

if (!$is_cli) {
    echo "<hr><small>Última actualización: " . date('Y-m-d H:i:s') . "</small>";
}
