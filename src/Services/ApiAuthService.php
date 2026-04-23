<?php
namespace App\Services;

use PDO;

/**
 * Servicio de Autenticación para API
 * Gestiona tokens de API para usuarios
 */
class ApiAuthService {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Generar token de API para un usuario
     */
    public function generateToken($user_id, $nombre = 'API Token', $expires_days = 365) {
        $token = bin2hex(random_bytes(32)); // 64 caracteres
        $expires_at = date('Y-m-d H:i:s', strtotime("+{$expires_days} days"));

        $stmt = $this->db->prepare("
            INSERT INTO api_tokens (usuario_id, token, nombre, expires_at)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([$user_id, hash('sha256', $token), $nombre, $expires_at]);

        // Retornar token plano (solo se muestra una vez)
        return [
            'token' => $token,
            'expires_at' => $expires_at,
            'nombre' => $nombre
        ];
    }

    /**
     * Validar token y retornar usuario
     */
    public function validateToken($token) {
        if (empty($token)) {
            return null;
        }

        $hashed_token = hash('sha256', $token);

        $stmt = $this->db->prepare("
            SELECT
                t.id as token_id,
                t.usuario_id,
                t.nombre as token_nombre,
                t.ultimo_uso,
                u.*,
                d.nombre as departamento_nombre,
                a.nombre as area_nombre
            FROM api_tokens t
            JOIN usuarios u ON t.usuario_id = u.id
            LEFT JOIN departamentos d ON u.departamento_id = d.id
            LEFT JOIN areas a ON u.area_id = a.id
            WHERE t.token = ?
            AND t.activo = 1
            AND (t.expires_at IS NULL OR t.expires_at > NOW())
            AND u.activo = 1
        ");

        $stmt->execute([$hashed_token]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Actualizar último uso
            $this->updateLastUsed($result['token_id']);
        }

        return $result;
    }

    /**
     * Actualizar último uso del token
     */
    private function updateLastUsed($token_id) {
        $stmt = $this->db->prepare("
            UPDATE api_tokens
            SET ultimo_uso = NOW(), total_usos = total_usos + 1
            WHERE id = ?
        ");
        $stmt->execute([$token_id]);
    }

    /**
     * Revocar token
     */
    public function revokeToken($token_id) {
        $stmt = $this->db->prepare("
            UPDATE api_tokens SET activo = 0 WHERE id = ?
        ");
        return $stmt->execute([$token_id]);
    }

    /**
     * Listar tokens de un usuario
     */
    public function getUserTokens($user_id) {
        $stmt = $this->db->prepare("
            SELECT
                id,
                nombre,
                LEFT(token, 8) as token_preview,
                created_at,
                expires_at,
                ultimo_uso,
                total_usos,
                activo
            FROM api_tokens
            WHERE usuario_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Extraer token del header Authorization
     */
    public static function extractTokenFromHeader() {
        $headers = getallheaders();

        // Buscar header Authorization
        if (isset($headers['Authorization'])) {
            $auth = $headers['Authorization'];

            // Formato: "Bearer {token}"
            if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
                return $matches[1];
            }

            // Formato directo: "{token}"
            return $auth;
        }

        // Buscar en query string (menos seguro, solo desarrollo)
        if (isset($_GET['api_token'])) {
            return $_GET['api_token'];
        }

        return null;
    }
}
