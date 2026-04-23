<?php
namespace App\Models;

class Meta extends Model {
    protected $table = 'metas_metricas';

    /**
     * Obtener meta mensual específica
     */
    public function getMetaMensual($metrica_id, $periodo_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE metrica_id = ?
            AND tipo_meta = 'mensual'
            AND periodo_id = ?
            AND activo = 1
            LIMIT 1
        ");
        $stmt->execute([$metrica_id, $periodo_id]);
        return $stmt->fetch();
    }

    /**
     * Obtener meta anual
     */
    public function getMetaAnual($metrica_id, $ejercicio) {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE metrica_id = ?
            AND tipo_meta = 'anual'
            AND ejercicio = ?
            AND activo = 1
            LIMIT 1
        ");
        $stmt->execute([$metrica_id, $ejercicio]);
        return $stmt->fetch();
    }

    /**
     * Obtener meta aplicable para un período específico
     * Prioriza meta mensual, si no existe busca meta anual y calcula proporcional
     */
    public function getMetaAplicable($metrica_id, $periodo_id) {
        // Obtener información del período
        $periodoModel = new Periodo();
        $periodo = $periodoModel->find($periodo_id);

        if (!$periodo) return null;

        // 1. Buscar meta mensual específica
        $metaMensual = $this->getMetaMensual($metrica_id, $periodo_id);
        if ($metaMensual) {
            return [
                'id' => $metaMensual['id'],
                'valor_objetivo' => $metaMensual['valor_objetivo'],
                'tipo_meta' => 'mensual',
                'tipo_comparacion' => $metaMensual['tipo_comparacion'],
                'origen' => 'mensual'
            ];
        }

        // 2. Si no existe mensual, buscar anual y dividir entre 12
        $metaAnual = $this->getMetaAnual($metrica_id, $periodo['ejercicio']);
        if ($metaAnual) {
            return [
                'id' => $metaAnual['id'],
                'valor_objetivo' => round($metaAnual['valor_objetivo'] / 12, 2),
                'valor_anual' => $metaAnual['valor_objetivo'],
                'tipo_meta' => 'anual',
                'tipo_comparacion' => $metaAnual['tipo_comparacion'],
                'origen' => 'anual_mensualizado'
            ];
        }

        return null;
    }

    /**
     * Obtener todas las metas de una métrica
     */
    public function getByMetrica($metrica_id) {
        $stmt = $this->db->prepare("
            SELECT m.*,
                   p.nombre as periodo_nombre,
                   p.ejercicio,
                   p.periodo
            FROM {$this->table} m
            LEFT JOIN periodos p ON m.periodo_id = p.id
            WHERE m.metrica_id = ?
            ORDER BY
                CASE m.tipo_meta
                    WHEN 'anual' THEN m.ejercicio
                    ELSE p.ejercicio
                END DESC,
                CASE m.tipo_meta
                    WHEN 'mensual' THEN p.periodo
                    ELSE 0
                END ASC
        ");
        $stmt->execute([$metrica_id]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener metas por departamento (para dept_admin)
     */
    public function getByDepartamento($departamento_id) {
        $stmt = $this->db->prepare("
            SELECT mm.*,
                   met.nombre as metrica_nombre,
                   met.unidad as metrica_unidad,
                   a.nombre as area_nombre,
                   p.nombre as periodo_nombre,
                   p.ejercicio,
                   p.periodo
            FROM {$this->table} mm
            JOIN metricas met ON mm.metrica_id = met.id
            JOIN areas a ON met.area_id = a.id
            LEFT JOIN periodos p ON mm.periodo_id = p.id
            WHERE a.departamento_id = ?
            ORDER BY
                CASE mm.tipo_meta
                    WHEN 'anual' THEN mm.ejercicio
                    ELSE p.ejercicio
                END DESC,
                a.nombre,
                met.nombre
        ");
        $stmt->execute([$departamento_id]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener todas las metas (para super_admin)
     */
    public function getAllWithRelations() {
        $stmt = $this->db->query("
            SELECT mm.*,
                   met.nombre as metrica_nombre,
                   met.unidad as metrica_unidad,
                   a.nombre as area_nombre,
                   d.nombre as departamento_nombre,
                   p.nombre as periodo_nombre,
                   p.ejercicio,
                   p.periodo
            FROM {$this->table} mm
            JOIN metricas met ON mm.metrica_id = met.id
            JOIN areas a ON met.area_id = a.id
            JOIN departamentos d ON a.departamento_id = d.id
            LEFT JOIN periodos p ON mm.periodo_id = p.id
            ORDER BY
                d.nombre,
                CASE mm.tipo_meta
                    WHEN 'anual' THEN mm.ejercicio
                    ELSE p.ejercicio
                END DESC,
                a.nombre,
                met.nombre
        ");
        return $stmt->fetchAll();
    }

    /**
     * Verificar si ya existe una meta
     */
    public function exists($metrica_id, $tipo_meta, $ejercicio = null, $periodo_id = null) {
        if ($tipo_meta === 'anual') {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count FROM {$this->table}
                WHERE metrica_id = ? AND tipo_meta = 'anual' AND ejercicio = ?
            ");
            $stmt->execute([$metrica_id, $ejercicio]);
        } else {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count FROM {$this->table}
                WHERE metrica_id = ? AND tipo_meta = 'mensual' AND periodo_id = ?
            ");
            $stmt->execute([$metrica_id, $periodo_id]);
        }

        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Calcular porcentaje de cumplimiento
     */
    public function calcularCumplimiento($valor_real, $valor_objetivo, $tipo_comparacion = 'mayor_igual') {
        if ($valor_objetivo == 0) return 0;

        $porcentaje = ($valor_real / $valor_objetivo) * 100;

        switch ($tipo_comparacion) {
            case 'mayor_igual':
                return min($porcentaje, 100); // No más de 100%
            case 'menor_igual':
                // Invertir: si real < objetivo, está bien
                return $valor_real <= $valor_objetivo ? 100 : max(0, 100 - (($valor_real - $valor_objetivo) / $valor_objetivo * 100));
            case 'igual':
                // Penalizar desviación
                $desviacion = abs($valor_real - $valor_objetivo) / $valor_objetivo * 100;
                return max(0, 100 - $desviacion);
            default:
                return $porcentaje;
        }
    }
}
