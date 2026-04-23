<?php
namespace App\Models;

class Area extends Model {
    protected $table = 'areas';

    /**
     * Obtener todas las áreas activas
     */
    public function getAll($conditions = '') {
        $stmt = $this->db->query("
            SELECT a.*, d.nombre as departamento_nombre
            FROM {$this->table} a
            JOIN departamentos d ON a.departamento_id = d.id
            WHERE a.activo = 1
            ORDER BY d.orden, a.orden, a.nombre
        ");
        return $stmt->fetchAll();
    }

    /**
     * Obtener áreas por departamento
     */
    public function getByDepartamento($departamento_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE departamento_id = ? AND activo = 1
            ORDER BY orden, nombre
        ");
        $stmt->execute([$departamento_id]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener área con información del departamento
     */
    public function findWithDepartamento($id) {
        $stmt = $this->db->prepare("
            SELECT a.*, d.nombre as departamento_nombre, d.color as departamento_color
            FROM {$this->table} a
            JOIN departamentos d ON a.departamento_id = d.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Obtener área por slug
     */
    public function findBySlug($slug) {
        return $this->findBy('slug', $slug);
    }

    /**
     * Obtener todas las áreas con estadísticas
     */
    public function getAllWithStats($departamento_id = null) {
        $sql = "
            SELECT a.*,
                   d.nombre as departamento_nombre,
                   d.color as departamento_color,
                   COUNT(DISTINCT m.id) as total_metricas,
                   COUNT(DISTINCT g.id) as total_graficos
            FROM {$this->table} a
            JOIN departamentos d ON a.departamento_id = d.id
            LEFT JOIN metricas m ON a.id = m.area_id AND m.activo = 1
            LEFT JOIN configuracion_graficos g ON a.id = g.area_id AND g.activo = 1
        ";

        if ($departamento_id) {
            $sql .= " WHERE a.departamento_id = ?";
        }

        $sql .= "
            GROUP BY a.id
            ORDER BY a.activo DESC, d.orden, a.orden, a.nombre
        ";

        if ($departamento_id) {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$departamento_id]);
        } else {
            $stmt = $this->db->query($sql);
        }

        return $stmt->fetchAll();
    }

    /**
     * Obtener el orden máximo por departamento
     */
    public function getMaxOrdenByDepartamento($departamento_id) {
        $stmt = $this->db->prepare("
            SELECT MAX(orden) as max_orden
            FROM {$this->table}
            WHERE departamento_id = ?
        ");
        $stmt->execute([$departamento_id]);
        $result = $stmt->fetch();
        return (int)($result['max_orden'] ?? 0);
    }
}
