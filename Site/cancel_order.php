<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit;
}

if (!isset($_POST['order_id']) || !is_numeric($_POST['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID do pedido inválido.']);
    exit;
}

require_once '../adminView/config/dbconnect.php';

$orderId = (int)$_POST['order_id'];
$userId = $_SESSION['user_id'];

// Verifica se o pedido pertence ao usuário
$stmt = $conn->prepare("SELECT id, status FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $orderId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Pedido não encontrado.']);
    exit;
}

// Só permite cancelar se não estiver já cancelado ou entregue
if ($order['status'] === 'cancelado' || $order['status'] === 'entregue') {
    echo json_encode(['success' => false, 'message' => 'Este pedido não pode ser cancelado.']);
    exit;
}

// Atualiza o status para cancelado
$stmt = $conn->prepare("UPDATE orders SET status = 'cancelado' WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $orderId, $userId);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao cancelar o pedido.']);
} 