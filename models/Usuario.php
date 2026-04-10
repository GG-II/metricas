<?php
/**
 * Modelo Usuario
 */

require_once __DIR__ . '/Model.php';

class Usuario extends Model {
    protected $table = 'usuarios';
    
    /**
     * Autenticar usuario
     */
    public function authenticate($username, $password) {
        $stmt = $this->db->prepare("
            SELECT * FROM usuarios 
            WHERE username = ? AND activo = 1
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && verifyPassword($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
    
    /**
     * Obtener usuarios activos
     */
    public function getActivos() {
        return $this->getAll(['activo' => 1], 'nombre ASC');
    }
    
    /**
     * Verificar si username existe
     */
    public function usernameExists($username, $excludeId = null) {
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE username = ?";
        $params = [$username];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'] > 0;
    }
    
    /**
     * Verificar si email existe
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'] > 0;
    }
    
    /**
     * Actualizar último acceso
     */
    public function updateLastAccess($id) {
        $stmt = $this->db->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Cambiar contraseña
     */
    public function changePassword($id, $newPassword) {
        $hash = hashPassword($newPassword);
        return $this->update($id, ['password' => $hash]);
    }
}