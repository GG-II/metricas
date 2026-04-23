<?php
/**
 * Configuración principal del sistema - TEMPLATE
 * Dashboard de Métricas IT
 *
 * INSTRUCCIONES:
 * 1. Copiar .env.example a .env y configurar credenciales
 * 2. Copiar este archivo a "config.php"
 * 3. NUNCA subir config.php o .env a Git (están en .gitignore)
 */

// ==========================================
// DEFINIR BASE_PATH PRIMERO
// ==========================================
define('BASE_PATH', __DIR__);

// ==========================================
// CARGAR VARIABLES DE ENTORNO (.env)
// ==========================================
require_once BASE_PATH . '/includes/env-loader.php';

// Cargar archivo .env
SimpleEnvLoader::load(BASE_PATH);

// Validar que existan las variables críticas
SimpleEnvLoader::required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS']);

// ==========================================
// CONFIGURACIÓN DE BASE DE DATOS
// ==========================================
define('DB_HOST', $_ENV['DB_HOST']);
define('DB_NAME', $_ENV['DB_NAME']);
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');

// ==========================================
// CONFIGURACIÓN DEL SISTEMA
// ==========================================
define('APP_NAME', $_ENV['APP_NAME'] ?? 'Dashboard de Métricas IT');
define('APP_VERSION', $_ENV['APP_VERSION'] ?? '2.0.0');
define('TIMEZONE', $_ENV['TIMEZONE'] ?? 'America/Guatemala');

// ==========================================
// RUTAS DEL SISTEMA
// ==========================================
define('ASSETS_PATH', BASE_PATH . '/assets');
define('INCLUDES_PATH', BASE_PATH . '/includes');

define('BASE_URL', $_ENV['BASE_URL'] ?? '/metricas-it');

// ==========================================
// CONFIGURACIÓN DE ENTORNO
// ==========================================
define('ENVIRONMENT', $_ENV['ENVIRONMENT'] ?? 'production');

// Configuración de errores según entorno
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    // Producción: Errores solo en logs, NUNCA en pantalla
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', BASE_PATH . '/logs/php-errors.log');
}

// ==========================================
// ZONA HORARIA
// ==========================================
date_default_timezone_set(TIMEZONE);

// ==========================================
// SESIONES
// ==========================================
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => false, // Cambiar a true si usas HTTPS
        'use_strict_mode' => true,
        'sid_length' => 48,
    ]);
}

// ==========================================
// INCLUIR ARCHIVOS NECESARIOS
// ==========================================
require_once INCLUDES_PATH . '/db.php';
require_once INCLUDES_PATH . '/functions.php';

// ==========================================
// CONSTANTES ADICIONALES
// ==========================================

// Roles de usuario
define('ROLE_ADMIN', 'admin');
define('ROLE_VIEWER', 'viewer');

// IDs de áreas (ajustar según tu BD)
define('AREA_SOFTWARE', 1);
define('AREA_INFRAESTRUCTURA', 2);
define('AREA_SOPORTE', 3);
define('AREA_CIBERSEGURIDAD', 4);
define('AREA_MEDIOS_DIGITALES', 5);

// ==========================================
// CONFIGURACIÓN DE CACHE (Opcional)
// ==========================================
define('CACHE_ENABLED', true);
define('CACHE_TTL', 300); // 5 minutos
define('CACHE_PATH', BASE_PATH . '/storage/cache');

// ==========================================
// CONFIGURACIÓN DE UPLOADS (Opcional)
// ==========================================
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('UPLOAD_PATH', BASE_PATH . '/uploads');

// ==========================================
// FIN DE CONFIGURACIÓN
// ==========================================
?>
