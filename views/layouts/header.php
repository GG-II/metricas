<!DOCTYPE html>
<html lang="es" data-bs-theme="<?php echo isLoggedIn() ? (getCurrentUser()['tema'] ?? 'light') : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Sistema de Métricas'; ?></title>

    <!-- Tabler CSS -->
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?php echo baseUrl('/assets/css/custom.css'); ?>" rel="stylesheet">
</head>
<body>
    <?php if (isLoggedIn()): ?>
        <?php $user = getCurrentUser(); ?>
        <!-- Navbar -->
        <header class="navbar navbar-expand-md navbar-light sticky-top d-print-none">
            <div class="container-xl">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <h1 class="navbar-brand d-none-navbar-horizontal pe-0 pe-md-3">
                    <a href="<?php echo baseUrl('/index.php'); ?>">
                        <i class="ti ti-chart-bar me-2"></i>
                        Sistema de Métricas
                    </a>
                </h1>
                <div class="navbar-nav flex-row order-md-last">
                    <!-- Toggle Tema -->
                    <div class="nav-item me-3">
                        <a href="#" class="nav-link px-0" id="theme-toggle" title="Cambiar tema">
                            <i class="ti ti-moon icon" id="theme-icon"></i>
                        </a>
                    </div>

                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
                            <span class="avatar avatar-sm" style="background-color: <?php echo $user['avatar_color'] ?? ($user['departamento_color'] ?? '#3b82f6'); ?>">
                                <?php if (isset($user['avatar_icono']) && $user['avatar_icono']): ?>
                                    <i class="ti ti-<?php echo e($user['avatar_icono']); ?>"></i>
                                <?php else: ?>
                                    <?php echo strtoupper(substr($user['nombre'], 0, 2)); ?>
                                <?php endif; ?>
                            </span>
                            <div class="d-none d-xl-block ps-2">
                                <div><?php echo e($user['nombre']); ?></div>
                                <div class="mt-1 small text-muted">
                                    <?php
                                    $roles = [
                                        'super_admin' => 'Super Admin',
                                        'dept_admin' => 'Admin Departamento',
                                        'area_admin' => 'Admin de Área',
                                        'dept_viewer' => 'Visualizador'
                                    ];
                                    echo $roles[$user['rol']] ?? $user['rol'];
                                    ?>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <a href="<?php echo baseUrl('/perfil.php'); ?>" class="dropdown-item">
                                <i class="ti ti-user me-2"></i> Mi Perfil
                            </a>
                            <?php if (in_array($user['rol'], ['super_admin', 'dept_admin', 'area_admin'])): ?>
                            <a href="<?php echo baseUrl('/admin/index.php'); ?>" class="dropdown-item">
                                <i class="ti ti-settings me-2"></i> Administración
                            </a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a href="<?php echo baseUrl('/logout.php'); ?>" class="dropdown-item">
                                <i class="ti ti-logout me-2"></i> Cerrar Sesión
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>
    <?php endif; ?>
