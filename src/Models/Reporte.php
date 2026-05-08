<?php

namespace App\Models;

use PDO;

class Reporte extends Model
{
    protected $table = 'reportes';

    /**
     * Obtener todos los reportes con información de departamento y usuario
     */
    public function getAllWithDetails()
    {
        $stmt = $this->db->query("
            SELECT
                r.*,
                d.nombre as departamento_nombre,
                d.color as departamento_color,
                d.icono as departamento_icono,
                d.tipo as departamento_tipo,
                u.nombre as autor_nombre,
                um.nombre as modificador_nombre,
                up.nombre as publicador_nombre
            FROM {$this->table} r
            INNER JOIN departamentos d ON r.departamento_id = d.id
            LEFT JOIN usuarios u ON r.usuario_creacion_id = u.id
            LEFT JOIN usuarios um ON r.usuario_modificacion_id = um.id
            LEFT JOIN usuarios up ON r.usuario_publicacion_id = up.id
            ORDER BY r.anio DESC, r.mes DESC, r.created_at DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener reportes por departamento
     */
    public function getByDepartamento($departamento_id, $estado = null)
    {
        $sql = "
            SELECT
                r.*,
                d.nombre as departamento_nombre,
                d.color as departamento_color,
                d.icono as departamento_icono,
                u.nombre as autor_nombre
            FROM {$this->table} r
            INNER JOIN departamentos d ON r.departamento_id = d.id
            LEFT JOIN usuarios u ON r.usuario_creacion_id = u.id
            WHERE r.departamento_id = ?
        ";

        if ($estado) {
            $sql .= " AND r.estado = ?";
            $stmt = $this->db->prepare($sql . " ORDER BY r.anio DESC, r.mes DESC");
            $stmt->execute([$departamento_id, $estado]);
        } else {
            $stmt = $this->db->prepare($sql . " ORDER BY r.anio DESC, r.mes DESC");
            $stmt->execute([$departamento_id]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener mes en formato texto
     */
    public function getMesNombre($mes)
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        return $meses[$mes] ?? '';
    }

    /**
     * Obtener reporte con todos los detalles (departamento, áreas, gráficos auto-generados)
     */
    public function findWithFullDetails($id)
    {
        $stmt = $this->db->prepare("
            SELECT
                r.*,
                d.nombre as departamento_nombre,
                d.color as departamento_color,
                d.icono as departamento_icono,
                d.tipo as departamento_tipo,
                d.descripcion as departamento_descripcion,
                u.nombre as autor_nombre,
                u.email as autor_email,
                um.nombre as modificador_nombre,
                up.nombre as publicador_nombre
            FROM {$this->table} r
            INNER JOIN departamentos d ON r.departamento_id = d.id
            LEFT JOIN usuarios u ON r.usuario_creacion_id = u.id
            LEFT JOIN usuarios um ON r.usuario_modificacion_id = um.id
            LEFT JOIN usuarios up ON r.usuario_publicacion_id = up.id
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        $reporte = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($reporte) {
            // Obtener todas las áreas del departamento
            $reporte['areas'] = $this->getAreasConGraficos($reporte['departamento_id']);
            $reporte['mes_nombre'] = $this->getMesNombre($reporte['mes']);
        }

        return $reporte;
    }

    /**
     * Obtener todas las áreas de un departamento con sus gráficos configurados
     */
    public function getAreasConGraficos($departamento_id)
    {
        // Obtener áreas del departamento
        $stmtAreas = $this->db->prepare("
            SELECT id, nombre, descripcion, color, icono
            FROM areas
            WHERE departamento_id = ? AND activo = 1
            ORDER BY orden ASC, nombre ASC
        ");
        $stmtAreas->execute([$departamento_id]);
        $areas = $stmtAreas->fetchAll(PDO::FETCH_ASSOC);

        // Para cada área, obtener sus gráficos configurados
        foreach ($areas as &$area) {
            $stmtGraficos = $this->db->prepare("
                SELECT id, titulo, tipo, configuracion, grid_w, grid_h, orden
                FROM configuracion_graficos
                WHERE area_id = ? AND activo = 1
                ORDER BY orden ASC
            ");
            $stmtGraficos->execute([$area['id']]);
            $area['graficos'] = $stmtGraficos->fetchAll(PDO::FETCH_ASSOC);
        }

        return $areas;
    }


    /**
     * Crear nuevo reporte consolidado por departamento
     */
    public function create($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (
                departamento_id,
                mes,
                anio,
                tipo_reporte,
                titulo,
                descripcion,
                resumen_ejecutivo,
                estado,
                usuario_creacion_id,
                version
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['departamento_id'],
            $data['mes'],
            $data['anio'],
            $data['tipo_reporte'] ?? 'mensual',
            $data['titulo'],
            $data['descripcion'] ?? '',
            $data['resumen_ejecutivo'] ?? '',
            $data['estado'] ?? 'borrador',
            $data['usuario_creacion_id'],
            1 // versión inicial
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Actualizar reporte
     */
    public function update($id, $data)
    {
        $fields = [];
        $values = [];

        if (isset($data['titulo'])) {
            $fields[] = 'titulo = ?';
            $values[] = $data['titulo'];
        }
        if (isset($data['descripcion'])) {
            $fields[] = 'descripcion = ?';
            $values[] = $data['descripcion'];
        }
        if (isset($data['resumen_ejecutivo'])) {
            $fields[] = 'resumen_ejecutivo = ?';
            $values[] = $data['resumen_ejecutivo'];
        }
        if (isset($data['estado'])) {
            $fields[] = 'estado = ?';
            $values[] = $data['estado'];
        }
        if (isset($data['usuario_modificacion_id'])) {
            $fields[] = 'usuario_modificacion_id = ?';
            $values[] = $data['usuario_modificacion_id'];
        }

        $values[] = $id;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute($values);
    }

    /**
     * Publicar reporte
     */
    public function publicar($id, $usuario_id)
    {
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET estado = 'publicado',
                fecha_publicacion = NOW(),
                usuario_publicacion_id = ?
            WHERE id = ?
        ");

        return $stmt->execute([$usuario_id, $id]);
    }

    /**
     * Cambiar estado del reporte
     */
    public function cambiarEstado($id, $estado, $usuario_id)
    {
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET estado = ?,
                usuario_modificacion_id = ?
            WHERE id = ?
        ");

        return $stmt->execute([$estado, $usuario_id, $id]);
    }

    /**
     * Buscar reportes (para búsqueda full-text)
     */
    public function search($query, $departamento_id = null)
    {
        $sql = "
            SELECT
                r.*,
                d.nombre as departamento_nombre
            FROM {$this->table} r
            INNER JOIN departamentos d ON r.departamento_id = d.id
            WHERE (
                r.titulo LIKE ? OR
                r.descripcion LIKE ? OR
                r.resumen_ejecutivo LIKE ?
            )
        ";

        $searchTerm = '%' . $query . '%';
        $params = [$searchTerm, $searchTerm, $searchTerm];

        if ($departamento_id) {
            $sql .= " AND r.departamento_id = ?";
            $params[] = $departamento_id;
        }

        $sql .= " ORDER BY r.anio DESC, r.mes DESC, r.created_at DESC LIMIT 50";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener estadísticas de reportes por departamento
     */
    public function getEstadisticasPorDepartamento($departamento_id)
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN estado = 'publicado' THEN 1 ELSE 0 END) as publicados,
                SUM(CASE WHEN estado = 'borrador' THEN 1 ELSE 0 END) as borradores,
                SUM(CASE WHEN estado = 'revision' THEN 1 ELSE 0 END) as en_revision
            FROM {$this->table}
            WHERE departamento_id = ?
        ");
        $stmt->execute([$departamento_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Verificar si existe reporte para mes/año/departamento
     */
    public function existeReporte($departamento_id, $mes, $anio)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM {$this->table}
            WHERE departamento_id = ?
            AND mes = ?
            AND anio = ?
        ");
        $stmt->execute([$departamento_id, $mes, $anio]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['count'] > 0;
    }

    /**
     * Eliminar (soft delete - cambiar a archivado)
     */
    public function archivar($id)
    {
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET estado = 'archivado'
            WHERE id = ?
        ");

        return $stmt->execute([$id]);
    }

    /**
     * Eliminar permanentemente
     */
    public function delete($id)
    {
        // Primero eliminar gráficos asociados (CASCADE lo hace automáticamente)
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
