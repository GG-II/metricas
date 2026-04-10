<?php
/**
 * Header reutilizable
 * Variables esperadas: $page_title, $user_name, $user_role, $is_admin
 */

$user_name = $_SESSION['user_name'] ?? 'Usuario';
$user_role = $_SESSION['user_role'] ?? 'viewer';
$is_admin = isAdmin();
$user_initial = strtoupper(substr($user_name, 0, 1));
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Dashboard'; ?> - <?php echo APP_NAME; ?></title>
    
    <!-- Tabler CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    
    <!-- ApexCharts -->
    <link rel="stylesheet" href="https://unpkg.com/apexcharts@3.44.0/dist/apexcharts.css">
    
    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/custom.css">
    
    <?php if (isset($extra_css)): ?>
        <?php echo $extra_css; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Header -->
    <header class="navbar navbar-expand-md navbar-dark d-print-none">
        <div class="container-xl">
            <h1 class="navbar-brand">
                <div class="brand-logo">IT</div>
                <span><?php echo APP_NAME; ?></span>
            </h1>
            
            <div class="navbar-nav flex-row order-md-last">
                <!-- Selector de período (si está en dashboard) -->
                <?php if (isset($periodo_selector) && $periodo_selector): ?>
                    <div class="nav-item me-3">
                        <select class="form-select" id="periodo-selector" onchange="cambiarPeriodo(this.value)">
                            <?php foreach ($periodos as $p): ?>
                                <option value="<?php echo $p['ejercicio'] . '-' . $p['periodo']; ?>"
                                        <?php echo ($p['ejercicio'] == $ejercicio && $p['periodo'] == $periodo_mes) ? 'selected' : ''; ?>>
                                    <?php echo $p['nombre']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                
                <?php
// Detectar si estamos en index.php y en modo edición
$is_index_page = (basename($_SERVER['PHP_SELF']) === 'index.php');
$is_edit_mode = isset($_GET['edit']) && $_GET['edit'] === '1' && $is_admin;
$area_param = isset($_GET['area']) ? '&area=' . (int)$_GET['area'] : '';
$periodo_param = isset($_GET['periodo']) ? '&periodo=' . htmlspecialchars($_GET['periodo']) : '';
?>

<?php if ($is_admin && $is_index_page): ?>
    <!-- Controles de Dashboard (solo en index.php) -->
    <div class="nav-item me-3">
        <?php if ($is_edit_mode): ?>
            <!-- Modo Edición Activo -->
            <div class="edit-mode-controls">
                <span class="edit-mode-badge">
                    <i class="ti ti-pencil"></i>
                    Editando Dashboard
                </span>
                <a href="<?php echo BASE_URL; ?>/index.php?<?php echo ltrim($area_param . $periodo_param, '&'); ?>" 
                   class="btn btn-success btn-sm">
                    <i class="ti ti-check me-1"></i>
                    Finalizar
                </a>
            </div>
        <?php else: ?>
            <!-- Modo Vista - Botón para activar edición -->
            <a href="<?php echo BASE_URL; ?>/index.php?edit=1<?php echo $area_param . $periodo_param; ?>" 
               class="btn btn-outline-primary btn-sm">
                <i class="ti ti-edit me-1"></i>
                Editar Dashboard
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>

                <!-- Usuario -->
<div class="nav-item dropdown">
    <?php
    // Obtener foto de perfil del usuario actual
    require_once __DIR__ . '/../models/Usuario.php';
    $usuarioModel = new Usuario();
    $usuario_actual = $usuarioModel->getById($_SESSION['user_id']);
    $foto_perfil = $usuario_actual['foto_perfil'] ?? null;
    ?>
    <a href="#" class="nav-link d-flex align-items-center" data-bs-toggle="dropdown">
        <div class="user-info">
            <?php if ($foto_perfil): ?>
                <div class="user-avatar" style="background-image: url('<?php echo BASE_URL; ?>/uploads/avatars/<?php echo $foto_perfil; ?>'); background-size: cover; background-position: center;">
                </div>
            <?php else: ?>
                <div class="user-avatar">
                    <?php echo $user_initial; ?>
                </div>
            <?php endif; ?>
            <div>
                <div style="font-size: 14px; font-weight: 500;"><?php echo $user_name; ?></div>
                <div style="font-size: 12px; color: #64748b;"><?php echo ucfirst($user_role); ?></div>
            </div>
        </div>
    </a>
    <div class="dropdown-menu dropdown-menu-end">
    <a class="dropdown-item" href="<?php echo BASE_URL; ?>/perfil.php">
        <i class="ti ti-user me-2"></i>
        Mi Perfil
    </a>
    <?php if ($is_admin): ?>
        <div class="dropdown-divider"></div>
        <div class="dropdown-header">Administración</div>
        <a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/metricas.php">
            <i class="ti ti-chart-dots me-2"></i>
            Configurar Métricas
        </a>
        <a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/configurar-graficos.php?area=<?php echo $area_id ?? 1; ?>">
            <i class="ti ti-layout-dashboard me-2"></i>
            Configurar Gráficos
        </a>
        <a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/usuarios.php">
            <i class="ti ti-users me-2"></i>
            Gestión de Usuarios
        </a>
    <?php endif; ?>
    <div class="dropdown-divider"></div>
    <a class="dropdown-item" href="<?php echo BASE_URL; ?>/logout.php">
        <i class="ti ti-logout me-2"></i>
        Cerrar Sesión
    </a>
</div>
</div>
            </div>
        </div>
    </header>