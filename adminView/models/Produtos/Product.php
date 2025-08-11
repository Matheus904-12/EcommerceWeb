<?php
// Caminho: adminView/model/Product.php
class Product
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getConnection()
    {
        return $this->conn;
    }

    public function createProduct($nome, $descricao, $imagem, $preco, $quantidade, $categoria, $destaque, $lancamento, $desconto, $em_alta, $comprimento, $largura, $altura, $peso, $caixa, $promocao)
    {
        $ativo = 1;
        
        $comprimento = is_numeric($comprimento) ? floatval($comprimento) : 40;
        $largura = is_numeric($largura) ? floatval($largura) : 40;
        $altura = is_numeric($altura) ? floatval($altura) : 35;
        $peso = is_numeric($peso) ? floatval($peso) : 2;
        $caixa = in_array($caixa, ['P','M','G']) ? $caixa : 'G';
        $promocao = $promocao ? 1 : 0;

        $query = "INSERT INTO produtos (nome, descricao, imagem, preco, comprimento, largura, altura, peso, quantidade, categoria, destaque, lancamento, desconto, em_alta, promocao, ativo, caixa, data_cadastro) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";

        $stmt = $this->conn->prepare($query);

        if ($stmt === false) {
            error_log("Erro ao preparar a consulta: " . $this->conn->error);
            return false;
        }

        $stmt->bind_param("sssdddddisiiddiiss", $nome, $descricao, $imagem, $preco, $comprimento, $largura, $altura, $peso, $quantidade, $categoria, $destaque, $lancamento, $desconto, $em_alta, $promocao, $ativo, $caixa);

        if ($stmt->execute()) {
            return $stmt->insert_id;
        } else {
            error_log("Erro ao executar a inserção: " . $stmt->error);
            return false;
        }
    }

    public function getAllProducts()
    {
        $query = "SELECT * FROM produtos WHERE ativo = 1 ORDER BY data_cadastro DESC";
        $result = $this->conn->query($query);
        
        if ($result === false) {
            error_log("Erro ao executar consulta: " . $this->conn->error);
            return [];
        }
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getProductsByCategory($categoria)
    {
        $query = "SELECT * FROM produtos WHERE categoria = ? AND ativo = 1 ORDER BY data_cadastro DESC";
        $stmt = $this->conn->prepare($query);

        if ($stmt === false) {
            error_log("Erro ao preparar a consulta: " . $this->conn->error);
            return [];
        }

        $stmt->bind_param("s", $categoria);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getProductsOrderedBy($orderBy = 'destaque')
    {
        $query = "SELECT * FROM produtos WHERE ativo = 1";

        switch ($orderBy) {
            case 'promocao':
                $query .= " AND (desconto > 0 OR lancamento = 1 OR em_alta = 1) ORDER BY desconto DESC, data_cadastro DESC";
                break;
            case 'baratos':
                $query .= " ORDER BY preco ASC";
                break;
            case 'caros':
                $query .= " ORDER BY preco DESC";
                break;
            case 'destaque':
                $query .= " AND destaque = 1 ORDER BY data_cadastro DESC";
                break;
            default:
                $query .= " ORDER BY data_cadastro DESC";
                break;
        }

        $result = $this->conn->query($query);
        
        if ($result === false) {
            error_log("Erro ao executar consulta: " . $this->conn->error);
            return [];
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getProductById($id)
    {
        if (!is_numeric($id) || $id <= 0) {
            return false;
        }

        $query = "SELECT * FROM produtos WHERE id = ? AND ativo = 1";
        $stmt = $this->conn->prepare($query);

        if ($stmt === false) {
            error_log("Erro ao preparar a consulta: " . $this->conn->error);
            return false;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function updateProduct($id, $nome, $descricao, $imagem, $preco, $quantidade, $categoria, $destaque, $lancamento, $desconto, $em_alta, $comprimento, $largura, $altura, $peso, $caixa, $promocao)
    {
        if (!is_numeric($id) || $id <= 0) {
            return false;
        }

        $comprimento = is_numeric($comprimento) ? floatval($comprimento) : 40;
        $largura = is_numeric($largura) ? floatval($largura) : 40;
        $altura = is_numeric($altura) ? floatval($altura) : 35;
        $peso = is_numeric($peso) ? floatval($peso) : 2;
        $caixa = in_array($caixa, ['P','M','G']) ? $caixa : 'G';
        $promocao = $promocao ? 1 : 0;

        if (empty($imagem)) {
            $query = "UPDATE produtos SET nome = ?, descricao = ?, preco = ?, comprimento = ?, largura = ?, altura = ?, peso = ?, quantidade = ?, categoria = ?, destaque = ?, lancamento = ?, desconto = ?, em_alta = ?, promocao = ?, caixa = ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            
            if ($stmt === false) {
                error_log("Erro ao preparar a consulta: " . $this->conn->error);
                return false;
            }
            
            $stmt->bind_param("ssdddddiisiiddisi", $nome, $descricao, $preco, $comprimento, $largura, $altura, $peso, $quantidade, $categoria, $destaque, $lancamento, $desconto, $em_alta, $promocao, $caixa, $id);
        } else {
            $query = "UPDATE produtos SET nome = ?, descricao = ?, imagem = ?, preco = ?, comprimento = ?, largura = ?, altura = ?, peso = ?, quantidade = ?, categoria = ?, destaque = ?, lancamento = ?, desconto = ?, em_alta = ?, promocao = ?, caixa = ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            
            if ($stmt === false) {
                error_log("Erro ao preparar a consulta: " . $this->conn->error);
                return false;
            }
            
            $stmt->bind_param("sssdddddiisiiddisi", $nome, $descricao, $imagem, $preco, $comprimento, $largura, $altura, $peso, $quantidade, $categoria, $destaque, $lancamento, $desconto, $em_alta, $promocao, $caixa, $id);
        }

        return $stmt->execute();
    }

    public function deleteProduct($id)
    {
        if (!is_numeric($id) || $id <= 0) {
            return false;
        }

        $query = "UPDATE produtos SET ativo = 0 WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        if ($stmt === false) {
            error_log("Erro ao preparar a consulta: " . $this->conn->error);
            return false;
        }

        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function searchProducts($termo)
    {
        $termo = "%$termo%";
        $query = "SELECT * FROM produtos WHERE (nome LIKE ? OR descricao LIKE ? OR categoria LIKE ?) AND ativo = 1";
        $stmt = $this->conn->prepare($query);

        if ($stmt === false) {
            error_log("Erro ao preparar a consulta: " . $this->conn->error);
            return [];
        }

        $stmt->bind_param("sss", $termo, $termo, $termo);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getFeaturedProducts($limit = 6)
    {
        $query = "SELECT * FROM produtos WHERE ativo = 1 AND destaque = 1 ORDER BY data_cadastro DESC LIMIT ?";
        $stmt = $this->conn->prepare($query);

        if ($stmt === false) {
            error_log("Erro ao preparar a consulta: " . $this->conn->error);
            return [];
        }

        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getPromoProducts($limit = 3)
    {
        $query = "SELECT * FROM produtos WHERE ativo = 1 AND (promocao = 1 OR desconto > 0 OR lancamento = 1 OR em_alta = 1) 
                  ORDER BY promocao DESC, desconto DESC, lancamento DESC, em_alta DESC, data_cadastro DESC LIMIT ?";
        $stmt = $this->conn->prepare($query);

        if ($stmt === false) {
            error_log("Erro ao preparar a consulta: " . $this->conn->error);
            return [];
        }

        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>
