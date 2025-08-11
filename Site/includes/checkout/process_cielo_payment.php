<?php
header('Content-Type: application/json');

// Incluir arquivo de configuração do ambiente para carregar variáveis de ambiente
require_once __DIR__ . '/../../../Site/includes/config.php';

// Configurações de produção - usando variáveis de ambiente
define('CIELO_API_URL', getenv('CIELO_API_URL') ?: 'https://api.cieloecommerce.cielo.com.br/1/sales');
define('CIELO_MERCHANT_ID', getenv('CIELO_MERCHANT_ID') ?: 'e85b80d2-3bec-4c7b-b64f-cb24aa76f51e');
define('CIELO_MERCHANT_KEY', getenv('CIELO_MERCHANT_KEY') ?: 's7KwzcUMS9fkYsKgTTijA0uYc4RSyKS1QlJbhhkD');
define('CIELO_LOG_FILE', __DIR__ . '/cielo_payment_errors.log');

// Função para registrar logs
function logCieloError($message, $data = []) {
    $log = "[" . date('Y-m-d H:i:s') . "] " . $message . PHP_EOL;
    $log .= "Request Data: " . json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL;
    file_put_contents(CIELO_LOG_FILE, $log, FILE_APPEND);
}

// Verificar se é uma requisição GET para consulta de status
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];
    
    if ($action === 'check_status') {
        $merchantOrderId = $_GET['merchantOrderId'] ?? '';
        
        if (empty($merchantOrderId)) {
            echo json_encode(['success' => false, 'message' => 'MerchantOrderId não fornecido']);
            exit;
        }
        
        // Consultar status na Cielo
        $headers = [
            'Content-Type: application/json',
            'MerchantId: ' . CIELO_MERCHANT_ID,
            'MerchantKey: ' . CIELO_MERCHANT_KEY
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => CIELO_API_URL . '/' . $merchantOrderId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            $status = $data['Payment']['Status'] ?? 'Unknown';
            $statusDescription = getStatusDescription($status);
            
            echo json_encode([
                'success' => true,
                'status' => $status,
                'statusDescription' => $statusDescription,
                'data' => $data
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao consultar status']);
        }
        exit;
    }
}

// Verificar se é uma requisição POST para cancelamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'cancel_payment') {
    $paymentId = $_GET['paymentId'] ?? '';
    
    if (empty($paymentId)) {
        echo json_encode(['success' => false, 'message' => 'PaymentId não fornecido']);
        exit;
    }
    
    // Cancelar pagamento na Cielo
    $headers = [
        'Content-Type: application/json',
        'MerchantId: ' . CIELO_MERCHANT_ID,
        'MerchantKey: ' . CIELO_MERCHANT_KEY
    ];
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => CIELO_API_URL . '/' . $paymentId . '/void',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo json_encode(['success' => true, 'message' => 'Pagamento cancelado com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao cancelar pagamento']);
    }
    exit;
}

// Verificar método HTTP para criação de pagamento
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Dados de entrada inválidos');
    }

    // Adicionar MerchantOrderId se necessário
    if (empty($input['MerchantOrderId'])) {
        $input['MerchantOrderId'] = 'ORD-' . uniqid();
    }

    // Forçar captura automática
    $input['Payment']['Capture'] = true;
    
    // Remover ExternalAuthentication se não for necessário
    if (isset($input['Payment']['ExternalAuthentication']) && 
        (!isset($input['Payment']['ExternalAuthentication']['cavv']) || 
         !isset($input['Payment']['ExternalAuthentication']['xid']) || 
         !isset($input['Payment']['ExternalAuthentication']['eci']))) {
        unset($input['Payment']['ExternalAuthentication']);
    }

    // Antes de logar qualquer payload ou resposta, remova dados sensíveis
    if (isset($input['Payment']['CreditCard']['CardNumber'])) {
        $input['Payment']['CreditCard']['CardNumber'] = '****' . substr($input['Payment']['CreditCard']['CardNumber'], -4);
    }
    if (isset($input['Payment']['CreditCard']['SecurityCode'])) {
        $input['Payment']['CreditCard']['SecurityCode'] = '***';
    }

    // Headers obrigatórios
    $headers = [
        'Content-Type: application/json',
        'MerchantId: ' . CIELO_MERCHANT_ID,
        'MerchantKey: ' . CIELO_MERCHANT_KEY
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => CIELO_API_URL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($input),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 45,
        CURLOPT_HEADER => true
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Log da requisição
    logCieloError('Request Info', [
        'url' => CIELO_API_URL,
        'headers' => $headers,
        'payload' => $input
    ]);

    // Log adicional para debug
    error_log("CIELO DEBUG - Payload: " . json_encode($input, JSON_PRETTY_PRINT));

    if (curl_errno($ch)) {
        throw new Exception('CURL Error: ' . curl_error($ch), 500);
    }

    // Processar resposta
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $body = substr($response, $headerSize);
    $responseData = json_decode($body, true);

    curl_close($ch);

    // Log da resposta
    logCieloError('Cielo Response', [
        'http_code' => $httpCode,
        'response' => $responseData,
        'return_code' => $responseData['Payment']['ReturnCode'] ?? 'N/A',
        'return_message' => $responseData['Payment']['ReturnMessage'] ?? 'N/A'
    ]);

    // Log adicional para debug
    error_log("CIELO DEBUG - Response: " . json_encode($responseData, JSON_PRETTY_PRINT));

    // Verificar erros da API
    if ($httpCode >= 400) {
        $errorMsg = 'Erro na API Cielo';
        if (isset($responseData[0]['Message'])) {
            $errorMsg = $responseData[0]['Message'];
        } elseif (isset($responseData['message'])) {
            $errorMsg = $responseData['message'];
        } elseif (isset($responseData['Payment']['ReturnMessage'])) {
            $errorMsg = $responseData['Payment']['ReturnMessage'];
        }
        throw new Exception($errorMsg, $httpCode);
    }

    // Verificar se o pagamento foi criado
    if (empty($responseData['Payment']['PaymentId'])) {
        throw new Exception('Resposta inválida da Cielo', 500);
    }

    // Log adicional para debug
    error_log("CIELO DEBUG - PaymentId: " . ($responseData['Payment']['PaymentId'] ?? 'N/A'));

    // Verificar códigos de retorno específicos
    if (isset($responseData['Payment']['ReturnCode'])) {
        $returnCode = $responseData['Payment']['ReturnCode'];
        $returnMessage = $responseData['Payment']['ReturnMessage'];

        $errorMap = [
            '05' => 'Transação não autorizada',
            '57' => 'Transação não permitida para este cartão',
            '70' => 'Problema no processamento',
            '77' => 'Transação cancelada',
            '96' => 'Falha no sistema'
        ];

        // Códigos de sucesso (incluindo AI que indica transação sem 3DS)
        $successCodes = ['00', '000', 'AI'];

        if (isset($errorMap[$returnCode])) {
            throw new Exception($errorMap[$returnCode] . " ($returnCode)", 400);
        }

        if (!in_array($returnCode, $successCodes)) {
            throw new Exception("Transação recusada: $returnMessage ($returnCode)", 400);
        }
    }

    // Ao retornar dados para salvar cartão, só envie os 4 últimos dígitos, nome, validade e PaymentId/token seguro
    $responseData['Payment']['CreditCard']['CardNumber'] = '****' . substr($responseData['Payment']['CreditCard']['CardNumber'], -4);
    $responseData['Payment']['CreditCard']['SecurityCode'] = '***';

    echo json_encode([
        'success' => true,
        'payment' => $responseData['Payment']
    ]);
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);

    logCieloError($e->getMessage(), [
        'input' => $input ?? null,
        'trace' => $e->getTraceAsString()
    ]);

    // Log adicional para debug
    error_log("CIELO DEBUG - Exception: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
}

// Função para obter descrição do status
function getStatusDescription($status) {
    $statusMap = [
        0 => 'Não finalizada',
        1 => 'Autorizada',
        2 => 'Pagamento confirmado',
        3 => 'Negada',
        10 => 'Em autenticação',
        11 => 'Cancelada',
        12 => 'Em cancelamento',
        13 => 'Abortada',
        20 => 'Agendada'
    ];
    
    return $statusMap[$status] ?? 'Status desconhecido';
}
?>