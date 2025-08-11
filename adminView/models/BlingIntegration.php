<?php
// Esqueleto da integração Bling conforme documentação oficial
// https://developer.bling.com.br/bling-api
class BlingIntegration {
    private $token;
    private $baseUrl = 'https://www.bling.com.br/Api/v3/';

    public function __construct() {
        // Lê o token do arquivo de configuração JSON
        $configPath = __DIR__ . '/../config/bling_api.json';
        if (!file_exists($configPath)) {
            error_log('Tentando carregar token do Bling de: ' . $configPath);
            throw new Exception('Arquivo de configuração do token do Bling não encontrado: ' . $configPath);
        }
        $config = json_decode(file_get_contents($configPath), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Erro ao decodificar JSON do arquivo de tokens: ' . json_last_error_msg());
            throw new Exception('Erro ao ler configuração do token do Bling - JSON inválido');
        }
        if (!isset($config['access_token']) || empty($config['access_token'])) {
            error_log('access_token não encontrado no arquivo bling_api.json');
            throw new Exception('Token OAuth2 não configurado no arquivo bling_api.json');
        }
        $this->token = $config['access_token'];
        error_log('Token OAuth2 do Bling carregado: ' . substr($this->token, 0, 10) . '...');
    }

    private function request($endpoint, $method = 'GET', $body = null) {
        $url = $this->baseUrl . ltrim($endpoint, '/');
        $headers = [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if ($body) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
    }
        curl_close($ch);
        $data = json_decode($response, true);
        if ($httpCode >= 400) {
            throw new Exception('Erro API Bling: ' . ($data['message'] ?? $response));
        }
        return $data;
    }

    // Cria um contato no Bling e salva o id_externo no cliente
    public function criarContato($clienteData, $pdo = null) {
        $payload = [
            'nome' => $clienteData['nome'],
            'codigo' => $clienteData['codigo'] ?? null,
            'situacao' => $clienteData['situacao'] ?? 'A',
            'numeroDocumento' => $clienteData['numeroDocumento'],
            'telefone' => $clienteData['telefone'] ?? null,
            'celular' => $clienteData['celular'] ?? null,
            'fantasia' => $clienteData['fantasia'] ?? null,
            'tipo' => $clienteData['tipo'] ?? 'F',
            'indicadorIe' => $clienteData['indicadorIe'] ?? 9,
            'ie' => $clienteData['ie'] ?? null,
            'rg' => $clienteData['rg'] ?? null,
            'inscricaoMunicipal' => $clienteData['inscricaoMunicipal'] ?? null,
            'orgaoEmissor' => $clienteData['orgaoEmissor'] ?? null,
            'email' => $clienteData['email'],
            'endereco' => $clienteData['endereco'] ?? [],
            'vendedor' => $clienteData['vendedor'] ?? null,
            'dadosAdicionais' => $clienteData['dadosAdicionais'] ?? null,
            'financeiro' => $clienteData['financeiro'] ?? null,
            'pais' => $clienteData['pais'] ?? null,
            'tiposContato' => $clienteData['tiposContato'] ?? null,
            'pessoasContato' => $clienteData['pessoasContato'] ?? null
        ];
        try {
            $resp = $this->request('/contatos', 'POST', $payload);
            if (!empty($resp['data']['id']) && $pdo && !empty($clienteData['usuario_id'])) {
                $stmt = $pdo->prepare("UPDATE usuarios SET id_externo_bling = ? WHERE id = ?");
                $stmt->execute([$resp['data']['id'], $clienteData['usuario_id']]);
            }
            return $resp['data']['id'] ?? null;
        } catch (Exception $e) {
            $msg = $e->getMessage();
            // Se o erro for de documento já cadastrado (CPF ou CNPJ), busca o contato existente
            if (strpos($msg, 'VALIDATION_ERROR') !== false && preg_match('/(CPF|CNPJ) já está cadastrado/i', $msg)) {
                // Busca o contato pelo CPF
                $cpf = $clienteData['numeroDocumento'];
                $contato = $this->buscarContatoPorDocumento($cpf);
                if ($contato && isset($contato['id'])) {
                    // Atualiza id_externo_bling no banco se possível
                    if ($pdo && !empty($clienteData['usuario_id'])) {
                        $stmt = $pdo->prepare("UPDATE usuarios SET id_externo_bling = ? WHERE id = ?");
                        $stmt->execute([$contato['id'], $clienteData['usuario_id']]);
                    }
                    return $contato['id'];
                }
                // Se não encontrar, lança a Exception original
                throw $e;
            } else {
                throw $e;
            }
        }

    }

    // Busca contato pelo CPF/CNPJ
    public function buscarContatoPorDocumento($documento) {
        $resp = $this->request('/contatos?numeroDocumento=' . urlencode($documento), 'GET');
        if (!empty($resp['data']) && is_array($resp['data'])) {
            foreach ($resp['data'] as $contato) {
                if (isset($contato['numeroDocumento']) && $contato['numeroDocumento'] == $documento) {
                    return $contato;
                }
            }
        }
        return null;
    }

    // Cria um pedido de venda no Bling
    public function criarPedidoVenda($pedidoData, $pdo = null) {
        // Busca id_externo do cliente se não vier
        $contato_id = $pedidoData['contato_id'] ?? null;
        if (!$contato_id && !empty($pedidoData['usuario_id']) && $pdo) {
            $stmt = $pdo->prepare("SELECT id_externo_bling FROM usuarios WHERE id = ?");
            $stmt->execute([$pedidoData['usuario_id']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $contato_id = $row['id_externo_bling'] ?? null;
        }
        if (!$contato_id) {
            throw new Exception('Contato do cliente não encontrado para o pedido.');
        }
        // Monta payload conforme especificação do arquiteto
        $payload = [
            'numero' => $pedidoData['numero'],
            'data' => $pedidoData['data'],
            'dataSaida' => $pedidoData['dataSaida'],
            'dataPrevista' => $pedidoData['dataPrevista'],
            'contato' => [ 'id' => $contato_id ],
            'loja' => [ 'id' => $pedidoData['loja_id'] ?? 205510600 ],
            'numeroPedidoCompra' => $pedidoData['numeroPedidoCompra'] ?? '',
            'outrasDespesas' => $pedidoData['outrasDespesas'] ?? 0,
            'observacoes' => $pedidoData['observacoes'] ?? '',
            'observacoesInternas' => $pedidoData['observacoesInternas'] ?? '',
            'desconto' => $pedidoData['desconto'] ?? [ 'valor' => 0, 'unidade' => 'REAL' ],
            'tributacao' => $pedidoData['tributacao'] ?? [ 'totalICMS' => 0, 'totalIPI' => 0 ],
            'itens' => $pedidoData['itens'],
            'pagamento' => $pedidoData['pagamento'],
            'taxas' => $pedidoData['taxas'] ?? [ 'taxaComissao' => 0, 'custoFrete' => 0, 'valorBase' => 0 ]
        ];
        $resp = $this->request('/pedidos/vendas', 'POST', $payload);
        return $resp;
    }

    // Emissão de nota fiscal
    public function emitirNotaFiscal($nfeData) {
        // Endpoint oficial: /notasfiscais
        return $this->request('/nfe', 'POST', $nfeData);
    }

    // Consulta de nota fiscal
    public function consultarNotaFiscal($chave) {
        // Endpoint oficial: /notasfiscais/{chaveAcesso}
        return $this->request('/nfe' . $chave, 'GET');
    }

    // Download do PDF da nota fiscal
    public function obterPdfNotaFiscal($chave) {
        // Endpoint oficial: /notasfiscais/{chaveAcesso}/pdf
        $data = $this->request('/nfe' . $chave . '/pdf', 'GET');
        return $data['pdf_url'] ?? null;
    }
}
