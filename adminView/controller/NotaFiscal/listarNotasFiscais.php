<?php
session_start();
require_once __DIR__ . '/../../config/dbconnect.php';
require_once __DIR__ . '/BetelNotaFiscalController.php';

header('Content-Type: application/json');

// Verifica se o usuário está logado e tem permissão de administrador
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Acesso negado']);
    exit;
}

// Processa os filtros da requisição
$filters = [];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;

if (isset($_GET['order_id']) && !empty($_GET['order_id'])) {
    $filters['order_id'] = $_GET['order_id'];
}

if (isset($_GET['nfe_status']) && !empty($_GET['nfe_status'])) {
    $filters['nfe_status'] = $_GET['nfe_status'];
}

if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
    $filters['date_from'] = $_GET['date_from'];
}

if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
    $filters['date_to'] = $_GET['date_to'];
}

try {
    $notaFiscalController = new BetelNotaFiscalController($conn);
    $result = $notaFiscalController->listarNotasFiscais($filters, $page, $perPage);
    
    http_response_code(200);
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro ao listar notas fiscais: ' . $e->getMessage(),
        'details' => [
            'error_time' => date('Y-m-d H:i:s')
        ]
    ]);
}