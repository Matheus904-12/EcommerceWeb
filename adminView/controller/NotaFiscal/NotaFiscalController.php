<?php

// Verifica se as constantes de caminho foram definidas
if (!defined('PROJECT_ROOT')) {
    throw new Exception("PROJECT_ROOT não está definido");
}

if (!defined('INCLUDES_PATH')) {
    define('INCLUDES_PATH', PROJECT_ROOT . '/includes');
}

if (!defined('MODELS_PATH')) {
    define('MODELS_PATH', PROJECT_ROOT . '/models');
}

if (!defined('SITE_PATH')) {
    define('SITE_PATH', dirname(PROJECT_ROOT) . '/Site');
}

// Log do caminho atual para debug
error_log("Diretório atual: " . __DIR__);
error_log("PROJECT_ROOT: " . (defined('PROJECT_ROOT') ? PROJECT_ROOT : 'não definido'));
error_log("SITE_PATH: " . (defined('SITE_PATH') ? SITE_PATH : 'não definido'));

// Define o caminho base do sistema
$baseDir = dirname(dirname(dirname(__DIR__))); // Sobe 3 níveis até a raiz do projeto
error_log("Base directory: " . $baseDir);

// Define os caminhos dos arquivos
$blingServicePath = $baseDir . '/Site/includes/BlingService.php';
$blingIntegrationPath = $baseDir . '/adminView/models/BlingIntegration.php';
$blingOAuth2Path = $baseDir . '/adminView/includes/BlingOAuth2.php';

// Log dos caminhos para debug
error_log("BlingService.php path: " . $blingServicePath);
error_log("BlingIntegration.php path: " . $blingIntegrationPath);
error_log("BlingOAuth2.php path: " . $blingOAuth2Path);

// Verifica se o arquivo existe e é legível
if (is_readable($blingServicePath)) {
    error_log("BlingService.php é legível");
} else {
    error_log("BlingService.php não é legível ou não existe");
    error_log("Tentando listar diretório Site/includes:");
    if (is_dir($baseDir . '/Site/includes')) {
        error_log(print_r(scandir($baseDir . '/Site/includes'), true));
    } else {
        error_log("Diretório Site/includes não encontrado");
    }
}

// Verifica se os arquivos existem
if (!file_exists($blingServicePath)) {
    error_log("BlingService não encontrado em: " . $blingServicePath);
    throw new Exception("Arquivo BlingService.php não encontrado");
}

if (!file_exists($blingIntegrationPath)) {
    error_log("BlingIntegration não encontrado em: " . $blingIntegrationPath);
    throw new Exception("Arquivo BlingIntegration.php não encontrado");
}

if (!file_exists($blingOAuth2Path)) {
    error_log("BlingOAuth2 não encontrado em: " . $blingOAuth2Path);
    throw new Exception("Arquivo BlingOAuth2.php não encontrado");
}

// Carrega os arquivos
require_once $blingServicePath;
require_once $blingIntegrationPath;
require_once $blingOAuth2Path;

// Esta verificação foi removida pois já está sendo feita acima

class NotaFiscalController {
    private $blingService;
    private $blingIntegration;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $apiKey = getenv('BLING_API_KEY');
        $this->blingService = new BlingService($conn, $apiKey);
        $this->blingIntegration = new BlingIntegration();
    }

    // Alias para compatibilidade
    public function getOrderDetails($orderId) {
        return $this->getOrderData($orderId);
    }

    public function getOrderItems($orderId) {
        error_log("Buscando pedido #" . $orderId);
        
        // Query para buscar os dados do pedido
        $sql = "SELECT id, total, order_date FROM orders WHERE id = ?";
        
        error_log("Executando query: " . $sql . " com order_id = " . $orderId);
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            error_log("Pedido #" . $orderId . " não encontrado");
            return [];
        }
        
        $order = $result->fetch_assoc();
        $itens = [];
        
        // Se o pedido tem valor total, criamos um item genérico
        if ($order['total'] > 0) {
            $itens[] = [
                "codigo" => "PROD-" . $orderId,
                "descricao" => "Pedido #" . $orderId,
                "detalhes" => "Pedido realizado em " . date('d/m/Y', strtotime($order['order_date'])),
                "quantidade" => 1,
                "valor_unitario" => $order['total']
            ];
            
            error_log("Item criado para o pedido #" . $orderId . " com valor total: " . $order['total']);
        }
        while ($item = $result->fetch_assoc()) {
            $itens[] = [
                "codigo" => $item['product_id'],
                "descricao" => $item['nome'],
                "detalhes" => $item['descricao'],
                "quantidade" => $item['quantity'],
                "valor_unitario" => $item['price_at_purchase']
            ];
        }
        $stmt->close();
        return $itens;
    }

    public function emitirNotaFiscal($orderId) {
        try {
            // 1. Verifica e obtém os dados do pedido
            $orderData = $this->getOrderData($orderId);
            if (!empty($orderData['nfe_key'])) {
                throw new Exception("Este pedido já possui uma nota fiscal emitida.");
            }

            // 2. Obtém e verifica os itens do pedido
            $orderItems = $this->getOrderItems($orderId);
            if (empty($orderItems)) {
                throw new Exception("Pedido sem itens. Não é possível emitir a nota fiscal.");
            }

            // Log dos dados do pedido para debug
            error_log("Dados do pedido #" . $orderId . ": " . json_encode($orderData, JSON_UNESCAPED_UNICODE));
            error_log("Itens do pedido #" . $orderId . ": " . json_encode($orderItems, JSON_UNESCAPED_UNICODE));

            // 3. Verifica se o cliente tem ID no Bling
            if (empty($orderData['id_externo_bling'])) {
                // Verifica se todos os campos necessários estão preenchidos
                $camposObrigatorios = ['name', 'cpf', 'shipping_address', 'shipping_number', 'shipping_cep', 'telefone', 'email'];
                $camposFaltantes = [];
                foreach ($camposObrigatorios as $campo) {
                    if (empty($orderData[$campo])) {
                        $camposFaltantes[] = $campo;
                    }
                }
                
                if (!empty($camposFaltantes)) {
                    throw new Exception("Campos obrigatórios não preenchidos: " . implode(", ", $camposFaltantes));
                }
                
                // Extrai cidade, estado e bairro do shipping_address
                $enderecoParts = explode(',', $orderData['shipping_address']);
                if (count($enderecoParts) < 3) {
                    throw new Exception("Endereço inválido. Formato esperado: Bairro, Cidade, Estado");
                }
                
                // Cria o contato no Bling utilizando a nova integração
                $contatoData = [
                    'nome' => $orderData['name'],
                    'tipoPessoa' => strlen(preg_replace('/[^0-9]/', '', $orderData['cpf'])) === 14 ? 'J' : 'F',
                    'numeroDocumento' => $orderData['cpf'],
                    'endereco' => $orderData['shipping_address'],
                    'numero' => $orderData['shipping_number'],
                    'complemento' => $orderData['shipping_complement'] ?? '',
                    'bairro' => trim($enderecoParts[0]),
                    'cep' => $orderData['shipping_cep'],
                    'cidade' => trim($enderecoParts[1]),
                    'uf' => trim(end($enderecoParts)),
                    'fone' => $orderData['telefone'],
                    'email' => $orderData['email']
                ];
                
                // Log dos dados do contato para debug
                error_log("Dados do contato a ser criado no Bling: " . json_encode($contatoData, JSON_UNESCAPED_UNICODE));
                
                try {
                    $contatoResponse = $this->blingIntegration->criarContato($contatoData);
                    $idExternoBling = $contatoResponse['data']['id'];
                    
                    // Atualiza o ID do cliente no banco
                    $stmt = $this->conn->prepare("UPDATE usuarios SET id_externo_bling = ? WHERE id = ?");
                    $stmt->bind_param("si", $idExternoBling, $orderData['user_id']);
                    $stmt->execute();
                    $orderData['id_externo_bling'] = $idExternoBling;
                } catch (Exception $e) {
                    throw new Exception("Erro ao criar contato no Bling: " . $e->getMessage());
                }
            }

            // 4. Prepara os itens do pedido
            $blingItems = array_map(function($item) {
                return [
                    'codigo' => $item['product_id'],
                    'unidade' => 'UN',
                    'quantidade' => $item['quantity'],
                    'valor' => $item['price_at_purchase'],
                    'descricao' => $item['nome'],
                    'ncm' => $item['ncm'] ?? '70139110',
                    'cfop' => $item['cfop'] ?? '5102'
                ];
            }, $orderItems);

            // 5. Monta o payload do pedido
            $numeroPedido = $this->getNextPedidoNumber();
            $pedidoData = [
                'numero' => $numeroPedido,
                'data' => date('Y-m-d'),
                'dataSaida' => date('Y-m-d', strtotime('+10 days')),
                'dataPrevista' => date('Y-m-d', strtotime('+10 days')),
                'contato' => [
                    'id' => $orderData['id_externo_bling']
                ],
                'loja' => [
                    'id' => 205510600 // ID da loja no Bling
                ],
                'itens' => $blingItems,
                'pagamento' => [
                    'formaPagamento' => $this->mapPaymentMethod($orderData['payment_method']),
                    'parcelas' => '1x'
                ]
            ];

            // 6. Envia o pedido para o Bling e trata possíveis erros
            try {
                // Criar pedido usando o BlingService existente
                $pedidoResponse = $this->blingService->criarPedido($orderData, $blingItems);
                
                if (empty($pedidoResponse['numero'])) {
                    throw new Exception("Resposta inválida do Bling ao criar pedido");
                }
                
                // Atualiza o número do pedido no banco somente se a criação for bem-sucedida
                $this->updatePedidoNumber($numeroPedido);
                
                // Atualiza o número do pedido no banco de dados
                $stmt = $this->conn->prepare("UPDATE orders SET bling_numero_pedido = ? WHERE id = ?");
                $stmt->bind_param("si", $pedidoResponse['numero'], $orderId);
                $stmt->execute();
                $stmt->close();
            } catch (Exception $e) {
                throw new Exception("Erro ao criar pedido no Bling: " . $e->getMessage());
            }

            // 7. Registra o payload para debug
            $logPath = __DIR__ . '/../../../../Site/includes/bling_pedido_payload.json';
            $logData = [
                'timestamp' => date('Y-m-d H:i:s'),
                'pedido' => $orderId,
                'payload' => $pedidoData
            ];
            
            try {
                if (file_exists($logPath)) {
                    $logs = json_decode(file_get_contents($logPath), true) ?? [];
                } else {
                    $logs = [];
                }
                array_unshift($logs, $logData);
                file_put_contents($logPath, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            } catch (Exception $e) {
                error_log("Erro ao salvar log do payload: " . $e->getMessage());
                // Não interrompe o fluxo se houver erro no log
            }

            // 8. Atualiza o contador de pedidos
            $this->updatePedidoNumber($numeroPedido);

            // 9. Emite a nota fiscal
            $nfeBody = [
                "pedido" => [
                    "numero" => $numeroPedido
                ],
                "tipo" => "E",
                "natureza_operacao" => "Venda de mercadoria",
                "cliente" => [
                    "id" => $orderData['id_externo_bling']
                ]
            ];

            $nfeResponse = $this->blingIntegration->emitirNotaFiscal($nfeBody);

            // 10. Atualiza o pedido com os dados da nota
            $this->updateOrderWithNfeData($orderId, [
                'chave' => $nfeResponse['data']['chaveAcesso'] ?? null,
                'numero' => $nfeResponse['data']['numero'] ?? $numeroPedido,
                'serie' => $nfeResponse['data']['serie'] ?? '1',
                'data_emissao' => date('Y-m-d H:i:s'),
                'pdf_url' => $nfeResponse['data']['pdf'] ?? null
            ]);

            // 11. Registra o log de sucesso
            $this->registrarLog($orderId, 'emissao', 'sucesso', 'Nota fiscal emitida com sucesso', json_encode($nfeBody), json_encode($nfeResponse));

            return [
                'numero' => $nfeResponse['data']['numero'] ?? $numeroPedido,
                'serie' => $nfeResponse['data']['serie'] ?? '1',
                'chave' => $nfeResponse['data']['chaveAcesso'] ?? null
            ];

        } catch (Exception $e) {
            $this->registrarLog($orderId, 'emissao', 'erro', $e->getMessage(), '', '');
            throw $e;
        }
    }

    public function consultarNotaFiscal($orderId) {
        $orderData = $this->getOrderData($orderId);
        if (empty($orderData['nfe_number']) || empty($orderData['nfe_series'])) {
            throw new Exception("Este pedido não possui uma nota fiscal emitida.");
        }
        $nfeInfo = $this->blingService->consultarNotaFiscal($orderData['nfe_number'], $orderData['nfe_series']);
        $this->updateOrderWithNfeStatus($orderId, $nfeInfo);
        $this->registrarLog($orderId, 'consulta', 'sucesso', 'Nota fiscal consultada com sucesso', '', json_encode($nfeInfo));
        return $nfeInfo;
    }

    public function obterPdfNotaFiscal($orderId) {
        $orderData = $this->getOrderData($orderId);
        if (empty($orderData['nfe_key'])) {
            throw new Exception("Este pedido não possui uma nota fiscal emitida.");
        }
        if (!empty($orderData['nfe_pdf_url'])) {
            return $orderData['nfe_pdf_url'];
        }
        $pdfUrl = $this->blingService->obterPdfNotaFiscal($orderData['nfe_key']);
        $stmt = $this->conn->prepare("UPDATE orders SET nfe_pdf_url = ? WHERE id = ?");
        $stmt->bind_param("si", $pdfUrl, $orderId);
        $stmt->execute();
        $this->registrarLog($orderId, 'pdf', 'sucesso', 'PDF da nota fiscal obtido com sucesso', '', json_encode(['pdf_url' => $pdfUrl]));
        return $pdfUrl;
    }

    public function getOrderData($orderId) {
        $stmt = $this->conn->prepare("
            SELECT 
                o.*,
                u.name,
                u.email,
                u.cpf,
                u.telefone,
                o.shipping_address as endereco,
                o.shipping_number as numero,
                o.shipping_complement as complemento,
                '' as bairro, -- será extraído do shipping_address
                o.shipping_cep as cep,
                '' as cidade, -- será extraído do shipping_address
                '' as estado -- será extraído do shipping_address
            FROM orders o 
            JOIN usuarios u ON o.user_id = u.id 
            WHERE o.id = ?
        ");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            throw new Exception("Pedido não encontrado: {$orderId}");
        }
        
        $orderData = $result->fetch_assoc();
        
        // Extrair cidade, estado e bairro do shipping_address
        if (!empty($orderData['shipping_address'])) {
            $enderecoParts = explode(',', $orderData['shipping_address']);
            if (count($enderecoParts) >= 3) {
                $orderData['bairro'] = trim($enderecoParts[0]);
                $orderData['cidade'] = trim($enderecoParts[1]);
                $orderData['estado'] = trim(end($enderecoParts));
            }
        }
        
        // Log dos dados do pedido para debug
        error_log("Dados completos do pedido #" . $orderId . ": " . json_encode($orderData, JSON_UNESCAPED_UNICODE));
        
        return $orderData;
    }

    private function updateOrderWithNfeData($orderId, $nfeInfo) {
        $stmt = $this->conn->prepare("UPDATE orders SET nfe_key = ?, nfe_number = ?, nfe_series = ?, nfe_status = 'autorizada', nfe_issue_date = ?, nfe_pdf_url = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $nfeInfo['chave'], $nfeInfo['numero'], $nfeInfo['serie'], $nfeInfo['data_emissao'], $nfeInfo['pdf_url'], $orderId);
        $stmt->execute();
        $stmt->close();
    }

    private function updateOrderWithNfeStatus($orderId, $nfeInfo) {
        $stmt = $this->conn->prepare("UPDATE orders SET nfe_status = ?, nfe_issue_date = ? WHERE id = ?");
        $stmt->bind_param("ssi", $nfeInfo['status'], $nfeInfo['data_emissao'], $orderId);
        $stmt->execute();
        $stmt->close();
    }

    private function registrarLog($orderId, $eventType, $status, $message, $requestData, $responseData) {
        $stmt = $this->conn->prepare("INSERT INTO nfe_logs (order_id, event_type, status, message, request_data, response_data) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $orderId, $eventType, $status, $message, $requestData, $responseData);
        return $stmt->execute();
    }

    private function getNextPedidoNumber() {
        $stmt = $this->conn->prepare("SELECT numero FROM bling_pedidos_contador LIMIT 1");
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt = $this->conn->prepare("INSERT INTO bling_pedidos_contador (numero) VALUES (1)");
            $stmt->execute();
            return 1;
        }
        
        $row = $result->fetch_assoc();
        return $row['numero'] + 1;
    }

    private function updatePedidoNumber($numero) {
        $stmt = $this->conn->prepare("UPDATE bling_pedidos_contador SET numero = ?");
        $stmt->bind_param("i", $numero);
        $stmt->execute();
    }

    private function mapPaymentMethod($method) {
        $methods = [
            'credit_card' => 'Cartão de Crédito',
            'boleto' => 'Boleto',
            'pix' => 'Pix',
            'default' => 'A vista'
        ];
        
        return $methods[$method] ?? $methods['default'];
    }

    public function listarNotasFiscais($filters = [], $page = 1, $perPage = 10) {
        $whereClause = "WHERE o.nfe_key IS NOT NULL ";
        $params = [];
        $types = "";
        if (!empty($filters['order_id'])) {
            $whereClause .= "AND o.id = ? ";
            $params[] = $filters['order_id'];
            $types .= "i";
        }
        if (!empty($filters['nfe_status'])) {
            $whereClause .= "AND o.nfe_status = ? ";
            $params[] = $filters['nfe_status'];
            $types .= "s";
        }
        if (!empty($filters['date_from'])) {
            $whereClause .= "AND o.nfe_issue_date >= ? ";
            $params[] = $filters['date_from'] . ' 00:00:00';
            $types .= "s";
        }
        if (!empty($filters['date_to'])) {
            $whereClause .= "AND o.nfe_issue_date <= ? ";
            $params[] = $filters['date_to'] . ' 23:59:59';
            $types .= "s";
        }
        $offset = ($page - 1) * $perPage;
        $countQuery = "SELECT COUNT(*) as total FROM orders o {$whereClause}";
        $countStmt = $this->conn->prepare($countQuery);
        if (!empty($params)) {
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $totalResult = $countStmt->get_result();
        $totalRow = $totalResult->fetch_assoc();
        $total = $totalRow['total'];
        $query = "SELECT o.id, o.order_date, o.total, o.payment_method, o.nfe_key, o.nfe_number, o.nfe_series, o.nfe_status, o.nfe_issue_date, o.nfe_pdf_url, u.name as cliente_nome, u.email as cliente_email FROM orders o JOIN usuarios u ON o.user_id = u.id {$whereClause} ORDER BY o.nfe_issue_date DESC LIMIT ?, ?";
        $stmt = $this->conn->prepare($query);
        $params[] = $offset;
        $params[] = $perPage;
        $types .= "ii";
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $notasFiscais = [];
        while ($row = $result->fetch_assoc()) {
            $notasFiscais[] = $row;
        }
        return [
            'data' => $notasFiscais,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => ceil($total / $perPage)
        ];
    }
}