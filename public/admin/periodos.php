<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Periodo;

AuthMiddleware::handle();
PermissionMiddleware::requireSuperAdmin();

$user = getCurrentUser();
$periodoModel = new Periodo();

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ✅ PROTECCIÓN CSRF
    csrf_validate();

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'crear':
                $ejercicio = (int)$_POST['ejercicio'];
                $periodo = (int)$_POST['periodo'];

                // Verificar que no exista ya
                $existe = $periodoModel->findByEjercicioAndPeriodo($ejercicio, $periodo);
                if ($existe) {
                    setFlash('error', 'Este período ya existe');
                    redirect('/public/admin/periodos.php');
                    break;
                }

                // Generar nombre automático
                $meses = [
                    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                ];
                $nombre = $meses[$periodo] . ' ' . $ejercicio;

                // Calcular fechas de inicio y fin
                $fecha_inicio = date('Y-m-d', strtotime("$ejercicio-$periodo-01"));
                $fecha_fin = date('Y-m-t', strtotime($fecha_inicio));

                $data = [
                    'ejercicio' => $ejercicio,
                    'periodo' => $periodo,
                    'nombre' => $nombre,
                    'fecha_inicio' => $fecha_inicio,
                    'fecha_fin' => $fecha_fin,
                    'activo' => 1
                ];

                if ($periodoModel->create($data)) {
                    setFlash('success', "✓ Período $nombre creado exitosamente");
                } else {
                    setFlash('error', 'Error al crear el período');
                }
                redirect('/public/admin/periodos.php');
                break;

            case 'toggle_activo':
                $id = (int)$_POST['id'];
                $nuevo_estado = (int)$_POST['activo'];

                if ($periodoModel->update($id, ['activo' => $nuevo_estado])) {
                    setFlash('success', $nuevo_estado ? 'Período activado' : 'Período desactivado');
                } else {
                    setFlash('error', 'Error al cambiar el estado');
                }
                redirect('/public/admin/periodos.php');
                break;

            case 'generar_anio':
                $ejercicio = (int)$_POST['generar_ejercicio'];
                $creados = 0;

                $meses = [
                    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                ];

                for ($mes = 1; $mes <= 12; $mes++) {
                    // Verificar que no exista
                    $existe = $periodoModel->findByEjercicioAndPeriodo($ejercicio, $mes);
                    if ($existe) continue;

                    $nombre = $meses[$mes] . ' ' . $ejercicio;
                    $fecha_inicio = date('Y-m-d', strtotime("$ejercicio-$mes-01"));
                    $fecha_fin = date('Y-m-t', strtotime($fecha_inicio));

                    $data = [
                        'ejercicio' => $ejercicio,
                        'periodo' => $mes,
                        'nombre' => $nombre,
                        'fecha_inicio' => $fecha_inicio,
                        'fecha_fin' => $fecha_fin,
                        'activo' => 1
                    ];

                    if ($periodoModel->create($data)) {
                        $creados++;
                    }
                }

                if ($creados > 0) {
                    setFlash('success', "✓ $creados períodos generados para el año $ejercicio");
                } else {
                    setFlash('info', 'Todos los períodos del año ya existen');
                }
                redirect('/public/admin/periodos.php');
                break;
        }
    }
}

// Obtener todos los períodos (activos e inactivos)
$periodos = $periodoModel->getAllWithStats();

// Agrupar por ejercicio
$periodos_por_anio = [];
foreach ($periodos as $periodo) {
    $periodos_por_anio[$periodo['ejercicio']][] = $periodo;
}

// Ordenar períodos dentro de cada año de enero (1) a diciembre (12)
foreach ($periodos_por_anio as $anio => &$periodos_anio) {
    usort($periodos_anio, function($a, $b) {
        return $a['periodo'] - $b['periodo'];
    });
}
unset($periodos_anio); // Romper referencia

krsort($periodos_por_anio); // Ordenar años descendente

// Paginación por año
$anios_disponibles = array_keys($periodos_por_anio);
$anio_seleccionado = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)date('Y');

// Si el año seleccionado no existe, usar el primer año disponible
if (!in_array($anio_seleccionado, $anios_disponibles) && !empty($anios_disponibles)) {
    $anio_seleccionado = $anios_disponibles[0];
}

// Filtrar solo el año seleccionado
if (isset($periodos_por_anio[$anio_seleccionado])) {
    $periodos_mostrar = [$anio_seleccionado => $periodos_por_anio[$anio_seleccionado]];
} else {
    $periodos_mostrar = [];
}

$pageTitle = 'Gestión de Períodos';
require_once __DIR__ . '/../../views/layouts/header.php';
?>

<div class="page-wrapper">
    <div class="page-body">
        <div class="container-xl">

            <!-- Breadcrumb -->
            <div class="page-header mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo baseUrl('/public/admin/index.php'); ?>">Administración</a></li>
                                <li class="breadcrumb-item active">Períodos</li>
                            </ol>
                        </nav>
                        <h2 class="page-title">
                            <i class="ti ti-calendar me-2"></i>
                            Gestión de Períodos
                        </h2>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalGenerarAnio">
                            <i class="ti ti-calendar-plus me-1"></i>
                            Generar Año Completo
                        </button>
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
            <?php if (isset($_SESSION['flash']) && isset($_SESSION['flash']['type']) && isset($_SESSION['flash']['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['flash']['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo e($_SESSION['flash']['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <!-- Paginación de Años -->
            <?php if (!empty($anios_disponibles)): ?>
            <div class="card mb-3">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            <i class="ti ti-calendar me-1"></i>
                            Mostrando períodos de: <strong><?php echo $anio_seleccionado; ?></strong>
                        </div>
                        <div class="btn-group" role="group">
                            <?php foreach ($anios_disponibles as $anio): ?>
                            <a href="?anio=<?php echo $anio; ?>"
                               class="btn btn-sm <?php echo $anio === $anio_seleccionado ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                <?php echo $anio; ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Períodos del año seleccionado -->
            <?php if (empty($periodos_por_anio)): ?>
                <div class="empty text-center py-5">
                    <div class="empty-icon mb-3">
                        <i class="ti ti-calendar" style="font-size: 4rem; color: #94a3b8;"></i>
                    </div>
                    <h2>No hay períodos configurados</h2>
                    <p class="text-muted">Los períodos son necesarios para capturar valores de métricas.</p>
                    <div class="empty-action">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalGenerarAnio">
                            <i class="ti ti-calendar-plus me-1"></i>
                            Generar Año Completo
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($periodos_mostrar as $anio => $periodos_anio): ?>
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-calendar me-2"></i>
                            Año <?php echo $anio; ?>
                        </h3>
                        <div class="card-actions">
                            <span class="badge bg-blue-lt">
                                <?php echo count($periodos_anio); ?> períodos
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>Período</th>
                                        <th>Fechas</th>
                                        <th>Valores Capturados</th>
                                        <th>Estado</th>
                                        <th class="w-1"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($periodos_anio as $periodo): ?>
                                    <tr class="<?php echo !$periodo['activo'] ? 'opacity-50' : ''; ?>">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="avatar avatar-sm me-2 <?php echo $periodo['es_actual'] ? 'bg-blue' : 'bg-azure-lt'; ?>">
                                                    <i class="ti ti-calendar"></i>
                                                </span>
                                                <div>
                                                    <div class="fw-bold"><?php echo e($periodo['nombre']); ?></div>
                                                    <div class="text-muted small">
                                                        Mes <?php echo $periodo['periodo']; ?> / <?php echo $periodo['ejercicio']; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small">
                                                <?php echo date('d/m/Y', strtotime($periodo['fecha_inicio'])); ?>
                                                <span class="text-muted">al</span>
                                                <?php echo date('d/m/Y', strtotime($periodo['fecha_fin'])); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($periodo['total_valores'] > 0): ?>
                                                <span class="badge bg-green-lt">
                                                    <i class="ti ti-check me-1"></i>
                                                    <?php echo $periodo['total_valores']; ?> valores
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">Sin valores</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($periodo['activo']): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactivo</span>
                                            <?php endif; ?>
                                            <?php if ($periodo['es_actual']): ?>
                                                <span class="badge bg-blue ms-1">Actual</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="toggle_activo">
                                                <input type="hidden" name="id" value="<?php echo $periodo['id']; ?>">
                                                <input type="hidden" name="activo" value="<?php echo $periodo['activo'] ? 0 : 1; ?>">
                                                <button type="submit" class="btn btn-sm btn-icon btn-ghost-<?php echo $periodo['activo'] ? 'warning' : 'success'; ?>"
                                                        title="<?php echo $periodo['activo'] ? 'Desactivar' : 'Activar'; ?>">
                                                    <i class="ti ti-<?php echo $periodo['activo'] ? 'eye-off' : 'eye'; ?>"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Modal de Crear Período Individual -->
<div class="modal modal-blur fade" id="modalPeriodo" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Período</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="crear">

                    <div class="mb-3">
                        <label class="form-label required">Año (Ejercicio)</label>
                        <input type="number" name="ejercicio" class="form-control"
                               value="<?php echo date('Y'); ?>" required min="2020" max="2099">
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Mes</label>
                        <select name="periodo" class="form-select" required>
                            <?php
                            $meses = [
                                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                            ];
                            $mes_actual = (int)date('n');
                            foreach ($meses as $num => $nombre):
                            ?>
                            <option value="<?php echo $num; ?>" <?php echo $num == $mes_actual ? 'selected' : ''; ?>>
                                <?php echo $nombre; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="ti ti-info-circle me-2"></i>
                        El nombre y las fechas se generarán automáticamente.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check me-1"></i>
                        Crear Período
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Generar Año Completo -->
<div class="modal modal-blur fade" id="modalGenerarAnio" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Generar Año Completo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="generar_anio">

                    <div class="mb-3">
                        <label class="form-label required">Año a Generar</label>
                        <input type="number" name="generar_ejercicio" class="form-control"
                               value="<?php echo date('Y'); ?>" required min="2020" max="2099">
                    </div>

                    <div class="alert alert-blue">
                        <div class="d-flex">
                            <div>
                                <i class="ti ti-calendar-plus icon alert-icon"></i>
                            </div>
                            <div>
                                <h4 class="alert-title">Generación automática</h4>
                                <div class="text-muted">
                                    Se crearán automáticamente los 12 períodos del año seleccionado.
                                    Los períodos que ya existan serán omitidos.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-calendar-plus me-1"></i>
                        Generar 12 Períodos
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS (incluye Dropdown) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Tabler JS -->
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/js/tabler.min.js"></script>
<script src="<?php echo baseUrl('/public/assets/js/theme-toggle.js'); ?>"></script>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
