<?php
/**
 * Definición de permisos por rol
 */

return [
    'roles' => [
        'super_admin' => [
            'name' => 'Super Administrador',
            'description' => 'Acceso total al sistema',
            'scope' => 'global'
        ],
        'dept_admin' => [
            'name' => 'Administrador de Departamento',
            'description' => 'Administra su departamento completo',
            'scope' => 'departamento'
        ],
        'dept_viewer' => [
            'name' => 'Visualizador de Área',
            'description' => 'Solo visualiza su área asignada',
            'scope' => 'area'
        ]
    ],

    'permissions' => [
        'departamentos' => [
            'view' => ['super_admin', 'dept_admin', 'dept_viewer'],
            'create' => ['super_admin'],
            'edit' => ['super_admin'],
            'delete' => ['super_admin'],
        ],
        'areas' => [
            'view' => ['super_admin', 'dept_admin', 'dept_viewer'],
            'create' => ['super_admin', 'dept_admin'],
            'edit' => ['super_admin', 'dept_admin'],
            'delete' => ['super_admin', 'dept_admin'],
        ],
        'metricas' => [
            'view' => ['super_admin', 'dept_admin', 'dept_viewer'],
            'create' => ['super_admin', 'dept_admin'],
            'edit' => ['super_admin', 'dept_admin'],
            'delete' => ['super_admin', 'dept_admin'],
        ],
        'valores_metricas' => [
            'view' => ['super_admin', 'dept_admin', 'dept_viewer'],
            'create' => ['super_admin', 'dept_admin'],
            'edit' => ['super_admin', 'dept_admin'],
            'delete' => ['super_admin', 'dept_admin'],
        ],
        'graficos' => [
            'view' => ['super_admin', 'dept_admin', 'dept_viewer'],
            'configure' => ['super_admin', 'dept_admin'],
        ],
        'usuarios' => [
            'view' => ['super_admin', 'dept_admin'],
            'create' => ['super_admin'],
            'edit' => ['super_admin'],
            'delete' => ['super_admin'],
        ],
        'metas' => [
            'view' => ['super_admin', 'dept_admin', 'dept_viewer'],
            'configure' => ['super_admin', 'dept_admin'],
        ],
    ],
];
