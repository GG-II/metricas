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
    $departamento_id = (int)($_POST['departamento_id'] ?? 0);
    $mes = (int)($_POST['mes'] ?? 0);
    $anio = (int)($_POST['anio'] ?? date('Y'));
    $descripcion = trim($_POST['descripcion'] ?? '');
    $resumen_ejecutivo = trim($_POST['resumen_ejecutivo'] ?? '');
    $estado = $_POST['estado'] ?? 'borrador';
    $is_auto_save = ($_POST['auto_save'] ?? '0') === '1';

    // Validaciones
    if (empty($titulo)) {
        throw new Exception('El título es requerido');
    }

    if (!$departamento_id) {
        throw new Exception('El departamento es requerido');
    }

    if ($mes < 1 || $mes > 12) {
        throw new Exception('Mes inválido');
    }

    if (!$anio || $anio < 2000 || $anio > 2100) {
        throw new Exception('Año inválido');
    }

    // Verificar permisos sobre el departamento
    if ($user['rol'] !== 'super_admin' &&
        !($user['rol'] === 'dept_admin' && $user['departamento_id'] == $departamento_id)) {
        throw new Exception('No tienes permiso para crear/editar reportes en este departamento');
    }

    // Preparar datos
    $data = [
        'titulo' => $titulo,
        'departamento_id' => $departamento_id,
        'mes' => $mes,
        'anio' => $anio,
        'tipo_reporte' => 'mensual', // Siempre mensual consolidado
        'descripcion' => $descripcion,
        'resumen_ejecutivo' => $resumen_ejecutivo,
        'estado' => $estado
    ];

    if ($id) {
        // ACTUALIZAR
        $reporte = $reporteModel->find($id);

        if (!$reporte) {
            throw new Exception('Reporte no encontrado');
        }

        // Verificar permisos
        if ($user['rol'] !== 'super_admin' &&
            !($user['rol'] === 'dept_admin' && $user['departamento_id'] == $reporte['departamento_id'])) {
            throw new Exception('No tienes permiso para editar este reporte');
        }

        $data['usuario_modificacion_id'] = $user['id'];

        $reporteModel->update($id, $data);

        // Si está marcando como publicado y no lo estaba, registrar publicación
        if ($estado === 'publicado' && $reporte['estado'] !== 'publicado') {
            $reporteModel->publicar($id, $user['id']);
        }

        echo json_encode([
            'success' => true,
            'reporte_id' => $id,
            'message' => $is_auto_save ? 'Guardado automático' : 'Reporte actualizado correctamente',
            'redirect' => 'reportes.php'
        ]);

    } else {
        // CREAR NUEVO
        $data['usuario_creacion_id'] = $user['id'];
        $data['version'] = 1;

        $nuevo_id = $reporteModel->create($data);

        echo json_encode([
            'success' => true,
            'reporte_id' => $nuevo_id,
            'message' => 'Reporte creado correctamente',
            'redirect' => 'reportes.php'
        ]);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
