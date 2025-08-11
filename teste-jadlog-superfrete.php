.frete-option-card.jadlog-option {
    border: 1px solid #ff6600;
    background-color: #fff9f5;
}

.jadlog-selo {
    background: #ff6600;
    color: white;
    font-weight: bold;
    font-size: 0.85em;
    padding: 2px 10px;
    border-radius: 10px;
    margin-bottom: 7px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}<?php
// Teste da integração Jadlog com SuperFrete
header('Content-Type: text/plain');

// Token da Jadlog - Substitua pelo token real da sua conta
$jadlog_token = 'eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJqYWRsb2dfdG9rZW4iLCJqdGkiOiIzNzgwNDAxODAwMDE1NiJ9.hTZMgwJhZMCbXQnwvlxLiU6tJGh0q7LRXEJtQtjYS8A';

// Token do SuperFrete
$superfrete_token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE3NTIyMDY1OTksInN1YiI6InBPWTZHVVQ1b0NTZll2TVEwT05XeHpSYUR2ajIifQ.Fg45Uc1QopBlkjfTmk0jxH8AnpVwdGl3iP2BjkTdp60';

echo "=== TESTE INTEGRAÇÃO JADLOG + SUPERFRETE ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

// Dados de teste
$cep_origem = '08690265';
$cep_destino = '01310100'; // São Paulo
$peso = 2;
$comprimento = 30;
$largura = 30;
$altura = 30;
$valor_mercadoria = 100;

// 1. Teste direto com a API da Jadlog
echo "1. TESTE DIRETO COM API JADLOG:\n";

$payload_jadlog = [
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

echo "Payload Jadlog: " . json_encode($payload_jadlog, JSON_PRETTY_PRINT) . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.jadlog.com.br/embarcador/api/frete/valor');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_jadlog));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $jadlog_token
]);

$response_jadlog = curl_exec($ch);
$http_code_jadlog = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error_jadlog = curl_error($ch);
curl_close($ch);

echo "HTTP Code Jadlog: $http_code_jadlog\n";
echo "Error Jadlog: " . ($error_jadlog ?: 'Nenhum') . "\n";
echo "Resposta Jadlog: " . ($response_jadlog ?: 'VAZIA') . "\n\n";

// 2. Teste com a API do SuperFrete
echo "2. TESTE COM API SUPERFRETE:\n";

$payload_superfrete = [
    'from' => ['postal_code' => $cep_origem],
    'to' => ['postal_code' => $cep_destino],
    'services' => "1,2,17", // PAC, SEDEX e Jadlog
    'options' => [
        'own_hand' => false,
        'receipt' => false,
        'insurance_value' => 0,
        'use_insurance_value' => false
    ],
    'package' => [
        'weight' => $peso,
        'width' => $largura,
        'height' => $altura,
        'length' => $comprimento
    ],
    'value' => $valor_mercadoria
];

echo "Payload SuperFrete: " . json_encode($payload_superfrete, JSON_PRETTY_PRINT) . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.superfrete.com/api/v0/calculator');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_superfrete));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $superfrete_token,
    'User-Agent: CristaisGoldLar ([cristaisgoldlar@outlook.com])'
]);

$response_superfrete = curl_exec($ch);
$http_code_superfrete = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error_superfrete = curl_error($ch);
curl_close($ch);

echo "HTTP Code SuperFrete: $http_code_superfrete\n";
echo "Error SuperFrete: " . ($error_superfrete ?: 'Nenhum') . "\n";
echo "Resposta SuperFrete: " . ($response_superfrete ?: 'VAZIA') . "\n\n";

// 3. Teste com o endpoint local (calcular-frete.php)
echo "3. TESTE COM ENDPOINT LOCAL (calcular-frete.php):\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/public_html/Site/includes/checkout/calcular-frete.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_superfrete));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response_local = curl_exec($ch);
$http_code_local = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error_local = curl_error($ch);
curl_close($ch);

echo "HTTP Code Local: $http_code_local\n";
echo "Error Local: " . ($error_local ?: 'Nenhum') . "\n";
echo "Resposta Local: " . ($response_local ?: 'VAZIA') . "\n\n";

// Analisar resposta local para verificar se a Jadlog está incluída
if ($response_local) {
    $data = json_decode($response_local, true);
    if (is_array($data)) {
        echo "Opções de frete encontradas: " . count($data) . "\n";
        
        $jadlog_found = false;
        foreach ($data as $option) {
            if (isset($option['id']) && $option['id'] === 'jadlog' || 
                isset($option['name']) && strpos($option['name'], 'JADLOG') !== false) {
                $jadlog_found = true;
                echo "✅ JADLOG encontrada na resposta!\n";
                echo "Nome: " . $option['name'] . "\n";
                echo "Preço: R$ " . $option['price'] . "\n";
                echo "Prazo: " . $option['delivery_time'] . " dias\n";
                break;
            }
        }
        
        if (!$jadlog_found) {
            echo "❌ JADLOG NÃO encontrada na resposta!\n";
        }
    } else {
        echo "Erro ao decodificar resposta JSON\n";
    }
}

echo "\n=== FIM TESTE ===\n";
?>