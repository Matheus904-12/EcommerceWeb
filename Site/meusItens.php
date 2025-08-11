<?php
session_start();
require_once '../adminView/config/dbconnect.php';
require_once '../adminView/controller/Produtos/ProductController.php';
require_once '../adminView/controller/Produtos/UserCartController.php';
require_once '../adminView/controller/Produtos/UserFavoritesController.php';

$productController = new ProductController($conn);
$userCartController = new UserCartController($conn);
$userFavoritesController = new UserFavoritesController($conn);

$isLoggedIn = isset($_SESSION['user_id']) && $_SESSION['logged_in'] === true;

if ($isLoggedIn) {
    $userName = $_SESSION['username'];
    if (strlen($userName) > 16) {
        $userName = substr($userName, 0, 16) . "...";
    }
    if (isset($_SESSION['user_picture']) && !empty($_SESSION['user_picture'])) {
        $userPicture = $_SESSION['user_picture'];
    } else {
        $userId = $_SESSION['user_id'];
        $query = "SELECT profile_picture FROM usuarios WHERE id = ?";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $row = $result->fetch_assoc()) {
                $userPicture = $row['profile_picture'] ?: '../Site/img/icons/perfil.png';
            } else {
                $userPicture = '../Site/img/icons/perfil.png';
            }
            $stmt->close();
        } else {
            $userPicture = '../Site/img/icons/perfil.png';
        }
    }
    $cartItems = $userCartController->getCartItems($_SESSION['user_id']);
    $favoritesItems = $userFavoritesController->getFavoriteItems($_SESSION['user_id']);
    // ENRIQUECER ITENS DO CARRINHO COM DADOS COMPLETOS DO PRODUTO
    foreach ($cartItems as &$item) {
        $produtoCompleto = $productController->getProductById($item['product_id']);
        if ($produtoCompleto) {
            $item['desconto'] = $produtoCompleto['desconto'] ?? 0;
            $item['preco_original'] = $produtoCompleto['preco'] ?? $item['preco'];
            $item['preco_final'] = (!empty($produtoCompleto['desconto']) && $produtoCompleto['desconto'] > 0)
                ? $produtoCompleto['preco'] * (1 - $produtoCompleto['desconto'] / 100)
                : $produtoCompleto['preco'];
            $item['preco_pix'] = round($item['preco_final'] * 0.95, 2); // 5% de desconto no Pix
        } else {
            $item['desconto'] = 0;
            $item['preco_original'] = $item['preco'];
            $item['preco_final'] = $item['preco'];
            $item['preco_pix'] = round($item['preco_final'] * 0.95, 2); // 5% de desconto no Pix
        }
    }
    unset($item);
    // ENRIQUECER ITENS FAVORITOS
    foreach ($favoritesItems as &$item) {
        $produtoCompleto = $productController->getProductById($item['id']);
        if ($produtoCompleto) {
            $item['desconto'] = $produtoCompleto['desconto'] ?? 0;
            $item['preco_original'] = $produtoCompleto['preco'] ?? $item['preco'];
            $item['preco_final'] = (!empty($produtoCompleto['desconto']) && $produtoCompleto['desconto'] > 0)
                ? $produtoCompleto['preco'] * (1 - $produtoCompleto['desconto'] / 100)
                : $produtoCompleto['preco'];
            $item['preco_pix'] = round($item['preco_final'] * 0.95, 2); // 5% de desconto no Pix
        } else {
            $item['desconto'] = 0;
            $item['preco_original'] = $item['preco'];
            $item['preco_final'] = $item['preco'];
            $item['preco_pix'] = round($item['preco_final'] * 0.95, 2); // 5% de desconto no Pix
        }
    }
    unset($item);
    $cartCount = array_sum(array_column($cartItems, 'quantity'));
    $favoriteCount = count($favoritesItems);
} else {
    $cartItems = [];
    $favoritesItems = [];
    $cartCount = 0;
    $favoriteCount = 0;
}

$siteConfigPath = __DIR__ . '/../adminView/config_site.json';
if (!file_exists($siteConfigPath)) {
    echo "Erro ao carregar as configurações do site.";
    return;
}
$jsonContent = file_get_contents($siteConfigPath);
if ($jsonContent === false) {
    echo "Erro ao carregar as configurações do site.";
    return;
}
$configData = json_decode($jsonContent, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "Erro ao carregar as configurações do site.";
    return;
}

function getConfigValue($config, $keys, $default = '')
{
    $value = $config;
    foreach ($keys as $key) {
        if (!isset($value[$key])) return $default;
        $value = $value[$key];
    }
    return is_string($value) ? htmlspecialchars($value) : $value;
}

$whatsapp = getConfigValue($configData, ['contato', 'whatsapp']);
$instagram = getConfigValue($configData, ['contato', 'instagram'], '#');
$email = getConfigValue($configData, ['contato', 'email'], '#');
$footerTexto = getConfigValue($configData, ['rodape', 'texto']);

// Limpa o número para o formato internacional do WhatsApp
$whatsapp_link = preg_replace('/[^0-9]/', '', $whatsapp);

// Função para obter o caminho da imagem do produto
function getProductImagePath($productId, $conn)
{
    $query = "SELECT imagem FROM produtos WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (!empty($row['imagem'])) {
            if (strpos($row['imagem'], '/') === false && strpos($row['imagem'], '\\') === false) {
                return '../adminView/uploads/produtos/' . $row['imagem'];
            } else {
                return $row['imagem'];
            }
        }
    }
    return '../adminView/uploads/produtos/placeholder.jpeg';
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Itens - Cristais Gold Lar</title>
    <link rel="shortcut icon" href="../adminView/assets/images/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="../Site/css/index/index.css">
    <link rel="stylesheet" href="../Site/css/meusItens/meus.css">
    <link rel="stylesheet" href="../Site/css/global-alerts.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <script src="../Site/js/global-alerts.js"></script>
</head>

<body>
    <div id="loading-screen">
        <div class="loader"></div>
    </div>

    <!-- Barra de Notificações -->
    <div class="notification-bar">
        <div class="message active" id="message1">Até 6x Sem Juros</div>
    </div>

    <!-- Navbar -->
    <header class="navbar">
        <nav>
            <div class="logo-container">
                <div class="logo">
                    <img src="../Site/img/logo.png" alt="Cristais Gold Lar Logo">
                </div>
                <div class="store-name">
                    Cristais Gold Lar
                </div>
            </div>
            <div class="nav-links">
                <a href="../Site/index.php" class="nav-link">Início</a>
                <a href="../Site/avaliacoes.php" class="nav-link">Avaliações</a>
                <a href="#footer" class="nav-link scroll-to-footer">Contato</a>
            </div>
            <div class="nav-icons">
                <a href="#" class="cart-icon">
                    <img src="../Site/img/icons/compras.png" alt="Carrinho" id="cart-icon">
                    <span class="counter cart-counter"><?php echo $cartCount; ?></span>
                </a>
                <a href="#" class="cart-icon">
                    <img src="../Site/img/icons/salvar preto.png" alt="Favoritos" id="favorites-icon">
                    <span class="counter favorites-counter"><?php echo $favoriteCount; ?></span>
                </a>
                <div class="dropdown">
                    <a href="#" id="profile-btn">
                        <span class="profile-toggle">
                            <img src="<?php echo $isLoggedIn ? (!empty($userPicture) && strpos($userPicture, 'http') !== 0 ? '../adminView/uploads/profile_pictures/' . htmlspecialchars($userPicture) : htmlspecialchars($userPicture)) : '../Site/img/icons/perfil.png'; ?>" alt="Foto de Perfil" id="profile-pic" onerror="this.src='../Site/img/icons/perfil.png';">
                            <?php echo $isLoggedIn ? htmlspecialchars($userName) : 'Cadastrar/Entrar'; ?>
                            <img src="../Site/img/icons/seta.png" alt="Seta" class="arrow">
                        </span>
                    </a>
                    <div class="dropdown-menu">
                        <?php if ($isLoggedIn) : ?>
                            <a href="../Site/includes/configuracoes/logout.php" class="logout-btn">Sair</a>
                            <a href="../Site/profile.php" class="config-btn">Configurações</a>
                        <?php else : ?>
                            <button class="google-login" onclick="location.href='../Site/google_login.php'">Entrar com Google</button>
                            <button class="google-login" onclick="location.href='../Site/login_site.php'">Cadastrar</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Navbar Secundária (para mobile e tablet) -->
    <header class="secondary-navbar">
        <nav>
            <div class="logo-container">
                <div class="logo">
                    <img src="../Site/img/logo.png" alt="Cristais Gold Lar Logo">
                </div>
                <div class="store-name">
                    Cristais Gold Lar
                </div>
            </div>
            <button class="menu-toggle" aria-label="Abrir menu">
                <span class="hamburger"></span>
            </button>
        </nav>
    </header>

    <!-- Menu Lateral -->
    <div class="side-menu" id="side-menu">
        <div class="side-menu-header">
            <button class="close-menu" aria-label="Fechar menu">✕</button>
        </div>
        <ul class="side-menu-items">
            <li class="side-menu-item"><a href="../Site/index.php">Início</a></li>
            <li class="side-menu-item"><a href="../Site/avaliacoes.php">Avaliações</a></li>
            <li class="side-menu-item"><a href="#footer" class="scroll-to-footer">Contato</a></li>
            <li class="side-menu-item">
                <a href="<?php echo $isLoggedIn ? '../Site/profile.php' : '../Site/login_site.php'; ?>">
                    <?php echo $isLoggedIn ? 'Perfil' : 'Entrar'; ?>
                </a>
            </li>
            <?php if ($isLoggedIn) : ?>
                <li class="side-menu-item"><a href="../Site/includes/configuracoes/logout.php">Sair</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Tabbar para dispositivos móveis -->
    <div class="tabbar">
        <a href="#">
            <img src="../Site/img/icons/salvar preto.png" alt="Favoritos">
            <span>Favoritos</span>
            <span class="counter favorites-counter"><?php echo $favoriteCount; ?></span>
        </a>
        <a href="<?php echo $isLoggedIn ? '../Site/profile.php' : '../Site/login_site.php'; ?>">
            <img src="<?php echo $isLoggedIn ? (!empty($userPicture) && strpos($userPicture, 'http') !== 0 ? '../adminView/uploads/profile_pictures/' . htmlspecialchars($userPicture) : htmlspecialchars($userPicture)) : '../Site/img/icons/perfil.png'; ?>" alt="Perfil" onerror="this.src='../Site/img/icons/perfil.png';">
            <span><?php echo $isLoggedIn ? 'Perfil' : 'Entrar'; ?></span>
        </a>
        <a href="#">
            <img src="../Site/img/icons/compras.png" alt="Carrinho">
            <span>Carrinho</span>
            <span class="counter cart-counter"><?php echo $cartCount; ?></span>
        </a>
    </div>

    <!-- Modal de Login -->
    <div class="login-modal-overlay" id="login-modal" style="display: <?php echo $isLoggedIn ? 'none' : 'flex'; ?>;">
        <div class="login-modal">
            <div class="login-modal-content">
                <h2>Faça Login para Continuar</h2>
                <p>Você precisa estar logado para visualizar ou gerenciar seus itens.</p>
                <div class="login-modal-actions">
                    <button class="btn-login-now" onclick="location.href='../Site/google_login.php'">Entrar com Google</button>
                    <button class="btn-login-now" onclick="location.href='../Site/login_site.php'">Cadastrar</button>
                    <button class="btn-cancel" onclick="location.href='../Site/index.php'">Voltar à Página Inicial</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Seção de Itens -->
    <section class="my-items-section">
        <div class="items-container" id="cart-section">
            <div class="items-header">
                <h2>Meu Carrinho (<span class="count-badge"><?php echo $cartCount; ?></span>)</h2>
                <?php if ($isLoggedIn && !empty($cartItems)) : ?>
                    <a href="../Site/checkout.php" class="checkout-btn">Finalizar Compra</a>
                <?php endif; ?>
            </div>
            <div class="items-grid" id="cart-items">
                <?php if ($isLoggedIn && !empty($cartItems)) : ?>
                    <?php foreach ($cartItems as $item) :
                        $imagePath = getProductImagePath($item['product_id'], $conn);
                        $parcelas = min(5, ceil($item['preco_final'] / 50));
                        $valor_parcela = $item['preco_final'] / $parcelas;
                    ?>
                        <div class="item-card cart-box" data-id="<?php echo htmlspecialchars($item['product_id']); ?>">
                            <div class="item-image">
                                <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($item['nome']); ?>" onerror="this.src='../adminView/uploads/produtos/placeholder.jpeg';">
                            </div>
                            <h3><?php echo htmlspecialchars($item['nome']); ?></h3>
                            <p class="item-price">
                                <?php if (!empty($item['desconto']) && $item['desconto'] > 0): ?>
                                    <span style="text-decoration:line-through;color:#888;font-size:0.95em;">R$<?php echo number_format($item['preco_original'], 2, ',', '.'); ?></span>
                                    <span>R$<?php echo number_format($item['preco_final'], 2, ',', '.'); ?></span>
                                <?php else: ?>
                                    R$<?php echo number_format($item['preco_final'], 2, ',', '.'); ?>
                                <?php endif; ?>
                            </p>
                            <span class="item-price-pix" style="color:#219653;font-weight:600;font-size:12px;">No Pix: R$<?php echo number_format($item['preco_pix'], 2, ',', '.'); ?> <span style="font-size:0.85em;font-weight:400;">(5% OFF)</span></span>
                            <p class="item-installment">ou <?php echo $parcelas; ?>x R$<?php echo number_format($valor_parcela, 2, ',', '.'); ?> sem juros</p>
                            <div class="item-quantity-control">
                                <button class="item-quantity-btn decrement" data-product-id="<?php echo htmlspecialchars($item['product_id']); ?>">
                                    <svg>
                                        <use xlink:href="#minus-icon"></use>
                                    </svg>
                                </button>
                                <input type="number" class="item-quantity-input" value="<?php echo htmlspecialchars($item['quantity']); ?>" min="1" data-product-id="<?php echo htmlspecialchars($item['product_id']); ?>">
                                <button class="item-quantity-btn increment" data-product-id="<?php echo htmlspecialchars($item['product_id']); ?>">
                                    <svg>
                                        <use xlink:href="#plus-icon"></use>
                                    </svg>
                                </button>
                            </div>
                            <div class="item-action-buttons">
                                <button class="item-action-btn item-remove-btn" data-product-id="<?php echo htmlspecialchars($item['product_id']); ?>">
                                    Remover
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="empty-message" id="cart-empty">
                        <p>Seu carrinho está vazio.</p>
                        <a href="../Site/index.php" class="btn-shop">Ver Produtos</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="items-container" id="favorites-section">
            <div class="items-header">
                <h2>Meus Favoritos (<span class="count-badge"><?php echo $favoriteCount; ?></span>)</h2>
                <?php if ($isLoggedIn && !empty($favoritesItems)) : ?>
                    <button class="item-move-all-btn" id="move-all-favorites">
                        <svg>
                            <use xlink:href="#cart-icon"></use>
                        </svg>
                        Mover Todos para o Carrinho
                    </button>
                <?php endif; ?>
            </div>
            <div class="items-grid" id="favorites-items">
                <?php if ($isLoggedIn && !empty($favoritesItems)) : ?>
                    <?php foreach ($favoritesItems as $item) :
                        $imagePath = getProductImagePath($item['id'], $conn);
                        $parcelas = min(5, ceil($item['preco_final'] / 50));
                        $valor_parcela = $item['preco_final'] / $parcelas;
                    ?>
                        <div class="item-card saved-box" data-id="<?php echo htmlspecialchars($item['id']); ?>">
                            <div class="item-image">
                                <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($item['nome']); ?>" onerror="this.src='../adminView/uploads/produtos/placeholder.jpeg';">
                            </div>
                            <h3><?php echo htmlspecialchars($item['nome']); ?></h3>
                            <p class="item-price">
                                <?php if (!empty($item['desconto']) && $item['desconto'] > 0): ?>
                                    <span style="text-decoration:line-through;color:#888;font-size:0.95em;">R$<?php echo number_format($item['preco_original'], 2, ',', '.'); ?></span>
                                    <span>R$<?php echo number_format($item['preco_final'], 2, ',', '.'); ?></span>
                                <?php else: ?>
                                    R$<?php echo number_format($item['preco_final'], 2, ',', '.'); ?>
                                <?php endif; ?>
                                <span class="item-price-pix" style="color:#219653;font-weight:600;font-size:12px;">No Pix: R$<?php echo number_format($item['preco_pix'], 2, ',', '.'); ?> <span style="font-size:0.85em;font-weight:400;">(5% OFF)</span></span>
                            </p>
                            <p class="item-installment">ou <?php echo $parcelas; ?>x R$<?php echo number_format($valor_parcela, 2, ',', '.'); ?> sem juros</p>
                            <div class="item-action-buttons">
                                <button class="item-action-btn item-move-btn" data-product-id="<?php echo htmlspecialchars($item['id']); ?>">
                                    Mover para Carrinho
                                </button>
                                <button class="item-action-btn item-remove-btn" data-product-id="<?php echo htmlspecialchars($item['id']); ?>">
                                    Remover
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="empty-message" id="favorites-empty">
                        <p>Você não tem itens favoritados.</p>
                        <a href="../Site/index.php" class="btn-shop">Ver Produtos</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- SVG Icons -->
    <svg style="display: none;">
        <symbol id="minus-icon" viewBox="0 0 24 24">
            <path d="M5 12h14" stroke-width="2" stroke-linecap="round" />
        </symbol>
        <symbol id="plus-icon" viewBox="0 0 24 24">
            <path d="M12 5v14m-7-7h14" stroke-width="2" stroke-linecap="round" />
        </symbol>
        <symbol id="cart-icon" viewBox="0 0 24 24">
            <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
        </symbol>
        <symbol id="trash-icon" viewBox="0 0 24 24">
            <path d="M3 6h18m-2 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
        </symbol>
        <symbol id="heart-broken-icon" viewBox="0 0 24 24">
            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
        </symbol>
    </svg>

    <!-- Rodapé -->
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

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <script src="../Site/js/meusItens/meusItens.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Dropdown
            const dropdowns = document.querySelectorAll('.custom-dropdown');
            dropdowns.forEach(dropdown => {
                const toggle = dropdown.querySelector('.dropdown-toggle');
                const optionsList = dropdown.querySelector('.dropdown-options');
                const options = dropdown.querySelectorAll('.dropdown-options li');

                toggle.addEventListener('click', () => {
                    optionsList.classList.toggle('show');
                    toggle.classList.toggle('active');
                });

                options.forEach(option => {
                    option.addEventListener('click', () => {
                        options.forEach(opt => opt.classList.remove('selected'));
                        option.classList.add('selected');
                        toggle.firstChild.textContent = option.textContent.trim() + " ";
                        optionsList.classList.remove('show');
                        toggle.classList.remove('active');
                        const category = option.getAttribute('data-value');
                        window.location.href = category ? `../Site/index.php?categoria=${category}` : '../Site/index.php';
                    });
                });

                document.addEventListener('click', (e) => {
                    if (!dropdown.contains(e.target)) {
                        optionsList.classList.remove('show');
                        toggle.classList.remove('active');
                    }
                });
            });

            // Menu Lateral e Modal de Categorias
            const menuToggle = document.querySelector('.menu-toggle');
            const sideMenu = document.querySelector('#side-menu');
            const closeMenu = document.querySelector('.close-menu');
            const categoryLink = document.querySelector('.category-link');
            const categoryToggle = document.querySelector('.category-toggle');
            const categoryModal = document.querySelector('#category-modal');
            const closeModal = document.querySelector('.close-modal');

            if (menuToggle && sideMenu && closeMenu) {
                menuToggle.addEventListener('click', () => sideMenu.classList.add('open'));
                closeMenu.addEventListener('click', () => sideMenu.classList.remove('open'));
                sideMenu.addEventListener('click', (e) => {
                    if (e.target === sideMenu) sideMenu.classList.remove('open');
                });
            }

            if (categoryLink && categoryToggle && categoryModal && closeModal) {
                categoryLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    categoryModal.classList.add('open');
                });
                categoryToggle.addEventListener('click', (e) => {
                    e.preventDefault();
                    categoryModal.classList.add('open');
                });
                closeModal.addEventListener('click', () => categoryModal.classList.remove('open'));
                categoryModal.addEventListener('click', (e) => {
                    if (e.target === categoryModal) categoryModal.classList.remove('open');
                });
                document.querySelectorAll('.modal-categories a').forEach(link => {
                    link.addEventListener('click', () => categoryModal.classList.remove('open'));
                });
            }

            // Rolagem suave para o footer
            document.querySelectorAll('.scroll-to-footer').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    document.querySelector('#footer').scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });

            // Carrossel lateral com maior fluidez
            const carousel = document.querySelector('#cart-items');
            const prevBtn = document.querySelector('.carousel-btn.prev');
            const nextBtn = document.querySelector('.carousel-btn.next');

            if (carousel && prevBtn && nextBtn) {
                const scrollAmount = 300; // Distância de rolagem por clique
                let isDragging = false;
                let startX;
                let scrollLeft;
                let velocity = 0;
                let lastX;
                let lastTime;
                let rafId;

                // Função para aplicar inércia
                const applyMomentum = () => {
                    if (Math.abs(velocity) < 0.1) {
                        cancelAnimationFrame(rafId);
                        return;
                    }
                    carousel.scrollLeft -= velocity;
                    velocity *= 0.95; // Decaimento da velocidade
                    rafId = requestAnimationFrame(applyMomentum);
                };

                // Clique nos botões
                prevBtn.addEventListener('click', () => {
                    carousel.scrollBy({
                        left: -scrollAmount,
                        behavior: 'smooth'
                    });
                });

                nextBtn.addEventListener('click', () => {
                    carousel.scrollBy({
                        left: scrollAmount,
                        behavior: 'smooth'
                    });
                });

                // Início do arrastar (mouse)
                carousel.addEventListener('mousedown', (e) => {
                    isDragging = true;
                    startX = e.pageX - carousel.offsetLeft;
                    scrollLeft = carousel.scrollLeft;
                    lastX = startX;
                    lastTime = Date.now();
                    velocity = 0;
                    cancelAnimationFrame(rafId);
                    carousel.style.cursor = 'grabbing';
                });

                // Fim do arrastar (mouse)
                carousel.addEventListener('mouseup', () => {
                    if (isDragging) {
                        isDragging = false;
                        carousel.style.cursor = 'grab';
                        rafId = requestAnimationFrame(applyMomentum);
                    }
                });

                carousel.addEventListener('mouseleave', () => {
                    if (isDragging) {
                        isDragging = false;
                        carousel.style.cursor = 'grab';
                        rafId = requestAnimationFrame(applyMomentum);
                    }
                });

                // Movimento do arrastar (mouse)
                carousel.addEventListener('mousemove', (e) => {
                    if (!isDragging) return;
                    e.preventDefault();
                    const x = e.pageX - carousel.offsetLeft;
                    const walk = (x - startX) * 1.5; // Sensibilidade reduzida
                    carousel.scrollLeft = scrollLeft - walk;

                    // Calcular velocidade
                    const currentTime = Date.now();
                    const deltaTime = (currentTime - lastTime) / 1000;
                    const deltaX = x - lastX;
                    if (deltaTime > 0) {
                        velocity = deltaX / deltaTime * 0.02; // Ajuste da velocidade
                    }
                    lastX = x;
                    lastTime = currentTime;
                });

                // Início do arrastar (toque)
                carousel.addEventListener('touchstart', (e) => {
                    isDragging = true;
                    startX = e.touches[0].pageX - carousel.offsetLeft;
                    scrollLeft = carousel.scrollLeft;
                    lastX = startX;
                    lastTime = Date.now();
                    velocity = 0;
                    cancelAnimationFrame(rafId);
                });

                // Fim do arrastar (toque)
                carousel.addEventListener('touchend', () => {
                    if (isDragging) {
                        isDragging = false;
                        rafId = requestAnimationFrame(applyMomentum);
                    }
                });

                // Movimento do arrastar (toque)
                carousel.addEventListener('touchmove', (e) => {
                    if (!isDragging) return;
                    e.preventDefault();
                    const x = e.touches[0].pageX - carousel.offsetLeft;
                    const walk = (x - startX) * 1.5;
                    carousel.scrollLeft = scrollLeft - walk;

                    // Calcular velocidade
                    const currentTime = Date.now();
                    const deltaTime = (currentTime - lastTime) / 1000;
                    const deltaX = x - lastX;
                    if (deltaTime > 0) {
                        velocity = deltaX / deltaTime * 0.02;
                    }
                    lastX = x;
                    lastTime = currentTime;
                });

                // Mostrar/esconder botões com base no scroll
                const updateButtons = () => {
                    prevBtn.style.display = carousel.scrollLeft > 0 ? 'block' : 'none';
                    nextBtn.style.display = carousel.scrollLeft < (carousel.scrollWidth - carousel.clientWidth - 1) ? 'block' : 'none';
                };

                carousel.addEventListener('scroll', updateButtons);
                window.addEventListener('resize', updateButtons);
                updateButtons();
            }

            // Copyright
            const currentYear = new Date().getFullYear();
            document.getElementById('copyright').innerHTML = `Copyright © ${currentYear} Cristais Gold Lar. Todos os direitos reservados`;

            // Alterna as mensagens da notification-bar a cada 5 segundos
            (function() {
                const messages = document.querySelectorAll('.notification-bar .message');
                let current = 0;
                setInterval(() => {
                    messages.forEach((msg, idx) => msg.classList.toggle('active', idx === current));
                    current = (current + 1) % messages.length;
                }, 5000);
            })();
        });
    </script>
</body>

</html>