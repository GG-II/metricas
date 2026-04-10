<?php
/**
 * Modelo: Grafico
 * Gestión de configuración de gráficos del dashboard
 */

require_once __DIR__ . '/Model.php';

class Grafico extends Model {
    protected $table = 'configuracion_graficos';
    
    /**
     * Obtener tipos de gráficos disponibles (usando ChartRegistry)
     */
    public static function getTiposDisponibles() {
        require_once __DIR__ . '/ChartRegistry.php';
        return ChartRegistry::getMetadata();
    }
    
    /**
     * Obtener gráficos de un área (ordenados por grid)
     */
    public function getByArea($area_id, $solo_activos = true, $rol = null) {
        $sql = "SELECT g.*, 
                       u.nombre as creador_nombre
                FROM {$this->table} g
                LEFT JOIN usuarios u ON g.creado_por = u.id
                WHERE g.area_id = :area_id";
        
        if ($solo_activos) {
            $sql .= " AND g.activo = 1";
        }
        
        if ($rol !== 'admin') {
            $sql .= " AND g.permisos_visualizacion = 'todos'";
        }
        
        $sql .= " ORDER BY g.grid_y ASC, g.grid_x ASC, g.id ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['area_id' => $area_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener layout completo de un área
     */
    public function getLayoutByArea($area_id, $rol = 'viewer') {
        $sql = "SELECT g.id,
                       g.titulo,
                       g.descripcion,
                       g.tipo,
                       g.configuracion,
                       g.grid_x,
                       g.grid_y,
                       g.grid_w,
                       g.grid_h,
                       g.activo,
                       g.permisos_visualizacion,
                       u.nombre as creador_nombre
                FROM {$this->table} g
                LEFT JOIN usuarios u ON g.creado_por = u.id
                WHERE g.area_id = :area_id
                  AND g.activo = 1";
        
        if ($rol !== 'admin') {
            $sql .= " AND g.permisos_visualizacion = 'todos'";
        }
        
        $sql .= " ORDER BY g.grid_y ASC, g.grid_x ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['area_id' => $area_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                
                $grafico = $this->getById($grafico_id);
                if (!$grafico || $grafico['area_id'] != $area_id) {
                    throw new Exception("Gráfico $grafico_id no pertenece al área $area_id");
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
            
        } catch (Exception $e) {
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
                WHERE area_id = :area_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['area_id' => $data['area_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $max_y = $result['max_y'] ?? 0;
        
        $data['grid_x'] = 0;
        $data['grid_y'] = $max_y;
        
        if (!isset($data['grid_w'])) $data['grid_w'] = 6;
        if (!isset($data['grid_h'])) $data['grid_h'] = 4;
        
        return $this->create($data);
    }
    
    /**
     * Duplicar gráfico
     */
    public function duplicar($grafico_id, $usuario_id) {
        $grafico = $this->getById($grafico_id);
        
        if (!$grafico) {
            throw new Exception("Gráfico no encontrado");
        }
        
        $nuevo_grafico = [
            'area_id' => $grafico['area_id'],
            'titulo' => $grafico['titulo'] . ' (Copia)',
            'descripcion' => $grafico['descripcion'],
            'tipo' => $grafico['tipo'],
            'configuracion' => $grafico['configuracion'],
            'grid_w' => $grafico['grid_w'],
            'grid_h' => $grafico['grid_h'],
            'activo' => 0,
            'permisos_visualizacion' => $grafico['permisos_visualizacion'],
            'creado_por' => $usuario_id
        ];
        
        return $this->createConPosicionAutomatica($nuevo_grafico);
    }
}