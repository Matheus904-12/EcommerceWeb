<?php
// Autenticação Melhor Envio - Client Credentials (PRODUÇÃO)
header('Content-Type: application/json');

$client_id = '19270';
$client_secret = '8hMR7mJSfgRbA8ry1xeEOfTriFE8FxRQ93gZxt2O';

$url = 'https://api.melhorenvio.com.br/oauth/token';

$data = [
    'grant_type' => 'client_credentials',
    'client_id' => $client_id,
    'client_secret' => $client_secret
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json'
]);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($http_code === 200 && $response) {
    echo $response;
} else {
    echo json_encode([
        'error' => 'Falha ao autenticar com Melhor Envio (produção)',
        'http_code' => $http_code,
        'curl_error' => $error,
        'response' => $response
    ]);
} 