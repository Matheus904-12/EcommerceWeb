<?php
// Teste final da SuperFrete com cURL
header('Content-Type: text/plain');

$superfrete_token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE3NTIwODE3ODAsInN1YiI6InBPWTZHVVQ1b0NTZll2TVEwT05XeHpSYUR2ajIifQ.IgnswbL1J8n3SlUDU8OFi1wHp9ILeImSD0nIa-EI6bY';

echo "=== TESTE FINAL SUPERFRETE ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

// Teste 1: SP
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

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.superfrete.com.br/v1/quote');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_sp));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $superfrete_token,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$result_sp = curl_exec($ch);
$http_code_sp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error_sp = curl_error($ch);
curl_close($ch);

echo "HTTP Code SP: $http_code_sp\n";
echo "Error SP: " . ($error_sp ?: 'Nenhum') . "\n";
echo "Resposta SP: " . ($result_sp ?: 'VAZIA') . "\n\n";

// Teste 2: MG
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

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.superfrete.com.br/v1/quote');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_mg));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $superfrete_token,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$result_mg = curl_exec($ch);
$http_code_mg = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error_mg = curl_error($ch);
curl_close($ch);

echo "HTTP Code MG: $http_code_mg\n";
echo "Error MG: " . ($error_mg ?: 'Nenhum') . "\n";
echo "Resposta MG: " . ($result_mg ?: 'VAZIA') . "\n\n";

echo "=== FIM TESTE ===\n";
?> 