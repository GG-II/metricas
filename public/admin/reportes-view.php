<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Services\PermissionService;
use App\Models\Reporte;
use App\Utils\Parsedown;

AuthMiddleware::handle();

$user = getCurrentUser();

$reporteModel = new Reporte();
$parsedown = new Parsedown();

$reporte_id = $_GET['id'] ?? null;

if (!$reporte_id) {
    die('ID de reporte no especificado');
}

$reporte = $reporteModel->findWithFullDetails($reporte_id);

if (!$reporte) {
    die('Reporte no encontrado');
}

// Verificar permisos - Permitir a todos por ahora (temporal)
// TODO: Verificar permisos correctamente después de actualizar sesión
// if ($user['rol'] !== 'super_admin' && !PermissionService::canViewArea($user, $reporte['area_id'])) {
//     die('No tienes permiso para ver este reporte');
// }

// Convertir Markdown a HTML
$contenidoHTML = $parsedown->text($reporte['contenido'] ?? '');

$pageTitle = $reporte['titulo'];
?>

<!DOCTYPE html>
<html lang="es" data-bs-theme="<?php echo $user['tema'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?> - Sistema de Métricas</title>

    <!-- Tabler CSS -->
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet">

    <!-- GitHub Markdown CSS -->
    <link href="https://cdn.jsdelivr.net/npm/github-markdown-css@5.5.0/github-markdown.min.css" rel="stylesheet">

    <!-- Highlight.js para código -->
    <link href="https://cdn.jsdelivr.net/npm/highlight.js@11.9.0/styles/github.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/highlight.js@11.9.0/highlight.min.js"></script>

    <style>
        .report-viewer {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .report-header {
            text-align: center;
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid var(--tblr-border-color);
        }

        .report-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--tblr-body-color);
        }

        .report-meta {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
            color: var(--tblr-secondary);
            font-size: 0.95rem;
        }

        .report-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .report-meta-item i {
            font-size: 1.1rem;
        }

        .report-content {
            background: var(--tblr-card-bg);
            border-radius: 8px;
            padding: 3rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        /* Asegurar fondo blanco en modo claro */
        [data-bs-theme="light"] .report-content {
            background: #ffffff !important;
        }

        /* Asegurar fondo oscuro pero no negro en dark mode */
        [data-bs-theme="dark"] .report-content {
            background: #1e293b !important;
        }

        /* Estilos Markdown mejorados */
        .markdown-body {
            font-size: 16px;
            line-height: 1.8;
            color: var(--tblr-body-color);
            background: transparent !important;
        }

        /* Forzar colores legibles en modo claro */
        [data-bs-theme="light"] .markdown-body {
            color: #24292f !important;
        }

        [data-bs-theme="light"] .markdown-body h1,
        [data-bs-theme="light"] .markdown-body h2,
        [data-bs-theme="light"] .markdown-body h3 {
            color: #24292f !important;
        }

        /* Forzar colores legibles en modo oscuro */
        [data-bs-theme="dark"] .markdown-body {
            color: #e6edf3 !important;
        }

        [data-bs-theme="dark"] .markdown-body h1,
        [data-bs-theme="dark"] .markdown-body h2,
        [data-bs-theme="dark"] .markdown-body h3 {
            color: #e6edf3 !important;
        }

        .markdown-body h1 {
            font-size: 2rem;
            margin-top: 2rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--tblr-border-color);
        }

        .markdown-body h2 {
            font-size: 1.6rem;
            margin-top: 1.5rem;
            margin-bottom: 0.8rem;
        }

        .markdown-body h3 {
            font-size: 1.3rem;
            margin-top: 1.2rem;
            margin-bottom: 0.6rem;
        }

        .markdown-body p {
            margin-bottom: 1.2rem;
        }

        .markdown-body ul, .markdown-body ol {
            margin-bottom: 1.2rem;
            padding-left: 2rem;
        }

        .markdown-body li {
            margin-bottom: 0.5rem;
        }

        .markdown-body blockquote {
            border-left: 4px solid var(--tblr-primary);
            padding-left: 1rem;
            margin: 1.5rem 0;
            color: var(--tblr-secondary);
            font-style: italic;
        }

        .markdown-body code {
            padding: 0.2rem 0.4rem;
            border-radius: 3px;
            font-size: 0.9em;
            font-family: 'Courier New', monospace;
        }

        /* Código inline - modo claro */
        [data-bs-theme="light"] .markdown-body code {
            background: #f6f8fa;
            color: #cf222e;
            border: 1px solid #d0d7de;
        }

        /* Código inline - modo oscuro */
        [data-bs-theme="dark"] .markdown-body code {
            background: #343a40;
            color: #ff7b72;
            border: 1px solid #4a5568;
        }

        .markdown-body pre {
            padding: 1rem;
            border-radius: 6px;
            overflow-x: auto;
            margin: 1.5rem 0;
            border: 1px solid;
        }

        /* Bloques de código - modo claro */
        [data-bs-theme="light"] .markdown-body pre {
            background: #f6f8fa;
            border-color: #d0d7de;
        }

        /* Bloques de código - modo oscuro */
        [data-bs-theme="dark"] .markdown-body pre {
            background: #2d333b;
            border-color: #444c56;
        }

        .markdown-body pre code {
            background: none;
            padding: 0;
            border: none;
            color: inherit;
        }

        .markdown-body img {
            max-width: 100%;
            height: auto;
            border-radius: 6px;
            margin: 1.5rem 0;
        }

        .markdown-body hr {
            border: none;
            border-top: 2px solid var(--tblr-border-color);
            margin: 2rem 0;
        }

        .markdown-body table {
            border-collapse: collapse;
            width: 100%;
            margin: 1.5rem 0;
        }

        .markdown-body table th,
        .markdown-body table td {
            border: 1px solid var(--tblr-border-color);
            padding: 0.75rem;
            text-align: left;
        }

        .markdown-body table th {
            background: var(--tblr-bg-surface-secondary);
            font-weight: 600;
        }

        /* Placeholder para gráficos */
        .grafico-placeholder {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem;
            border-radius: 8px;
            text-align: center;
            font-size: 1.2rem;
            font-weight: 600;
            margin: 2rem 0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        /* Botones de acción */
        .report-actions {
            position: fixed;
            top: 80px;
            right: 20px;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            z-index: 100;
        }

        .btn-float {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: all 0.3s;
        }

        .btn-float:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.2);
        }

        @media print {
            /* Ocultar elementos de navegación */
            .report-actions,
            .navbar,
            header,
            .btn-back,
            .mb-3,
            nav {
                display: none !important;
            }

            /* Ocultar botón "Volver a Reportes" */
            body > div.page-wrapper > div > div > div.mb-3 {
                display: none !important;
            }

            /* Ajustar página para impresión */
            body {
                margin: 0;
                padding: 0;
            }

            .page-wrapper,
            .page-body,
            .container-xl {
                margin: 0;
                padding: 0;
                max-width: 100%;
            }

            .report-viewer {
                max-width: 100%;
                padding: 20px;
                margin: 0;
            }

            .report-content {
                box-shadow: none;
                padding: 20px;
            }

            /* Asegurar que el contenido sea negro sobre blanco */
            .report-content,
            .markdown-body {
                background: white !important;
                color: black !important;
            }

            .markdown-body h1,
            .markdown-body h2,
            .markdown-body h3,
            .markdown-body h4,
            .markdown-body h5,
            .markdown-body h6 {
                color: black !important;
            }
        }

        /* Badge de estado */
        .status-badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-badge.borrador {
            background: #e3f2fd;
            color: #1976d2;
        }

        .status-badge.revision {
            background: #fff3e0;
            color: #f57c00;
        }

        .status-badge.publicado {
            background: #e8f5e9;
            color: #388e3c;
        }
    </style>
</head>
<body>
    <!-- Navbar simple -->
    <header class="navbar navbar-expand-md navbar-light d-print-none" data-bs-theme="<?php echo $user['tema'] ?? 'light'; ?>">
        <div class="container-xl">
            <a href="../../public/index.php" class="navbar-brand">
                <i class="ti ti-chart-bar me-2"></i>
                Sistema de Métricas
            </a>
            <div class="navbar-nav flex-row order-md-last">
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link d-flex align-items-center" data-bs-toggle="dropdown">
                        <span class="avatar avatar-sm" style="background-color: #206bc4">
                            <?php echo strtoupper(substr($user['nombre'], 0, 2)); ?>
                        </span>
                        <span class="ms-2"><?php echo e($user['nombre']); ?></span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl">
                <!-- Botón volver -->
                <div class="mb-3">
                    <a href="reportes.php" class="btn btn-ghost-secondary">
                        <i class="ti ti-arrow-left me-2"></i>
                        Volver a Reportes
                    </a>
                </div>

                <!-- Acciones flotantes -->
                <div class="report-actions">
                    <?php if (PermissionService::canEditArea($user, $reporte['area_id'])): ?>
                    <a href="reportes-editor.php?id=<?php echo $reporte['id']; ?>"
                       class="btn btn-primary btn-float"
                       title="Editar">
                        <i class="ti ti-edit"></i>
                    </a>
                    <?php endif; ?>

                    <a href="reportes-pdf.php?id=<?php echo $reporte['id']; ?>"
                       class="btn btn-danger btn-float"
                       target="_blank"
                       title="Exportar a PDF">
                        <i class="ti ti-file-type-pdf"></i>
                    </a>

                    <button onclick="window.print()"
                            class="btn btn-secondary btn-float"
                            title="Imprimir">
                        <i class="ti ti-printer"></i>
                    </button>

                    <button onclick="toggleDarkMode()"
                            class="btn btn-dark btn-float"
                            title="Cambiar tema">
                        <i class="ti ti-moon"></i>
                    </button>
                </div>

                <!-- Visor de reporte -->
                <div class="report-viewer">
                    <!-- Header -->
                    <div class="report-header">
                        <h1 class="report-title"><?php echo e($reporte['titulo']); ?></h1>

                        <div class="report-meta">
                            <div class="report-meta-item">
                                <i class="ti ti-building"></i>
                                <span><?php echo e($reporte['departamento_nombre']); ?></span>
                            </div>

                            <div class="report-meta-item">
                                <i class="ti ti-folder"></i>
                                <span style="color: <?php echo $reporte['area_color']; ?>">
                                    <?php echo e($reporte['area_nombre']); ?>
                                </span>
                            </div>

                            <?php if ($reporte['periodo_nombre']): ?>
                            <div class="report-meta-item">
                                <i class="ti ti-calendar"></i>
                                <span><?php echo e($reporte['periodo_nombre']); ?> <?php echo $reporte['anio']; ?></span>
                            </div>
                            <?php endif; ?>

                            <div class="report-meta-item">
                                <i class="ti ti-user"></i>
                                <span><?php echo e($reporte['autor_nombre']); ?></span>
                            </div>

                            <div class="report-meta-item">
                                <span class="status-badge <?php echo $reporte['estado']; ?>">
                                    <?php echo ucfirst($reporte['estado']); ?>
                                </span>
                            </div>
                        </div>

                        <?php if ($reporte['descripcion']): ?>
                        <p class="text-secondary mt-3" style="font-size: 1.1rem;">
                            <?php echo e($reporte['descripcion']); ?>
                        </p>
                        <?php endif; ?>
                    </div>

                    <!-- Contenido Markdown renderizado -->
                    <div class="report-content">
                        <div class="markdown-body">
                            <?php echo $contenidoHTML; ?>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="text-center mt-5 text-secondary">
                        <small>
                            Generado el <?php echo formatDateTime($reporte['created_at']); ?>
                            <?php if ($reporte['updated_at'] != $reporte['created_at']): ?>
                                | Última modificación: <?php echo formatDateTime($reporte['updated_at']); ?>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Highlight code blocks
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('pre code').forEach((block) => {
                hljs.highlightElement(block);
            });
        });

        // Toggle dark mode
        function toggleDarkMode() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-bs-theme', newTheme);
        }
    </script>
</body>
</html>
