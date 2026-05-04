<?php
// Capturar TODOS los errores FATALES y devolverlos como JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Manejador de errores fatales
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Error fatal de PHP: ' . $error['message'] . ' en ' . $error['file'] . ':' . $error['line']
        ]);
    }
});

session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Models\Reporte;
use App\Services\PermissionService;

// Establecer header JSON ANTES de todo
header('Content-Type: application/json');

try {
    AuthMiddleware::handle();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'No autorizado: ' . $e->getMessage()]);
    exit;
}

$user = getCurrentUser();
$reporteModel = new Reporte();

try {
    // Validar método
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Obtener datos
    $id = $_POST['id'] ?? null;
    $titulo = trim($_POST['titulo'] ?? '');
    $area_id = (int)($_POST['area_id'] ?? 0);
    $tipo_reporte = $_POST['tipo_reporte'] ?? 'mensual';
    $periodo_id = !empty($_POST['periodo_id']) ? (int)$_POST['periodo_id'] : null;
    $anio = (int)($_POST['anio'] ?? date('Y'));
    $descripcion = trim($_POST['descripcion'] ?? '');
    $contenido = $_POST['contenido'] ?? '';
    $is_auto_save = ($_POST['auto_save'] ?? '0') === '1';

    // Validaciones
    if (empty($titulo)) {
        throw new Exception('El título es requerido');
    }

    if (!$area_id) {
        throw new Exception('El área es requerida');
    }

    if (!$anio || $anio < 2000 || $anio > 2100) {
        throw new Exception('Año inválido');
    }

    // Verificar permisos sobre el área
    if (!PermissionService::canEditArea($user, $area_id)) {
        throw new Exception('No tienes permiso para crear/editar reportes en esta área');
    }

    // Preparar datos
    $data = [
        'titulo' => $titulo,
        'area_id' => $area_id,
        'tipo_reporte' => $tipo_reporte,
        'periodo_id' => $periodo_id,
        'anio' => $anio,
        'descripcion' => $descripcion,
        'contenido' => $contenido
    ];

    if ($id) {
        // ACTUALIZAR
        $reporte = $reporteModel->find($id);

        if (!$reporte) {
            throw new Exception('Reporte no encontrado');
        }

        // Verificar permisos
        if (!PermissionService::canEditArea($user, $reporte['area_id'])) {
            throw new Exception('No tienes permiso para editar este reporte');
        }

        $data['usuario_modificacion_id'] = $user['id'];

        $reporteModel->update($id, $data);

        echo json_encode([
            'success' => true,
            'reporte_id' => $id,
            'message' => $is_auto_save ? 'Guardado automático' : 'Reporte actualizado correctamente'
        ]);

    } else {
        // CREAR NUEVO
        $data['usuario_creacion_id'] = $user['id'];
        $data['estado'] = 'borrador';
        $data['version'] = 1;

        $nuevo_id = $reporteModel->create($data);

        echo json_encode([
            'success' => true,
            'reporte_id' => $nuevo_id,
            'message' => 'Reporte creado correctamente'
        ]);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
