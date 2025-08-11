<?php
// Teste detalhado da SuperFrete
header('Content-Type: text/plain');

$superfrete_token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE3NTIwODE3ODAsInN1YiI6InBPWTZHVVQ1b0NTZll2TVEwT05XeHpSYUR2ajIifQ.IgnswbL1J8n3SlUDU8OFi1wHp9ILeImSD0nIa-EI6bY';

echo "=== TESTE DETALHADO SUPERFRETE ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n";
echo "Token: " . substr($superfrete_token, 0, 50) . "...\n\n";

// Teste com cURL para mais detalhes
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

echo "Payload: " . json_encode($payload) . "\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.superfrete.com.br/v1/quote');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $superfrete_token,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: " . $http_code . "\n";
echo "cURL Error: " . ($error ?: 'Nenhum') . "\n";
echo "Response completa:\n" . $response . "\n\n";

// Separar headers do body
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $header_size);
$body = substr($response, $header_size);

echo "Headers:\n" . $headers . "\n";
echo "Body:\n" . $body . "\n";

echo "=== FIM TESTE ===\n";
?> 