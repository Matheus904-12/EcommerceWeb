<?php
require_once '../../config/dbconnect.php';
require_once '../../controller/NotaFiscal/NotaFiscalController.php';

// Inicializa o controlador de notas fiscais
$notaFiscalController = new NotaFiscalController($conn);

// Verifica se foi informado o ID do pedido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$orderId = intval($_GET['id']);

// Busca os dados do pedido
try {
    $orderData = $notaFiscalController->getOrderData($orderId);
} catch (Exception $e) {
    $message = $e->getMessage();
    $messageType = 'danger';
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Nota Fiscal - Goldlar Cristais</title>
    <?php include '../../includes/adminHeader.php'; ?>
    <style>
        .status-badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pendente {
            background-color: #ffc107;
            color: #212529;
        }
        .status-emitida {
            background-color: #28a745;
            color: #fff;
        }
        .status-cancelada {
            background-color: #dc3545;
            color: #fff;
        }
        .status-erro {
            background-color: #dc3545;
            color: #fff;
        }
        .order-details {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .nfe-details {
            background-color: #e9f7ef;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/adminSidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Detalhes da Nota Fiscal</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="index.php" class="btn btn-sm btn-outline-secondary">Voltar</a>
                        </div>
                    </div>
                </div>
                
                <?php if (isset($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($orderData)): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <!-- Detalhes do Pedido -->
                            <div class="order-details">
                                <h4>Pedido #<?php echo $orderData['id']; ?></h4>
                                <hr>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Data do Pedido:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo date('d/m/Y H:i', strtotime($orderData['order_date'])); ?>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Status do Pedido:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo htmlspecialchars($orderData['status']); ?>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Forma de Pagamento:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo htmlspecialchars($orderData['payment_method']); ?>
                                        <?php if ($orderData['payment_method'] === 'credit_card' && !empty($orderData['payment_id'])): ?>
                                            (Final <?php echo substr($orderData['payment_id'], -4); ?>)
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Subtotal:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        R$ <?php echo number_format($orderData['subtotal'], 2, ',', '.'); ?>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Frete:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        R$ <?php echo number_format($orderData['shipping'], 2, ',', '.'); ?>
                                    </div>
                                </div>
                                
                                <?php if ($orderData['discount'] > 0): ?>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Desconto:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        R$ <?php echo number_format($orderData['discount'], 2, ',', '.'); ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Total:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>R$ <?php echo number_format($orderData['total'], 2, ',', '.'); ?></strong>
                                    </div>
                                </div>
                                
                                <?php if (!empty($orderData['tracking_code'])): ?>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Código de Rastreio:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo htmlspecialchars($orderData['tracking_code']); ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Informações do Cliente -->
                            <div class="order-details">
                                <h4>Informações do Cliente</h4>
                                <hr>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Nome:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo htmlspecialchars($orderData['nome']); ?>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Email:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo htmlspecialchars($orderData['email']); ?>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>CPF/CNPJ:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo htmlspecialchars($orderData['cpf']); ?>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Telefone:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo htmlspecialchars($orderData['telefone'] ?? 'Não informado'); ?>
                                    </div>
                                </div>
                                
                                <h5 class="mt-4">Endereço de Entrega</h5>
                                <p>
                                    <?php echo htmlspecialchars($orderData['shipping_address']); ?>, 
                                    <?php echo htmlspecialchars($orderData['shipping_number']); ?>
                                    <?php if (!empty($orderData['shipping_complement'])): ?>
                                        - <?php echo htmlspecialchars($orderData['shipping_complement']); ?>
                                    <?php endif; ?>
                                    <br>
                                    CEP: <?php echo htmlspecialchars($orderData['shipping_cep']); ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <!-- Detalhes da Nota Fiscal -->
                            <?php if (!empty($orderData['nfe_key'])): ?>
                            <div class="nfe-details">
                                <h4>Nota Fiscal Eletrônica</h4>
                                <hr>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Número:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo htmlspecialchars($orderData['nfe_number']); ?>/<?php echo htmlspecialchars($orderData['nfe_series']); ?>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Chave de Acesso:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="text-break"><?php echo htmlspecialchars($orderData['nfe_key']); ?></span>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Status:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="status-badge status-<?php echo strtolower($orderData['nfe_status']); ?>">
                                            <?php echo htmlspecialchars($orderData['nfe_status']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Data de Emissão:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo date('d/m/Y H:i', strtotime($orderData['nfe_issue_date'])); ?>
                                    </div>
                                </div>
                                
                                <div class="row mb-4">
                                    <div class="col-md-12 text-center mt-3">
                                        <?php if (!empty($orderData['nfe_pdf_url'])): ?>
                                            <a href="<?php echo htmlspecialchars($orderData['nfe_pdf_url']); ?>" target="_blank" class="btn btn-info">
                                                <i class="bi bi-file-pdf"></i> Visualizar PDF
                                            </a>
                                        <?php else: ?>
                                            <a href="pdf.php?id=<?php echo $orderId; ?>" class="btn btn-info">
                                                <i class="bi bi-file-pdf"></i> Obter PDF
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="consultar.php?id=<?php echo $orderId; ?>" class="btn btn-warning ms-2">
                                            <i class="bi bi-arrow-repeat"></i> Consultar Status
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-warning">
                                <h5><i class="bi bi-exclamation-triangle"></i> Nota Fiscal Não Emitida</h5>
                                <p>Este pedido ainda não possui uma nota fiscal emitida.</p>
                                <a href="emitir.php?id=<?php echo $orderId; ?>" class="btn btn-primary mt-2">
                                    <i class="bi bi-receipt"></i> Emitir Nota Fiscal
                                </a>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Itens do Pedido -->
                            <div class="order-details">
                                <h4>Itens do Pedido</h4>
                                <hr>
                                
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Produto</th>
                                                <th>Qtd</th>
                                                <th>Preço Unit.</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $stmt = $conn->prepare(
                                                "SELECT oi.*, p.nome as product_name 
                                                 FROM order_items oi 
                                                 JOIN produtos p ON oi.product_id = p.id 
                                                 WHERE oi.order_id = ?"
                                            );
                                            $stmt->bind_param("i", $orderId);
                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                            
                                            while ($item = $result->fetch_assoc()):
                                            ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                                    <td><?php echo $item['quantity']; ?></td>
                                                    <td>R$ <?php echo number_format($item['price_at_purchase'], 2, ',', '.'); ?></td>
                                                    <td>R$ <?php echo number_format($item['quantity'] * $item['price_at_purchase'], 2, ',', '.'); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Histórico de Eventos -->
                            <?php if (!empty($orderData['nfe_key'])): ?>
                            <div class="order-details">
                                <h4>Histórico de Eventos</h4>
                                <hr>
                                
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Data/Hora</th>
                                                <th>Evento</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $stmt = $conn->prepare(
                                                "SELECT * FROM nfe_logs 
                                                 WHERE order_id = ? 
                                                 ORDER BY created_at DESC 
                                                 LIMIT 5"
                                            );
                                            $stmt->bind_param("i", $orderId);
                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                            
                                            if ($result->num_rows > 0):
                                                while ($log = $result->fetch_assoc()):
                                            ?>
                                                <tr>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></td>
                                                    <td>
                                                        <?php 
                                                        $eventTypes = [
                                                            'emissao' => 'Emissão',
                                                            'consulta' => 'Consulta',
                                                            'pdf' => 'Obtenção de PDF',
                                                            'cancelamento' => 'Cancelamento'
                                                        ];
                                                        echo $eventTypes[$log['event_type']] ?? $log['event_type'];
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php echo ($log['status'] === 'sucesso') ? 'success' : 'danger'; ?>">
                                                            <?php echo ucfirst($log['status']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php 
                                                endwhile;
                                            else:
                                            ?>
                                                <tr>
                                                    <td colspan="3" class="text-center">Nenhum evento registrado</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                    
                                    <?php if ($result->num_rows > 0): ?>
                                    <div class="text-center mt-2">
                                        <a href="consultar.php?id=<?php echo $orderId; ?>" class="btn btn-sm btn-outline-secondary">Ver histórico completo</a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>