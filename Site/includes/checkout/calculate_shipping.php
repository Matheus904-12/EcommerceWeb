<?php
// calculate_shipping.php
// Calculates shipping cost based on CEP for AJAX requests

// Start session to access user data
session_start();
header('Content-Type: application/json');

// Include database connection
require_once '../../../adminView/config/dbconnect.php';

// Validate database connection
if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco de dados']);
    exit;
}

// Verify user is logged in
$userId = (int)($_SESSION['user_id'] ?? 0);
if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

// Get subtotal from cart
try {
    $query = "SELECT p.preco, c.quantity FROM user_cart c JOIN produtos p ON c.product_id = p.id WHERE c.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $subtotal = 0;
    while ($item = $result->fetch_assoc()) {
        $subtotal += $item['preco'] * $item['quantity'];
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching cart: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao calcular subtotal']);
    exit;
}

// Get CEP from POST data
$input = json_decode(file_get_contents('php://input'), true);
$cep = $input['cep'] ?? '';
$cep = preg_replace('/\D/', '', $cep);
if (strlen($cep) !== 8) {
    echo json_encode(['success' => false, 'message' => 'CEP inválido']);
    exit;
}

// Calculate shipping
$cepBase = substr($cep, 0, 5);
$spCeps = ['01', '02', '03', '04', '05', '06', '07', '08', '09'];
$cepInicial = substr($cepBase, 0, 2);
$shipping = 0;
if ($subtotal < 350 && !in_array($cepInicial, $spCeps)) {
    if ($cepBase >= '08000' && $cepBase <= '08499') {
        $shipping = 30.00;
    } elseif ($cepBase >= '08500' && $cepBase <= '08999') {
        $shipping = 45.00;
    } elseif ($cepBase >= '09000' && $cepBase <= '09999') {
        $shipping = 60.00;
    } else {
        $shipping = 100.00;
    }
}

$total = $subtotal + $shipping;

// Return JSON response
echo json_encode([
    'success' => true,
    'shipping' => $shipping,
    'total' => $total
]);

// Close database connection
$conn->close();
?>