<?php
// Teste do fallback com IP direto
header('Content-Type: text/plain');

$superfrete_token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE3NTIwODE3ODAsInN1YiI6InBPWTZHVVQ1b0NTZll2TVEwT05XeHpSYUR2ajIifQ.IgnswbL1J8n3SlUDU8OFi1wHp9ILeImSD0nIa-EI6bY';

echo "=== TESTE FALLBACK SUPERFRETE ===\n";
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

$urls = [
    'https://api.superfrete.com.br/v1/quote',
    'https://199.60.103.28/v1/quote'
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
        'Accept: application/json',
        'Host: api.superfrete.com.br'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
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