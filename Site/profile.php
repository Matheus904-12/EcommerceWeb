<?php
require_once '../adminView/config/dbconnect.php';
require_once '../adminView/controller/Produtos/UserCartController.php';
require_once '../adminView/controller/Produtos/UserFavoritesController.php';
require_once '../adminView/controller/Produtos/ProductController.php';
$userCartController = new UserCartController($conn);
$UserFavoritesController = new UserFavoritesController($conn);
$productController = new ProductController($conn);
session_start();

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
                $userPicture = $row['profile_picture'];
            } else {
                $userPicture = '/img/icons/perfil.png';
            }
            $stmt->close();
        } else {
            $userPicture = '/img/icons/perfil.png';
        }
    }
}

// Obtém as informações do usuário
$userId = $_SESSION['user_id'];
$userName = $_SESSION['username'];
if (strlen($userName) > 16) {
    $userName = substr($userName, 0, 16) . "...";
}
$userPicture = isset($_SESSION['user_picture']) && !empty($_SESSION['user_picture'])
    ? $_SESSION['user_picture']
    : '../Site/img/icons/perfil.png';
$userEmail = $_SESSION['user_email'] ?? '';

// Carregar dados do usuário
$query = "SELECT profile_picture, email, name, telefone, endereco, cep, numero_casa, cpf FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    die("Erro na preparação da query: " . $conn->error);
}
$stmt->bind_param("i", $userId);
if (!$stmt->execute()) {
    die("Erro na execução da query: " . $stmt->error);
}
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $userPicture = $row['profile_picture'] ?? $userPicture;
    $userEmail = $row['email'] ?? $userEmail;
    $userNome = $row['name'] ?? $userName;
    $userTelefone = $row['telefone'] ?? '';
    $userEndereco = $row['endereco'] ?? '';
    $userCep = $row['cep'] ?? '';
    $userNumeroCasa = $row['numero_casa'] ?? '';
    $userCpf = $row['cpf'] ?? '';
    $_SESSION['user_picture'] = $userPicture;
    $_SESSION['user_email'] = $userEmail;
}
$stmt->close();

// Buscar notificações do usuário
$query = "SELECT id, titulo, mensagem, data_criacao, lida FROM notificacoes WHERE usuario_id = ? ORDER BY data_criacao DESC";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    die("Erro na preparação da query de notificações: " . $conn->error);
}
$stmt->bind_param("i", $userId);
if (!$stmt->execute()) {
    die("Erro na execução da query de notificações: " . $stmt->error);
}
$notificacoes = $stmt->get_result();
$stmt->close();

// Buscar pedidos do usuário
$queryCompras = "SELECT o.*, 
    (SELECT p.imagem FROM order_items oi JOIN produtos p ON oi.product_id = p.id WHERE oi.order_id = o.id LIMIT 1) as imagem,
    (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as qtd_itens 
    FROM orders o WHERE o.user_id = ? ORDER BY o.order_date DESC";
$stmtCompras = $conn->prepare($queryCompras);
if ($stmtCompras === false) {
    die("Erro na preparação da query de pedidos: " . $conn->error);
}
$stmtCompras->bind_param("i", $userId);
if (!$stmtCompras->execute()) {
    die("Erro na execução da query de pedidos: " . $stmtCompras->error);
}
$resultCompras = $stmtCompras->get_result();
$compras = $resultCompras->fetch_all(MYSQLI_ASSOC);
$stmtCompras->close();

// Carregar contadores de carrinho e favoritos
$userCartController = new UserCartController($conn);
$userFavoritesController = new UserFavoritesController($conn);
$cartItems = $userCartController->getCartItems($userId);
$favoriteItems = $userFavoritesController->getFavoriteItems($userId);
$cartCount = array_sum(array_column($cartItems, 'quantity'));
$favoriteCount = count($favoriteItems);

// Configurações do site
$siteConfigPath = __DIR__ . '/../adminView/config_site.json';
if (!file_exists($siteConfigPath)) {
    echo "Erro ao carregar as configurações do site.";
    exit();
}
$jsonContent = file_get_contents($siteConfigPath);
if ($jsonContent === false) {
    echo "Erro ao carregar as configurações do site.";
    exit();
}
$configData = json_decode($jsonContent, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "Erro ao carregar as configurações do site.";
    exit();
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
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cristais Gold Lar - Configurações</title>
    <link rel="shortcut icon" href="../adminView/assets/images/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="../Site/css/index/index.css">
    <link rel="stylesheet" href="../Site/css/profile/profile.css">
    <link rel="stylesheet" href="../Site/css/global-alerts.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />

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
                <a href="meusItens.php" class="cart-icon">
                    <img src="../Site/img/icons/compras.png" alt="Carrinho" id="cart-icon">
                    <span class="counter cart-counter"><?php echo $cartCount; ?></span>
                </a>
                <a href="meusItens.php" class="cart-icon">
                    <img src="../Site/img/icons/salvar preto.png" alt="Favoritos" id="favorites-icon">
                    <span class="counter favorites-counter"><?php echo $favoriteCount; ?></span>
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
            <li class="side-menu-item"><a href="../Site/profile.php">Perfil</a></li>
            <li class="side-menu-item"><a href="../Site/includes/configuracoes/logout.php">Sair</a></li>
        </ul>
    </div>

    <!-- Tabbar para dispositivos móveis -->
    <div class="tabbar">
        <a href="../Site/meusItens.php">
            <img src="../Site/img/icons/salvar preto.png" alt="Favoritos">
            <span>Favoritos</span>
            <span class="counter favorites-counter"><?php echo $favoriteCount; ?></span>
        </a>
        <a href="<?= $isLoggedIn ? '../Site/profile.php' : '../Site/login_site.php' ?>">
            <img src="<?= $isLoggedIn ? (!empty($userPicture) && strpos($userPicture, 'http') !== 0 ? '../adminView/uploads/profile_pictures/' . htmlspecialchars($userPicture) : htmlspecialchars($userPicture)) : '../Site/img/icons/perfil.png' ?>" alt="Perfil" onerror="this.src='../Site/img/icons/perfil.png';">
            <span><?= $isLoggedIn ? 'Perfil' : 'Entrar' ?></span>
        </a>
        <a href="../Site/meusItens.php">
            <img src="../Site/img/icons/compras.png" alt="Carrinho">
            <span>Carrinho</span>
            <span class="counter cart-counter"><?php echo $cartCount; ?></span>
        </a>
    </div>

    <!-- Seção de Perfil -->
    <section class="profile-section">
    <aside class="sidebar">
            <ul class="side-menu-items">
                <li class="side-menu-item" data-section="notificacoes" role="button" tabindex="0">Notificações</li>
                <li class="side-menu-item" data-section="meus-dados" role="button" tabindex="0">Meus Dados</li>
                <li class="side-menu-item" data-section="senha" role="button" tabindex="0">Minha Senha</li>
                <li class="side-menu-item" data-section="meus-pedidos" role="button" tabindex="0">Meus Pedidos</li>
            </ul>
        </aside>
        <div class="main-content">
            <!-- Notificações -->
            <div class="profile-container active" id="notificacoes">
                <div class="profile-header">
                    <h2>Notificações</h2>
                </div>
                <div class="profile-card">
                    <?php if ($notificacoes->num_rows > 0) : ?>
                        <ul class="notification-list">
                            <?php while ($notificacao = $notificacoes->fetch_assoc()) : ?>
                                <li class="notification-item <?php echo $notificacao['lida'] ? 'lida' : ''; ?>">
                                    <strong><?php echo htmlspecialchars($notificacao['titulo']); ?></strong>
                                    <p><?php echo htmlspecialchars($notificacao['mensagem']); ?></p>
                                    <small>
                                        <?php echo date('d/m/Y H:i', strtotime($notificacao['data_criacao'])); ?>
                                        <?php if ($notificacao['lida']) : ?>
                                            - Lida
                                        <?php endif; ?>
                                    </small>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else : ?>
                        <p class="empty-message">Você não tem notificações.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Meus Dados -->
            <div class="profile-container" id="meus-dados">
                <div class="profile-header">
                    <h2>Meus Dados</h2>
                </div>
                <div class="profile-card">
                    <form action="../Site/includes/profile/atualizar_dados.php" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="nome">Nome</label>
                            <input type="text" name="nome" id="nome" value="<?php echo htmlspecialchars($userNome); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($userEmail); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="cpf">CPF</label>
                            <input type="text" name="cpf" id="cpf" value="<?php echo htmlspecialchars($userCpf); ?>" maxlength="14" pattern="\d{3}\.\d{3}\.\d{3}-\d{2}" placeholder="000.000.000-00">
                        </div>
                        <div class="form-group">
                            <label for="telefone">Telefone</label>
                            <input type="text" name="telefone" id="telefone" value="<?php echo htmlspecialchars($userTelefone); ?>">
                        </div>
                        <div class="form-group">
                            <label for="endereco">Endereço</label>
                            <input type="text" name="endereco" id="endereco" value="<?php echo htmlspecialchars($userEndereco); ?>">
                        </div>
                        <div class="form-group">
                            <label for="cep">CEP</label>
                            <input type="text" name="cep" id="cep" value="<?php echo htmlspecialchars($userCep); ?>">
                        </div>
                        <div class="form-group">
                            <label for="numero_casa">Número da Casa</label>
                            <input type="text" name="numero_casa" id="numero_casa" value="<?php echo htmlspecialchars($userNumeroCasa); ?>">
                        </div>
                        <div class="form-group">
                            <label for="profile_picture">Foto de Perfil</label>
                            <?php if (isset($_SESSION['google_user']) && $_SESSION['google_user']) : ?>
                                <input type="file" id="profile_picture" name="profile_picture" accept="image/*" disabled>
                                <p>Você fez login com Google. Não é possível alterar a foto de perfil.</p>
                            <?php else : ?>
                                <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                            <?php endif; ?>
                            <img src="<?php echo !empty($userPicture) && strpos($userPicture, 'http') !== 0 ? '../adminView/uploads/profile_pictures/' . htmlspecialchars($userPicture) : htmlspecialchars($userPicture); ?>" alt="Foto de Perfil" class="profile-picture-preview" onerror="this.src='../Site/img/icons/perfil.png';">
                        </div>
                        <div class="button-container">
                            <button type="submit" class="save-btn">Atualizar Dados</button>
                            <button type="button" class="delete-account-btn" onclick="confirmDeleteAccount()">Excluir Minha Conta</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Trocar Senha -->
            <div class="profile-container" id="senha">
                <div class="profile-header">
                    <h2>Minha Senha</h2>
                </div>
                <div class="profile-card">
                    <?php if (!isset($_SESSION['google_user'])) : ?>
                        <form action="../Site/includes/profile/atualizar_senha.php" method="POST">
                            <div class="form-group">
                                <label for="senha_atual">Senha Atual</label>
                                <div class="password-field">
                                    <input type="password" name="senha_atual" id="senha_atual" required>
                                    <i class="fa-solid fa-eye toggle-password" onclick="togglePassword('senha_atual')"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="nova_senha">Nova Senha</label>
                                <div class="password-field">
                                    <input type="password" name="nova_senha" id="nova_senha" required>
                                    <i class="fa-solid fa-eye toggle-password" onclick="togglePassword('nova_senha')"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="confirmar_senha">Confirmar Nova Senha</label>
                                <div class="password-field">
                                    <input type="password" name="confirmar_senha" id="confirmar_senha" required>
                                    <i class="fa-solid fa-eye toggle-password" onclick="togglePassword('confirmar_senha')"></i>
                                </div>
                            </div>
                            <button type="submit" class="save-btn2">Atualizar Senha</button>
                        </form>
                    <?php else : ?>
                        <p>Você fez login com o Google. Não é possível alterar a senha aqui.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Meus Pedidos -->
            <div class="profile-container" id="meus-pedidos">
                <div class="profile-header">
                    <h2>Meus Pedidos</h2>
                </div>
                <div class="profile-card">
                    <?php if (count($compras) > 0) : ?>
                        <div class="orders-list">
                            <?php foreach ($compras as $compra) :
                                $total = 'R$ ' . number_format($compra['total'], 2, ',', '.');
                                $statusTexto = ucfirst($compra['status']);
                                $statusClass = 'status-' . strtolower(str_replace('_', '-', $compra['status']));
                                $metodoPagamento = ucfirst($compra['payment_method']);
                                $data = date('d/m/Y H:i', strtotime($compra['order_date']));
                            ?>
                                <div class="order-item">
                                    <div class="order-image">
                                        <?php if (!empty($compra['imagem'])) : ?>
                                            <img src="../adminView/uploads/produtos/<?php echo htmlspecialchars($compra['imagem']); ?>" alt="Imagem do pedido" onerror="this.src='../Site/img/placeholder.jpeg';">
                                        <?php else : ?>
                                            <div class="no-image">📦</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="order-info">
                                        <h3>Pedido #<?php echo htmlspecialchars($compra['id']); ?></h3>
                                        <p>Data: <?php echo $data; ?></p>
                                        <p>Total: <?php echo $total; ?></p>
                                        <p>Itens: <?php echo htmlspecialchars($compra['qtd_itens']); ?></p>
                                        <p>Pagamento: <?php echo $metodoPagamento; ?></p>
                                        <?php if (!empty($compra['tracking_code'])) : ?>
                                            <p>Código de Rastreio: <?php echo htmlspecialchars($compra['tracking_code']); ?></p>
                                        <?php endif; ?>
                                        <p class="order-status"><span class="<?php echo $statusClass; ?>"><?php echo $statusTexto; ?></span></p>
                                        <a href="detalhes-pedido.php?id=<?php echo $compra['id']; ?>" class="btn-detalhes">Ver Detalhes</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p class="empty-message">Você ainda não realizou nenhum pedido.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

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
                <li><a href="../politica-de-privacidade.php" class="link">Política de Privacidade</a></li>
                <li><a href="../termos-de-servico.php" class="link">Termos de Serviço</a></li>
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

    <!-- ALERTA CENTRALIZADO (debug) -->
    <!-- <button style="position:fixed;top:10px;right:10px;z-index:99999;" onclick="showProfileAlert('Mensagem de teste!','success')">Testar Alerta</button> -->

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <script src="../Site/js/global-alerts.js"></script>
    <script src="../Site/js/profile/profile.js"></script>
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
                        window.location.href = category ? `index.php?categoria=${category}` : 'index.php';
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

            if (categoryToggle && categoryModal && closeModal) {
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

            // Copyright
            const currentYear = new Date().getFullYear();
            document.getElementById('copyright').innerHTML = `Copyright © ${currentYear} Cristais Gold Lar. Todos os direitos reservados`;

            // Navegação do Menu Lateral
            document.querySelectorAll('.sidebar-item').forEach(item => {
                item.addEventListener('click', () => {
                    // Remove a classe 'active' de todos os itens
                    document.querySelectorAll('.sidebar-item').forEach(i => i.classList.remove('active'));
                    // Adiciona a classe 'active' ao item clicado
                    item.classList.add('active');

                    // Esconde todos os contêineres
                    document.querySelectorAll('.profile-container').forEach(container => {
                        container.classList.remove('active');
                    });

                    // Mostra o contêiner correspondente
                    const section = item.getAttribute('data-section');
                    document.getElementById(section).classList.add('active');
                });
            });

            // Função para exibir o modal de exclusão
            window.confirmDeleteAccount = function() {
                const modal = document.createElement('div');
                modal.className = 'modal';
                modal.innerHTML = `
                    <div class="modal-content">
                        <h3>Excluir Conta</h3>
                        <p>Tem certeza que deseja excluir sua conta? Esta ação não pode ser desfeita.</p>
                        <div class="modal-buttons">
                            <button class="modal-btn confirm" onclick="deleteAccount()">Confirmar</button>
                            <button class="modal-btn cancel" onclick="closeModal()">Cancelar</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);

                // Fechar o modal ao clicar fora
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) closeModal();
                });
            };

            // Função para fechar o modal
            window.closeModal = function() {
                const modal = document.querySelector('.modal');
                if (modal) modal.remove();
            };

            // Função para confirmar a exclusão
            window.deleteAccount = function() {
                window.location.href = 'includes/configuracoes/delete_account.php';
                closeModal();
            };

            // Preview da foto de perfil
            const profilePictureInput = document.getElementById('profile_picture');
            const profilePicturePreview = document.querySelector('.profile-picture-preview');
            if (profilePictureInput && profilePicturePreview) {
                profilePictureInput.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            profilePicturePreview.src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            // Função para alternar visibilidade da senha
            window.togglePassword = function(id) {
                const input = document.getElementById(id);
                const icon = input.nextElementSibling;
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            };



            // Formatação automática do CPF
            const cpfInput = document.getElementById('cpf');
            if (cpfInput) {
                cpfInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é dígito
                    value = value.replace(/(\d{3})(\d)/, '$1.$2'); // Coloca ponto depois do 3º dígito
                    value = value.replace(/(\d{3})(\d)/, '$1.$2'); // Coloca ponto depois do 6º dígito
                    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2'); // Coloca hífen antes dos últimos 2 dígitos
                    e.target.value = value;
                });
            }

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
