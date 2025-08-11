/**
 * Enhanced Checkout System
 * Handles checkout process, payment methods, form validation, and API interactions
 */

document.addEventListener('DOMContentLoaded', function () {
    // DOM elements
    const checkoutForm = document.getElementById('checkout-form');
    const paymentMethods = document.querySelectorAll('.payment-method');
    const paymentMethodInput = document.getElementById('payment_method');
    const creditCardDetails = document.getElementById('credit_card_details');
    const pixDetails = document.getElementById('pix_details');
    const cardNumberInput = document.getElementById('card_number');
    const cardExpiryInput = document.getElementById('card_expiry');
    const cardCvvInput = document.getElementById('card_cvv');
    const cepInput = document.getElementById('shipping_cep');
    const btnFindCep = document.getElementById('btn-find-cep');
    const processingOverlay = document.getElementById('processing-overlay');
    const pixModal = document.getElementById('pix-modal');
    const phoneInput = document.getElementById('shipping_phone');
    const savedCards = document.querySelectorAll('.saved-card');
    const newCardForm = document.getElementById('new_card_form');
    const CIELO_MERCHANT_ID = "e85b80d2-3bec-4c7b-b64f-cb24aa76f51e";
    const CIELO_MERCHANT_KEY = "s7KwzcUMS9fkYsKgTTijA0uYc4RSyKS1QlJbhhkD";
    const STORE_ADDRESS = "Rua Mário Bochetti 1102, Suzano, SP, 08673-021";

    // Track order information globally
    const orderInfo = {
        paymentMethod: '',
        installments: 1,
        orderNumber: generateOrderNumber(),
        orderTotal: 0
    };

    // Initialize payment method
    let selectedPaymentMethod = '';

    // Detectar se 3DS está disponível (função global)
    const is3DSAvailable = typeof window.bpmpi_authenticate === 'function';

    // Se 3DS não está disponível, ocultar/desabilitar opção de débito
    if (!is3DSAvailable) {
        const debitOption = document.querySelector('.payment-method[data-method="debit_card"]');
        if (debitOption) {
            debitOption.style.display = 'none';
        }
    }

    // Initialize masks for form inputs
    initializeInputMasks();

    // Setup event listeners
    setupEventListeners();

    // Add installment selector to the page for credit card
    addCreditCardInstallmentSelector();

    /**
     * Initialize the checkout page
     */
    function initializeCheckout() {
        initializeInputMasks();
        setupEventListeners();

        // Auto-select first payment method (credit card by default)
        const firstPaymentMethod = document.querySelector('.payment-method');
        if (firstPaymentMethod) {
            selectPaymentMethod(firstPaymentMethod.dataset.method);
        }

        // Initialize installment selectors
        addCreditCardInstallmentSelector();

        // Initial price calculation
        updateAllPrices();

        // Set up real-time price updates
        document.querySelectorAll('.cart-item-quantity input').forEach(input => {
            input.addEventListener('change', updateAllPrices);
        });

        // Update prices when shipping is calculated
        if (cepInput) { // Add null check
            cepInput.addEventListener('blur', function () {
                if (this.value.replace(/\D/g, '').length === 8) {
                    findAddressByCep();
                }
            });
        } else {
            console.warn('CEP input not found, skipping event listener attachment');
        }

        // Initialize card inputs for brand detection
        initializeCardInputs();
    }

    /**
     * Initialize card input event listeners for brand detection
     */
    function initializeCardInputs() {
        if (cardNumberInput) {
            cardNumberInput.removeEventListener('input', handleCardInput); // Prevent duplicate listeners
            cardNumberInput.addEventListener('input', handleCardInput);

            // Trigger initial detection if input has a value
            if (cardNumberInput.value) {
                handleCardInput.call(cardNumberInput);
            }
        } else {
            console.warn('Card number input not found');
        }
    }

    /**
     * Handle card number input to detect and display card brand icon
     */
    function handleCardInput() {
        const cardNumber = this.value.replace(/\s/g, '');
        const cardTypeIconContainer = document.querySelector('.card-type-icon');

        if (!cardTypeIconContainer) {
            console.warn('Card type icon container not found');
            return;
        }

        // Clear existing icons
        cardTypeIconContainer.innerHTML = '';

        // Create new icon element
        const iconElement = document.createElement('i');
        iconElement.style.fontSize = '24px';
        iconElement.style.marginLeft = '10px';

        // Detect card brand based on number pattern
        if (cardNumber.startsWith('4')) {
            iconElement.className = 'fab fa-cc-visa';
        } else if (/^5[1-5]/.test(cardNumber)) {
            iconElement.className = 'fab fa-cc-mastercard';
        } else if (/^3[47]/.test(cardNumber)) {
            iconElement.className = 'fab fa-cc-amex';
        } else if (/^6(?:011|5)/.test(cardNumber)) {
            iconElement.className = 'fab fa-cc-discover';
        } else if (/^(?:30[0-5]|36|38)/.test(cardNumber)) {
            iconElement.className = 'fab fa-cc-diners-club';
        } else if (/^35(?:2[89]|[3-8][0-9])/.test(cardNumber)) {
            iconElement.className = 'fab fa-cc-jcb';
        } else if (/^((5067)|(4576)|(4011))/.test(cardNumber)) {
            iconElement.className = 'fas fa-credit-card'; // Elo não tem ícone específico no FontAwesome
        } else if (/^38/.test(cardNumber)) {
            iconElement.className = 'fas fa-credit-card'; // Hipercard não tem ícone específico
        } else {
            iconElement.className = 'fas fa-credit-card';
        }

        cardTypeIconContainer.appendChild(iconElement);
    }

    /**
     * Process credit card payment using Cielo API
     */

    async function processCreditCardPayment() {
        if (!validateCardInputs()) return;

        // Bloquear débito se não houver 3DS
        if (selectedPaymentMethod === 'debit_card' && !is3DSAvailable) {
            showError('Pagamento com cartão de débito não está disponível sem autenticação 3DS. Utilize crédito ou PIX.');
            processingOverlay.style.display = 'none';
            return;
        }

        const isDebit = selectedPaymentMethod === 'debit_card';
        const brand = detectCardBrand(cardNumberInput.value);

        // Mapear nomes para padrão Cielo
        const brandMapping = {
            'Visa': 'Visa',
            'Master': 'Master',
            'Amex': 'Amex',
            'Elo': 'Elo',
            'Hipercard': 'Hipercard',
            'Hiper': 'Hiper'
        };
        const cieloBrand = brandMapping[brand] || 'Visa';

        // Formatar validade
        const expiry = cardExpiryInput.value.replace(/\D/g, '');
        if (expiry.length !== 4) {
            showError('Data de validade inválida. Use o formato MM/AA.');
            return;
        }

        const expiryMonth = expiry.substring(0, 2);
        const expiryYear = `20${expiry.substring(2, 4)}`;
        const expirationDate = `${expiryMonth}/${expiryYear}`;

        // Validação de data
        const currentYear = new Date().getFullYear();
        const currentMonth = new Date().getMonth() + 1;
        const inputYear = parseInt(expiryYear);
        const inputMonth = parseInt(expiryMonth);

        if (inputMonth < 1 || inputMonth > 12) {
            showError('Mês de validade inválido.');
            return;
        }
        if (inputYear < currentYear || (inputYear === currentYear && inputMonth < currentMonth)) {
            showError('Data de validade expirada.');
            return;
        }

        // Preencher campos 3DS (mantém para compatibilidade, mas não usa)
        document.getElementById('bpmpi_installments').value = isDebit ? 1 : orderInfo.installments;
        document.getElementById('bpmpi_paymentmethod').value = isDebit ? 'Debit' : 'Credit';
        document.getElementById('card_expiry_month').value = expiryMonth;
        document.getElementById('card_expiry_year').value = expiryYear;

        // 3DS desabilitado: seguir direto para pagamento
        try {
            processingOverlay.style.display = 'flex';

            // Dados para API Cielo
            const paymentData = {
                MerchantOrderId: orderInfo.orderNumber,
                Customer: {
                    Name: document.getElementById('card_name').value.trim(),
                    Email: document.getElementById('cardholder_email').value,
                    Identity: document.getElementById('identification_number').value.replace(/\D/g, ''),
                    IdentityType: 'CPF'
                },
                Payment: {
                    Type: isDebit ? 'DebitCard' : 'CreditCard',
                    Amount: Math.round(orderInfo.orderTotal * 100),
                    Installments: isDebit ? 1 : orderInfo.installments,
                    Capture: true,
                    SoftDescriptor: 'GOLDLARCRISTAIS',
                    Authenticate: false,
                    [isDebit ? 'DebitCard' : 'CreditCard']: {
                        CardNumber: cardNumberInput.value.replace(/\D/g, ''),
                        Holder: document.getElementById('card_name').value.trim(),
                        ExpirationDate: expirationDate,
                        SecurityCode: cardCvvInput.value.trim(),
                        Brand: cieloBrand
                    }
                }
            };

            console.log('Enviando dados para Cielo:', paymentData);
            
            const response = await fetch('includes/checkout/process_cielo_payment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(paymentData)
            });

            console.log('Resposta da Cielo:', response.status, response.statusText);

            if (!response.ok) {
                const errorData = await response.text();
                throw new Error(`HTTP ${response.status}: ${errorData}`);
            }

            const data = await response.json();

            console.log('Dados da resposta da Cielo:', data);

            if (data.success) {
                const paymentIdInput = document.createElement('input');
                paymentIdInput.type = 'hidden';
                paymentIdInput.name = 'payment_id';
                paymentIdInput.value = data.payment.PaymentId;
                checkoutForm.appendChild(paymentIdInput);

                if (isDebit && data.payment.AuthenticationUrl) {
                    localStorage.setItem('cielo_payment_id', data.payment.PaymentId);
                    localStorage.setItem('order_tracking_code', 'CG' + Math.random().toString(36).substr(2, 8).toUpperCase());
                    window.location.href = data.payment.AuthenticationUrl;
                } else {
                    console.log('[Checkout] Pagamento cartão aprovado, submetendo formulário');
                    try {
                        checkoutForm.submit();
                        setTimeout(() => {
                            if (!window.__checkout_submitted) {
                                console.error('[Checkout] Fallback: submit forçado após cartão');
                    checkoutForm.submit();
                            }
                        }, 2000);
                    } catch (e) {
                        console.error('[Checkout] Erro ao submeter formulário cartão:', e);
                        checkoutForm.submit();
                    }
                }
            } else {
                showError(data.message || 'Erro no processamento do pagamento');
            }
        } catch (error) {
            console.error('Erro na transação:', error);
            showError(`Falha na comunicação: ${error.message}`);
        } finally {
            processingOverlay.style.display = 'none';
        }
    }

    // Função de validação dos campos do cartão
    function validateCardInputs() {
        const cardNumber = cardNumberInput.value.replace(/\D/g, '');
        const cvv = cardCvvInput.value.trim();
        const expiry = cardExpiryInput.value;

        // Verificação de Luhn
        if (!isValidLuhn(cardNumber)) {
            showError('Número do cartão inválido.');
            return false;
        }

        if (!cardNumber || cardNumber.length < 13 || cardNumber.length > 19) {
            showError('Número do cartão inválido.');
            return false;
        }

        if (!cvv || cvv.length < 3 || cvv.length > 4) {
            showError('CVV inválido.');
            return false;
        }

        if (!validateCardExpiry(expiry)) {
            showError('Data de validade inválida. Use o formato MM/AA.');
            return false;
        }

        return true;
    }

    function isValidLuhn(cardNumber) {
        let sum = 0;
        let isEven = false;

        for (let i = cardNumber.length - 1; i >= 0; i--) {
            let digit = parseInt(cardNumber[i]);

            if (isEven) {
                digit *= 2;
                if (digit > 9) digit -= 9;
            }

            sum += digit;
            isEven = !isEven;
        }

        return sum % 10 === 0;
    }

    /**
     * Detect card brand
     */
    function detectCardBrand(cardNumber) {
        if (!cardNumber) return 'Unknown';
        const cleaned = cardNumber.replace(/\D/g, '');

        // Padrões atualizados para bandeiras brasileiras
        const patterns = {
            'Visa': /^4/,
            'Master': /^(5[1-5]|222[1-9]|22[3-9]|2[3-6]|27[01]|2720)/,
            'Amex': /^3[47]/,
            'Elo': /^(4011|4312|4389|4514|4573|4576|5041|5067|5068|5090|5099|6277|6362|6363|6500|6504|6505|6509|6516|6550)/,
            'Hipercard': /^(384[0-9]{13}|606282[0-9]{10})/,
            'Hiper': /^(637[0-9]{13})/
        };

        for (const brand in patterns) {
            if (patterns[brand].test(cleaned)) {
                return brand;
            }
        }

        return 'Unknown';
    }

    /**
     * Copy text to clipboard
     */
    function copyToClipboard(elementId) {
        const element = document.getElementById(elementId);
        if (!element) {
            console.error(`Element with ID ${elementId} not found`);
            return;
        }

        const tempElement = document.createElement('textarea');
        tempElement.value = element.textContent;
        document.body.appendChild(tempElement);
        tempElement.select();

        try {
            document.execCommand('copy');
            showMessage('Código PIX copiado para a área de transferência!', 'success');
        } catch (err) {
            console.error('Erro ao copiar texto:', err);
            showMessage('Erro ao copiar o código PIX. Por favor, copie manualmente.', 'error');
        }

        document.body.removeChild(tempElement);
    }

    /**
     * Generate a random order number
     */
    function generateOrderNumber() {
        return 'ORD-' + Math.floor(Math.random() * 1000000).toString().padStart(6, '0');
    }

    /**
     * Add installment selector for credit card payments
     */
    function addCreditCardInstallmentSelector() {
        // Remover container existente
        const existingContainer = document.querySelector('.form-group#installment-container');
        if (existingContainer) existingContainer.remove();

        // Usar valor total atualizado
        const orderTotal = getTotal();
        if (!orderTotal || orderTotal <= 0) return; // Não mostrar se total for 0

        const installmentSelect = document.createElement('select');
        installmentSelect.className = 'form-control';
        installmentSelect.id = 'installment-select';
        installmentSelect.name = 'cc_installments'; // Corrigido para o backend

        const maxInstallments = 6; // Máximo 6x sem juros
        const minInstallmentValue = 10.00; // Valor mínimo por parcela

        let hasValidInstallment = false;
        for (let i = 1; i <= maxInstallments; i++) {
            const installmentAmount = orderTotal / i;
            if (installmentAmount < minInstallmentValue && i > 1) {
                break;
            }
            const option = document.createElement('option');
            option.value = i;
            option.textContent = `${i}x de R$${installmentAmount.toFixed(2)}${i === 1 ? ' (à vista)' : ' sem juros'}`;
            installmentSelect.appendChild(option);
            hasValidInstallment = true;
        }
        if (!hasValidInstallment) {
            // Se não houver opção válida, só permite à vista
            const option = document.createElement('option');
            option.value = 1;
            option.textContent = '1x de R$' + orderTotal.toFixed(2) + ' (à vista)';
            installmentSelect.appendChild(option);
        }

        const installmentContainer = document.createElement('div');
        installmentContainer.className = 'form-group';
        installmentContainer.id = 'installment-container';
        installmentContainer.innerHTML = '<label for="installment-select">Parcelas</label>';
        installmentContainer.appendChild(installmentSelect);

        // Inserir após os detalhes do cartão
        creditCardDetails.appendChild(installmentContainer);

        installmentSelect.addEventListener('change', () => {
            orderInfo.installments = parseInt(installmentSelect.value);
        });

        // Atualizar parcelas quando método mudar
        paymentMethods.forEach(method => {
            method.addEventListener('click', () => {
                if (method.dataset.method === 'debit_card') {
                    installmentSelect.value = '1';
                    installmentSelect.disabled = true;
                } else {
                    installmentSelect.disabled = false;
                }
            });
        });
    }

    /**
     * Initialize input masks for better user experience
     */
    function initializeInputMasks() {
        // Máscara de cartão com validação
        if (cardNumberInput) {
            $(cardNumberInput).mask('0000 0000 0000 0000', {
                onComplete: function () {
                    const brand = detectCardBrand(this.value);
                    const icon = document.querySelector('.card-type-icon i');
                    if (icon) {
                        icon.className = getBrandIconClass(brand);
                    }
                }
            });
        }
        if (cardExpiryInput) $(cardExpiryInput).mask('00/00');
        if (cardCvvInput) $(cardCvvInput).mask('0000');
        if (cepInput) $(cepInput).mask('00000-000');
        if (phoneInput) $(phoneInput).mask('(00) 00000-0000');
        const identificationNumberInput = document.getElementById('identification_number');
        if (identificationNumberInput) $(identificationNumberInput).mask('000.000.000-00');
    }

    /**
 * Obter classe de ícone para bandeira
 */
    function getBrandIconClass(brand) {
        const icons = {
            'Visa': 'fab fa-cc-visa',
            'Master': 'fab fa-cc-mastercard',
            'Amex': 'fab fa-cc-amex',
            'Elo': 'fab fa-cc-elo',
            'Diners': 'fab fa-cc-diners-club',
            'Discover': 'fab fa-cc-discover',
            'JCB': 'fab fa-cc-jcb',
            'Hipercard': 'fas fa-credit-card',
            'Hiper': 'fas fa-credit-card',
            'Aura': 'fas fa-credit-card'
        };
        return icons[brand] || 'fas fa-credit-card';
    }

    /**
     * Setup all event listeners
     */
    function setupEventListeners() {
        paymentMethods.forEach(method => {
            method.addEventListener('click', function () {
                selectPaymentMethod(this.dataset.method);
            });
        });

        if (btnFindCep) {
            btnFindCep.addEventListener('click', findAddressByCep);
        }

        if (cepInput) {
            cepInput.addEventListener('change', findAddressByCep);
        }

        if (savedCards) {
            savedCards.forEach(card => {
                card.addEventListener('click', function () {
                    if (this.classList.contains('new-card')) {
                        showNewCardForm();
                    } else {
                        selectSavedCard(this);
                    }
                });
            });
        }

        if (checkoutForm) {
            checkoutForm.addEventListener('submit', function (e) {
                console.log('[Checkout] Submit iniciado');
                if (!validateCheckoutForm()) {
                    console.log('[Checkout] Validação falhou');
                    e.preventDefault();
                    return false;
                }

                // Forçar atualização do campo shipping_cost antes do submit
                let shippingCostInput = document.getElementById('shipping_cost');
                if (!shippingCostInput) {
                    shippingCostInput = document.createElement('input');
                    shippingCostInput.type = 'hidden';
                    shippingCostInput.name = 'shipping_cost';
                    shippingCostInput.id = 'shipping_cost';
                    checkoutForm.appendChild(shippingCostInput);
                }
                shippingCostInput.value = shippingCostInput.value || 0; // Garante que o valor seja 0 se não existir
                console.log('[Checkout] shipping_cost forçado no submit:', shippingCostInput.value);

                processingOverlay.style.display = 'flex';

                if (selectedPaymentMethod === 'pix') {
                    console.log('[Checkout] Pagamento PIX selecionado, processando...');
                    e.preventDefault();
                    processPixPayment();
                    return false;
                } else if (selectedPaymentMethod === 'credit_card' || selectedPaymentMethod === 'debit_card') {
                    console.log('[Checkout] Pagamento cartão selecionado, processando...');
                    e.preventDefault();
                    processCreditCardPayment();
                    return false;
                }

                orderInfo.paymentMethod = selectedPaymentMethod;
                console.log('[Checkout] Submit normal permitido');
                return true;
            });
        }

        // Exemplo: Adicionar botões para consulta e cancelamento (opcional)
        const checkStatusBtn = document.getElementById('check-status-btn');
        if (checkStatusBtn) {
            checkStatusBtn.addEventListener('click', checkPaymentStatus);
        }

        const cancelBtn = document.getElementById('cancel-payment-btn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', cancelPayment);
        }

        const modalClose = document.querySelector('.modal-close');
        if (modalClose) {
            modalClose.addEventListener('click', function () {
                pixModal.style.display = 'none';
            });
        }

        const copyPixCode = document.getElementById('copy-pix-code');
        if (copyPixCode) {
            copyPixCode.addEventListener('click', function () {
                copyToClipboard('pix-code-text');
            });
        }

        const pixConfirmBtn = document.getElementById('pix-confirm-payment');
        if (pixConfirmBtn) {
            pixConfirmBtn.addEventListener('click', confirmPixPayment);
        }

        if (document.getElementById('cardholder_email')) {
            document.getElementById('cardholder_email').readOnly = true;
        }
    }

    async function checkPaymentStatus() {
        const merchantOrderId = orderInfo.orderNumber;

        try {
            const response = await fetch('includes/checkout/process_cielo_payment.php?action=check_status&merchantOrderId=' + merchantOrderId, {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' }
            });

            const data = await response.json();

            if (data.success) {
                showMessage(`Status do pagamento: ${data.statusDescription}`, 'info');
            } else {
                showError('Nenhuma transação encontrada para este pedido.');
            }
        } catch (error) {
            console.error('Erro ao consultar status:', error);
            showError('Erro ao consultar o status do pagamento.');
        }
    }

    async function cancelPayment() {
        const paymentId = orderInfo.paymentId || document.querySelector('input[name="payment_id"]')?.value;

        if (!paymentId) {
            showError('Nenhum pagamento encontrado para cancelar.');
            return;
        }

        try {
            const response = await fetch('includes/checkout/process_cielo_payment.php?action=cancel_payment&paymentId=' + paymentId, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });

            const data = await response.json();

            if (data.success) {
                showMessage('Pagamento cancelado com sucesso!', 'success');
            } else {
                showError('Erro ao cancelar o pagamento: ' + (data.message || 'Desconhecido'));
            }
        } catch (error) {
            console.error('Erro ao cancelar pagamento:', error);
            showError('Erro ao conectar ao servidor para cancelamento.');
        }
    }

    /**
     * Select payment method and update UI
     */
    function selectPaymentMethod(method) {
        paymentMethods.forEach(m => m.classList.remove('active'));
        document.querySelector(`.payment-method[data-method="${method}"]`).classList.add('active');

        paymentMethodInput.value = method;
        selectedPaymentMethod = method;
        orderInfo.paymentMethod = method;

        if (method === 'credit_card' || method === 'debit_card') {
            // Se débito e não há 3DS, mostrar erro e não permitir seleção
            if (method === 'debit_card' && !is3DSAvailable) {
                showError('Pagamento com cartão de débito não está disponível no momento. Utilize crédito ou PIX.');
                // Voltar para crédito automaticamente
                selectedPaymentMethod = 'credit_card';
                paymentMethodInput.value = 'credit_card';
                document.querySelector('.payment-method[data-method="credit_card"]').classList.add('active');
                document.querySelector('.payment-method[data-method="debit_card"]').classList.remove('active');
                return;
            }
            creditCardDetails.style.display = 'block';
            pixDetails.style.display = 'none';

            // Atualiza o texto do botão conforme o tipo
            const checkoutBtn = document.querySelector('.btn-checkout');
            if (method === 'debit_card') {
                checkoutBtn.textContent = 'Pagar com Débito';
            } else {
                checkoutBtn.textContent = 'Finalizar Compra';
            }
            // Ao trocar para crédito, se já houver CEP, busca frete automaticamente
            const cepInput = document.getElementById('shipping_cep');
            if (cepInput && cepInput.value && cepInput.value.replace(/\D/g, '').length === 8) {
                fetchFreteOptions(cepInput.value.replace(/\D/g, ''));
            }
        } else if (method === 'pix') {
            creditCardDetails.style.display = 'none';
            pixDetails.style.display = 'block';
            // Ao trocar para Pix, NÃO busca frete novamente, apenas mantém as opções já exibidas
        }
    }

    /**
     * Get the subtotal from the cart items
     */
    function getSubtotal() {
        let subtotal = 0;
        const cartItems = document.querySelectorAll('.cart-item');

        cartItems.forEach(item => {
            const priceElement = item.querySelector('.cart-item-price');
            const quantityElement = item.querySelector('.cart-item-quantity');

            if (priceElement && quantityElement) {
                const price = parseFloat(priceElement.textContent.replace('R$', '').replace(',', '.'));
                const quantity = parseInt(quantityElement.textContent.replace('Quantidade: ', ''));
                subtotal += price * quantity;
            }
        });

        return subtotal;
    }

    /**
     * Get discount value
     */
    function getDiscount() {
        // Busca a linha de desconto de primeira compra
        const rows = document.querySelectorAll('.price-row');
        let totalDiscount = 0;
        rows.forEach(row => {
            const label = row.querySelector('span:first-child');
            const value = row.querySelector('span:last-child');
            if (label && value && label.textContent.toLowerCase().includes('1ª compra')) {
                totalDiscount += parseFloat(value.textContent.replace('-R$', '').replace(',', '.'));
            }
            // Mantém outros descontos (ex: PIX)
            if (label && value && label.textContent.toLowerCase().includes('pix')) {
                totalDiscount += parseFloat(value.textContent.replace('-R$', '').replace(',', '.'));
            }
        });
        return totalDiscount;
    }

    /**
     * Find address information by CEP using API
     */
    function findAddressByCep() {
        const cep = cepInput.value.replace(/\D/g, '');

        if (cep.length !== 8) {
            showError('CEP inválido. Por favor, digite um CEP válido.');
            return;
        }

        btnFindCep.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btnFindCep.disabled = true;

        // Usar backend PHP para evitar problemas de CORS
        fetch(`includes/checkout/calcular-frete.php?action=buscar_cep&cep=${cep}`)
            .then(response => response.json())
            .then(data => {
                if (data.erro) {
                    showError(data.message || 'CEP não encontrado.');
                    return;
                }

                const addressInput = document.getElementById('shipping_address');
                if (addressInput) {
                    addressInput.value = `${data.logradouro}, ${data.bairro}, ${data.localidade}, ${data.uf}`;
                    const fullAddress = `${data.logradouro}, ${data.bairro}, ${data.localidade}, ${data.uf}, ${cep}`;
                    let fullAddressInput = document.getElementById('full_shipping_address');
                    if (!fullAddressInput) {
                        fullAddressInput = document.createElement('input');
                        fullAddressInput.type = 'hidden';
                        fullAddressInput.id = 'full_shipping_address';
                        fullAddressInput.name = 'full_shipping_address';
                        checkoutForm.appendChild(fullAddressInput);
                    }
                    fullAddressInput.value = fullAddress;

                    // Atualizar CEP de destino na tela
                    const cepDestinoSpan = document.querySelector('.frete-destino-bloco .frete-cep');
                    if (cepDestinoSpan) {
                        cepDestinoSpan.textContent = cep.replace(/(\d{5})(\d{3})/, '$1-$2');
                    }
                    // Atualizar endereço de destino na tela
                    const enderecoDestinoDiv = document.getElementById('frete-endereco-destino');
                    if (enderecoDestinoDiv) {
                        enderecoDestinoDiv.textContent = `${data.logradouro ? data.logradouro + ' - ' : ''}${data.bairro ? data.bairro + ' - ' : ''}${data.localidade}/${data.uf}`;
                    }
                    // Buscar opções de frete automaticamente
                    fetchFreteOptions(cep).catch(() => {});
                }

                btnFindCep.innerHTML = 'Buscar';
                btnFindCep.disabled = false;
            })
            .catch(error => {
                btnFindCep.innerHTML = 'Buscar';
                btnFindCep.disabled = false;
            });
    }

    /**
     * Show new card form and hide saved cards
     */
    function showNewCardForm() {
        const savedCardsContainer = document.querySelector('.saved-cards');
        if (savedCardsContainer) savedCardsContainer.style.display = 'none';
        if (newCardForm) newCardForm.style.display = 'block';
        initializeCardInputs(); // Reinitialize card input for brand detection
    }

    /**
     * Select a saved card
     */
    function selectSavedCard(cardElement) {
        savedCards.forEach(card => card.classList.remove('active'));
        cardElement.classList.add('active');
        if (newCardForm) newCardForm.style.display = 'none';
        const cardId = cardElement.dataset.cardId;
        let cardIdInput = document.getElementById('saved_card_id');
        if (!cardIdInput) {
            cardIdInput = document.createElement('input');
            cardIdInput.type = 'hidden';
            cardIdInput.id = 'saved_card_id';
            cardIdInput.name = 'saved_card_id';
            checkoutForm.appendChild(cardIdInput);
        }
        cardIdInput.value = cardId;
    }

    /**
     * Validate checkout form before submission
     */
    function validateCheckoutForm() {
        if (!selectedPaymentMethod) {
            showError('Por favor, selecione um método de pagamento.');
            return false;
        }

        const shippingAddress = document.getElementById('shipping_address');
        const shippingNumber = document.getElementById('shipping_number');
        const shippingCep = document.getElementById('shipping_cep');

        if (!shippingAddress || !shippingAddress.value.trim()) {
            showError('Por favor, informe o endereço de entrega.');
            return false;
        }

        if (!shippingNumber || !shippingNumber.value.trim()) {
            showError('Por favor, informe o número do endereço.');
            return false;
        }

        if (!shippingCep || !shippingCep.value.trim() || shippingCep.value.replace(/\D/g, '').length !== 8) {
            showError('Por favor, informe um CEP válido.');
            return false;
        }

        if (selectedPaymentMethod === 'credit_card' || selectedPaymentMethod === 'debit_card') {
            const savedCardId = document.getElementById('saved_card_id');
            if (!savedCardId || !savedCardId.value) {
                if (!validateCardInputs()) {
                    return false;
                }
            } else {
                if (!cardCvvInput || !cardCvvInput.value.trim() || cardCvvInput.value.length < 3) {
                    showError('Por favor, informe o código de segurança do cartão salvo.');
                    return false;
                }
            }

            const cardholderEmail = document.getElementById('cardholder_email');
            if (!cardholderEmail || !cardholderEmail.value.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(cardholderEmail.value)) {
                showError('Por favor, informe um e-mail válido.');
                return false;
            }

            const identificationNumber = document.getElementById('identification_number');
            if (!identificationNumber || !identificationNumber.value.trim() || identificationNumber.value.replace(/\D/g, '').length !== 11) {
                showError('Por favor, informe um CPF válido.');
                return false;
            }
        }

        return true;
    }

    /**
     * Validate credit card expiry date
     */
    function validateCardExpiry(expiry) {
        const [month, year] = expiry.split('/').map(Number);
        if (!month || !year || isNaN(month) || isNaN(year)) return false;

        const currentYear = new Date().getFullYear() % 100;
        const currentMonth = new Date().getMonth() + 1;

        return (
            month >= 1 && month <= 12 &&
            year >= currentYear &&
            (year > currentYear || month >= currentMonth)
        );
    }

    /**
     * Process PIX payment
     */
    function processPixPayment() {
        const pixData = { amount: getTotal(), orderId: generateTempOrderId() };
        orderInfo.paymentMethod = 'pix';
        orderInfo.installments = 1;

        setTimeout(() => {
            processingOverlay.style.display = 'none';
            pixModal.style.display = 'flex';
            const pixPayload = generatePixPayload(pixData.amount, orderInfo);
            const encodedPixPayload = encodeURIComponent(pixPayload);
            const pixCodeText = document.getElementById('pix-code-text');
            if (pixCodeText) {
                pixCodeText.textContent = pixPayload;
            }

            const qrContainer = document.querySelector('.pix-qrcode');
            if (qrContainer) {
                qrContainer.innerHTML = '';
                const qrImage = document.createElement('img');
                qrImage.id = 'pix-qrcode-img';
                qrImage.style.width = '100%';
                qrImage.style.height = '100%';
                qrImage.style.maxWidth = '200px';
                qrImage.style.zIndex = '100';
                qrImage.src = `https://api.qrserver.com/v1/create-qr-code/?data=${encodedPixPayload}&size=200x200`;
                qrImage.alt = 'QR Code PIX';
                qrContainer.appendChild(qrImage);
            }
        }, 1500);
    }

    /**
     * Update shipping cost display on the page
     */
    function updateShippingCost(cost) {
        if (cost === 0) {
            console.warn('updateShippingCost(0) chamado! Stack:', new Error().stack);
        }
        const shippingCostElement = document.querySelector('.shipping-cost');
        if (shippingCostElement) {
            if (cost === null || cost === undefined || isNaN(cost)) {
                shippingCostElement.textContent = 'Calculando...';
            } else {
                shippingCostElement.textContent = cost.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
            }
        } else {
            const cartTotalElement = document.querySelector('.price-total');
            if (cartTotalElement) {
                const shippingElement = document.createElement('div');
                shippingElement.className = 'price-row shipping-row';
                shippingElement.innerHTML = `
                    <span>Frete:</span>
                    <span class="shipping-cost">${(cost === null || cost === undefined || isNaN(cost)) ? 'Calculando...' : cost.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}</span>
                `;
                cartTotalElement.parentNode.insertBefore(shippingElement, cartTotalElement);
            }
        }
        let shippingCostInput = document.getElementById('shipping_cost');
        if (!shippingCostInput) {
            shippingCostInput = document.createElement('input');
            shippingCostInput.type = 'hidden';
            shippingCostInput.id = 'shipping_cost';
            shippingCostInput.name = 'shipping_cost';
            // SEMPRE append dentro do formulário!
            checkoutForm.appendChild(shippingCostInput);
        }
        shippingCostInput.value = (cost === null || cost === undefined || isNaN(cost)) ? '' : cost.toFixed(2);
        console.log('[Checkout] shipping_cost atualizado: ' + shippingCostInput.value);
        // Sempre recalcula o total e o Pix preview
        updateTotalWithShipping(cost);
        showPixPreview();
        addCreditCardInstallmentSelector();
    }

    /**
     * Update total price including shipping cost
     */
    function updateTotalWithShipping(shippingCost) {
        const subtotal = getSubtotal();
        let discountPix = 0;
        if (selectedPaymentMethod === 'pix') {
            discountPix = (subtotal * 0.05);
        }
        const discount = (selectedPaymentMethod === 'pix' ? discountPix : 0);
        let total;
        if (shippingCost === null || shippingCost === undefined || isNaN(shippingCost)) {
            total = subtotal - discount;
        } else {
            total = subtotal + shippingCost - discount;
        }

        const shippingCostElement = document.querySelector('.shipping-cost');
        if (shippingCostElement) {
            shippingCostElement.textContent = (shippingCost === null || shippingCost === undefined || isNaN(shippingCost)) ? 'Calculando...' : `R$ ${shippingCost.toFixed(2).replace('.', ',')}`;
        }

        const totalElement = document.querySelector('.price-total span:last-child');
        if (totalElement) {
            totalElement.textContent = `R$${total.toFixed(2).replace('.', ',')}`;
        }

        let shippingCostInput = document.getElementById('shipping_cost');
        if (!shippingCostInput) {
            shippingCostInput = document.createElement('input');
            shippingCostInput.type = 'hidden';
            shippingCostInput.id = 'shipping_cost';
            shippingCostInput.name = 'shipping_cost';
            checkoutForm.appendChild(shippingCostInput);
        }
        shippingCostInput.value = (shippingCost === null || shippingCost === undefined || isNaN(shippingCost)) ? '' : shippingCost;
        orderInfo.orderTotal = total;
        updateInstallmentOptions();
        // Atualiza Pix preview e desconto Pix
        updatePixDiscount();
    }

    /**
     * Update the installment options when the total price changes
     */
    function updateInstallmentOptions() {
        const total = getTotal();
        const ccInstallments = document.getElementById('cc_installments');
        if (ccInstallments) {
            const selectedValue = ccInstallments.value;
            let options = '';
            for (let i = 1; i <= 6; i++) {
                const installmentAmount = (total / i).toFixed(2).replace('.', ',');
                options += `<option value="${i}" ${i == selectedValue ? 'selected' : ''}>${i}x de R$${installmentAmount} sem juros</option>`;
            }
            ccInstallments.innerHTML = options;
        }
    }

    /**
     * Generate PIX payload
     */
    function generatePixPayload(value, orderInfo) {
        const pixKey = "cristaisgoldlar@outlook.com";
        const merchantName = "GOLDLAR CRISTAIS";
        const merchantCity = "SUZANO";
        const valueFormatted = value.toFixed(2);
        const transactionId = orderInfo.orderNumber.replace(/\D/g, '').padStart(10, '0').substring(0, 10);

        const payload = {
            '00': '01',
            '26': {
                '00': 'BR.GOV.BCB.PIX',
                '01': pixKey
            },
            '52': '0000',
            '53': '986',
            '54': valueFormatted,
            '58': 'BR',
            '59': merchantName,
            '60': merchantCity,
            '62': {
                '05': transactionId
            }
        };

        let pixCode = '';
        for (const [id, value] of Object.entries(payload)) {
            if (typeof value === 'object') {
                let subPayload = '';
                for (const [subId, subValue] of Object.entries(value)) {
                    subPayload += `${subId}${subValue.length.toString().padStart(2, '0')}${subValue}`;
                }
                pixCode += `${id}${subPayload.length.toString().padStart(2, '0')}${subPayload}`;
            } else {
                pixCode += `${id}${value.length.toString().padStart(2, '0')}${value}`;
            }
        }

        pixCode += '6304';
        const crc = calculateCRC16(pixCode);
        pixCode += crc;
        return pixCode;
    }

    /**
     * Calculate CRC16 for PIX payload
     */
    function calculateCRC16(payload) {
        const polynomial = 0x1021;
        let crc = 0xFFFF;
        const bytes = [];
        for (let i = 0; i < payload.length; i++) {
            bytes.push(payload.charCodeAt(i));
        }

        for (const byte of bytes) {
            crc ^= (byte << 8);
            for (let i = 0; i < 8; i++) {
                if ((crc & 0x8000) !== 0) {
                    crc = ((crc << 1) ^ polynomial) & 0xFFFF;
                } else {
                    crc = (crc << 1) & 0xFFFF;
                }
            }
        }
        return crc.toString(16).toUpperCase().padStart(4, '0');
    }

    /**
     * Confirm PIX payment
     */
    function confirmPixPayment() {
        processingOverlay.style.display = 'flex';
        setTimeout(() => {
            if (window.pixCountdownInterval) {
                clearInterval(window.pixCountdownInterval);
            }
            pixModal.style.display = 'none';
            const hiddenPaymentInfo = document.createElement('input');
            hiddenPaymentInfo.type = 'hidden';
            hiddenPaymentInfo.name = 'pix_payment_id';
            hiddenPaymentInfo.value = 'PIX_' + Date.now();
            checkoutForm.appendChild(hiddenPaymentInfo);
            console.log('[Checkout] Confirmando pagamento PIX e submetindo formulário');
            try {
            checkoutForm.submit();
                setTimeout(() => {
                    if (!window.__checkout_submitted) {
                        console.error('[Checkout] Fallback: submit forçado após PIX');
                        checkoutForm.submit();
                    }
                }, 2000);
            } catch (e) {
                console.error('[Checkout] Erro ao submeter formulário PIX:', e);
                checkoutForm.submit();
            }
        }, 2000);
    }

    /**
     * Show error message
     */
    function showError(message) {
        showSiteAlert(message, 'error');
    }

    /**
     * Show message to user
     */
    function showMessage(text, type = 'success') {
        showSiteAlert(text, type);
    }

    /**
     * Calculate total order value
     */
    function getTotal() {
        const subtotal = getSubtotal();
        const shipping = getShippingCost(); // getShippingCost agora retorna null se não houver valor
        const discount = getDiscount();
        let total;
        if (shipping === null || shipping === undefined || isNaN(shipping)) {
            total = subtotal - discount;
        } else {
            total = subtotal + shipping - discount;
        }
        return total;
    }

    /**
     * Update all price totals in real-time
     */
    function updateAllPrices() {
        const subtotal = getSubtotal();
        const shipping = getShippingCost(); // getShippingCost agora retorna null se não houver valor
        let discountPix = 0;
        if (selectedPaymentMethod === 'pix') {
            discountPix = (subtotal * 0.05);
        }
        const discount = (selectedPaymentMethod === 'pix' ? discountPix : 0);
        let total;
        if (shipping === null || shipping === undefined || isNaN(shipping)) {
            total = subtotal - discount;
        } else {
            total = subtotal + shipping - discount;
        }

        const subtotalElement = document.querySelector('.price-row:first-child span:last-child');
        if (subtotalElement) {
            subtotalElement.textContent = `R$${subtotal.toFixed(2).replace('.', ',')}`;
        }

        const totalElement = document.querySelector('.price-total span:last-child');
        if (totalElement) {
            totalElement.textContent = `R$${total.toFixed(2).replace('.', ',')}`;
        }

        addCreditCardInstallmentSelector(); // Atualiza o seletor de parcelas sempre que o total mudar
        // Atualiza Pix preview e desconto Pix
        updatePixDiscount();
    }

    /**
     * Generate temporary order ID for PIX
     */
    function generateTempOrderId() {
        return 'TEMP_' + Date.now();
    }

    /**
     * Update checkout progress
     */
    function updateProgress(step) {
        const steps = document.querySelectorAll('.progress-step');
        steps.forEach((stepElement, index) => {
            const circle = stepElement.querySelector('.step-circle');
            const line = stepElement.querySelector('.step-line');
            if (index < step) {
                circle.classList.add('completed');
                circle.classList.remove('active');
                if (line) line.classList.add('active');
            } else if (index === step) {
                circle.classList.add('active');
                circle.classList.remove('completed');
                if (line) line.classList.remove('active');
            } else {
                circle.classList.remove('active', 'completed');
                if (line) line.classList.remove('active');
            }
        });
    }

    // Atualizar desconto Pix e resumo do pedido corretamente
    function updatePixDiscount() {
        const subtotal = getSubtotal();
        const shipping = getShippingCost(); // getShippingCost agora retorna null se não houver valor
        let discountPix = 0;
        // Se PIX selecionado, aplicar desconto de 5%
        if (selectedPaymentMethod === 'pix') {
            discountPix = (subtotal * 0.05);
            // Adiciona/atualiza linha do desconto PIX
            let pixRow = document.querySelector('.price-row.pix-discount');
            if (!pixRow) {
                pixRow = document.createElement('div');
                pixRow.className = 'price-row pix-discount';
                pixRow.innerHTML = '<span>Desconto PIX (5%)</span><span>-R$0,00</span>';
                const totalRow = document.querySelector('.price-row.price-total');
                totalRow.parentNode.insertBefore(pixRow, totalRow);
            }
            pixRow.querySelector('span:last-child').textContent = `-R$${discountPix.toFixed(2).replace('.', ',')}`;
        } else {
            // Remove desconto PIX se não for PIX
            const pixRow = document.querySelector('.price-row.pix-discount');
            if (pixRow) pixRow.remove();
        }
        // Atualiza total
        const discount = (selectedPaymentMethod === 'pix' ? discountPix : 0);
        let total;
        if (shipping === null || shipping === undefined || isNaN(shipping)) {
            total = subtotal - discount;
        } else {
            total = subtotal + shipping - discount;
        }
        const totalElement = document.querySelector('.price-total span:last-child');
        if (totalElement) {
            totalElement.textContent = `R$${total.toFixed(2).replace('.', ',')}`;
        }
        // Atualiza Pix preview
        showPixPreview(total);
    }

    // Corrigir showPixPreview para só mostrar quando Pix estiver selecionado e valor igual ao total
    function showPixPreview(totalPix) {
        let prev = document.getElementById('pix-preview');
        if (prev) prev.remove();
        if (selectedPaymentMethod !== 'pix') return;
        const container = document.querySelector('.cart-summary .price-details');
        if (container) {
            const div = document.createElement('div');
            div.id = 'pix-preview';
            div.className = 'pix-preview-row';
            div.innerHTML = `<span><i class='fas fa-qrcode'></i> <b>R$${totalPix.toFixed(2).replace('.', ',')}</b> no Pix</span> <span style='color:#888;font-size:0.95em;'>(5% OFF)</span>`;
            container.appendChild(div);
        }
    }

    // Atualizar total com frete SEMPRE usando o desconto calculado
    function updateTotalWithShipping(shippingCost) {
        const subtotal = getSubtotal();
        let discountPix = 0;
        if (selectedPaymentMethod === 'pix') {
            discountPix = (subtotal * 0.05);
        }
        const discount = (selectedPaymentMethod === 'pix' ? discountPix : 0);
        let total;
        if (shippingCost === null || shippingCost === undefined || isNaN(shippingCost)) {
            total = subtotal - discount;
        } else {
            total = subtotal + shippingCost - discount;
        }

        const shippingCostElement = document.querySelector('.shipping-cost');
        if (shippingCostElement) {
            shippingCostElement.textContent = (shippingCost === null || shippingCost === undefined || isNaN(shippingCost)) ? 'Calculando...' : `R$ ${shippingCost.toFixed(2).replace('.', ',')}`;
        }

        const totalElement = document.querySelector('.price-total span:last-child');
        if (totalElement) {
            totalElement.textContent = `R$${total.toFixed(2).replace('.', ',')}`;
        }

        let shippingCostInput = document.getElementById('shipping_cost');
        if (!shippingCostInput) {
            shippingCostInput = document.createElement('input');
            shippingCostInput.type = 'hidden';
            shippingCostInput.id = 'shipping_cost';
            shippingCostInput.name = 'shipping_cost';
            checkoutForm.appendChild(shippingCostInput);
        }
        shippingCostInput.value = (shippingCost === null || shippingCost === undefined || isNaN(shippingCost)) ? '' : shippingCost;
        orderInfo.orderTotal = total;
        updateInstallmentOptions();
        // Atualiza Pix preview e desconto Pix
        updatePixDiscount();
    }

    // Atualizar todos os preços SEMPRE usando o desconto calculado
    function updateAllPrices() {
        const subtotal = getSubtotal();
        const shipping = getShippingCost(); // getShippingCost agora retorna null se não houver valor
        let discountPix = 0;
        if (selectedPaymentMethod === 'pix') {
            discountPix = (subtotal * 0.05);
        }
        const discount = (selectedPaymentMethod === 'pix' ? discountPix : 0);
        let total;
        if (shipping === null || shipping === undefined || isNaN(shipping)) {
            total = subtotal - discount;
        } else {
            total = subtotal + shipping - discount;
        }

        const subtotalElement = document.querySelector('.price-row:first-child span:last-child');
        if (subtotalElement) {
            subtotalElement.textContent = `R$${subtotal.toFixed(2).replace('.', ',')}`;
        }

        const totalElement = document.querySelector('.price-total span:last-child');
        if (totalElement) {
            totalElement.textContent = `R$${total.toFixed(2).replace('.', ',')}`;
        }

        addCreditCardInstallmentSelector(); // Atualiza o seletor de parcelas sempre que o total mudar
        // Atualiza Pix preview e desconto Pix
        updatePixDiscount();
    }

    // Chamar updatePixDiscount ao selecionar método de pagamento
    paymentMethods.forEach(method => {
        method.addEventListener('click', function () {
            setTimeout(updatePixDiscount, 100); // Pequeno delay para garantir DOM atualizado
        });
    });

    // Initialize page
    if (paymentMethods.length > 0) {
        selectPaymentMethod(paymentMethods[0].dataset.method);
    }
    updateProgress(1);

    const shippingInfo = document.querySelector('.shipping-info');
    if (shippingInfo) {
        shippingInfo.textContent = 'Frete grátis para SP, RJ, SC, RS, PR e GO em compras acima de R$399,99';
    }
    // Buscar frete automaticamente se CEP já estiver preenchido ao carregar
    const cepInputAuto = document.getElementById('shipping_cep');
    // btnFindCep já foi declarado no início do arquivo
    if (cepInputAuto && cepInputAuto.value && cepInputAuto.value.replace(/\D/g, '').length === 8) {
        fetchFreteOptions(cepInputAuto.value.replace(/\D/g, ''));
    }

    // Bloquear botão Buscar CEP se modo Pix estiver selecionado
    function updateBtnFindCepState() {
        if (!btnFindCep) return;
        if (selectedPaymentMethod === 'pix') {
            btnFindCep.disabled = true;
            btnFindCep.title = 'Para alterar o CEP, mude para cartão de crédito.';
            if (!btnFindCep.dataset.originalText) {
                btnFindCep.dataset.originalText = btnFindCep.textContent;
            }
            btnFindCep.textContent = 'Mude para cartão de crédito para liberar esta função';
        } else {
            btnFindCep.disabled = false;
            btnFindCep.title = '';
            if (btnFindCep.dataset.originalText) {
                btnFindCep.textContent = btnFindCep.dataset.originalText;
                delete btnFindCep.dataset.originalText;
            }
        }
    }
    // Atualizar estado do botão ao trocar método
    if (btnFindCep) {
        paymentMethods.forEach(method => {
            method.addEventListener('click', updateBtnFindCepState);
        });
        updateBtnFindCepState();
    }

    // Ao digitar novo CEP, reabilitar botão Buscar CEP mesmo no Pix
    if (cepInputAuto && btnFindCep) {
        cepInputAuto.addEventListener('input', function() {
            if (selectedPaymentMethod === 'pix') {
                btnFindCep.disabled = false;
                btnFindCep.title = 'Clique para buscar o novo CEP (o modo de pagamento será alterado para cartão)';
            }
        });
        btnFindCep.addEventListener('click', function(e) {
            if (selectedPaymentMethod === 'pix') {
                e.preventDefault();
                // Troca para cartão de crédito e só então executa a busca
                let buscou = false;
                const doBusca = () => {
                    if (!buscou) {
                        buscou = true;
                        if (typeof findAddressByCep === 'function') {
                            findAddressByCep();
                        } else if (typeof window.findAddressByCep === 'function') {
                            window.findAddressByCep();
                        }
                    }
                };
                // Troca visual e lógica
                selectPaymentMethod('credit_card');
                // Aguarda DOM e variáveis
                setTimeout(doBusca, 350);
                return false;
            }
            // Se não for Pix, segue fluxo normal
        });
    }
    /**
     * Buscar e exibir opções de frete da SuperFrete (com loading e tratamento de erro detalhado)
     */
    async function fetchFreteOptions(cepDestino) {
        // Sempre buscar opções de frete da SuperFrete, independente do estado
        let caixa = 'P';
        if (window.carrinhoCaixas && Array.isArray(window.carrinhoCaixas)) {
            if (window.carrinhoCaixas.includes('G')) {
                caixa = 'G';
            } else if (window.carrinhoCaixas.includes('M')) {
                caixa = 'M';
            } else if (window.carrinhoCaixas.includes('P')) {
                caixa = 'P';
            }
        }
        let dimensoes = {};
        if (caixa === 'G') {
            dimensoes = { comprimento: 40, largura: 40, altura: 35, peso: 2 };
        } else if (caixa === 'M') {
            dimensoes = { comprimento: 37, largura: 37, altura: 35, peso: 2 };
        } else {
            dimensoes = { comprimento: 22, largura: 22, altura: 35, peso: 2 };
        }
        let container = document.getElementById('frete-options-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'frete-options-container';
            container.className = 'frete-options-container';
            const shippingRow = document.querySelector('.price-row.shipping-row') || document.querySelector('.price-row');
            if (shippingRow) {
                shippingRow.parentNode.insertBefore(container, shippingRow.nextSibling);
            } else {
                document.querySelector('.cart-summary').appendChild(container);
            }
        }
        container.innerHTML = '<div class="frete-loading">Buscando opções de frete...</div>';
        try {
            const subtotal = getSubtotal();
            // Buscar UF do destino via backend
            let ufDestino = null;
            try {
                const cepInfoResp = await fetch(`includes/checkout/calcular-frete.php?action=buscar_cep&cep=${cepDestino}`);
                const cepInfo = await cepInfoResp.json();
                ufDestino = cepInfo.uf;
            } catch (e) {
                ufDestino = null;
            }
            const ufsFreteGratis = ['SP', 'RJ', 'SC', 'RS', 'PR', 'GO'];
            const payload = {
                from: { postal_code: '08690265' },
                to: { postal_code: cepDestino },
                services: "1,2,17",
                options: {
                    own_hand: false,
                    receipt: false,
                    insurance_value: 0,
                    use_insurance_value: false
                },
                package: {
                    weight: dimensoes.peso,
                    width: dimensoes.largura,
                    height: dimensoes.altura,
                    length: dimensoes.comprimento
                },
                value: subtotal
            };
            const response = await fetch('includes/checkout/calcular-frete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            if (!response.ok) {
                container.innerHTML = `<div class='frete-error'>Erro ao buscar opções de frete.</div>`;
                return;
            }
            let data = await response.json();
            let options = Array.isArray(data) ? data : data.options;
            // Adicionar opção de frete grátis se UF e valor atenderem
            if (ufDestino && ufsFreteGratis.includes(ufDestino) && subtotal >= 399.99) {
                options = [
                    {
                        id: 'gratis',
                        name: 'FRETE GRÁTIS',
                        price: 0,
                        delivery_time: 7,
                        company: { name: 'Frete Grátis', picture: '' }
                    },
                    ...options
                ];
            }
            showFreteOptions(options);
        } catch (e) {
            container.innerHTML = `<div class='frete-error'>Erro ao buscar opções de frete.</div>`;
        }
    }
    /**
     * Exibir opções de frete na tela e permitir seleção (visual aprimorado)
     */
    function showFreteOptions(options) {
        // Se houver frete grátis, mostrar apenas ela
        const temFreteGratis = options.some(opt => (opt.id === 'gratis' || opt.name === 'FRETE GRÁTIS') && opt.price == 0);
        if (temFreteGratis) {
            options = options.filter(opt => (opt.id === 'gratis' || opt.name === 'FRETE GRÁTIS'));
        }
        let container = document.getElementById('frete-options-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'frete-options-container';
            container.className = 'frete-options-container';
            const shippingRow = document.querySelector('.price-row.shipping-row') || document.querySelector('.price-row');
            if (shippingRow) {
                shippingRow.parentNode.insertBefore(container, shippingRow.nextSibling);
            } else {
                document.querySelector('.cart-summary').appendChild(container);
            }
        }
        if (!options || options.length === 0) {
            container.innerHTML = `<div class='frete-error'>Não foi possível calcular o frete. Tente novamente ou verifique o CEP.</div>`;
            let shippingCostInput = document.getElementById('shipping_cost');
            if (shippingCostInput) shippingCostInput.value = '';
            const shippingCostElement = document.querySelector('.shipping-cost');
            if (shippingCostElement) shippingCostElement.textContent = 'Calculando...';
            return;
        }
        // Limpar logs e bordas de debug
        container.innerHTML = '<strong>Poste na agência <i class="fas fa-info-circle" title="Qualquer agência dos Correios"></i></strong><div class="frete-cards"></div>';
        const cardsDiv = container.querySelector('.frete-cards');
        cardsDiv.style.display = 'flex';
        cardsDiv.style.flexDirection = 'column';
        cardsDiv.style.gap = '6px';
        let createdCards = 0;
        // Calcular menor preço (ignorando frete grátis)
        let menorPreco = Math.min(...options.filter(opt => parseFloat(opt.price) > 0).map(opt => parseFloat(opt.price)));
        
        // Verificar se Jadlog é a opção mais barata
        const jadlogOption = options.find(opt => opt.id === 'jadlog' || opt.name.includes('JADLOG'));
        const isJadlogCheapest = jadlogOption && parseFloat(jadlogOption.price) === menorPreco;
        options.forEach((opt, idx) => {
            const card = document.createElement('label');
            card.className = 'frete-card';
            card.style.display = 'flex';
            card.style.alignItems = 'center';
            card.style.justifyContent = 'flex-start';
            card.style.padding = '6px 8px';
            card.style.border = '1px solid #eee';
            card.style.borderRadius = '7px';
            card.style.cursor = 'pointer';
            card.style.transition = 'border 0.2s, background 0.2s';
            card.style.position = 'relative';
            // Radio button
            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = 'frete_option';
            radio.value = opt.id || opt.name || 'Servico';
            radio.style.marginRight = '10px';
            radio.checked = idx === 0;
            radio.style.width = '15px';
            radio.style.height = '15px';
            // Logo + nome serviço
            const logoNomeDiv = document.createElement('div');
            logoNomeDiv.style.display = 'flex';
            logoNomeDiv.style.alignItems = 'center';
            logoNomeDiv.style.gap = '6px';            
            // Nome serviço
            const serviceName = document.createElement('span');
            serviceName.textContent = opt.name || opt.service || 'Serviço';
            serviceName.style.fontWeight = '500';
            serviceName.style.fontSize = '1em';
            // Logo - não exibir para frete grátis
            const logo = document.createElement('img');
            if (opt.id === 'gratis' || opt.name === 'FRETE GRÁTIS') {
                logo.style.display = 'none';
            } else if (opt.id === 'jadlog' || opt.name.includes('JADLOG')) {
                logo.src = '/public_html/Site/images/jadlog-logo.svg';
                logo.alt = 'Jadlog';
                logo.style.width = '78px';
                logo.style.height = '18px';
                logo.style.objectFit = 'contain';
                logo.style.marginLeft = '8px';
            } else {
                logo.src = (opt.company && opt.company.picture) ? opt.company.picture : '';
                logo.alt = (opt.company && opt.company.name) ? opt.company.name : 'Transportadora';
                logo.style.width = '78px';
                logo.style.height = '18px';
                logo.style.objectFit = 'contain';
                logo.style.marginLeft = '8px';
            }
            logoNomeDiv.appendChild(serviceName);
            if (!(opt.id === 'gratis' || opt.name === 'FRETE GRÁTIS')) {
                logoNomeDiv.appendChild(logo);
            }
            // Prazo
            const prazo = document.createElement('div');
            prazo.textContent = `Até ${opt.delivery_time || (opt.delivery_range && opt.delivery_range.min) || 0} dia útil${((opt.delivery_time || (opt.delivery_range && opt.delivery_range.min)) > 1) ? 's' : ''}`;
            prazo.style.fontSize = '0.93em';
            prazo.style.color = '#888';
            prazo.style.marginTop = '2px';
            // Preço
            const preco = document.createElement('div');
            preco.textContent = opt.price == 0 ? 'Grátis' : `R$ ${parseFloat(opt.price).toFixed(2)}`;
            preco.style.fontWeight = 'bold';
            preco.style.fontSize = '1em';
            preco.style.color = opt.price == 0 ? '#1976d2' : '#2e7d32';
            preco.style.marginLeft = 'auto';
            preco.style.display = 'flex';
            preco.style.alignItems = 'center';
            preco.style.flexDirection = 'column';
            preco.style.justifyContent = 'center';
            preco.style.textAlign = 'center';
            // Estrutura vertical para nome+prazo
            const infoDiv = document.createElement('div');
            infoDiv.style.display = 'flex';
            infoDiv.style.flexDirection = 'column';
            infoDiv.style.alignItems = 'flex-start';
            infoDiv.appendChild(logoNomeDiv);
            infoDiv.appendChild(prazo);
            // Montar estrutura
            card.appendChild(radio);
            card.appendChild(infoDiv);
            card.appendChild(preco);
            // Fundo verde apenas para o menor preço (ignorando grátis)
            if (opt.price > 0 && parseFloat(opt.price) === menorPreco) {
                // Se for Jadlog e for o mais barato, destacar com cor diferente
                if (opt.id === 'jadlog' || opt.name.includes('JADLOG')) {
                    card.style.background = '#e8f5ff';
                    card.style.borderColor = '#0277bd';
                    // Selo especial para Jadlog
                    const selo = document.createElement('span');
                    selo.textContent = 'Melhor opção ★';
                    selo.className = 'jadlog-melhor-preco-selo';
                    selo.style.background = '#90caf9';
                    selo.style.color = '#01579b';
                    selo.style.fontWeight = 'bold';
                    selo.style.fontSize = '0.85em';
                    selo.style.padding = '2px 10px';
                    selo.style.borderRadius = '10px';
                    selo.style.marginBottom = '7px';
                    selo.style.display = 'inline-flex';
                    selo.style.alignItems = 'center';
                    selo.style.justifyContent = 'center';
                    preco.prepend(selo);
                } else {
                    card.style.background = '#eaffea';
                    card.style.borderColor = '#4e8d7c';
                    // Selo padrão para melhor preço
                    const selo = document.createElement('span');
                    selo.textContent = 'Melhor preço';
                    selo.className = 'melhor-preco-selo';
                    selo.style.background = '#b6f2c5';
                    selo.style.color = '#1b5e20';
                    selo.style.fontWeight = 'bold';
                    selo.style.fontSize = '0.85em';
                    selo.style.padding = '2px 10px';
                    selo.style.borderRadius = '10px';
                    selo.style.marginBottom = '7px';
                    selo.style.display = 'inline-flex';
                    selo.style.alignItems = 'center';
                    selo.style.justifyContent = 'center';
                    preco.prepend(selo);
                }
            }
            // Destaque azul para frete grátis
            if (opt.price == 0) {
                card.style.background = '#e3f2fd';
                card.style.borderColor = '#1976d2';
                const seloGratis = document.createElement('span');
                seloGratis.textContent = 'Frete Grátis';
                seloGratis.className = 'frete-gratis-selo';
                seloGratis.style.background = '#bbdefb';
                seloGratis.style.color = '#1976d2';
                seloGratis.style.fontWeight = 'bold';
                seloGratis.style.fontSize = '0.85em';
                seloGratis.style.padding = '2px 10px';
                seloGratis.style.borderRadius = '10px';
                seloGratis.style.marginBottom = '7px';
                seloGratis.style.display = 'inline-flex';
                seloGratis.style.alignItems = 'center';
                seloGratis.style.justifyContent = 'center';
                preco.prepend(seloGratis);
            }
            // Destaque para Jadlog (independente do preço)
            else if (opt.id === 'jadlog' || opt.name.includes('JADLOG')) {
                card.classList.add('jadlog-option');
                
                // Se for a opção mais barata, adicionar selo especial
                if (parseFloat(opt.price) === menorPreco) {
                    const seloJadlog = document.createElement('span');
                    seloJadlog.textContent = 'Melhor opção ★';
                    seloJadlog.className = 'jadlog-selo';
                    preco.prepend(seloJadlog);
                } 
                // Se não for a mais barata, ainda destacar com estrela no nome
                else {
                    if (!opt.name.includes('★')) {
                        serviceName.textContent += ' ★';
                    }
                }
            }
            cardsDiv.appendChild(card);
            // Evento para atualizar o frete ao selecionar
            card.addEventListener('click', () => {
                document.querySelectorAll('.frete-card').forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
                radio.checked = true;
                updateShippingCost(parseFloat(opt.price));
                let serviceInput = document.getElementById('superfrete_service_id');
                if (!serviceInput) {
                    serviceInput = document.createElement('input');
                    serviceInput.type = 'hidden';
                    serviceInput.id = 'superfrete_service_id';
                    serviceInput.name = 'superfrete_service_id';
                    checkoutForm.appendChild(serviceInput);
                }
                serviceInput.value = opt.id || opt.name || 'Servico';
            });
            createdCards++;
        });
        // Selecionar o primeiro por padrão
        if (options.length > 0) {
            updateShippingCost(parseFloat(options[0].price));
            let serviceInput = document.getElementById('superfrete_service_id');
            if (!serviceInput) {
                serviceInput = document.createElement('input');
                serviceInput.type = 'hidden';
                serviceInput.id = 'superfrete_service_id';
                serviceInput.name = 'superfrete_service_id';
                checkoutForm.appendChild(serviceInput);
            }
            serviceInput.value = options[0].id || options[0].name || 'Servico';
        }
        showPixPreview();
    }

    // Exibir rastreamento automático se o pedido tiver etiqueta SuperFrete
    window.addEventListener('DOMContentLoaded', function() {
        const trackingSection = document.querySelector('.tracking-section');
        const labelId = document.querySelector('[data-superfrete-label-id]');
        if (trackingSection && labelId) {
            const id = labelId.getAttribute('data-superfrete-label-id');
            const rastreioDiv = document.createElement('div');
            rastreioDiv.className = 'superfrete-tracking';
            rastreioDiv.innerHTML = `<a href='https://painel.superfrete.com.br/painel/track/${id}' target='_blank' rel='noopener'><i class='fas fa-search-location'></i> Rastrear pedido via SuperFrete</a>`;
            trackingSection.appendChild(rastreioDiv);
        }
    });

    /**
     * Retorna o valor do frete do input hidden (atualizado pelo SuperFrete)
     */
    function getShippingCost() {
        const shippingCostInput = document.getElementById('shipping_cost');
        if (!shippingCostInput || shippingCostInput.value === '' || isNaN(parseFloat(shippingCostInput.value.replace(',', '.')))) return null;
        return parseFloat(shippingCostInput.value.replace(',', '.'));
    }

    // Atualizar endereço de destino em tempo real ao trocar o CEP
    const shippingCepInput = document.getElementById('shipping_cep');
    if (shippingCepInput) {
        shippingCepInput.addEventListener('input', function() {
            const cep = this.value.replace(/\D/g, '');
            if (cep.length === 8) {
                fetch('includes/checkout/calcular-frete.php?action=buscar_cep&cep=' + cep)
                    .then(r => r.json())
                    .then(data => {
                        if (!data.erro) {
                            document.getElementById('frete-endereco-destino').textContent = `${data.logradouro ? data.logradouro + ' - ' : ''}${data.bairro ? data.bairro + ' - ' : ''}${data.localidade}/${data.uf}`;
                        } else {
                            document.getElementById('frete-endereco-destino').textContent = 'CEP não encontrado';
                        }
                    })
                    .catch(() => {
                        document.getElementById('frete-endereco-destino').textContent = 'Erro ao buscar endereço';
                    });
            } else {
                document.getElementById('frete-endereco-destino').textContent = 'CEP não informado';
            }
        });
    }
});