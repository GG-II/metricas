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
}
