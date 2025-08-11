<?php
// Caminho: adminView/controller/ProductController.php
require_once __DIR__ . '/../../models/Produtos/Product.php';

class ProductController
{
    private $productModel;
    private $conn;

    public function __construct($conn)
    {
        $this->productModel = new Product($conn);
        $this->conn = $conn;
    }

    public function getProductSalesHistory($productId)
    {
        $sql = "SELECT o.id, o.order_date as data_venda, o.total as valor_total, 
                oi.price_at_purchase as preco_unitario, oi.quantity as quantidade 
                FROM orders o 
                JOIN order_items oi ON o.id = oi.order_id 
                WHERE oi.product_id = ? 
                ORDER BY o.order_date DESC";

        $conn = $this->productModel->getConnection();
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            throw new Exception("Erro na preparação da consulta MySQLi: " . $conn->error);
        }

        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    private function handleImageUpload($file)
    {
        $targetDir = __DIR__ . "/../../uploads/produtos/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        if (empty($file) || empty($file["name"])) {
            return false;
        }

        $originalFileName = basename($file["name"]);
        $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
        $sanitizedFileName = preg_replace("/[^a-zA-Z0-9.]/", "_", $originalFileName);
        $uniqueFileName = time() . '_' . $sanitizedFileName;
        $targetFilePath = $targetDir . $uniqueFileName;

        $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'webp');

        if (in_array($fileExtension, $allowTypes)) {
            $check = getimagesize($file["tmp_name"]);
            if ($check === false) {
                error_log("Erro: Arquivo inválido - Não é uma imagem válida: " . $originalFileName);
                return false;
            }

            if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
                return $uniqueFileName;
            } else {
                error_log("Erro: Falha ao mover o arquivo para $targetFilePath. Permissões ou espaço em disco?");
                return false;
            }
        }
        error_log("Erro: Tipo de arquivo não suportado: " . $fileExtension);
        return false;
    }

    public function getAllCategories()
    {
        $sql = "SELECT DISTINCT categoria as nome FROM produtos WHERE ativo = 1 AND categoria IS NOT NULL ORDER BY categoria ASC";
        $conn = $this->productModel->getConnection();
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            throw new Exception("Erro na preparação da consulta MySQLi: " . $conn->error);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function createProduct($data, $file)
    {
        try {
            if (empty($data['nome']) || empty($data['preco'])) {
                return array('success' => false, 'message' => 'Nome e preço são obrigatórios');
            }

            if (!is_numeric($data['preco']) || $data['preco'] <= 0) {
                return array('success' => false, 'message' => 'Preço inválido');
            }

            $quantidade = isset($data['quantidade']) && is_numeric($data['quantidade']) && $data['quantidade'] >= 0 && floor($data['quantidade']) == $data['quantidade'] ? $data['quantidade'] : 0;

            if (isset($data['desconto']) && (!is_numeric($data['desconto']) || $data['desconto'] < 0 || $data['desconto'] > 100)) {
                return array('success' => false, 'message' => 'Desconto inválido (0 a 100%)');
            }

            // Tratar dimensões e peso
            if (!is_numeric($data['comprimento']) || $data['comprimento'] <= 0) {
                return array('success' => false, 'message' => 'Comprimento inválido.');
            }
            if (!is_numeric($data['largura']) || $data['largura'] <= 0) {
                return array('success' => false, 'message' => 'Largura inválida.');
            }
            if (!is_numeric($data['altura']) || $data['altura'] <= 0) {
                return array('success' => false, 'message' => 'Altura inválida.');
            }
            if (!is_numeric($data['peso']) || $data['peso'] <= 0) {
                return array('success' => false, 'message' => 'Peso inválido.');
            }
            $comprimento = floatval($data['comprimento']);
            $largura = floatval($data['largura']);
            $altura = floatval($data['altura']);
            $peso = floatval($data['peso']);

            $imagePath = "";
            if (isset($file) && !empty($file['name'])) { // Verifica diretamente o arquivo
    $imagePath = $this->handleImageUpload($file);
    if (!$imagePath) {
        return array('success' => false, 'message' => 'Erro ao fazer upload da imagem');
    }
}

            $destaque = isset($data['destaque']) ? 1 : 0;
            $lancamento = isset($data['lancamento']) ? 1 : 0;
            $emAlta = isset($data['em_alta']) ? 1 : 0;
            $desconto = $data['desconto'] ?? 0;
            $promocao = isset($data['promocao']) ? 1 : 0;

            // Categorias válidas
            $categoriasValidas = ['Arranjos', 'Vasos de Vidro', 'Muranos', 'Muranos Color', 'Vaso Cerâmica'];
            if (!in_array($data['categoria'], $categoriasValidas)) {
                return array('success' => false, 'message' => 'Categoria inválida. Escolha uma das opções disponíveis.');
            }

            $result = $this->productModel->createProduct(
                $data['nome'],
                $data['descricao'],
                $imagePath,
                $data['preco'],
                $quantidade,
                $data['categoria'],
                $destaque,
                $lancamento,
                $desconto,
                $emAlta,
                $comprimento,
                $largura,
                $altura,
                $peso,
                $data['caixa'] ?? 'G',
                $promocao
            );

            if ($result) {
                return array('success' => true, 'message' => 'Produto adicionado com sucesso');
            } else {
                return array('success' => false, 'message' => 'Erro ao adicionar produto');
            }
        } catch (\Throwable $e) {
            file_put_contents(__DIR__ . '/../../../product_error.log', "[".date('Y-m-d H:i:s')."] ".$e->getMessage()."\n".$e->getTraceAsString()."\n", FILE_APPEND);
            return array('success' => false, 'message' => 'Erro inesperado ao salvar produto: ' . $e->getMessage());
        }
    }

    public function getProductsByPriceRange($range)
    {
        $query = "SELECT * FROM produtos WHERE ativo = 1";
        $conn = $this->productModel->getConnection();
        if ($range !== 'all') {
            list($min, $max) = explode('-', $range);
            $query .= " AND preco >= ? AND " . ($max ? "preco <= ?" : "preco >= ?");
        }
        $stmt = $conn->prepare($query);

        if ($stmt === false) {
            throw new Exception("Erro na preparação da consulta MySQLi: " . $conn->error);
        }

        if ($range !== 'all') {
            if ($max) {
                $stmt->bind_param("dd", $min, $max);
            } else {
                $stmt->bind_param("d", $min);
            }
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getCarouselImages()
    {
        try {
            $query = "SELECT * FROM carousel_images WHERE status = 1 ORDER BY ordem ASC";
            $stmt = $this->conn->prepare($query);
            
            if ($stmt === false) {
                throw new Exception("Erro na preparação da consulta: " . $this->conn->error);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $images = [];
            
            while ($row = $result->fetch_assoc()) {
                $images[] = [
                    'id' => $row['id'],
                    'imagem' => $row['imagem'],
                    'titulo' => $row['titulo'],
                    'descricao' => $row['descricao'],
                    'link' => $row['link']
                ];
            }
            
            return $images;
        } catch (Exception $e) {
            error_log("Erro ao buscar imagens do carrossel: " . $e->getMessage());
            return [];
        }
    }

    // Adicionar métodos para gerenciar o carrossel
    public function addCarouselImage($data, $file)
    {
        try {
            $imagePath = $this->handleCarouselImageUpload($file);
            if (!$imagePath) {
                throw new Exception("Erro ao fazer upload da imagem");
            }

            $query = "INSERT INTO carousel_images (imagem, titulo, descricao, link, ordem, status) 
                     VALUES (?, ?, ?, ?, ?, 1)";
            $stmt = $this->conn->prepare($query);
            
            if ($stmt === false) {
                throw new Exception("Erro na preparação da consulta: " . $this->conn->error);
            }

            $stmt->bind_param("ssssi", 
                $imagePath,
                $data['titulo'],
                $data['descricao'],
                $data['link'],
                $data['ordem']
            );

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Erro ao adicionar imagem ao carrossel: " . $e->getMessage());
            return false;
        }
    }

    private function handleCarouselImageUpload($file)
    {
        $targetDir = __DIR__ . "/../../uploads/carousel/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        if (empty($file) || empty($file["name"])) {
            return false;
        }

        $originalFileName = basename($file["name"]);
        $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
        $uniqueFileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $originalFileName);
        $targetFilePath = $targetDir . $uniqueFileName;

        $allowTypes = array('jpg', 'png', 'jpeg', 'webp');

        if (!in_array($fileExtension, $allowTypes)) {
            error_log("Tipo de arquivo não permitido: " . $fileExtension);
            return false;
        }

        $check = getimagesize($file["tmp_name"]);
        if ($check === false) {
            error_log("Arquivo não é uma imagem válida");
            return false;
        }

        if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
            return $uniqueFileName;
        }

        error_log("Erro ao mover o arquivo para: " . $targetFilePath);
        return false;
    }

    public function updateCarouselImage($id, $data, $file = null)
    {
        try {
            $setFields = [];
            $params = [];
            $types = "";

            if (!empty($file['name'])) {
                $imagePath = $this->handleCarouselImageUpload($file);
                if ($imagePath) {
                    $setFields[] = "imagem = ?";
                    $params[] = $imagePath;
                    $types .= "s";
                }
            }

            if (isset($data['titulo'])) {
                $setFields[] = "titulo = ?";
                $params[] = $data['titulo'];
                $types .= "s";
            }

            if (isset($data['descricao'])) {
                $setFields[] = "descricao = ?";
                $params[] = $data['descricao'];
                $types .= "s";
            }

            if (isset($data['link'])) {
                $setFields[] = "link = ?";
                $params[] = $data['link'];
                $types .= "s";
            }

            if (isset($data['ordem'])) {
                $setFields[] = "ordem = ?";
                $params[] = $data['ordem'];
                $types .= "i";
            }

            if (empty($setFields)) {
                return false;
            }

            $query = "UPDATE carousel_images SET " . implode(", ", $setFields) . " WHERE id = ?";
            $params[] = $id;
            $types .= "i";

            $stmt = $this->conn->prepare($query);
            if ($stmt === false) {
                throw new Exception("Erro na preparação da consulta: " . $this->conn->error);
            }

            $stmt->bind_param($types, ...$params);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Erro ao atualizar imagem do carrossel: " . $e->getMessage());
            return false;
        }
    }

    public function deleteCarouselImage($id)
    {
        try {
            // Primeiro, busca a imagem atual
            $query = "SELECT imagem FROM carousel_images WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $image = $result->fetch_assoc();

            // Delete do banco
            $query = "DELETE FROM carousel_images WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                // Se deletou do banco, deleta o arquivo
                if ($image && !empty($image['imagem'])) {
                    $filepath = __DIR__ . "/../../uploads/carousel/" . $image['imagem'];
                    if (file_exists($filepath)) {
                        unlink($filepath);
                    }
                }
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Erro ao deletar imagem do carrossel: " . $e->getMessage());
            return false;
        }
    }

    public function getAllProducts()
    {
        return $this->productModel->getAllProducts();
    }

    public function getProductsByCategory($categoria)
    {
        return $this->productModel->getProductsByCategory($categoria);
    }

    public function getProductsOrderedBy($orderBy)
    {
        return $this->productModel->getProductsOrderedBy($orderBy);
    }

    public function getProductById($id)
    {
        return $this->productModel->getProductById($id);
    }

    public function updateProduct($id, $data, $file)
    {
        try {
            if (empty($data['nome']) || empty($data['preco'])) {
                return array('success' => false, 'message' => 'Nome e preço são obrigatórios');
            }

            if (!is_numeric($data['preco']) || $data['preco'] <= 0) {
                return array('success' => false, 'message' => 'Preço inválido');
            }

            $quantidade = isset($data['quantidade']) && is_numeric($data['quantidade']) && $data['quantidade'] >= 0 && floor($data['quantidade']) == $data['quantidade'] ? $data['quantidade'] : 0;

            if (isset($data['desconto']) && (!is_numeric($data['desconto']) || $data['desconto'] < 0 || $data['desconto'] > 100)) {
                return array('success' => false, 'message' => 'Desconto inválido (0 a 100%)');
            }

            // Tratar dimensões e peso
            $comprimento = isset($data['comprimento']) && is_numeric($data['comprimento']) ? floatval($data['comprimento']) : 40;
            $largura = isset($data['largura']) && is_numeric($data['largura']) ? floatval($data['largura']) : 40;
            $altura = isset($data['altura']) && is_numeric($data['altura']) ? floatval($data['altura']) : 35;
            $peso = isset($data['peso']) && is_numeric($data['peso']) ? floatval($data['peso']) : 2;

            $imagePath = '';
            if (isset($file) && !empty($file['name'])) { // Verifica diretamente o arquivo
    $imagePath = $this->handleImageUpload($file);
    if (!$imagePath) {
        return array('success' => false, 'message' => 'Erro ao fazer upload da imagem');
    }
}

            $destaque = isset($data['destaque']) ? 1 : 0;
            $lancamento = isset($data['lancamento']) ? 1 : 0;
            $emAlta = isset($data['em_alta']) ? 1 : 0;
            $desconto = $data['desconto'] ?? 0;
            $promocao = isset($data['promocao']) ? 1 : 0;

            $result = $this->productModel->updateProduct(
                $id,
                $data['nome'],
                $data['descricao'],
                $imagePath,
                $data['preco'],
                $quantidade,
                $data['categoria'],
                $destaque,
                $lancamento,
                $desconto,
                $emAlta,
                $comprimento,
                $largura,
                $altura,
                $peso,
                $data['caixa'] ?? 'G',
                $promocao
            );

            if ($result) {
                return array('success' => true, 'message' => 'Produto atualizado com sucesso');
            } else {
                return array('success' => false, 'message' => 'Erro ao atualizar produto');
            }
        } catch (\Throwable $e) {
            file_put_contents(__DIR__ . '/../../../product_error.log', "[".date('Y-m-d H:i:s')."] ".$e->getMessage()."\n".$e->getTraceAsString()."\n", FILE_APPEND);
            return array('success' => false, 'message' => 'Erro inesperado ao atualizar produto: ' . $e->getMessage());
        }
    }

    public function deleteProduct($id)
    {
        if (!is_numeric($id) || $id <= 0) {
            return array('success' => false, 'message' => 'ID de produto inválido');
        }

        $product = $this->productModel->getProductById($id);

        if ($product) {
            $result = $this->productModel->deleteProduct($id);
            if ($result && !empty($product['imagem']) && file_exists(__DIR__ . "/../../uploads/produtos/" . $product['imagem'])) {
                @unlink(__DIR__ . "/../../uploads/produtos/" . $product['imagem']);
            }
            return array('success' => true, 'message' => 'Produto excluído com sucesso');
        }
        return array('success' => false, 'message' => 'Produto não encontrado');
    }

    public function searchProducts($termo)
    {
        return $this->productModel->searchProducts($termo);
    }

    public function updateProductStatus($productId, $status)
    {
        $sql = "UPDATE produtos SET ativo = ? WHERE id = ?";
        $conn = $this->productModel->getConnection();
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            error_log("Erro ao preparar consulta: " . $conn->error);
            return false;
        }

        $stmt->bind_param("ii", $status, $productId);
        return $stmt->execute();
    }

    public function getFeaturedProducts($limit = 6)
    {
        return $this->productModel->getFeaturedProducts($limit);
    }

    public function getPromoProducts($limit = 3)
    {
        return $this->productModel->getPromoProducts($limit);
    }
}
