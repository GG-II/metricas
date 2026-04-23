<?php
namespace App\Models;

class Grafico extends Model {
    protected $table = 'configuracion_graficos';

    /**
     * Obtener gráficos de un área (ordenados por grid)
     */
    public function getByArea($area_id, $solo_activos = true, $rol = null) {
        $sql = "SELECT g.*
                FROM {$this->table} g
                WHERE g.area_id = ?";

        if ($solo_activos) {
            $sql .= " AND g.activo = 1";
        }

        if ($rol && $rol !== 'super_admin' && $rol !== 'dept_admin') {
            $sql .= " AND g.visible_para IN ('todos', 'viewer')";
        }

        $sql .= " ORDER BY g.grid_y ASC, g.grid_x ASC, g.id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$area_id]);

        return $stmt->fetchAll();
    }

    /**
     * Obtener layout completo de un área
     */
    public function getLayoutByArea($area_id, $rol = 'dept_viewer') {
        $sql = "SELECT g.id,
                       g.titulo,
                       g.tipo,
                       g.configuracion,
                       g.grid_x,
                       g.grid_y,
                       g.grid_w,
                       g.grid_h,
                       g.activo,
                       g.visible_para
                FROM {$this->table} g
                WHERE g.area_id = ?
                  AND g.activo = 1";

        if ($rol !== 'super_admin' && $rol !== 'dept_admin') {
            $sql .= " AND g.visible_para IN ('todos', 'viewer')";
        }

        $sql .= " ORDER BY g.grid_y ASC, g.grid_x ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$area_id]);

        return $stmt->fetchAll();
    }

    /**
     * Guardar posiciones de múltiples gráficos
     */
    public function guardarPosiciones($area_id, $items) {
        $this->db->beginTransaction();

        try {
            $actualizados = 0;

            foreach ($items as $item) {
                $grafico_id = (int)$item['id'];

                $grafico = $this->find($grafico_id);
                if (!$grafico || $grafico['area_id'] != $area_id) {
                    throw new \Exception("Gráfico $grafico_id no pertenece al área $area_id");
                }

                $data = [
                    'grid_x' => (int)$item['x'],
                    'grid_y' => (int)$item['y'],
                    'grid_w' => (int)$item['w'],
                    'grid_h' => (int)$item['h']
                ];

                $this->update($grafico_id, $data);
                $actualizados++;
            }

            $this->db->commit();
            return $actualizados;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Crear gráfico con posición automática al final
     */
    public function createConPosicionAutomatica($data) {
        $sql = "SELECT MAX(grid_y + grid_h) as max_y
                FROM {$this->table}
                WHERE area_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$data['area_id']]);
        $result = $stmt->fetch();

        $max_y = $result['max_y'] ?? 0;

        $data['grid_x'] = 0;
        $data['grid_y'] = $max_y;

        if (!isset($data['grid_w'])) $data['grid_w'] = 6;
        if (!isset($data['grid_h'])) $data['grid_h'] = 4;

        return $this->create($data);
    }
}
