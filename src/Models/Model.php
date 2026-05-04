<?php
namespace App\Models;

use PDO;
use PDOException;

/**
 * Clase base para todos los modelos
 * Proporciona funcionalidad CRUD básica y conexión a BD
 */
class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct() {
        $this->db = $this->getConnection();
    }

    /**
     * Obtener conexión a la base de datos (singleton)
     */
    private function getConnection() {
        static $db = null;

        if ($db === null) {
            // Cargar config.php si las constantes no están definidas
            if (!defined('DB_HOST')) {
                require_once __DIR__ . '/../../config.php';
            }

            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];

            try {
                $db = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                die('Error de conexión a la base de datos: ' . $e->getMessage());
            }
        }

        return $db;
    }

    /**
     * Obtener todos los registros
     */
    public function getAll($conditions = '') {
        $sql = "SELECT * FROM {$this->table}";
        if ($conditions) {
            $sql .= " WHERE {$conditions}";
        }

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtener un registro por ID
     */
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Obtener un registro por condición
     */
    public function findBy($field, $value) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$field} = ?");
        $stmt->execute([$value]);
        return $stmt->fetch();
    }

    /**
     * Crear un nuevo registro
     */
    public function create($data) {
        $fields = array_keys($data);
        $values = array_values($data);
        $placeholders = array_fill(0, count($fields), '?');

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ")
                VALUES (" . implode(', ', $placeholders) . ")";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);

        return $this->db->lastInsertId();
    }

    /**
     * Actualizar un registro
     */
    public function update($id, $data) {
        $fields = array_keys($data);
        $values = array_values($data);

        $set = implode(' = ?, ', $fields) . ' = ?';

        $sql = "UPDATE {$this->table} SET {$set} WHERE {$this->primaryKey} = ?";

        $values[] = $id;
        $stmt = $this->db->prepare($sql);

        return $stmt->execute($values);
    }

    /**
     * Eliminar un registro
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Ejecutar consulta personalizada
     */
    public function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
