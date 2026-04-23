<?php
/**
 * Protección contra ataques CSRF (Cross-Site Request Forgery)
 *
 * Genera y valida tokens únicos por sesión para prevenir
 * que sitios externos puedan ejecutar acciones en nombre del usuario
 */

class CsrfProtection {

    /**
     * Nombre de la clave en sesión
     */
    private const SESSION_KEY = 'csrf_token';

    /**
     * Nombre del campo en formularios
     */
    private const FIELD_NAME = 'csrf_token';

    /**
     * Genera un nuevo token CSRF y lo guarda en sesión
     *
     * @return string El token generado
     */
    public static function generateToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Generar token aleatorio seguro de 32 bytes (64 caracteres hex)
        $token = bin2hex(random_bytes(32));

        $_SESSION[self::SESSION_KEY] = $token;

        return $token;
    }

    /**
     * Obtiene el token actual de la sesión (o genera uno nuevo si no existe)
     *
     * @return string
     */
    public static function getToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION[self::SESSION_KEY])) {
            return self::generateToken();
        }

        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Valida el token CSRF recibido contra el almacenado en sesión
     *
     * @param string|null $token Token a validar (si es null, lo busca en $_POST)
     * @return bool True si el token es válido
     */
    public static function validateToken($token = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Si no se proporciona token, buscarlo en POST
        if ($token === null) {
            $token = $_POST[self::FIELD_NAME] ?? '';
        }

        // Verificar que existe token en sesión
        if (!isset($_SESSION[self::SESSION_KEY])) {
            return false;
        }

        // Comparación segura contra timing attacks
        return hash_equals($_SESSION[self::SESSION_KEY], $token);
    }

    /**
     * Genera el campo hidden HTML con el token CSRF
     *
     * @return string HTML del input hidden
     */
    public static function getField() {
        $token = self::getToken();
        $fieldName = self::FIELD_NAME;

        return '<input type="hidden" name="' . $fieldName . '" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Valida el token y retorna error 403 si falla
     *
     * @param string|null $token Token a validar
     * @throws Exception Si el token es inválido
     */
    public static function validateOrDie($token = null) {
        if (!self::validateToken($token)) {
            http_response_code(403);

            // Si es petición AJAX, retornar JSON
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error' => 'Token CSRF inválido o expirado. Recarga la página.',
                    'code' => 'CSRF_VALIDATION_FAILED'
                ]);
            } else {
                // Petición normal, mostrar error
                echo '<!DOCTYPE html>
<html>
<head>
    <title>403 Forbidden - CSRF Validation Failed</title>
    <style>
        body { font-family: Arial; padding: 50px; background: #f5f5f5; }
        .error { background: white; padding: 30px; border-left: 5px solid #ff4444; max-width: 600px; margin: 0 auto; }
        h1 { color: #ff4444; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="error">
        <h1>🚫 403 Forbidden</h1>
        <h2>Token CSRF Inválido</h2>
        <p>La petición fue bloqueada por razones de seguridad.</p>
        <p><strong>Posibles causas:</strong></p>
        <ul>
            <li>La sesión expiró</li>
            <li>El formulario se envió desde un sitio externo</li>
            <li>Enviaste el formulario dos veces</li>
        </ul>
        <p><strong>Solución:</strong> <a href="javascript:history.back()">Vuelve atrás</a> y recarga la página.</p>
    </div>
</body>
</html>';
            }
            exit;
        }
    }

    /**
     * Regenera el token CSRF (útil después de login/logout)
     *
     * @return string Nuevo token
     */
    public static function regenerateToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset($_SESSION[self::SESSION_KEY]);

        return self::generateToken();
    }

    /**
     * Obtiene el nombre del campo
     *
     * @return string
     */
    public static function getFieldName() {
        return self::FIELD_NAME;
    }
}
?>
