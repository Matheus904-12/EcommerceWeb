<?php
// Teste simples da API Jadlog com token atualizado
header('Content-Type: text/plain');

// Token da Jadlog - Substitua pelo token real da sua conta
$jadlog_token = 'SEU_TOKEN_ATUALIZADO_AQUI'; // Substitua por um token válido

// Dados de teste
$cep_origem = '08690265';
$cep_destino = '01310100'; // São Paulo
$peso = 2;
$valor_mercadoria = 100;

// Payload para a API da Jadlog
$payload = [
    'frete' => [
        [
            'cepori' => $cep_origem,
            'cepdes' => $cep_destino,
            'peso' => $peso,
            'cnpj' => '37804018000156', // CNPJ da loja
            'modalidade' => 3, // .PACKAGE (Rodoviário)
            'tpentrega' => 'D', // Entrega domiciliar
            'ipseguro' => 'N', // Seguro normal
            'vldeclarado' => $valor_mercadoria
        ]
    ]
];

echo "Payload Jadlog: " . json_encode($payload, JSON_PRETTY_PRINT) . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.jadlog.com.br/embarcador/api/frete/valor');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $jadlog_token
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $http_code\n";
echo "Error: " . ($error ?: 'Nenhum') . "\n";
echo "Resposta: " . ($response ?: 'VAZIA') . "\n";

// Decodificar a resposta se for JSON
if ($response) {
    $data = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "\nResposta decodificada:\n";
        print_r($data);
    } else {
        echo "\nErro ao decodificar JSON: " . json_last_error_msg() . "\n";
    }
}
?>