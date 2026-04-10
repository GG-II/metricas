<?php
/**
 * Modelo Metrica
 */

require_once __DIR__ . '/Model.php';

class Metrica extends Model {
    protected $table = 'metricas';
    
    /**
     * Obtener métricas por área
     */
    public function getByArea($areaId, $soloActivas = true) {
        $sql = "SELECT * FROM metricas WHERE area_id = ?";
        $params = [$areaId];
        
        if ($soloActivas) {
            $sql .= " AND activo = 1";
        }
        
        $sql .= " ORDER BY orden ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener métricas con sus valores para un período
     */
    public function getByAreaConValores($areaId, $periodoId) {
        $sql = "
    SELECT 
        m.id,
        m.nombre,
        m.slug,
        m.icono,
        m.tipo_valor,
        m.unidad,
        m.tipo_grafico,
        m.orden,
        m.grupo,
        m.descripcion,
        COALESCE(vm.valor_numero, vm.valor_decimal) as valor,
        vm.nota
    FROM metricas m
            LEFT JOIN valores_metricas vm ON m.id = vm.metrica_id AND vm.periodo_id = ?
            WHERE m.area_id = ? AND m.activo = 1
            ORDER BY m.orden ASC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$periodoId, $areaId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener métricas agrupadas
     */
    public function getAgrupadasByArea($areaId, $periodoId) {
        $metricas = $this->getByAreaConValores($areaId, $periodoId);
        
        $agrupadas = [];
        foreach ($metricas as $metrica) {
            $grupo = $metrica['grupo'] ?? 'sin_grupo';
            if (!isset($agrupadas[$grupo])) {
                $agrupadas[$grupo] = [];
            }
            $agrupadas[$grupo][] = $metrica;
        }
        
        return $agrupadas;
    }
}