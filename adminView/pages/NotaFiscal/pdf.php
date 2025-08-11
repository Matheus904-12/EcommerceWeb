<?php
require_once '../../config/dbconnect.php';
require_once '../../controller/NotaFiscal/NotaFiscalController.php';

// Inicializa o controlador de notas fiscais
$notaFiscalController = new NotaFiscalController($conn);

// Verifica se foi informado o ID do pedido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$orderId = intval($_GET['id']);

try {
    // Obtém o PDF da nota fiscal
    $pdfUrl = $notaFiscalController->obterPdfNotaFiscal($orderId);
    
    // Redireciona para a URL do PDF
    header("Location: {$pdfUrl}");
    exit;
} catch (Exception $e) {
    // Em caso de erro, redireciona para a página de consulta com mensagem de erro
    header("Location: consultar.php?id={$orderId}&error=" . urlencode($e->getMessage()));
    exit;
}
?>