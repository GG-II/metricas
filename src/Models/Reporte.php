<?php

namespace App\Models;

use PDO;

class Reporte extends Model
{
    protected $table = 'reportes';

    /**
     * Obtener todos los reportes con información de área y usuario
     */
    public function getAllWithDetails()
    {
        $stmt = $this->db->query("
            SELECT
                r.*,
                a.nombre as area_nombre,
                a.color as area_color,
                a.icono as area_icono,
                d.nombre as departamento_nombre,
                d.id as departamento_id,
                p.nombre as periodo_nombre,
                u.nombre as autor_nombre,
                um.nombre as modificador_nombre,
                up.nombre as publicador_nombre
            FROM {$this->table} r
            INNER JOIN areas a ON r.area_id = a.id
            INNER JOIN departamentos d ON a.departamento_id = d.id
            LEFT JOIN periodos p ON r.periodo_id = p.id
            LEFT JOIN usuarios u ON r.usuario_creacion_id = u.id
            LEFT JOIN usuarios um ON r.usuario_modificacion_id = um.id
            LEFT JOIN usuarios up ON r.usuario_publicacion_id = up.id
            ORDER BY r.created_at DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener reportes por área
     */
    public function getByArea($area_id, $estado = null)
    {
        $sql = "
            SELECT
                r.*,
                a.nombre as area_nombre,
                p.nombre as periodo_nombre,
                u.nombre as autor_nombre
            FROM {$this->table} r
            INNER JOIN areas a ON r.area_id = a.id
            LEFT JOIN periodos p ON r.periodo_id = p.id
            LEFT JOIN usuarios u ON r.usuario_creacion_id = u.id
            WHERE r.area_id = ?
        ";

        if ($estado) {
            $sql .= " AND r.estado = ?";
            $stmt = $this->db->prepare($sql . " ORDER BY r.anio DESC, r.periodo_id DESC");
            $stmt->execute([$area_id, $estado]);
        } else {
            $stmt = $this->db->prepare($sql . " ORDER BY r.anio DESC, r.periodo_id DESC");
            $stmt->execute([$area_id]);
        }

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
                a.nombre as area_nombre,
                a.id as area_id,
                d.nombre as departamento_nombre,
                p.nombre as periodo_nombre,
                u.nombre as autor_nombre
            FROM {$this->table} r
            INNER JOIN areas a ON r.area_id = a.id
            INNER JOIN departamentos d ON a.departamento_id = d.id
            LEFT JOIN periodos p ON r.periodo_id = p.id
            LEFT JOIN usuarios u ON r.usuario_creacion_id = u.id
            WHERE d.id = ?
        ";

        if ($estado) {
            $sql .= " AND r.estado = ?";
            $stmt = $this->db->prepare($sql . " ORDER BY r.anio DESC, r.periodo_id DESC");
            $stmt->execute([$departamento_id, $estado]);
        } else {
            $stmt = $this->db->prepare($sql . " ORDER BY r.anio DESC, r.periodo_id DESC");
            $stmt->execute([$departamento_id]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener reporte con todos los detalles (área, gráficos insertados, etc)
     */
    public function findWithFullDetails($id)
    {
        $stmt = $this->db->prepare("
            SELECT
                r.*,
                a.nombre as area_nombre,
                a.color as area_color,
                a.icono as area_icono,
                a.id as area_id,
                d.nombre as departamento_nombre,
                d.id as departamento_id,
                p.nombre as periodo_nombre,
                p.ejercicio,
                p.periodo,
                u.nombre as autor_nombre,
                u.email as autor_email,
                um.nombre as modificador_nombre,
                up.nombre as publicador_nombre
            FROM {$this->table} r
            INNER JOIN areas a ON r.area_id = a.id
            INNER JOIN departamentos d ON a.departamento_id = d.id
            LEFT JOIN periodos p ON r.periodo_id = p.id
            LEFT JOIN usuarios u ON r.usuario_creacion_id = u.id
            LEFT JOIN usuarios um ON r.usuario_modificacion_id = um.id
            LEFT JOIN usuarios up ON r.usuario_publicacion_id = up.id
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        $reporte = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($reporte) {
            // Obtener gráficos insertados
            $reporte['graficos'] = $this->getGraficosInsertados($id);
        }

        return $reporte;
    }

    /**
     * Obtener gráficos insertados en un reporte
     */
    public function getGraficosInsertados($reporte_id)
    {
        $stmt = $this->db->prepare("
            SELECT
                rg.*,
                cg.titulo as grafico_titulo_original,
                cg.tipo as grafico_tipo
            FROM reportes_graficos rg
            LEFT JOIN configuracion_graficos cg ON rg.grafico_id = cg.id
            WHERE rg.reporte_id = ?
            ORDER BY rg.posicion_en_reporte ASC
        ");
        $stmt->execute([$reporte_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crear nuevo reporte
     */
    public function create($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (
                area_id,
                periodo_id,
                anio,
                tipo_reporte,
                titulo,
                descripcion,
                contenido,
                estado,
                usuario_creacion_id,
                version
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['area_id'],
            $data['periodo_id'] ?? null,
            $data['anio'],
            $data['tipo_reporte'] ?? 'mensual',
            $data['titulo'],
            $data['descripcion'] ?? '',
            $data['contenido'] ?? '',
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
        if (isset($data['contenido'])) {
            $fields[] = 'contenido = ?';
            $values[] = $data['contenido'];
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
     * Asociar gráfico al reporte (cuando se inserta)
     */
    public function insertarGrafico($reporte_id, $data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO reportes_graficos (
                reporte_id,
                grafico_id,
                imagen_path,
                imagen_thumbnail,
                imagen_width,
                imagen_height,
                periodo_captura_id,
                titulo_grafico,
                posicion_en_reporte,
                alineacion,
                ajuste_texto,
                ancho_display
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $reporte_id,
            $data['grafico_id'],
            $data['imagen_path'],
            $data['imagen_thumbnail'] ?? null,
            $data['imagen_width'] ?? null,
            $data['imagen_height'] ?? null,
            $data['periodo_captura_id'] ?? null,
            $data['titulo_grafico'],
            $data['posicion_en_reporte'] ?? 1,
            $data['alineacion'] ?? 'center',
            $data['ajuste_texto'] ?? 'inline',
            $data['ancho_display'] ?? null
        ]);
    }

    /**
     * Buscar reportes (para búsqueda full-text)
     */
    public function search($query, $area_id = null)
    {
        $sql = "
            SELECT
                r.*,
                a.nombre as area_nombre,
                p.nombre as periodo_nombre
            FROM {$this->table} r
            INNER JOIN areas a ON r.area_id = a.id
            LEFT JOIN periodos p ON r.periodo_id = p.id
            WHERE (
                r.titulo LIKE ? OR
                r.descripcion LIKE ? OR
                r.contenido LIKE ?
            )
        ";

        $searchTerm = '%' . $query . '%';
        $params = [$searchTerm, $searchTerm, $searchTerm];

        if ($area_id) {
            $sql .= " AND r.area_id = ?";
            $params[] = $area_id;
        }

        $sql .= " ORDER BY r.created_at DESC LIMIT 50";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener estadísticas de reportes por área
     */
    public function getEstadisticasPorArea($area_id)
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN estado = 'publicado' THEN 1 ELSE 0 END) as publicados,
                SUM(CASE WHEN estado = 'borrador' THEN 1 ELSE 0 END) as borradores,
                SUM(CASE WHEN estado = 'revision' THEN 1 ELSE 0 END) as en_revision
            FROM {$this->table}
            WHERE area_id = ?
        ");
        $stmt->execute([$area_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Verificar si existe reporte para período/área/tipo
     */
    public function existeReporte($area_id, $periodo_id, $anio, $tipo_reporte)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM {$this->table}
            WHERE area_id = ?
            AND periodo_id = ?
            AND anio = ?
            AND tipo_reporte = ?
        ");
        $stmt->execute([$area_id, $periodo_id, $anio, $tipo_reporte]);
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
