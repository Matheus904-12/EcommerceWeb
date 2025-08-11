<?php
// Teste simples para verificar se a busca de CEP está funcionando
header('Content-Type: application/json');

function buscarCepViaBackend($cep) {
    $cep = preg_replace('/[^0-9]/', '', $cep);
    
    if (strlen($cep) !== 8) {
        return ['erro' => true, 'message' => 'CEP inválido'];
    }
    
    $url = "https://viacep.com.br/ws/{$cep}/json/";
    
    // Primeiro tentar com cURL
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'CristaisGoldLar/1.0');
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        if (!$curl_error && $http_code === 200) {
            $data = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $data;
            }
        }
    }
    
    // Se cURL falhou, tentar com file_get_contents
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: CristaisGoldLar/1.0',
                'Accept: application/json'
            ],
            'timeout' => 10
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        return ['erro' => true, 'message' => 'Erro de conexão com ViaCEP'];
    }
    
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['erro' => true, 'message' => 'Resposta inválida do ViaCEP'];
    }
    
    return $data;
}

// Teste com CEP de São Paulo
$cep_teste = '08501300';
$resultado = buscarCepViaBackend($cep_teste);

echo json_encode($resultado, JSON_PRETTY_PRINT);
?> 