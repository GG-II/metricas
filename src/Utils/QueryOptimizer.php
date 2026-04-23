<?php
namespace App\Utils;

use PDO;

/**
 * Optimizador de Queries
 * Helpers para mejorar performance de consultas
 */
class QueryOptimizer {

    /**
     * Batch load: Cargar múltiples métricas de una vez
     * En lugar de N queries, hace 1 query
     */
    public static function batchLoadValores($db, array $metrica_ids, $periodo_id) {
        if (empty($metrica_ids)) {
            return [];
        }

        $placeholders = str_repeat('?,', count($metrica_ids) - 1) . '?';

        $sql = "
            SELECT
                vm.metrica_id,
                vm.valor_numero,
                vm.valor_decimal,
                m.tipo_valor,
                m.nombre as metrica_nombre,
                m.unidad
            FROM valores_metricas vm
            JOIN metricas m ON vm.metrica_id = m.id
            WHERE vm.metrica_id IN ($placeholders)
            AND vm.periodo_id = ?
        ";

        $params = array_merge($metrica_ids, [$periodo_id]);
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[$row['metrica_id']] = $row;
        }

        return $results;
    }

    /**
     * Batch load históricos: Cargar históricos de múltiples métricas
     */
    public static function batchLoadHistoricos($db, array $metrica_ids, $periodos = 12) {
        if (empty($metrica_ids)) {
            return [];
        }

        $placeholders = str_repeat('?,', count($metrica_ids) - 1) . '?';

        $sql = "
            SELECT
                vm.metrica_id,
                vm.valor_numero,
                vm.valor_decimal,
                m.tipo_valor,
                p.ejercicio,
                p.periodo,
                p.nombre as periodo_nombre,
                p.id as periodo_id
            FROM valores_metricas vm
            JOIN metricas m ON vm.metrica_id = m.id
            JOIN periodos p ON vm.periodo_id = p.id
            WHERE vm.metrica_id IN ($placeholders)
            AND p.activo = 1
            ORDER BY p.ejercicio DESC, p.periodo DESC
            LIMIT ?
        ";

        $limit = count($metrica_ids) * $periodos;
        $params = array_merge($metrica_ids, [$limit]);
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $metrica_id = $row['metrica_id'];
            if (!isset($results[$metrica_id])) {
                $results[$metrica_id] = [];
            }
            $results[$metrica_id][] = $row;
        }

        return $results;
    }

    /**
     * Precargar relaciones: JOIN para evitar N+1 queries
     */
    public static function loadMetricasWithRelations($db, $area_id = null) {
        $sql = "
            SELECT
                m.*,
                a.nombre as area_nombre,
                a.color as area_color,
                d.nombre as departamento_nombre
            FROM metricas m
            JOIN areas a ON m.area_id = a.id
            JOIN departamentos d ON a.departamento_id = d.id
            WHERE m.activo = 1
        ";

        if ($area_id) {
            $sql .= " AND m.area_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$area_id]);
        } else {
            $stmt = $db->query($sql);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cache key generator: Generar key consistente para caché
     */
    public static function generateCacheKey($prefix, ...$params) {
        $key = $prefix . ':' . implode(':', array_map(function($param) {
            if (is_array($param)) {
                return md5(json_encode($param));
            }
            return (string)$param;
        }, $params));

        return $key;
    }

    /**
     * Explain query: Analizar plan de ejecución (debugging)
     */
    public static function explainQuery($db, $sql, $params = []) {
        $stmt = $db->prepare("EXPLAIN " . $sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count con caché: Contar registros con caché automático
     */
    public static function cachedCount($db, $table, $where = '', $params = [], $ttl = 600) {
        $cache_key = QueryOptimizer::generateCacheKey('count', $table, $where, $params);

        return Cache::remember($cache_key, $ttl, function() use ($db, $table, $where, $params) {
            $sql = "SELECT COUNT(*) as total FROM {$table}";
            if ($where) {
                $sql .= " WHERE {$where}";
            }

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return (int)$result['total'];
        });
    }
}
