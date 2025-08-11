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

// Verifica se o método da requisição é GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
    exit;
}

// Verifica se a chave da NFe foi fornecida
$nfeKey = isset($_GET['nfe_key']) ? $_GET['nfe_key'] : null;

if (!$nfeKey) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Chave da NFe não fornecida']);
    exit;
}

try {
    $notaFiscalController = new BetelNotaFiscalController($conn);
    $result = $notaFiscalController->obterPdfNotaFiscal($nfeKey);
    
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'data' => [
            'pdf_url' => $result
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro ao obter PDF da nota fiscal: ' . $e->getMessage(),
        'details' => [
            'nfe_key' => $nfeKey,
            'error_time' => date('Y-m-d H:i:s')
        ]
    ]);
}