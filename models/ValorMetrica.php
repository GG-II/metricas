<?php
/**
 * Modelo ValorMetrica
 */

require_once __DIR__ . '/Model.php';

class ValorMetrica extends Model {
    protected $table = 'valores_metricas';
    
    /**
     * Obtener valor de una métrica en un período
     */
    public function getValor($metricaId, $periodoId) {
        $stmt = $this->db->prepare("
            SELECT * FROM valores_metricas 
            WHERE metrica_id = ? AND periodo_id = ?
        ");
        $stmt->execute([$metricaId, $periodoId]);
        
        return $stmt->fetch();
    }
    
    /**
     * Guardar o actualizar valor
     */
    public function guardarValor($metricaId, $periodoId, $valor, $nota = null, $usuarioId = null) {
        // Verificar si ya existe
        $existe = $this->getValor($metricaId, $periodoId);
        
        // Determinar el campo a usar según tipo de métrica
        $metricaModel = new Metrica();
        $metrica = $metricaModel->getById($metricaId);
        
        $campo = ($metrica['tipo_valor'] === 'numero') ? 'valor_numero' : 'valor_decimal';
        
        if ($existe) {
            // Actualizar
            $data = [
                $campo => $valor,
                'nota' => $nota,
                'usuario_modificacion_id' => $usuarioId
            ];
            
            return $this->update($existe['id'], $data);
        } else {
            // Crear nuevo
            $data = [
                'metrica_id' => $metricaId,
                'periodo_id' => $periodoId,
                $campo => $valor,
                'nota' => $nota,
                'usuario_registro_id' => $usuarioId
            ];
            
            return $this->create($data);
        }
    }
    
    /**
     * Obtener valores de período anterior (para comparativas)
     */
    public function getValoresPeriodoAnterior($periodoId) {
        // Obtener período actual
        $periodoModel = new Periodo();
        $periodoActual = $periodoModel->getById($periodoId);
        
        if (!$periodoActual) {
            return [];
        }
        
        // Calcular período anterior
        $ejercicio = $periodoActual['ejercicio'];
        $periodo = $periodoActual['periodo'];
        
        if ($periodo == 1) {
            $ejercicioAnterior = $ejercicio - 1;
            $periodoAnterior = 12;
        } else {
            $ejercicioAnterior = $ejercicio;
            $periodoAnterior = $periodo - 1;
        }
        
        // Obtener ID del período anterior
        $stmt = $this->db->prepare("
            SELECT id FROM periodos 
            WHERE ejercicio = ? AND periodo = ?
        ");
        $stmt->execute([$ejercicioAnterior, $periodoAnterior]);
        $periodoAnt = $stmt->fetch();
        
        if (!$periodoAnt) {
            return [];
        }
        
        // Obtener todos los valores del período anterior
        $stmt = $this->db->prepare("
            SELECT metrica_id, COALESCE(valor_numero, valor_decimal) as valor
            FROM valores_metricas
            WHERE periodo_id = ?
        ");
        $stmt->execute([$periodoAnt['id']]);
        $valores = $stmt->fetchAll();
        
        // Indexar por metrica_id
        $resultado = [];
        foreach ($valores as $v) {
            $resultado[$v['metrica_id']] = $v['valor'];
        }
        
        return $resultado;
    }

    /**
 * Obtener valor de métrica para un período específico + período anterior
 */
public function getByMetricaYPeriodo($metrica_id, $periodo_id) {
    // Obtener información del período actual
    $sql = "SELECT ejercicio, periodo FROM periodos WHERE id = :periodo_id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['periodo_id' => $periodo_id]);
    $periodo_actual = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$periodo_actual) return null;
    
    // Obtener valor actual
    $sql = "SELECT vm.*, 
                   m.nombre, 
                   m.unidad, 
                   m.tipo_valor,
                   m.icono,
                   COALESCE(vm.valor_decimal, vm.valor_numero) as valor
            FROM {$this->table} vm
            INNER JOIN metricas m ON vm.metrica_id = m.id
            WHERE vm.metrica_id = :metrica_id 
              AND vm.periodo_id = :periodo_id
            LIMIT 1";
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
        'metrica_id' => $metrica_id,
        'periodo_id' => $periodo_id
    ]);
    
    $valor_actual = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$valor_actual) return null;
    
    // Calcular período anterior
    $periodo_anterior_mes = $periodo_actual['periodo'] - 1;
    $periodo_anterior_ejercicio = $periodo_actual['ejercicio'];
    
    if ($periodo_anterior_mes < 1) {
        $periodo_anterior_mes = 12;
        $periodo_anterior_ejercicio--;
    }
    
    // Obtener ID del período anterior
    $sql = "SELECT id FROM periodos 
            WHERE ejercicio = :ejercicio 
              AND periodo = :periodo 
            LIMIT 1";
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
        'ejercicio' => $periodo_anterior_ejercicio,
        'periodo' => $periodo_anterior_mes
    ]);
    
    $periodo_anterior_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Obtener valor anterior si existe
    $valor_anterior = null;
    if ($periodo_anterior_data) {
        $sql = "SELECT COALESCE(valor_decimal, valor_numero) as valor
                FROM {$this->table}
                WHERE metrica_id = :metrica_id 
                  AND periodo_id = :periodo_id
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'metrica_id' => $metrica_id,
            'periodo_id' => $periodo_anterior_data['id']
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $valor_anterior = $result ? $result['valor'] : null;
    }
    
    // Agregar valor anterior al resultado
    $valor_actual['valor_anterior'] = $valor_anterior;
    
    return $valor_actual;
}
    
    /**
     * Obtener valor del período anterior
     * 
     * @param int $metrica_id ID de la métrica
     * @param int $ejercicio Año del período actual
     * @param int $periodo_mes Mes del período actual
     * @return float|null
     */
    private function getValorPeriodoAnterior($metrica_id, $ejercicio, $periodo_mes) {
        // Calcular período anterior
        if ($periodo_mes == 1) {
            $ejercicio_anterior = $ejercicio - 1;
            $periodo_anterior = 12;
        } else {
            $ejercicio_anterior = $ejercicio;
            $periodo_anterior = $periodo_mes - 1;
        }
        
        $sql = "SELECT COALESCE(vm.valor_decimal, vm.valor_numero) as valor
                FROM valores_metricas vm
                INNER JOIN periodos p ON vm.periodo_id = p.id
                WHERE vm.metrica_id = :metrica_id
                  AND p.ejercicio = :ejercicio
                  AND p.periodo = :periodo
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'metrica_id' => $metrica_id,
            'ejercicio' => $ejercicio_anterior,
            'periodo' => $periodo_anterior
        ]);
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $resultado ? $resultado['valor'] : null;
    }
    
    /**
     * Obtener histórico de valores de una métrica
     * 
     * @param int $metrica_id ID de la métrica
     * @param int $cantidad_periodos Cantidad de períodos a obtener
     * @return array
     */
    public function getHistorico($metrica_id, $limite = 12) {
    $sql = "SELECT vm.*,
                   p.ejercicio,
                   p.periodo,
                   p.nombre as periodo_nombre,
                   COALESCE(vm.valor_decimal, vm.valor_numero) as valor
            FROM {$this->table} vm
            INNER JOIN periodos p ON vm.periodo_id = p.id
            WHERE vm.metrica_id = :metrica_id
            ORDER BY p.ejercicio ASC, p.periodo ASC
            LIMIT :limite";
    
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':metrica_id', $metrica_id, PDO::PARAM_INT);
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}