<?php
// checkout.php
date_default_timezone_set('America/Sao_Paulo');

require_once '../adminView/config/dbconnect.php';
require_once '../adminView/controller/Produtos/UserCartController.php';
require_once '../adminView/controller/Produtos/ProductController.php';
require_once '../adminView/controller/Produtos/OrderController.php';

require_once '../adminView/controller/Produtos/UserFavoritesController.php';
require_once '../adminView/controller/BlingPayloadMapper.php';
require_once '../adminView/models/BlingIntegration.php';

$userCartController = new UserCartController($conn);
$productController = new ProductController($conn);
$orderController = new OrderController($conn);
$userFavoritesController = new UserFavoritesController($conn);

// Função para calcular o total do pedido
function calculateOrderTotal($subtotal, $shipping, $discount = 0)
{
    return $subtotal + $shipping - $discount;
}

$orderPlaced = false;
$orderError = '';

session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../Site/login_site.php?redirect=checkout');
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['username'];

// Obter foto de perfil
if (isset($_SESSION['user_picture']) && !empty($_SESSION['user_picture'])) {
    $userPicture = $_SESSION['user_picture'];
} else {
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

$userEmail = $_SESSION['user_email'] ?? '';

// Obter itens do carrinho
$cartItems = $userCartController->getCartItems($userId);
// Enriquecer cada item do carrinho com dados completos do produto
foreach ($cartItems as &$item) {
    $produtoCompleto = $productController->getProductById($item['product_id']);
    if ($produtoCompleto) {
        $item['desconto'] = $produtoCompleto['desconto'] ?? 0;
        $item['lancamento'] = $produtoCompleto['lancamento'] ?? 0;
        $item['em_alta'] = $produtoCompleto['em_alta'] ?? 0;
        $item['preco_original'] = $produtoCompleto['preco'] ?? $item['preco'];
        $item['preco_final'] = (!empty($produtoCompleto['desconto']) && $produtoCompleto['desconto'] > 0)
            ? $produtoCompleto['preco'] * (1 - $produtoCompleto['desconto'] / 100)
            : $produtoCompleto['preco'];
        // Adicionar dimensões e peso
        $item['comprimento'] = $produtoCompleto['comprimento'] ?? 0;
        $item['largura'] = $produtoCompleto['largura'] ?? 0;
        $item['altura'] = $produtoCompleto['altura'] ?? 0;
        $item['peso'] = $produtoCompleto['peso'] ?? 2;
        // Adicionar tipo de caixa
        $item['caixa'] = $produtoCompleto['caixa'] ?? 'G';
    }
}
unset($item);

// Calcular dimensões totais do carrinho
$totalComprimento = 0;
$maxLargura = 0;
$maxAltura = 0;
$totalPeso = 0;

foreach ($cartItems as $item) {
    $comprimento = (isset($item['comprimento']) && $item['comprimento'] > 0) ? $item['comprimento'] : 40;
    $largura = (isset($item['largura']) && $item['largura'] > 0) ? $item['largura'] : 40;
    $altura = (isset($item['altura']) && $item['altura'] > 0) ? $item['altura'] : 35;
    $peso = (isset($item['peso']) && $item['peso'] > 0) ? $item['peso'] : 2;
    $totalComprimento += $comprimento * $item['quantity'];
    $maxLargura = max($maxLargura, $largura);
    $maxAltura = max($maxAltura, $altura);
    $totalPeso += $peso * $item['quantity'];
}

// Se não há dimensões definidas, usar valores padrão
if ($totalComprimento == 0) $totalComprimento = 40; // Caixa G padrão
if ($maxLargura == 0) $maxLargura = 40;
if ($maxAltura == 0) $maxAltura = 35;
if ($totalPeso == 0) $totalPeso = 2;

$favoritesItems = $userFavoritesController->getFavoriteItems($userId);
$cartCount = array_sum(array_column($cartItems, 'quantity'));
$favoritesCount = count($favoritesItems);

// Obter dados do usuário
$query = 'SELECT name, telefone, endereco, cep, numero_casa, cpf FROM usuarios WHERE id = ?';
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

// Verificar se usuário tem direito ao desconto de primeira compra
$discountPrimeiraCompra = 0;

// Função para buscar UF pelo CEP (melhorada com mais estados)
function getUfByCep($cep) {
    $cep = preg_replace('/\D/', '', $cep);
    $cep5 = substr($cep, 0, 5);
    $faixas = [
        'SP' => ['10001', '19999'],
        'RJ' => ['20000', '28999'],
        'SC' => ['88000', '89999'],
        'RS' => ['90000', '99999'],
        'PR' => ['80000', '87999'],
        'GO' => ['72800', '76799'],
        'BA' => ['40000', '48999'],
        'MG' => ['30000', '39999'],
        'ES' => ['29000', '29999'],
        'MS' => ['79000', '79999'],
        'MT' => ['78000', '78999'],
        'RO' => ['76800', '77999'],
        'AC' => ['69900', '69999'],
        'RR' => ['69300', '69399'],
        'AP' => ['68900', '68999'],
        'AM' => ['69000', '69999'],
        'PA' => ['66000', '69999'],
        'TO' => ['77000', '77999'],
        'MA' => ['65000', '69999'],
        'PI' => ['64000', '64999'],
        'CE' => ['60000', '63999'],
        'RN' => ['59000', '59999'],
        'PB' => ['58000', '58999'],
        'PE' => ['50000', '59999'],
        'AL' => ['57000', '57999'],
        'SE' => ['49000', '49999'],
        'DF' => ['70000', '70999']
    ];
    foreach ($faixas as $uf => $range) {
        // Comparar como strings para evitar problemas com CEPs que começam com0        if (strcmp($cep5, $range[0]) >= 0 && strcmp($cep5, $range[1]) <= 0) return $uf;
    }
    return null;
}

// Calcular subtotal considerando promoções
$subtotal = 0;
foreach ($cartItems as $item) {
    $preco_final = isset($item['desconto']) && $item['desconto'] > 0 ? $item['preco'] * (1 - $item['desconto'] / 100) : $item['preco'];
    $subtotal += $preco_final * $item['quantity'];
}
// Aplicar desconto de 10% se for a primeira compra

// Cálculo do frete: sempre null para que o JS calcule e exiba opções pagas para todos os estados
$shipping = null;

// Detectar se o método de pagamento é PIX (para exibir desconto de 5%)
$pixSelected = false;
$discountPix = 0;
if (isset($_POST['payment_method']) && $_POST['payment_method'] === 'pix') {
    $pixSelected = true;
    $discountPix = round($subtotal * 0.05, 2);
}

// Total geral
$total = $subtotal + $shipping - $discountPix;

// Processar o pedido se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMethod = $_POST['payment_method'] ?? '';
    $shippingAddress = $_POST['shipping_address'] ?? '';
    $shippingNumber = $_POST['shipping_number'] ?? '';
    $shippingCep = $_POST['shipping_cep'] ?? '';
    $shippingComplement = $_POST['shipping_complement'] ?? '';
    $shippingPhone = $_POST['shipping_phone'] ?? '';
    $savedCardId = $_POST['saved_card_id'] ?? '';
    $saveCard = isset($_POST['save_card']) && $_POST['save_card'] === 'on';
    $cardNumber = $_POST['card_number'] ?? '';
    $cardName = $_POST['card_name'] ?? '';
    $cardExpiry = $_POST['card_expiry'] ?? '';
    $cardCvv = $_POST['card_cvv'] ?? '';
    $installments = $_POST['cc_installments'] ?? 1;
    $paymentId = $_POST['payment_id'] ?? '';

    // Usar o valor de frete enviado pelo formulário
    $shipping = isset($_POST['shipping_cost']) ? floatval(str_replace(',', '.', $_POST['shipping_cost'])) : 0;
    error_log('POST shipping_cost: ' . ($_POST['shipping_cost'] ?? 'NULO'));
    error_log('DEBUG payment_id: ' . $paymentId);

    // NÃO recalcule o frete do zero aqui, apenas use o valor enviado
    // Recalcular o total final após processar frete e descontos
    $total = $subtotal + $shipping - $discountPix;

    // Validação acumulando erros
    $validationErrors = [];
    if (empty($paymentMethod)) {
        $validationErrors[] = 'Selecione um método de pagamento.';
    }
    if (empty($shippingAddress)) {
        $validationErrors[] = 'Informe o endereço de entrega.';
    }
    if ($paymentMethod === 'credit_card' && empty($savedCardId)) {
        $expiryParts = explode('/', $cardExpiry);
        if (count($expiryParts) !== 2 || !preg_match('/^\d{2}$/', $expiryParts[0]) || !preg_match('/^\d{2}$/', $expiryParts[1])) {
            $validationErrors[] = 'Data de validade inválida. Use o formato MM/AA.';
        } elseif ($expiryParts[0] < 1 || $expiryParts[0] > 12) {
            $validationErrors[] = 'Mês de validade inválido.';
        } elseif (intval('20' . $expiryParts[1]) < date('Y') || (intval('20' . $expiryParts[1]) === date('Y') && $expiryParts[0] < date('m'))) {
            $validationErrors[] = 'Data de validade expirada.';
        }
    }

    if (!empty($validationErrors)) {
        $orderError = 'Erro ao processar o pedido:<br>' . implode('<br>', $validationErrors);
        error_log('ERRO VALIDACAO PEDIDO: ' . strip_tags(implode(' | ', $validationErrors)));
        $orderPlaced = false;
    }

    // Processamento do pedido
    if (empty($orderError)) {

        try {
            // Instanciar BlingIntegration se não existir
            if (!isset($bling) || !$bling) {
                $bling = new BlingIntegration();
            }

            // Buscar usuário atualizado
            $stmt = $conn->prepare('SELECT * FROM usuarios WHERE id = ?');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $usuarioRowAtualizado = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // Buscar contato no Bling pelo CPF/CNPJ antes de criar
            if (empty($usuarioRowAtualizado['id_externo_bling'])) {
                $cpfCnpj = preg_replace('/\D/', '', $usuarioRowAtualizado['cpf']);
                $blingContatoExistente = $bling->buscarContatoPorDocumento($cpfCnpj); // Implementar esse método na BlingIntegration
                $blingId = null;
                if (is_array($blingContatoExistente) && isset($blingContatoExistente['data'][0]['codigo'])) {
                    $blingId = $blingContatoExistente['data'][0]['codigo'];
                }
                if (!empty($blingId)) {
                    // Já existe, salva no banco
                    $stmt = $conn->prepare('UPDATE usuarios SET id_externo_bling = ? WHERE id = ?');
                    $stmt->bind_param('si', $blingId, $userId);
                    $stmt->execute();
                    $stmt->close();
                    $usuarioRowAtualizado['id_externo_bling'] = $blingId;
                    echo '<div style="color:blue; background:#eaf3ff; border:1px solid #08f; padding:10px; margin:10px 0; font-weight:bold;">Contato já existente no Bling! Código: '.htmlspecialchars($blingId).'</div>';
                } else {
                    // Não existe, criar contato
                    $contatoPayload = [
                        'nome' => $usuarioRowAtualizado['name'],
                        'codigo' => $cpfCnpj,
                        'situacao' => 'A',
                        'numeroDocumento' => $cpfCnpj,
                        'telefone' => $usuarioRowAtualizado['telefone'],
                        'celular' => $usuarioRowAtualizado['celular'] ?? $usuarioRowAtualizado['telefone'],
                        'fantasia' => $usuarioRowAtualizado['name'],
                        'tipo' => 'F',
                        'email' => $userEmail,
                        'endereco' => [
                            'geral' => [
                                'endereco' => $usuarioRowAtualizado['endereco'],
                                'cep' => $usuarioRowAtualizado['cep'],
                                'bairro' => $usuarioRowAtualizado['bairro'] ?? '',
                                'municipio' => $usuarioRowAtualizado['cidade'] ?? '',
                                'uf' => $usuarioRowAtualizado['estado'] ?? '',
                                'numero' => $usuarioRowAtualizado['numero_casa'],
                                'complemento' => $usuarioRowAtualizado['complemento'] ?? ''
                            ],
                            'cobranca' => [
                                'endereco' => $usuarioRowAtualizado['endereco'],
                                'cep' => $usuarioRowAtualizado['cep'],
                                'bairro' => $usuarioRowAtualizado['bairro'] ?? '',
                                'municipio' => $usuarioRowAtualizado['cidade'] ?? '',
                                'uf' => $usuarioRowAtualizado['estado'] ?? '',
                                'numero' => $usuarioRowAtualizado['numero_casa'],
                                'complemento' => $usuarioRowAtualizado['complemento'] ?? ''
                            ]
                        ],
                        'rg' => $usuarioRowAtualizado['rg'] ?? '',
                        'ie' => $usuarioRowAtualizado['ie'] ?? '',
                        'inscricaoMunicipal' => $usuarioRowAtualizado['inscricao_municipal'] ?? '',
                        'orgaoEmissor' => $usuarioRowAtualizado['orgao_emissor'] ?? '',
                        'dadosAdicionais' => [
                            'dataNascimento' => $usuarioRowAtualizado['data_nascimento'] ?? '',
                            'sexo' => $usuarioRowAtualizado['sexo'] ?? '',
                            'naturalidade' => $usuarioRowAtualizado['nacionalidade'] ?? ''
                        ],
                        'financeiro' => [
                            'limiteCredito' => 0,
                            'condicaoPagamento' => '30',
                            'categoria' => [ 'id' => 1 ]
                        ],
                        'pais' => [ 'nome' => 'BRASIL' ]
                    ];
                    error_log('BLING CONTATO PAYLOAD: ' . json_encode($contatoPayload));
                    $blingContato = $bling->criarContato($contatoPayload);
                    error_log('BLING CONTATO RESPONSE: ' . json_encode($blingContato));
                    error_log('DADOS USUARIO PARA CONTATO: ' . json_encode($usuarioRowAtualizado));
                    // Exibir resultado do cadastro na tela e console SEMPRE
                    echo '<details style="background:#eaffea; border:1px solid #0a0; padding:10px; margin:10px 0; color:#333;">
                            <summary style="font-weight:bold; color:#080;">Debug Bling (cadastro do cliente)</summary>
                            <div><b>Payload enviado:</b><br><pre style="white-space:pre-wrap;">'.htmlspecialchars(json_encode($contatoPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)).'</pre></div>
                            <div><b>Resposta Bling:</b><br><pre style="white-space:pre-wrap;">'.htmlspecialchars(json_encode($blingContato, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)).'</pre></div>
                        </details>';
                    echo '<script>console.log("BLING CONTATO PAYLOAD:", '.json_encode($contatoPayload).');</script>';
                    echo '<script>console.log("BLING CONTATO RESPONSE:", '.json_encode($blingContato).');</script>';
                    $blingId = null;
                    if (is_array($blingContato) && isset($blingContato['data']['id'])) {
                        $blingId = $contatoPayload['codigo'];
                    } elseif (is_string($blingContato) || is_numeric($blingContato)) {
                        $blingId = $contatoPayload['codigo'];
                    }
                    if (!empty($blingId)) {
                        $stmt = $conn->prepare('UPDATE usuarios SET id_externo_bling = ? WHERE id = ?');
                        $stmt->bind_param('si', $blingId, $userId);
                        $stmt->execute();
                        $stmt->close();
                        $usuarioRowAtualizado['id_externo_bling'] = $blingId;
                        echo '<div style="color:green; background:#eaffea; border:1px solid #0a0; padding:10px; margin:10px 0; font-weight:bold;">Contato criado no Bling com sucesso! Código: '.htmlspecialchars($blingId).'</div>';
                        // Exibir log sempre que criar contato
                        echo '<details style="background:#eaffea; border:1px solid #0a0; padding:10px; margin:10px 0; color:#333;">
                                <summary style="font-weight:bold; color:#080;">Debug Bling (cadastro do cliente)</summary>
                                <div><b>Payload enviado:</b><br><pre style="white-space:pre-wrap;">'.htmlspecialchars(json_encode($contatoPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)).'</pre></div>
                                <div><b>Resposta Bling:</b><br><pre style="white-space:pre-wrap;">'.htmlspecialchars(json_encode($blingContato, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)).'</pre></div>
                            </details>';
                        echo '<script>console.log("BLING CONTATO PAYLOAD:", '.json_encode($contatoPayload).');</script>';
                        echo '<script>console.log("BLING CONTATO RESPONSE:", '.json_encode($blingContato).');</script>';
                    } else {
                        $msg = 'Erro ao criar contato do cliente no Bling.';
                        $detalhe = '';
                        // Exibir log mesmo em caso de erro
                        echo '<details style="background:#fff3f3; border:1px solid #f00; padding:10px; margin:10px 0; color:#333;">
                                <summary style="font-weight:bold; color:#f00;">Debug Bling (cadastro do cliente)</summary>
                                <div><b>Payload enviado:</b><br><pre style="white-space:pre-wrap;">'.htmlspecialchars(json_encode($contatoPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)).'</pre></div>
                                <div><b>Resposta Bling:</b><br><pre style="white-space:pre-wrap;">'.htmlspecialchars(json_encode($blingContato, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)).'</pre></div>
                            </details>';
                        echo '<script>console.log("BLING CONTATO PAYLOAD:", '.json_encode($contatoPayload).');</script>';
                        echo '<script>console.log("BLING CONTATO RESPONSE:", '.json_encode($blingContato).');</script>';
                        if (isset($blingContato['erro'])) {
                            $detalhe = $blingContato['erro'];
                            $msg .= ' Detalhe: ' . $detalhe;
                            echo '<script>console.error("BLING ERRO: ' . addslashes($detalhe) . '");</script>';
                        }
                        echo '<div style="color:red; background:#fff3f3; border:1px solid #f00; padding:10px; margin:10px 0; font-weight:bold;">Erro Bling: ' . htmlspecialchars($detalhe) . '</div>';
                        throw new Exception($msg);
                    }
                    // Bloqueia envio do pedido se não cadastrou o cliente
                    if (empty($usuarioRowAtualizado['id_externo_bling'])) {
                        throw new Exception('Cliente não cadastrado no Bling. Pedido não será enviado.');
                    }
                }
            }
            $usuarioRow = $usuarioRowAtualizado;

            // Dados do pedido local
            $orderData = [
                'user_id' => (int)$userId,
                'total' => (float)$total,
                'subtotal' => (float)$subtotal,
                'shipping' => (float)$shipping,
                'discount' => (float)($discountPrimeiraCompra ?? 0),
                'payment_method' => (string)$paymentMethod,
                'status' => (string)($paymentMethod === 'pix' ? 'aguardando_pagamento' : 'processando'),
                'shipping_address' => (string)$shippingAddress,
                'shipping_number' => (string)$shippingNumber,
                'shipping_cep' => (string)$shippingCep,
                'shipping_complement' => (string)$shippingComplement,
                'tracking_code' => '',
                'installments' => (int)$installments,
                'payment_id' => (string)$paymentId
            ];

            $orderId = $orderController->createOrder($orderData);
            if (!$orderId) {
                throw new Exception('Falha ao criar pedido no banco.');
            }

            // Adicionar itens ao pedido
            foreach ($cartItems as $item) {
                $ok = $orderController->addOrderItem($orderId, $item['product_id'], $item['quantity'], $item['preco']);
                if (!$ok) {
                    throw new Exception('Falha ao adicionar item ao pedido.');
                }
            }

            $trackingCode = 'CG' . strtoupper(substr(md5($orderId . time()), 0, 8));
            $orderController->updateOrder($orderId, ['tracking_code' => $trackingCode]);

            // --- ENVIAR PEDIDO PARA O BLING ---
            $pedidoBanco = $orderController->getOrderById($orderId);
            $itensPedido = $orderController->getOrderItems($orderId);

            $blingConfig = [
                'loja_id' => 205510600,
                'default_ncm' => '70139110',
                'default_cfop' => '5102'
            ];

            $stmt = $conn->prepare('SELECT * FROM usuarios WHERE id = ?');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $usuarioRowAtual = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (empty($usuarioRowAtual['id_externo_bling'])) {
                throw new Exception('Contato do cliente não encontrado para o pedido. Não foi possível obter ou criar o ID do cliente no Bling.');
            }

            require_once '../adminView/controller/BlingPayloadMapper.php';
            $usuarioRowAtual['id_externo_bling'] = trim($usuarioRowAtual['id_externo_bling']);
            $pedidoPayload = BlingPayloadMapper::mapOrderToBling(
                $pedidoBanco,
                $usuarioRowAtual,
                $itensPedido,
                $blingConfig
            );
            if (
                isset($pedidoPayload['cliente']) &&
                (
                    !isset($pedidoPayload['cliente']['codigo']) ||
                    $pedidoPayload['cliente']['codigo'] != $usuarioRowAtual['id_externo_bling']
                )
            ) {
                $pedidoPayload['cliente']['codigo'] = $usuarioRowAtual['id_externo_bling'];
            }
            $bling->criarPedidoVenda($pedidoPayload, null);

            $userCartController->clearCart($userId);

            if ($paymentMethod === 'credit_card' && empty($savedCardId) && $saveCard) {
                $cardLast4 = substr(preg_replace('/\D/', '', $cardNumber), -4);
                $cardNameToSave = $cardName;
                $cardExpiryToSave = $cardExpiry;
                $userCardToken = $paymentId;
                $insertCard = $conn->prepare('INSERT INTO user_cards (user_id, card_last4, card_name, card_expiry, card_token) VALUES (?, ?, ?, ?, ?)');
                $insertCard->bind_param('issss', $userId, $cardLast4, $cardNameToSave, $cardExpiryToSave, $userCardToken);
                $insertCard->execute();
                $insertCard->close();
            }

            if ($discountPrimeiraCompra > 0) {
                $update = $conn->prepare('UPDATE usuarios SET primeira_compra = 0 WHERE id = ?');
                $update->bind_param('i', $userId);
                $update->execute();
                $update->close();
            }

            $orderPlaced = true;
        } catch (Exception $e) {
            // Exibir log do Bling se existir payload/response
            if (isset($contatoPayload) && isset($blingContato)) {
                echo '<details style="background:#fff3f3; border:1px solid #f00; padding:10px; margin:10px 0; color:#333;">
                        <summary style="font-weight:bold; color:#f00;">Debug Bling (cadastro do cliente)</summary>
                        <div><b>Payload enviado:</b><br><pre style="white-space:pre-wrap;">'.htmlspecialchars(json_encode($contatoPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)).'</pre></div>
                        <div><b>Resposta Bling:</b><br><pre style="white-space:pre-wrap;">'.htmlspecialchars(json_encode($blingContato, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)).'</pre></div>
                    </details>';
                echo '<script>console.log("BLING CONTATO PAYLOAD:", '.json_encode($contatoPayload).');</script>';
                echo '<script>console.log("BLING CONTATO RESPONSE:", '.json_encode($blingContato).');</script>';
            }
            $orderError = 'Erro: ' . $e->getMessage();
            error_log('ERRO FINALIZACAO: ' . $e->getMessage());
            if (isset($conn) && $conn->error) {
                error_log('ERRO MYSQL: ' . $conn->error);
            }
            $orderPlaced = false;
        }
    } else {
        if (empty($orderError)) {
            $orderError = 'Erro ao processar o pedido: motivo desconhecido.';
        } else {
            $orderError = 'Erro ao processar o pedido: ' . $orderError;
        }
        error_log('ERRO PROCESSAMENTO PEDIDO: ' . $orderError);
        $orderPlaced = false;
    }
} // Fim do bloco if POST

// Antes do cálculo do total no bloco do POST:
$discountPrimeiraCompra = 0;
$discountPix = isset($discountPix) ? $discountPix : 0;
$total = (float)$subtotal + (float)$shipping - (float)$discountPix;

// O bloco de finalização de pedido, adicionar itens, limpar carrinho e definir $orderPlaced existe apenas dentro do if POST

// Antes de exibir o frete:
// NÃO fazer cast para float, pois null significa "ainda não calculado" e 0 é só para grátis

// Obter cartões salvos
$savedCards = [];
$cardsQuery = 'SELECT id, card_last4, card_name, card_expiry FROM user_cards WHERE user_id = ? ORDER BY id DESC';
$cardsStmt = $conn->prepare($cardsQuery);
$cardsStmt->bind_param('i', $userId);
$cardsStmt->execute();
$cardsResult = $cardsStmt->get_result();

while ($card = $cardsResult->fetch_assoc()) {
    $savedCards[] = $card;
}

// Funções auxiliares
function fetchProducts($productController, $conn)
{
    $categoria = isset($_GET['categoria']) ? htmlspecialchars($_GET['categoria']) : '';
    $orderBy = isset($_GET['orderBy']) ? htmlspecialchars($_GET['orderBy']) : '';
    $search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';

    if (!empty($categoria)) {
        return $productController->getProductsByCategory($categoria);
    } elseif (!empty($orderBy)) {
        return $productController->getProductsOrderedBy($orderBy);
    } elseif (!empty($search)) {
        return $productController->searchProducts($search);
    } else {
        return $productController->getAllProducts();
    }
}

function getProductImagePath($productId, $conn)
{
    $query = 'SELECT imagem FROM produtos WHERE id = ?';
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $productId);
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

function loadSiteConfig($configPath)
{
    if (!file_exists($configPath)) {
        throw new Exception('Erro ao carregar as configurações do site.');
    }

    $jsonContent = file_get_contents($configPath);
    if ($jsonContent === false) {
        throw new Exception('Erro ao carregar as configurações do site.');
    }

    $configData = json_decode($jsonContent, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Erro ao carregar as configurações do site.');
    }

    return $configData;
}

$siteConfigPath = __DIR__ . '/../adminView/config_site.json';
$jsonContent = file_get_contents($siteConfigPath);
$configData = json_decode($jsonContent, true);

$getConfigValue = function ($config, $keys, $default = '') {
    $value = $config;
    foreach ($keys as $key) {
        if (!isset($value[$key])) {
            return $default;
        }
        $value = $value[$key];
    }
    return is_string($value) ? htmlspecialchars($value) : $value;
};

$sobreMidia = $getConfigValue($configData, ['pagina_inicial', 'sobre', 'midia']);
$whatsapp = $getConfigValue($configData, ['contato', 'whatsapp']);
$instagram = $getConfigValue($configData, ['contato', 'instagram'], '#');
$facebook = $getConfigValue($configData, ['contato', 'facebook'], '#');
$email = $getConfigValue($configData, ['contato', 'email'], '#');
$footerTexto = $getConfigValue($configData, ['rodape', 'texto']);

// Limpa o número para o formato internacional do WhatsApp
$whatsapp_link = preg_replace('/[^0-9]/', '', $whatsapp);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../adminView/assets/images/logo.png" type="image/x-icon">
    <title>Finalizar Compra - Cristais Gold Lar</title>
    <link rel="stylesheet" href="../Site/css/index/index.css">
    <link rel="stylesheet" href="../Site/css/checkout/checkout.css">
    <link rel="stylesheet" href="../Site/css/checkout/checkout-responsivo.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="../Site/css/global-alerts.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <script src="../Site/js/global-alerts.js"></script>
</head>

<body>
    <div id="loading-screen">
        <div class="loader"></div>
    </div>

    <div class="notification-bar">
        <div class="message active" id="message1">Até 6x Sem Juros</div>
    </div>

    <!-- Cabeçalho principal -->
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
                    <img src="../Site/img/icons/compras.png" alt="Carrinho">
                    <span class="counter cart-counter"><?= $cartCount ?></span>
                </a>
                <a href="../Site/meusItens.php" class="cart-icon">
                    <img src="../Site/img/icons/salvar preto.png" alt="Favoritos">
                    <span class="counter favorites-counter"><?= $favoritesCount ?></span>
                </a>
                <div class="dropdown">
                    <a href="#" id="profile-btn">
                        <span class="profile-toggle">
                            <img src="<?php echo !empty($userPicture) && strpos($userPicture, 'http') !== 0 ? '../adminView/uploads/profile_pictures/' . htmlspecialchars($userPicture) : htmlspecialchars($userPicture); ?>" alt="Foto de Perfil" onerror="this.src='../Site/img/icons/perfil.png';">
                            <?= htmlspecialchars($userName) ?>
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

    <!-- Cabeçalho secundário para mobile -->
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

    <!-- Menu lateral -->
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

    <!-- Barra inferior para mobile -->
    <div class="tabbar">
        <a href="../Site/meusItens.php">
            <img src="../Site/img/icons/salvar preto.png" alt="Favoritos">
            <span>Favoritos</span>
            <span class="counter favorites-counter"><?= $favoritesCount ?></span>
        </a>
        <a href="../Site/profile.php">
            <img src="<?php echo !empty($userPicture) && strpos($userPicture, 'http') !== 0 ? '../adminView/uploads/profile_pictures/' . htmlspecialchars($userPicture) : htmlspecialchars($userPicture); ?>" alt="Perfil" onerror="this.src='../Site/img/icons/perfil.png';">
            <span>Perfil</span>
        </a>
        <a href="../Site/meusItens.php">
            <img src="../Site/img/icons/compras.png" alt="Carrinho">
            <span>Carrinho</span>
            <span class="counter cart-counter"><?= $cartCount ?></span>
        </a>
    </div>


    <?php if ($orderPlaced): ?>
        <?php
        // Buscar os dados do pedido salvo no banco
        $pedido = $orderController->getOrderById($orderId);
        $paymentMethod = $pedido['payment_method'] ?? '';
        $total = $pedido['total'] ?? 0;
        $status = $pedido['status'] ?? '';
        $installments = $pedido['installments'] ?? 1;
        $valorParcela = $installments > 1 ? $total / $installments : $total;
        ?>
        <div class="checkout-container">
            <div class="order-confirmation">
                <div class="confirmation-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1>Pedido Confirmado!</h1>
                <p>Seu pedido foi processado com sucesso. Obrigado pela compra!</p>

                <div class="order-details">
                    <h3>Detalhes do Pedido:</h3>
                    <div class="detail-row">
                        <span>Número do Pedido:</span>
                        <span>#<?= htmlspecialchars($orderId) ?></span>
                    </div>
                    <div class="detail-row">
                        <span>Data:</span>
                        <span><?= date('d/m/Y H:i', time()) ?></span>
                    </div>
                    <div class="detail-row">
                        <span>Total:</span>
                        <span>R$<?= number_format($total, 2, ',', '.') ?></span>
                    </div>
                    <div class="detail-row">
                        <span>Parcelamento:</span>
                        <span><?= $installments ?>x de R$<?= number_format($valorParcela, 2, ',', '.') ?> sem juros</span>
                    </div>
                    <div class="detail-row">
                        <span>Método de Pagamento:</span>
                        <span class="payment-method-display">
                            <?php
                            if ($paymentMethod === 'credit_card') {
                                echo '<i class="fas fa-credit-card"></i> Cartão de Crédito';
                                if (!empty($cardNumber)) {
                                    echo ' <span class="card-number">(final ' . substr(preg_replace('/\D/', '', $cardNumber), -4) . ')</span>';
                                } elseif (!empty($savedCardId)) {
                                    foreach ($savedCards as $card) {
                                        if ($card['id'] == $savedCardId) {
                                            echo ' <span class="card-number">(final ' . $card['card_last4'] . ')</span>';
                                        }
                                    }
                                }
                            } elseif ($paymentMethod === 'pix') {
                                echo '<i class="fas fa-qrcode"></i> PIX';
                            }
                            ?>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span>Status:</span>
                        <span>
                            <?php
                            if ($paymentMethod === 'credit_card') {
                                echo '<span style="color: #4e8d7c;">Pagamento Aprovado</span>';
                            } elseif ($paymentMethod === 'pix') {
                                echo '<span style="color: #f0ad4e;">Aguardando Pagamento</span>';
                            }
                            ?>
                        </span>
                    </div>
                </div>

                <div class="tracking-section">
                    <h3>Acompanhe seu Pedido</h3>
                    <p>Código de Rastreio: <strong><?= $trackingCode ?></strong></p>

                    <div class="confirmation-buttons">
                        <a href="../Site/index.php" class="btn-continue-shopping">Continuar Comprando</a>
                        <a href="../Site/profile.php?tab=orders" class="btn-view-order">Ver Meus Pedidos</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php if ($orderError): ?>
                <script>
                    // Exibe o erro do backend no console do navegador
                    console.error(<?= json_encode($orderError) ?>);
                </script>
            <?php endif; ?>

            <div class="checkout-container">
                <div class="checkout-header">
                    <h1>Finalizar Compra</h1>
                    <div class="checkout-progress">
                        <div class="progress-step">
                            <div class="step-circle completed"></div>
                            <div class="step-line active"></div>
                            <div class="step-label">Carrinho</div>
                        </div>
                        <div class="progress-step">
                            <div class="step-circle active"></div>
                            <div class="step-line"></div>
                            <div class="step-label">Pagamento</div>
                        </div>
                        <div class="progress-step">
                            <div class="step-circle"></div>
                            <div class="step-line"></div>
                            <div class="step-label">Confirmação</div>
                        </div>
                        <div class="progress-step">
                            <div class="step-circle"></div>
                            <div class="step-label">Entrega</div>
                        </div>
                    </div>
                </div>

                <div class="checkout-content">
                    <div class="checkout-section">
                        <h2 class="section-title">Informações de Entrega</h2>

                        <form id="checkout-form" method="POST" action="">
                            <input type="hidden" name="shipping_cost" id="shipping_cost" value="">
                            <div class="form-group">
                                <label for="shipping_address">Endereço de Entrega</label>
                                <input type="text" id="shipping_address" name="shipping_address" class="form-control" value="<?= htmlspecialchars($userData['endereco'] ?? '') ?>" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="shipping_number">Número</label>
                                    <input type="text" id="shipping_number" name="shipping_number" class="form-control" value="<?= htmlspecialchars($userData['numero_casa'] ?? '') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="shipping_complement">Complemento</label>
                                    <input type="text" id="shipping_complement" name="shipping_complement" class="form-control" value="<?= htmlspecialchars($userData['complemento'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="shipping_cep">CEP</label>
                                <div class="cep-finder">
                                    <input type="text" id="shipping_cep" name="shipping_cep" class="form-control" value="<?= htmlspecialchars($userData['cep'] ?? '') ?>" maxlength="9" placeholder="00000-000" required>
                                    <button type="button" id="btn-find-cep" class="btn-find-cep">Buscar</button>
                                </div>
                                <small class="shipping-info text-muted">Frete grátis para SP, RJ, SC, RS, PR e GO em compras acima de R$399,99</small>
                            </div>

                            <div class="form-group">
                                <label for="shipping_phone">Telefone para Contato</label>
                                <input type="tel" id="shipping_phone" name="shipping_phone" class="form-control" value="<?= htmlspecialchars($userData['telefone'] ?? '') ?>" placeholder="(00) 00000-0000" required>
                            </div>

                            <h2 class="section-title">Método de Pagamento</h2>

                            <div class="payment-methods">
                                <div class="payment-method" data-method="credit_card">
                                    <i class="fas fa-credit-card"></i>
                                    <div>Cartão de Crédito</div>
                                </div>
                                <div class="payment-method" data-method="debit_card">
                                    <i class="fas fa-credit-card"></i>
                                    <div>Cartão de Débito</div>
                                </div>
                                <div class="payment-method" data-method="pix">
                                    <i class="fas fa-qrcode"></i>
                                    <div>PIX</div>
                                </div>
                            </div>

                            <input type="hidden" name="payment_method" id="payment_method" value="">

                            <div class="payment-details" id="credit_card_details">
                                <?php if (!empty($savedCards)): ?>
                                    <div class="saved-cards">
                                        <h3>Cartões Salvos</h3>
                                        <?php foreach ($savedCards as $card): ?>
                                            <div class="saved-card" data-card-id="<?= htmlspecialchars($card['id']) ?>">
                                                <i class="fas fa-credit-card card-icon"></i>
                                                <span class="card-last4">•••• <?= htmlspecialchars($card['card_last4']) ?></span>
                                                <span class="card-expiry"><?= htmlspecialchars($card['card_expiry']) ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                        <div class="saved-card new-card">
                                            <i class="fas fa-plus-circle card-icon"></i>
                                            <span>Usar novo cartão</span>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="credit-card-form" id="new_card_form">
                                    <div class="form-group">
                                        <label for="card_number">Número do Cartão</label>
                                        <div class="card-input-container">
                                            <input type="text" id="card_number" name="card_number" class="form-control bpmpi_cardnumber" placeholder="0000 0000 0000 0000" maxlength="19" value="<?= htmlspecialchars($_POST['card_number'] ?? '') ?>">
                                            <span class="card-type-icon"><i class="fas fa-credit-card"></i></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="card_name">Nome no Cartão</label>
                                        <input type="text" id="card_name" name="card_name" class="form-control" placeholder="Como aparece no cartão">
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="card_expiry">Validade</label>
                                            <input type="text" id="card_expiry" name="card_expiry" class="form-control" placeholder="MM/AA" maxlength="5" value="<?= htmlspecialchars($_POST['card_expiry'] ?? '') ?>">
                                            <input type="hidden" id="card_expiry_month" class="bpmpi_cardexpirationmonth" value="<?= isset($cardExpiry) ? substr($cardExpiry, 0, 2) : '' ?>">
                                            <input type="hidden" id="card_expiry_year" class="bpmpi_cardexpirationyear" value="<?= isset($cardExpiry) ? '20' . substr($cardExpiry, -2) : '' ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="card_cvv">CVV</label>
                                            <input type="text" id="card_cvv" name="card_cvv" class="form-control bpmpi_cardcvv" placeholder="000" maxlength="4" value="<?= htmlspecialchars($_POST['card_cvv'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="cardholder_email">E-mail do Pagador</label>
                                        <input type="email" id="cardholder_email" name="cardholder_email" class="form-control" value="<?= htmlspecialchars($userEmail) ?>" placeholder="E-mail do pagador" required readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="identification_number">CPF do Pagador</label>
                                        <input type="text" id="identification_number" name="identification_number" class="form-control" value="<?= htmlspecialchars($userData['cpf'] ?? '') ?>" placeholder="000.000.000-00" maxlength="14" required readonly>
                                    </div>
                                    <input type="hidden" class="bpmpi_auth" value="true">
                                    <input type="hidden" class="bpmpi_accesstoken" value="">
                                    <input type="hidden" class="bpmpi_ordernumber" value="<?= htmlspecialchars($orderId ?? 'ORD-' . uniqid()) ?>">
                                    <input type="hidden" class="bpmpi_currency" value="BRL">
                                    <input type="hidden" class="bpmpi_totalamount" value="<?= htmlspecialchars(number_format($total * 100, 0, '', '')) ?>">
                                    <input type="hidden" class="bpmpi_installments" id="bpmpi_installments" value="<?= htmlspecialchars($installments) ?>">
                                    <input type="hidden" class="bpmpi_paymentmethod" id="bpmpi_paymentmethod" value="<?= htmlspecialchars($paymentMethod) ?>">
                                    <input type="hidden" class="bpmpi_auth_suppresschallenge" value="false">
                                    <input type="hidden" class="bpmpi_merchant_url" value="<?= htmlspecialchars('https://' . $_SERVER['HTTP_HOST']) ?>">
                                    <input type="hidden" class="bpmpi_device_ipaddress" value="<?= $_SERVER['REMOTE_ADDR'] ?>">
                                    <input type="hidden" class="bpmpi_billto_customerid" value="<?= htmlspecialchars($userData['cpf'] ?? '') ?>">
                                    <input type="hidden" class="bpmpi_billto_contactname" value="<?= htmlspecialchars($userData['name'] ?? '') ?>">
                                    <input type="hidden" class="bpmpi_billto_phonenumber" value="<?= htmlspecialchars($userData['telefone'] ?? '') ?>">
                                    <input type="hidden" class="bpmpi_billto_email" value="<?= htmlspecialchars($userEmail) ?>">
                                    <input type="hidden" class="bpmpi_billto_street1" value="<?= htmlspecialchars($userData['endereco'] ?? '') ?>">
                                    <input type="hidden" class="bpmpi_billto_street2" value="<?= htmlspecialchars($userData['complemento'] ?? '') ?>">
                                    <input type="hidden" class="bpmpi_billto_city" value="<?= htmlspecialchars($userData['cidade'] ?? '') ?>">
                                    <input type="hidden" class="bpmpi_billto_state" value="<?= htmlspecialchars($userData['estado'] ?? '') ?>">
                                    <input type="hidden" class="bpmpi_billto_zipcode" value="<?= htmlspecialchars(preg_replace('/\D/', '', $userData['cep'] ?? '')) ?>">
                                    <input type="hidden" class="bpmpi_billto_country" value="BR">
                                    <input type="hidden" class="bpmpi_shipto_sameasbillto" value="true">
                                    <input type="hidden" class="bpmpi_shipto_addressee" value="<?= htmlspecialchars($userData['name'] ?? '') ?>">
                                    <input type="hidden" class="bpmpi_shipto_phonenumber" value="<?= htmlspecialchars($userData['telefone'] ?? '') ?>">
                                    <input type="hidden" class="bpmpi_shipto_email" value="<?= htmlspecialchars($userEmail) ?>">
                                    <input type="hidden" class="bpmpi_shipto_street1" value="<?= htmlspecialchars($userData['endereco'] ?? '') ?>">
                                    <input type="hidden" class="bpmpi_shipto_street2" value="<?= htmlspecialchars($userData['complemento'] ?? '') ?>">
                                    <input type="hidden" class="bpmpi_shipto_city" value="<?= htmlspecialchars($userData['cidade'] ?? '') ?>">
                                    <input type="hidden" class="bpmpi_shipto_state" value="<?= htmlspecialchars($userData['estado'] ?? '') ?>">
                                    <input type="hidden" class="bpmpi_shipto_zipcode" value="<?= htmlspecialchars(preg_replace('/\D/', '', $userData['cep'] ?? '')) ?>">
                                    <input type="hidden" class="bpmpi_shipto_country" value="BR">
                                    <input type="hidden" class="bpmpi_shipto_shippingmethod" value="other">
                                    <input type="hidden" class="bpmpi_useraccount_guest" value="false">
                                    <input type="hidden" class="bpmpi_useraccount_createddate" value="<?= htmlspecialchars($userData['created_at'] ?? date('Y-m-d')) ?>">
                                    <input type="hidden" class="bpmpi_useraccount_changeddate" value="<?= htmlspecialchars($userData['updated_at'] ?? date('Y-m-d')) ?>">
                                    <input type="hidden" class="bpmpi_useraccount_authenticationmethod" value="02">
                                    <input type="hidden" class="bpmpi_useraccount_authenticationtimestamp" value="<?= date('Y-m-d\TH:i:s') ?>">
                                    <input type="hidden" class="bpmpi_merchant_newcustomer" value="false">
                                    <input type="hidden" class="bpmpi_order_productcode" value="PHY">
                                    <input type="hidden" class="bpmpi_order_countlast24hours" value="1">
                                    <input type="hidden" class="bpmpi_order_countlast6months" value="1">
                                    <input type="hidden" class="bpmpi_order_countlast1year" value="1">
                                    <input type="hidden" class="bpmpi_order_cardattemptslast24hours" value="1">
                                    <input type="hidden" class="bpmpi_order_marketingoptin" value="false">
                                    <input type="hidden" class="bpmpi_order_marketingsource" value="site">
                                    <input type="hidden" class="bpmpi_transaction_mode" value="S">
                                    <input type="hidden" class="bpmpi_brand_establishment_code" value="">
                                    <?php $i = 1;
                                    foreach ($cartItems as $item): ?>
                                    <input type="hidden" class="bpmpi_cart_<?= $i ?>_name" value="<?= htmlspecialchars($item['nome']) ?>">
                                    <input type="hidden" class="bpmpi_cart_<?= $i ?>_description" value="<?= htmlspecialchars($item['descricao'] ?? '') ?>">
                                    <input type="hidden" class="bpmpi_cart_<?= $i ?>_sku" value="<?= htmlspecialchars($item['product_id']) ?>">
                                    <input type="hidden" class="bpmpi_cart_<?= $i ?>_quantity" value="<?= htmlspecialchars($item['quantity']) ?>">
                                    <input type="hidden" class="bpmpi_cart_<?= $i ?>_unitprice" value="<?= htmlspecialchars(number_format($item['preco'] * 100, 0, '', '')) ?>">
                                    <?php $i++;
                                    endforeach; ?>
                                </div>
                            </div>

                            <div class="payment-details" id="pix_details">
                                <div class="pix-container">
                                    <p>Ao finalizar a compra, você receberá um QR Code para pagamento via PIX.</p>
                                    <p>O prazo para pagamento é de 30 minutos. Após este período, o pedido será cancelado automaticamente.</p>
                                </div>
                            </div>

                            <?php if ($orderError): ?>
                                <div class="error-message">
                                    <?= htmlspecialchars($orderError) ?>
                                </div>
                            <?php endif; ?>

                            <button type="submit" class="btn-checkout">Finalizar Compra</button>
                        </form>
                    </div>

                    <div class="cart-summary">
                        <h2 class="section-title">Resumo do Pedido</h2>

                        

                        <div class="cart-items">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="cart-item">
                                    <img src="<?= !empty($item['imagem']) ? htmlspecialchars('../adminView/uploads/produtos/' . $item['imagem']) : getProductImagePath($item['product_id'], $conn) ?>" alt="<?= htmlspecialchars($item['nome']) ?>" class="cart-item-image">
                                    <div class="cart-item-details">
                                        <div class="cart-item-name"><?= htmlspecialchars($item['nome']) ?></div>
                                        <div class="cart-item-price">R$<?= number_format($item['preco_final'], 2, ',', '.') ?></div>
                                        <div class="cart-item-quantity">Quantidade: <?= $item['quantity'] ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="price-details">
                            <div class="price-row">
                                <span>Subtotal</span>
                                <span>R$<?= number_format($subtotal, 2, ',', '.') ?></span>
                            </div>
                            <div class="price-row">
                                <span>Frete</span>
                                <span class="shipping-cost" style="<?php if ($shipping === 0) echo 'color:green;font-weight:bold;'; ?>">
                                    <?php
                                    if ($shipping === 0) {
                                        echo 'R$0,00';
                                    } elseif ($shipping === null || $shipping === '' || !isset($shipping) || $shipping === false) {
                                        echo 'Calculando...';
                                    } else {
                                        echo 'R$' . number_format($shipping, 2, ',', '.');
                                    }
                                    ?>
                                </span>
                                <input type="hidden" name="shipping_cost" id="shipping_cost" value="<?= ($shipping === null || $shipping === '' || $shipping === false) ? '' : $shipping ?>">
                            </div>
                            <?php if ($discountPrimeiraCompra > 0): ?>
                                <div class="price-row">
                                    <span>Desconto 1ª compra (10%)</span>
                                    <span>-R$<?= number_format($discountPrimeiraCompra, 2, ',', '.') ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($pixSelected && $discountPix > 0): ?>
                                <div class="price-row">
                                    <span>Desconto PIX (5%)</span>
                                    <span>-R$<?= number_format($discountPix, 2, ',', '.') ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="price-row price-total">
                                <span>Total</span>
                                <span>R$<?= number_format($total, 2, ',', '.') ?></span>
                            </div>
                            <div class="price-divider"></div>
                            <div class="frete-origem-destino">
                                <div class="frete-origem-bloco">
                                    <div class="frete-icone origem">
                                        <i class="fas fa-warehouse"></i>
                                    </div>
                                    <div class="frete-origem-conteudo">
                                        <div class="frete-label">
                                            <strong>ORIGEM:</strong> 
                                            <span class="frete-cep">08690-265</span>
                                        </div>
                                        <div class="frete-endereco">Rua Mário Bochetti 1102 - Suzano/SP</div>
                                    </div>
                                </div>
                                <div class="frete-destino-bloco">
                                    <div class="frete-icone destino">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div class="frete-destino-conteudo">
                                        <div class="frete-label">
                                            <strong>DESTINO:</strong> 
                                            <span class="frete-cep"><?= htmlspecialchars($userData['cep'] ?? '') ?></span>
                                        </div>
                                        <div class="frete-endereco" id="frete-endereco-destino">Buscando endereço...</div>
                                    </div>
                                </div>
                            </div>
                            <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                var cep = '<?= preg_replace('/\D/', '', $userData['cep'] ?? '') ?>';
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
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div id="processing-overlay" class="processing-overlay" style="display: none;">
            <div class="processing-spinner"></div>
            <div class="processing-message">Processando seu pedido...</div>
        </div>

        <div id="pix-modal" class="modal-overlay" style="display: none;">
            <div class="modal-content">
                <span class="modal-close">×</span>
                <h2>Pagamento via PIX</h2>
                <br>
                <div class="pix-qrcode"></div>
                <div class="pix-container">
                    <div class="pix-code">
                        <span id="pix-code-text"></span>
                    </div>
                </div>
                <p class="pix-instructions">
                    Escaneie o QR Code com o aplicativo do seu banco ou copie o código PIX.
                    <br>
                </p>
                <button class="copy-button" id="copy-pix-code">Copiar Código do PIX</button>
                <button class="pix-confirm-btn" id="pix-confirm-payment">Confirmar Pagamento</button>
            </div>
        </div>



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

        <?php if (isset($GLOBALS['blingDebugHtml'])) echo $GLOBALS['blingDebugHtml']; ?>
        <?php if (isset($GLOBALS['blingDebugScript'])) echo $GLOBALS['blingDebugScript']; ?>

        <!-- Scripts -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
        <script>
            window.temPrimeiraCompra = <?= $discountPrimeiraCompra > 0 ? 'true' : 'false' ?>;
            
            // Passar dimensões calculadas para o JavaScript
            window.carrinhoDimensoes = {
                comprimento: <?= $totalComprimento ?>,
                largura: <?= $maxLargura ?>,
                altura: <?= $maxAltura ?>,
                peso: <?= $totalPeso ?>
            };
        </script>
        <script>
window.carrinhoCaixas = [
<?php foreach ($cartItems as $item): ?>
    "<?= (isset($item['caixa']) && in_array($item['caixa'], ['P','M','G'])) ? $item['caixa'] : 'G' ?>",
<?php endforeach; ?>
];
        </script>
        <script src="../Site/js/checkout/checkout.js"></script>
        <script src="../Site/js/elementos/element.js"></script>
        <script>
            // Esconder a tela de carregamento quando a página estiver pronta
            window.addEventListener('load', function() {
                document.getElementById('loading-screen').style.display = 'none';
            });

            // Copyright
            const currentYear = new Date().getFullYear();
            document.getElementById('copyright').innerHTML = `Copyright © ${currentYear} Cristais Gold Lar. Todos os direitos reservados`;

            document.addEventListener('DOMContentLoaded', function() {
                // Inicializar máscara para o CEP
                if (document.getElementById('shipping_cep')) {
                    $(document.getElementById('shipping_cep')).mask('00000-000');
                }
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
</body>
        <script>
        // Exibe erros do Bling no console do navegador se vierem via header
        (function() {
            if (typeof window !== 'undefined') {
                var req = new XMLHttpRequest();
                req.open('HEAD', window.location.href, true);
                req.onreadystatechange = function() {
                    if (req.readyState === 4) {
                        var blingError = req.getResponseHeader('X-Bling-Error');
                        if (blingError) {
                            try {
                                console.error(decodeURIComponent(blingError));
                            } catch (e) {
                                console.error(blingError);
                            }
                        }
                    }
                };
                req.send(null);
            }
        })();
        </script>

</html>