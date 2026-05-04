<?php
namespace App\Models;

class Usuario extends Model {
    protected $table = 'usuarios';

    /**
     * Obtener usuario por username
     */
    public function findByUsername($username) {
        return $this->findBy('username', $username);
    }

    /**
     * Obtener usuario con información completa (departamento y área)
     */
    public function findWithRelations($id) {
        $stmt = $this->db->prepare("
            SELECT u.*,
                   d.nombre as departamento_nombre,
                   d.color as departamento_color,
                   a.nombre as area_nombre,
                   a.slug as area_slug
            FROM {$this->table} u
            LEFT JOIN departamentos d ON u.departamento_id = d.id
            LEFT JOIN areas a ON u.area_id = a.id
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Autenticar usuario
     */
    public function authenticate($username, $password) {
        $stmt = $this->db->prepare("
            SELECT u.*,
                   d.nombre as departamento_nombre,
                   d.color as departamento_color,
                   a.nombre as area_nombre,
                   a.slug as area_slug
            FROM {$this->table} u
            LEFT JOIN departamentos d ON u.departamento_id = d.id
            LEFT JOIN areas a ON u.area_id = a.id
            WHERE u.username = ? AND u.activo = 1
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Actualizar último acceso
            $this->update($user['id'], ['ultimo_acceso' => date('Y-m-d H:i:s')]);
            return $user;
        }

        return false;
    }

    /**
     * Obtener todos los usuarios con información de departamento y área
     */
    public function getAllWithRelations() {
        $stmt = $this->db->query("
            SELECT u.*,
                   d.nombre as departamento_nombre,
                   a.nombre as area_nombre
            FROM {$this->table} u
            LEFT JOIN departamentos d ON u.departamento_id = d.id
            LEFT JOIN areas a ON u.area_id = a.id
            ORDER BY u.nombre
        ");
        return $stmt->fetchAll();
    }

    /**
     * Verificar si el username ya existe
     */
    public function usernameExists($username, $exclude_id = null) {
        if ($exclude_id) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE username = ? AND id != ?");
            $stmt->execute([$username, $exclude_id]);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE username = ?");
            $stmt->execute([$username]);
        }
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Validar que area_admin tenga area_id asignado
     */
    public function validateAreaAdmin($data) {
        if ($data['rol'] === 'area_admin' && empty($data['area_id'])) {
            return [
                'valid' => false,
                'error' => 'El rol area_admin requiere un área asignada'
            ];
        }
        return ['valid' => true];
    }

    /**
     * Verificar si un usuario tiene acceso a un área específica
     *
     * @param array $user Usuario (debe incluir rol, departamento_id, area_id)
     * @param int $area_id ID del área a verificar
     * @return bool
     */
    public function canAccessArea($user, $area_id) {
        // super_admin tiene acceso a todo
        if ($user['rol'] === 'super_admin') {
            return true;
        }

        // area_admin solo puede acceder a su área asignada
        if ($user['rol'] === 'area_admin') {
            return (int)$user['area_id'] === (int)$area_id;
        }

        // dept_admin y dept_viewer pueden acceder a áreas de su departamento
        if (in_array($user['rol'], ['dept_admin', 'dept_viewer'])) {
            // Verificar que el área pertenezca a su departamento
            $stmt = $this->db->prepare("
                SELECT COUNT(*)
                FROM areas
                WHERE id = ? AND departamento_id = ?
            ");
            $stmt->execute([$area_id, $user['departamento_id']]);
            return $stmt->fetchColumn() > 0;
        }

        return false;
    }

    /**
     * Verificar si un usuario puede editar (crear/modificar) en un área
     *
     * @param array $user Usuario
     * @param int $area_id ID del área
     * @return bool
     */
    public function canEditArea($user, $area_id) {
        // dept_viewer no puede editar nada
        if ($user['rol'] === 'dept_viewer') {
            return false;
        }

        // Para los demás roles, verificar acceso
        return $this->canAccessArea($user, $area_id);
    }

    /**
     * Obtener áreas accesibles para un usuario
     *
     * @param array $user Usuario
     * @return array
     */
    public function getAccessibleAreas($user) {
        // super_admin ve todas las áreas
        if ($user['rol'] === 'super_admin') {
            $stmt = $this->db->query("
                SELECT a.*, d.nombre as departamento_nombre, d.tipo as departamento_tipo
                FROM areas a
                JOIN departamentos d ON a.departamento_id = d.id
                WHERE a.activo = 1
                ORDER BY d.orden, a.orden
            ");
            return $stmt->fetchAll();
        }

        // area_admin solo ve su área asignada
        if ($user['rol'] === 'area_admin' && !empty($user['area_id'])) {
            $stmt = $this->db->prepare("
                SELECT a.*, d.nombre as departamento_nombre, d.tipo as departamento_tipo
                FROM areas a
                JOIN departamentos d ON a.departamento_id = d.id
                WHERE a.id = ? AND a.activo = 1
            ");
            $stmt->execute([$user['area_id']]);
            return $stmt->fetchAll();
        }

        // dept_admin y dept_viewer ven áreas de su departamento
        if (!empty($user['departamento_id'])) {
            $stmt = $this->db->prepare("
                SELECT a.*, d.nombre as departamento_nombre, d.tipo as departamento_tipo
                FROM areas a
                JOIN departamentos d ON a.departamento_id = d.id
                WHERE a.departamento_id = ? AND a.activo = 1
                ORDER BY a.orden
            ");
            $stmt->execute([$user['departamento_id']]);
            return $stmt->fetchAll();
        }

        return [];
    }

    /**
     * Verificar si usuario puede acceder a áreas globales
     *
     * @param array $user Usuario
     * @return bool
     */
    public function canAccessGlobal($user) {
        return $user['rol'] === 'super_admin';
    }
}
