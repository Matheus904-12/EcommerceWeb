<?php

class BlingService {
    // Emite uma nota fiscal no Bling
    public function emitirNotaFiscal($orderData, $nfeBody) {
        // Monta o payload conforme esperado pela API do Bling
        $payload = [
            "pedido" => $nfeBody["pedido"],
            "tipo" => $nfeBody["tipo"],
            "natureza_operacao" => $nfeBody["natureza_operacao"],
            "cliente" => $nfeBody["cliente"]
        ];
        $endpoint = "/n";
        $response = $this->fazerRequisicao('POST', $endpoint, $payload);
        if (isset($response['data'])) {
            // Normaliza os dados para o controller
            $data = $response['data'];
            return [
                'chave' => $data['chave'] ?? ($data['chaveAcesso'] ?? ''),
                'numero' => $data['numero'] ?? ($data['numeroNota'] ?? ''),
                'serie' => $data['serie'] ?? ($data['serieNota'] ?? ''),
                'data_emissao' => $data['data_emissao'] ?? ($data['dataEmissao'] ?? ''),
                'pdf_url' => $data['pdf_url'] ?? ($data['pdfUrl'] ?? '')
            ];
        }
        throw new Exception("Erro ao emitir nota fiscal: " . json_encode($response));
    }
    // Consulta nota fiscal pelo número e série
    public function consultarNotaFiscal($numero, $serie) {
        $endpoint = "/nfe?numero={$numero}&serie={$serie}";
        $response = $this->fazerRequisicao('GET', $endpoint);
        if (isset($response['data'][0])) {
            return $response['data'][0];
        }
        throw new Exception("Nota fiscal não encontrada: " . json_encode($response));
    }

    // Obtém o PDF da nota fiscal pela chave
    public function obterPdfNotaFiscal($chave) {
        $endpoint = "/nfe/{$chave}/pdf";
        $response = $this->fazerRequisicao('GET', $endpoint);
        if (isset($response['data']['pdf_url'])) {
            return $response['data']['pdf_url'];
        }
        throw new Exception("PDF da nota fiscal não encontrado: " . json_encode($response));
    }
    private $apiUrl = 'https://api.bling.com.br/Api/v3';
    private $apiKey;
    private $conn;
    private $lojaId = '205510600'; // ID fixo da loja conforme especificado

    public function __construct($conn, $apiKey) {
        $this->conn = $conn;
        $this->apiKey = $apiKey;
    }

    // Cria ou atualiza um contato no Bling
    public function criarOuAtualizarContato($userData) {
        // Normaliza e valida os dados do cliente
        $cpf = isset($userData['cpf']) ? preg_replace('/\D/', '', $userData['cpf']) : '';
        $codigo = $cpf;
        $telefone = isset($userData['telefone']) ? $userData['telefone'] : '';
        $celular = isset($userData['celular']) ? $userData['celular'] : $telefone;
        $cep = isset($userData['cep']) ? preg_replace('/\D/', '', $userData['cep']) : '';
        $endereco = $userData['endereco'] ?? '';
        $numero = $userData['numero'] ?? ($userData['numero_casa'] ?? '');
        $complemento = $userData['complemento'] ?? '';
        $bairro = $userData['bairro'] ?? ($userData['bairro_entrega'] ?? '');
        $cidade = $userData['cidade'] ?? ($userData['cidade_entrega'] ?? '');
        $uf = $userData['estado'] ?? ($userData['uf_entrega'] ?? '');
        $email = $userData['email'] ?? '';
        $nome = $userData['name'] ?? $userData['nome'] ?? '';
        $payload = [
            "nome" => $nome,
            "codigo" => $codigo,
            "situacao" => "A",
            "numeroDocumento" => $cpf,
            "telefone" => $telefone,
            "celular" => $celular,
            "fantasia" => $nome,
            "tipo" => "F",
            "indicadorIe" => 9,
            "ie" => $userData['ie'] ?? '',
            "rg" => $userData['rg'] ?? '',
            "inscricaoMunicipal" => $userData['inscricao_municipal'] ?? '',
            "orgaoEmissor" => $userData['orgao_emissor'] ?? '',
            "email" => $email,
            "endereco" => [
                "geral" => [
                    "endereco" => $endereco,
                    "numero" => $numero,
                    "complemento" => $complemento,
                    "bairro" => $bairro,
                    "cep" => $cep,
                    "municipio" => $cidade,
                    "uf" => $uf
                ],
                "cobranca" => [
                    "endereco" => $endereco,
                    "numero" => $numero,
                    "complemento" => $complemento,
                    "bairro" => $bairro,
                    "cep" => $cep,
                    "municipio" => $cidade,
                    "uf" => $uf
                ]
            ],
            "dadosAdicionais" => [
                "dataNascimento" => $userData['data_nascimento'] ?? '',
                "sexo" => $userData['sexo'] ?? '',
                "naturalidade" => $userData['nacionalidade'] ?? ''
            ],
            "financeiro" => [
                "limiteCredito" => 0,
                "condicaoPagamento" => '30',
                "categoria" => [ "id" => 1 ]
            ],
            "pais" => [ "nome" => 'BRASIL' ]
        ];

        $response = $this->fazerRequisicao('POST', '/contatos', $payload);

        // Exibe o payload e resposta do Bling na tela e no console para debug
        echo '<details style="background:#eaffea; border:1px solid #0a0; padding:10px; margin:10px 0; color:#333;">
                <summary style="font-weight:bold; color:#080;">Debug Bling (cadastro do cliente)</summary>
                <div><b>Payload enviado:</b><br><pre style="white-space:pre-wrap;">'.htmlspecialchars(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)).'</pre></div>
                <div><b>Resposta Bling:</b><br><pre style="white-space:pre-wrap;">'.htmlspecialchars(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)).'</pre></div>
            </details>';
        echo '<script>console.log("BLING CONTATO PAYLOAD:", '.json_encode($payload).');</script>';
        echo '<script>console.log("BLING CONTATO RESPONSE:", '.json_encode($response).');</script>';

        if (isset($response['data']['codigo'])) {
            // Atualiza o id_externo_bling no banco de dados com o código (CPF/CNPJ)
            $stmt = $this->conn->prepare("UPDATE usuarios SET id_externo_bling = ? WHERE id = ?");
            $stmt->bind_param("si", $response['data']['codigo'], $userData['id']);
            $stmt->execute();
            
            $this->registrarLog('contato', 'criar', 'sucesso', $userData['id'], $payload, $response);
            return $response['data']['codigo'];
        }

        throw new Exception("Erro ao criar contato no Bling: " . json_encode($response));
    }

    // Cria um pedido no Bling
    public function criarPedido($orderData, $items) {
        // Busca o número do último pedido
        $stmt = $this->conn->prepare("SELECT MAX(bling_numero_pedido) as ultimo FROM orders");
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $numeroPedido = ($row['ultimo'] ?? 0) + 1;

        $payload = [
            "numero" => $numeroPedido,
            "data" => date('Y-m-d'),
            "dataSaida" => date('Y-m-d', strtotime('+7 days')),
            "dataPrevista" => date('Y-m-d', strtotime('+7 days')),
            "contato" => [
                "id" => $orderData['bling_id']
            ],
            "loja" => [
                "id" => $this->lojaId
            ],
            "numeroPedidoCompra" => $orderData['id'],
            "itens" => array_map(function($item) {
                return [
                    "codigo" => $item['product_id'],
                    "unidade" => "UN",
                    "quantidade" => $item['quantity'],
                    "valor" => $item['price'],
                    "descricao" => $item['nome'],
                    "ncm" => "70139110", // NCM padrão - ajuste conforme necessário
                    "cfop" => "5102"  // CFOP padrão para venda
                ];
            }, $items),
            "pagamento" => [
                "formaPagamento" => $this->mapearFormaPagamento($orderData['payment_method']),
                "parcelas" => "1x"
            ]
        ];

        $response = $this->fazerRequisicao('POST', '/pedidos/vendas', $payload);
        
        if (isset($response['data']['id'])) {
            // Atualiza o número do pedido no banco
            $stmt = $this->conn->prepare("UPDATE orders SET bling_numero_pedido = ? WHERE id = ?");
            $stmt->bind_param("ii", $numeroPedido, $orderData['id']);
            $stmt->execute();
            
            $this->registrarLog('pedido', 'criar', 'sucesso', $orderData['id'], $payload, $response);
            return $response['data'];
        }

        throw new Exception("Erro ao criar pedido no Bling: " . json_encode($response));
    }

    private function mapearFormaPagamento($metodo) {
        $mapeamento = [
            'credit_card' => 'Cartão de Crédito',
            'boleto' => 'Boleto',
            'pix' => 'PIX',
            // Adicione outros mapeamentos conforme necessário
        ];
        
        return $mapeamento[$metodo] ?? 'Outros';
    }

    private function fazerRequisicao($method, $endpoint, $data = null) {
        $url = $this->apiUrl . $endpoint;
        
        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            throw new Exception("Erro na requisição ao Bling: " . $response);
        }

        return json_decode($response, true);
    }

    private function registrarLog($tipo, $operacao, $status, $id_referencia, $payload, $resposta) {
        $stmt = $this->conn->prepare(
            "INSERT INTO bling_integration_log 
             (tipo, operacao, status, id_referencia, payload, resposta) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        
        $payloadJson = json_encode($payload);
        $respostaJson = json_encode($resposta);
        
        $stmt->bind_param(
            "ssssss",
            $tipo,
            $operacao,
            $status,
            $id_referencia,
            $payloadJson,
            $respostaJson
        );
        
        $stmt->execute();
    }
}
