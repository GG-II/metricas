<?php
/**
 * API: Obtener Layout del Dashboard
 * Retorna gráficos con sus posiciones y configuraciones
 */

require_once '../config.php';
requireLogin();

require_once '../models/Grafico.php';

// Solo responder a peticiones GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$area_id = isset($_GET['area_id']) ? (int)$_GET['area_id'] : 0;

if (!$area_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'area_id requerido']);
    exit;
}

try {
    $graficoModel = new Grafico();
    $rol_usuario = $_SESSION['user_role'] ?? 'viewer';
    
    // Obtener gráficos con layout
    $graficos = $graficoModel->getLayoutByArea($area_id, $rol_usuario);
    
    echo json_encode([
        'success' => true,
        'graficos' => $graficos,
        'total' => count($graficos)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener layout: ' . $e->getMessage()
    ]);
    
    error_log("Error obtener-layout.php: " . $e->getMessage());
}