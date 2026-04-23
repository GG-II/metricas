<?php
namespace App\Models;

class Periodo extends Model {
    protected $table = 'periodos';

    /**
     * Obtener todos los períodos activos
     */
    public function getAll($conditions = '') {
        $stmt = $this->db->query("
            SELECT * FROM {$this->table}
            WHERE activo = 1
            ORDER BY ejercicio DESC, periodo DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Obtener períodos por ejercicio
     */
    public function getByEjercicio($ejercicio) {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE ejercicio = ? AND activo = 1
            ORDER BY periodo
        ");
        $stmt->execute([$ejercicio]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener el período actual
     */
    public function getCurrentPeriodo() {
        $stmt = $this->db->query("
            SELECT * FROM {$this->table}
            WHERE activo = 1
            AND CURDATE() BETWEEN fecha_inicio AND fecha_fin
            LIMIT 1
        ");
        return $stmt->fetch();
    }

    /**
     * Obtener período por ejercicio y periodo
     */
    public function findByEjercicioAndPeriodo($ejercicio, $periodo) {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE ejercicio = ? AND periodo = ?
        ");
        $stmt->execute([$ejercicio, $periodo]);
        return $stmt->fetch();
    }

    /**
     * Obtener todos los períodos con estadísticas
     */
    public function getAllWithStats() {
        $stmt = $this->db->query("
            SELECT p.*,
                   COUNT(DISTINCT vm.id) as total_valores,
                   CASE WHEN CURDATE() BETWEEN p.fecha_inicio AND p.fecha_fin THEN 1 ELSE 0 END as es_actual
            FROM {$this->table} p
            LEFT JOIN valores_metricas vm ON p.id = vm.periodo_id
            GROUP BY p.id
            ORDER BY p.ejercicio DESC, p.periodo DESC
        ");
        return $stmt->fetchAll();
    }
}
