<?php
require_once '../../config/dbconnect.php';
require_once '../../controller/NotaFiscal/NotaFiscalController.php';

// Inicializa o controlador de notas fiscais
$notaFiscalController = new NotaFiscalController($conn);

// Processa filtros
$filters = [];
if (isset($_GET['order_id']) && !empty($_GET['order_id'])) {
    $filters['order_id'] = $_GET['order_id'];
}

if (isset($_GET['nfe_status']) && !empty($_GET['nfe_status'])) {
    $filters['nfe_status'] = $_GET['nfe_status'];
}

if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
    $filters['date_from'] = $_GET['date_from'];
}

if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
    $filters['date_to'] = $_GET['date_to'];
}

// Paginação
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;

// Busca a lista de notas fiscais
$notasFiscais = $notaFiscalController->listarNotasFiscais($filters, $page, $perPage);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Notas Fiscais - Goldlar Cristais</title>
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
        .filters-container {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
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
                    <h1 class="h2">Gerenciamento de Notas Fiscais</h1>
                </div>
                
                <!-- Filtros -->
                <div class="filters-container">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-2">
                            <label for="order_id" class="form-label">Nº do Pedido</label>
                            <input type="text" class="form-control" id="order_id" name="order_id" value="<?php echo isset($_GET['order_id']) ? htmlspecialchars($_GET['order_id']) : ''; ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="nfe_status" class="form-label">Status</label>
                            <select class="form-select" id="nfe_status" name="nfe_status">
                                <option value="">Todos</option>
                                <option value="pendente" <?php echo (isset($_GET['nfe_status']) && $_GET['nfe_status'] === 'pendente') ? 'selected' : ''; ?>>Pendente</option>
                                <option value="emitida" <?php echo (isset($_GET['nfe_status']) && $_GET['nfe_status'] === 'emitida') ? 'selected' : ''; ?>>Emitida</option>
                                <option value="cancelada" <?php echo (isset($_GET['nfe_status']) && $_GET['nfe_status'] === 'cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                                <option value="erro" <?php echo (isset($_GET['nfe_status']) && $_GET['nfe_status'] === 'erro') ? 'selected' : ''; ?>>Erro</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="date_from" class="form-label">Data Inicial</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : ''; ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="date_to" class="form-label">Data Final</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : ''; ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                            <a href="index.php" class="btn btn-secondary ms-2">Limpar</a>
                        </div>
                    </form>
                </div>
                
                <!-- Tabela de Notas Fiscais -->
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Pedido</th>
                                <th>Cliente</th>
                                <th>Data Emissão</th>
                                <th>Número NFe</th>
                                <th>Status</th>
                                <th>Valor</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($notasFiscais['data']) > 0): ?>
                                <?php foreach ($notasFiscais['data'] as $nf): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($nf['id']); ?></td>
                                        <td><?php echo htmlspecialchars($nf['cliente_nome']); ?></td>
                                        <td>
                                            <?php if ($nf['nfe_issue_date']): ?>
                                                <?php echo date('d/m/Y H:i', strtotime($nf['nfe_issue_date'])); ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($nf['nfe_number']): ?>
                                                <?php echo htmlspecialchars($nf['nfe_number']); ?>/<?php echo htmlspecialchars($nf['nfe_series']); ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($nf['nfe_status']); ?>">
                                                <?php echo htmlspecialchars($nf['nfe_status']); ?>
                                            </span>
                                        </td>
                                        <td>R$ <?php echo number_format($nf['total'], 2, ',', '.'); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <?php if ($nf['nfe_pdf_url']): ?>
                                                    <a href="<?php echo htmlspecialchars($nf['nfe_pdf_url']); ?>" target="_blank" class="btn btn-sm btn-info" title="Visualizar PDF">
                                                        <i class="bi bi-file-pdf"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <a href="visualizar.php?id=<?php echo $nf['id']; ?>" class="btn btn-sm btn-primary" title="Visualizar Detalhes">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                
                                                <?php if (empty($nf['nfe_key'])): ?>
                                                    <a href="emitir.php?id=<?php echo $nf['id']; ?>" class="btn btn-sm btn-success" title="Emitir NFe">
                                                        <i class="bi bi-receipt"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="consultar.php?id=<?php echo $nf['id']; ?>" class="btn btn-sm btn-warning" title="Consultar Status">
                                                        <i class="bi bi-arrow-repeat"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">Nenhuma nota fiscal encontrada</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginação -->
                <?php if ($notasFiscais['last_page'] > 1): ?>
                    <nav aria-label="Navegação de página">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo http_build_query(array_filter($filters)); ?>" aria-label="Anterior">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            
                            <?php for ($i = 1; $i <= $notasFiscais['last_page']; $i++): ?>
                                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_filter($filters)); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo ($page >= $notasFiscais['last_page']) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&<?php echo http_build_query(array_filter($filters)); ?>" aria-label="Próximo">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>