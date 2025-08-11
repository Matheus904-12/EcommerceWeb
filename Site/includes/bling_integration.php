<?php

/**
 * Classe de integração com a API do Bling para emissão, consulta, PDF de NF-e, criação de contato e pedido de venda
 */
class BlingIntegration {
    private $apiUrl;
    private $accessToken;

    public function __construct() {
        $this->apiUrl = getenv('BLING_API_URL') ?: 'https://api.bling.com.br/Api/v3';
        $tokenFile = __DIR__ . '/../../adminView/config/bling_api.json';
        if (file_exists($tokenFile)) {
            $tokens = json_decode(file_get_contents($tokenFile), true);
            $this->accessToken = $tokens['access_token'] ?? '';
        } else {
            $this->accessToken = '';
        }
    }

    /**
     * Cria um contato completo no Bling e salva o ID externo no cliente
     */
    public function criarContatoCompleto($clienteData, $pdo = null) {
        // Remove campos indesejados do clienteData
        unset($clienteData['cnpj'], $clienteData['cpfCnpj'], $clienteData['cpf_cnpj']);

        // Garante que só CPF válido será enviado
        $cpf = '';
        if (!empty($clienteData['cpf'])) {
            $cpf = preg_replace('/\D/', '', $clienteData['cpf']);
        }
        if (!$this->isCpfValido($cpf)) {
            throw new Exception('CPF do cliente inválido para integração com o Bling.');
        }

        $payload = [
            'nome' => $clienteData['nome'],
            'codigo' => $clienteData['codigo'] ?? '',
            'situacao' => $clienteData['situacao'] ?? 'A',
            'numeroDocumento' => $cpf,
            'telefone' => $clienteData['telefone'] ?? '',
            'celular' => $clienteData['celular'] ?? '',
            'fantasia' => $clienteData['fantasia'] ?? '',
            'tipo' => 'F', // Sempre pessoa física
            'indicadorIe' => $clienteData['indicadorIe'] ?? 9,
            'ie' => $clienteData['ie'] ?? null,
            'rg' => $clienteData['rg'] ?? '',
            'inscricaoMunicipal' => $clienteData['inscricaoMunicipal'] ?? '',
            'orgaoEmissor' => $clienteData['orgaoEmissor'] ?? '',
            'email' => $clienteData['email'],
            'endereco' => [
                'geral' => [
                    'endereco' => $clienteData['endereco'] ?? '',
                    'cep' => $clienteData['cep'] ?? '',
                    'bairro' => $clienteData['bairro'] ?? '',
                    'municipio' => $clienteData['municipio'] ?? '',
                    'uf' => $clienteData['uf'] ?? '',
                    'numero' => $clienteData['numero'] ?? '',
                    'complemento' => $clienteData['complemento'] ?? ''
                ],
                'cobranca' => [
                    'endereco' => $clienteData['endereco'] ?? '',
                    'cep' => $clienteData['cep'] ?? '',
                    'bairro' => $clienteData['bairro'] ?? '',
                    'municipio' => $clienteData['municipio'] ?? '',
                    'uf' => $clienteData['uf'] ?? '',
                    'numero' => $clienteData['numero'] ?? '',
                    'complemento' => $clienteData['complemento'] ?? ''
                ]
            ],
            'vendedor' => [
                'id' => $clienteData['vendedor_id'] ?? null
            ],
            'dadosAdicionais' => [
                'dataNascimento' => $clienteData['dataNascimento'] ?? null,
                'sexo' => $clienteData['sexo'] ?? null,
                'naturalidade' => $clienteData['naturalidade'] ?? null
            ],
            'financeiro' => [
                'limiteCredito' => $clienteData['limiteCredito'] ?? 0,
                'condicaoPagamento' => $clienteData['condicaoPagamento'] ?? '',
                'categoria' => [
                    'id' => $clienteData['categoria_id'] ?? null
                ]
            ],
            'pais' => [
                'nome' => $clienteData['pais'] ?? 'BRASIL'
            ],
            'tiposContato' => $clienteData['tiposContato'] ?? [],
            'pessoasContato' => $clienteData['pessoasContato'] ?? []
        ];
        // Remove qualquer campo cnpj/cpfCnpj do payload final, por segurança
        unset($payload['cnpj'], $payload['cpfCnpj'], $payload['cpf_cnpj']);

        // Loga o payload para debug
        file_put_contents(__DIR__ . '/debug_bling_payload_contato.log', date('Y-m-d H:i:s') . ' | Payload: ' . json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

        $resp = $this->request('/contatos', 'POST', $payload);
        if (!empty($resp['data']['id'])) {
            // Salva o id externo no cliente
            if ($pdo && !empty($clienteData['id'])) {
                $stmt = $pdo->prepare('UPDATE usuarios SET id_externo_bling = :id_externo WHERE id = :id');
                $stmt->execute([':id_externo' => $resp['data']['id'], ':id' => $clienteData['id']]);
            }
            return $resp['data']['id'];
        }
        $this->logBlingError('Falha ao criar contato completo no Bling', $payload);
        throw new Exception('Erro ao criar contato completo no Bling. Veja detalhes no log.');
    }

    /**
     * Valida se o CPF é válido (algoritmo oficial)
     */
    private function isCpfValido($cpf) {
        if (strlen($cpf) != 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        return true;
    }

    /**
     * Cria um pedido de venda no Bling conforme o payload do arquiteto
     */
    public function criarPedidoVenda($pedidoData) {
        // Log inicial para garantir criação do arquivo
        $logFile = __DIR__ . '/debug_bling_pedido.log';
        file_put_contents($logFile, date('Y-m-d H:i:s') . " | Entrou no criarPedidoVenda\n", FILE_APPEND);
        try {
            // Corrige os itens para garantir que o nome do produto seja o cadastrado no banco e monta todos os campos obrigatórios do item
            $itensCorrigidos = [];
            foreach ($pedidoData['itens'] as $item) {
                // Validação de campos obrigatórios do item
                if (empty($item['codigo'])) {
                    $this->logBlingError('Item sem código (SKU) no pedido', $item);
                    throw new Exception('Item do pedido sem código (SKU).');
                }
                $nomeProduto = $item['descricao'] ?? '';
                if (empty($nomeProduto) && isset($item['codigo'])) {
                    if (!empty($item['produto_id']) && isset($GLOBALS['pdo'])) {
                        $stmt = $GLOBALS['pdo']->prepare('SELECT nome FROM produtos WHERE id = :id');
                        $stmt->execute([':id' => $item['produto_id']]);
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($row && !empty($row['nome'])) {
                            $nomeProduto = $row['nome'];
                        }
                    }
                }
                // Padronização de tipos numéricos
                $quantidade = isset($item['quantidade']) ? (float)$item['quantidade'] : 1;
                $desconto = isset($item['desconto']) ? (float)$item['desconto'] : 0;
                $valor = isset($item['valor']) ? (float)$item['valor'] : 0;
                $aliquotaIPI = isset($item['aliquotaIPI']) ? (float)$item['aliquotaIPI'] : 0;
                $novoItem = [
                    'codigo' => $item['codigo'],
                    // Bling espera o campo 'un' para unidade, não 'unidade' (padronização)
                    'un' => $item['unidade'] ?? 'UN',
                    'quantidade' => $quantidade,
                    'desconto' => $desconto,
                    'situacao' => $item['situacao'] ?? 'A',
                    'ncm' => $item['ncm'] ?? '',
                    'numeroPedidoCompra' => $item['numeroPedidoCompra'] ?? '',
                    'classificacaoFiscal' => $item['classificacaoFiscal'] ?? '',
                    'cest' => $item['cest'] ?? '',
                    'cfop' => $item['cfop'] ?? '',
                    'valor' => $valor,
                    'aliquotaIPI' => $aliquotaIPI,
                    'descricao' => $nomeProduto,
                    'descricaoDetalhada' => $item['descricaoDetalhada'] ?? ''
                ];
                // Validação de campos obrigatórios do item para o Bling
                if (empty($novoItem['descricao'])) {
                    $this->logBlingError('Item sem nome/descrição no pedido', $novoItem);
                    throw new Exception('Item do pedido sem nome/descrição.');
                }
                $itensCorrigidos[] = $novoItem;
            }

            // FLUXO: Buscar ID do contato, criar se não existir, garantir ID para o payload
            $contato_id = $pedidoData['contato_id'] ?? null;
            if (!$contato_id && !empty($pedidoData['cliente_id']) && isset($GLOBALS['pdo'])) {
                $stmt = $GLOBALS['pdo']->prepare('SELECT * FROM usuarios WHERE id = :id LIMIT 1');
                $stmt->execute([':id' => $pedidoData['cliente_id']]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    // Tenta buscar o id_externo_bling
                    if (!empty($row['id_externo_bling'])) {
                        $contato_id = $row['id_externo_bling'];
                    } else {
                        // Não existe, criar contato no Bling
                        $clienteData = [
                            'id' => $pedidoData['cliente_id'],
                            'nome' => $row['nome'] ?? '',
                            'email' => $row['email'] ?? '',
                            'cpf' => $row['cpf'] ?? '',
                            'telefone' => $row['telefone'] ?? '',
                            'endereco' => $row['endereco'] ?? '',
                            'numero' => $row['numero_casa'] ?? '',
                            'cep' => $row['cep'] ?? '',
                            'bairro' => $row['bairro'] ?? '',
                            'municipio' => $row['municipio'] ?? '',
                            'uf' => $row['uf'] ?? '',
                        ];
                        try {
                            $contato_id = $this->criarContatoCompleto($clienteData, $GLOBALS['pdo']);
                        } catch (\Throwable $e) {
                            $this->logBlingError('Falha ao criar cliente no Bling durante criarPedidoVenda', $clienteData);
                            throw new Exception('Não foi possível criar o cliente no Bling para o pedido.');
                        }
                        // Buscar novamente o id_externo_bling após criar
                        $stmt2 = $GLOBALS['pdo']->prepare('SELECT id_externo_bling FROM usuarios WHERE id = :id LIMIT 1');
                        $stmt2->execute([':id' => $pedidoData['cliente_id']]);
                        $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
                        if ($row2 && !empty($row2['id_externo_bling'])) {
                            $contato_id = $row2['id_externo_bling'];
                        }
                    }
                }
            }
            // Se ainda não encontrou, erro explícito
            if (empty($contato_id)) {
                $this->logBlingError('Contato do cliente não encontrado para o pedido', $pedidoData);
                // Exibe erro no console do navegador (se for chamada via AJAX)
                $msg = 'Contato do cliente não encontrado para o pedido.';
                if (!headers_sent()) {
                    header('X-Bling-Error: ' . rawurlencode($msg));
                }
                echo "<script>console.error('[Bling] Erro: " . addslashes($msg) . "');</script>";
                throw new Exception($msg);
            }

            // Busca endereço do cliente para log e debug (não vai no payload do pedido, mas pode ser útil)
            $endereco_cliente = [];
            if (!empty($pedidoData['cliente_id']) && isset($GLOBALS['pdo'])) {
                $stmt = $GLOBALS['pdo']->prepare('SELECT endereco, numero_casa, cep, telefone, profile_picture, name, email, cpf FROM usuarios WHERE id = :id LIMIT 1');
                $stmt->execute([':id' => $pedidoData['cliente_id']]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    $endereco_cliente = $row;
                    // Loga o endereço do cliente para debug
                    $logEndereco = [
                        'timestamp' => date('Y-m-d H:i:s'),
                        'cliente_id' => $pedidoData['cliente_id'],
                        'endereco_cliente' => $endereco_cliente
                    ];
                    file_put_contents(__DIR__ . '/debug_bling_endereco_cliente.log', json_encode($logEndereco, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
                }
            }

            // Monta o payload conforme documentação do Bling
            $payload = [
                'numero' => $pedidoData['numero'],
                'data' => $pedidoData['data'],
                'dataSaida' => $pedidoData['dataSaida'],
                'dataPrevista' => $pedidoData['dataPrevista'],
                'contato' => [
                    'id' => $contato_id
                ],
                'loja' => [
                    'id' => $pedidoData['loja_id'] ?? 205510600 // HARDCODE se não vier
                ],
                'numeroPedidoCompra' => $pedidoData['numeroPedidoCompra'] ?? '',
                'outrasDespesas' => $pedidoData['outrasDespesas'] ?? 0,
                'observacoes' => $pedidoData['observacoes'] ?? '',
                'observacoesInternas' => $pedidoData['observacoesInternas'] ?? '',
                'desconto' => [
                    'valor' => $pedidoData['desconto_valor'] ?? 0,
                    'unidade' => $pedidoData['desconto_unidade'] ?? 'REAL',
                ],
                'tributacao' => [
                    'totalICMS' => $pedidoData['totalICMS'] ?? 0,
                    'totalIPI' => $pedidoData['totalIPI'] ?? 0
                ],
                'itens' => $itensCorrigidos,
                'pagamento' => [
                    'formaPagamento' => $pedidoData['formaPagamento'] ?? '',
                    'parcelas' => $pedidoData['parcelas'] ?? ''
                ],
                'taxas' => [
                    'taxaComissao' => $pedidoData['taxaComissao'] ?? 0,
                    'custoFrete' => isset($pedidoData['custoFrete']) ? (float)$pedidoData['custoFrete'] : (isset($pedidoData['shipping']) ? (float)$pedidoData['shipping'] : 0),
                    'valorBase' => $pedidoData['valorBase'] ?? 0
                ]
            ];

            // Log do payload enviado para o Bling (pedido de venda) em formato JSON, cada linha um objeto
            $jsonLogFile = __DIR__ . '/bling_pedido_payload.json';
            $jsonLogEntry = [
                'timestamp' => date('Y-m-d H:i:s'),
                'pedido' => $payload['numero'],
                'payload' => $payload
            ];
            $jsonLine = json_encode($jsonLogEntry, JSON_UNESCAPED_UNICODE) . "\n";
            $fp = fopen($jsonLogFile, 'a');
            file_put_contents($logFile, date('Y-m-d H:i:s') . " | Tentando escrever o payload do pedido de venda: " . $payload['numero'] . "\n", FILE_APPEND);
            if ($fp) {
                fwrite($fp, $jsonLine);
                fclose($fp);
                file_put_contents($logFile, date('Y-m-d H:i:s') . " | Sucesso ao escrever o payload do pedido de venda: " . $payload['numero'] . "\n", FILE_APPEND);
            } else {
                $this->logBlingError('Não foi possível criar ou escrever no arquivo bling_pedido_payload.json', $jsonLogEntry);
                file_put_contents($logFile, date('Y-m-d H:i:s') . " | Falha ao escrever o payload do pedido de venda: " . $payload['numero'] . "\n", FILE_APPEND);
            }

            $resp = $this->request('/pedidos/vendas', 'POST', $payload);
            file_put_contents($logFile, date('Y-m-d H:i:s') . " | Resposta do Bling: " . json_encode($resp, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
            if (!empty($resp['data']['id'])) {
                // Salvar o número do pedido no banco de dados (tabela pedidos_bling, por exemplo)
                try {
                    if (isset($GLOBALS['pdo'])) {
                        $stmt = $GLOBALS['pdo']->prepare('INSERT INTO pedidos_bling (pedido_id_local, pedido_id_bling, data_criacao) VALUES (:pedido_id_local, :pedido_id_bling, NOW())');
                        $stmt->execute([
                            ':pedido_id_local' => $pedidoData['numero'],
                            ':pedido_id_bling' => $resp['data']['id']
                        ]);
                    }
                } catch (\Throwable $e) {
                    $this->logBlingError('Falha ao salvar número do pedido Bling no banco', [
                        'pedido_id_local' => $pedidoData['numero'],
                        'pedido_id_bling' => $resp['data']['id'],
                        'erro' => $e->getMessage()
                    ]);
                }
                file_put_contents($logFile, date('Y-m-d H:i:s') . " | Pedido criado com sucesso no Bling: " . $resp['data']['id'] . "\n", FILE_APPEND);
                return $resp['data'];
            }
            $this->logBlingError('Falha ao criar pedido de venda no Bling', $payload);
            $msg = 'Erro ao criar pedido de venda no Bling. Veja detalhes no log.';
            if (!headers_sent()) {
                header('X-Bling-Error: ' . rawurlencode($msg));
            }
            echo "<script>console.error('[Bling] Erro: " . addslashes($msg) . "');</script>";
            throw new Exception($msg);
        } catch (\Throwable $e) {
            file_put_contents($logFile, date('Y-m-d H:i:s') . " | Exceção em criarPedidoVenda: " . $e->getMessage() . "\n", FILE_APPEND);
            // Exibe erro no console do navegador (se for chamada via AJAX)
            $msg = '[Bling] Exceção: ' . $e->getMessage();
            if (!headers_sent()) {
                header('X-Bling-Error: ' . rawurlencode($msg));
            }
            echo "<script>console.error('" . addslashes($msg) . "');</script>";
            throw $e;
        }
    }

    // ...restante dos métodos já definidos acima...

    /**
     * Emite uma nota fiscal eletrônica via Bling
     */
    public function emitirNotaFiscal($orderData) {
        // Buscar itens reais do pedido no banco
        $pdo = null;
        if (isset($GLOBALS['pdo'])) {
            $pdo = $GLOBALS['pdo'];
        } else {
            $paths = [
                __DIR__ . '/../../../adminView/config/dbconnect.php',
                __DIR__ . '/../../adminView/config/dbconnect.php',
                __DIR__ . '/../../../config/dbconnect.php',
                __DIR__ . '/../../config/dbconnect.php',
            ];
            foreach ($paths as $path) {
                if (file_exists($path)) {
                    include_once $path;
                    if (isset($pdo)) break;
                }
            }
        }
        if (!$pdo) {
            $this->logBlingError('Não foi possível obter conexão PDO para buscar itens do pedido.', $orderData);
            throw new Exception('Erro interno ao buscar itens do pedido. Verifique o log.');
        }

        $orderId = $orderData['id'];
        // Descobre os campos existentes na tabela produtos
        $columns = $pdo->query("SHOW COLUMNS FROM produtos")->fetchAll(PDO::FETCH_COLUMN);
        $fields = [];
        // SKU
        $fields[] = in_array('sku', $columns) ? 'p.sku' : 'p.id as sku';
        // Campos opcionais
        foreach ([
            'nome', 'preco', 'unidade', 'pesoBruto', 'pesoLiquido', 'largura', 'altura', 'profundidade', 'volumes',
            'ncm', 'cfop', 'cest', 'codigoServico', 'origem', 'informacoesAdicionais', 'unidadeMedida'
        ] as $col) {
            if (in_array($col, $columns)) {
                $fields[] = "p.$col";
            }
        }
        $sql = "SELECT oi.*, " . implode(', ', $fields) . " FROM order_items oi JOIN produtos p ON oi.product_id = p.id WHERE oi.order_id = :order_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':order_id' => $orderId]);
        $itensPedido = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$itensPedido || count($itensPedido) === 0) {
            $this->logBlingError('Nenhum item encontrado para o pedido.', $orderData);
            throw new Exception('Pedido sem itens. Não é possível emitir a nota fiscal. Verifique o log.');
        }

        // Busca CPF/CNPJ do cliente em vários formatos
        $cpfCnpj = '';
        if (isset($orderData['cpf']) && !empty($orderData['cpf'])) {
            $cpfCnpj = preg_replace('/\D/', '', $orderData['cpf']);
        } elseif (isset($orderData['cnpj']) && !empty($orderData['cnpj'])) {
            $cpfCnpj = preg_replace('/\D/', '', $orderData['cnpj']);
        } elseif (isset($orderData['cliente']['cpf_cnpj']) && !empty($orderData['cliente']['cpf_cnpj'])) {
            $cpfCnpj = preg_replace('/\D/', '', $orderData['cliente']['cpf_cnpj']);
        }

        // ...

        // Garante cliente e produtos no Bling
        $this->garantirClienteBling($orderData);
        $this->garantirProdutosBling($itensPedido);

        // Detecta tipo de pessoa e documento
        $documento = $cpfCnpj;
        $tipoPessoa = 'J';
        if (strlen($documento) === 11) $tipoPessoa = 'F';
        if (strlen($documento) === 14) $tipoPessoa = 'J';

        // Busca valor do frete
    }








    /**
     * Garante que o cliente existe no Bling, criando se necessário
     */
    private function garantirClienteBling($orderData) {
        $cpf = preg_replace('/\D/', '', $orderData['cpf'] ?? '');
        // Só prossegue se CPF for válido
        if (!$this->isCpfValido($cpf)) {
            $this->logBlingError('CPF inválido ao tentar criar cliente no Bling', $orderData);
            throw new Exception('CPF do cliente inválido para integração com o Bling.');
        }
        $response = $this->request('/contatos?cpfCnpj=' . $cpf, 'GET');
        if (!empty($response['data'][0]['id'])) {
            return $response['data'][0]['id'];
        }
        $payload = [
            'nome' => $orderData['name'],
            'email' => $orderData['email'],
            'cpfCnpj' => $cpf,
            'telefone' => $orderData['telefone']
        ];
        // Remove qualquer campo cnpj/cpfCnpj se não for CPF válido (garantia extra)
        if (strlen($cpf) !== 11 || !$this->isCpfValido($cpf)) {
            unset($payload['cpfCnpj']);
        }
        $resp = $this->request('/contatos', 'POST', $payload);
        if (!empty($resp['data']['id'])) {
            return $resp['data']['id'];
        }
        $this->logBlingError('Falha ao criar cliente no Bling', $payload);
        throw new Exception('Erro ao criar cliente no Bling. Veja detalhes no log.');
    }

    private function garantirProdutosBling($itensPedido) {
        foreach ($itensPedido as &$item) {
            $codigo = $item['sku'];
            $resp = $this->request('/produtos?codigo=' . urlencode($codigo), 'GET');
            if (empty($resp['data'][0]['id'])) {
                $payload = [
                    'nome' => $item['nome'],
                    'preco' => $item['preco'] ?? $item['valor'] ?? 0,
                    'codigo' => $codigo,
                    'tipo' => 'P',
                    'un' => 'un',
                    'formato' => 'S',
                    'tipoEstoque' => 'F'
                ];
                $create = $this->request('/produtos', 'POST', $payload);
                if (empty($create['data']['id'])) {
                    $this->logBlingError('Falha ao criar produto no Bling', $payload);
                    throw new Exception('Erro ao criar produto no Bling. Veja detalhes no log.');
                }
            }
        }
    }

    /**
     * Registra erros de integração Bling em arquivo de log
     */
    private function logBlingError($mensagem, $dados = null) {
        // Caminho absoluto seguro para log, ajustado para ambiente de produção
        $logDir = $_SERVER['DOCUMENT_ROOT'] . '/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . '/bling_nfe_errors.log';
        $data = date('Y-m-d H:i:s');
        $conteudo = "[$data] $mensagem\n";
        if ($dados) {
            $conteudo .= "Dados: " . print_r($dados, true) . "\n";
        }
        $conteudo .= "-----------------------------\n";
        // Tenta gravar o log, mas ignora erro de permissão
        @file_put_contents($logFile, $conteudo, FILE_APPEND);
    }

    /**
     * Consulta uma nota fiscal pelo número e série
     */
    public function consultarNotaFiscal($numero, $serie) {
        $response = $this->request("/notasfiscais?numero={$numero}&serie={$serie}", 'GET');
        if (empty($response['data'][0]['chaveAcesso'])) {
            throw new Exception('Nota fiscal não encontrada no Bling.');
        }
        $nfe = $response['data'][0];
        return [
            'status' => $nfe['status'],
            'data_emissao' => $nfe['dataEmissao'],
            'pdf_url' => $nfe['urlDanfePdf'] ?? null,
            'chaveAcesso' => $nfe['chaveAcesso'] ?? null
        ];
    }

    /**
     * Obtém o PDF da nota fiscal pela chave de acesso
     */
    public function obterPdfNotaFiscal($chaveAcesso) {
        $response = $this->request("/notasfiscais/{$chaveAcesso}/pdf", 'GET');
        if (empty($response['data']['urlDanfePdf'])) {
            throw new Exception('PDF da nota fiscal não encontrado.');
        }
        return $response['data']['urlDanfePdf'];
    }

    /**
     * Função genérica para requisições à API do Bling
     */
    private function request($endpoint, $method = 'GET', $data = null) {
        $url = $this->apiUrl . $endpoint;
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $this->accessToken
        ];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }
        $result = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (curl_errno($curl)) {
            $this->logBlingError('Erro de comunicação com o Bling', curl_error($curl));
            throw new Exception('Erro de comunicação com o Bling: ' . curl_error($curl));
        }
        curl_close($curl);
        $response = json_decode($result, true);
        if ($httpCode >= 400) {
            $this->logBlingError('Erro na API do Bling', [
                'endpoint' => $endpoint,
                'method' => $method,
                'data' => $data,
                'httpCode' => $httpCode,
                'response' => $response
            ]);
            throw new Exception('Erro na API do Bling: ' . ($response['error']['message'] ?? 'Erro desconhecido'));
        }
        return $response;
    }
}
