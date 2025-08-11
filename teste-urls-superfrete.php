<?php
// Teste de diferentes URLs da SuperFrete
header('Content-Type: text/plain');

$superfrete_token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE3NTIwODE3ODAsInN1YiI6InBPWTZHVVQ1b0NTZll2TVEwT05XeHpSYUR2ajIifQ.IgnswbL1J8n3SlUDU8OFi1wHp9ILeImSD0nIa-EI6bY';

$urls = [
    'https://api.superfrete.com.br/v1/quote',
    'https://api.superfrete.com/v1/quote',
    'https://superfrete.com.br/api/v1/quote',
    'https://superfrete.com/api/v1/quote'
];

$payload = [
    'from' => '08673021',
    'to' => '38405140',
    'weight' => 4,
    'width' => 40,
    'height' => 35,
    'length' => 40,
    'insurance_value' => 9,
    'quantity' => 1
];

echo "=== TESTE DE URLs SUPERFRETE ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

foreach ($urls as $url) {
    echo "Testando: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $superfrete_token,
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "HTTP Code: $http_code\n";
    echo "Error: " . ($error ?: 'Nenhum') . "\n";
    echo "Response: " . ($response ? substr($response, 0, 200) : 'VAZIA') . "\n";
    echo "---\n\n";
}

echo "=== FIM TESTE ===\n";
?> 