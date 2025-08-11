
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Define o caminho base do projeto
$baseDir = dirname(dirname(__DIR__)); // Sobe 2 níveis até a raiz do projeto
define('PROJECT_ROOT', $baseDir);

// Habilita exibição de erros detalhados
ini_set('display_startup_errors', 1);

// Log do diretório base
error_log("Diretório base do projeto: " . $baseDir);

// Define e verifica os caminhos críticos
$paths = [
    'project_root' => $baseDir,
    'includes' => $baseDir . '/adminView/includes',
    'models' => $baseDir . '/adminView/models',
    'site' => $baseDir . '/Site'
];

// Verifica se os diretórios existem
foreach ($paths as $key => $path) {
    if (is_dir($path)) {
        error_log("Diretório {$key} existe: {$path}");
    } else {
        error_log("AVISO: Diretório {$key} não encontrado: {$path}");
    }
}

// Log dos caminhos para debug
error_log("Caminhos do projeto: " . print_r($paths, true));

// Define as constantes de caminho
define('INCLUDES_PATH', PROJECT_ROOT . '/includes');
define('MODELS_PATH', PROJECT_ROOT . '/models');
define('SITE_PATH', dirname(PROJECT_ROOT) . '/Site');

require_once __DIR__ . '/config/dbconnect.php';
require_once __DIR__ . '/config/env_loader.php';
require_once __DIR__ . '/controller/NotaFiscal/NotaFiscalController.php';

if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../../login.php');
    exit;
}

$notaFiscalController = new NotaFiscalController($conn);
$message = '';
$messageType = '';
$orderData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['emitir_nfe'])) {
    $orderId = $_POST['order_id'];
    try {
        $orderItems = $notaFiscalController->getOrderItems($orderId);
        if (empty($orderItems)) {
            throw new Exception("Pedido sem itens. Não é possível emitir a nota fiscal.");
        }
        
        $nfeInfo = $notaFiscalController->emitirNotaFiscal($orderId);
        $message = "Nota fiscal emitida com sucesso! Número: {$nfeInfo['numero']}, Série: {$nfeInfo['serie']}";
        $messageType = 'success';
    } catch (Exception $e) {
        $msg = $e->getMessage();
        $trace = $e->getTraceAsString();
        $message = "<b>Erro ao emitir nota fiscal:</b> <pre style='white-space:pre-wrap;color:#ff5555;background:#222;padding:8px;border-radius:4px;'>" . htmlspecialchars($msg) . "\n" . htmlspecialchars($trace) . "</pre>";
        $messageType = 'danger';
    }
}

if (isset($_GET['order_id'])) {
    $orderId = $_GET['order_id'];
    try {
        $orderData = $notaFiscalController->getOrderDetails($orderId);
    } catch (Exception $e) {
        $message = "Erro ao buscar pedido: " . $e->getMessage();
        $messageType = 'danger';
    }
}

// Listagem de pedidos
$orders = null;
try {
    $stmt = $conn->prepare("SELECT o.id, o.order_date, o.total, o.nfe_key, u.name as cliente_nome FROM orders o JOIN usuarios u ON o.user_id = u.id WHERE o.status = 'completed' ORDER BY o.order_date DESC LIMIT 50");
    $stmt->execute();
    $orders = $stmt->get_result();
} catch (Exception $e) {
    $message = "Erro ao buscar pedidos: " . $e->getMessage();
    $messageType = 'danger';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emitir Nota Fiscal - Bling</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-900 text-white">
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center mb-6">
            <a href="pages/index.php" class="mr-4 text-indigo-500 hover:text-indigo-400">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <h1 class="text-2xl font-bold text-indigo-400">Emitir Nota Fiscal (Bling)</h1>
        </div>
        <?php if (!empty($message)): ?>
        <div class="bg-<?php echo $messageType === 'success' ? 'green' : 'red'; ?>-900 border border-<?php echo $messageType === 'success' ? 'green' : 'red'; ?>-700 text-white px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo $message; ?></span>
            <button type="button" class="absolute top-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
                <span class="fas fa-times"></span>
            </button>
        </div>
        <?php endif; ?>
        <div class="bg-gray-800 rounded-lg shadow-md overflow-hidden mb-6">
            <div class="bg-gray-700 px-4 py-3 border-b border-gray-600">
                <div class="flex items-center">
                    <i class="fas fa-file-invoice mr-2 text-indigo-400"></i>
                    <h2 class="text-lg font-semibold text-white">Emitir Nota Fiscal via Bling</h2>
                </div>
            </div>
            <div class="p-4">
            <?php if (isset($orderData)): ?>
                <?php if (empty($orderData['nfe_key'])): ?>
                    <?php 
                    $orderItems = $notaFiscalController->getOrderItems($orderData['id']);
                    if (empty($orderItems)): 
                    ?>
                        <div class="bg-red-900 border border-red-700 text-white px-6 py-4 rounded-lg mb-6">
                            <h4 class="text-xl font-semibold text-red-300 mb-3">Erro - Pedido #<?php echo $orderData['id']; ?></h4>
                            <p class="text-white">Este pedido não possui itens. Não é possível emitir a nota fiscal.</p>
                            <div class="mt-4">
                                <a href="emitir_nfe.php" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition duration-200">Voltar</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="bg-indigo-900 border border-indigo-700 text-white px-6 py-4 rounded-lg mb-6">
                            <h4 class="text-xl font-semibold text-indigo-300 mb-3">Detalhes do Pedido #<?php echo $orderData['id']; ?></h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <p class="text-indigo-300 font-medium">Cliente</p>
                                    <p class="text-white"><?php echo $orderData['name']; ?></p>
                                </div>
                                <div>
                                    <p class="text-indigo-300 font-medium">Data</p>
                                    <p class="text-white"><?php echo date('d/m/Y H:i', strtotime($orderData['order_date'])); ?></p>
                                </div>
                                <div>
                                    <p class="text-indigo-300 font-medium">Valor Total</p>
                                    <p class="text-white font-bold">R$ <?php echo number_format($orderData['total'], 2, ',', '.'); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php if (empty($orderItems)): ?>
                            <div class="bg-red-900 border border-red-700 text-white px-6 py-4 rounded-lg mb-6">
                                <h4 class="text-xl font-semibold text-red-300 mb-3">Erro - Pedido #<?php echo $orderData['id']; ?></h4>
                                <p class="text-white">Este pedido não possui itens. Não é possível emitir a nota fiscal.</p>
                                <div class="mt-4">
                                    <a href="emitir_nfe.php" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition duration-200">Voltar</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <form method="post" onsubmit="return confirm('Tem certeza que deseja emitir a nota fiscal para este pedido?')" action="" class="bg-gray-800 p-6 rounded-lg border border-gray-700">
                                <input type="hidden" name="order_id" value="<?php echo $orderData['id']; ?>">
                                <div class="mb-6">
                                    <div class="flex items-center">
                                        <input class="h-5 w-5 text-indigo-500 border-gray-600 rounded bg-gray-700 focus:ring-indigo-400" type="checkbox" id="confirm_emission" required>
                                        <label class="ml-2 text-gray-300" for="confirm_emission">
                                            Confirmo que desejo emitir uma nota fiscal para este pedido via Bling
                                        </label>
                                    </div>
                                </div>
                                <div class="flex space-x-4">
                                    <button type="submit" name="emitir_nfe" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-200 flex items-center">
                                        <i class="fas fa-file-invoice mr-2"></i> Emitir Nota Fiscal
                                    </button>
                                    <a href="emitir_nfe.php" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition duration-200">Cancelar</a>
                                </div>
                            </form>
                        <?php endif; ?>
                        <!-- Histórico de payloads enviados para o Bling -->
                        <?php
                        $jsonLogFile = __DIR__ . '/../Site/includes/bling_pedido_payload.json';
                        if (file_exists($jsonLogFile)) {
                            $logs = json_decode(file_get_contents($jsonLogFile), true);
                            if (is_array($logs) && count($logs) > 0) {
                                echo '<div class="bg-gray-900 border border-gray-700 rounded-lg mt-8 p-6">';
                                echo '<h3 class="text-lg font-bold text-indigo-300 mb-4"><i class="fas fa-history mr-2"></i>Histórico de payloads enviados para o Bling</h3>';
                                echo '<div class="overflow-x-auto">';
                                foreach ($logs as $log) {
                                    echo '<div class="mb-4">';
                                    echo '<div class="text-sm text-gray-400 mb-1">' . date('d/m/Y H:i:s', strtotime($log['timestamp'])) . ' | Pedido: ' . htmlspecialchars($log['pedido']) . '</div>';
                                    echo '<pre class="bg-gray-800 text-gray-200 p-3 rounded border border-gray-700 text-xs">' . json_encode($log['payload'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</pre>';
                                    echo '</div>';
                                }
                                echo '</div>';
                                echo '</div>';
                            }
                        }
                        ?>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="bg-yellow-900 border border-yellow-700 text-white px-6 py-4 rounded-lg mb-6">
                        <h4 class="text-xl font-semibold text-yellow-300 mb-3">Este pedido já possui uma nota fiscal emitida</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <p class="text-yellow-300 font-medium">Número</p>
                                <p class="text-white"><?php echo $orderData['nfe_number']; ?></p>
                            </div>
                            <div>
                                <p class="text-yellow-300 font-medium">Série</p>
                                <p class="text-white"><?php echo $orderData['nfe_series']; ?></p>
                            </div>
                            <div>
                                <p class="text-yellow-300 font-medium">Status</p>
                                <p class="text-white"><?php echo $orderData['nfe_status']; ?></p>
                            </div>
                            <div>
                                <p class="text-yellow-300 font-medium">Data de Emissão</p>
                                <p class="text-white"><?php echo date('d/m/Y H:i', strtotime($orderData['nfe_issue_date'])); ?></p>
                            </div>
                        </div>
                        <a href="consultarNotaFiscal.php?order_id=<?php echo $orderData['id']; ?>" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200 flex items-center">
                            <i class="fas fa-search mr-2"></i> Consultar Status
                        </a>
                        <a href="obterPdfNotaFiscal.php?order_id=<?php echo $orderData['id']; ?>" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-200 flex items-center">
                            <i class="fas fa-file-pdf mr-2"></i> Visualizar PDF
                        </a>
                        <a href="emitir_nfe.php" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition duration-200">Voltar</a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-gray-800 text-white rounded-lg overflow-hidden" id="ordersTable">
                        <thead class="bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-indigo-300 uppercase tracking-wider">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-indigo-300 uppercase tracking-wider">Cliente</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-indigo-300 uppercase tracking-wider">Data</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-indigo-300 uppercase tracking-wider">Valor</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-indigo-300 uppercase tracking-wider">Status NFe</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-indigo-300 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            <?php if ($orders): ?>
                                <?php while ($order = $orders->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-700 transition-colors duration-200">
                                        <td class="px-4 py-3 whitespace-nowrap"><?php echo $order['id']; ?></td>
                                        <td class="px-4 py-3"><?php echo $order['cliente_nome']; ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap"><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap font-medium">R$ <?php echo number_format($order['total'], 2, ',', '.'); ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <?php if (empty($order['nfe_key'])): ?>
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-600 text-gray-200">Não emitida</span>
                                            <?php else: ?>
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-700 text-green-100">Emitida</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <?php if (empty($order['nfe_key'])): ?>
                                                <div class="flex space-x-2">
                                                    <a href="emitir_nfe.php?order_id=<?php echo $order['id']; ?>" class="px-3 py-1 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-200 inline-flex items-center text-sm">
                                                        <i class="fas fa-file-invoice mr-1"></i> Emitir
                                                    </a>
                                                </div>
                                            <?php else: ?>
                                                <div class="flex space-x-2">
                                                    <a href="obterPdfNotaFiscal.php?order_id=<?php echo $order['id']; ?>" class="px-3 py-1 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-200 inline-flex items-center text-sm">
                                                        <i class="fas fa-file-pdf mr-1"></i> Visualizar
                                                    </a>
                                                    <a href="consultarNotaFiscal.php?order_id=<?php echo $order['id']; ?>" class="px-3 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200 inline-flex items-center text-sm">
                                                        <i class="fas fa-search mr-1"></i> Status
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap5.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap5.min.css">
    <script>
        $(document).ready(function() {
            $('#ordersTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json'
                }
            });
        });
    </script>
</body>
</html>