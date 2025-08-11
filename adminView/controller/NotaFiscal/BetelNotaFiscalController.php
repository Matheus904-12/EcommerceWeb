<?php
require_once __DIR__ . '/../../config/dbconnect.php';
require_once __DIR__ . '/../../models/BlingIntegration.php';


class BlingNotaFiscalController {
    private $conn;
    private $blingIntegration;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->blingIntegration = new BlingIntegration();
    }

    public function emitirNotaFiscal($orderId) {
        $logDir = realpath(__DIR__ . '/../../../Site/logs');
        if ($logDir === false) {
            $logDir = __DIR__ . '/../../../Site/logs';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, true);
            }
        }
        $blingLogFile = $logDir . '/bling_api.log';
        if (!is_dir(dirname($blingLogFile))) {
            mkdir(dirname($blingLogFile), 0777, true);
        }
        try {
            $orderData = $this->getOrderData($orderId);
            // Log detalhado dos dados do pedido
            file_put_contents($blingLogFile, date('Y-m-d H:i:s') . " [Bling] Debug orderData: " . json_encode($orderData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
            if (!$orderData) {
                throw new Exception("Pedido com ID $orderId não encontrado.");
            }
            if (!empty($orderData['nfe_key'])) {
                throw new Exception("Nota fiscal já emitida para o pedido $orderId.");
            }

            // Validação extra dos campos obrigatórios
            $camposObrigatorios = ['name','cpf','email','telefone','items'];
            foreach ($camposObrigatorios as $campo) {
                if (empty($orderData[$campo])) {
                    file_put_contents($blingLogFile, date('Y-m-d H:i:s') . " [Bling] Campo obrigatório ausente: $campo\n", FILE_APPEND);
                }
            }
            if (empty($orderData['items']) || !is_array($orderData['items']) || count($orderData['items']) === 0) {
                file_put_contents($blingLogFile, date('Y-m-d H:i:s') . " [Bling] Nenhum item encontrado para o pedido $orderId\n", FILE_APPEND);
            }

            // Monta dados do cliente e produtos conforme esperado pela API do Bling
            $clienteData = [
                'nome' => $orderData['name'],
                'cpf_cnpj' => preg_replace('/[^0-9]/', '', $orderData['cpf']),
                'endereco' => $orderData['shipping_address'],
                'numero' => $orderData['shipping_number'] ?? 'S/N',
                'bairro' => $orderData['shipping_neighborhood'] ?? '',
                'cep' => preg_replace('/[^0-9]/', '', $orderData['shipping_cep'] ?? ''),
                'cidade' => $orderData['shipping_city'] ?? '',
                'uf' => $orderData['shipping_state'] ?? '',
                'email' => $orderData['email'],
                'telefone' => preg_replace('/[^0-9]/', '', $orderData['telefone'])
            ];

            $itens = array_map(function($item) {
                return [
                    'codigo' => $item['codigo'],
                    'descricao' => $item['descricao'],
                    'quantidade' => floatval($item['quantidade']),
                    'valor' => floatval($item['valor_unitario'])
                ];
            }, $orderData['items'] ?? []);

            // Log detalhado dos itens
            file_put_contents($blingLogFile, date('Y-m-d H:i:s') . " [Bling] Itens do pedido: " . json_encode($itens, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

            $nfeData = [
                'tipo' => 1, // NF-e
                'numero' => $orderId,
                'dataOperacao' => date('Y-m-d H:i:s'),
                'cliente' => $clienteData,
                'naturezaOperacao' => [ 'id' => 1 ], // Ajuste conforme cadastro no Bling
                'itens' => $itens,
                'valor' => array_reduce($itens, function($soma, $item) { return $soma + $item['valor'] * $item['quantidade']; }, 0)
                // Adicione outros campos obrigatórios do Bling aqui
            ];

            // Log do payload enviado
            file_put_contents($blingLogFile, date('Y-m-d H:i:s') . ' [Bling] Payload enviado para emitir NFe: ' . json_encode($nfeData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

            // Chama integração Bling
            $blingResponse = $this->blingIntegration->emitirNotaFiscal($nfeData);
            file_put_contents($blingLogFile, date('Y-m-d H:i:s') . ' [Bling] Resposta ao emitir NFe: ' . json_encode($blingResponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

            if (isset($blingResponse['error'])) {
                throw new Exception('Erro da API Bling ao emitir nota: ' . $blingResponse['error']);
            }

            $nfeInfo = [
                'chave' => $blingResponse['chave'] ?? null,
                'numero' => $blingResponse['numero'] ?? null,
                'serie' => $blingResponse['serie'] ?? null,
                'pdf_url' => $blingResponse['pdf_url'] ?? null,
                'data_emissao' => $blingResponse['data_emissao'] ?? null
            ];
            $this->updateOrderWithNfeData($orderId, $nfeInfo);
            $this->logNfeEvent($orderId, 'emissao', 'success', 'Nota fiscal emitida com sucesso', $orderData, $nfeInfo);

            return [
                'status' => 'success',
                'message' => 'Nota fiscal emitida com sucesso',
                'data' => $nfeInfo
            ];
        } catch (Exception $e) {
            $this->logNfeEvent($orderId, 'emissao', 'error', $e->getMessage(), isset($orderData) ? $orderData : null, null);
            throw $e;
        }
    }

    public function consultarNotaFiscal($orderId) {
        $blingLogFile = __DIR__ . '/../logs/bling/bling_api.log';
        try {
            $orderData = $this->getOrderData($orderId);
            if (!$orderData || empty($orderData['nfe_key'])) {
                throw new Exception("Nenhuma nota fiscal encontrada para o pedido $orderId.");
            }
            $nfeInfo = $this->blingIntegration->consultarNotaFiscal($orderData['nfe_key']);
            $this->updateOrderWithNfeStatus($orderId, $nfeInfo);
            $this->logNfeEvent($orderId, 'consulta', 'success', 'Status da nota fiscal atualizado', $orderData, $nfeInfo);
            return [
                'status' => 'success',
                'message' => 'Status da nota fiscal atualizado',
                'data' => $nfeInfo
            ];
        } catch (Exception $e) {
            $this->logNfeEvent($orderId, 'consulta', 'error', $e->getMessage(), isset($orderData) ? $orderData : null, null);
            throw $e;
        }
    }

    
    public function obterPdfNotaFiscal($orderId) {
        $blingLogFile = __DIR__ . '/../logs/bling/bling_api.log';
        try {
            $orderData = $this->getOrderData($orderId);
            if (!$orderData || empty($orderData['nfe_key'])) {
                throw new Exception("Nenhuma nota fiscal encontrada para o pedido $orderId.");
            }
            if (!empty($orderData['nfe_pdf_url'])) {
                return [
                    'status' => 'success',
                    'message' => 'URL do PDF da nota fiscal recuperada com sucesso',
                    'data' => ['pdf_url' => $orderData['nfe_pdf_url']]
                ];
            }
            $pdfUrl = $this->blingIntegration->obterPdfNotaFiscal($orderData['nfe_key']);
            $sql = "UPDATE orders SET nfe_pdf_url = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar atualização SQL: " . $this->conn->error);
            }
            $stmt->bind_param("si", $pdfUrl, $orderId);
            if (!$stmt->execute()) {
                throw new Exception("Erro ao atualizar URL do PDF: " . $stmt->error);
            }
            $stmt->close();
            $this->logNfeEvent($orderId, 'pdf', 'success', 'PDF da nota fiscal obtido com sucesso', $orderData, ['pdf_url' => $pdfUrl]);
            return [
                'status' => 'success',
                'message' => 'PDF da nota fiscal obtido com sucesso',
                'data' => ['pdf_url' => $pdfUrl]
            ];
        } catch (Exception $e) {
            $this->logNfeEvent($orderId, 'pdf', 'error', $e->getMessage(), isset($orderData) ? $orderData : null, null);
            throw $e;
        }
    }

    public function getOrderDetails($orderId) {
        return $this->getOrderData($orderId);
    }

     // NOVO MÉTODO: Criar pedido de venda no Bling conforme especificação do arquiteto
    public function criarPedidoBling($orderId) {
        $logDir = realpath(__DIR__ . '/../../../Site/logs');
        if ($logDir === false) {
            $logDir = __DIR__ . '/../../../Site/logs';
            if (!is_dir($logDir)) mkdir($logDir, 0777, true);
        }
        $blingLogFile = $logDir . '/bling_api.log';
        if (!is_dir(dirname($blingLogFile))) mkdir(dirname($blingLogFile), 0777, true);
        try {
            $orderData = $this->getOrderData($orderId);
            if (!$orderData) throw new Exception('Pedido não encontrado');
                // Busca cliente na tabela usuarios
                $pdo = $this->conn;
                $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
            $stmt->bind_param("i", $orderData['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $cliente = $result->fetch_assoc();
            $stmt->close();
            // Se não tem id_externo_bling, cria contato no Bling
            if (empty($cliente['id_externo_bling'])) {
                $clienteData = [
                    'nome' => $orderData['name'],
                    'codigo' => 'U' . $orderData['user_id'],
                    'numeroDocumento' => $orderData['cpf'],
                    'telefone' => $orderData['telefone'],
                    'email' => $orderData['email'],
                    'cliente_id' => $cliente['customer_id'],
                    // Adapte outros campos conforme necessário
                ];
                $id_externo = $this->blingIntegration->criarContato($clienteData, $pdo);
                $cliente['id_externo_bling'] = $id_externo;
            }
            // Monta itens do pedido
            $itens = [];
            foreach ($orderData['items'] as $item) {
                $itens[] = [
                    'codigo' => $item['product_id'],
                    'unidade' => 'UN',
                    'quantidade' => $item['quantity'],
                    'desconto' => 0,
                    'situacao' => 'A',
                    'ncm' => $item['ncm'] ?? '70139110',
                    'numeroPedidoCompra' => $orderData['id'],
                    'classificacaoFiscal' => $item['classificacaoFiscal'] ?? '9999.99.99',
                    'cest' => $item['cest'] ?? '99.999.99',
                    'cfop' => $item['cfop'] ?? '5102',
                    'valor' => $item['price'],
                    'aliquotaIPI' => 0,
                    'descricao' => $item['name'] ?? 'Produto',
                    'descricaoDetalhada' => $item['descricaoDetalhada'] ?? 'Brinde'
                ];
            }
            // Monta payload do pedido
            $pedidoData = [
                'numero' => $orderData['id'],
                'data' => date('Y-m-d'),
                'dataSaida' => date('Y-m-d'),
                'dataPrevista' => date('Y-m-d'),
                'contato_id' => $cliente['id_externo_bling'],
                'loja_id' => 205510600,
                'numeroPedidoCompra' => $orderData['id'],
                'outrasDespesas' => 0,
                'observacoes' => '',
                'observacoesInternas' => '',
                'desconto' => [ 'valor' => 0, 'unidade' => 'REAL' ],
                'tributacao' => [ 'totalICMS' => 0, 'totalIPI' => 0 ],
                'itens' => $itens,
                'pagamento' => [
                    'formaPagamento' => 'A vista',
                    'parcelas' => '1x'
                ],
                'taxas' => [ 'taxaComissao' => 0, 'custoFrete' => 0, 'valorBase' => 0 ]
            ];
            // Log do payload
            file_put_contents($blingLogFile, date('Y-m-d H:i:s') . ' [Bling] Payload pedido venda: ' . json_encode($pedidoData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
            // Chama integração para criar pedido
            $blingResponse = $this->blingIntegration->criarPedidoVenda($pedidoData, $pdo);
            file_put_contents($blingLogFile, date('Y-m-d H:i:s') . ' [Bling] Resposta pedido venda: ' . json_encode($blingResponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
            if (isset($blingResponse['error'])) throw new Exception($blingResponse['error']);
            return [
                'status' => 'success',
                'message' => 'Pedido criado no Bling com sucesso',
                'data' => $blingResponse
            ];
        } catch (Exception $e) {
            file_put_contents($blingLogFile, date('Y-m-d H:i:s') . ' [Bling] Erro ao criar pedido: ' . $e->getMessage() . "\n", FILE_APPEND);
            throw $e;
        }
    }
    // ...existing code...

    // Métodos utilitários necessários para funcionamento do controller
    private function getOrderData($orderId) {
        $sql = "
            SELECT o.*, u.name, u.email, u.cpf, u.telefone
            FROM orders o
            JOIN usuarios u ON o.user_id = u.id
            WHERE o.id = ?
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Erro ao preparar consulta SQL: " . $this->conn->error);
        }

        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return null;
        }

        $orderData = $result->fetch_assoc();
        $stmt->close();

        $sqlItems = "SELECT * FROM order_items WHERE order_id = ?";
        $stmtItems = $this->conn->prepare($sqlItems);
        if (!$stmtItems) {
            throw new Exception("Erro ao preparar consulta SQL para itens: " . $this->conn->error);
        }

        $stmtItems->bind_param("i", $orderId);
        $stmtItems->execute();
        $itemsResult = $stmtItems->get_result();
        $orderData['items'] = $itemsResult->fetch_all(MYSQLI_ASSOC);
        $stmtItems->close();

        return $orderData;
    }

    private function updateOrderWithNfeData($orderId, $nfeInfo) {
        $sql = "
            UPDATE orders
            SET nfe_key = ?, nfe_number = ?, nfe_series = ?, nfe_status = 'autorizada',
                nfe_issue_date = ?, nfe_pdf_url = ?
            WHERE id = ?
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Erro ao preparar atualização SQL: " . $this->conn->error);
        }

        $stmt->bind_param(
            "sssssi",
            $nfeInfo['chave'],
            $nfeInfo['numero'],
            $nfeInfo['serie'],
            $nfeInfo['data_emissao'],
            $nfeInfo['pdf_url'],
            $orderId
        );

        if (!$stmt->execute()) {
            throw new Exception("Erro ao atualizar pedido com dados da NF-e: " . $stmt->error);
        }

        $stmt->close();
    }

    private function updateOrderWithNfeStatus($orderId, $nfeInfo) {
        $sql = "
            UPDATE orders
            SET nfe_status = ?, nfe_issue_date = ?
            WHERE id = ?
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Erro ao preparar atualização de status SQL: " . $this->conn->error);
        }

        $stmt->bind_param(
            "ssi",
            $nfeInfo['status'],
            $nfeInfo['data_emissao'],
            $orderId
        );

        if (!$stmt->execute()) {
            throw new Exception("Erro ao atualizar status do pedido: " . $stmt->error);
        }

        $stmt->close();
    }

    private function logNfeEvent($orderId, $eventType, $status, $message, $requestData, $responseData) {
        $sql = "
            INSERT INTO nfe_logs (order_id, event_type, status, message, request_data, response_data, provider, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'bling', NOW())
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Erro ao preparar inserção de log: " . $this->conn->error);
            return;
        }

        $requestDataJson = json_encode($requestData);
        $responseDataJson = $responseData ? json_encode($responseData) : null;

        $stmt->bind_param(
            "isssss",
            $orderId,
            $eventType,
            $status,
            $message,
            $requestDataJson,
            $responseDataJson
        );

        if (!$stmt->execute()) {
            error_log("Erro ao inserir log: " . $stmt->error);
        }

        $stmt->close();
    }

    // Não é mais necessário mapear cidade para ID, Bling usa nome/UF
}