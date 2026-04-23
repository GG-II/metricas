<?php
// Limpiar caché de OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✓ OPcache limpiado<br>";
} else {
    echo "⚠ OPcache no está habilitado<br>";
}

// Limpiar caché de APC si existe
if (function_exists('apc_clear_cache')) {
    apc_clear_cache();
    echo "✓ APC cache limpiado<br>";
}

echo "<hr>";
echo "<h3>Ahora recarga captura-valores.php</h3>";
echo "<a href='/metricas/public/captura-valores.php?periodo=3&area=1'>Ir a Captura de Valores</a>";
