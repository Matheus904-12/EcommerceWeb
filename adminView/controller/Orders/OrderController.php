<?php
require_once __DIR__ . '/../controller/Bling/BlingIntegrationController.php';

class OrderController {
    private $conn;
    private $blingIntegration;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->blingIntegration = new BlingIntegrationController($conn, getenv('BLING_API_KEY'));
    }

    public function createOrder($userData, $cartItems, $paymentMethod) {
        try {
            $this->conn->begin_transaction();

            // Criar o pedido no banco de dados local
            $stmt = $this->conn->prepare("INSERT INTO orders (user_id, total, payment_method, status) VALUES (?, ?, ?, 'pending')");
            $total = $this->calculateTotal($cartItems);
            $stmt->bind_param("ids", $userData['id'], $total, $paymentMethod);
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao criar pedido: " . $stmt->error);
            }

            $orderId = $this->conn->insert_id;

            // Inserir itens do pedido
            $this->insertOrderItems($orderId, $cartItems);

            // Criar pedido no Bling
            try {
                $orderData = [
                    'id' => $orderId,
                    'id_externo_bling' => $userData['id_externo_bling'],
                    'payment_method' => $paymentMethod
                ];
                
                $blingResponse = $this->blingIntegration->criarPedido($orderData, $cartItems);
                
                // Atualizar o pedido com as informações do Bling
                $stmt = $this->conn->prepare("UPDATE orders SET bling_pedido_id = ? WHERE id = ?");
                $blingPedidoId = $blingResponse['id'];
                $stmt->bind_param("si", $blingPedidoId, $orderId);
                $stmt->execute();
                
            } catch (Exception $e) {
                // Log do erro mas continua com o pedido
                error_log("Erro ao criar pedido no Bling: " . $e->getMessage());
                // Opcionalmente, você pode decidir se quer continuar ou não
            }

            $this->conn->commit();
            return $orderId;

        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Erro no processo de criação de pedido: " . $e->getMessage());
            throw $e;
        }
    }

    private function calculateTotal($cartItems) {
        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item['preco'] * $item['quantidade'];
        }
        return $total;
    }

    private function insertOrderItems($orderId, $cartItems) {
        $stmt = $this->conn->prepare("INSERT INTO order_items (order_id, produto_id, quantidade, preco) VALUES (?, ?, ?, ?)");
        
        foreach ($cartItems as $item) {
            $stmt->bind_param("iiid", $orderId, $item['produto_id'], $item['quantidade'], $item['preco']);
            if (!$stmt->execute()) {
                throw new Exception("Erro ao inserir item do pedido: " . $stmt->error);
            }
        }
    }
}
