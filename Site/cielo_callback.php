<?php
// cielo_callback.php
session_start();
require_once __DIR__ . '/adminView/config/dbconnect.php';

// Configurações de exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Recupera dados da transação
$paymentId = $_GET['PaymentId'] ?? null;
$trackingCode = $_SESSION['order_tracking_code'] ?? null;

if ($paymentId && $trackingCode) {
    try {
        // Atualizar o pedido
        $query = "UPDATE orders SET 
                  payment_id = ?, 
                  status = 'aprovado',
                  updated_at = NOW()
                  WHERE tracking_code = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $paymentId, $trackingCode);
        $stmt->execute();
        
        // Limpar sessão
        unset($_SESSION['cielo_payment_id']);
        unset($_SESSION['order_tracking_code']);
        
        // Redirecionar para sucesso
        header('Location: checkout_success.php?tracking=' . $trackingCode);
        exit;
        
    } catch (Exception $e) {
        error_log('Erro no callback: ' . $e->getMessage());
        header('Location: checkout_error.php?code=db_error');
        exit;
    }
}

// Redirecionamento genérico para erro
header('Location: checkout_error.php?code=invalid_callback');
exit;