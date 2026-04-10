<?php
/**
 * Modelo Periodo
 */

require_once __DIR__ . '/Model.php';

class Periodo extends Model {
    protected $table = 'periodos';
    
    /**
     * Obtener períodos activos recientes
     */
    public function getRecientes($limit = 12) {
        $stmt = $this->db->prepare("
            SELECT * FROM periodos 
            WHERE activo = 1 
            ORDER BY ejercicio DESC, periodo DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener período por ejercicio y mes
     */
    public function getByEjercicioPeriodo($ejercicio, $periodo) {
        $stmt = $this->db->prepare("
            SELECT * FROM periodos 
            WHERE ejercicio = ? AND periodo = ?
        ");
        $stmt->execute([$ejercicio, $periodo]);
        
        return $stmt->fetch();
    }
    
    /**
     * Obtener período actual (mes actual)
     */
    public function getActual() {
        $ejercicio = date('Y');
        $periodo = (int)date('m');
        
        return $this->getByEjercicioPeriodo($ejercicio, $periodo);
    }

    /**
     * Obtener el período más reciente (actual)
     * 
     * @return array|null
     */
    public function getPeriodoActual() {
        $sql = "SELECT * FROM {$this->table} 
                ORDER BY ejercicio DESC, periodo DESC 
                LIMIT 1";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener período por ejercicio y mes
     * 
     * @param int $ejercicio Año (ej: 2026)
     * @param int $periodo Mes (1-12)
     * @return array|null
     */
    public function getByEjercicioYPeriodo($ejercicio, $periodo) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE ejercicio = :ejercicio 
                  AND periodo = :periodo 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'ejercicio' => $ejercicio,
            'periodo' => $periodo
        ]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}