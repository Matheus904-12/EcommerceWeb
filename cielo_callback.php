<?php
/**
 * Cielo 3DS Callback Handler
 * Processa o retorno da autenticação 3DS da Cielo/Braspag
 */

session_start();
header('Content-Type: text/html; charset=utf-8');

// Configurações
define('CIELO_LOG_FILE', __DIR__ . '/cielo_callback_errors.log');

// Função para registrar logs
function logCallback($message, $data = []) {
    $log = "[" . date('Y-m-d H:i:s') . "] " . $message . PHP_EOL;
    if (!empty($data)) {
        $log .= "Data: " . json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL;
    }
    file_put_contents(CIELO_LOG_FILE, $log, FILE_APPEND);
}

// Função para obter dados do pedido
function getOrderData($paymentId) {
    try {
        require_once 'adminView/config/dbconnect.php';
        
        $query = "SELECT o.*, u.name as customer_name, u.email as customer_email 
                  FROM orders o 
                  JOIN usuarios u ON o.user_id = u.id 
                  WHERE o.payment_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $paymentId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    } catch (Exception $e) {
        logCallback('Erro ao buscar dados do pedido: ' . $e->getMessage());
        return null;
    }
}

// Função para atualizar status do pedido
function updateOrderStatus($paymentId, $status, $additionalData = []) {
    try {
        require_once 'adminView/config/dbconnect.php';
        
        $query = "UPDATE orders SET status = ?, updated_at = NOW() WHERE payment_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $status, $paymentId);
        $stmt->execute();
        
        // Salvar dados adicionais se fornecidos
        if (!empty($additionalData)) {
            $query = "UPDATE orders SET payment_response = ? WHERE payment_id = ?";
            $stmt = $conn->prepare($query);
            $responseData = json_encode($additionalData);
            $stmt->bind_param("ss", $responseData, $paymentId);
            $stmt->execute();
        }
        
        return true;
    } catch (Exception $e) {
        logCallback('Erro ao atualizar status do pedido: ' . $e->getMessage());
        return false;
    }
}

// Função para enviar e-mail de confirmação
function sendConfirmationEmail($orderData) {
    try {
        require_once 'vendor/autoload.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Configurações do servidor SMTP (ajuste conforme necessário)
        $mail->isSMTP();
        $mail->Host = 'smtp.office365.com'; // Para Outlook
        $mail->SMTPAuth = true;
        $mail->Username = 'cristaisgoldlar@outlook.com';
        $mail->Password = 'sua_senha_aqui'; // Substitua pela senha real
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        
        // Destinatários
        $mail->setFrom('cristaisgoldlar@outlook.com', 'Cristais Gold Lar');
        $mail->addAddress($orderData['customer_email'], $orderData['customer_name']);
        
        // Conteúdo
        $mail->isHTML(true);
        $mail->Subject = 'Pedido Confirmado - Cristais Gold Lar';
        
        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4e8d7c; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .order-details { background-color: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
                .footer { text-align: center; padding: 20px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Pedido Confirmado!</h1>
                </div>
                <div class='content'>
                    <p>Olá {$orderData['customer_name']},</p>
                    <p>Seu pedido foi processado com sucesso!</p>
                    
                    <div class='order-details'>
                        <h3>Detalhes do Pedido:</h3>
                        <p><strong>Número do Pedido:</strong> #{$orderData['id']}</p>
                        <p><strong>Data:</strong> " . date('d/m/Y H:i', strtotime($orderData['created_at'])) . "</p>
                        <p><strong>Total:</strong> R$" . number_format($orderData['total'], 2, ',', '.') . "</p>
                        <p><strong>Status:</strong> Pagamento Aprovado</p>
                    </div>
                    
                    <p>Acompanhe seu pedido através do código de rastreio: <strong>{$orderData['tracking_code']}</strong></p>
                    
                    <p>Obrigado por escolher a Cristais Gold Lar!</p>
                </div>
                <div class='footer'>
                    <p>Cristais Gold Lar<br>
                    CNPJ: 37.804.018/0001-56</p>
                </div>
            </div>
        </body>
        </html>";
        
        $mail->send();
        logCallback('E-mail de confirmação enviado para: ' . $orderData['customer_email']);
        return true;
        
    } catch (Exception $e) {
        logCallback('Erro ao enviar e-mail: ' . $e->getMessage());
        return false;
    }
}

// Processar dados recebidos
$paymentId = $_GET['PaymentId'] ?? $_POST['PaymentId'] ?? null;
$returnCode = $_GET['ReturnCode'] ?? $_POST['ReturnCode'] ?? null;
$returnMessage = $_GET['ReturnMessage'] ?? $_POST['ReturnMessage'] ?? '';
$authenticationUrl = $_GET['AuthenticationUrl'] ?? $_POST['AuthenticationUrl'] ?? '';

logCallback('Callback recebido', [
    'paymentId' => $paymentId,
    'returnCode' => $returnCode,
    'returnMessage' => $returnMessage,
    'authenticationUrl' => $authenticationUrl,
    'get' => $_GET,
    'post' => $_POST
]);

// Verificar se temos dados mínimos necessários
if (!$paymentId) {
    logCallback('Erro: PaymentId não fornecido');
    $errorMessage = 'Dados de pagamento inválidos.';
    $redirectUrl = 'Site/checkout.php?error=' . urlencode($errorMessage);
} else {
    // Obter dados do pedido
    $orderData = getOrderData($paymentId);
    
    if (!$orderData) {
        logCallback('Erro: Pedido não encontrado para PaymentId: ' . $paymentId);
        $errorMessage = 'Pedido não encontrado.';
        $redirectUrl = 'Site/checkout.php?error=' . urlencode($errorMessage);
    } else {
        // Determinar status baseado no código de retorno
        $status = 'pendente';
        $success = false;
        
        switch ($returnCode) {
            case '00':
            case '000':
            case 'AI': // Código AI indica transação sem 3DS (sucesso)
                $status = 'aprovado';
                $success = true;
                break;
            case '05':
                $status = 'negado';
                $errorMessage = 'Transação não autorizada.';
                break;
            case '57':
                $status = 'negado';
                $errorMessage = 'Transação não permitida para este cartão.';
                break;
            case '70':
                $status = 'erro';
                $errorMessage = 'Problema no processamento.';
                break;
            case '77':
                $status = 'cancelado';
                $errorMessage = 'Transação cancelada.';
                break;
            case '96':
                $status = 'erro';
                $errorMessage = 'Falha no sistema.';
                break;
            default:
                if ($returnCode && !in_array($returnCode, ['00', '000', 'AI'])) {
                    $status = 'negado';
                    $errorMessage = $returnMessage ?: 'Transação recusada.';
                }
                break;
        }
        
        // Atualizar status do pedido
        $updateData = [
            'returnCode' => $returnCode,
            'returnMessage' => $returnMessage,
            'authenticationUrl' => $authenticationUrl
        ];
        
        if (updateOrderStatus($paymentId, $status, $updateData)) {
            logCallback('Status do pedido atualizado', [
                'paymentId' => $paymentId,
                'status' => $status,
                'returnCode' => $returnCode
            ]);
            
            // Enviar e-mail de confirmação se aprovado
            if ($success) {
                sendConfirmationEmail($orderData);
            }
            
            // Definir URL de redirecionamento
            if ($success) {
                $redirectUrl = 'Site/checkout.php?success=1&order_id=' . $orderData['id'];
            } else {
                $redirectUrl = 'Site/checkout.php?error=' . urlencode($errorMessage) . '&order_id=' . $orderData['id'];
            }
        } else {
            logCallback('Erro ao atualizar status do pedido');
            $redirectUrl = 'Site/checkout.php?error=' . urlencode('Erro ao processar pagamento.');
        }
    }
}

// Se houver URL de autenticação, redirecionar para ela
if ($authenticationUrl && $returnCode === '10') {
    logCallback('Redirecionando para autenticação 3DS', ['url' => $authenticationUrl]);
    header('Location: ' . $authenticationUrl);
    exit;
}

// Redirecionar para a página apropriada
logCallback('Redirecionando para: ' . $redirectUrl);
header('Location: ' . $redirectUrl);
exit;
?> 