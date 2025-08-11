<?php
session_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/php_errors.log');
error_log('Iniciando processa_avaliacao.php');

header('Content-Type: application/json');

try {
    // Incluir conexão com o banco
    $possible_paths = [
        '../../config/dbconnect.php',
        '../config/dbconnect.php',
        'config/dbconnect.php'
    ];
    $included = false;
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            include $path;
            $included = true;
            break;
        }
    }
    if (!$included) {
        throw new Exception('Arquivo de configuração do banco não encontrado');
    }

    if (!isset($conn) || $conn->connect_error) {
        throw new Exception('Falha na conexão com o banco de dados: ' . (isset($conn) ? $conn->connect_error : 'Conexão não definida'));
    }
    error_log('Conexão com banco estabelecida');
} catch (Exception $e) {
    error_log('Erro de conexão: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco.']);
    exit();
}

// Verificar se é uma requisição AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
if (!$isAjax) {
    error_log('Requisição não é AJAX');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Requisição inválida.']);
    exit();
}

// Processar requisições GET (buscar avaliações e comentários)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
    error_log('Requisição GET com product_id: ' . $product_id);

    try {
        if ($product_id <= 0) {
            throw new Exception('ID do produto inválido.');
        }

        // Buscar média de avaliações
        $stmt = $conn->prepare("SELECT AVG(avaliacao) as avg_rating, COUNT(*) as total_ratings FROM produto_avaliacoes WHERE post_id = ?");
        if (!$stmt) {
            throw new Exception('Erro na preparação da query SELECT avaliações: ' . $conn->error);
        }
        
        $stmt->bind_param("i", $product_id);
        if (!$stmt->execute()) {
            throw new Exception('Erro ao executar query SELECT avaliações: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $avg_rating = $row['avg_rating'] ? round($row['avg_rating'], 1) : 0;
        $total_ratings = (int)$row['total_ratings'];
        $stmt->close();
        error_log('Avaliações buscadas para product_id: ' . $product_id);

        // Buscar comentários
        $stmt = $conn->prepare("
            SELECT pc.comentario, pc.data_comentario as created_at, COALESCE(u.name, 'Anônimo') as user_name 
            FROM produto_comentarios pc 
            LEFT JOIN usuarios u ON pc.user_id = u.id 
            WHERE pc.produto_id = ? 
            ORDER BY pc.data_comentario DESC
        ");
        if (!$stmt) {
            throw new Exception('Erro na preparação da query SELECT comentários: ' . $conn->error);
        }
        
        $stmt->bind_param("i", $product_id);
        if (!$stmt->execute()) {
            throw new Exception('Erro ao executar query SELECT comentários: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $comments = [];
        while ($row_comment = $result->fetch_assoc()) {
            $comments[] = [
                'user_name' => htmlspecialchars($row_comment['user_name']),
                'comment_text' => htmlspecialchars($row_comment['comentario']),
                'created_at' => $row_comment['created_at']
            ];
        }
        $stmt->close();
        error_log('Comentários buscados para product_id: ' . $product_id . ', total: ' . count($comments));

        $response = [
            'success' => true,
            'avg_rating' => $avg_rating,
            'total_ratings' => $total_ratings,
            'comments' => $comments
        ];
        error_log('GET bem-sucedido para product_id: ' . $product_id);
        
    } catch (Exception $e) {
        error_log('Erro na requisição GET: ' . $e->getMessage() . ' na linha ' . $e->getLine());
        http_response_code(500);
        $response = ['success' => false, 'message' => 'Erro ao processar requisição: ' . $e->getMessage()];
    }

    echo json_encode($response);
    exit();
}

// Processar requisições POST (salvar avaliação ou comentário)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Erro no JSON: ' . json_last_error_msg());
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'JSON inválido: ' . json_last_error_msg()]);
        exit();
    }

    $post_id = isset($input['post_id']) ? intval($input['post_id']) : 0;
    $rating = isset($input['rating']) ? intval($input['rating']) : 0;
    $comment = isset($input['comment']) ? trim($input['comment']) : null;
    $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
    $ip_usuario = $_SERVER['REMOTE_ADDR'];
    error_log('Requisição POST com post_id: ' . $post_id . ', rating: ' . $rating . ', comment: ' . ($comment ?? 'null') . ', user_id: ' . ($user_id ?? 'null'));

    try {
        if ($post_id <= 0) {
            throw new Exception('ID do produto inválido.');
        }

        // Verificar se o produto existe
        $stmt = $conn->prepare("SELECT id FROM produtos WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Erro na preparação da query CHECK produto: ' . $conn->error);
        }
        
        $stmt->bind_param("i", $post_id);
        if (!$stmt->execute()) {
            throw new Exception('Erro ao executar query CHECK produto: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $stmt->close();
            throw new Exception('Produto não encontrado.');
        }
        $stmt->close();
        error_log('Produto verificado: post_id=' . $post_id);

        // Processar avaliação (se fornecida)
        if ($rating >= 1 && $rating <= 5) {
            // Validar avaliação
            if ($rating < 1 || $rating > 5) {
                throw new Exception('Avaliação deve ser entre 1 e 5.');
            }

            // Inserir ou atualizar avaliação
            $stmt = $conn->prepare("
                INSERT INTO produto_avaliacoes (post_id, avaliacao, ip_usuario, data_avaliacao) 
                VALUES (?, ?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE avaliacao = ?, data_avaliacao = NOW()
            ");
            if (!$stmt) {
                throw new Exception('Erro na preparação da query INSERT rating: ' . $conn->error);
            }
            
            $stmt->bind_param("iisi", $post_id, $rating, $ip_usuario, $rating);
            if (!$stmt->execute()) {
                throw new Exception('Erro ao executar query INSERT rating: ' . $stmt->error);
            }
            $stmt->close();
            error_log('Avaliação inserida/atualizada para post_id=' . $post_id);
        }

        // Processar comentário (se fornecido)
        if ($comment) {
            // Inserir comentário
            $stmt = $conn->prepare("
                INSERT INTO produto_comentarios (produto_id, user_id, ip_usuario, comentario, data_comentario) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            if (!$stmt) {
                throw new Exception('Erro na preparação da query INSERT comment: ' . $conn->error);
            }
            
            $stmt->bind_param("iiss", $post_id, $user_id, $ip_usuario, $comment);
            if (!$stmt->execute()) {
                throw new Exception('Erro ao executar query INSERT comment: ' . $stmt->error);
            }
            $stmt->close();
            error_log('Comentário inserido para post_id=' . $post_id);
        }

        // Calcular nova média de avaliações
        $stmt = $conn->prepare("SELECT AVG(avaliacao) as avg_rating, COUNT(*) as total_ratings FROM produto_avaliacoes WHERE post_id = ?");
        if (!$stmt) {
            throw new Exception('Erro na preparação da query AVG: ' . $conn->error);
        }
        
        $stmt->bind_param("i", $post_id);
        if (!$stmt->execute()) {
            throw new Exception('Erro ao executar query AVG: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        $response = [
            'success' => true,
            'message' => ($rating ? 'Avaliação' : 'Comentário') . ' registrado com sucesso!',
            'avg_rating' => $row['avg_rating'] ? round($row['avg_rating'], 1) : 0,
            'total_ratings' => (int)$row['total_ratings']
        ];
        error_log('POST bem-sucedido para post_id: ' . $post_id);
        
    } catch (Exception $e) {
        error_log('Erro na requisição POST: ' . $e->getMessage() . ' na linha ' . $e->getLine());
        http_response_code(500);
        $response = ['success' => false, 'message' => 'Erro ao processar requisição: ' . $e->getMessage()];
    }

    echo json_encode($response);
    exit();
}

// Método não permitido
error_log('Método não permitido: ' . $_SERVER['REQUEST_METHOD']);
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
exit();
?>