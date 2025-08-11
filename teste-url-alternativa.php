<?php
// Teste de URL alternativa da SuperFrete
header('Content-Type: text/plain');

$superfrete_token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE3NTIwODE3ODAsInN1YiI6InBPWTZHVVQ1b0NTZll2TVEwT05XeHpSYUR2ajIifQ.IgnswbL1J8n3SlUDU8OFi1wHp9ILeImSD0nIa-EI6bY';

echo "=== TESTE URL ALTERNATIVA SUPERFRETE ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

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

// Possíveis URLs da API
$urls = [
    'https://api.superfrete.com.br/v1/quote',
    'https://api.superfrete.com/v1/quote',
    'https://superfrete.com.br/api/v1/quote',
    'https://superfrete.com/api/v1/quote',
    'https://api.superfrete.com.br/quote',
    'https://api.superfrete.com/quote',
    'https://superfrete.com.br/api/quote',
    'https://superfrete.com/api/quote'
];

foreach ($urls as $index => $url) {
    echo "TESTE " . ($index + 1) . " - URL: $url\n";
    
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
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "HTTP Code: $http_code\n";
    echo "Error: " . ($error ?: 'Nenhum') . "\n";
    echo "Resposta: " . ($result ? substr($result, 0, 200) : 'VAZIA') . "\n";
    
    if ($result !== false && $http_code === 200) {
        echo "✅ SUCESSO!\n";
        break;
    } else {
        echo "❌ FALHOU\n";
    }
    echo "---\n\n";
}

echo "=== FIM TESTE ===\n";
?> 