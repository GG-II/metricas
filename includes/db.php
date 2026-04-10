<?php
/**
 * Conexión a la base de datos con PDO
 */

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    // Guardar conexión en variable global para usar en todo el proyecto
    $GLOBALS['db'] = $pdo;
    
} catch (PDOException $e) {
    // En desarrollo, mostrar el error
    if (ENVIRONMENT === 'development') {
        die("Error de conexión a la base de datos: " . $e->getMessage());
    } else {
        // En producción, mostrar mensaje genérico y logear el error
        error_log("DB Connection Error: " . $e->getMessage());
        die("Error al conectar con la base de datos. Por favor, contacta al administrador.");
    }
}

/**
 * Función helper para obtener la conexión PDO
 * 
 * @return PDO
 */
function getDB() {
    return $GLOBALS['db'];
}