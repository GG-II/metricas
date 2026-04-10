<?php
/**
 * API: Guardar Layout del Dashboard
 * Recibe posiciones de gráficos vía AJAX y las guarda en BD
 */

require_once '../config.php';

// Solo responder a peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Verificar que sea admin
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No tienes permisos']);
    exit;
}

// Obtener datos JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['area_id']) || !isset($data['items'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

$area_id = (int)$data['area_id'];
$items = $data['items'];

try {
    $db = getDB();
    $db->beginTransaction();
    
    $actualizados = 0;
    
    foreach ($items as $item) {
        $grafico_id = (int)$item['id'];
        $grid_x = (int)$item['x'];
        $grid_y = (int)$item['y'];
        $grid_w = (int)$item['w'];
        $grid_h = (int)$item['h'];
        
        // Validar que el gráfico pertenece al área
        $stmt = $db->prepare("SELECT id FROM configuracion_graficos WHERE id = ? AND area_id = ?");
        $stmt->execute([$grafico_id, $area_id]);
        
        if (!$stmt->fetch()) {
            throw new Exception("Gráfico ID $grafico_id no pertenece al área $area_id");
        }
        
        // Actualizar posición
        $stmt = $db->prepare("
            UPDATE configuracion_graficos 
            SET grid_x = ?, grid_y = ?, grid_w = ?, grid_h = ?
            WHERE id = ?
        ");
        
        $stmt->execute([$grid_x, $grid_y, $grid_w, $grid_h, $grafico_id]);
        $actualizados++;
    }
    
    $db->commit();
    
    // Registrar en log
    logActivity(
        'actualizar_layout_dashboard',
        'configuracion_graficos',
        $area_id,
        "Actualizado layout de $actualizados gráficos"
    );
    
    echo json_encode([
        'success' => true,
        'message' => "Layout guardado: $actualizados gráficos actualizados",
        'actualizados' => $actualizados
    ]);
    
} catch (Exception $e) {
    $db->rollBack();
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar layout: ' . $e->getMessage()
    ]);
    
    error_log("Error guardar-layout.php: " . $e->getMessage());
}