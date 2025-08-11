<?php
/**
 * BlingPayloadMapper.php
 * Centraliza o mapeamento de dados do banco para o payload do Bling (cliente e pedido)
 */

class BlingPayloadMapper
{
    /**
     * Loga erros de integração Bling em arquivo JSON, sempre criando o arquivo.
     * @param string $msg
     * @param array $context
     */
    private static function logBlingError($msg, $context = []) {
        $logPath = __DIR__ . '/erro_id_externo_bling.json';
        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'msg' => $msg,
            'context' => $context
        ];
        $log = [];
        if (file_exists($logPath)) {
            $old = @file_get_contents($logPath);
            if ($old) {
                $log = json_decode($old, true) ?: [];
            }
        }
        $log[] = $entry;
        // Tenta gravar, se falhar, tenta chmod e grava de novo
        $ok = @file_put_contents($logPath, json_encode($log, JSON_PRETTY_PRINT));
        if ($ok === false) {
            @chmod(__DIR__, 0777);
            @file_put_contents($logPath, json_encode($log, JSON_PRETTY_PRINT));
        }
    }
    /**
     * Monta o payload de contato (cliente) para o Bling a partir dos dados do banco
     * @param array $customer Dados do cliente (ex: customers, usuarios)
     * @return array
     */
    public static function mapCustomerToBling(array $customer): array
    {
        // Sempre pessoa física, só CPF, nunca CNPJ
        $cpf = $customer['cpf'] ?? $customer['numeroDocumento'] ?? '';
        $cpf = preg_replace('/\D/', '', $cpf); // Remove tudo que não for número
        if (strlen($cpf) !== 11) {
            $cpf = '';
        }
        // Garante que o campo 'codigo' nunca fique vazio: usa o id do usuário como fallback
        $codigo = $customer['codigo'] ?? $customer['customer_id'] ?? $customer['id'] ?? null;
        if (empty($codigo)) {
            // fallback para CPF se não houver id
            $codigo = $cpf;
        }
        return [
            'nome' => $customer['customer_name'] ?? $customer['nome'] ?? '',
            'codigo' => (string)$codigo,
            'situacao' => 'A',
            'numeroDocumento' => $cpf,
            'telefone' => $customer['phone'] ?? $customer['telefone'] ?? '',
            'celular' => $customer['celular'] ?? '',
            'fantasia' => $customer['fantasia'] ?? '',
            'tipo' => 'F', // Sempre pessoa física
            'indicadorIe' => $customer['indicadorIe'] ?? 9,
            'ie' => $customer['ie'] ?? null,
            'rg' => $customer['rg'] ?? '',
            'inscricaoMunicipal' => $customer['inscricaoMunicipal'] ?? '',
            'orgaoEmissor' => $customer['orgaoEmissor'] ?? '',
            'email' => $customer['email'] ?? '',
            'endereco' => [
                'geral' => [
                    'endereco' => $customer['endereco'] ?? $customer['shipping_address'] ?? '',
                    'cep' => $customer['cep'] ?? $customer['shipping_cep'] ?? '',
                    'bairro' => $customer['bairro'] ?? '',
                    'municipio' => $customer['municipio'] ?? '',
                    'uf' => $customer['uf'] ?? '',
                    'numero' => $customer['numero'] ?? $customer['shipping_number'] ?? '',
                    'complemento' => $customer['complemento'] ?? $customer['shipping_complement'] ?? '',
                ],
                'cobranca' => [
                    'endereco' => $customer['endereco_cobranca'] ?? $customer['shipping_address'] ?? '',
                    'cep' => $customer['cep_cobranca'] ?? $customer['shipping_cep'] ?? '',
                    'bairro' => $customer['bairro_cobranca'] ?? '',
                    'municipio' => $customer['municipio_cobranca'] ?? '',
                    'uf' => $customer['uf_cobranca'] ?? '',
                    'numero' => $customer['numero_cobranca'] ?? $customer['shipping_number'] ?? '',
                    'complemento' => $customer['complemento_cobranca'] ?? $customer['shipping_complement'] ?? '',
                ],
            ],
            'vendedor' => [
                'id' => $customer['vendedor_id'] ?? null
            ],
            'dadosAdicionais' => [
                'dataNascimento' => $customer['data_nascimento'] ?? '',
                'sexo' => $customer['sexo'] ?? '',
                'naturalidade' => $customer['naturalidade'] ?? ''
            ],
            'financeiro' => [
                'limiteCredito' => $customer['limite_credito'] ?? 0,
                'condicaoPagamento' => $customer['condicao_pagamento'] ?? '',
                'categoria' => [
                    'id' => $customer['categoria_id'] ?? null
                ]
            ],
            'pais' => [
                'nome' => $customer['pais'] ?? 'BRASIL'
            ],
            'tiposContato' => $customer['tiposContato'] ?? [],
            'pessoasContato' => $customer['pessoasContato'] ?? []
        ];
    }

    /**
     * Monta o payload de pedido para o Bling a partir dos dados do banco
     * @param array $order Dados do pedido (orders)
     * @param array $customer Dados do cliente (customers)
     * @param array $itens Lista de itens do pedido (itens_pedido)
     * @param array $config Configurações fixas (ex: loja_id)
     * @return array
     */
    public static function mapOrderToBling(array $order, array $customer, array $itens, array $config = []): array
    {
        // Validação do id_externo_bling antes de montar o payload
        if (empty($customer['id_externo_bling']) || !is_numeric($customer['id_externo_bling'])) {
            // Tenta buscar novamente do banco se possível
            if (!empty($customer['id'])) {
                $conn = null;
                if (isset($GLOBALS['conn'])) {
                    $conn = $GLOBALS['conn'];
                } elseif (function_exists('getDbConnection')) {
                    $conn = getDbConnection();
                }
                if ($conn) {
                    $stmt = $conn->prepare('SELECT id_externo_bling FROM usuarios WHERE id = ?');
                    $stmt->bind_param('i', $customer['id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($row = $result->fetch_assoc()) {
                        if (!empty($row['id_externo_bling']) && is_numeric($row['id_externo_bling'])) {
                            $customer['id_externo_bling'] = $row['id_externo_bling'];
                        }
                    }
                    $stmt->close();
                }
            }
            if (empty($customer['id_externo_bling']) || !is_numeric($customer['id_externo_bling'])) {
                $msg = 'Cliente não possui id_externo_bling válido. Pedido não será enviado ao Bling.';
                $msg .= '<br><b>Debug:</b> id_externo_bling=' . ($customer['id_externo_bling'] ?? 'null') . ', id=' . ($customer['id'] ?? 'null');
                $msg .= '<br>Verifique se o cadastro do cliente está correto e sincronizado com o Bling.';
                throw new \Exception($msg);
            }
        }
        return [
            'numero' => $order['numero'] ?? $order['id'] ?? '',
            'data' => $order['data'] ?? $order['order_date'] ?? date('Y-m-d'),
            'dataSaida' => $order['data_saida'] ?? $order['order_date'] ?? date('Y-m-d'),
            'dataPrevista' => $order['data_prevista'] ?? $order['order_date'] ?? date('Y-m-d'),
            'contato' => [
                'id' => (int)$customer['id_externo_bling']
            ],
            'loja' => [
                'id' => $config['loja_id'] ?? 205510600
            ],
            'numeroPedidoCompra' => $order['numeroPedidoCompra'] ?? '',
            'outrasDespesas' => $order['outrasDespesas'] ?? 0,
            'observacoes' => $order['observacoes'] ?? '',
            'observacoesInternas' => $order['observacoesInternas'] ?? '',
            'desconto' => [
                'valor' => $order['desconto_valor'] ?? $order['discount'] ?? 0,
                'unidade' => $order['desconto_unidade'] ?? 'REAL'
            ],
            'tributacao' => [
                'totalICMS' => $order['totalICMS'] ?? 0,
                'totalIPI' => $order['totalIPI'] ?? 0
            ],
            'itens' => array_map(function($item) use ($config) {
                return [
                    'codigo' => $item['codigo'] ?? $item['product_id'] ?? '',
                    'unidade' => $item['unidade'] ?? 'UN',
                    'quantidade' => $item['quantidade'] ?? $item['quantity'] ?? 1,
                    'desconto' => $item['desconto'] ?? 0,
                    'situacao' => $item['situacao'] ?? 'A',
                    'ncm' => $item['ncm'] ?? ($config['default_ncm'] ?? ''),
                    'numeroPedidoCompra' => $item['numeroPedidoCompra'] ?? '',
                    'classificacaoFiscal' => $item['classificacaoFiscal'] ?? '',
                    'cest' => $item['cest'] ?? '',
                    'cfop' => $item['cfop'] ?? ($config['default_cfop'] ?? ''),
                    'valor' => $item['valor'] ?? $item['price'] ?? 0,
                    'aliquotaIPI' => $item['aliquotaIPI'] ?? 0,
                    'descricao' => $item['descricao'] ?? '',
                    'descricaoDetalhada' => $item['descricaoDetalhada'] ?? ''
                ];
            }, $itens),
            'pagamento' => [
                'formaPagamento' => $order['formaPagamento'] ?? $order['payment_method'] ?? '',
                'parcelas' => $order['parcelas'] ?? ($order['installments'] ?? '1x')
            ],
            'taxas' => [
                'taxaComissao' => $order['taxaComissao'] ?? 0,
                'custoFrete' => $order['custoFrete'] ?? $order['shipping'] ?? 0,
                'valorBase' => $order['valorBase'] ?? $order['subtotal'] ?? 0
            ]
        ];
    }
}
