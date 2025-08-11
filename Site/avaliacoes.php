<?php
session_start();
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/php_errors.log');
error_log('Iniciando avaliacoes.php');

include '../adminView/config/dbconnect.php';
if ($conn->connect_error) {
    error_log('Erro de conexão: ' . $conn->connect_error);
    die('Connection failed: ' . $conn->connect_error);
}

require_once '../adminView/controller/Produtos/ProductController.php';
require_once '../adminView/controller/Produtos/UserFavoritesController.php';
require_once '../adminView/controller/Produtos/UserCartController.php';

// Inicializar controladores
$productController = new ProductController($conn);
$userFavoritesController = new UserFavoritesController($conn);
$userCartController = new UserCartController($conn);

$isLoggedIn = isset($_SESSION['user_id']) && $_SESSION['logged_in'] === true;
if ($isLoggedIn) {
    $userName = $_SESSION['username'];
    if (strlen($userName) > 16) {
        $userName = substr($userName, 0, 16) . '...';
    }
    if (isset($_SESSION['user_picture']) && !empty($_SESSION['user_picture'])) {
        $userPicture = $_SESSION['user_picture'];
    } else {
        $userId = $_SESSION['user_id'];
        $query = 'SELECT profile_picture FROM usuarios WHERE id = ?';
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $userPicture = $row['profile_picture'] ?: '../Site/img/icons/perfil.png';
            } else {
                $userPicture = '../Site/img/icons/perfil.png';
            }
            $stmt->close();
        } else {
            error_log('Erro ao preparar query de perfil: ' . $conn->error);
            $userPicture = '../Site/img/icons/perfil.png';
        }
    }
} else {
    $userName = 'Entrar';
    $userPicture = '../Site/img/icons/perfil.png';
}

// Obter itens do carrinho e favoritos
$cartItems = $isLoggedIn ? $userCartController->getCartItems($_SESSION['user_id']) : [];
$favoritesItems = $isLoggedIn ? $userFavoritesController->getFavoriteItems($_SESSION['user_id']) : [];
$cartCount = $isLoggedIn ? array_sum(array_column($cartItems, 'quantity')) : 0;
$favoritesCount = count($favoritesItems);

$siteConfigPath = __DIR__ . '/../adminView/config_site.json';
if (!file_exists($siteConfigPath) || ($jsonContent = file_get_contents($siteConfigPath)) === false || ($configData = json_decode($jsonContent, true)) === null) {
    error_log('Erro ao carregar config_site.json');
    die('Erro ao carregar as configurações do site.');
}

function getConfigValue($config, $keys, $default = '')
{
    $value = $config;
    foreach ($keys as $key) {
        if (!isset($value[$key])) {
            return $default;
        }
        $value = $value[$key];
    }
    return is_string($value) ? htmlspecialchars($value) : $value;
}

$sobreMidia = getConfigValue($configData, ['pagina_inicial', 'sobre', 'midia']);
$whatsapp = getConfigValue($configData, ['contato', 'whatsapp']);
$instagram = getConfigValue($configData, ['contato', 'instagram'], '#');
$facebook = getConfigValue($configData, ['contato', 'facebook'], '#');
$email = getConfigValue($configData, ['contato', 'email'], '#');
$footerTexto = getConfigValue($configData, ['rodape', 'texto']);

$uploadPath = '../adminView/uploads/produtos/';
$produtos = $productController->getAllProducts();
if ($produtos === false) {
    error_log('Erro ao buscar produtos via ProductController');
}
foreach ($produtos as &$produto) {
    $produto['imagem_path'] = !empty($produto['imagem']) ? $uploadPath . $produto['imagem'] : $uploadPath . 'placeholder.jpeg';
    $produto['preco_final'] = !empty($produto['desconto']) && $produto['desconto'] > 0 ? $produto['preco'] * (1 - $produto['desconto'] / 100) : $produto['preco'];

    $stmt = $conn->prepare('SELECT AVG(avaliacao) as avg_rating, COUNT(*) as total_ratings FROM produto_avaliacoes WHERE post_id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $produto['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $produto['avg_rating'] = $row['avg_rating'] ? round($row['avg_rating'], 1) : 0;
        $produto['total_ratings'] = $row['total_ratings'];
        $stmt->close();
    } else {
        error_log('Erro ao preparar query de avaliações: ' . $conn->error);
        $produto['avg_rating'] = 0;
        $produto['total_ratings'] = 0;
    }
}
error_log('Produtos processados: ' . count($produtos));

// Limpa o número para o formato internacional do WhatsApp
$whatsapp_link = preg_replace('/[^0-9]/', '', $whatsapp);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../adminView/assets/images/logo.png" type="image/x-icon">
    <title>Cristais Gold Lar - Avaliações de Produtos</title>
    <link rel="stylesheet" href="../Site/css/blog/blog.css">
    <link rel="stylesheet" href="../Site/css/index/index.css">
    <link rel="stylesheet" href="../Site/css/global-alerts.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <script src="../Site/js/global-alerts.js"></script>
    <style>
        .alert {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px;
            border-radius: 5px;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1000;
        }
        .alert.success { background-color: #4CAF50; color: white; }
        .alert.error { background-color: #f44336; color: white; }
        .alert.show { opacity: 1; }
    </style>
</head>
<body>
    <div id="loading-screen">
        <div class="loader"></div>
    </div>

    <div class="notification-bar">
        <div class="message active" id="message1">Até 6x Sem Juros</div>
    </div>

    <header class="navbar">
        <nav>
            <div class="logo-container">
                <div class="logo">
                    <img src="../Site/img/logo.png" alt="Cristais Gold Lar Logo">
                </div>
                <div class="store-name">Cristais Gold Lar</div>
            </div>
            <div class="nav-links">
                <a href="../Site/index.php" class="nav-link">Início</a>
                <a href="../Site/avaliacoes.php" class="nav-link">Avaliações</a>
                <a href="#footer" class="nav-link scroll-to-footer">Contato</a>
            </div>
            <div class="nav-icons">
                <a href="../Site/meusItens.php" class="cart-icon">
                    <img src="../Site/img/icons/compras.png" alt="Carrinho" id="cart-icon">
                    <span class="counter cart-counter"><?php echo $cartCount; ?></span>
                </a>
                <a href="../Site/meusItens.php" class="cart-icon">
                    <img src="../Site/img/icons/salvar preto.png" alt="Favoritos" id="favorites-icon">
                    <span class="counter favorites-counter"><?php echo $favoritesCount; ?></span>
                </a>
                <div class="dropdown">
                    <a href="#" id="profile-btn">
                        <span class="profile-toggle">
                            <img src="<?php echo !empty($userPicture) && strpos($userPicture, 'http') !== 0 ? '../adminView/uploads/profile_pictures/' . htmlspecialchars($userPicture) : htmlspecialchars($userPicture); ?>" alt="Foto de Perfil" id="profile-pic" onerror="this.src='../Site/img/icons/perfil.png';">
                            <?php echo htmlspecialchars($userName); ?>
                            <img src="../Site/img/icons/seta.png" alt="Seta" class="arrow">
                        </span>
                    </a>
                    <div class="dropdown-menu">
                        <a href="../Site/includes/configuracoes/logout.php" class="logout-btn">Sair</a>
                        <a href="../Site/profile.php" class="config-btn">Configurações</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <header class="secondary-navbar">
        <nav>
            <div class="logo-container">
                <div class="logo">
                    <img src="../Site/img/logo.png" alt="Cristais Gold Lar Logo">
                </div>
                <div class="store-name">Cristais Gold Lar</div>
            </div>
            <button class="menu-toggle" aria-label="Abrir menu">
                <span class="hamburger"></span>
            </button>
        </nav>
    </header>

    <div class="side-menu" id="side-menu">
        <div class="side-menu-header">
            <button class="close-menu" aria-label="Fechar menu">✕</button>
        </div>
        <ul class="side-menu-items">
            <li class="side-menu-item"><a href="../Site/index.php">Início</a></li>
            <li class="side-menu-item"><a href="#footer" class="scroll-to-footer">Contato</a></li>
            <li class="side-menu-item"><a href="../Site/meusItens.php">Meus Itens</a></li>
            <li class="side-menu-item">
                <a href="<?php echo $isLoggedIn ? '../Site/profile.php' : '../Site/login_site.php'; ?>">
                    <?php echo $isLoggedIn ? 'Perfil' : 'Entrar'; ?>
                </a>
            </li>
            <li class="side-menu-item"><a href="../Site/includes/configuracoes/logout.php">Sair</a></li>
        </ul>
    </div>

    <div class="tabbar">
        <a href="../Site/meusItens.php">
            <img src="../Site/img/icons/salvar preto.png" alt="Favoritos">
            <span>Favoritos</span>
            <span class="counter favorites-counter"><?php echo $favoritesCount; ?></span>
        </a>
        <a href="<?php echo $isLoggedIn ? '../Site/profile.php' : '../Site/login_site.php'; ?>">
            <img src="<?php echo $isLoggedIn ? (!empty($userPicture) && strpos($userPicture, 'http') !== 0 ? '../adminView/uploads/profile_pictures/' . htmlspecialchars($userPicture) : htmlspecialchars($userPicture)) : '../Site/img/icons/perfil.png'; ?>" alt="Perfil" onerror="this.src='../Site/img/icons/perfil.png';">
            <span><?php echo $isLoggedIn ? 'Perfil' : 'Entrar'; ?></span>
        </a>
        <a href="../Site/meusItens.php">
            <img src="../Site/img/icons/compras.png" alt="Carrinho">
            <span>Carrinho</span>
            <span class="counter cart-counter"><?php echo $cartCount; ?></span>
        </a>
    </div>

    <div class="product-container">
        <div class="product-reviews">
            <h2>Avaliações de Produtos</h2>
            <div class="search-container">
                <div class="search-input-container">
                    <input type="text" id="searchInput" class="search-input" placeholder="Pesquisar produtos...">
                    <button class="search-btn">Buscar</button>
                </div>
            </div>
            <div class="product-grid">
                <div class="card-wrapper2">
                    <?php if (!empty($produtos)): ?>
                        <?php foreach ($produtos as $produto): ?>
                            <div class="blog-card" data-id="<?php echo htmlspecialchars($produto['id']); ?>">
                                <div class="image-placeholder">
                                    <img src="<?php echo htmlspecialchars($produto['imagem_path']); ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                                </div>
                                <div class="blog-info">
                                    <h3><?php echo htmlspecialchars($produto['nome']); ?></h3>
                                    <div class="rating-stars" data-product-id="<?php echo htmlspecialchars($produto['id']); ?>" data-rating="<?php echo htmlspecialchars($produto['avg_rating']); ?>">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fa-star <?php echo $i <= round($produto['avg_rating']) ? 'fas' : 'far'; ?>" data-rating="<?php echo $i; ?>"></i>
                                        <?php endfor; ?>
                                        <span class="rating-info">
                                            <span class="rating-average"><?php echo htmlspecialchars($produto['avg_rating']); ?></span>
                                            <span class="rating-count">(<?php echo htmlspecialchars($produto['total_ratings']); ?>)</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-products">Nenhum produto disponível para avaliação.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">×</span>
            <div class="modal-left">
                <img id="modalImage" src="" alt="Produto">
            </div>
            <div class="modal-right">
                <h3 id="modalTitle"></h3>
                <div class="modal-rating">
                    <h4>Média de Avaliação:</h4>
                    <div class="rating-stars" id="modalRating"></div>
                    <span id="modalReviewsCount"></span>
                </div>
                <div class="comments-section">
                    <h4>Comentários</h4>
                    <div id="modalComments" class="comments-list"></div>
                    <?php if ($isLoggedIn): ?>
                        <form class="comment-form">
                            <textarea id="commentText" placeholder="Escreva seu comentário..." required></textarea>
                            <button type="submit" class="comment-btn">Enviar Comentário</button>
                        </form>
                    <?php else: ?>
                        <p><a href="../Site/login_site.php">Faça login</a> para comentar.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <footer id="footer">
        <div id="content">
            <div id="contacts">
                <div class="logo2">
                    <img src="../Site/img/logo2.png" alt="Logo">
                </div>
                <p>Transformando vidro em arte para decorar seus momentos.</p>
                <a href="https://transparencyreport.google.com/safe-browsing/search?url=cristaisgoldlar.com.br" target="_blank" rel="noopener" class="google-safe-browsing-footer">
                    <img src="../Site/img/icons/google-safe-browsing.png" alt="Google Safe Browsing" class="google-safe-browsing-icon" />
                </a>
            </div>
            <ul class="list">
                <li><h3>Avaliação</h3></li>
                <li><a href="../Site/avaliacoes.php" class="link">Clique Aqui e Confira as Avaliações dos Nossos Produtos!</a></li>
            </ul>
            <ul class="list">
                <li><h3>Contatos</h3></li>
                <li><a href="<?= htmlspecialchars($instagram) ?>" class="link">Instagram</a></li>
                <li><a href="mailto:<?= htmlspecialchars($email) ?>" class="link">Email</a></li>
                <li><a href="https://wa.me/<?= $whatsapp_link ?>" class="link">WhatsApp</a></li>
            </ul>
            <ul class="list">
                <li><h3>Termos de Segurança</h3></li>
                <li><a href="../../politica-de-privacidade.php" class="link">Política de Privacidade</a></li>
                <li><a href="../../termos-de-servico.php" class="link">Termos de Serviço</a></li>
            </ul>
        </div>
        <div class="cnpj-section">
            <p>CNPJ: 37.804.018/0001-56</p>
        </div>
        <div class="payment-section">
            <h3>Formas de Pagamento</h3>
            <li class="payment-methods">
                <img src="../Site/img/pagamento/visa.png" alt="Visa" class="payment-icon">
                <img src="../Site/img/pagamento/master.png" alt="Mastercard" class="payment-icon">
                <img src="../Site/img/pagamento/amex.png" alt="American Express" class="payment-icon">
                <img src="../Site/img/pagamento/elo.png" alt="Paypal" class="payment-icon">
                <img src="../Site/img/pagamento/pix.png" alt="Pix" class="payment-icon">
                <img src="../Site/img/pagamento/bradesco.png" alt="Bradesco" class="payment-icon">
            </li>
        </div>
        <div id="copyright"></div>
    </footer>

    <!-- Botão Flutuante WhatsApp -->
    <a href="https://wa.me/<?= $whatsapp_link ?>" id="whatsapp-float-btn" title="Conversar no WhatsApp">
        <img src="../Site/img/icons/whatsapp-removebg-preview.png" alt="WhatsApp" style="width:30px;height:30px;object-fit:contain;display:flex;" />
    </a>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    let userRatings = {};

    const menuToggle = document.querySelector('.menu-toggle');
    const sideMenu = document.querySelector('.side-menu');
    const closeMenu = document.querySelector('.close-menu');
    
    if (menuToggle && sideMenu && closeMenu) {
        menuToggle.addEventListener('click', () => sideMenu.classList.add('open'));
        closeMenu.addEventListener('click', () => sideMenu.classList.remove('open'));
        document.addEventListener('click', (e) => {
            if (!sideMenu.contains(e.target) && !menuToggle.contains(e.target)) {
                sideMenu.classList.remove('open');
            }
        });
    }

    // Copyright
    const currentYear = new Date().getFullYear();
    document.getElementById('copyright').innerHTML = `Copyright © ${currentYear} Cristais Gold Lar. Todos os direitos reservados`;

    const searchInput = document.querySelector('#searchInput');
    const cards = document.querySelectorAll('.blog-card');
    
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            cards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                const shouldShow = title.includes(query);
                card.style.display = shouldShow ? 'block' : 'none';
                card.classList.toggle('fade-in', shouldShow);
                card.classList.toggle('fade-out', !shouldShow);
            });
        });
    }

    document.querySelectorAll('.rating-stars').forEach(container => {
        const stars = container.querySelectorAll('i');
        const productId = container.dataset.productId;
        let userRating = userRatings[productId] || parseFloat(container.dataset.rating) || 0;

        function updateStars(rating, permanent = false) {
            stars.forEach((star, index) => {
                star.classList.toggle('fas', index < Math.round(rating));
                star.classList.toggle('far', index >= Math.round(rating));
            });
            if (permanent) {
                userRating = rating;
                container.classList.add('rated');
                userRatings[productId] = rating;
            }
        }

        updateStars(userRating, true);

        stars.forEach((star, index) => {
            star.addEventListener('mouseover', () => {
                if (!container.classList.contains('rated')) {
                    updateStars(index + 1);
                }
            });

            star.addEventListener('mouseout', () => {
                if (!container.classList.contains('rated')) {
                    updateStars(userRating);
                }
            });

            star.addEventListener('click', () => {
                if (!productId || productId === '0' || isNaN(productId)) {
                    showAlert('ID do produto inválido.', 'error');
                    return;
                }
                
                const rating = index + 1;
                updateStars(rating, true);

                stars.forEach((s, i) => {
                    if (i < rating) {
                        s.classList.add('active');
                        setTimeout(() => s.classList.remove('active'), 300);
                    }
                });

                showAlert('Enviando avaliação...');

                fetch('../adminView/pages/Blog/processa_avaliacao.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ 
                        post_id: parseInt(productId), 
                        rating: rating 
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.message || `Erro na requisição: ${response.status}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showAlert('Avaliação salva com sucesso!', 'success');
                        userRatings[productId] = rating;
                        
                        const avgElement = container.querySelector('.rating-average');
                        const countElement = container.querySelector('.rating-count');
                        if (avgElement && countElement && data.avg_rating !== undefined) {
                            avgElement.textContent = data.avg_rating.toFixed(1);
                            countElement.textContent = `(${data.total_ratings})`;
                        }
                    } else {
                        showAlert(data.message || 'Erro ao salvar avaliação.', 'error');
                    }
                })
                .catch(error => {
                    showAlert(`Erro ao salvar avaliação: ${error.message}`, 'error');
                    updateStars(userRatings[productId] || 0, true);
                });
            });
        });
    });

    const productModal = document.getElementById('productModal');
    const closeModalBtn = productModal?.querySelector('.close-modal');
    const modalRating = document.getElementById('modalRating');
    const modalReviewsCount = document.getElementById('modalReviewsCount');
    const modalComments = document.getElementById('modalComments');
    const commentForm = document.querySelector('.comment-form');

    cards.forEach(card => {
        card.addEventListener('click', (e) => {
            if (!e.target.closest('.rating-stars') && productModal) {
                const productId = card.dataset.id;
                if (!productId || isNaN(productId)) {
                    showAlert('ID do produto inválido.', 'error');
                    return;
                }

                const modalImage = document.getElementById('modalImage');
                const modalTitle = document.getElementById('modalTitle');

                modalImage.src = card.querySelector('img').src;
                modalTitle.textContent = card.querySelector('h3').textContent;

                // Linha 478: Requisição GET para carregar avaliações e comentários
                fetch(`../adminView/pages/Blog/processa_avaliacao.php?product_id=${encodeURIComponent(productId)}`, {
                    method: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.message || `Erro na requisição: ${response.status}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const avgRating = data.avg_rating || 0;
                        const totalRatings = data.total_ratings || 0;
                        const comments = data.comments || [];
                        
                        modalRating.innerHTML = '';
                        for (let i = 1; i <= 5; i++) {
                            const star = document.createElement('i');
                            star.className = `fa-star ${i <= Math.round(avgRating) ? 'fas' : 'far'}`;
                            modalRating.appendChild(star);
                        }
                        modalReviewsCount.textContent = `(${totalRatings} avaliações)`;

                        modalComments.innerHTML = '';
                        if (comments.length > 0) {
                            comments.forEach(comment => {
                                const commentDiv = document.createElement('div');
                                commentDiv.className = 'comment-item';
                                commentDiv.innerHTML = `
                                    <div class="comment-header">
                                        <span class="comment-user">${comment.user_name || 'Anônimo'}</span>
                                        <span class="comment-date">${new Date(comment.created_at).toLocaleDateString('pt-BR')}</span>
                                    </div>
                                    <p class="comment-text">${comment.comment_text}</p>
                                `;
                                modalComments.appendChild(commentDiv);
                            });
                        } else {
                            modalComments.innerHTML = '<p>Sem comentários ainda. Seja o primeiro!</p>';
                        }
                    } else {
                        throw new Error(data.message || 'Erro ao carregar dados.');
                    }
                })
                // Linha 526: Tratamento de erro da requisição GET
                .catch(error => {
                    console.error('Erro ao carregar avaliações:', error);
                    modalRating.innerHTML = '<p>Erro ao carregar avaliações.</p>';
                    modalReviewsCount.textContent = '';
                    modalComments.innerHTML = `<p>Não foi possível carregar os comentários: ${error.message}</p>`;
                    showAlert(`Não foi possível carregar as avaliações: ${error.message}`, 'error');
                });

                productModal.style.display = 'block';
            }
        });
    });

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', () => productModal.style.display = 'none');
    }

    if (productModal) {
        window.addEventListener('click', (e) => {
            if (e.target === productModal) {
                productModal.style.display = 'none';
            }
        });
    }

    if (commentForm) {
        commentForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const productId = document.querySelector('.blog-card:hover')?.dataset.id || cards[0].dataset.id;
            const commentText = document.getElementById('commentText').value;

            if (!productId || isNaN(productId)) {
                showAlert('ID do produto inválido.', 'error');
                return;
            }

            if (!commentText.trim()) {
                showAlert('O comentário não pode estar vazio.', 'error');
                return;
            }

            fetch('../adminView/pages/Blog/processa_avaliacao.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    post_id: parseInt(productId),
                    comment: commentText
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || `Erro na requisição: ${response.status}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showAlert('Comentário enviado com sucesso!', 'success');
                    document.getElementById('commentText').value = '';
                    const commentDiv = document.createElement('div');
                    commentDiv.className = 'comment-item';
                    commentDiv.innerHTML = `
                        <div class="comment-header">
                            <span class="comment-user"><?php echo htmlspecialchars($userName); ?></span>
                            <span class="comment-date">${new Date().toLocaleDateString('pt-BR')}</span>
                        </div>
                        <p class="comment-text">${commentText}</p>
                    `;
                    modalComments.prepend(commentDiv);
                } else {
                    showAlert(data.message || 'Erro ao enviar comentário.', 'error');
                }
            })
            .catch(error => {
                showAlert(`Erro ao enviar comentário: ${error.message}`, 'error');
            });
        });
    }

    function showAlert(message, type = 'success') {
        const alert = document.createElement('div');
        alert.className = `alert ${type}`;
        alert.textContent = message;
        document.body.appendChild(alert);
        
        setTimeout(() => alert.classList.add('show'), 100);
        setTimeout(() => {
            alert.classList.remove('show');
            setTimeout(() => alert.remove(), 300);
        }, 3000);
    }

    window.handleCredentialResponse = function(response) {
        fetch('../Site/google_login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'credential=' + encodeURIComponent(response.credential)
        })
        .then(response => {
            if (response.ok) {
                return response.json();
            }
            throw new Error('Erro ao processar login do Google.');
        })
        .then(data => {
            document.getElementById('profile-pic').src = data.picture;
            window.location.href = "../Site/index.php";
        })
        .catch(error => {
            showAlert('Erro ao fazer login com Google.', 'error');
        });
    };
});

// Alterna as mensagens da notification-bar a cada 5 segundos
(function() {
    const messages = document.querySelectorAll('.notification-bar .message');
    let current = 0;
    setInterval(() => {
        messages.forEach((msg, idx) => msg.classList.toggle('active', idx === current));
        current = (current + 1) % messages.length;
    }, 5000);
})();
</script>

    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <div id="g_id_onload" data-client_id="818588658305-7hfcrmuocusbi88bpq0insq09srdv8jd.apps.googleusercontent.com" data-context="signin" data-ux_mode="popup" data-callback="handleCredentialResponse" data-auto_prompt="false"></div>
</body>
</html>
<?php $conn->close(); ?>