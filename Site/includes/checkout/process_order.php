<?php
// Configuration
$merchantId = 'e85b80d2-3bec-4c7b-b64f-cb24aa76f51e';
$merchantKey = '9VYU5i0FSG7JDsleOaRTkDrbqzbI14AotHcG5Yow';
$apiUrl = 'https://apisandbox.cieloecommerce.cielo.com.br/1/sales/';
$storeAddress = 'Rua Mário Bochetti 1102, Suzano, SP, 08673-021';

// Headers for Cielo API
$headers = [
    'Content-Type: application/json',
    'MerchantId: ' . $merchantId,
    'MerchantKey: ' . $merchantKey
];

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$orderId = $input['order_id'];
$amount = floatval($input['amount']) * 100; // Convert to cents
$installments = isset($input['installments']) ? intval($input['installments']) : 1;
$paymentType = $input['payment_type']; // 'CreditCard' or 'DebitCard'
$cardholderEmail = $input['cardholder_email'];
$identificationNumber = $input['identification_number'];
$saveCard = isset($input['save_card']) ? $input['save_card'] : 0;

// Database connection (adjust with your credentials)
$db = new mysqli('localhost', 'your_username', 'your_password', 'your_database');
if ($db->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco de dados']);
    exit;
}

// Handle saved card payment
if (isset($input['saved_card_id']) && !empty($input['saved_card_id'])) {
    $savedCardId = $input['saved_card_id'];
    $cvv = $input['card_cvv'];
    
    // Fetch saved card data
    $stmt = $db->prepare("SELECT card_token, card_brand FROM saved_cards WHERE id = ?");
    $stmt->bind_param('i', $savedCardId);
    $stmt->execute();
    $result = $stmt->get_result();
    $card = $result->fetch_assoc();
    $stmt->close();
    
    if (!$card) {
        echo json_encode(['success' => false, 'message' => 'Cartão salvo não encontrado']);
        exit;
    }
    
    // Prepare payment data
    $paymentData = [
        'MerchantOrderId' => $orderId,
        'Payment' => [
            'Type' => $paymentType,
            'Amount' => $amount,
            'Installments' => $paymentType === 'CreditCard' ? $installments : 1,
            'SoftDescriptor' => 'GOLDLARCRISTAIS',
            'Card' => [
                'CardToken' => $card['card_token'],
                'SecurityCode' => $cvv,
                'Brand' => $card['card_brand']
            ]
        ]
    ];
} else {
    // Tokenize new card
    $cardData = [
        'CardNumber' => $input['card_number'],
        'Holder' => $input['card_name'],
        'ExpirationDate' => $input['card_expiry'],
        'SecurityCode' => $input['card_cvv'],
        'Brand' => $input['card_brand']
    ];
    
    // Tokenize card via Cielo API
    $ch = curl_init('https://apisandbox.cieloecommerce.cielo.com.br/1/card/');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($cardData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $tokenResponse = json_decode($response, true);
    if ($httpCode !== 201 || !isset($tokenResponse['CardToken'])) {
        echo json_encode(['success' => false, 'message' => 'Erro ao tokenizar cartão: ' . ($tokenResponse['Message'] ?? 'Tente novamente')]);
        exit;
    }
    
    // Save card if requested
    if ($saveCard) {
        $stmt = $db->prepare("INSERT INTO saved_cards (user_id, card_token, last_4_digits, cardholder_name, card_brand, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $userId = 23; // Adjust: fetch from session or input
        $last4 = substr($input['card_number'], -4);
        $stmt->bind_param('issss', $userId, $tokenResponse['CardToken'], $last4, $input['card_name'], $input['card_brand']);
        $stmt->execute();
        $stmt->close();
    }
    
    // Prepare payment data
    $paymentData = [
        'MerchantOrderId' => $orderId,
        'Payment' => [
            'Type' => $paymentType,
            'Amount' => $amount,
            'Installments' => $paymentType === 'CreditCard' ? $installments : 1,
            'SoftDescriptor' => 'GOLDLARCRISTAIS',
            'Card' => [
                'CardToken' => $tokenResponse['CardToken'],
                'SecurityCode' => $input['card_cvv'],
                'Brand' => $input['card_brand']
            ]
        ]
    ];
}

// Process payment via Cielo API
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($paymentData));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$responseData = json_decode($response, true);
if ($httpCode !== 201 || !isset($responseData['Payment']['PaymentId'])) {
    echo json_encode(['success' => false, 'message' => $responseData['Payment']['ReturnMessage'] ?? 'Erro ao processar pagamento']);
    exit;
}

// Save order to database
$stmt = $db->prepare("INSERT INTO pedidos (user_id, total, subtotal, shipping, discount, payment_method, status, shipping_address, shipping_number, shipping_cep, card_last4, tracking_code, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
$userId = 23; // Adjust: fetch from session or input
$total = $input['amount'];
$subtotal = $input['amount']; // Adjust if shipping/discount applies
$shipping = 0.00;
$discount = 0.00;
$paymentMethod = $paymentType === 'CreditCard' ? 'credit_card' : 'debit_card';
$status = $responseData['Payment']['Status'] == 1 ? 'aguardando_pagamento' : 'processando';
$shippingAddress = $storeAddress; // Adjust: fetch from input
$shippingNumber = '111'; // Adjust: fetch from input
$shippingCep = $input['shipping_cep'] ?? '08673-021';
$cardLast4 = substr($input['card_number'], -4);
$trackingCode = $responseData['Payment']['PaymentId'];
$stmt->bind_param('iddddsdsssss', $userId, $total, $subtotal, $shipping, $discount, $paymentMethod, $status, $shippingAddress, $shippingNumber, $shippingCep, $cardLast4, $trackingCode);
$stmt->execute();
$stmt->close();

// Send email notification
$to = $cardholderEmail;
$subject = "Confirmação de Pagamento - Pedido $orderId";
$message = "Olá,\n\nSeu pagamento foi processado com sucesso!\n\nDetalhes do Pedido:\n- Pedido: $orderId\n- Total: R$ " . number_format($input['amount'], 2, ',', '.') . "\n- Método: " . ($paymentType === 'CreditCard' ? 'Cartão de Crédito' : 'Cartão de Débito') . "\n- Parcelas: $installments\n- Status: $status\n\nObrigado por comprar conosco!\n$storeAddress";
$headers = "From: no-reply@goldlarcristais.com.br";
mail($to, $subject, $message, $headers);

// Return success response
echo json_encode([
    'success' => true,
    'payment_id' => $responseData['Payment']['PaymentId'],
    'card_token' => $tokenResponse['CardToken'] ?? null
]);

$db->close();
?>