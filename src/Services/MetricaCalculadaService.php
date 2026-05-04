<?php
namespace App\Services;

use App\Models\Metrica;
use App\Models\ValorMetrica;

/**
 * Servicio para manejar métricas calculadas
 * Calcula automáticamente valores basados en componentes
 */
class MetricaCalculadaService
{
    private $db;
    private $metricaModel;
    private $valorModel;

    public function __construct()
    {
        $this->db = getDB();
        $this->metricaModel = new Metrica();
        $this->valorModel = new ValorMetrica();
    }

    /**
     * Verificar si un área pertenece al departamento Global
     *
     * Detecta si un área es global verificando el tipo de su departamento padre.
     * CUALQUIER área dentro de un departamento tipo='global' es considerada área global.
     *
     * @param int $area_id ID del área a verificar
     * @return bool True si el área pertenece a un departamento global
     */
    private function esAreaGlobal($area_id)
    {
        $stmt = $this->db->prepare("
            SELECT d.tipo
            FROM areas a
            JOIN departamentos d ON a.departamento_id = d.id
            WHERE a.id = ?
        ");
        $stmt->execute([$area_id]);
        $result = $stmt->fetch();

        return $result && $result['tipo'] === 'global';
    }

    /**
     * Método público para verificar si un área es global
     *
     * @param int $area_id ID del área a verificar
     * @return bool True si el área es global
     */
    public function isAreaGlobal($area_id)
    {
        return $this->esAreaGlobal($area_id);
    }

    /**
     * Obtener componentes de una métrica calculada
     */
    public function getComponentes($metrica_calculada_id)
    {
        $stmt = $this->db->prepare("
            SELECT mc.*,
                   m.nombre as metrica_nombre,
                   m.unidad as metrica_unidad,
                   m.tipo_valor,
                   a.nombre as area_nombre
            FROM metricas_componentes mc
            JOIN metricas m ON mc.metrica_componente_id = m.id
            JOIN areas a ON m.area_id = a.id
            WHERE mc.metrica_calculada_id = ? AND mc.activo = 1
            ORDER BY mc.orden ASC
        ");
        $stmt->execute([$metrica_calculada_id]);
        return $stmt->fetchAll();
    }

    /**
     * Guardar componentes de una métrica calculada
     */
    public function guardarComponentes($metrica_calculada_id, $componentes, $operacion = 'suma')
    {
        // Primero, desactivar componentes existentes
        $stmt = $this->db->prepare("
            UPDATE metricas_componentes
            SET activo = 0
            WHERE metrica_calculada_id = ?
        ");
        $stmt->execute([$metrica_calculada_id]);

        // Insertar nuevos componentes
        $orden = 0;
        foreach ($componentes as $componente_id) {
            $stmt = $this->db->prepare("
                INSERT INTO metricas_componentes
                (metrica_calculada_id, metrica_componente_id, operacion, orden, activo)
                VALUES (?, ?, ?, ?, 1)
            ");
            $stmt->execute([$metrica_calculada_id, $componente_id, $operacion, $orden++]);
        }

        return true;
    }

    /**
     * Calcular valor de métrica calculada para un período
     */
    public function calcularValor($metrica_calculada_id, $periodo_id, $usuario_id = null)
    {
        // Obtener componentes
        $componentes = $this->getComponentes($metrica_calculada_id);

        if (empty($componentes)) {
            return null; // No hay componentes para calcular
        }

        // Obtener operación (asumimos que todas tienen la misma)
        $operacion = $componentes[0]['operacion'];

        // Obtener valores de los componentes
        $valores = [];
        foreach ($componentes as $componente) {
            $valor = $this->valorModel->findByMetricaYPeriodo(
                $componente['metrica_componente_id'],
                $periodo_id
            );

            if ($valor) {
                $valor_numerico = $valor['valor_numero'] ?? $valor['valor_decimal'];
                if ($valor_numerico !== null) {
                    $valores[] = (float)$valor_numerico;
                }
            }
        }

        // Si no hay valores, no calculamos
        if (empty($valores)) {
            return null;
        }

        // Calcular según operación
        $resultado = null;
        switch ($operacion) {
            case 'suma':
                $resultado = array_sum($valores);
                break;

            case 'resta':
                $resultado = $valores[0];
                for ($i = 1; $i < count($valores); $i++) {
                    $resultado -= $valores[$i];
                }
                break;

            case 'promedio':
                $resultado = array_sum($valores) / count($valores);
                break;
        }

        // Guardar el resultado
        if ($resultado !== null) {
            // Obtener tipo de valor de la métrica calculada
            $metrica = $this->metricaModel->find($metrica_calculada_id);

            $this->valorModel->guardarValor(
                $metrica_calculada_id,
                $periodo_id,
                $resultado,
                'Calculado automáticamente',
                $usuario_id
            );
        }

        return $resultado;
    }

    /**
     * Recalcular todas las métricas calculadas que dependen de una métrica
     */
    public function recalcularDependientes($metrica_id, $periodo_id, $usuario_id = null)
    {
        // Buscar métricas calculadas que usan esta métrica como componente
        $stmt = $this->db->prepare("
            SELECT DISTINCT metrica_calculada_id
            FROM metricas_componentes
            WHERE metrica_componente_id = ? AND activo = 1
        ");
        $stmt->execute([$metrica_id]);
        $calculadas = $stmt->fetchAll();

        $recalculadas = 0;
        foreach ($calculadas as $calc) {
            $valor = $this->calcularValor($calc['metrica_calculada_id'], $periodo_id, $usuario_id);
            if ($valor !== null) {
                $recalculadas++;
            }
        }

        return $recalculadas;
    }

    /**
     * Obtener métricas disponibles como componentes para un área
     *
     * - Áreas NORMALES: retorna solo métricas de esa área
     * - Áreas GLOBALES: retorna métricas de TODAS las áreas (excepto otras áreas globales)
     *   Esto permite crear métricas calculadas globales que sumen de cualquier departamento/área
     *
     * @param int $area_id ID del área
     * @param int|null $excluir_metrica_id ID de métrica a excluir (para evitar recursión)
     * @return array Lista de métricas disponibles
     */
    public function getMetricasDisponibles($area_id, $excluir_metrica_id = null)
    {
        // Verificar si es área global usando el nuevo método basado en tipo de departamento
        $es_area_global = $this->esAreaGlobal($area_id);

        if ($es_area_global) {
            // Área GLOBAL: mostrar métricas de TODAS las áreas
            // EXCEPTO métricas de otras áreas globales (evitar recursión)
            $sql = "
                SELECT m.id, m.nombre, m.unidad, m.tipo_valor, m.es_calculada,
                       a.nombre as area_nombre, a.color as area_color,
                       d.nombre as departamento_nombre,
                       d.tipo as departamento_tipo,
                       d.color as departamento_color
                FROM metricas m
                JOIN areas a ON m.area_id = a.id
                JOIN departamentos d ON a.departamento_id = d.id
                WHERE m.activo = 1
                  AND d.tipo != 'global'
            ";
            $params = [];
        } else {
            // Área NORMAL: solo métricas de esta área
            $sql = "
                SELECT m.id, m.nombre, m.unidad, m.tipo_valor, m.es_calculada,
                       a.nombre as area_nombre, a.color as area_color
                FROM metricas m
                JOIN areas a ON m.area_id = a.id
                WHERE m.area_id = ? AND m.activo = 1
            ";
            $params = [$area_id];
        }

        if ($excluir_metrica_id) {
            $sql .= " AND m.id != ?";
            $params[] = $excluir_metrica_id;
        }

        if ($es_area_global) {
            // Ordenar: primero agencias, luego corporativos, por orden y nombre
            $sql .= " ORDER BY
                        FIELD(d.tipo, 'agencia', 'corporativo'),
                        d.orden, a.orden, m.orden, m.nombre";
        } else {
            $sql .= " ORDER BY m.orden, m.nombre";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Verificar si una métrica tiene dependientes calculadas
     */
    public function tieneDependientes($metrica_id)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM metricas_componentes
            WHERE metrica_componente_id = ? AND activo = 1
        ");
        $stmt->execute([$metrica_id]);
        return $stmt->fetchColumn() > 0;
    }
}
