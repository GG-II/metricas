<?php
/**
 * API: Obtener métricas de un área específica
 * GET /api/get-area.php?area=1&periodo=2026-4
 */

require_once '../config.php';

// Solo permitir requests AJAX
header('Content-Type: application/json');

// Verificar que el usuario esté logueado
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'No autenticado'
    ]);
    exit;
}

try {
    // Obtener parámetros
    $area_id = isset($_GET['area']) ? (int)$_GET['area'] : null;
    $periodo_str = isset($_GET['periodo']) ? $_GET['periodo'] : getCurrentPeriod();
    
    if (!$area_id) {
        throw new Exception('Área no especificada');
    }
    
    // Separar ejercicio y período
    list($ejercicio, $periodo_mes) = explode('-', $periodo_str);
    
    $db = getDB();
    
    // Obtener el período
    $stmt = $db->prepare("SELECT id FROM periodos WHERE ejercicio = ? AND periodo = ?");
    $stmt->execute([$ejercicio, $periodo_mes]);
    $periodo = $stmt->fetch();
    
    if (!$periodo) {
        throw new Exception('Período no encontrado');
    }
    
    $periodo_id = $periodo['id'];
    
    // Obtener métricas del área con sus valores
    $stmt = $db->prepare("
        SELECT 
            m.id,
            m.nombre,
            m.slug,
            m.tipo_valor,
            m.unidad,
            m.tipo_grafico,
            m.orden,
            m.grupo,
            COALESCE(vm.valor_numero, vm.valor_decimal) as valor,
            vm.nota
        FROM metricas m
        LEFT JOIN valores_metricas vm ON m.id = vm.metrica_id AND vm.periodo_id = ?
        WHERE m.area_id = ? AND m.activo = 1
        ORDER BY m.orden
    ");
    
    $stmt->execute([$periodo_id, $area_id]);
    $metricas = $stmt->fetchAll();
    
    // Formatear valores según tipo
    foreach ($metricas as &$metrica) {
        if ($metrica['valor'] !== null) {
            if ($metrica['tipo_valor'] === 'porcentaje' || $metrica['tipo_valor'] === 'decimal') {
                // Decimales: mostrar 1 decimal
                $metrica['valor'] = number_format($metrica['valor'], 1);
            } else {
                // Números enteros: sin decimales
                $metrica['valor'] = (int)$metrica['valor'];
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'metricas' => $metricas,
        'area_id' => $area_id,
        'periodo_id' => $periodo_id
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}