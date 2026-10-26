<?php
// Script de teste para SuperFrete
header('Content-Type: text/plain');

$superfrete_token = getenv('SUPERFRETE_TOKEN');
if (!$superfrete_token) {
    http_response_code(500);
    exit("Configure a variável de ambiente SUPERFRETE_TOKEN antes de executar este teste.\n");
}

echo "=== TESTE SUPERFRETE ===\n";

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