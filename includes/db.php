<?php
/**
 * Conexión a Base de Datos
 */

if (!function_exists('getDB')) {
    function getDB() {
        static $db = null;

        if ($db === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];

                $db = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // En producción, no mostrar detalles del error
                if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                    die("Error de conexión: " . $e->getMessage());
                } else {
                    error_log("DB Connection Error: " . $e->getMessage());
                    die("Error de conexión a la base de datos. Contacte al administrador.");
                }
            }
        }

        return $db;
    }
}
?>
