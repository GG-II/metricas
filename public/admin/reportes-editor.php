<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Services\PermissionService;
use App\Models\Reporte;
use App\Models\Departamento;
use App\Models\Area;
use App\Models\Periodo;
use App\Models\Grafico;

AuthMiddleware::handle();

$user = getCurrentUser();
$reporteModel = new Reporte();

// Modo: crear o editar
$reporte_id = $_GET['id'] ?? null;
$modo = $reporte_id ? 'editar' : 'crear';

$reporte = null;
if ($modo === 'editar') {
    $reporte = $reporteModel->findWithFullDetails($reporte_id);

    if (!$reporte) {
        die('Reporte no encontrado');
    }

    // Verificar permisos
    if (!PermissionService::canEditArea($user, $reporte['area_id'])) {
        die('No tienes permiso para editar este reporte');
    }
}

// Obtener departamentos y áreas permitidos
$departamentos = PermissionService::getDepartamentosPermitidos($user);
$periodoModel = new Periodo();
$periodos = $periodoModel->getAll();

// Si es creación, pre-seleccionar departamento/área del usuario o desde parámetro
$departamento_inicial = null;
$area_inicial = null;
$tipo_reporte_inicial = 'mensual';
$periodo_inicial = null;
$anio_inicial = date('Y');

if ($modo === 'crear') {
    // Si viene área por parámetro (desde modal)
    $area_inicial = $_GET['area'] ?? null;
    $tipo_reporte_inicial = $_GET['tipo_reporte'] ?? 'mensual';
    $periodo_inicial = $_GET['periodo_id'] ?? null;
    $anio_inicial = $_GET['anio'] ?? date('Y');

    if ($area_inicial) {
        // Obtener departamento del área
        $areaModel = new Area();
        $areaData = $areaModel->findWithDepartamento($area_inicial);
        if ($areaData) {
            $departamento_inicial = $areaData['departamento_id'];
        }
    } elseif ($user['rol'] === 'dept_admin') {
        $departamento_inicial = $user['departamento_id'];
    }
} else {
    $departamento_inicial = $reporte['departamento_id'];
    $area_inicial = $reporte['area_id'];
    $tipo_reporte_inicial = $reporte['tipo_reporte'];
    $periodo_inicial = $reporte['periodo_id'];
    $anio_inicial = $reporte['anio'];
}

$pageTitle = ($modo === 'crear' ? 'Crear Nuevo Reporte' : 'Editar Reporte');
?>

<!DOCTYPE html>
<html lang="es" data-bs-theme="<?php echo $user['tema'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Sistema de Métricas</title>

    <!-- Tabler CSS -->
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet">

    <!-- EasyMDE Markdown Editor -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde@2.18.0/dist/easymde.min.css">
    <script src="https://cdn.jsdelivr.net/npm/easymde@2.18.0/dist/easymde.min.js"></script>

    <!-- SweetAlert2 para notificaciones elegantes -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* ========================================
           ESTILOS TIPO WORD - VERSION MEJORADA
           ======================================== */

        body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* ========================================
           HEADERS STICKY TIPO GOOGLE DOCS
           ======================================== */

        /* Header 1: Top - Archivo y controles */
        .top-header {
            background-color: var(--tblr-bg-surface);
            border-bottom: 1px solid var(--tblr-border-color);
            position: sticky;
            top: 0;
            z-index: 1002;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            gap: 1rem;
        }

        /* Header 2: Toolbar - Debajo del Header 1 */
        .toolbar-header {
            position: sticky;
            top: 56px; /* Altura del top-header */
            z-index: 1001;
            background-color: var(--tblr-bg-surface);
            border-bottom: 1px solid var(--tblr-border-color);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .header-center {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .header-center h3 {
            margin: 0;
            font-size: 1rem;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Botón Archivo */
        .word-tab-btn {
            background: transparent;
            border: none;
            padding: 0.4rem 0.875rem;
            font-size: 0.875rem;
            color: var(--tblr-body-color);
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .word-tab-btn:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }

        [data-bs-theme="dark"] .word-tab-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Área del documento - gris como Word */
        .document-area {
            background-color: #f3f3f3 !important;
            padding: 40px 20px;
            min-height: calc(100vh - 112px); /* Compensar ambos headers sticky */
            overflow-y: auto;
        }

        [data-bs-theme="dark"] .document-area {
            background-color: #0f172a !important;
        }

        /* Contenedor de páginas - simula páginas múltiples */
        .pages-container {
            max-width: 21.59cm;
            margin: 0 auto;
            position: relative;
        }

        /* Hoja simple continua (sin altura fija) */
        .document-page {
            background-color: #ffffff !important;
            width: 21.59cm;  /* Letter width */
            min-height: 27.94cm;
            padding: 2.54cm; /* 1 inch margins */
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            position: relative;
            margin: 0 auto;
        }

        /* Quill Editor Container */
        #editor-container {
            background-color: transparent !important;
            font-family: 'Calibri', 'Arial', sans-serif !important;
            font-size: 11pt !important;
            line-height: 1.6 !important;
            color: #000000 !important;
            border: none !important;
            min-height: 22.86cm;
        }

        .ql-container {
            border: none !important;
            font-size: 11pt !important;
        }

        .ql-editor {
            padding: 0 !important;
            background-color: transparent !important;
            color: #000000 !important;
        }

        /* Toolbar de EasyMDE - dentro de toolbar-header */
        .EasyMDEContainer .editor-toolbar {
            position: static !important;
            background-color: transparent !important;
            border: none !important;
            border-radius: 0 !important;
            padding: 8px 20px !important;
        }

        .CodeMirror {
            border: none !important;
        }

        /* Espacio para que el contenido no quede debajo del toolbar sticky */
        .EasyMDEContainer {
            margin-top: 0 !important;
        }

        .CodeMirror-scroll {
            margin-top: 0 !important;
        }

        /* ========================================
           MODO OSCURO - ADAPTAR UI (NO DOCUMENTO)
           ======================================== */

        /* Headers en modo oscuro */
        [data-bs-theme="dark"] .top-header,
        [data-bs-theme="dark"] .toolbar-header {
            background-color: #1e293b !important;
            border-bottom-color: #334155 !important;
        }

        /* Las páginas SIEMPRE son blancas, sin importar el tema */
        .document-page {
            background-color: #ffffff !important;
        }

        /* Toolbar EasyMDE en modo oscuro */
        [data-bs-theme="dark"] .EasyMDEContainer .editor-toolbar {
            background-color: transparent !important;
        }

        [data-bs-theme="dark"] .editor-toolbar a {
            color: #e2e8f0 !important;
        }

        [data-bs-theme="dark"] .editor-toolbar a:hover {
            background-color: #334155 !important;
            border-color: #334155 !important;
        }

        [data-bs-theme="dark"] .editor-toolbar.disabled-for-preview a:not(.no-disable) {
            background-color: transparent !important;
            opacity: 0.5;
        }

        [data-bs-theme="dark"] .editor-toolbar i.separator {
            border-left: 1px solid #334155 !important;
            border-right: 1px solid #475569 !important;
        }

        /* ========================================
           DROPDOWN DE BOOTSTRAP (Exportar)
           ======================================== */
        .dropdown-menu {
            z-index: 9999 !important;
        }

        [data-bs-theme="dark"] .dropdown-menu {
            background-color: #1e293b !important;
            border-color: #334155 !important;
        }

        [data-bs-theme="dark"] .dropdown-item {
            color: #e2e8f0 !important;
        }

        [data-bs-theme="dark"] .dropdown-item:hover {
            background-color: #334155 !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .dropdown-divider {
            border-color: #334155 !important;
        }

        .dropdown-header {
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 600;
            padding-top: 0.5rem;
            padding-bottom: 0.25rem;
        }

        [data-bs-theme="dark"] .dropdown-header {
            color: #94a3b8 !important;
        }

        /* ========================================
           TÍTULO EDITABLE INLINE
           ======================================== */
        #document-title-display {
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        #document-title-display:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }

        [data-bs-theme="dark"] #document-title-display:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        #document-title-input {
            font-size: 1rem;
            font-weight: 500;
            border: 2px solid #3b82f6;
            padding: 4px 8px;
            border-radius: 4px;
            background-color: #ffffff;
            color: #000000;
        }

        [data-bs-theme="dark"] #document-title-input {
            background-color: #334155 !important;
            color: #e2e8f0 !important;
            border-color: #3b82f6 !important;
        }

        /* Contenido del editor: SIEMPRE BLANCO */
        /* Estilos tipo Word */
        .ql-editor h1 {
            font-size: 20pt !important;
            font-weight: bold !important;
            margin-top: 24px !important;
            margin-bottom: 12px !important;
        }

        .ql-editor h2 {
            font-size: 16pt !important;
            font-weight: bold !important;
            margin-top: 18px !important;
            margin-bottom: 10px !important;
        }

        .ql-editor h3 {
            font-size: 14pt !important;
            font-weight: bold !important;
            margin-top: 14px !important;
            margin-bottom: 8px !important;
        }

        .ql-editor p {
            margin-bottom: 10pt !important;
        }

        .ql-editor table {
            border-collapse: collapse !important;
            border: 1px solid #000 !important;
        }

        .ql-editor table td,
        .ql-editor table th {
            border: 1px solid #000 !important;
            padding: 6px !important;
        }

        /* Placeholder */
        .ql-editor.ql-blank::before {
            color: #999 !important;
            font-style: italic !important;
        }

        /* Indicador de guardado */
        .save-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--tblr-muted);
        }

        .save-indicator.saving {
            color: #f59e0b;
        }

        .save-indicator.saved {
            color: #10b981;
        }

        .save-indicator.error {
            color: #ef4444;
        }

        /* Spinner de guardado */
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: 0.15em;
        }

        /* Modal de gráficos */
        .chart-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            max-height: 500px;
            overflow-y: auto;
        }

        .chart-thumbnail {
            border: 2px solid transparent;
            border-radius: 0.5rem;
            padding: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
            background: var(--tblr-bg-surface);
        }

        .chart-thumbnail:hover {
            border-color: #3b82f6;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }

        .chart-thumbnail.selected {
            border-color: #3b82f6;
            background-color: rgba(59, 130, 246, 0.05);
        }

        .chart-preview {
            width: 100%;
            height: 150px;
            background: #ffffff;
            border-radius: 0.25rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chart-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        /* Contador de palabras */
        .word-count {
            font-size: 0.875rem;
            color: var(--tblr-muted);
        }

        #word-count {
            border-left: 1px solid var(--tblr-border-color);
            padding-left: 1rem;
            margin-left: 0.5rem;
        }

        #save-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Metadata form */
        .metadata-form {
            background: var(--tblr-bg-surface);
            border-bottom: 1px solid var(--tblr-border-color);
            padding: 1rem 1.5rem;
            position: relative;
            z-index: 999;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .document-page {
                width: 100%;
                padding: 1.5cm;
                height: auto;
            }
        }

        /* ========================================
           REDIMENSIONAMIENTO DE IMÁGENES
           ======================================== */
        .ql-editor img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 10px auto;
            cursor: pointer;
        }

        .ql-editor img.selected-image {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }

        .resize-handle {
            position: absolute;
            width: 10px;
            height: 10px;
            background: #3b82f6;
            border: 2px solid white;
            border-radius: 50%;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .resize-handle:hover {
            background: #2563eb;
            transform: scale(1.2);
        }

        @media print {
            .top-header,
            .toolbar-header,
            .save-indicator {
                display: none !important;
            }

            .document-area {
                background-color: #ffffff !important;
                padding: 0 !important;
            }

            .pages-container {
                gap: 0 !important;
            }

            .document-page {
                box-shadow: none;
                margin: 0;
                page-break-after: always;
            }

            .document-page:last-child {
                page-break-after: auto;
            }

            .resize-handle {
                display: none !important;
            }

            .ql-editor img.selected-image {
                outline: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- Header 1: Top - Archivo y Controles -->
    <div class="top-header">
        <!-- Izquierda: Volver + Archivo -->
        <div class="header-left">
            <!-- Volver -->
            <a href="reportes.php" class="btn btn-ghost-secondary btn-sm" title="Volver">
                <i class="ti ti-arrow-left"></i>
            </a>

            <!-- Dropdown Archivo -->
            <div class="dropdown">
                <button class="word-tab-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    Archivo
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="#" id="btn-save-manual-menu">
                        <i class="ti ti-device-floppy me-2"></i> Guardar
                    </a>
                    <?php if ($modo === 'editar' && $reporte['estado'] !== 'publicado'): ?>
                    <a class="dropdown-item" href="#" id="btn-publish-menu">
                        <i class="ti ti-send me-2"></i> Publicar
                    </a>
                    <?php endif; ?>
                    <?php if ($modo === 'editar'): ?>
                    <div class="dropdown-divider"></div>
                    <h6 class="dropdown-header">Exportar como</h6>
                    <a class="dropdown-item" href="reportes-export.php?id=<?php echo $reporte_id; ?>&format=pdf" target="_blank">
                        <i class="ti ti-file-type-pdf me-2"></i> PDF
                    </a>
                    <a class="dropdown-item" href="reportes-export.php?id=<?php echo $reporte_id; ?>&format=docx" target="_blank">
                        <i class="ti ti-file-type-docx me-2"></i> Word (DOCX)
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Centro: Título del documento -->
        <div class="header-center">
            <h3>
                <i class="ti ti-file-text me-2"></i>
                <span id="document-title-display">
                    <?php echo $modo === 'editar' ? htmlspecialchars($reporte['titulo']) : 'Nuevo Reporte'; ?>
                </span>
            </h3>
        </div>

        <!-- Derecha: Guardado + Contador + Tema -->
        <div class="header-right">
            <!-- Indicador de guardado -->
            <div class="save-indicator" id="save-indicator">
                <span class="spinner-border spinner-border-sm d-none" id="save-spinner"></span>
                <span id="save-text">Todos los cambios guardados</span>
            </div>

            <!-- Contador de palabras -->
            <span class="text-muted small" id="word-count">0 palabras</span>

            <!-- Toggle tema -->
            <button type="button" class="btn btn-ghost-secondary btn-sm" id="theme-toggle" title="Cambiar tema">
                <i class="ti ti-moon" id="theme-icon"></i>
            </button>
        </div>
    </div>

    <!-- Header 2: Toolbar EasyMDE -->
    <div class="toolbar-header">
        <!-- EasyMDE toolbar se renderiza aquí automáticamente -->
    </div>

    <!-- Metadata Form - OCULTO si viene desde modal -->
    <div class="metadata-form" id="metadata-section" style="display:none;">
        <form id="metadata-form">
            <div class="row g-3">
                <!-- Título -->
                <div class="col-md-6">
                    <label class="form-label required">Título del Reporte</label>
                    <input type="text" class="form-control" id="titulo" name="titulo" required
                           value="<?php echo $modo === 'editar' ? htmlspecialchars($reporte['titulo']) : ''; ?>"
                           placeholder="Ej: Reporte Mensual de Desarrollo...">
                </div>

                <!-- Departamento -->
                <?php if ($user['rol'] === 'super_admin'): ?>
                <div class="col-md-3">
                    <label class="form-label required">Departamento</label>
                    <select class="form-select" id="departamento_id" name="departamento_id" required>
                        <option value="">Seleccionar...</option>
                        <?php foreach ($departamentos as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>"
                                    <?php echo $departamento_inicial == $dept['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php else: ?>
                    <input type="hidden" id="departamento_id" name="departamento_id" value="<?php echo $user['departamento_id']; ?>">
                <?php endif; ?>

                <!-- Área -->
                <div class="col-md-3">
                    <label class="form-label required">Área</label>
                    <select class="form-select" id="area_id" name="area_id" required>
                        <option value="">Seleccionar...</option>
                        <?php
                        $areas = PermissionService::getAreasPermitidas($user, $departamento_inicial);
                        foreach ($areas as $area):
                        ?>
                            <option value="<?php echo $area['id']; ?>"
                                    <?php echo $area_inicial == $area['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($area['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Tipo de Reporte -->
                <div class="col-md-3">
                    <label class="form-label required">Tipo</label>
                    <select class="form-select" id="tipo_reporte" name="tipo_reporte" required>
                        <option value="mensual" <?php echo ($reporte['tipo_reporte'] ?? '') === 'mensual' ? 'selected' : ''; ?>>Mensual</option>
                        <option value="trimestral" <?php echo ($reporte['tipo_reporte'] ?? '') === 'trimestral' ? 'selected' : ''; ?>>Trimestral</option>
                        <option value="semestral" <?php echo ($reporte['tipo_reporte'] ?? '') === 'semestral' ? 'selected' : ''; ?>>Semestral</option>
                        <option value="anual" <?php echo ($reporte['tipo_reporte'] ?? '') === 'anual' ? 'selected' : ''; ?>>Anual</option>
                    </select>
                </div>

                <!-- Período (condicional) -->
                <div class="col-md-3" id="periodo-container">
                    <label class="form-label">Período</label>
                    <select class="form-select" id="periodo_id" name="periodo_id">
                        <option value="">N/A (Anual)</option>
                        <?php foreach ($periodos as $periodo): ?>
                            <option value="<?php echo $periodo['id']; ?>"
                                    <?php echo ($reporte['periodo_id'] ?? '') == $periodo['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($periodo['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Año -->
                <div class="col-md-2">
                    <label class="form-label required">Año</label>
                    <input type="number" class="form-control" id="anio" name="anio" required
                           value="<?php echo $reporte['anio'] ?? date('Y'); ?>"
                           min="2000" max="2100">
                </div>

                <!-- Descripción breve -->
                <div class="col-md-12">
                    <label class="form-label">Descripción Breve (Resumen Ejecutivo)</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="2"
                              placeholder="Breve resumen del reporte (opcional)..."><?php echo $modo === 'editar' ? htmlspecialchars($reporte['descripcion']) : ''; ?></textarea>
                </div>

                <!-- Botón continuar -->
                <?php if ($modo === 'crear'): ?>
                <div class="col-md-12">
                    <button type="button" class="btn btn-primary" id="btn-start-writing">
                        <i class="ti ti-pencil me-1"></i>
                        Comenzar a Escribir
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Área del Documento Simple -->
    <div class="document-area">
        <div class="pages-container">
            <div class="document-page">
                <textarea id="markdown-editor" name="contenido"><?php echo $modo === 'editar' ? htmlspecialchars($reporte['contenido']) : ''; ?></textarea>
            </div>
        </div>
    </div>

    <!-- Modal: Insertar Gráfico -->
    <div class="modal fade" id="modal-insert-chart" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-chart-bar me-2"></i>
                        Insertar Gráfico
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Selecciona un gráfico del área para insertar en el reporte:</p>

                    <!-- Galería de gráficos -->
                    <div class="chart-gallery" id="chart-gallery">
                        <!-- Se llena dinámicamente -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btn-insert-chart-confirm">
                        <i class="ti ti-plus me-1"></i>
                        Insertar Gráfico
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        // Configuración global
        const REPORTE_ID = <?php echo $reporte_id ?? 'null'; ?>;
        const MODO = '<?php echo $modo; ?>';
        const AREA_ID = <?php echo $area_inicial ?? 'null'; ?>;
        const INITIAL_CONTENT = <?php echo json_encode($modo === 'editar' && isset($reporte['contenido']) ? $reporte['contenido'] : ''); ?>;
    </script>

    <script>
        // ========================================
        // TÍTULO EDITABLE INLINE
        // ========================================
        document.addEventListener('DOMContentLoaded', function() {
            const titleDisplay = document.getElementById('document-title-display');
            const titleContainer = titleDisplay.parentElement;
            let originalTitle = titleDisplay.textContent.trim();

            titleDisplay.addEventListener('click', function() {
                // Crear input
                const input = document.createElement('input');
                input.type = 'text';
                input.id = 'document-title-input';
                input.className = 'form-control form-control-sm';
                input.value = titleDisplay.textContent.trim();
                input.style.width = Math.max(200, titleDisplay.offsetWidth) + 'px';

                // Reemplazar span por input
                titleContainer.replaceChild(input, titleDisplay);
                input.focus();
                input.select();

                // Guardar al perder foco
                function saveTitle() {
                    const newTitle = input.value.trim();
                    if (newTitle && newTitle !== originalTitle) {
                        originalTitle = newTitle;

                        // Actualizar hidden input si existe
                        const hiddenTitulo = document.getElementById('titulo');
                        if (hiddenTitulo) {
                            hiddenTitulo.value = newTitle;
                        }

                        // Trigger autosave si está disponible
                        if (typeof saveReporte === 'function') {
                            saveReporte(true);
                        }
                    }

                    // Restaurar span
                    titleDisplay.textContent = newTitle || originalTitle;
                    titleContainer.replaceChild(titleDisplay, input);
                }

                input.addEventListener('blur', saveTitle);
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        input.blur();
                    } else if (e.key === 'Escape') {
                        input.value = originalTitle;
                        input.blur();
                    }
                });
            });

            // ========================================
            // BOTONES DEL MENÚ ARCHIVO
            // ========================================
            const btnSaveMenu = document.getElementById('btn-save-manual-menu');
            const btnPublishMenu = document.getElementById('btn-publish-menu');

            if (btnSaveMenu) {
                btnSaveMenu.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (typeof saveReporte === 'function') {
                        saveReporte(false);
                    }
                });
            }

            if (btnPublishMenu) {
                btnPublishMenu.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (typeof publishReporte === 'function') {
                        publishReporte();
                    }
                });
            }
        });
    </script>

    <script src="<?php echo baseUrl('/public/assets/js/reportes-editor.js'); ?>"></script>

</body>
</html>
