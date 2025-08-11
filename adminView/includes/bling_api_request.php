<?php
// bling_api_request.php
// Emissão real de NF-e no Bling: recebe dados do pedido via POST, emite, consulta status e PDF

header('Content-Type: application/json');

$tokenFile = __DIR__ . '/bling_tokens.json';
if (!file_exists($tokenFile)) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Token de acesso não encontrado. Faça a autenticação primeiro.']);
    exit;
}

$tokens = json_decode(file_get_contents($tokenFile), true);
if (!isset($tokens['access_token'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Access token inválido.']);
    exit;
}
$accessToken = $tokens['access_token'];

// Recebe os dados do pedido via POST (JSON)
$input = file_get_contents('php://input');
$order = json_decode($input, true);
if (!$order || !isset($order['cliente']) || !isset($order['itens'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Dados do pedido incompletos. Envie cliente e itens.']);
    exit;
}

// Monta o payload completo para emissão de NF-e
$payload = [
    'cliente' => [
        'nome' => $order['cliente']['nome'],
        'tipoPessoa' => $order['cliente']['tipoPessoa'], // 'F' ou 'J'
        'cpfCnpj' => $order['cliente']['cpfCnpj'],
        'endereco' => $order['cliente']['endereco'],
        'numero' => $order['cliente']['numero'],
        'bairro' => $order['cliente']['bairro'],
        'cep' => $order['cliente']['cep'],
        'cidade' => $order['cliente']['cidade'],
        'uf' => $order['cliente']['uf'],
        'email' => $order['cliente']['email'],
        'fone' => $order['cliente']['fone']
    ],
    'itens' => array_map(function($item) {
        return [
            'codigo' => $item['codigo'],
            'descricao' => $item['descricao'],
            'quantidade' => $item['quantidade'],
            'valor' => $item['valor'],
            'un' => $item['un'] ?? 'UN',
            'ncm' => $item['ncm'] ?? ''
        ];
    }, $order['itens']),
    'pagamento' => [
        'forma' => $order['pagamento']['forma'] ?? '99',
        'valor' => $order['pagamento']['valor'] ?? 0
    ],
    'transporte' => $order['transporte'] ?? null,
    'numero' => $order['numero'] ?? rand(10000,99999),
    'serie' => $order['serie'] ?? 1,
    'tipo' => $order['tipo'] ?? 'saida',
    'naturezaOperacao' => $order['naturezaOperacao'] ?? 'VENDA',
    'dataEmissao' => $order['dataEmissao'] ?? date('Y-m-d'),
    'frete' => $order['frete'] ?? 0,
    'desconto' => $order['desconto'] ?? 0,
    'observacoes' => $order['observacoes'] ?? ''
];
if (!$payload['transporte']) unset($payload['transporte']);

// Emite a nota fiscal
$url = 'https://api.bling.com.br/Api/v3/notas-fiscais';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Accept: application/json',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$resArr = json_decode($response, true);

if ($httpCode !== 200 && $httpCode !== 201) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro ao emitir nota fiscal.',
        'http_code' => $httpCode,
        'response' => $resArr
    ]);
    exit;
}

// Consulta a nota fiscal emitida para obter chave, status e PDF
if (!isset($resArr['data']['numero']) || !isset($resArr['data']['serie'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Nota emitida mas sem número/serie retornados.',
        'response' => $resArr
    ]);
    exit;
}
$numero = $resArr['data']['numero'];
$serie = $resArr['data']['serie'];

$consultaUrl = 'https://api.bling.com.br/Api/v3/notas-fiscais?numero=' . urlencode($numero) . '&serie=' . urlencode($serie);
$ch = curl_init($consultaUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$consultaResp = curl_exec($ch);
curl_close($ch);
$consultaArr = json_decode($consultaResp, true);

// Busca o PDF da DANFE se disponível
$pdfUrl = null;
if (isset($consultaArr['data'][0]['urlDanfePdf'])) {
    $pdfUrl = $consultaArr['data'][0]['urlDanfePdf'];
}

echo json_encode([
    'status' => 'success',
    'message' => 'Nota fiscal emitida com sucesso!',
    'numero' => $numero,
    'serie' => $serie,
    'chave' => $consultaArr['data'][0]['chaveAcesso'] ?? null,
    'status_nfe' => $consultaArr['data'][0]['status'] ?? null,
    'pdf_url' => $pdfUrl,
    'resposta_emissao' => $resArr,
    'resposta_consulta' => $consultaArr
]);
