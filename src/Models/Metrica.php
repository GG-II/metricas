<?php
namespace App\Models;

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

        $sql .= " ORDER BY orden ASC, id ASC";

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
                m.orden,
                m.descripcion,
                m.es_calculada,
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
     * Obtener todas las métricas con información de área y departamento
     */
    public function getAllWithRelations() {
        $sql = "
            SELECT m.*,
                   a.nombre as area_nombre,
                   a.slug as area_slug,
                   d.id as departamento_id,
                   d.nombre as departamento_nombre
            FROM metricas m
            JOIN areas a ON m.area_id = a.id
            JOIN departamentos d ON a.departamento_id = d.id
            WHERE m.activo = 1
            ORDER BY d.orden, a.orden, m.orden
        ";

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Verificar si el slug ya existe en el área
     */
    public function slugExists($slug, $area_id, $exclude_id = null) {
        if ($exclude_id) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM metricas WHERE slug = ? AND area_id = ? AND id != ?");
            $stmt->execute([$slug, $area_id, $exclude_id]);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM metricas WHERE slug = ? AND area_id = ?");
            $stmt->execute([$slug, $area_id]);
        }
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Obtener el orden máximo por área
     */
    public function getMaxOrdenByArea($area_id) {
        $stmt = $this->db->prepare("
            SELECT MAX(orden) as max_orden
            FROM {$this->table}
            WHERE area_id = ?
        ");
        $stmt->execute([$area_id]);
        $result = $stmt->fetch();
        return (int)($result['max_orden'] ?? 0);
    }

    /**
     * Obtener métricas por área con estadísticas
     */
    public function getByAreaWithStats($area_id) {
        $stmt = $this->db->prepare("
            SELECT m.*,
                   a.nombre as area_nombre,
                   a.color as area_color,
                   d.id as departamento_id,
                   d.nombre as departamento_nombre,
                   COUNT(DISTINCT vm.id) as total_valores
            FROM {$this->table} m
            JOIN areas a ON m.area_id = a.id
            JOIN departamentos d ON a.departamento_id = d.id
            LEFT JOIN valores_metricas vm ON m.id = vm.metrica_id
            WHERE m.area_id = ?
            GROUP BY m.id
            ORDER BY m.activo DESC, m.orden, m.nombre
        ");
        $stmt->execute([$area_id]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener todas las métricas con estadísticas
     */
    public function getAllWithStats() {
        $stmt = $this->db->query("
            SELECT m.*,
                   a.nombre as area_nombre,
                   a.color as area_color,
                   d.id as departamento_id,
                   d.nombre as departamento_nombre,
                   COUNT(DISTINCT vm.id) as total_valores
            FROM {$this->table} m
            JOIN areas a ON m.area_id = a.id
            JOIN departamentos d ON a.departamento_id = d.id
            LEFT JOIN valores_metricas vm ON m.id = vm.metrica_id
            GROUP BY m.id
            ORDER BY m.activo DESC, d.orden, a.orden, m.orden, m.nombre
        ");
        return $stmt->fetchAll();
    }

    /**
     * Obtener métrica con información completa de relaciones
     */
    public function findWithRelations($id) {
        $stmt = $this->db->prepare("
            SELECT m.*,
                   a.nombre as area_nombre,
                   a.departamento_id,
                   d.nombre as departamento_nombre
            FROM {$this->table} m
            JOIN areas a ON m.area_id = a.id
            JOIN departamentos d ON a.departamento_id = d.id
            WHERE m.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Obtener todas las métricas de un departamento con estadísticas
     */
    public function getAllWithStatsByDepartamento($departamento_id) {
        $stmt = $this->db->prepare("
            SELECT m.*,
                   a.nombre as area_nombre,
                   a.color as area_color,
                   d.id as departamento_id,
                   d.nombre as departamento_nombre,
                   COUNT(DISTINCT vm.id) as total_valores
            FROM {$this->table} m
            JOIN areas a ON m.area_id = a.id
            JOIN departamentos d ON a.departamento_id = d.id
            LEFT JOIN valores_metricas vm ON m.id = vm.metrica_id
            WHERE d.id = ?
            GROUP BY m.id
            ORDER BY m.activo DESC, d.orden, a.orden, m.orden, m.nombre
        ");
        $stmt->execute([$departamento_id]);
        return $stmt->fetchAll();
    }
}
