<?php
class BlingIntegrationController {
    private $apiUrl = 'https://api.bling.com.br/Api/v3';
    private $apiKey;
    private $conn;
    private $lojaId = 205510600; // ID fixo da loja conforme especificado

    public function __construct($conn, $apiKey) {
        $this->conn = $conn;
        $this->apiKey = $apiKey;
    }

    // Método para criar contato no Bling
    public function criarContato($userData) {
        $endpoint = '/contatos';
        
        $contatoData = [
            'nome' => $userData['name'],
            'codigo' => 'CLI' . str_pad($userData['id'], 6, '0', STR_PAD_LEFT),
            'situacao' => 'A',
            'numeroDocumento' => preg_replace('/[^0-9]/', '', $userData['cpf']),
            'telefone' => $userData['telefone'] ?? '',
            'celular' => $userData['celular'] ?? '',
            'tipo' => 'F', // Pessoa Física
            'indicadorIe' => 9, // Não contribuinte
            'email' => $userData['email'],
            'endereco' => [
                'geral' => [
                    'endereco' => $userData['endereco'],
                    'numero' => $userData['numero'],
                    'complemento' => $userData['complemento'] ?? '',
                    'bairro' => $userData['bairro'],
                    'cep' => preg_replace('/[^0-9]/', '', $userData['cep']),
                    'municipio' => $userData['cidade'],
                    'uf' => $userData['estado']
                ]
            ]
        ];

        try {
            $response = $this->sendRequest('POST', $endpoint, $contatoData);
            if (isset($response['data']['id'])) {
                // Atualiza o ID externo do cliente no banco de dados
                $stmt = $this->conn->prepare("UPDATE usuarios SET id_externo_bling = ? WHERE id = ?");
                $stmt->bind_param("si", $response['data']['id'], $userData['id']);
                $stmt->execute();
                return $response['data']['id'];
            }
            throw new Exception("Erro ao criar contato no Bling: Resposta inválida");
        } catch (Exception $e) {
            error_log("Erro ao criar contato no Bling: " . $e->getMessage());
            throw $e;
        }
    }

    // Método para criar pedido no Bling
    public function criarPedido($orderData, $items) {
        $endpoint = '/pedidos/vendas';
        
        // Busca o próximo número de pedido
        $numeroPedido = $this->getNextPedidoNumero();
        
        // Prepara os itens do pedido
        $blingItems = [];
        foreach ($items as $item) {
            $blingItems[] = [
                'codigo' => $item['produto_id'],
                'unidade' => 'UN',
                'quantidade' => $item['quantidade'],
                'valor' => $item['preco'],
                'descricao' => $item['nome'],
                'ncm' => '70139110', // NCM padrão - ajuste conforme necessário
                'cfop' => '5102'  // CFOP padrão para vendas
            ];
        }

        $pedidoData = [
            'numero' => $numeroPedido,
            'data' => date('Y-m-d'),
            'dataSaida' => date('Y-m-d', strtotime('+10 days')),
            'dataPrevista' => date('Y-m-d', strtotime('+10 days')),
            'contato' => [
                'id' => $orderData['id_externo_bling']
            ],
            'loja' => [
                'id' => $this->lojaId
            ],
            'itens' => $blingItems,
            'pagamento' => [
                'formaPagamento' => $this->mapPaymentMethod($orderData['payment_method']),
                'parcelas' => '1x'
            ]
        ];

        try {
            $response = $this->sendRequest('POST', $endpoint, $pedidoData);
            if (isset($response['data']['id'])) {
                // Atualiza o número do pedido na tabela de controle
                $this->updatePedidoNumero($numeroPedido);
                return $response['data'];
            }
            throw new Exception("Erro ao criar pedido no Bling: Resposta inválida");
        } catch (Exception $e) {
            error_log("Erro ao criar pedido no Bling: " . $e->getMessage());
            throw $e;
        }
    }

    // Método auxiliar para enviar requisições
    private function sendRequest($method, $endpoint, $data = null) {
        $ch = curl_init();
        $url = $this->apiUrl . $endpoint;
        
        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json'
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
        
        curl_close($ch);
        
        if ($httpCode >= 400) {
            throw new Exception("Erro na requisição: HTTP $httpCode - $response");
        }

        return json_decode($response, true);
    }

    // Método para obter próximo número de pedido
    private function getNextPedidoNumero() {
        $stmt = $this->conn->prepare("SELECT numero FROM bling_pedidos_contador LIMIT 1");
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // Se não existir registro, cria um começando do 1
            $stmt = $this->conn->prepare("INSERT INTO bling_pedidos_contador (numero) VALUES (1)");
            $stmt->execute();
            return 1;
        }
        
        $row = $result->fetch_assoc();
        return $row['numero'] + 1;
    }

    // Método para atualizar o número do pedido
    private function updatePedidoNumero($numero) {
        $stmt = $this->conn->prepare("UPDATE bling_pedidos_contador SET numero = ?");
        $stmt->bind_param("i", $numero);
        $stmt->execute();
    }

    // Método para mapear métodos de pagamento
    private function mapPaymentMethod($method) {
        $methods = [
            'credit_card' => 'Cartão de Crédito',
            'boleto' => 'Boleto',
            'pix' => 'Pix',
            'default' => 'A vista'
        ];
        
        return $methods[$method] ?? $methods['default'];
    }
}
