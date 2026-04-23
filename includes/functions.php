<?php
/**
 * Funciones Helper del Sistema
 */

// ==========================================
// AUTENTICACIÓN
// ==========================================

if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}

if (!function_exists('getCurrentUser')) {
    function getCurrentUser() {
        if (!isLoggedIn()) {
            return null;
        }

        // Cargar datos del usuario desde sesión
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'nombre' => $_SESSION['user_nombre'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'rol' => $_SESSION['user_rol'] ?? 'viewer',
            'departamento_id' => $_SESSION['user_departamento_id'] ?? null,
            'avatar_icono' => $_SESSION['user_avatar_icono'] ?? null,
            'avatar_color' => $_SESSION['user_avatar_color'] ?? null,
            'tema' => $_SESSION['user_tema'] ?? 'light',
        ];
    }
}

if (!function_exists('isDeptAdmin')) {
    function isDeptAdmin() {
        $user = getCurrentUser();
        return $user && in_array($user['rol'], ['super_admin', 'dept_admin']);
    }
}

if (!function_exists('isSuperAdmin')) {
    function isSuperAdmin() {
        $user = getCurrentUser();
        return $user && $user['rol'] === 'super_admin';
    }
}

// ==========================================
// UTILIDADES
// ==========================================

if (!function_exists('sanitize')) {
    function sanitize($string) {
        return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('baseUrl')) {
    function baseUrl($path = '') {
        return BASE_URL . $path;
    }
}

if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: " . baseUrl($url));
        exit;
    }
}

// ==========================================
// FLASH MESSAGES
// ==========================================

if (!function_exists('setFlash')) {
    function setFlash($type, $message) {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }
}

if (!function_exists('getFlash')) {
    function getFlash() {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}

// ==========================================
// VALIDACIÓN
// ==========================================

if (!function_exists('validateEmail')) {
    function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('generateToken')) {
    function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
}

// ==========================================
// FORMATEO
// ==========================================

if (!function_exists('formatNumber')) {
    function formatNumber($number, $decimals = 0) {
        return number_format($number, $decimals, '.', ',');
    }
}

if (!function_exists('formatDate')) {
    function formatDate($date, $format = 'd/m/Y') {
        if (empty($date)) return '';
        $dt = new DateTime($date);
        return $dt->format($format);
    }
}

if (!function_exists('formatDateTime')) {
    function formatDateTime($datetime, $format = 'd/m/Y H:i') {
        if (empty($datetime)) return '';
        $dt = new DateTime($datetime);
        return $dt->format($format);
    }
}

// ==========================================
// PROTECCIÓN CSRF
// ==========================================

/**
 * Incluir clase CsrfProtection
 */
require_once __DIR__ . '/CsrfProtection.php';

/**
 * Genera y retorna el campo hidden de CSRF para formularios
 *
 * @return string HTML del campo hidden
 */
if (!function_exists('csrf_field')) {
    function csrf_field() {
        return CsrfProtection::getField();
    }
}

/**
 * Obtiene el token CSRF actual
 *
 * @return string
 */
if (!function_exists('csrf_token')) {
    function csrf_token() {
        return CsrfProtection::getToken();
    }
}

/**
 * Valida el token CSRF o termina la ejecución con error 403
 *
 * ⚠️ DESHABILITADO - La validación CSRF está desactivada
 *
 * @param string|null $token Token a validar (null = busca en $_POST)
 */
if (!function_exists('csrf_validate')) {
    function csrf_validate($token = null) {
        // CSRF DESHABILITADO - No valida nada
        return true;
    }
}

/**
 * Verifica si el token CSRF es válido (sin terminar ejecución)
 *
 * @param string|null $token Token a validar
 * @return bool
 */
if (!function_exists('csrf_check')) {
    function csrf_check($token = null) {
        return CsrfProtection::validateToken($token);
    }
}
?>
