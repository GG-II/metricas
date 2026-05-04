<?php
namespace App\Services;

use App\Models\Departamento;
use App\Models\Area;

/**
 * Servicio de gestión de permisos
 * Implementa la lógica de permisos por roles
 */
class PermissionService {

    /**
     * Verifica si el usuario puede ver un departamento
     */
    public static function canViewDepartamento($user, $departamento_id) {
        if (!$user) return false;

        if ($user['rol'] === 'super_admin') {
            return true;
        }

        return $user['departamento_id'] == $departamento_id;
    }

    /**
     * Verifica si el usuario puede editar un departamento
     */
    public static function canEditDepartamento($user, $departamento_id) {
        if (!$user) return false;

        return $user['rol'] === 'super_admin';
    }

    /**
     * Verifica si el usuario puede ver un área
     */
    public static function canViewArea($user, $area_id) {
        if (!$user) return false;

        if ($user['rol'] === 'super_admin') {
            return true;
        }

        $areaModel = new Area();
        $area = $areaModel->find($area_id);

        if (!$area) return false;

        if ($user['rol'] === 'dept_admin') {
            return $area['departamento_id'] == $user['departamento_id'];
        }

        if ($user['rol'] === 'area_admin') {
            return $area_id == $user['area_id'];
        }

        if ($user['rol'] === 'dept_viewer') {
            return $area['departamento_id'] == $user['departamento_id'];
        }

        return false;
    }

    /**
     * Verifica si el usuario puede editar un área
     */
    public static function canEditArea($user, $area_id) {
        if (!$user) return false;

        if ($user['rol'] === 'super_admin') {
            return true;
        }

        if ($user['rol'] === 'dept_admin') {
            $areaModel = new Area();
            $area = $areaModel->find($area_id);

            return $area && $area['departamento_id'] == $user['departamento_id'];
        }

        if ($user['rol'] === 'area_admin') {
            return $area_id == $user['area_id'];
        }

        return false;
    }

    /**
     * Obtiene lista de departamentos que el usuario puede ver
     */
    public static function getDepartamentosPermitidos($user) {
        $deptModel = new Departamento();

        if ($user['rol'] === 'super_admin') {
            return $deptModel->getAll();
        }

        if ($user['departamento_id']) {
            $dept = $deptModel->find($user['departamento_id']);
            return $dept ? [$dept] : [];
        }

        return [];
    }

    /**
     * Obtiene lista de áreas que el usuario puede ver
     */
    public static function getAreasPermitidas($user, $departamento_id = null) {
        $areaModel = new Area();

        if ($user['rol'] === 'super_admin') {
            if ($departamento_id) {
                return $areaModel->getByDepartamento($departamento_id);
            }
            return $areaModel->getAll();
        }

        if ($user['rol'] === 'dept_admin') {
            if ($departamento_id && $departamento_id != $user['departamento_id']) {
                return [];
            }
            return $areaModel->getByDepartamento($user['departamento_id']);
        }

        if ($user['rol'] === 'area_admin' && $user['area_id']) {
            $area = $areaModel->find($user['area_id']);
            return $area ? [$area] : [];
        }

        if ($user['rol'] === 'dept_viewer' && $user['departamento_id']) {
            return $areaModel->getByDepartamento($user['departamento_id']);
        }

        return [];
    }

    /**
     * Verifica si el usuario puede crear recursos en un departamento
     */
    public static function canCreateInDepartamento($user, $departamento_id) {
        if (!$user) return false;

        if ($user['rol'] === 'super_admin') {
            return true;
        }

        if ($user['rol'] === 'dept_admin') {
            return $user['departamento_id'] == $departamento_id;
        }

        return false;
    }

    /**
     * Verifica si el usuario puede crear métricas o gráficos en un área
     */
    public static function canCreateInArea($user, $area_id) {
        if (!$user) return false;

        if ($user['rol'] === 'super_admin') {
            return true;
        }

        if ($user['rol'] === 'area_admin') {
            return $area_id == $user['area_id'];
        }

        if ($user['rol'] === 'dept_admin') {
            $areaModel = new Area();
            $area = $areaModel->find($area_id);
            return $area && $area['departamento_id'] == $user['departamento_id'];
        }

        return false;
    }
}
