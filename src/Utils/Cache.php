<?php
namespace App\Utils;

/**
 * Sistema de Caché Simple Basado en Archivos
 * Mejora performance evitando consultas repetidas a BD
 */
class Cache {
    private static $cache_dir = null;
    private static $enabled = true;
    private static $default_ttl = 300; // 5 minutos

    /**
     * Inicializar directorio de caché
     */
    private static function init() {
        if (self::$cache_dir === null) {
            self::$cache_dir = __DIR__ . '/../../storage/cache';

            if (!is_dir(self::$cache_dir)) {
                mkdir(self::$cache_dir, 0755, true);
            }
        }
    }

    /**
     * Obtener valor del caché
     */
    public static function get($key, $default = null) {
        if (!self::$enabled) {
            return $default;
        }

        self::init();

        $filename = self::getFilename($key);

        if (!file_exists($filename)) {
            return $default;
        }

        $data = file_get_contents($filename);

        // ✅ SEGURIDAD: Usar JSON en lugar de unserialize() para prevenir object injection
        $cached = json_decode($data, true);

        // Validar estructura
        if (!is_array($cached) || !isset($cached['expires_at'], $cached['value'])) {
            unlink($filename);
            return $default;
        }

        // Verificar si expiró
        if ($cached['expires_at'] < time()) {
            unlink($filename);
            return $default;
        }

        return $cached['value'];
    }

    /**
     * Guardar valor en caché
     */
    public static function set($key, $value, $ttl = null) {
        if (!self::$enabled) {
            return false;
        }

        self::init();

        $ttl = $ttl ?? self::$default_ttl;
        $filename = self::getFilename($key);

        $data = [
            'key' => $key,
            'value' => $value,
            'expires_at' => time() + $ttl,
            'created_at' => time()
        ];

        // ✅ SEGURIDAD: Usar JSON en lugar de serialize()
        return file_put_contents($filename, json_encode($data)) !== false;
    }

    /**
     * Verificar si existe en caché
     */
    public static function has($key) {
        return self::get($key, '__CACHE_MISS__') !== '__CACHE_MISS__';
    }

    /**
     * Eliminar del caché
     */
    public static function forget($key) {
        self::init();

        $filename = self::getFilename($key);

        if (file_exists($filename)) {
            return unlink($filename);
        }

        return false;
    }

    /**
     * Limpiar todo el caché
     */
    public static function flush() {
        self::init();

        $files = glob(self::$cache_dir . '/*.cache');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        return true;
    }

    /**
     * Limpiar caché expirado
     */
    public static function cleanup() {
        self::init();

        $files = glob(self::$cache_dir . '/*.cache');
        $deleted = 0;

        foreach ($files as $file) {
            if (is_file($file)) {
                $data = unserialize(file_get_contents($file));
                if ($data['expires_at'] < time()) {
                    unlink($file);
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    /**
     * Remember: Obtener del caché o ejecutar callback y guardar
     */
    public static function remember($key, $ttl, callable $callback) {
        $value = self::get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        self::set($key, $value, $ttl);

        return $value;
    }

    /**
     * Deshabilitar caché (útil para debugging)
     */
    public static function disable() {
        self::$enabled = false;
    }

    /**
     * Habilitar caché
     */
    public static function enable() {
        self::$enabled = true;
    }

    /**
     * Obtener nombre de archivo para una key
     */
    private static function getFilename($key) {
        $hash = md5($key);
        return self::$cache_dir . '/' . $hash . '.cache';
    }

    /**
     * Obtener estadísticas del caché
     */
    public static function stats() {
        self::init();

        $files = glob(self::$cache_dir . '/*.cache');
        $total = count($files);
        $expired = 0;
        $total_size = 0;

        foreach ($files as $file) {
            if (is_file($file)) {
                $total_size += filesize($file);
                $data = unserialize(file_get_contents($file));
                if ($data['expires_at'] < time()) {
                    $expired++;
                }
            }
        }

        return [
            'total_entries' => $total,
            'active_entries' => $total - $expired,
            'expired_entries' => $expired,
            'total_size_bytes' => $total_size,
            'total_size_mb' => round($total_size / 1024 / 1024, 2)
        ];
    }
}
