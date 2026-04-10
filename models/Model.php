<?php
/**
 * Modelo Base
 * Clase padre para todos los modelos
 */

class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Obtener todos los registros
     */
    public function getAll($conditions = [], $orderBy = null) {
        $sql = "SELECT * FROM {$this->table}";
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "$key = ?";
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($conditions));
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener por ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Crear nuevo registro
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
     * Actualizar registro
     */
    public function update($id, $data) {
        $fields = [];
        foreach (array_keys($data) as $field) {
            $fields[] = "$field = ?";
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . 
               " WHERE {$this->primaryKey} = ?";
        
        $values = array_values($data);
        $values[] = $id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }
    
    /**
     * Eliminar registro
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Contar registros
     */
    public function count($conditions = []) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "$key = ?";
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($conditions));
        $result = $stmt->fetch();
        
        return $result['total'];
    }
}