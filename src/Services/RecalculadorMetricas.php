<?php

namespace App\Services;

use PDO;

class RecalculadorMetricas
{
    private $db;

    public function __construct()
    {
        // Cargar config.php si las constantes no están definidas
        if (!defined('DB_HOST')) {
            require_once __DIR__ . '/../../config.php';
        }

        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];
        $this->db = new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    /**
     * Recalcula todas las métricas calculadas que dependen de una métrica específica
     *
     * @param int $metricaId ID de la métrica que fue actualizada
     * @param int $periodoId ID del período que fue actualizado
     * @return int Número de métricas recalculadas
     */
    public function recalcularDependientes($metricaId, $periodoId)
    {
        // Buscar métricas calculadas que usan esta métrica como componente
        $stmt = $this->db->prepare("
            SELECT DISTINCT mc.metrica_calculada_id
            FROM metricas_componentes mc
            WHERE mc.metrica_componente_id = ?
            AND mc.activo = 1
        ");
        $stmt->execute([$metricaId]);
        $metricasCalculadas = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $recalculadas = 0;

        foreach ($metricasCalculadas as $metricaCalculadaId) {
            if ($this->recalcularMetrica($metricaCalculadaId, $periodoId)) {
                $recalculadas++;
            }
        }

        return $recalculadas;
    }

    /**
     * Recalcula una métrica calculada específica para un período
     *
     * @param int $metricaCalculadaId
     * @param int $periodoId
     * @return bool true si se recalculó correctamente
     */
    public function recalcularMetrica($metricaCalculadaId, $periodoId)
    {
        // Obtener información de la métrica calculada
        $stmt = $this->db->prepare("
            SELECT id, tipo_valor
            FROM metricas
            WHERE id = ? AND es_calculada = 1
        ");
        $stmt->execute([$metricaCalculadaId]);
        $metrica = $stmt->fetch();

        if (!$metrica) {
            return false;
        }

        // Obtener componentes
        $stmt = $this->db->prepare("
            SELECT mc.metrica_componente_id, mc.operacion
            FROM metricas_componentes mc
            WHERE mc.metrica_calculada_id = ? AND mc.activo = 1
            ORDER BY mc.orden
        ");
        $stmt->execute([$metricaCalculadaId]);
        $componentes = $stmt->fetchAll();

        if (empty($componentes)) {
            return false;
        }

        $operacion = $componentes[0]['operacion'];
        $valoresComponentes = [];

        // Obtener valores de los componentes para el período
        foreach ($componentes as $comp) {
            $stmt = $this->db->prepare("
                SELECT COALESCE(valor_numero, valor_decimal) as valor
                FROM valores_metricas
                WHERE metrica_id = ? AND periodo_id = ?
                LIMIT 1
            ");
            $stmt->execute([$comp['metrica_componente_id'], $periodoId]);
            $valorComp = $stmt->fetch();

            if ($valorComp && $valorComp['valor'] !== null) {
                $valoresComponentes[] = floatval($valorComp['valor']);
            }
        }

        // Si no hay valores, no calculamos
        if (empty($valoresComponentes)) {
            return false;
        }

        // Calcular según la operación
        $valorCalculado = null;

        switch ($operacion) {
            case 'suma':
                $valorCalculado = array_sum($valoresComponentes);
                break;

            case 'promedio':
                $valorCalculado = array_sum($valoresComponentes) / count($valoresComponentes);
                break;

            case 'resta':
                $valorCalculado = $valoresComponentes[0];
                for ($i = 1; $i < count($valoresComponentes); $i++) {
                    $valorCalculado -= $valoresComponentes[$i];
                }
                break;

            default:
                return false;
        }

        // Guardar el valor calculado
        $valorNumero = null;
        $valorDecimal = null;

        if ($metrica['tipo_valor'] === 'decimal' || $metrica['tipo_valor'] === 'porcentaje') {
            $valorDecimal = round($valorCalculado, 2);
        } else {
            $valorNumero = round($valorCalculado);
        }

        $stmt = $this->db->prepare("
            INSERT INTO valores_metricas (metrica_id, periodo_id, valor_numero, valor_decimal, created_at, updated_at)
            VALUES (?, ?, ?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                valor_numero = VALUES(valor_numero),
                valor_decimal = VALUES(valor_decimal),
                updated_at = NOW()
        ");

        $stmt->execute([
            $metricaCalculadaId,
            $periodoId,
            $valorNumero,
            $valorDecimal
        ]);

        return true;
    }

    /**
     * Recalcula todas las métricas calculadas para todos los períodos
     *
     * @return array Estadísticas de recálculo
     */
    public function recalcularTodas()
    {
        $stats = [
            'metricas_procesadas' => 0,
            'valores_calculados' => 0,
            'errores' => 0
        ];

        // Obtener todas las métricas calculadas
        $metricasCalculadas = $this->db->query("
            SELECT id FROM metricas WHERE es_calculada = 1
        ")->fetchAll(PDO::FETCH_COLUMN);

        // Obtener todos los períodos
        $periodos = $this->db->query("
            SELECT id FROM periodos WHERE activo = 1
        ")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($metricasCalculadas as $metricaId) {
            $stats['metricas_procesadas']++;

            foreach ($periodos as $periodoId) {
                if ($this->recalcularMetrica($metricaId, $periodoId)) {
                    $stats['valores_calculados']++;
                } else {
                    $stats['errores']++;
                }
            }
        }

        return $stats;
    }
}
