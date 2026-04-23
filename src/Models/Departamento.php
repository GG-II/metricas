<?php
namespace App\Models;

class Departamento extends Model {
    protected $table = 'departamentos';

    /**
     * Obtener todos los departamentos activos
     */
    public function getAll($conditions = '') {
        $stmt = $this->db->query("
            SELECT * FROM {$this->table}
            WHERE activo = 1
            ORDER BY orden, nombre
        ");
        return $stmt->fetchAll();
    }

    /**
     * Obtener departamento con sus áreas
     */
    public function getWithAreas($id) {
        $stmt = $this->db->prepare("
            SELECT d.*,
                   COUNT(a.id) as total_areas
            FROM {$this->table} d
            LEFT JOIN areas a ON d.id = a.departamento_id AND a.activo = 1
            WHERE d.id = ?
            GROUP BY d.id
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Obtener áreas de un departamento
     */
    public function getAreas($departamento_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM areas
            WHERE departamento_id = ? AND activo = 1
            ORDER BY orden, nombre
        ");
        $stmt->execute([$departamento_id]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener todos los departamentos con estadísticas
     */
    public function getAllWithStats() {
        $stmt = $this->db->query("
            SELECT d.*,
                   COUNT(DISTINCT a.id) as total_areas,
                   COUNT(DISTINCT u.id) as total_usuarios
            FROM {$this->table} d
            LEFT JOIN areas a ON d.id = a.departamento_id AND a.activo = 1
            LEFT JOIN usuarios u ON d.id = u.departamento_id AND u.activo = 1
            GROUP BY d.id
            ORDER BY d.activo DESC, d.orden, d.nombre
        ");
        return $stmt->fetchAll();
    }

    /**
     * Obtener el orden máximo actual
     */
    public function getMaxOrden() {
        $stmt = $this->db->query("SELECT MAX(orden) as max_orden FROM {$this->table}");
        $result = $stmt->fetch();
        return (int)($result['max_orden'] ?? 0);
    }
}
