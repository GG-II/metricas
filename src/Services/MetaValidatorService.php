<?php

namespace App\Services;

use App\Models\Meta;
use PDO;

class MetaValidatorService
{
    private $db;
    private $metaModel;

    public function __construct()
    {
        $this->db = getDB();
        $this->metaModel = new Meta();
    }

    /**
     * Obtiene la meta anual para una métrica y ejercicio
     */
    public function getMetaAnual($metrica_id, $ejercicio)
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM metas_metricas
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
     * Calcula el valor mensual sugerido basado en la meta anual
     * Divide el RESTANTE entre los meses NO ASIGNADOS para mejor distribución
     */
    public function getValorMensualSugerido($metrica_id, $ejercicio)
    {
        $meta_anual = $this->getMetaAnual($metrica_id, $ejercicio);

        if (!$meta_anual) {
            return null;
        }

        $suma_mensual = $this->getSumaMensual($metrica_id, $ejercicio);
        $meses_configurados = $this->contarMetasMensuales($metrica_id, $ejercicio);

        $restante = $meta_anual['valor_objetivo'] - $suma_mensual;
        $meses_sin_asignar = 12 - $meses_configurados;

        // Si no hay meses sin asignar, no hay sugerencia
        if ($meses_sin_asignar <= 0) {
            return 0;
        }

        // Valor sugerido = restante / meses sin asignar
        return round($restante / $meses_sin_asignar, 2);
    }

    /**
     * Obtiene la suma de todas las metas mensuales para una métrica en un ejercicio
     */
    public function getSumaMensual($metrica_id, $ejercicio, $excluir_periodo_id = null)
    {
        $sql = "
            SELECT COALESCE(SUM(valor_objetivo), 0) as suma
            FROM metas_metricas
            WHERE metrica_id = ?
            AND tipo_meta = 'mensual'
            AND ejercicio = ?
            AND activo = 1
        ";

        $params = [$metrica_id, $ejercicio];

        if ($excluir_periodo_id) {
            $sql .= " AND periodo_id != ?";
            $params[] = $excluir_periodo_id;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        return (float)$result['suma'];
    }

    /**
     * Calcula el valor máximo permitido para una nueva meta mensual
     *
     * @param int $metrica_id
     * @param int $ejercicio
     * @param int|null $excluir_periodo_id ID del período a excluir (para edición)
     * @return array ['max_permitido' => float, 'meta_anual' => float, 'suma_mensual' => float]
     */
    public function getMaximoPermitido($metrica_id, $ejercicio, $excluir_periodo_id = null)
    {
        $meta_anual = $this->getMetaAnual($metrica_id, $ejercicio);

        if (!$meta_anual) {
            return [
                'tiene_meta_anual' => false,
                'max_permitido' => null,
                'meta_anual' => null,
                'suma_mensual' => 0,
                'restante' => null
            ];
        }

        $suma_mensual = $this->getSumaMensual($metrica_id, $ejercicio, $excluir_periodo_id);
        $restante = $meta_anual['valor_objetivo'] - $suma_mensual;

        return [
            'tiene_meta_anual' => true,
            'max_permitido' => max(0, $restante),
            'meta_anual' => (float)$meta_anual['valor_objetivo'],
            'suma_mensual' => $suma_mensual,
            'restante' => $restante
        ];
    }

    /**
     * Valida si se puede crear/actualizar una meta mensual
     *
     * @param int $metrica_id
     * @param int $ejercicio
     * @param float $valor_propuesto
     * @param int|null $excluir_periodo_id
     * @return array ['valido' => bool, 'mensaje' => string, 'detalles' => array]
     */
    public function validarMetaMensual($metrica_id, $ejercicio, $valor_propuesto, $excluir_periodo_id = null)
    {
        $info = $this->getMaximoPermitido($metrica_id, $ejercicio, $excluir_periodo_id);

        if (!$info['tiene_meta_anual']) {
            // No hay meta anual, permitir cualquier valor
            return [
                'valido' => true,
                'mensaje' => 'No hay meta anual para validar',
                'detalles' => $info
            ];
        }

        $nueva_suma = $info['suma_mensual'] + $valor_propuesto;

        if ($nueva_suma > $info['meta_anual']) {
            $mensaje = sprintf(
                'El valor propuesto ($%s) excede el máximo permitido. Meta anual: $%s, Ya asignado: $%s, Restante: $%s',
                number_format($valor_propuesto, 2),
                number_format($info['meta_anual'], 2),
                number_format($info['suma_mensual'], 2),
                number_format($info['restante'], 2)
            );

            return [
                'valido' => false,
                'mensaje' => $mensaje,
                'detalles' => $info
            ];
        }

        return [
            'valido' => true,
            'mensaje' => 'Valor válido',
            'detalles' => $info
        ];
    }

    /**
     * Cuenta cuántas metas mensuales existen para una métrica en un ejercicio
     */
    public function contarMetasMensuales($metrica_id, $ejercicio)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total
            FROM metas_metricas
            WHERE metrica_id = ?
            AND tipo_meta = 'mensual'
            AND ejercicio = ?
            AND activo = 1
        ");
        $stmt->execute([$metrica_id, $ejercicio]);
        $result = $stmt->fetch();

        return (int)$result['total'];
    }

    /**
     * Obtiene información completa de metas para una métrica
     */
    public function getInfoCompletaMetas($metrica_id, $ejercicio)
    {
        $meta_anual = $this->getMetaAnual($metrica_id, $ejercicio);
        $suma_mensual = $this->getSumaMensual($metrica_id, $ejercicio);
        $total_meses = $this->contarMetasMensuales($metrica_id, $ejercicio);
        $valor_sugerido = $this->getValorMensualSugerido($metrica_id, $ejercicio);

        return [
            'tiene_meta_anual' => (bool)$meta_anual,
            'meta_anual' => $meta_anual ? (float)$meta_anual['valor_objetivo'] : null,
            'suma_mensual' => $suma_mensual,
            'meses_configurados' => $total_meses,
            'meses_restantes' => 12 - $total_meses,
            'valor_sugerido_mensual' => $valor_sugerido,
            'restante_disponible' => $meta_anual ? ($meta_anual['valor_objetivo'] - $suma_mensual) : null,
            'porcentaje_asignado' => $meta_anual ? round(($suma_mensual / $meta_anual['valor_objetivo']) * 100, 1) : null
        ];
    }
}
