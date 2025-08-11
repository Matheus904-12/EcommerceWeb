

// Verificar login
function checkLogin(callback) {
    fetch('../Site/api/compras/cart_operations.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=check_login'
    })
    .then(response => response.json())
    .then(data => callback(data.status === 'success' && data.data.isLoggedIn))
    .catch(error => {
        console.error('Erro ao verificar login:', error);
        showSiteAlert('Erro ao verificar login.', 'error');
        callback(false);
    });
}

// Atualizar contadores na UI
function updateCounters(cartCount, favoritesCount) {
    document.querySelector('.cart-counter').textContent = cartCount;
    document.querySelector('.favorites-counter').textContent = favoritesCount;
}

// Função para adicionar ao carrinho
function addToCart(productId, button) {
    checkLogin(isLoggedIn => {
        if (!isLoggedIn) {
            showSiteAlert('Faça login para adicionar ao carrinho.', 'error');
            window.location.href = '../Site/login_site.php';
            return;
        }

        // Feedback imediato
        button.disabled = true;
        button.classList.add('loading');
        const originalText = button.textContent;
        button.textContent = 'Adicionando...';

        // Atualização otimística
        const cartCounter = document.querySelector('.cart-counter');
        let currentCartCount = parseInt(cartCounter.textContent) || 0;
        cartCounter.textContent = currentCartCount + 1;

        fetch('../Site/api/compras/cart_operations.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=add_to_cart&product_id=${productId}&quantity=1`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showSiteAlert(data.message || 'Produto adicionado ao carrinho!');
                updateCounters(data.data.cartCount, parseInt(document.querySelector('.favorites-counter').textContent));
            } else {
                // Reverter atualização otimística
                cartCounter.textContent = currentCartCount;
                showSiteAlert(data.message || 'Erro ao adicionar ao carrinho.', 'error');
            }
        })
        .catch(error => {
            // Reverter atualização otimística
            cartCounter.textContent = currentCartCount;
            console.error('Erro:', error);
            showSiteAlert('Erro ao adicionar ao carrinho.', 'error');
        })
        .finally(() => {
            button.disabled = false;
            button.classList.remove('loading');
            button.textContent = originalText;
        });
    });
}

// Função para comprar agora
function buyNow(productId, button) {
    addToCart(productId, button);
    setTimeout(() => window.location.href = '../Site/checkout.php', 500);
}

// Função para adicionar/remover favoritos
function toggleFavorite(productId, element) {
    checkLogin(isLoggedIn => {
        if (!isLoggedIn) {
            showSiteAlert('Faça login para gerenciar favoritos.', 'error');
            window.location.href = '../Site/login_site.php';
            return;
        }

        const isActive = element.classList.contains('active');
        const action = isActive ? 'remove_from_favorites' : 'add_to_favorites';

        // Feedback imediato
        element.classList.add('loading');
        const svg = element.querySelector('svg');
        const originalFill = svg.getAttribute('fill');
        const originalStroke = svg.getAttribute('stroke');

        // Atualização otimística
        const favoritesCounter = document.querySelector('.favorites-counter');
        let currentFavoritesCount = parseInt(favoritesCounter.textContent) || 0;
        if (isActive) {
            element.classList.remove('active');
            svg.setAttribute('fill', 'none');
            svg.setAttribute('stroke', 'currentColor');
            favoritesCounter.textContent = currentFavoritesCount - 1;
        } else {
            element.classList.add('active');
            svg.setAttribute('fill', '#FF5252');
            svg.setAttribute('stroke', '#FF5252');
            favoritesCounter.textContent = currentFavoritesCount + 1;
        }

        fetch('../Site/api/compras/cart_operations.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=${action}&product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showSiteAlert(data.message || (isActive ? 'Removido dos favoritos!' : 'Adicionado aos favoritos!'));
                updateCounters(parseInt(document.querySelector('.cart-counter').textContent), data.data.favoritesCount);
            } else {
                // Reverter atualização otimística
                element.classList.toggle('active', isActive);
                svg.setAttribute('fill', originalFill);
                svg.setAttribute('stroke', originalStroke);
                favoritesCounter.textContent = currentFavoritesCount;
                showSiteAlert(data.message || 'Erro ao atualizar favoritos.', 'error');
            }
        })
        .catch(error => {
            // Reverter atualização otimística
            element.classList.toggle('active', isActive);
            svg.setAttribute('fill', originalFill);
            svg.setAttribute('stroke', originalStroke);
            favoritesCounter.textContent = currentFavoritesCount;
            console.error('Erro:', error);
            showSiteAlert('Erro ao atualizar favoritos.', 'error');
        })
        .finally(() => element.classList.remove('loading'));
    });
}

// Função para sincronizar contadores
function syncCounters() {
    fetch('../Site/api/compras/cart_operations.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=get_counters'
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            updateCounters(data.data.cartCount, data.data.favoritesCount);
        }
    })
    .catch(error => console.error('Erro ao sincronizar contadores:', error));
}

// --- Scroll e Modal Persistente ---
(function() {
    // Salvar posição e modal/seção
    function saveState() {
        sessionStorage.setItem('scrollY', window.scrollY);
        const modal = document.querySelector('.modal.active, .category-modal.open');
        sessionStorage.setItem('modalOpen', modal ? modal.id : '');
    }
    window.addEventListener('beforeunload', saveState);
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'hidden') saveState();
    });
    // Restaurar
    window.addEventListener('load', function() {
        const y = sessionStorage.getItem('scrollY');
        if (y) window.scrollTo(0, parseInt(y));
        const modalId = sessionStorage.getItem('modalOpen');
        if (modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                if (modal.classList.contains('modal')) {
                    modal.style.display = 'block';
                    setTimeout(() => modal.classList.add('active'), 10);
                } else if (modal.classList.contains('category-modal')) {
                    modal.classList.add('open');
                    setTimeout(() => {
                        const content = modal.querySelector('.modal-content');
                        if (content) content.focus();
                    }, 100);
                }
            }
        }
    });
})();

// --- Barra de pesquisa mobile: X sempre visível ---
document.addEventListener('DOMContentLoaded', function() {
    const searchBarMobile = document.querySelector('.search-bar-mobile');
    const closeSearch = document.getElementById('close-search-mobile');
    if (searchBarMobile && closeSearch) {
        closeSearch.style.display = 'inline';
        closeSearch.addEventListener('click', function() {
            searchBarMobile.classList.remove('open');
        });
    }
});

// Inicialização
document.addEventListener('DOMContentLoaded', () => {
    // Adicionar ao carrinho
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', (e) => {
            e.stopPropagation();
            const productId = button.getAttribute('data-product-id');
            addToCart(productId, button);
        });
    });

    // Comprar agora
    document.querySelectorAll('.btn-primary').forEach(button => {
        button.addEventListener('click', (e) => {
            e.stopPropagation();
            const productId = button.getAttribute('data-product-id');
            buyNow(productId, button);
        });
    });

    // Toggle favorito
    document.querySelectorAll('.favorite-icon, .modal-favorite').forEach(button => {
        button.addEventListener('click', (e) => {
            e.stopPropagation();
            const productId = button.getAttribute('data-product-id');
            if (productId) {
                toggleFavorite(productId, button);
            }
        });
    });

    // Sincronizar contadores na inicialização
    syncCounters();

    // Estilo para modais e loading
    const style = document.createElement('style');
    style.textContent = `
        .modal {
            transition: opacity 0.3s ease;
            opacity: 0;
        }
        .modal.active {
            opacity: 1;
        }
        .modal-content {
            transform: translateY(100px);
            transition: transform 0.3s ease, opacity 0.3s ease;
            opacity: 0;
        }
        .modal.active .modal-content {
            transform: translateY(0);
            opacity: 1;
        }
        .favorite-icon.active svg,
        .modal-favorite.active svg {
            fill: #FF5252;
            stroke: #FF5252;
        }
        .counter {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #FF5252;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
        }
        .add-to-cart-btn.loading,
        .btn-primary.loading,
        .favorite-icon.loading,
        .modal-favorite.loading {
            opacity: 0.7;
            cursor: not-allowed;
        }
        .add-to-cart-btn.loading:after,
        .btn-primary.loading:after {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #fff;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            margin-left: 8px;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
});