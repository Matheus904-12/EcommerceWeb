<?php
/**
 * Classe PaymentGateway
 * 
 * Implementa a integração com o gateway de pagamento Cielo
 * para processar diferentes tipos de pagamento (PIX, cartão de crédito, boleto)
 */

// Incluir arquivo de configuração do ambiente para carregar variáveis de ambiente
require_once __DIR__ . '/config.php';

class PaymentGateway {
    private $merchantId;
    private $merchantKey;
    private $apiUrl;
    private $sandboxUrl;
    private $environment;
    private $logFile;
    
    /**
     * Construtor da classe
     */
    public function __construct() {
        // Configurações da Cielo - usando variáveis de ambiente com fallback para valores padrão
        $this->merchantId = getenv('CIELO_MERCHANT_ID') ?: 'e85b80d2-3bec-4c7b-b64f-cb24aa76f51e';
        $this->merchantKey = getenv('CIELO_MERCHANT_KEY') ?: 's7KwzcUMS9fkYsKgTTijA0uYc4RSyKS1QlJbhhkD';
        $this->apiUrl = getenv('CIELO_API_URL') ?: 'https://api.cieloecommerce.cielo.com.br/';
        $this->sandboxUrl = getenv('CIELO_SANDBOX_URL') ?: 'https://apisandbox.cieloecommerce.cielo.com.br/';
        $this->environment = getenv('CIELO_ENVIRONMENT') ?: 'sandbox';
        $this->logFile = __DIR__ . '/checkout/cielo_payment_errors.log';
    }
    
    /**
     * Gera um código PIX para pagamento
     * 
     * @param array $data Dados do pagamento PIX
     * @return array Resposta do processamento
     */
    public function generatePIX($data) {
        // Validar dados obrigatórios
        if (empty($data['order_id']) || empty($data['amount']) || empty($data['description'])) {
            return [
                'success' => false,
                'message' => 'Dados incompletos para geração do PIX'
            ];
        }
        
        // Preparar payload para a API da Cielo
        $payload = [
            'MerchantOrderId' => $data['order_id'],
            'Customer' => [
                'Name' => $data['customer']['name'] ?? 'Cliente',
                'Identity' => $data['customer']['document'] ?? '',
                'IdentityType' => 'CPF',
                'Email' => $data['customer']['email'] ?? ''
            ],
            'Payment' => [
                'Type' => 'Pix',
                'Amount' => (int)($data['amount'] * 100), // Cielo usa centavos
                'Provider' => 'Cielo',
                'Description' => $data['description'],
                'ExpirationDate' => $data['expiration_date'] ?? date('Y-m-d', strtotime('+1 day'))
            ]
        ];
        
        // Fazer requisição à API
        $response = $this->makeApiRequest('1/sales', 'POST', $payload);
        
        if ($response['httpCode'] == 201 && isset($response['response']['Payment']['PaymentId'])) {
            return [
                'success' => true,
                'transaction_id' => $response['response']['Payment']['PaymentId'],
                'pix_code' => $response['response']['Payment']['QrCodeBase64Image'] ?? '',
                'pix_key' => $response['response']['Payment']['QrCodeString'] ?? '',
                'expiration_date' => $response['response']['Payment']['ExpirationDate'] ?? '',
                'status' => 'pending'
            ];
        } else {
            $errorMessage = $response['response']['Message'] ?? 'Erro ao gerar PIX';
            $this->logError("Erro ao gerar PIX: $errorMessage", $payload);
            
            return [
                'success' => false,
                'message' => $errorMessage,
                'error_code' => $response['httpCode']
            ];
        }
    }
    
    /**
     * Processa pagamento com cartão de crédito
     * 
     * @param array $data Dados do pagamento com cartão
     * @return array Resposta do processamento
     */
    public function processCreditCardPayment($data) {
        // Validar dados obrigatórios
        if (empty($data['order_id']) || empty($data['amount']) || 
            empty($data['card_data']) || empty($data['installments'])) {
            return [
                'success' => false,
                'message' => 'Dados incompletos para pagamento com cartão'
            ];
        }
        
        $cardData = $data['card_data'];
        
        // Preparar payload para a API da Cielo
        $payload = [
            'MerchantOrderId' => $data['order_id'],
            'Customer' => [
                'Name' => $cardData['name'] ?? 'Cliente',
                'Identity' => $data['customer']['document'] ?? '',
                'IdentityType' => 'CPF',
                'Email' => $data['customer']['email'] ?? ''
            ],
            'Payment' => [
                'Type' => 'CreditCard',
                'Amount' => (int)($data['amount'] * 100), // Cielo usa centavos
                'Installments' => (int)$data['installments'],
                'Capture' => true,
                'SoftDescriptor' => substr($data['description'] ?? 'Compra Online', 0, 13),
                'CreditCard' => [
                    'CardNumber' => preg_replace('/\D/', '', $cardData['number']),
                    'Holder' => $cardData['name'],
                    'ExpirationDate' => $this->formatExpiryDate($cardData['expiry']),
                    'SecurityCode' => $cardData['cvv'],
                    'Brand' => $this->detectCardBrand($cardData['number']),
                    'SaveCard' => isset($cardData['save_card']) && $cardData['save_card'] ? 'true' : 'false'
                ]
            ]
        ];
        
        // Fazer requisição à API
        $response = $this->makeApiRequest('1/sales', 'POST', $payload);
        
        if ($response['httpCode'] == 201 && isset($response['response']['Payment']['PaymentId'])) {
            $status = $response['response']['Payment']['Status'];
            $isApproved = $status == 1 || $status == 2; // 1=Autorizado, 2=Confirmado
            
            return [
                'success' => true,
                'transaction_id' => $response['response']['Payment']['PaymentId'],
                'status' => $isApproved ? 'approved' : 'pending',
                'card_token' => $response['response']['Payment']['CreditCard']['CardToken'] ?? '',
                'card_brand' => $response['response']['Payment']['CreditCard']['Brand'] ?? ''
            ];
        } else {
            $errorMessage = $response['response']['Message'] ?? '';
            $errorCode = $response['response']['Payment']['ReturnCode'] ?? '';
            
            if (empty($errorMessage) && isset($response['response']['Payment']['ReturnMessage'])) {
                $errorMessage = $response['response']['Payment']['ReturnMessage'];
            }
            
            $this->logError("Erro ao processar cartão: $errorMessage (Código: $errorCode)", $payload);
            
            return [
                'success' => false,
                'message' => $errorMessage ?: 'Erro ao processar pagamento',
                'error_code' => $errorCode ?: 'processing_error'
            ];
        }
    }
    
    /**
     * Processa pagamento com cartão tokenizado
     * 
     * @param array $data Dados do pagamento com token
     * @return array Resposta do processamento
     */
    public function processTokenizedPayment($data) {
        // Validar dados obrigatórios
        if (empty($data['order_id']) || empty($data['amount']) || empty($data['card_token'])) {
            return [
                'success' => false,
                'message' => 'Dados incompletos para pagamento com token'
            ];
        }
        
        // Preparar payload para a API da Cielo
        $payload = [
            'MerchantOrderId' => $data['order_id'],
            'Customer' => [
                'Name' => 'Cliente',
                'Identity' => $data['customer_document'] ?? '',
                'IdentityType' => 'CPF',
                'Email' => $data['customer_email'] ?? ''
            ],
            'Payment' => [
                'Type' => 'CreditCard',
                'Amount' => (int)($data['amount'] * 100), // Cielo usa centavos
                'Installments' => (int)($data['installments'] ?? 1),
                'Capture' => true,
                'SoftDescriptor' => substr($data['description'] ?? 'Compra Online', 0, 13),
                'CreditCard' => [
                    'CardToken' => $data['card_token'],
                    'SecurityCode' => $data['security_code'] ?? '',
                    'Brand' => $data['card_brand'] ?? 'Visa'
                ]
            ]
        ];
        
        // Fazer requisição à API
        $response = $this->makeApiRequest('1/sales', 'POST', $payload);
        
        if ($response['httpCode'] == 201 && isset($response['response']['Payment']['PaymentId'])) {
            $status = $response['response']['Payment']['Status'];
            $isApproved = $status == 1 || $status == 2; // 1=Autorizado, 2=Confirmado
            
            return [
                'success' => true,
                'transaction_id' => $response['response']['Payment']['PaymentId'],
                'status' => $isApproved ? 'approved' : 'pending'
            ];
        } else {
            $errorMessage = $response['response']['Message'] ?? '';
            $errorCode = $response['response']['Payment']['ReturnCode'] ?? '';
            
            if (empty($errorMessage) && isset($response['response']['Payment']['ReturnMessage'])) {
                $errorMessage = $response['response']['Payment']['ReturnMessage'];
            }
            
            $this->logError("Erro ao processar cartão tokenizado: $errorMessage (Código: $errorCode)", $payload);
            
            return [
                'success' => false,
                'message' => $errorMessage ?: 'Erro ao processar pagamento',
                'error_code' => $errorCode ?: 'processing_error'
            ];
        }
    }
    
    /**
     * Gera boleto para pagamento
     * 
     * @param array $data Dados do boleto
     * @return array Resposta do processamento
     */
    public function generateBoleto($data) {
        // Validar dados obrigatórios
        if (empty($data['order_id']) || empty($data['amount']) || empty($data['customer'])) {
            return [
                'success' => false,
                'message' => 'Dados incompletos para geração do boleto'
            ];
        }
        
        // Preparar payload para a API da Cielo
        $payload = [
            'MerchantOrderId' => $data['order_id'],
            'Customer' => [
                'Name' => $data['customer']['name'],
                'Identity' => $data['customer']['document'],
                'IdentityType' => 'CPF',
                'Email' => $data['customer']['email'],
                'Address' => [
                    'Street' => $data['customer']['address'],
                    'Number' => $data['customer']['number'],
                    'ZipCode' => $data['customer']['zipcode'],
                    'City' => $data['customer']['city'],
                    'State' => $data['customer']['state'],
                    'Country' => 'BRA'
                ]
            ],
            'Payment' => [
                'Type' => 'Boleto',
                'Amount' => (int)($data['amount'] * 100), // Cielo usa centavos
                'Provider' => 'Bradesco2',
                'Address' => 'Rua Teste',
                'BoletoNumber' => $data['order_id'],
                'ExpirationDate' => $data['expiration_date'],
                'Instructions' => 'Não receber após o vencimento',
                'DaysToFine' => 1,
                'FineRate' => 10.00,
                'FineAmount' => 1000,
                'DaysToInterest' => 1,
                'InterestRate' => 5.00,
                'InterestAmount' => 500
            ]
        ];
        
        // Fazer requisição à API
        $response = $this->makeApiRequest('1/sales', 'POST', $payload);
        
        if ($response['httpCode'] == 201 && isset($response['response']['Payment']['PaymentId'])) {
            return [
                'success' => true,
                'transaction_id' => $response['response']['Payment']['PaymentId'],
                'boleto_url' => $response['response']['Payment']['Url'],
                'barcode' => $response['response']['Payment']['BarCodeNumber'],
                'expiration_date' => $response['response']['Payment']['ExpirationDate']
            ];
        } else {
            $errorMessage = $response['response']['Message'] ?? 'Erro ao gerar boleto';
            $this->logError("Erro ao gerar boleto: $errorMessage", $payload);
            
            return [
                'success' => false,
                'message' => $errorMessage,
                'error_code' => $response['httpCode']
            ];
        }
    }
    
    /**
     * Consulta status de um pagamento
     * 
     * @param string $paymentId ID da transação
     * @return array Resposta da consulta
     */
    public function checkPaymentStatus($paymentId) {
        $response = $this->makeApiRequest("1/sales/$paymentId", 'GET');
        
        if ($response['httpCode'] == 200 && isset($response['response']['Payment']['Status'])) {
            $status = $response['response']['Payment']['Status'];
            $statusMap = [
                0 => 'pending', // Não finalizada
                1 => 'approved', // Autorizada
                2 => 'approved', // Pagamento confirmado
                3 => 'denied', // Negada
                10 => 'pending', // Em autenticação
                11 => 'cancelled', // Cancelada
                12 => 'pending', // Em cancelamento
                13 => 'cancelled', // Abortada
                20 => 'pending' // Agendada
            ];
            
            return [
                'success' => true,
                'status' => $statusMap[$status] ?? 'unknown',
                'payment_id' => $paymentId,
                'raw_status' => $status
            ];
        } else {
            $errorMessage = $response['response']['Message'] ?? 'Erro ao consultar pagamento';
            $this->logError("Erro ao consultar pagamento $paymentId: $errorMessage");
            
            return [
                'success' => false,
                'message' => $errorMessage,
                'error_code' => $response['httpCode']
            ];
        }
    }
    
    /**
     * Cancela um pagamento
     * 
     * @param string $paymentId ID da transação
     * @param int $amount Valor a ser cancelado (opcional, se não informado cancela o valor total)
     * @return array Resposta do cancelamento
     */
    public function cancelPayment($paymentId, $amount = null) {
        $endpoint = "1/sales/$paymentId/void";
        if ($amount !== null) {
            $endpoint .= '/' . (int)($amount * 100); // Cielo usa centavos
        }
        
        $response = $this->makeApiRequest($endpoint, 'PUT');
        
        if ($response['httpCode'] == 200) {
            return [
                'success' => true,
                'message' => 'Pagamento cancelado com sucesso',
                'payment_id' => $paymentId
            ];
        } else {
            $errorMessage = $response['response']['Message'] ?? 'Erro ao cancelar pagamento';
            $this->logError("Erro ao cancelar pagamento $paymentId: $errorMessage");
            
            return [
                'success' => false,
                'message' => $errorMessage,
                'error_code' => $response['httpCode']
            ];
        }
    }
    
    /**
     * Faz requisição para a API da Cielo
     * 
     * @param string $endpoint Endpoint da API
     * @param string $method Método HTTP (GET, POST, PUT)
     * @param array $payload Dados a serem enviados (para POST e PUT)
     * @return array Resposta da API
     */
    private function makeApiRequest($endpoint, $method = 'GET', $payload = null) {
        $baseUrl = $this->environment === 'production' ? $this->apiUrl : $this->sandboxUrl;
        $url = $baseUrl . $endpoint;
        
        $headers = [
            'Content-Type: application/json',
            'MerchantId: ' . $this->merchantId,
            'MerchantKey: ' . $this->merchantKey
        ];
        
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->environment === 'production');
            
            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                if ($payload) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                }
            } elseif ($method === 'PUT') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($payload) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                }
            }
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                $this->logError("Erro CURL: $error");
                return ['httpCode' => 500, 'response' => ['Message' => 'Erro na requisição: ' . $error]];
            }
            
            $decoded = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logError("Erro ao decodificar JSON: " . json_last_error_msg());
                return ['httpCode' => 500, 'response' => ['Message' => 'Erro ao decodificar resposta']];
            }
            
            return ['httpCode' => $httpCode, 'response' => $decoded];
        } catch (Exception $e) {
            $this->logError("Exceção na requisição: " . $e->getMessage());
            return ['httpCode' => 500, 'response' => ['Message' => 'Erro na requisição: ' . $e->getMessage()]];
        }
    }
    
    /**
     * Detecta a bandeira do cartão com base no número
     * 
     * @param string $cardNumber Número do cartão
     * @return string Bandeira do cartão
     */
    private function detectCardBrand($cardNumber) {
        $number = preg_replace('/\D/', '', $cardNumber);
        
        $patterns = [
            'visa' => '/^4\d{12}(\d{3})?$/',
            'mastercard' => '/^(5[1-5]\d{4}|2(2(2[1-9]|[3-9]\d)|[3-6]\d{2}|7([0-1]\d|20)))\d{10}$/',
            'amex' => '/^3[47]\d{13}$/',
            'elo' => '/^(4011(78|79)|43(1274|8935)|45(1416|7393|763(1|2))|50(4175|6699|67[0-7][0-9]|9000)|627780|63(6297|6368)|650(03([^4])|04([0-9])|05(0|1)|4(0[5-9]|3[0-9]|8[5-9]|9[0-9])|5([0-2][0-9]|3[0-8])|9([2-6][0-9]|7[0-8])|541|700|720|901)|651652|655000|655021)\d{10}$/',
            'hipercard' => '/^(38|60)\d{11,17}$/'
        ];
        
        foreach ($patterns as $brand => $pattern) {
            if (preg_match($pattern, $number)) {
                return ucfirst($brand);
            }
        }
        
        return 'Visa'; // Padrão se não conseguir detectar
    }
    
    /**
     * Formata a data de validade do cartão para o formato aceito pela Cielo
     * 
     * @param string $expiryDate Data de validade no formato MM/YY
     * @return string Data formatada para MM/YYYY
     */
    private function formatExpiryDate($expiryDate) {
        if (preg_match('/^(\d{2})\/?(\d{2})$/', $expiryDate, $matches)) {
            $month = $matches[1];
            $year = $matches[2];
            return $month . '/20' . $year;
        }
        return $expiryDate;
    }
    
    /**
     * Registra erros em arquivo de log
     * 
     * @param string $message Mensagem de erro
     * @param array $data Dados adicionais para o log
     */
    private function logError($message, $data = []) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message" . PHP_EOL;
        
        if (!empty($data)) {
            // Remover dados sensíveis antes de logar
            if (isset($data['Payment']['CreditCard']['CardNumber'])) {
                $data['Payment']['CreditCard']['CardNumber'] = '****' . substr($data['Payment']['CreditCard']['CardNumber'], -4);
            }
            if (isset($data['Payment']['CreditCard']['SecurityCode'])) {
                $data['Payment']['CreditCard']['SecurityCode'] = '***';
            }
            
            $logMessage .= "Dados: " . json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL;
        }
        
        $logMessage .= "--------------------------------------------------" . PHP_EOL;
        
        // Criar diretório de log se não existir
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }
}
?>