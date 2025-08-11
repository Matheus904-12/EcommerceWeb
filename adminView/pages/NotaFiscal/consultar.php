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
    
    // Verifica se o pedido possui nota fiscal
    if (empty($orderData['nfe_key'])) {
        $message = "Este pedido não possui uma nota fiscal emitida.";
        $messageType = 'warning';
    }
} catch (Exception $e) {
    $message = $e->getMessage();
    $messageType = 'danger';
}

// Processa a requisição de consulta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['consultar_nfe'])) {
    try {
        // Consulta a nota fiscal
        $nfeInfo = $notaFiscalController->consultarNotaFiscal($orderId);
        
        $message = "Consulta realizada com sucesso! Status atual: {$nfeInfo['situacao']}";
        $messageType = 'success';
        
        // Atualiza os dados do pedido
        $orderData = $notaFiscalController->getOrderData($orderId);
    } catch (Exception $e) {
        $message = "Erro ao consultar nota fiscal: " . $e->getMessage();
        $messageType = 'danger';
    }
}

// Processa a requisição para obter o PDF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['obter_pdf'])) {
    try {
        // Obtém o PDF da nota fiscal
        $pdfUrl = $notaFiscalController->obterPdfNotaFiscal($orderId);
        
        $message = "PDF obtido com sucesso!";
        $messageType = 'success';
        
        // Atualiza os dados do pedido
        $orderData = $notaFiscalController->getOrderData($orderId);
    } catch (Exception $e) {
        $message = "Erro ao obter PDF da nota fiscal: " . $e->getMessage();
        $messageType = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Nota Fiscal - Goldlar Cristais</title>
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
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/adminSidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Consultar Nota Fiscal</h1>
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
                
                <?php if (isset($orderData) && !empty($orderData['nfe_key'])): ?>
                    <!-- Detalhes da Nota Fiscal -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Nota Fiscal do Pedido #<?php echo $orderData['id']; ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Informações da Nota Fiscal</h6>
                                    <p><strong>Número:</strong> <?php echo htmlspecialchars($orderData['nfe_number']); ?>/<?php echo htmlspecialchars($orderData['nfe_series']); ?></p>
                                    <p><strong>Chave de Acesso:</strong> <?php echo htmlspecialchars($orderData['nfe_key']); ?></p>
                                    <p>
                                        <strong>Status:</strong> 
                                        <span class="status-badge status-<?php echo strtolower($orderData['nfe_status']); ?>">
                                            <?php echo htmlspecialchars($orderData['nfe_status']); ?>
                                        </span>
                                    </p>
                                    <p><strong>Data de Emissão:</strong> <?php echo date('d/m/Y H:i', strtotime($orderData['nfe_issue_date'])); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Informações do Pedido</h6>
                                    <p><strong>Cliente:</strong> <?php echo htmlspecialchars($orderData['nome']); ?></p>
                                    <p><strong>CPF/CNPJ:</strong> <?php echo htmlspecialchars($orderData['cpf']); ?></p>
                                    <p><strong>Data do Pedido:</strong> <?php echo date('d/m/Y H:i', strtotime($orderData['order_date'])); ?></p>
                                    <p><strong>Valor Total:</strong> R$ <?php echo number_format($orderData['total'], 2, ',', '.'); ?></p>
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-between">
                                        <form method="POST" action="">
                                            <button type="submit" name="consultar_nfe" class="btn btn-primary">
                                                <i class="bi bi-arrow-repeat"></i> Atualizar Status
                                            </button>
                                        </form>
                                        
                                        <?php if (empty($orderData['nfe_pdf_url'])): ?>
                                            <form method="POST" action="">
                                                <button type="submit" name="obter_pdf" class="btn btn-info">
                                                    <i class="bi bi-file-pdf"></i> Obter PDF
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <a href="<?php echo htmlspecialchars($orderData['nfe_pdf_url']); ?>" target="_blank" class="btn btn-info">
                                                <i class="bi bi-file-pdf"></i> Visualizar PDF
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Histórico de Eventos -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Histórico de Eventos</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Data/Hora</th>
                                            <th>Evento</th>
                                            <th>Status</th>
                                            <th>Mensagem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $stmt = $conn->prepare(
                                            "SELECT * FROM nfe_logs 
                                             WHERE order_id = ? 
                                             ORDER BY created_at DESC"
                                        );
                                        $stmt->bind_param("i", $orderId);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        
                                        if ($result->num_rows > 0):
                                            while ($log = $result->fetch_assoc()):
                                        ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?></td>
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
                                                <td><?php echo htmlspecialchars($log['message']); ?></td>
                                            </tr>
                                        <?php 
                                            endwhile;
                                        else:
                                        ?>
                                            <tr>
                                                <td colspan="4" class="text-center">Nenhum evento registrado</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php elseif (isset($orderData)): ?>
                    <div class="alert alert-warning">
                        <h5><i class="bi bi-exclamation-triangle"></i> Nota Fiscal Não Emitida</h5>
                        <p>Este pedido ainda não possui uma nota fiscal emitida.</p>
                        <a href="emitir.php?id=<?php echo $orderId; ?>" class="btn btn-primary mt-2">
                            <i class="bi bi-receipt"></i> Emitir Nota Fiscal
                        </a>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>