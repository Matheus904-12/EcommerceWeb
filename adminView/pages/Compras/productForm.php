<?php
// Iniciar sessão se ainda não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/dbconnect.php';
require_once '../../controller/Produtos/ProductController.php';

$productController = new ProductController($conn);

$isEditing = false;
$produto = null;
$productId = null;

// Verificar se é uma edição
if (!empty($_GET['edit']) && is_numeric($_GET['edit'])) {
    $productId = (int)$_GET['edit'];
    $produto = $productController->getProductById($productId);
    $isEditing = !empty($produto);
}

// Definir categorias padronizadas
$categorias = ['Arranjos', 'Vasos de Vidro', 'Muranos', 'Muranos Color', 'Vaso Cerâmica'];

// Processar envio do formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Garantir que o campo desconto seja 0 se não estiver aplicando desconto
    if (!isset($_POST['aplicar_desconto'])) {
        $_POST['desconto'] = 0;
    }

    if ($isEditing) {
        $result = $productController->updateProduct($productId, $_POST, $_FILES['imagem'] ?? null);
    } else {
        $result = $productController->createProduct($_POST, $_FILES['imagem'] ?? null);
    }

    $_SESSION['message'] = $result['message'] ?? "Erro desconhecido.";
    $_SESSION['message_type'] = ($result['success'] ?? false) ? "success" : "danger";

    // Adicionar mensagem específica se o upload falhar
    if (!$result['success'] && strpos($result['message'], 'Erro ao fazer upload') !== false) {
        $_SESSION['message'] .= " Verifique o tipo de arquivo (JPG, PNG, GIF, WEBP) ou as permissões da pasta 'uploads/produtos/'.";
    }

    if (!empty($result['success']) && $result['success']) {
        // Após salvar o arquivo $caminhoImagem
        if (in_array($extensao, ['jpg', 'jpeg', 'png'])) {
            // Copie a função convertImageToWebP do script para cá
            convertImageToWebP($caminhoImagem);
        }
        echo '<script>window.location.href = "../../visualizar_produtos.php";</script>';
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEditing ? 'Editar' : 'Adicionar'; ?> Produto</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-900 text-gray-100">
    <div class="container mx-auto max-w-4xl py-8 px-4">
        <div class="bg-gray-800 rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold mb-6 text-blue-400"><?php echo $isEditing ? 'Editar' : 'Adicionar'; ?> Produto</h2>

            <?php if (!empty($_SESSION['message'])) : ?>
                <div class="mb-6 rounded-md p-4 <?php echo $_SESSION['message_type'] === 'success' ? 'bg-green-800 text-green-200' : 'bg-red-600 text-red-200'; ?>">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <?php if ($_SESSION['message_type'] === 'success'): ?>
                                <svg class="h-5 w-5 text-green-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 0 0 0 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 0 001.414 1.414l2 2 a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            <?php else: ?>
                                <svg class="h-5 w-5 text-red-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 0 0 0 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 0 101.414 1.414L10 11.414l1.293 1.293a1 0 00-1.414-1.414L10 8.586l-8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            <?php endif; ?>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm"><?php echo htmlspecialchars($_SESSION['message']); ?></p>
                        </div>
                    </div>
                </div>
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="nome" class="block text-sm font-medium text-gray-300 mb-2">Nome do Produto*</label>
                        <input type="text" id="nome" name="nome" value="<?php echo $isEditing ? htmlspecialchars($produto['nome']) : ''; ?>" required
                            class="w-full bg-gray-700 border-gray-600 border rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="categoria" class="block text-sm font-medium text-gray-300 mb-2">Categoria</label>
                        <select id="categoria" name="categoria"
                            class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Selecione uma categoria</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo htmlspecialchars($categoria); ?>" <?php echo $isEditing && isset($produto['categoria']) && $produto['categoria'] === $categoria ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($categoria); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="preco" class="block text-sm font-medium text-gray-300 mb-2">Preço (R$)*</label>
                        <input type="number" id="preco" name="preco" step="0.01" min="0" value="<?php echo $isEditing ? htmlspecialchars($produto['preco']) : ''; ?>" required
                            class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div></div> <!-- Espaço vazio para manter o grid -->
                </div>

                <!-- Campos de dimensões e peso -->
                <div class="mb-6">
                    <div class="flex items-center mb-2">
                        <h3 class="text-lg font-medium text-gray-300 mr-4">Dimensões e Peso</h3>
                        <div id="caixa-selector" class="flex space-x-2">
                            <button type="button" data-caixa="P" class="caixa-btn px-3 py-1 rounded border border-gray-500 text-gray-200 bg-gray-700 hover:bg-blue-700 focus:bg-blue-800 focus:text-white">P</button>
                            <button type="button" data-caixa="M" class="caixa-btn px-3 py-1 rounded border border-gray-500 text-gray-200 bg-gray-700 hover:bg-blue-700 focus:bg-blue-800 focus:text-white">M</button>
                            <button type="button" data-caixa="G" class="caixa-btn px-3 py-1 rounded border border-gray-500 text-gray-200 bg-gray-700 hover:bg-blue-700 focus:bg-blue-800 focus:text-white">G</button>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="comprimento" class="block text-sm font-medium text-gray-300 mb-2">Comprimento (cm)</label>
                            <input type="number" id="comprimento" name="comprimento" step="0.01" min="0" value="<?php echo $isEditing ? htmlspecialchars($produto['comprimento'] ?? '40') : '40'; ?>"
                                class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="largura" class="block text-sm font-medium text-gray-300 mb-2">Largura (cm)</label>
                            <input type="number" id="largura" name="largura" step="0.01" min="0" value="<?php echo $isEditing ? htmlspecialchars($produto['largura'] ?? '40') : '40'; ?>"
                                class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="altura" class="block text-sm font-medium text-gray-300 mb-2">Altura (cm)</label>
                            <input type="number" id="altura" name="altura" step="0.01" min="0" value="<?php echo $isEditing ? htmlspecialchars($produto['altura'] ?? '35') : '35'; ?>"
                                class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="peso" class="block text-sm font-medium text-gray-300 mb-2">Peso (kg)</label>
                            <input type="number" id="peso" name="peso" step="0.01" min="0" value="<?php echo $isEditing ? htmlspecialchars($produto['peso'] ?? '2') : '2'; ?>"
                                class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <p class="mt-2 text-sm text-gray-400">Estas informações são usadas para calcular o frete corretamente.</p>
                </div>

                <div class="mb-6">
                    <label for="descricao" class="block text-sm font-medium text-gray-300 mb-2">Descrição</label>
                    <textarea id="descricao" name="descricao" rows="4"
                        class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo $isEditing ? htmlspecialchars($produto['descricao'] ?? '') : ''; ?></textarea>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Status do Produto</label>
                    <div class="flex flex-wrap gap-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="destaque" value="1" <?php echo $isEditing && ($produto['destaque'] ?? 0) ? 'checked' : ''; ?>
                                class="form-checkbox h-5 w-5 text-blue-600 bg-gray-700 border-gray-600">
                            <span class="ml-2 text-gray-300">Destaque</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="lancamento" value="1" <?php echo $isEditing && ($produto['lancamento'] ?? 0) ? 'checked' : ''; ?>
                                class="form-checkbox h-5 w-5 text-blue-600 bg-gray-700 border-gray-600">
                            <span class="ml-2 text-gray-300">Lançamento</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="em_alta" value="1" <?php echo $isEditing && ($produto['em_alta'] ?? 0) ? 'checked' : ''; ?>
                                class="form-checkbox h-5 w-5 text-blue-600 bg-gray-700 border-gray-600">
                            <span class="ml-2 text-gray-300">Em Alta</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="promocao" value="1" <?php echo $isEditing && ($produto['promocao'] ?? 0) ? 'checked' : ''; ?>
                                class="form-checkbox h-5 w-5 text-blue-600 bg-gray-700 border-gray-600">
                            <span class="ml-2 text-gray-300">Promoção</span>
                        </label>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="inline-flex items-center mb-2">
                        <input type="checkbox" id="aplicar-desconto" name="aplicar_desconto" value="1" <?php echo $isEditing && ($produto['desconto'] ?? 0) > 0 ? 'checked' : ''; ?>
                            class="form-checkbox h-5 w-5 text-blue-600 bg-gray-700 border-gray-600">
                        <span class="ml-2 text-sm font-medium text-gray-300">Aplicar Desconto</span>
                    </label>
                    <div id="desconto-container" class="<?php echo $isEditing && ($produto['desconto'] ?? 0) > 0 ? '' : 'opacity-50 pointer-events-none'; ?>">
                        <label for="desconto" class="block text-sm font-medium text-gray-300 mb-2">Desconto (%)</label>
                        <input type="number" id="desconto" name="desconto" step="0.01" min="0" max="100" value="<?php echo $isEditing ? htmlspecialchars($produto['desconto'] ?? 0) : '0'; ?>"
                            class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="mt-2 text-sm text-gray-400">Insira o percentual de desconto (0 a 100).</p>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="imagem" class="block text-sm font-medium text-gray-300 mb-2">Imagem do Produto</label>
                    <?php if ($isEditing && !empty($produto['imagem'])): ?>
                        <div class="mb-4">
                            <p class="text-sm text-gray-400 mb-2">Imagem atual:</p>
                            <img src="../../uploads/produtos/<?php echo htmlspecialchars($produto['imagem']); ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>" class="max-h-40 rounded-md border border-gray-600">
                        </div>
                    <?php endif; ?>
                    <input type="file" id="imagem" name="imagem" accept="image/*"
                        class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-2 text-sm text-gray-400">Formatos aceitos: JPG, PNG, GIF, WEBP. Tamanho máximo: 5MB.</p>
                    <div id="img-preview-container" class="hidden mt-4">
                        <p class="text-sm text-gray-400 mb-2">Nova imagem:</p>
                        <img id="img-preview" class="max-h-40 rounded-md border border-gray-600">
                    </div>
                </div>

                <input type="hidden" id="caixa" name="caixa" value="<?php echo $isEditing ? htmlspecialchars($produto['caixa'] ?? 'G') : 'G'; ?>">

                <div class="flex justify-end space-x-4">
                    <a href="../../visualizar_produtos.php" class="bg-gray-700 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-md transition duration-200">Voltar</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                        <?php echo $isEditing ? 'Atualizar' : 'Adicionar'; ?> Produto
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Exibir preview da imagem escolhida
        document.getElementById('imagem').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validar tamanho do arquivo (5MB = 5 * 1024 * 1024 bytes)
                if (file.size > 5 * 1024 * 1024) {
                    alert('A imagem excede o tamanho máximo de 5MB.');
                    e.target.value = ''; // Limpa o input
                    return;
                }

                const reader = new FileReader();
                const previewContainer = document.getElementById('img-preview-container');
                const preview = document.getElementById('img-preview');

                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewContainer.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });

        // Ativar/desativar input de desconto
        const aplicarDesconto = document.getElementById('aplicar-desconto');
        const descontoContainer = document.getElementById('desconto-container');
        const descontoInput = document.getElementById('desconto');

        aplicarDesconto.addEventListener('change', function() {
            if (this.checked) {
                descontoContainer.classList.remove('opacity-50', 'pointer-events-none');
                descontoInput.disabled = false;
            } else {
                descontoContainer.classList.add('opacity-50', 'pointer-events-none');
                descontoInput.disabled = true;
                descontoInput.value = 0;
            }
        });

        // Validar preço, desconto e imagem no lado do cliente
        document.querySelector('form').addEventListener('submit', function(e) {
            const preco = parseFloat(document.getElementById('preco').value);
            const desconto = parseFloat(document.getElementById('desconto').value);
            const imagemInput = document.getElementById('imagem');
            const file = imagemInput.files[0];

            if (isNaN(preco) || preco <= 0) {
                e.preventDefault();
                alert('Por favor, insira um preço válido maior que 0.');
                return;
            }

            if (aplicarDesconto.checked && (isNaN(desconto) || desconto < 0 || desconto > 100)) {
                e.preventDefault();
                alert('Por favor, insira um desconto válido entre 0 e 100.');
                return;
            }

            // Validar imagem
            if (!file && !<?php echo $isEditing ? '!empty($produto["imagem"])' : 'true'; ?>) {
                e.preventDefault();
                alert('Por favor, selecione uma imagem para o produto.');
                return;
            }

            if (file && file.size > 5 * 1024 * 1024) {
                e.preventDefault();
                alert('A imagem excede o tamanho máximo de 5MB.');
                imagemInput.value = ''; // Limpa o input
                return;
            }
        });
    </script>
    <script>
        // Valores padrão das caixas
        const medidasCaixas = {
            P: { comprimento: 22, largura: 22, altura: 35, peso: 2 },
            M: { comprimento: 37, largura: 37, altura: 35, peso: 2 },
            G: { comprimento: 40, largura: 40, altura: 35, peso: 2 }
        };

        const caixaBtns = document.querySelectorAll('.caixa-btn');
        const comprimentoInput = document.getElementById('comprimento');
        const larguraInput = document.getElementById('largura');
        const alturaInput = document.getElementById('altura');
        const pesoInput = document.getElementById('peso');

        // Função para atualizar o destaque das caixinhas
        function atualizarDestaqueCaixa() {
            const valorAtual = document.getElementById('caixa').value;
            caixaBtns.forEach(btn => {
                if (btn.dataset.caixa === valorAtual) {
                    btn.classList.add('ring', 'ring-blue-400', 'bg-blue-800', 'text-white');
                } else {
                    btn.classList.remove('ring', 'ring-blue-400', 'bg-blue-800', 'text-white');
                }
            });
        }
        // Atualizar ao clicar
        caixaBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const tipo = this.dataset.caixa;
                const medidas = medidasCaixas[tipo];
                comprimentoInput.value = medidas.comprimento;
                larguraInput.value = medidas.largura;
                alturaInput.value = medidas.altura;
                pesoInput.value = medidas.peso;
                document.getElementById('caixa').value = tipo;
                atualizarDestaqueCaixa();
            });
        });
        // Atualizar ao carregar a página
        atualizarDestaqueCaixa();
    </script>
</body>

</html>