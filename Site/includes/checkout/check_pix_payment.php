<?php
/**
 * Verificar status de pagamento PIX
 * 
 * Este script verifica o status de um pagamento PIX com base no PaymentId do Cielo
 * e retorna o resultado em formato JSON compatível com checkout.js.
 * 
 * @return JSON {"status": "aprovado|pendente|expirado", "message": "mensagem descritiva"}
 */

// Configurações iniciais
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/pix_payment.log');

session_start();

// Incluir arquivo de configuração do ambiente para carregar variáveis de ambiente
require_once __DIR__ . '/../../../Site/includes/config.php';

// Função para registrar logs
function logError($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    $logDir = __DIR__ . '/../../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    file_put_contents(__DIR__ . '/../../logs/pix_payment.log', $logMessage, FILE_APPEND);
}

// Função para retornar respostas JSON
function jsonResponse($status, $message = '', $data = []) {
    $response = ['status' => $status, 'message' => $message];
    if (!empty($data)) {
        $response = array_merge($response, $data);
    }
    echo json_encode($response);
    exit;
}

// Cielo API Configuration - usando variáveis de ambiente
define('CIELO_MERCHANT_ID', getenv('CIELO_MERCHANT_ID') ?: 'e85b80d2-3bec-4c7b-b64f-cb24aa76f51e');
define('CIELO_MERCHANT_KEY', getenv('CIELO_MERCHANT_KEY') ?: '9VYU5i0FSG7JDsIeOaRTkDrbqzbI14AotHcG5Yow');
define('CIELO_API_URL', getenv('CIELO_API_URL') ?: 'https://api.cieloecommerce.cielo.com.br/');
define('CIELO_SANDBOX_URL', getenv('CIELO_SANDBOX_URL') ?: 'https://apisandbox.cieloecommerce.cielo.com.br/');
define('CIELO_ENVIRONMENT', getenv('CIELO_ENVIRONMENT') ?: 'sandbox');

// Função para consultar Cielo API
function cieloApiRequest($endpoint, $method = 'GET') {
    $url = (CIELO_ENVIRONMENT === 'sandbox' ? CIELO_SANDBOX_URL : CIELO_API_URL) . $endpoint;
    $headers = [
        'Content-Type: application/json',
        'MerchantId: ' . CIELO_MERCHANT_ID,
        'MerchantKey: ' . CIELO_MERCHANT_KEY
    ];

    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Set to true in production
        if ($method === 'GET') {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            logError("Cielo API error: $error");
            return ['httpCode' => 500, 'response' => ['Message' => 'Erro na requisição: ' . $error]];
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            logError("Cielo API JSON decode error: " . json_last_error_msg());
            return ['httpCode' => 500, 'response' => ['Message' => 'Erro ao decodificar resposta']];
        }

        return ['httpCode' => $httpCode, 'response' => $decoded];
    } catch (Exception $e) {
        logError("Cielo API request failed: " . $e->getMessage());
        return ['httpCode' => 500, 'response' => ['Message' => 'Erro na requisição: ' . $e->getMessage()]];
    }
}

// Verificar método e parâmetros
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Método não permitido');
}

$input = json_decode(file_get_contents('php://input'), true);
$paymentId = $input['payment_id'] ?? '';

if (empty($paymentId)) {
    jsonResponse('error', 'ID do pagamento não fornecido');
}

// Validar formato do PaymentId (UUID do Cielo)
if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $paymentId)) {
    jsonResponse('error', 'Formato de ID de pagamento inválido');
}

try {
    // Conectar ao banco de dados
    require_once '../../../adminView/config/dbconnect.php';
    if (!$conn) {
        logError("Database connection failed: " . mysqli_connect_error());
        jsonResponse('error', 'Erro de conexão com o banco de dados');
    }

    // Consultar status do pedido
    $query = "SELECT status, payment_status, created_at FROM orders WHERE payment_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $paymentId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$order) {
        logError("Pagamento não encontrado: $paymentId");
        jsonResponse('error', 'Pagamento não encontrado');
    }

    // Mapear status
    if ($order['payment_status'] === 'aprovado') {
        jsonResponse('aprovado', 'Pagamento confirmado com sucesso');
    } elseif ($order['payment_status'] === 'rejeitado' || $order['payment_status'] === 'expirado') {
        jsonResponse('expirado', 'Pagamento expirado ou rejeitado');
    } else {
        // Verificar tempo limite para PIX (30 minutos)
        $createdAt = strtotime($order['created_at']);
        $now = time();
        if (($now - $createdAt) > 1800) {
            $updateQuery = "UPDATE orders SET payment_status = 'expirado', status = 'cancelado' WHERE payment_id = ?";
            $updateStmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($updateStmt, 's', $paymentId);
            mysqli_stmt_execute($updateStmt);
            mysqli_stmt_close($updateStmt);
            logError("Pagamento PIX expirado por tempo: $paymentId");
            jsonResponse('expirado', 'Tempo para pagamento expirado');
        }

        // Consultar Cielo API para status atual
        $response = cieloApiRequest("1/sales/$paymentId");
        if ($response['httpCode'] == 200 && isset($response['response']['Payment']['Status'])) {
            $cieloStatus = $response['response']['Payment']['Status'];
            $statusMap = [
                2 => 'aprovado', // Payment Confirmed
                0 => 'pendente', // Not Finished
                10 => 'expirado', // Voided
                12 => 'pendente' // Pending
            ];
            $newStatus = $statusMap[$cieloStatus] ?? 'pendente';

            if ($newStatus !== $order['payment_status']) {
                $updateQuery = "UPDATE orders SET payment_status = ?, status = ? WHERE payment_id = ?";
                $newOrderStatus = $newStatus === 'aprovado' ? 'processando' : ($newStatus === 'expirado' ? 'cancelado' : 'aguardando_pagamento');
                $updateStmt = mysqli_prepare($conn, $updateQuery);
                mysqli_stmt_bind_param($updateStmt, 'sss', $newStatus, $newOrderStatus, $paymentId);
                mysqli_stmt_execute($updateStmt);
                mysqli_stmt_close($updateStmt);
            }

            if ($newStatus === 'aprovado') {
                jsonResponse('aprovado', 'Pagamento confirmado com sucesso');
            } elseif ($newStatus === 'expirado') {
                jsonResponse('expirado', 'Pagamento expirado');
            } else {
                jsonResponse('pendente', 'Aguardando confirmação de pagamento');
            }
        } else {
            logError("Erro ao consultar Cielo API para pagamento $paymentId: " . ($response['response']['Message'] ?? 'Erro desconhecido'));
            jsonResponse('error', 'Erro ao verificar status do pagamento');
        }
    }

    mysqli_close($conn);
} catch (Exception $e) {
    logError("Erro geral: " . $e->getMessage());
    jsonResponse('error', 'Erro ao processar a solicitação: ' . $e->getMessage());
}
?>