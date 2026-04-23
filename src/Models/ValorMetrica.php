<?php
namespace App\Models;

use PDO;

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
        $metrica = $metricaModel->find($metricaId);

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
     * Obtener valor de métrica para un período específico + período anterior
     */
    public function getByMetricaYPeriodo($metrica_id, $periodo_id) {
        // Obtener información del período actual
        $sql = "SELECT ejercicio, periodo FROM periodos WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$periodo_id]);
        $periodo_actual = $stmt->fetch();

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
                WHERE vm.metrica_id = ?
                  AND vm.periodo_id = ?
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$metrica_id, $periodo_id]);

        $valor_actual = $stmt->fetch();

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
                WHERE ejercicio = ?
                  AND periodo = ?
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$periodo_anterior_ejercicio, $periodo_anterior_mes]);

        $periodo_anterior_data = $stmt->fetch();

        // Obtener valor anterior si existe
        $valor_anterior = null;
        if ($periodo_anterior_data) {
            $sql = "SELECT COALESCE(valor_decimal, valor_numero) as valor
                    FROM {$this->table}
                    WHERE metrica_id = ?
                      AND periodo_id = ?
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$metrica_id, $periodo_anterior_data['id']]);

            $result = $stmt->fetch();
            $valor_anterior = $result ? $result['valor'] : null;
        }

        // Agregar valor anterior al resultado
        $valor_actual['valor_anterior'] = $valor_anterior;

        return $valor_actual;
    }

    /**
     * Obtener histórico de valores de una métrica
     */
    public function getHistorico($metrica_id, $limite = 12) {
        $sql = "SELECT vm.*,
                       p.ejercicio,
                       p.periodo,
                       p.nombre as periodo_nombre,
                       COALESCE(vm.valor_decimal, vm.valor_numero) as valor
                FROM {$this->table} vm
                INNER JOIN periodos p ON vm.periodo_id = p.id
                WHERE vm.metrica_id = ?
                ORDER BY p.ejercicio ASC, p.periodo ASC
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $metrica_id, PDO::PARAM_INT);
        $stmt->bindValue(2, $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obtener histórico de valores con sus metas
     */
    public function getHistoricoConMetas($metrica_id, $limite = 6) {
        $sql = "
            SELECT
                p.id as periodo_id,
                p.ejercicio,
                p.periodo,
                p.nombre as periodo_nombre,
                COALESCE(vm.valor_numero, vm.valor_decimal) as valor,
                mm.valor_objetivo,
                mm.tipo_comparacion,
                mm.valor_min,
                mm.valor_max
            FROM periodos p
            LEFT JOIN {$this->table} vm ON p.id = vm.periodo_id AND vm.metrica_id = ?
            LEFT JOIN metas_metricas mm ON p.id = mm.periodo_id AND mm.metrica_id = ? AND mm.activo = 1
            WHERE p.activo = 1
            ORDER BY p.ejercicio DESC, p.periodo DESC
            LIMIT ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$metrica_id, $metrica_id, $limite]);
        $results = $stmt->fetchAll();

        // Invertir para que vaya de más antiguo a más reciente
        return array_reverse($results);
    }

    /**
     * Buscar valor simple por métrica y período
     */
    public function findByMetricaYPeriodo($metrica_id, $periodo_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE metrica_id = ? AND periodo_id = ?
            LIMIT 1
        ");
        $stmt->execute([$metrica_id, $periodo_id]);
        return $stmt->fetch();
    }
}
