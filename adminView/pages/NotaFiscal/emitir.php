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
$message = '';
$messageType = '';

// Busca os dados do pedido
try {
    $orderData = $notaFiscalController->getOrderData($orderId);
    
    // Verifica se o pedido já possui nota fiscal
    if (!empty($orderData['nfe_key'])) {
        $message = "Este pedido já possui uma nota fiscal emitida.";
        $messageType = 'warning';
    }
} catch (Exception $e) {
    $message = $e->getMessage();
    $messageType = 'danger';
}

// Processa a requisição de emissão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['emitir_nfe'])) {
    try {
        // Emite a nota fiscal
        $nfeInfo = $notaFiscalController->emitirNotaFiscal($orderId);
        
        $message = "Nota fiscal emitida com sucesso! Número: {$nfeInfo['numero']}/{$nfeInfo['serie']}";
        $messageType = 'success';
        
        // Atualiza os dados do pedido
        $orderData = $notaFiscalController->getOrderData($orderId);
    } catch (Exception $e) {
        $message = "Erro ao emitir nota fiscal: " . $e->getMessage();
        $messageType = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emitir Nota Fiscal - Goldlar Cristais</title>
    <?php include '../../includes/adminHeader.php'; ?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/adminSidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Emitir Nota Fiscal</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="index.php" class="btn btn-sm btn-outline-secondary">Voltar</a>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($orderData) && empty($orderData['nfe_key'])): ?>
                    <!-- Detalhes do Pedido -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Detalhes do Pedido #<?php echo $orderData['id']; ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Informações do Cliente</h6>
                                    <p><strong>Nome:</strong> <?php echo htmlspecialchars($orderData['nome']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($orderData['email']); ?></p>
                                    <p><strong>CPF/CNPJ:</strong> <?php echo htmlspecialchars($orderData['cpf']); ?></p>
                                    <p><strong>Telefone:</strong> <?php echo htmlspecialchars($orderData['telefone'] ?? 'Não informado'); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Informações do Pedido</h6>
                                    <p><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($orderData['order_date'])); ?></p>
                                    <p><strong>Valor Total:</strong> R$ <?php echo number_format($orderData['total'], 2, ',', '.'); ?></p>
                                    <p><strong>Forma de Pagamento:</strong> <?php echo htmlspecialchars($orderData['payment_method']); ?></p>
                                    <p><strong>Status:</strong> <?php echo htmlspecialchars($orderData['status']); ?></p>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <h6>Endereço de Entrega</h6>
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
                            
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <h6>Itens do Pedido</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Produto</th>
                                                    <th>Quantidade</th>
                                                    <th>Preço Unitário</th>
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
                                            <tfoot>
                                                <tr>
                                                    <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                                    <td>R$ <?php echo number_format($orderData['subtotal'], 2, ',', '.'); ?></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" class="text-end"><strong>Frete:</strong></td>
                                                    <td>R$ <?php echo number_format($orderData['shipping'], 2, ',', '.'); ?></td>
                                                </tr>
                                                <?php if ($orderData['discount'] > 0): ?>
                                                <tr>
                                                    <td colspan="3" class="text-end"><strong>Desconto:</strong></td>
                                                    <td>R$ <?php echo number_format($orderData['discount'], 2, ',', '.'); ?></td>
                                                </tr>
                                                <?php endif; ?>
                                                <tr>
                                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                                    <td><strong>R$ <?php echo number_format($orderData['total'], 2, ',', '.'); ?></strong></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <form method="POST" action="">
                                <div class="alert alert-info">
                                    <p><i class="bi bi-info-circle"></i> Ao emitir a nota fiscal, os dados do pedido serão enviados para o Bling e uma NFe será gerada.</p>
                                    <p>Certifique-se de que todos os dados do cliente estão corretos, especialmente o CPF/CNPJ.</p>
                                </div>
                                
                                <button type="submit" name="emitir_nfe" class="btn btn-primary" onclick="return confirm('Tem certeza que deseja emitir a nota fiscal para este pedido?')">
                                    <i class="bi bi-receipt"></i> Emitir Nota Fiscal
                                </button>
                            </form>
                        </div>
                    </div>
                <?php elseif (isset($orderData) && !empty($orderData['nfe_key'])): ?>
                    <!-- Detalhes da Nota Fiscal -->
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">Nota Fiscal Emitida</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Informações da Nota Fiscal</h6>
                                    <p><strong>Número:</strong> <?php echo htmlspecialchars($orderData['nfe_number']); ?>/<?php echo htmlspecialchars($orderData['nfe_series']); ?></p>
                                    <p><strong>Chave de Acesso:</strong> <?php echo htmlspecialchars($orderData['nfe_key']); ?></p>
                                    <p><strong>Status:</strong> <?php echo htmlspecialchars($orderData['nfe_status']); ?></p>
                                    <p><strong>Data de Emissão:</strong> <?php echo date('d/m/Y H:i', strtotime($orderData['nfe_issue_date'])); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Ações</h6>
                                    <?php if (!empty($orderData['nfe_pdf_url'])): ?>
                                        <a href="<?php echo htmlspecialchars($orderData['nfe_pdf_url']); ?>" target="_blank" class="btn btn-info mb-2">
                                            <i class="bi bi-file-pdf"></i> Visualizar PDF
                                        </a>
                                    <?php else: ?>
                                        <a href="pdf.php?id=<?php echo $orderId; ?>" class="btn btn-info mb-2">
                                            <i class="bi bi-file-pdf"></i> Obter PDF
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="consultar.php?id=<?php echo $orderId; ?>" class="btn btn-warning mb-2">
                                        <i class="bi bi-arrow-repeat"></i> Consultar Status
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>