<?php
header('Content-Type: application/json');

// Função para buscar CEP via backend (evita CORS)
function buscarCepViaBackend($cep) {
    $cep = preg_replace('/[^0-9]/', '', $cep);
    
    if (strlen($cep) !== 8) {
        return ['erro' => true, 'message' => 'CEP inválido'];
    }
    
    // Tentar múltiplas APIs de CEP
    $apis = [
        "https://viacep.com.br/ws/{$cep}/json/",
        "https://cep.la/{$cep}",
        "https://brasilapi.com.br/api/cep/v1/{$cep}"
    ];
    
    foreach ($apis as $url) {
        $result = tentarBuscarCep($url);
        if ($result && !isset($result['erro'])) {
            return $result;
        }
    }
    
    // Se todas as APIs falharam, retornar dados básicos baseados no CEP
    return gerarDadosCepFallback($cep);
}

function tentarBuscarCep($url) {
    // Primeiro tentar com cURL
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'CristaisGoldLar/1.0');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200 && $response) {
            $data = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE && $data) {
                // Normalizar dados para formato padrão
                return normalizarDadosCep($data, $url);
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
            'timeout' => 5
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE && $data) {
            return normalizarDadosCep($data, $url);
        }
    }
    
    return null;
}

function normalizarDadosCep($data, $url) {
    // Normalizar dados de diferentes APIs para formato padrão
    if (strpos($url, 'viacep.com.br') !== false) {
        if (isset($data['erro']) && $data['erro']) {
            return ['erro' => true, 'message' => 'CEP não encontrado'];
        }
        return [
            'logradouro' => $data['logradouro'] ?? '',
            'bairro' => $data['bairro'] ?? '',
            'localidade' => $data['localidade'] ?? '',
            'uf' => $data['uf'] ?? '',
            'cep' => $data['cep'] ?? ''
        ];
    } elseif (strpos($url, 'brasilapi.com.br') !== false) {
        return [
            'logradouro' => $data['street'] ?? '',
            'bairro' => $data['neighborhood'] ?? '',
            'localidade' => $data['city'] ?? '',
            'uf' => $data['state'] ?? '',
            'cep' => $data['cep'] ?? ''
        ];
    } elseif (strpos($url, 'cep.la') !== false) {
        return [
            'logradouro' => $data['logradouro'] ?? '',
            'bairro' => $data['bairro'] ?? '',
            'localidade' => $data['cidade'] ?? '',
            'uf' => $data['estado'] ?? '',
            'cep' => $data['cep'] ?? ''
        ];
    }
    
    return $data;
}

function gerarDadosCepFallback($cep) {
    // Dados básicos baseados no CEP quando APIs externas falham
    $cep_limpo = preg_replace('/[^0-9]/', '', $cep);
    
    // Mapeamento básico de CEPs para estados
    $estados = [
        '01' => ['uf' => 'SP', 'cidade' => 'São Paulo'],
        '02' => ['uf' => 'SP', 'cidade' => 'São Paulo'],
        '03' => ['uf' => 'SP', 'cidade' => 'São Paulo'],
        '04' => ['uf' => 'SP', 'cidade' => 'São Paulo'],
        '05' => ['uf' => 'SP', 'cidade' => 'São Paulo'],
        '06' => ['uf' => 'SP', 'cidade' => 'São Paulo'],
        '07' => ['uf' => 'SP', 'cidade' => 'São Paulo'],
        '08' => ['uf' => 'SP', 'cidade' => 'São Paulo'],
        '09' => ['uf' => 'SP', 'cidade' => 'São Paulo'],
        '10' => ['uf' => 'SP', 'cidade' => 'São Paulo'],
        '11' => ['uf' => 'SP', 'cidade' => 'São Paulo'],
        '12' => ['uf' => 'SP', 'cidade' => 'São Paulo'],
        '13' => ['uf' => 'SP', 'cidade' => 'São Paulo'],
        '14' => ['uf' => 'SP', 'cidade' => 'São Paulo'],
        '15' => ['uf' => 'SP', 'cidade' => 'São Paulo'],
        '16' => ['uf' => 'SP', 'cidade' => 'São Paulo'],
        '17' => ['uf' => 'SP', 'cidade' => 'São Paulo'],
        '18' => ['uf' => 'SP', 'cidade' => 'São Paulo'],
        '19' => ['uf' => 'SP', 'cidade' => 'São Paulo'],
        '20' => ['uf' => 'RJ', 'cidade' => 'Rio de Janeiro'],
        '21' => ['uf' => 'RJ', 'cidade' => 'Rio de Janeiro'],
        '22' => ['uf' => 'RJ', 'cidade' => 'Rio de Janeiro'],
        '23' => ['uf' => 'RJ', 'cidade' => 'Rio de Janeiro'],
        '24' => ['uf' => 'RJ', 'cidade' => 'Rio de Janeiro'],
        '25' => ['uf' => 'RJ', 'cidade' => 'Rio de Janeiro'],
        '26' => ['uf' => 'RJ', 'cidade' => 'Rio de Janeiro'],
        '27' => ['uf' => 'RJ', 'cidade' => 'Rio de Janeiro'],
        '28' => ['uf' => 'RJ', 'cidade' => 'Rio de Janeiro'],
        '29' => ['uf' => 'RJ', 'cidade' => 'Rio de Janeiro'],
        '30' => ['uf' => 'MG', 'cidade' => 'Belo Horizonte'],
        '31' => ['uf' => 'MG', 'cidade' => 'Belo Horizonte'],
        '32' => ['uf' => 'MG', 'cidade' => 'Belo Horizonte'],
        '33' => ['uf' => 'MG', 'cidade' => 'Belo Horizonte'],
        '34' => ['uf' => 'MG', 'cidade' => 'Belo Horizonte'],
        '35' => ['uf' => 'MG', 'cidade' => 'Belo Horizonte'],
        '36' => ['uf' => 'MG', 'cidade' => 'Belo Horizonte'],
        '37' => ['uf' => 'MG', 'cidade' => 'Belo Horizonte'],
        '38' => ['uf' => 'MG', 'cidade' => 'Belo Horizonte'],
        '39' => ['uf' => 'MG', 'cidade' => 'Belo Horizonte'],
        '40' => ['uf' => 'BA', 'cidade' => 'Salvador'],
        '41' => ['uf' => 'PR', 'cidade' => 'Curitiba'],
        '42' => ['uf' => 'PR', 'cidade' => 'Curitiba'],
        '43' => ['uf' => 'PR', 'cidade' => 'Curitiba'],
        '44' => ['uf' => 'PR', 'cidade' => 'Curitiba'],
        '45' => ['uf' => 'PR', 'cidade' => 'Curitiba'],
        '46' => ['uf' => 'PR', 'cidade' => 'Curitiba'],
        '47' => ['uf' => 'PR', 'cidade' => 'Curitiba'],
        '48' => ['uf' => 'PR', 'cidade' => 'Curitiba'],
        '49' => ['uf' => 'PR', 'cidade' => 'Curitiba'],
        '50' => ['uf' => 'PE', 'cidade' => 'Recife'],
        '51' => ['uf' => 'RS', 'cidade' => 'Porto Alegre'],
        '52' => ['uf' => 'RS', 'cidade' => 'Porto Alegre'],
        '53' => ['uf' => 'RS', 'cidade' => 'Porto Alegre'],
        '54' => ['uf' => 'RS', 'cidade' => 'Porto Alegre'],
        '55' => ['uf' => 'RS', 'cidade' => 'Porto Alegre'],
        '56' => ['uf' => 'RS', 'cidade' => 'Porto Alegre'],
        '57' => ['uf' => 'RS', 'cidade' => 'Porto Alegre'],
        '58' => ['uf' => 'RS', 'cidade' => 'Porto Alegre'],
        '59' => ['uf' => 'RS', 'cidade' => 'Porto Alegre'],
        '60' => ['uf' => 'CE', 'cidade' => 'Fortaleza'],
        '61' => ['uf' => 'DF', 'cidade' => 'Brasília'],
        '62' => ['uf' => 'GO', 'cidade' => 'Goiânia'],
        '63' => ['uf' => 'TO', 'cidade' => 'Palmas'],
        '64' => ['uf' => 'TO', 'cidade' => 'Palmas'],
        '65' => ['uf' => 'MT', 'cidade' => 'Cuiabá'],
        '66' => ['uf' => 'PA', 'cidade' => 'Belém'],
        '67' => ['uf' => 'MS', 'cidade' => 'Campo Grande'],
        '68' => ['uf' => 'AC', 'cidade' => 'Rio Branco'],
        '69' => ['uf' => 'RO', 'cidade' => 'Porto Velho'],
        '70' => ['uf' => 'DF', 'cidade' => 'Brasília'],
        '71' => ['uf' => 'DF', 'cidade' => 'Brasília'],
        '72' => ['uf' => 'DF', 'cidade' => 'Brasília'],
        '73' => ['uf' => 'DF', 'cidade' => 'Brasília'],
        '74' => ['uf' => 'DF', 'cidade' => 'Brasília'],
        '75' => ['uf' => 'DF', 'cidade' => 'Brasília'],
        '76' => ['uf' => 'DF', 'cidade' => 'Brasília'],
        '77' => ['uf' => 'DF', 'cidade' => 'Brasília'],
        '78' => ['uf' => 'DF', 'cidade' => 'Brasília'],
        '79' => ['uf' => 'DF', 'cidade' => 'Brasília'],
        '80' => ['uf' => 'PR', 'cidade' => 'Curitiba'],
        '81' => ['uf' => 'PE', 'cidade' => 'Recife'],
        '82' => ['uf' => 'AL', 'cidade' => 'Maceió'],
        '83' => ['uf' => 'PB', 'cidade' => 'João Pessoa'],
        '84' => ['uf' => 'RN', 'cidade' => 'Natal'],
        '85' => ['uf' => 'CE', 'cidade' => 'Fortaleza'],
        '86' => ['uf' => 'PI', 'cidade' => 'Teresina'],
        '87' => ['uf' => 'PE', 'cidade' => 'Recife'],
        '88' => ['uf' => 'SC', 'cidade' => 'Florianópolis'],
        '89' => ['uf' => 'PI', 'cidade' => 'Teresina'],
        '90' => ['uf' => 'RS', 'cidade' => 'Porto Alegre'],
        '91' => ['uf' => 'PA', 'cidade' => 'Belém'],
        '92' => ['uf' => 'AM', 'cidade' => 'Manaus'],
        '93' => ['uf' => 'PA', 'cidade' => 'Belém'],
        '94' => ['uf' => 'PA', 'cidade' => 'Belém'],
        '95' => ['uf' => 'RR', 'cidade' => 'Boa Vista'],
        '96' => ['uf' => 'AP', 'cidade' => 'Macapá'],
        '97' => ['uf' => 'AM', 'cidade' => 'Manaus'],
        '98' => ['uf' => 'MA', 'cidade' => 'São Luís'],
        '99' => ['uf' => 'MT', 'cidade' => 'Cuiabá']
    ];
    
    $prefixo = substr($cep_limpo, 0, 2);
    $estado_info = $estados[$prefixo] ?? ['uf' => 'SP', 'cidade' => 'São Paulo'];
    
    return [
        'logradouro' => 'Endereço não disponível',
        'bairro' => 'Bairro não disponível',
        'localidade' => $estado_info['cidade'],
        'uf' => $estado_info['uf'],
        'cep' => $cep_limpo,
        'fallback' => true
    ];
}

try {
    // Verificar se é uma requisição para buscar CEP
    if (isset($_GET['action']) && $_GET['action'] === 'buscar_cep') {
        $cep = $_GET['cep'] ?? '';
        $resultado = buscarCepViaBackend($cep);
        echo json_encode($resultado);
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método não permitido", 405);
    }
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("JSON inválido", 400);
    }

    // === CONFIGURAÇÃO SUPERFRETE ===
    $superfrete_token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE3NTIyMDY1OTksInN1YiI6InBPWTZHVVQ1b0NTZll2TVEwT05XeHpSYUR2ajIifQ.Fg45Uc1QopBlkjfTmk0jxH8AnpVwdGl3iP2BjkTdp60';
    $endpoint = 'https://api.superfrete.com/api/v0/calculator';

    // Forçar o CEP de origem cadastrado na SuperFrete
    $cep_origem_superfrete = '08690265'; // <-- CEP correto cadastrado na SuperFrete
    $data['from']['postal_code'] = $cep_origem_superfrete;

    // Forçar sempre os serviços PAC, SEDEX e Jadlog
    $services = '1,2,17';
    $payload = [
        'from' => $data['from'],
        'to' => $data['to'],
        'services' => $services,
        'options' => $data['options'],
        'package' => $data['package'],
        'value' => $data['value'] ?? 0
    ];

    // Log do payload para diagnóstico
    file_put_contents(__DIR__ . '/superfrete_payload_log.txt', json_encode($payload, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $superfrete_token,
        'User-Agent: CristaisGoldLar ([cristaisgoldlar@outlook.com])'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    // Log da resposta da SuperFrete para depuração
    file_put_contents(__DIR__ . '/superfrete_response_log.txt', $response . "\n", FILE_APPEND);
    curl_close($ch);

    if ($http_code !== 200) {
        throw new Exception("Erro na cotação SuperFrete: HTTP $http_code - $response", $http_code);
    }

    $responseData = json_decode($response, true);
    if (!is_array($responseData) || array_keys($responseData) !== range(0, count($responseData) - 1)) {
        // Não é um array numérico, encapsular
        $responseData = [$responseData];
    }
    
    // Consultar Jadlog como alternativa/complemento
    try {
        $jadlog_response = consultarJadlog($data);
        if ($jadlog_response) {
            $responseData = array_merge($responseData, $jadlog_response);
        }
    } catch (Exception $e) {
        // Silenciar erros da Jadlog para não afetar o fluxo principal
        error_log('Erro ao consultar Jadlog: ' . $e->getMessage());
    }
    
    echo json_encode($responseData);
    exit;

} catch (Exception $e) {
    http_response_code($e->getCode() > 0 ? $e->getCode() : 500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
    exit;
}
// Função para consultar Jadlog
function consultarJadlog($data) {
    // Token da Jadlog - ATENÇÃO: Este token está expirado ou inválido (erro 401)
    // É necessário obter um novo token válido da Jadlog para que a opção de frete apareça
    $jadlog_token = 'eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJqYWRsb2dfdG9rZW4iLCJqdGkiOiIzNzgwNDAxODAwMDE1NiJ9.hTZMgwJhZMCbXQnwvlxLiU6tJGh0q7LRXEJtQtjYS8A';
    
    // CNPJ da loja - Substitua pelo CNPJ real
    $cnpj = '37804018000156';
    
    $payload = [
        'frete' => [
            [
                'cepori' => '08690265', // CEP de origem (mesmo usado no SuperFrete)
                'cepdes' => $data['to']['postal_code'],
                'peso' => $data['package']['weight'],
                'cnpj' => $cnpj,
                'modalidade' => 3, // .PACKAGE (Rodoviário)
                'tpentrega' => 'D', // Entrega domiciliar
                'ipseguro' => 'N', // Seguro normal
                'vldeclarado' => $data['value'] ?? 0
            ]
        ]
    ];

    // Log do payload para diagnóstico
    file_put_contents(__DIR__ . '/jadlog_payload_log.txt', json_encode($payload, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

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
    $curl_error = curl_error($ch);
    
    // Log da resposta da Jadlog para depuração
    file_put_contents(__DIR__ . '/jadlog_response_log.txt', $response . "\n", FILE_APPEND);
    
    curl_close($ch);

    if ($http_code === 200) {
        $data = json_decode($response, true);
        if (isset($data['frete'][0]['vltotal'])) {
            return [
                [
                    'id' => 'jadlog',
                    'name' => 'JADLOG .PACKAGE ★',
                    'price' => $data['frete'][0]['vltotal'],
                    'delivery_time' => $data['frete'][0]['prazo'] ?? 7,
                    'company' => [
                        'name' => 'Jadlog',
                        'picture' => 'https://www.jadlog.com.br/assets/img/logo-jadlog.png'
                    ]
                ]
            ];
        }
    }
    return null;
}
?>