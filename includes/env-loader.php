<?php
/**
 * Cargador Simple de Variables de Entorno (.env)
 *
 * Carga variables desde archivo .env sin dependencias externas
 * Alternativa ligera a vlucas/phpdotenv
 */

class SimpleEnvLoader {

    /**
     * Carga variables del archivo .env
     *
     * @param string $path Ruta al directorio que contiene .env
     * @return bool True si se cargó exitosamente
     */
    public static function load($path) {
        $envFile = rtrim($path, '/\\') . DIRECTORY_SEPARATOR . '.env';

        if (!file_exists($envFile)) {
            throw new Exception("Archivo .env no encontrado en: $envFile");
        }

        if (!is_readable($envFile)) {
            throw new Exception("Archivo .env no es legible: $envFile");
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Ignorar comentarios
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parsear línea KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);

                $key = trim($key);
                $value = trim($value);

                // Remover comillas si existen
                $value = self::removeQuotes($value);

                // Establecer en $_ENV y $_SERVER
                if (!array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }

        return true;
    }

    /**
     * Valida que existan variables requeridas
     *
     * @param array $required Array de nombres de variables requeridas
     * @throws Exception Si falta alguna variable
     */
    public static function required(array $required) {
        foreach ($required as $key) {
            if (!isset($_ENV[$key]) || $_ENV[$key] === '') {
                throw new Exception("Variable de entorno requerida no encontrada: $key");
            }
        }
    }

    /**
     * Obtiene valor de variable de entorno
     *
     * @param string $key Nombre de la variable
     * @param mixed $default Valor por defecto si no existe
     * @return mixed
     */
    public static function get($key, $default = null) {
        return $_ENV[$key] ?? $default;
    }

    /**
     * Remueve comillas de un valor
     *
     * @param string $value
     * @return string
     */
    private static function removeQuotes($value) {
        // Remover comillas dobles
        if (preg_match('/^"(.*)"$/', $value, $matches)) {
            return $matches[1];
        }

        // Remover comillas simples
        if (preg_match("/^'(.*)'$/", $value, $matches)) {
            return $matches[1];
        }

        return $value;
    }
}

// Auto-cargar si se incluye este archivo
if (defined('BASE_PATH')) {
    SimpleEnvLoader::load(BASE_PATH);
}
?>
