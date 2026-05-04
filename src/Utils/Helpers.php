<?php

use App\Models\Usuario;

/**
 * Obtener usuario actual de sesión
 */
if (!function_exists('getCurrentUser')) {
    function getCurrentUser() {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        $usuarioModel = new Usuario();
        return $usuarioModel->findWithRelations($_SESSION['user_id']);
    }
}

/**
 * Verificar si está logueado
 */
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}

/**
 * Verificar si es super admin
 */
if (!function_exists('isSuperAdmin')) {
    function isSuperAdmin() {
        $user = getCurrentUser();
        return $user && $user['rol'] === 'super_admin';
    }
}

/**
 * Verificar si es admin de departamento
 */
if (!function_exists('isDeptAdmin')) {
    function isDeptAdmin() {
        $user = getCurrentUser();
        return $user && in_array($user['rol'], ['super_admin', 'dept_admin']);
    }
}

/**
 * Obtener conexión a BD
 */
if (!function_exists('getDB')) {
    function getDB() {
        static $db = null;

        if ($db === null) {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            $db = new PDO($dsn, DB_USER, DB_PASS, $options);
        }

        return $db;
    }
}

/**
 * Redirigir
 */
if (!function_exists('redirect')) {
    function redirect($url) {
        if (strpos($url, 'http') === 0) {
            header('Location: ' . $url);
        } else {
            header('Location: ' . BASE_URL . $url);
        }
        exit;
    }
}

/**
 * Sanitizar entrada
 */
if (!function_exists('sanitize')) {
    function sanitize($data) {
        if (is_array($data)) {
            return array_map('sanitize', $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Escapar salida HTML
 */
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Generar URL base
 */
if (!function_exists('baseUrl')) {
    function baseUrl($path = '') {
        return BASE_URL . $path;
    }
}

/**
 * Generar URL de asset
 */
if (!function_exists('asset')) {
    function asset($path) {
        return baseUrl('/assets/' . ltrim($path, '/'));
    }
}

/**
 * Debug helper
 */
if (!function_exists('dd')) {
    function dd(...$vars) {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
        die();
    }
}

/**
 * Mostrar mensaje flash
 */
if (!function_exists('setFlash')) {
    function setFlash($type, $message) {
        $_SESSION['flash'][$type] = $message;
    }
}

/**
 * Obtener y limpiar mensaje flash
 */
if (!function_exists('getFlash')) {
    function getFlash($type) {
        if (isset($_SESSION['flash'][$type])) {
            $message = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $message;
        }
        return null;
    }
}

/**
 * Formatear fecha
 */
if (!function_exists('formatDate')) {
    function formatDate($date, $format = 'd/m/Y') {
        if (empty($date)) return '';
        $dt = new DateTime($date);
        return $dt->format($format);
    }
}

/**
 * Formatear número
 */
if (!function_exists('formatNumber')) {
    function formatNumber($number, $decimals = 0) {
        return number_format($number, $decimals, '.', ',');
    }
}
