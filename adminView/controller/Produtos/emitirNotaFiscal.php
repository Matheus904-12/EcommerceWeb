<?php
session_start();
require_once __DIR__ . '/../../config/dbconnect.php';
require_once __DIR__ . '/../../controller/NotaFiscal/BlingNotaFiscalController.php';

header('Content-Type: application/json');

// Verifica se o usuário está logado como administrador
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Acesso negado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
    exit;
}

$orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : null;

if (!$orderId) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID do pedido não fornecido']);
    exit;
}

try {
    $notaFiscalController = new BlingNotaFiscalController($conn);
    $result = $notaFiscalController->emitirNotaFiscal($orderId);
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Nota fiscal emitida com sucesso',
        'data' => $result
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro ao emitir nota fiscal: ' . $e->getMessage(),
        'details' => [
            'order_id' => $orderId,
            'error_time' => date('Y-m-d H:i:s')
        ]
    ]);
}