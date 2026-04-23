<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Models\Usuario;

AuthMiddleware::handle();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ✅ PROTECCIÓN CSRF
    csrf_validate();

    $tema = $_POST['tema'] ?? 'light';

    // Validar tema
    if (!in_array($tema, ['light', 'dark'])) {
        echo json_encode(['success' => false, 'message' => 'Tema inválido']);
        exit;
    }

    $user = getCurrentUser();
    $usuarioModel = new Usuario();

    if ($usuarioModel->update($user['id'], ['tema' => $tema])) {
        echo json_encode(['success' => true, 'tema' => $tema]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar preferencia']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
