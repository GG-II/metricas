<?php
/**
 * Configuración principal del sistema
 * Dashboard de Métricas IT
 */

// ==========================================
// CONFIGURACIÓN DE BASE DE DATOS
// ==========================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'metricas_it_dev');
define('DB_USER', 'root');
define('DB_PASS', ''); // En XAMPP local, password vacío
define('DB_CHARSET', 'utf8mb4');

// ==========================================
// CONFIGURACIÓN DEL SISTEMA
// ==========================================
define('APP_NAME', 'Dashboard de Métricas IT');
define('APP_VERSION', '1.0.0');
define('TIMEZONE', 'America/Guatemala');

// ==========================================
// RUTAS DEL SISTEMA
// ==========================================
define('BASE_PATH', __DIR__);
define('ASSETS_PATH', BASE_PATH . '/assets');
define('INCLUDES_PATH', BASE_PATH . '/includes');

// URL base del proyecto (ajusta según tu configuración)
// Si accedes como: http://localhost/metricas/
define('BASE_URL', '/metricas');

// ==========================================
// CONFIGURACIÓN DE ENTORNO
// ==========================================
// Cambiar a 'production' cuando subas a AWS
define('ENVIRONMENT', 'development');

// Configuración de errores según entorno
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
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
    session_start();
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

// IDs de áreas (según la BD)
define('AREA_SOFTWARE', 1);
define('AREA_INFRAESTRUCTURA', 2);
define('AREA_SOPORTE', 3);
define('AREA_CIBERSEGURIDAD', 4);