<?php
namespace App\Middleware;

use App\Services\PermissionService;

/**
 * Middleware de permisos
 * Verifica que el usuario tenga los permisos necesarios
 */
class PermissionMiddleware {

    /**
     * Requiere rol de super admin
     */
    public static function requireSuperAdmin() {
        $user = getCurrentUser();

        if (!$user || $user['rol'] !== 'super_admin') {
            http_response_code(403);
            die('Acceso denegado: Se requiere rol Super Admin');
        }
    }

    /**
     * Requiere rol de admin (super_admin o dept_admin)
     */
    public static function requireAdmin() {
        $user = getCurrentUser();

        if (!$user || !in_array($user['rol'], ['super_admin', 'dept_admin'])) {
            http_response_code(403);
            die('Acceso denegado: Se requiere rol de Administrador');
        }
    }

    /**
     * Verificar acceso a un departamento
     */
    public static function canAccessDepartamento($departamento_id) {
        $user = getCurrentUser();

        if (!PermissionService::canViewDepartamento($user, $departamento_id)) {
            http_response_code(403);
            die('Acceso denegado: No tienes permiso para ver este departamento');
        }
    }

    /**
     * Verificar acceso a un área
     */
    public static function canAccessArea($area_id) {
        $user = getCurrentUser();

        if (!PermissionService::canViewArea($user, $area_id)) {
            http_response_code(403);
            die('Acceso denegado: No tienes permiso para ver esta área');
        }
    }
}
