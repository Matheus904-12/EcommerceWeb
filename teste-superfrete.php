<?php
// Script de teste para SuperFrete
header('Content-Type: text/plain');

$superfrete_token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE3NTIwODE3ODAsInN1YiI6InBPWTZHVVQ1b0NTZll2TVEwT05XeHpSYUR2ajIifQ.IgnswbL1J8n3SlUDU8OFi1wHp9ILeImSD0nIa-EI6bY';

echo "=== TESTE SUPERFRETE ===\n";
echo "Token: " . substr($superfrete_token, 0, 50) . "...\n\n";

// Teste 1: SP (que funciona)
echo "TESTE 1 - SP (CEP 01310-100):\n";
$payload_sp = [
    'from' => '08673021',
    'to' => '01310100',
    'weight' => 4,
    'width' => 40,
    'height' => 35,
    'length' => 40,
    'insurance_value' => 9,
    'quantity' => 1
];

echo "Payload: " . json_encode($payload_sp) . "\n";

$opts = [
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\nAuthorization: Bearer $superfrete_token\r\nAccept: application/json\r\n",
        'content' => json_encode($payload_sp),
        'timeout' => 15
    ]
];

$context = stream_context_create($opts);
$result_sp = @file_get_contents('https://api.superfrete.com.br/v1/quote', false, $context);

echo "Resposta SP: " . ($result_sp ?: 'VAZIA') . "\n\n";

// Teste 2: MG (que não funciona)
echo "TESTE 2 - MG (CEP 38405-140):\n";
$payload_mg = [
    'from' => '08673021',
    'to' => '38405140',
    'weight' => 4,
    'width' => 40,
    'height' => 35,
    'length' => 40,
    'insurance_value' => 9,
    'quantity' => 1
];

echo "Payload: " . json_encode($payload_mg) . "\n";

$opts['http']['content'] = json_encode($payload_mg);
$context = stream_context_create($opts);
$result_mg = @file_get_contents('https://api.superfrete.com.br/v1/quote', false, $context);

echo "Resposta MG: " . ($result_mg ?: 'VAZIA') . "\n\n";

// Teste 3: Verificar headers da resposta
echo "TESTE 3 - Verificar headers:\n";
$headers = $http_response_header ?? [];
foreach ($headers as $header) {
    echo $header . "\n";
}

echo "\n=== FIM TESTE ===\n";
?> 