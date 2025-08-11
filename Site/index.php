<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../adminView/config/dbconnect.php';
require_once '../adminView/controller/Produtos/ProductController.php';
require_once '../adminView/controller/Produtos/UserFavoritesController.php';
require_once '../adminView/controller/Produtos/UserCartController.php';
require_once '../adminView/controller/Configuracoes/BannerController.php';  // Adicionar BannerController

// Inicializar controladores
$productController = new ProductController($conn);
$userFavoritesController = new UserFavoritesController($conn);
$userCartController = new UserCartController($conn);
$bannerController = new BannerController($conn);  // Inicializar BannerController

// Verificar se o usuário está logado
$isLoggedIn = isset($_SESSION['user_id']) && $_SESSION['logged_in'] === true;

if ($isLoggedIn) {
    $userName = $_SESSION['username'] ?? '';
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
    // Obter favoritos do usuário para marcar produtos favoritados
    $favorites = $userFavoritesController->getFavoriteItems($_SESSION['user_id']);
    $favoriteProductIds = array_column($favorites, 'id');
} else {
    $userName = 'Cadastrar/Entrar';
    $userPicture = '../Site/img/icons/perfil.png';
    $favoriteProductIds = [];
}

// Buscar produtos
$produtos = [];
if (isset($_GET['categoria']) && !empty($_GET['categoria'])) {
    $produtos = $productController->getProductsByCategory($_GET['categoria']);
} elseif (isset($_GET['orderBy']) && !empty($_GET['orderBy'])) {
    $produtos = $productController->getProductsOrderedBy($_GET['orderBy']);
} elseif (isset($_GET['search']) && !empty($_GET['search'])) {
    $produtos = $productController->searchProducts($_GET['search']);
} else {
    $produtos = $productController->getAllProducts();
}

// Carregar configurações do site
$siteConfigPath = __DIR__ . '/../adminView/config_site.json';
if (!file_exists($siteConfigPath)) {
    echo 'Erro ao carregar as configurações do site.';
    exit;
}
$jsonContent = file_get_contents($siteConfigPath);
if ($jsonContent === false) {
    echo 'Erro ao carregar as configurações do site.';
    exit;
}
$configData = json_decode($jsonContent, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo 'Erro ao carregar as configurações do site.';
    exit;
}

// Função para obter valores de configuração
function getConfigValue($config, $keys, $default = '')
{
    $value = $config;
    foreach ($keys as $key) {
        if (!isset($value[$key]))
            return $default;
        $value = $value[$key];
    }
    return is_string($value) ? htmlspecialchars($value) : $value;
}

// Função para buscar produtos
function fetchProducts($productController, $conn)
{
    $categoria = isset($_GET['categoria']) ? htmlspecialchars($_GET['categoria']) : '';
    $orderBy = isset($_GET['orderBy']) ? htmlspecialchars($_GET['orderBy']) : '';
    $search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
    if (!empty($categoria))
        return $productController->getProductsByCategory($categoria);
    elseif (!empty($orderBy))
        return $productController->getProductsOrderedBy($orderBy);
    elseif (!empty($search))
        return $productController->searchProducts($search);
    return $productController->getAllProducts();
}

// Preparar dados do usuário e produtos
$userId = $isLoggedIn ? $_SESSION['user_id'] : 0;
$produtosRaw = fetchProducts($productController, $conn);

$produtos = [];
$uploadPath = '../adminView/uploads/produtos/';
foreach ($produtosRaw as $produto) {
    $produto['imagem_path'] = !empty($produto['imagem']) ? $uploadPath . $produto['imagem'] : $uploadPath . 'placeholder.jpeg';
    $produto['preco_final'] = !empty($produto['desconto']) && $produto['desconto'] > 0 ? $produto['preco'] * (1 - $produto['desconto'] / 100) : $produto['preco'];
    $produto['preco_pix'] = round($produto['preco_final'] * 0.95, 2); // 5% de desconto no Pix
    // Buscar média e total de avaliações
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
        $produto['avg_rating'] = 0;
        $produto['total_ratings'] = 0;
    }
    $produtos[] = $produto;
}

// Obter produtos em destaque e promoções
$featuredProducts = $productController->getFeaturedProducts(6);
$promoProducts = $productController->getPromoProducts(3);
foreach ($featuredProducts as &$produto) {
    $produto['imagem_path'] = !empty($produto['imagem']) ? $uploadPath . $produto['imagem'] : $uploadPath . 'placeholder.jpeg';
    $produto['preco_final'] = !empty($produto['desconto']) && $produto['desconto'] > 0 ? $produto['preco'] * (1 - $produto['desconto'] / 100) : $produto['preco'];
    $produto['preco_pix'] = round($produto['preco_final'] * 0.95, 2); // 5% de desconto no Pix
    // Buscar média e total de avaliações
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
        $produto['avg_rating'] = 0;
        $produto['total_ratings'] = 0;
    }
}
foreach ($promoProducts as &$produto) {
    $produto['imagem_path'] = !empty($produto['imagem']) ? $uploadPath . $produto['imagem'] : $uploadPath . 'placeholder.jpeg';
    $produto['preco_final'] = !empty($produto['desconto']) && $produto['desconto'] > 0 ? $produto['preco'] * (1 - $produto['desconto'] / 100) : $produto['preco'];
    $produto['preco_pix'] = round($produto['preco_final'] * 0.95, 2); // 5% de desconto no Pix
    // Buscar média e total de avaliações
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
        $produto['avg_rating'] = 0;
        $produto['total_ratings'] = 0;
    }
}

// Obter itens do carrinho e favoritos
$cartItems = $isLoggedIn ? $userCartController->getCartItems($userId) : [];
$favoritesItems = $isLoggedIn ? $userFavoritesController->getFavoriteItems($userId) : [];
$cartCount = $isLoggedIn ? array_sum(array_column($cartItems, 'quantity')) : 0;
$favoritesCount = count($favoritesItems);

// Configurações do site
$sobreMidia = getConfigValue($configData, ['pagina_inicial', 'sobre', 'midia']);
$whatsapp = getConfigValue($configData, ['contato', 'whatsapp']);
$instagram = getConfigValue($configData, ['contato', 'instagram'], '#');
$facebook = getConfigValue($configData, ['contato', 'facebook'], '#');
$email = getConfigValue($configData, ['contato', 'email'], '#');
$footerTexto = getConfigValue($configData, ['rodape', 'texto']);

// Limpa o número para o formato internacional do WhatsApp
$whatsapp_link = preg_replace('/[^0-9]/', '', $whatsapp);

// Verificar se deve exibir modal de boas-vindas
$showWelcomeModal = false;
if (isset($_SESSION['show_welcome_modal']) && $_SESSION['show_welcome_modal']) {
    $showWelcomeModal = true;
    unset($_SESSION['show_welcome_modal']);
}

// Verificar se usuário tem direito ao desconto de primeira compra
$temDescontoPrimeiraCompra = false;
if ($isLoggedIn) {
    $query = 'SELECT primeira_compra FROM usuarios WHERE id = ?';
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $temDescontoPrimeiraCompra = $row['primeira_compra'] == 1;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../adminView/assets/images/logo.png" type="image/x-icon">
    <title>Cristais Gold Lar - Inicio</title>
    <link rel="stylesheet" href="../Site/css/index/index.css">
    <link rel="stylesheet" href="../Site/css/global-alerts.css">
    <link rel="stylesheet" href="../Site/css/swiper-bundle.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
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
                    <picture>
                        <source srcset="../Site/img/logo.webp" type="image/webp">
                        <img src="../Site/img/logo.png" width="200" height="60" alt="Logo Cristais Gold Lar">
                    </picture>
                </div>
                <div class="store-name">
                    Cristais Gold Lar
                </div>
            </div>
            <div class="category-search-container">
                <div class="category-dropdown">
                    <div class="custom-dropdown">
                        <button class="dropdown-toggle">
                            Todas as Categorias
                            <span class="dropdown-arrow"></span>
                        </button>
                        <ul class="dropdown-options">
                            <li data-value="" class="selected">Todas as Categorias</li>
                            <?php
                            $categorias = ['Arranjos', 'Vasos de Vidro', 'Muranos', 'Muranos Color', 'Vaso Cerâmica'];
                            foreach ($categorias as $categoria):
                                ?>
                                <li data-value="<?= htmlspecialchars(strtolower($categoria)) ?>">
                                    <?= htmlspecialchars($categoria) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <div class="search-bar">
                    <input type="text" placeholder="Pesquise Aqui" id="search-input">
                    <span class="clear-search" style="display: none;">✕</span>
                </div>
            </div>
            <div class="nav-icons">
                <a href="../Site/meusItens.php" class="cart-icon">
                    <img src="../Site/img/icons/compras.png" alt="Carrinho" id="cart-icon">
                    <span class="counter cart-counter"><?= $isLoggedIn ? $cartCount : 0 ?></span>
                </a>
                <a href="../Site/meusItens.php" class="cart-icon">
                    <img src="../Site/img/icons/salvar preto.png" alt="Favoritos" id="favorites-icon">
                    <span class="counter favorites-counter"><?= $isLoggedIn ? $favoritesCount : 0 ?></span>
                </a>
                <div class="dropdown">
                    <a href="#" id="profile-btn">
                        <span class="profile-toggle">
                            <img src="<?= $isLoggedIn ? (!empty($userPicture) && strpos($userPicture, 'http') !== 0 ? '../adminView/uploads/profile_pictures/' . htmlspecialchars($userPicture) : htmlspecialchars($userPicture)) : '../Site/img/icons/perfil.png' ?>" alt="Foto de Perfil" id="profile-pic" onerror="this.src='../Site/img/icons/perfil.png';">
                            <?= $isLoggedIn ? htmlspecialchars($userName) : 'Cadastrar/Entrar' ?>
                            <img src="../Site/img/icons/seta.png" alt="Seta" class="arrow">
                        </span>
                    </a>
                    <div class="dropdown-menu">
                        <?php if ($isLoggedIn): ?>
                            <a href="../Site/includes/configuracoes/logout.php" class="logout-btn">Sair</a>
                            <a href="../Site/profile.php" class="config-btn">Configurações</a>
                        <?php else: ?>
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
                    <picture>
                        <source srcset="../Site/img/logo.webp" type="image/webp">
                        <img src="../Site/img/logo.png" width="200" height="60" alt="Logo Cristais Gold Lar">
                    </picture>
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
            <li class="side-menu-item"><a href="../Site/meusItens.php">Meus Itens</a></li>
            <li class="side-menu-item"><a href="../Site/avaliacoes.php">Avaliações</a></li>
            <li class="side-menu-item"><a href="#footer" class="scroll-to-footer">Contato</a></li>
            <li class="side-menu-item">
                <a href="<?= $isLoggedIn ? '../Site/profile.php' : '../Site/login_site.php' ?>">
                    <?= $isLoggedIn ? 'Perfil' : 'Entrar' ?>
                </a>
            </li>
            <li class="side-menu-item"><a href="../Site/includes/configuracoes/logout.php">Sair</a></li>
        </ul>
    </div>


    
    <!-- Tabbar para dispositivos móveis -->
    <div class="tabbar">
        <a href="../Site/meusItens.php">
            <img src="../Site/img/icons/salvar preto.png" alt="Favoritos">
            <span>Favoritos</span>
            <span class="counter favorites-counter"><?= $isLoggedIn ? $favoritesCount : 0 ?></span>
        </a>
        <a href="../Site/meusItens.php">
            <img src="../Site/img/icons/compras.png" alt="Carrinho">
            <span>Carrinho</span>
            <span class="counter cart-counter"><?= $isLoggedIn ? $cartCount : 0 ?></span>
        </a>
        <a href="<?= $isLoggedIn ? '../Site/profile.php' : '../Site/login_site.php' ?>">
            <img src="<?= $isLoggedIn ? (!empty($userPicture) && strpos($userPicture, 'http') !== 0 ? '../adminView/uploads/profile_pictures/' . htmlspecialchars($userPicture) : htmlspecialchars($userPicture)) : '../Site/img/icons/perfil.png' ?>" alt="Perfil" onerror="this.src='../Site/img/icons/perfil.png';">
            <span><?= $isLoggedIn ? 'Perfil' : 'Entrar' ?></span>
        </a>
        <a href="#search" class="search-toggle">
            <img src="../Site/img/icons/Pesquisar.png" alt="Pesquisar">
            <span>Pesquisar</span>
        </a>
        <a href="../Site/avaliacoes.php">
            <img src="../Site/img/icons/estrela.png" alt="Estrelas">
            <span>Avaliações</span>
        </a>
    </div>
    <div id="category-bubbles" class="category-bubbles" style="display:none;"></div>

    <!-- Barra de pesquisa expansível para mobile -->
    <div class="search-bar-mobile">
        <div class="search-bar-mobile-container">
            <input type="text" placeholder="Pesquise Aqui" id="search-input-mobile">
            <button class="search-icon-btn" aria-label="Pesquisar">
                <svg viewBox="0 0 16 16" fill="#999" width="20" height="20">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
                </svg>
            </button>
            <span class="clear-search" id="close-search-mobile" aria-label="Fechar pesquisa">✕</span>
        </div>
    </div>

    <!-- Carrossel de Imagens -->
    <section class="carousel-section">
        <div class="swiper carousel-container">
            <div class="swiper-wrapper">
                <?php
                $carouselImages = $bannerController->getBanners();
                if (!empty($carouselImages)) {
                    foreach ($carouselImages as $image):
                        ?>
                        <div class="swiper-slide">
                            <div class="carousel-slide-content">
                                <?php if (!empty($image['link'])): ?>
                                    <a href="<?php echo htmlspecialchars($image['link']); ?>">
                                    <?php endif; ?>
                                    <img src="../adminView/uploads/carousel/<?php echo htmlspecialchars($image['imagem']); ?>"
                                        alt="<?php echo htmlspecialchars($image['titulo'] ?? 'Banner'); ?>"
                                        loading="lazy">
                                    <?php if (!empty($image['titulo']) || !empty($image['descricao'])): ?>
                                        <div class="carousel-caption">
                                            <?php if (!empty($image['titulo'])): ?>
                                                <h3><?php echo htmlspecialchars($image['titulo']); ?></h3>
                                            <?php endif; ?>
                                            <?php if (!empty($image['descricao'])): ?>
                                                <p><?php echo htmlspecialchars($image['descricao']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($image['link'])): ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach;
                } else {
                    for ($i = 1; $i <= 4; $i++): ?>
                        <div class="swiper-slide">
                            <div class="carousel-slide-content">
                                <img src="../adminView/uploads/carousel/carrossel-placeholder-<?php echo $i; ?>.jpg"
                                    alt="Carrossel Imagem <?php echo $i; ?>"
                                    loading="lazy">
                            </div>
                        </div>
                <?php endfor;
                } ?>
            </div>
        </div>
    </section>

    <!-- Sobre Nós -->
    <section class="sobre-nos" id="sobre">
        <div class="texto">
            <h2>Sobre Nós</h2>
            <p>A Cristais Gold Lar foi fundada em 2020 por um casal resiliente, movido por sonhos e pela vontade de transformar ambientes através da beleza e elegância do vidro decorativo.

                Desde o início, nosso compromisso sempre foi oferecer produtos de qualidade superior, com design refinado e acabamento impecável. Atendemos lojistas de todo o Brasil e, mais recentemente, expandimos nossos serviços para o setor de varejista de arranjos, levando sofisticação e versatilidade a cada composição.

                Nossos valores estão enraizados na qualidade, confiança, inovação, atendimento humanizado e no bom relacionamento com nossos clientes. Acreditamos que cada peça deve refletir cuidado e excelência, contribuindo para criar espaços que encantam e inspiram.

                A Cristais Gold Lar é mais do que uma fornecedora de produtos – é um parceiro de confiança para quem valoriza bom gosto, durabilidade e atendimento diferenciado.

                Seja bem-vindo(a). Estamos prontos para atender com dedicação, seriedade e elegância em cada detalhe.</p>
        </div>
        <div class="imagem">
            <?php
            if (!empty($sobreMidia)) {
                // Se for um link externo (YouTube ou Vimeo)
                if (strpos($sobreMidia, 'youtube.com') !== false) {
                    preg_match('/v=([^&]+)/', $sobreMidia, $matches);
                    $videoId = $matches[1] ?? '';
                    echo "<iframe width='100%' height='117%' border-radius='12px' src='https://www.youtube.com/embed/$videoId' frameborder='0' allowfullscreen></iframe>";
                } elseif (strpos($sobreMidia, 'vimeo.com') !== false) {
                    preg_match('/vimeo.com\/(\d+)/', $sobreMidia, $matches);
                    $videoId = $matches[1] ?? '';
                    echo "<iframe src='https://player.vimeo.com/video/$videoId' width='100%' height='100%' frameborder='0' allowfullscreen></iframe>";
                } else {
                    // Arquivo de mídia local
                    $caminhoMidia = '../adminView/uploads/inicio/' . basename($sobreMidia);
                    if (preg_match('/\.(mp4|webm|ogg)$/i', $sobreMidia)) {
                        echo "<video controls style='width: 100%; height: 100%; object-fit: cover; border-radius: 12px;'>
                    <source src='" . htmlspecialchars($caminhoMidia) . "' type='video/mp4'>
                    Seu navegador não suporta vídeos HTML5.
                </video>";
                    } else {
                        echo "<img src='" . htmlspecialchars($caminhoMidia) . "' alt='Sobre Nós' style='width: 100%; height: 100%; object-fit: cover; border-radius: 12px;'>";
                    }
                }
            }
            ?>
        </div>
    </section>

    <!-- Seção de Categorias do Estoque -->
    <section class="category-section">
        <div class="category-grid">
            <div class="category-item">
                <div class="image-placeholder">
                    <img src="../Site/img/categories/arranjos.jpg" alt="Arranjos" onerror="this.style.display='none'; this.parentElement.style.background='#808080';">
                </div>
                <h3>Arranjos</h3>
                <p class="subtitle">Pequenos, Médios e Grandes</p>
            </div>
            <div class="category-item">
                <div class="image-placeholder">
                    <img src="../Site/img/categories/vasos-vidro.jpg" alt="Vasos de Vidro" onerror="this.style.display='none'; this.parentElement.style.background='#808080';">
                </div>
                <h3>Vasos de Vidro</h3>
            </div>
            <div class="category-item">
                <div class="image-placeholder">
                    <img src="../Site/img/categories/muranos.jpg" alt="Muranos" onerror="this.style.display='none'; this.parentElement.style.background='#808080';">
                </div>
                <h3>Muranos</h3>
            </div>
            <div class="category-item">
                <div class="image-placeholder">
                    <img src="../Site/img/categories/muranos-color.jpg" alt="Muranos Color" onerror="this.style.display='none'; this.parentElement.style.background='#808080';">
                </div>
                <h3>Muranos Color</h3>
            </div>
            <div class="category-item">
                <div class="image-placeholder">
                    <img src="../Site/img/categories/vaso-ceramica.jpg" alt="Vaso Cerâmica" onerror="this.style.display='none'; this.parentElement.style.background='#808080';">
                </div>
                <h3>Vaso Cerâmica</h3>
            </div>
        </div>
    </section>

    <!-- Seção de Produtos em Destaque -->
    <section class="product-section">
        <div class="product-container">
            <div class="product-grid">
                <div class="card-wrapper">
                    <?php
                    if (is_array($featuredProducts) && !empty($featuredProducts)) {
                        foreach ($featuredProducts as $produto) {
                            $avaliacao = rand(3, 5);
                            $parcelas = min(5, ceil($produto['preco_final'] / 50));
                            $valor_parcela = $produto['preco_final'] / $parcelas;
                            $modalId = 'modal-' . $produto['id'];
                            $isFavorited = $isLoggedIn && in_array($produto['id'], $favoriteProductIds);
                            ?>
                            <div class="product-card" data-modal-id="<?php echo $modalId; ?>">
                                <div class="image-placeholder">
                                    <img src="<?php echo htmlspecialchars($produto['imagem_path']); ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>" onerror="this.style.display='none'; this.parentElement.style.background='#808080';">
                                    <?php if ($produto['desconto'] > 0): ?>
                                        <span class="discount-tag"><?php echo number_format($produto['desconto'], 0); ?>% OFF</span>
                                    <?php endif; ?>
                                    <?php if ($produto['promocao'] == 1): ?>
                                        <span class="discount-tag" style="background:#f3ba00;color:#fff;">Promoção</span>
                                    <?php endif; ?>
                                    <div class="favorite-icon <?php echo $isFavorited ? 'active' : ''; ?>" data-product-id="<?php echo $produto['id']; ?>">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="<?php echo $isFavorited ? '#FF5252' : 'none'; ?>" stroke="<?php echo $isFavorited ? '#FF5252' : '#000000'; ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <h3><?php echo htmlspecialchars($produto['nome']); ?></h3>
                                <div class="price-container">
                                    <?php if ($produto['desconto'] > 0): ?>
                                        <p class="price-old">R$<?php echo number_format($produto['preco'], 2, ',', '.'); ?></p>
                                        <p class="price-current">R$<?php echo number_format($produto['preco_final'], 2, ',', '.'); ?></p>
                                    <?php else: ?>
                                        <p class="price-current">R$<?php echo number_format($produto['preco_final'], 2, ',', '.'); ?></p>
                                    <?php endif; ?>
                                    <p class="price-pix" style="color:#219653;font-weight:600;font-size:12px;">No Pix: R$<?php echo number_format($produto['preco_pix'], 2, ',', '.'); ?> <span style="font-size:0.85em;font-weight:400;">(5% OFF)</span></p>
                                </div>
                                <p class="installment">ou <?php echo $parcelas; ?>x R$<?php echo number_format($valor_parcela, 2, ',', '.'); ?> sem juros</p>
                                <button class="add-to-cart-btn" data-product-id="<?php echo $produto['id']; ?>">Adicionar ao Carrinho</button>
                            </div>
                    <?php
                        }
                    } else {
                        echo "<p class='text-center text-red-500'>Nenhum produto em destaque encontrado.</p>";
                    }
                    ?>
                </div>
            </div>
            <div class="promo-highlight">
                <h2>Promoções e Novidades</h2>
                <div class="promo-content">
                    <?php
                    if (is_array($promoProducts) && !empty($promoProducts)) {
                        // Limitar a exibição ao primeiro produto
                        $promoProduto = array_slice($promoProducts, 0, 1)[0];
                        $isFavorited = $isLoggedIn && in_array($promoProduto['id'], $favoriteProductIds);
                        $parcelas = min(5, ceil($promoProduto['preco_final'] / 50));
                        $valor_parcela = $promoProduto['preco_final'] / $parcelas;
                        $modalId = 'modal-' . $promoProduto['id'];
                        ?>
                        <div class="promo-item open-modal" data-modal-id="<?php echo $modalId; ?>">
                            <div class="image-placeholder">
                                <img src="<?php echo htmlspecialchars($promoProduto['imagem_path']); ?>" alt="<?php echo htmlspecialchars($promoProduto['nome']); ?>" onerror="this.style.display='none'; this.parentElement.style.background='#808080';">
                                <?php if ($promoProduto['desconto'] > 0): ?>
                                    <span class="discount-tag"><?php echo number_format($promoProduto['desconto'], 0); ?>% OFF</span>
                                <?php elseif ($promoProduto['lancamento']): ?>
                                    <span class="discount-tag">Lançamento</span>
                                <?php elseif ($promoProduto['em_alta']): ?>
                                    <span class="discount-tag">Em Alta</span>
                                <?php endif; ?>
                                <?php if ($promoProduto['promocao'] == 1): ?>
                                    <span class="discount-tag" style="background:#f3ba00;color:#fff;">Promoção</span>
                                <?php endif; ?>
                            </div>
                            <h3><?php echo htmlspecialchars($promoProduto['nome']); ?></h3>
                            <div class="price-container">
                                <?php if ($promoProduto['desconto'] > 0): ?>
                                    <p class="price-old">R$<?php echo number_format($promoProduto['preco'], 2, ',', '.'); ?></p>
                                    <p class="price-current price-animated">R$<?php echo number_format($promoProduto['preco_final'], 2, ',', '.'); ?></p>
                                <?php else: ?>
                                    <p class="price-current price-animated">R$<?php echo number_format($promoProduto['preco_final'], 2, ',', '.'); ?></p>
                                <?php endif; ?>
                            </div>
                            <p class="installment">ou <?php echo $parcelas; ?>x R$<?php echo number_format($valor_parcela, 2, ',', '.'); ?> sem juros</p>
                        </div>
                    <?php
                    } else {
                        echo "<p class='text-center text-red-500'>Nenhuma promoção ou novidade disponível.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Seção de Todos os Produtos -->
    <section class="all-products-section" id="all-products-section">
        <div class="all-products-btn">
            <p>Todos os Produtos</p>
        </div>
        <div class="all-products-grid">
            <?php
            if (is_array($produtos) && !empty($produtos)) {
                foreach ($produtos as $produto) {
                    $avaliacao = rand(3, 5);
                    $parcelas = min(5, ceil($produto['preco_final'] / 50));
                    $valor_parcela = $produto['preco_final'] / $parcelas;
                    $modalId = 'modal-' . $produto['id'];
                    $isFavorited = $isLoggedIn && in_array($produto['id'], $favoriteProductIds);
                    ?>
                    <div class="product-card" data-modal-id="<?php echo $modalId; ?>" data-name="<?php echo htmlspecialchars(strtolower($produto['nome'])); ?>">
                        <div class="image-placeholder">
                            <img src="<?php echo htmlspecialchars($produto['imagem_path']); ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>" onerror="this.style.display='none'; this.parentElement.style.background='#808080';">
                            <?php if ($produto['desconto'] > 0): ?>
                                <span class="discount-tag"><?php echo number_format($produto['desconto'], 0); ?>% OFF</span>
                            <?php endif; ?>
                            <?php if ($produto['promocao'] == 1): ?>
                                <span class="discount-tag" style="background:#f3ba00;color:#fff;">Promoção</span>
                            <?php endif; ?>
                            <div class="favorite-icon <?php echo $isFavorited ? 'active' : ''; ?>" data-product-id="<?php echo $produto['id']; ?>">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="<?php echo $isFavorited ? '#FF5252' : 'none'; ?>" stroke="<?php echo $isFavorited ? '#FF5252' : '#000000'; ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                            </div>
                        </div>
                        <h3><?php echo htmlspecialchars($produto['nome']); ?></h3>
                        <div class="price-container">
                            <?php if ($produto['desconto'] > 0): ?>
                                <p class="price-old">R$<?php echo number_format($produto['preco'], 2, ',', '.'); ?></p>
                                <p class="price-current">R$<?php echo number_format($produto['preco_final'], 2, ',', '.'); ?></p>
                            <?php else: ?>
                                <p class="price-current">R$<?php echo number_format($produto['preco_final'], 2, ',', '.'); ?></p>
                            <?php endif; ?>
                            <p class="price-pix" style="color:#219653;font-weight:600;font-size:0.98em;">No Pix: R$<?php echo number_format($produto['preco_pix'], 2, ',', '.'); ?> <span style="font-size:0.85em;font-weight:400;">(5% OFF)</span></p>
                        </div>
                        <p class="installment">ou <?php echo $parcelas; ?>x R$<?php echo number_format($valor_parcela, 2, ',', '.'); ?> sem juros</p>
                        <button class="add-to-cart-btn" data-product-id="<?php echo $produto['id']; ?>">Adicionar ao Carrinho</button>
                    </div>
            <?php
                }
            } else {
                echo "<p class='text-center text-red-500'>Nenhum produto encontrado.</p>";
            }
            ?>
        </div>
    </section>

    <!-- Modais -->
    <?php
    if (is_array($produtos) && !empty($produtos)) {
        foreach ($produtos as $produto) {
            $modalId = 'modal-' . $produto['id'];
            $parcelas_max = min(12, ceil($produto['preco'] / 50));
            $valor_parcela_max = $produto['preco'] / $parcelas_max;
            $random_reviews = rand(8, 47);
            $random_stock = rand(3, 20);
            $product_code = 'PROD' . str_pad($produto['id'], 5, '0', STR_PAD_LEFT);
            $isFavorited = $isLoggedIn && in_array($produto['id'], $favoriteProductIds);
            ?>
            <div id="<?php echo $modalId; ?>" class="modal">
                <div class="modal-content">
                    <div class="modal-close">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </div>
                    <div class="modal-container">
                        <div class="modal-image-container">
                            <div class="modal-image-wrapper">
                                <div class="modal-favorite <?php echo $isFavorited ? 'active' : ''; ?>" data-product-id="<?php echo $produto['id']; ?>">
                                    <svg viewBox="0 0 24 24" fill="<?php echo $isFavorited ? '#FF5252' : 'none'; ?>" stroke="<?php echo $isFavorited ? '#FF5252' : 'currentColor'; ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                    </svg>
                                </div>
                                <div class="modal-image">
                                    <img src="<?php echo htmlspecialchars($produto['imagem_path'] ?? ''); ?>" alt="<?php echo htmlspecialchars($produto['nome'] ?? ''); ?>" onerror="this.onerror=null; this.src='../adminView/uploads/produtos/placeholder.jpeg'; this.parentElement.classList.add('image-fallback');">
                                </div>
                            </div>
                        </div>
                        <div class="modal-details">
                            <div class="modal-subtitle">Detalhes do Produto</div>
                            <h2 class="modal-title"><?php echo htmlspecialchars($produto['nome'] ?? ''); ?></h2>
                            <div class="modal-rating">
                                <div class="stars">
                                    <?php
                                    $avg = $produto['avg_rating'] ?? 0;
                                    $total = $produto['total_ratings'] ?? 0;
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($total == 0) {
                                            // Sem avaliações: todas vazias
                                            echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>';
                                        } else {
                                            $filled = $i <= round($avg);
                                            echo '<svg viewBox="0 0 24 24" fill="' . ($filled ? 'currentColor' : 'none') . '" stroke="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>';
                                        }
                                    }
                                    ?>
                                </div>
                                <span class="reviews-count">
                                    <?php if ($total > 0) echo number_format($avg, 1, ',', '.') . ' · '; ?><?php echo $total; ?> avaliações
                                </span>
                            </div>
                            <p class="modal-description">
                                <?php echo htmlspecialchars($produto['descricao'] ?? 'Um produto excepcional com qualidade premium e acabamento perfeito. Ideal para quem busca excelência e funcionalidade em um único item.'); ?>
                            </p>
                            <div class="modal-price-container">
                                <div class="modal-price">
                                    <span class="price-currency">R$</span>
                                    <?php
                                    $price_parts = explode(',', number_format($produto['preco'] ?? 0, 2, ',', '.'));
                                    echo $price_parts[0];
                                    if (isset($price_parts[1])) {
                                        echo '<span class="price-decimal">,' . $price_parts[1] . '</span>';
                                    }
                                    ?>
                                </div>
                                <div class="modal-price-pix" style="color:#219653;font-weight:600;font-size:1em;margin-top:2px;">No Pix: R$<?php echo number_format($produto['preco_pix'], 2, ',', '.'); ?> <span style="font-size:0.85em;font-weight:400;">(5% OFF)</span></div>
                                <div class="modal-installment">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                        <line x1="1" y1="10" x2="23" y2="10"></line>
                                    </svg>
                                    ou até <?php echo $parcelas_max; ?>x de R$<?php echo number_format($valor_parcela_max, 2, ',', '.'); ?> sem juros
                                </div>
                            </div>
                            <div class="modal-payment-methods">
                                <span class="payment-text">Métodos de Pagamento: Cartão de Crédito e Pix</span>
                            </div>
                            <div class="modal-actions">
                                <button class="btn btn-primary" data-product-id="<?php echo $produto['id']; ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9 20L3 12l6-8M21 12H4"></path>
                                    </svg>
                                    Comprar Agora
                                </button>
                                <button class="btn btn-secondary add-to-cart-btn" data-product-id="<?php echo $produto['id']; ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="9" cy="21" r="1"></circle>
                                        <circle cx="20" cy="21" r="1"></circle>
                                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                    </svg>
                                    Adicionar ao Carrinho
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        }
    }

    // Adicionar modal para o produto de promoção
    if (is_array($promoProducts) && !empty($promoProducts)) {
        $promoProduto = $promoProducts[0];  // Pega o primeiro produto de promoção
        $modalId = 'modal-' . $promoProduto['id'];
        $parcelas_max = min(12, ceil($promoProduto['preco'] / 50));
        $valor_parcela_max = $promoProduto['preco'] / $parcelas_max;
        $random_reviews = rand(8, 47);
        $random_stock = rand(3, 20);
        $product_code = 'PROD' . str_pad($promoProduto['id'], 5, '0', STR_PAD_LEFT);
        $isFavorited = $isLoggedIn && in_array($promoProduto['id'], $favoriteProductIds);
        ?>
        <div id="<?php echo $modalId; ?>" class="modal">
            <div class="modal-content">
                <div class="modal-close">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </div>
                <div class="modal-container">
                    <div class="modal-image-container">
                        <div class="modal-image-wrapper">
                            <div class="modal-favorite <?php echo $isFavorited ? 'active' : ''; ?>" data-product-id="<?php echo $promoProduto['id']; ?>">
                                <svg viewBox="0 0 24 24" fill="<?php echo $isFavorited ? '#FF5252' : 'none'; ?>" stroke="<?php echo $isFavorited ? '#FF5252' : 'currentColor'; ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                            </div>
                            <div class="modal-image">
                                <img src="<?php echo htmlspecialchars($promoProduto['imagem_path'] ?? ''); ?>" alt="<?php echo htmlspecialchars($promoProduto['nome'] ?? ''); ?>" onerror="this.onerror=null; this.src='../adminView/uploads/produtos/placeholder.jpeg'; this.parentElement.classList.add('image-fallback');">
                            </div>
                        </div>
                    </div>
                    <div class="modal-details">
                        <div class="modal-subtitle">Detalhes do Produto</div>
                        <h2 class="modal-title"><?php echo htmlspecialchars($promoProduto['nome'] ?? ''); ?></h2>
                        <div class="modal-rating">
                            <div class="stars">
                                <?php
                                $avg = $promoProduto['avg_rating'] ?? 0;
                                $total = $promoProduto['total_ratings'] ?? 0;
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($total == 0) {
                                        echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>';
                                    } else {
                                        $filled = $i <= round($avg);
                                        echo '<svg viewBox="0 0 24 24" fill="' . ($filled ? 'currentColor' : 'none') . '" stroke="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>';
                                    }
                                }
                                ?>
                            </div>
                            <span class="reviews-count">
                                <?php if ($total > 0) echo number_format($avg, 1, ',', '.') . ' · '; ?><?php echo $total; ?> avaliações
                            </span>
                        </div>
                        <p class="modal-description">
                            <?php echo htmlspecialchars($promoProduto['descricao'] ?? 'Um produto excepcional com qualidade premium e acabamento perfeito. Ideal para quem busca excelência e funcionalidade em um único item.'); ?>
                        </p>
                        <div class="modal-price-container">
                            <div class="modal-price">
                                <span class="price-currency">R$</span>
                                <?php
                                $price_parts = explode(',', number_format($promoProduto['preco'] ?? 0, 2, ',', '.'));
                                echo $price_parts[0];
                                if (isset($price_parts[1])) {
                                    echo '<span class="price-decimal">,' . $price_parts[1] . '</span>';
                                }
                                ?>
                            </div>
                            <div class="modal-price-pix" style="color:#219653;font-weight:600;font-size:1em;margin-top:2px;">No Pix: R$<?php echo number_format($promoProduto['preco_pix'], 2, ',', '.'); ?> <span style="font-size:0.85em;font-weight:400;">(5% OFF)</span></div>
                            <div class="modal-installment">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                    <line x1="1" y1="10" x2="23" y2="10"></line>
                                </svg>
                                ou até <?php echo $parcelas_max; ?>x de R$<?php echo number_format($valor_parcela_max, 2, ',', '.'); ?> sem juros
                            </div>
                        </div>
                        <div class="modal-payment-methods">
                            <span class="payment-text">Métodos de Pagamento: Cartão de Crédito/Débito e Pix</span>
                        </div>
                        <div class="modal-actions">
                            <button class="btn btn-primary" data-product-id="<?php echo $promoProduto['id']; ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 20L3 12l6-8M21 12H4"></path>
                                </svg>
                                Comprar Agora
                            </button>
                            <button class="btn btn-secondary add-to-cart-btn" data-product-id="<?php echo $promoProduto['id']; ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="9" cy="21" r="1"></circle>
                                    <circle cx="20" cy="21" r="1"></circle>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                </svg>
                                Adicionar ao Carrinho
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }
    ?>

    <!-- Rodapé -->
    <footer>
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
                <li>
                    <h3>Avaliação</h3>
                </li>
                <li><a href="../Site/avaliacoes.php" class="link">Clique Aqui e Confira as Avaliações dos Nossos Produtos!</a></li>
            </ul>
            <ul class="list">
                <li>
                    <h3>Contatos</h3>
                </li>
                <li><a href="<?= htmlspecialchars($instagram) ?>" class="link">Instagram</a></li>
                <li><a href="mailto:<?= htmlspecialchars($email) ?>" class="link">Email</a></li>
                <li><a href="https://wa.me/<?= $whatsapp_link ?>" class="link">WhatsApp</a></li>
            </ul>
            <ul class="list">
                <li>
                    <h3>Termos de Segurança</h3>
                </li>
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
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="../Site/js/elementos/script.js"></script>
    <script src="../Site/js/index/index.js"></script>
    <script src="../Site/js/elementos/swiper-bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.isotope/3.0.6/isotope.pkgd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.imagesloaded/5.0.0/imagesloaded.pkgd.min.js"></script>
    <script>
        // Inicializar Carrossel
        document.addEventListener('DOMContentLoaded', () => {
            const carouselSwiper = new Swiper('.carousel-container', {
                slidesPerView: 1,
                spaceBetween: 0,
                loop: true,
                effect: 'fade',
                speed: 1000,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                on: {
                    init: function() {
                        if (this.slides.length > 0) {
                            this.slides[0].style.opacity = 1;
                        }
                    },
                    slideChange: function() {
                        if (this.slides && this.slides.length > 0) {
                            const slides = this.slides;
                            for (let i = 0; i < slides.length; i++) {
                                slides[i].style.opacity = 0;
                            }
                            slides[this.activeIndex].style.opacity = 1;
                        }
                    }
                }
            });
        });

        // Função para Google Sign-In
        function handleCredentialResponse(response) {
            const data = JSON.parse(atob(response.credential.split('.')[1]));
            fetch('../Site/google_login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        token: response.credential,
                        email: data.email,
                        name: data.name,
                        picture: data.picture
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Erro ao fazer login: ' + data.message);
                    }
                })
                .catch(err => console.error('Erro:', err));
        }

        // Dropdown - Categoria 
        document.addEventListener('DOMContentLoaded', () => {
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
                        window.location.href = `?categoria=${option.getAttribute('data-value')}`;
                    });
                });

                document.addEventListener('click', (e) => {
                    if (!dropdown.contains(e.target)) {
                        optionsList.classList.remove('show');
                        toggle.classList.remove('active');
                    }
                });
            });
        });

        // Modal e funcionalidades
        document.addEventListener('DOMContentLoaded', function() {
            // Abrir modal ao clicar nos cards e nos itens de promoção
            const cards = document.querySelectorAll('.product-card, .open-modal');
            cards.forEach(card => {
                card.addEventListener('click', function(e) {
                    if (e.target.closest('.add-to-cart-btn') || e.target.closest('.favorite-icon')) {
                        return;
                    }
                    const modalId = this.getAttribute('data-modal-id');
                    if (modalId) {
                        const modal = document.getElementById(modalId);
                        if (modal) {
                            openModal(modal);
                        }
                    }
                });
            });

            // Fechar modal ao clicar no botão de fechar
            const closeButtons = document.querySelectorAll('.modal-close');
            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const modal = this.closest('.modal');
                    closeModal(modal);
                });
            });

            // Fechar modal ao clicar fora do conteúdo
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeModal(this);
                    }
                });
            });

            // Fechar modal com tecla Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const openModal = document.querySelector('.modal.active');
                    if (openModal) {
                        closeModal(openModal);
                    }
                }
            });

            // Funções auxiliares
            function openModal(modal) {
                document.body.style.overflow = 'hidden';
                modal.style.display = 'block';
                setTimeout(() => {
                    modal.classList.add('active');
                }, 10);
            }

            function closeModal(modal) {
                modal.classList.remove('active');
                setTimeout(() => {
                    modal.style.display = 'none';
                    document.body.style.overflow = '';
                }, 300);
            }

            // Copyright
            const currentYear = new Date().getFullYear();
            document.getElementById('copyright').innerHTML = `Copyright © ${currentYear} Cristais Gold Lar. Todos os direitos reservados`;
        });

        // Menu Lateral e Modal de Categorias 
        document.addEventListener('DOMContentLoaded', () => {
            const menuToggle = document.querySelector('.menu-toggle');
            const sideMenu = document.querySelector('#side-menu');
            const closeMenu = document.querySelector('.close-menu');

            if (menuToggle && sideMenu && closeMenu) {
                menuToggle.addEventListener('click', (e) => {
                    e.preventDefault(); // Impede qualquer comportamento padrão
                    sideMenu.classList.add('open');
                });

                closeMenu.addEventListener('click', (e) => {
                    e.preventDefault();
                    sideMenu.classList.remove('open');
                });

                sideMenu.addEventListener('click', (e) => {
                    if (e.target === sideMenu) {
                        sideMenu.classList.remove('open');
                    }
                });
            }
        });

        // Pesquisa com Fade-In e Fade-Out
        document.addEventListener('DOMContentLoaded', () => {
            const searchToggle = document.querySelector('.search-toggle');
            const searchBarMobile = document.querySelector('.search-bar-mobile');
            const closeSearch = document.querySelector('.search-bar-mobile .clear-search');
            const searchInputMobile = document.querySelector('#search-input-mobile');
            const searchInputDesktop = document.querySelector('#search-input');
            const allProductsSection = document.querySelector('#all-products-section');
            const allProductsBtn = document.querySelector('.all-products-btn');
            const productCards = document.querySelectorAll('.all-products-grid .product-card');
            const searchIconBtn = document.querySelector('.search-bar-mobile .search-icon-btn');

            if (searchToggle && searchBarMobile && closeSearch && searchInputMobile) {
                searchToggle.addEventListener('click', (e) => {
                    e.preventDefault();
                    searchBarMobile.classList.add('open');
                    setTimeout(() => {
                        searchInputMobile.focus();
                    }, 300);
                });

                closeSearch.addEventListener('click', () => {
                    searchInputMobile.value = '';
                    searchInputDesktop.value = '';
                    closeSearch.style.display = 'none';
                    searchBarMobile.classList.remove('open');
                    const event = new Event('input');
                    searchInputDesktop.dispatchEvent(event);
                });

                searchInputMobile.addEventListener('input', () => {
                    closeSearch.style.display = searchInputMobile.value.trim() !== '' ? 'inline' : 'none';
                    searchInputDesktop.value = searchInputMobile.value;
                    const event = new Event('input');
                    searchInputDesktop.dispatchEvent(event);
                });

                if (searchIconBtn) {
                    searchIconBtn.addEventListener('click', () => {
                        searchInputMobile.focus();
                        const event = new Event('input');
                        searchInputDesktop.dispatchEvent(event);
                    });
                }
            }

            function filterProducts(query) {
                query = query.toLowerCase().trim();
                let foundProducts = false;

                productCards.forEach(card => {
                    const productName = card.getAttribute('data-name');
                    if (query === '' || productName.includes(query)) {
                        card.style.display = 'block';
                        foundProducts = true;
                    } else {
                        card.style.display = 'none';
                    }
                });

                const noResultsMessage = document.querySelector('.all-products-grid .text-center.text-red-500');
                if (!foundProducts && query !== '') {
                    if (!noResultsMessage) {
                        const message = document.createElement('p');
                        message.className = 'text-center text-red-500';
                        message.textContent = 'Nenhum produto encontrado.';
                        document.querySelector('.all-products-grid').appendChild(message);
                    }
                } else if (noResultsMessage) {
                    noResultsMessage.remove();
                }
            }

            function applyFadeIn() {
                allProductsSection.style.transform = 'translateY(0)'; // Reset explícito do transform
                allProductsSection.classList.remove('fading-out');
                allProductsSection.classList.add('search-active');
                allProductsSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                }); // Rola até o topo da seção
            }

            function applyFadeOut() {
                allProductsSection.classList.add('fading-out');
                setTimeout(() => {
                    allProductsSection.classList.remove('search-active', 'fading-out');
                    allProductsSection.style.transform = 'translateY(0)'; // Reset após fade-out
                    filterProducts('');
                }, 500); // Alinhado com a transição CSS de 0.5s
            }

            function syncSearchInputs(sourceInput, targetInput) {
                targetInput.value = sourceInput.value;
                filterProducts(sourceInput.value);
                if (sourceInput.value.trim() !== '') {
                    applyFadeIn();
                    document.querySelectorAll('.clear-search').forEach(btn => btn.style.display = 'inline');
                } else {
                    applyFadeOut();
                    document.querySelectorAll('.clear-search').forEach(btn => btn.style.display = 'none');
                }
            }

            searchInputDesktop.addEventListener('input', () => {
                syncSearchInputs(searchInputDesktop, searchInputMobile);
            });

            searchInputMobile.addEventListener('input', () => {
                syncSearchInputs(searchInputMobile, searchInputDesktop);
            });

            document.querySelectorAll('.clear-search').forEach(button => {
                button.addEventListener('click', () => {
                    searchInputDesktop.value = '';
                    searchInputMobile.value = '';
                    applyFadeOut();
                    document.querySelectorAll('.clear-search').forEach(btn => btn.style.display = 'none');
                });
            });

            searchInputDesktop.addEventListener('input', () => {
                const clearButton = searchInputDesktop.nextElementSibling;
                clearButton.style.display = searchInputDesktop.value.trim() !== '' ? 'inline' : 'none';
            });
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
    <div id="g_id_onload"
        data-client_id="818588658305-7hfcrmuocusbi88bpq0insq09srdv8jd.apps.googleusercontent.com"
        data-context="signin"
        data-ux_mode="popup"
        data-callback="handleCredentialResponse"
        data-auto_prompt="false">
    </div>

    <?php if ($showWelcomeModal): ?>
    <div id="welcome-modal" class="modal" style="display:flex;align-items:center;justify-content:center;z-index:3000;">
        <div class="modal-content" style="max-width:400px;text-align:center;padding:32px 24px;border-radius:16px;background:#fff;box-shadow:0 8px 32px rgba(0,0,0,0.18);">
            <h2>Bem-vindo à Cristais Gold Lar!</h2>
            <p style="margin:18px 0 0 0;font-size:1.1em;">Parabéns, seu cadastro foi realizado com sucesso.<br><b>Sua primeira compra terá 10% de desconto automático!</b></p>
            <button onclick="document.getElementById('welcome-modal').style.display='none'" style="margin-top:24px;padding:10px 28px;background:#F3BA00;color:#fff;border:none;border-radius:8px;font-size:1em;cursor:pointer;">OK</button>
        </div>
    </div>
    <script>document.body.style.overflow='hidden';document.getElementById('welcome-modal').addEventListener('click',function(e){if(e.target===this){this.style.display='none';document.body.style.overflow='';}});document.querySelector('#welcome-modal button').addEventListener('click',function(){document.body.style.overflow='';});</script>
    <?php endif; ?>
    <?php if ($temDescontoPrimeiraCompra): ?>
    <div class="notification-bar" style="background:#4e8d7c;color:#fff;font-weight:600;z-index:2000;position:relative;top:0;left:0;width:100%;text-align:center;">
        <span>Bem-vindo! Você tem <b>10% de desconto</b> na sua primeira compra. O desconto será aplicado automaticamente no checkout.</span>
    </div>
    <?php endif; ?>
</body>

</html>