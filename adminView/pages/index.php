<?php
session_start();
include '../config/dbconnect.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Inicializa um array para armazenar os dados
$data = [
    'total_usuarios' => 0,
    'total_logins' => 0,
    'rastreio_entregas' => 0,
    'total_estoque' => 0,
    'total_duvidas' => 0,
    'total_pedidos' => 0
];

// Define todas as consultas SQL
$queries = [
    // Usuários cadastrados
    "total_usuarios" => "SELECT COUNT(*) AS total FROM usuarios",
    // Administradores (tabela admins)
    "total_logins" => "SELECT COUNT(*) AS total FROM admins",
    // Rastreamento (pedidos com código de rastreio não vazio)
    "rastreio_entregas" => "SELECT COUNT(*) AS total FROM orders WHERE tracking_code IS NOT NULL AND tracking_code != ''",
    // Estoque (total de produtos cadastrados)
    "total_estoque" => "SELECT COUNT(*) AS total FROM produtos",
    // Comentários (tabela produto_comentarios)
    "total_duvidas" => "SELECT COUNT(*) AS total FROM produto_comentarios",
    // Pedidos (tabela orders)
    "total_pedidos" => "SELECT COUNT(*) AS total FROM orders"
];

// Executa cada consulta e armazena os resultados
try {
    foreach ($queries as $key => $sql) {
        $stmt = $pdo->query($sql);
        if ($stmt) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $data[$key] = $row['total'];
        }
    }
} catch (PDOException $e) {
    error_log("Erro na consulta SQL: " . $e->getMessage());
    // Opcional: Mostrar mensagem de erro amigável para o usuário
    // echo "Ocorreu um erro ao carregar os dados do dashboard.";
}

include '../includes/adminHeader.php';
include '../includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="shortcut icon" href="../assets/images/logo.png" type="image/png">
    <link rel="icon" href="../assets/images/logo.png" type="image/png">
    
    <style>
        /* Estilos customizados para manter aparência antiga */
        body {
            background-color: #111827;
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-container {
            padding: 2rem;
        }
        
        .dashboard-title {
            font-size: 1.875rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        
        .dashboard-subtitle {
            color: #9ca3af;
            margin-bottom: 1.5rem;
        }
        
        /* Cards Informativos */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .dashboard-card {
            background-color: #1f2937;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: transform 0.3s ease;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        
        .card-icon {
            font-size: 2.5rem;
            color: #fbbf24;
            margin-bottom: 1rem;
        }
        
        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .card-value {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        /* Botão customizado */
        .stockBtn {
            display: inline-block;
            background-color: #f59e0b;
            color: white;
            border-radius: 0.375rem;
            padding: 0.5rem 1rem;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .stockBtn:hover {
            background-color: #d97706;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
            color: white;
        }
    </style>
</head>

<body>
    <main class="container main-container">

        <!-- Conteúdo Padrão do Dashboard -->
        <div id="defaultContent">
            <h2 class="dashboard-title">Bem-vindo ao Painel Administrativo - Cristais Gold Lar</h2>
            <p class="dashboard-subtitle">Use o menu lateral para gerenciar o conteúdo do site.</p>
            
            <a href="../visualizar_produtos.php" class="stockBtn">Ver Estoque</a>
            <br><br>
            
            <!-- Cards Informativos -->
            <div class="dashboard-grid">
                <?php
                $cards = [
                    ["icon" => "fa-sign-in-alt", "title" => "Administradores", "value" => $data['total_logins']],
                    ["icon" => "fa-truck", "title" => "Rastreamento", "value" => $data['rastreio_entregas']],
                    ["icon" => "fa-user-plus", "title" => "Cadastros", "value" => $data['total_usuarios']],
                    ["icon" => "fa-boxes", "title" => "Estoque", "value" => $data['total_estoque']],
                    ["icon" => "fa-comments", "title" => "Comentários", "value" => $data['total_duvidas']],
                    ["icon" => "fa-clipboard-list", "title" => "Pedidos", "value" => $data['total_pedidos']],
                ];

                foreach ($cards as $card) {
                    echo '<div class="dashboard-card">';
                    echo '<i class="fas ' . htmlspecialchars($card['icon']) . ' card-icon"></i>';
                    echo '<h4 class="card-title">' . htmlspecialchars($card['title']) . '</h4>';
                    echo '<h5 class="card-value">' . number_format($card['value']) . '</h5>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>

        <!-- Aqui será carregado o conteúdo da sidebar via AJAX -->
        <div class="allContent-section mt-4"></div>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    
    <!-- Scripts customizados -->
    <script src="../assets/js/script.js"></script>

    <script>
        // Função para carregar páginas via AJAX
        function loadPage(page) {
            // Validação básica da URL
            if (!page || typeof page !== 'string') {
                console.error('Página inválida');
                return;
            }
            
            // Mostra loading
            $(".allContent-section").html('<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Carregando...</p></div>');
            
            $.ajax({
                url: page,
                type: "GET",
                timeout: 10000, // 10 segundos de timeout
                success: function(response) {
                    $("#defaultContent").hide();
                    $(".allContent-section").html(response).show();
                },
                error: function(xhr, status, error) {
                    console.error("Erro ao carregar a página:", {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        error: error
                    });
                    
                    let errorMessage = '<div class="alert alert-danger" role="alert">';
                    errorMessage += '<i class="fas fa-exclamation-triangle me-2"></i>';
                    errorMessage += '<strong>Erro ao carregar a página!</strong><br>';
                    
                    if (xhr.status === 404) {
                        errorMessage += 'Página não encontrada (404).';
                    } else if (xhr.status === 500) {
                        errorMessage += 'Erro interno do servidor (500).';
                    } else if (status === 'timeout') {
                        errorMessage += 'Tempo limite excedido. Tente novamente.';
                    } else {
                        errorMessage += 'Erro desconhecido. Verifique sua conexão.';
                    }
                    
                    errorMessage += '</div>';
                    $(".allContent-section").html(errorMessage);
                }
            });
        }
        
        // Função para voltar ao dashboard
        function showDashboard() {
            $("#defaultContent").show();
            $(".allContent-section").empty();
        }
        
        // Tratamento de erro global para imagens
        $(document).ready(function() {
            $('img').on('error', function() {
                $(this).attr('src', '../assets/images/placeholder.png');
            });
        });
    </script>

</body>
</html>