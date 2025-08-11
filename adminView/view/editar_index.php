<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once '../config/dbconnect.php';
require_once '../controller/Configuracoes/ConfigController.php';
require_once '../controller/Configuracoes/BannerController.php';

$jsonPath = '../config_site.json';
$configController = new ConfigController($jsonPath);
$bannerController = new BannerController($conn);

$mensagem = "";
$config = $configController->getConfig();
$banners = $bannerController->getBanners();
$sobreMidia = $config->pagina_inicial->sobre->midia ?? '';

// Processar mensagem de sucesso/erro do banner
if (isset($_SESSION['banner_message'])) {
    $mensagem = $_SESSION['banner_message'];
    unset($_SESSION['banner_message']);
}
// Remover lógica de mensagem baseada em sessão para configurações:
// if (isset($_SESSION['config_message'])) { ... }
// A mensagem será exibida apenas via JS após o AJAX.

// Determinar aba ativa com base na sessão ou padrão para banners
$activeTab = $_SESSION['active_config_tab'] ?? 'banners';

function extractYouTubeId($url) {
    if (!empty($url)) {
        if (preg_match('/youtube\.com\/watch\?v=([^\&\?\/]+)/', $url, $match)) {
            return $match[1];
        } elseif (preg_match('/youtu\.be\/([^\&\?\/]+)/', $url, $match)) {
            return $match[1];
        }
    }
    return '';
}

function extractVimeoId($url) {
    if (!empty($url) && preg_match('/vimeo\.com\/([0-9]+)/', $url, $match)) {
        return $match[1];
    }
    return '';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Página Inicial</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <style>
        .tab-content .tab-pane {
            display: none;
        }
        .tab-content .tab-pane.active {
            display: block;
        }
        .nav-tabs .nav-link.active {
            color: #fff;
            border-bottom-color: #4f46e5;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 50;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.7);
        }
        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body class="bg-gray-900 text-gray-100 font-sans">
    <div class="container mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-100">Editar Página Inicial</h1>
                <p class="text-gray-400">Gerencie o conteúdo da sua página inicial</p>
            </div>
            <a href="../pages/index.php" class="inline-flex items-center px-4 py-2 bg-indigo-800 text-white rounded-lg hover:bg-indigo-900 transition duration-300">
                <i class="fas fa-arrow-left mr-2"></i> Voltar ao Painel
            </a>
        </div>

        <?php if (!empty($mensagem)): ?>
            <div id="alertaMensagem" class="mb-6 p-4 rounded-lg <?php echo strpos($mensagem, 'sucesso') !== false ? 'bg-green-700' : 'bg-red-700'; ?> text-white text-center text-lg font-semibold shadow-lg transition-all duration-500">
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
            <script>
                setTimeout(function() {
                    const alerta = document.getElementById('alertaMensagem');
                    if (alerta) alerta.style.display = 'none';
                }, 4000);
            </script>
        <?php endif; ?>

        <ul class="flex border-b border-indigo-800 mb-6" id="myTab" role="tablist">
            <li class="mr-1" role="presentation">
                <button class="inline-block px-4 py-2 text-gray-400 hover:text-gray-100 border-b-2 border-transparent hover:border-indigo-600 transition duration-300 <?php echo $activeTab === 'banners' ? 'text-gray-100 border-indigo-600' : ''; ?>" 
                        id="banners-tab" 
                        onclick="switchTab('banners')">
                    <i class="fas fa-images mr-2"></i> Banners
                </button>
            </li>
            <li class="mr-1" role="presentation">
                <button class="inline-block px-4 py-2 text-gray-400 hover:text-gray-100 border-b-2 border-transparent hover:border-indigo-600 transition duration-300 <?php echo $activeTab === 'config' ? 'text-gray-100 border-indigo-600' : ''; ?>" 
                        id="config-tab" 
                        onclick="switchTab('config')">
                    <i class="fas fa-cog mr-2"></i> Configurações
                </button>
            </li>
        </ul>

        <div class="tab-content" id="myTabContent">
            <!-- Aba de Banners -->
            <div class="tab-pane <?php echo $activeTab === 'banners' ? 'active' : ''; ?>" id="banners" role="tabpanel" aria-labelledby="banners-tab">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-semibold text-gray-100">Gerenciar Banners</h3>
                    <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-indigo-800 text-white rounded-lg hover:bg-indigo-900 transition duration-300">
                        <i class="fas fa-plus mr-2"></i> Adicionar Banner
                    </button>
                </div>

                <div class="bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="bg-indigo-900 text-gray-200">
                                        <th class="p-4">Imagem</th>
                                        <th class="p-4">Título</th>
                                        <th class="p-4">Descrição</th>
                                        <th class="p-4">Link</th>
                                        <th class="p-4">Ordem</th>
                                        <th class="p-4">Status</th>
                                        <th class="p-4">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($banners)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-8">
                                                <div class="flex flex-col items-center">
                                                    <i class="fas fa-images text-5xl text-gray-500 mb-3"></i>
                                                    <p class="text-gray-400">Nenhum banner cadastrado</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($banners as $banner): ?>
                                            <tr class="hover:bg-indigo-900 transition duration-200">
                                                <td class="p-4">
                                                    <img src="../uploads/carousel/<?= htmlspecialchars($banner['imagem']) ?>" 
                                                         alt="Banner" class="rounded-lg" width="80" height="60">
                                                </td>
                                                <td class="p-4"><?= htmlspecialchars($banner['titulo'] ?? 'Sem título') ?></td>
                                                <td class="p-4 truncate max-w-xs">
                                                    <?= htmlspecialchars($banner['descricao'] ?? 'Sem descrição') ?>
                                                </td>
                                                <td class="p-4">
                                                    <?php if (!empty($banner['link'])): ?>
                                                        <a href="<?= htmlspecialchars($banner['link']) ?>" 
                                                           target="_blank" class="text-indigo-400 hover:text-indigo-300">
                                                            Ver Link
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-gray-400">Sem link</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="p-4"><?= $banner['ordem'] ?></td>
                                                <td class="p-4">
                                                    <span class="inline-block px-3 py-1 rounded-full text-sm <?= $banner['status'] ? 'bg-green-700' : 'bg-red-700' ?> text-white">
                                                        <?= $banner['status'] ? 'Ativo' : 'Inativo' ?>
                                                    </span>
                                                </td>
                                                <td class="p-4 flex space-x-2">
                                                    <button onclick="editBanner(<?= $banner['id'] ?>)" 
                                                            class="p-2 text-indigo-400 hover:bg-indigo-800 rounded-full transition duration-200">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button onclick="toggleStatus(<?= $banner['id'] ?>)" 
                                                            class="p-2 text-yellow-400 hover:bg-indigo-800 rounded-full transition duration-200">
                                                        <i class="fas fa-exchange-alt"></i>
                                                    </button>
                                                    <button onclick="deleteBanner(<?= $banner['id'] ?>)" 
                                                            class="p-2 text-red-400 hover:bg-indigo-800 rounded-full transition duration-200">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aba de Configurações -->
            <div class="tab-pane <?php echo $activeTab === 'config' ? 'active' : ''; ?>" id="config" role="tabpanel" aria-labelledby="config-tab">
                <form method="POST" action="../pages/Configuracoes/processa_configuracao.php" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <input type="hidden" name="form_type" value="site_config">

                    <div class="space-y-6">
                        <!-- Seção Sobre -->
                        <div>
                            <div class="bg-gray-800 rounded-lg shadow-lg">
                                <div class="p-6">
                                    <div class="mb-6">
                                        <label class="block text-gray-200 mb-2">Upload de Mídia</label>
                                        <div class="border-2 border-dashed border-indigo-800 rounded-lg p-6 text-center hover:border-indigo-600 transition-colors cursor-pointer">
                                            <input type="file" name="midia_upload" id="midia_upload" class="hidden" accept="image/*,video/mp4,video/webm,video/ogg" onchange="previewMedia(this)">
                                            <label for="midia_upload" class="cursor-pointer w-full block">
                                                <div class="mx-auto text-gray-400 mb-2">
                                                    <i class="fas fa-cloud-upload-alt text-5xl"></i>
                                                </div>
                                                <span class="text-sm text-gray-400">Clique para fazer upload ou arraste um arquivo</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mb-6">
                                        <label for="midia_url" class="block text-gray-200 mb-2">URL da Mídia</label>
                                        <input type="text" name="midia_url" id="midia_url" value="<?= htmlspecialchars($sobreMidia) ?>"
                                            class="w-full p-3 bg-gray-800 border border-indigo-800 rounded-lg text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-600"
                                            oninput="previewMedia(this)"
                                            placeholder="https://exemplo.com/imagem.jpg ou URL do YouTube/Vimeo">
                                    </div>

                                    <div>
                                        <label class="block text-gray-200 mb-2">Pré-visualização</label>
                                        <div id="media-preview" class="bg-gray-800 rounded-lg p-4 flex items-center justify-center min-h-[200px]">
                                            <?php if (!empty($sobreMidia)) : ?>
                                                <?php if (strpos($sobreMidia, '.mp4') !== false || strpos($sobreMidia, '.webm') !== false || strpos($sobreMidia, '.ogg') !== false) : ?>
                                                    <video controls class="max-w-full max-h-[300px] rounded-lg">
                                                        <source src="../uploads/inicio/<?= htmlspecialchars($sobreMidia) ?>" type="video/mp4">
                                                    </video>
                                                <?php elseif (strpos($sobreMidia, 'youtube.com') !== false || strpos($sobreMidia, 'youtu.be') !== false) : ?>
                                                    <div class="relative w-full" style="padding-bottom: 56.25%;">
                                                        <iframe class="absolute top-0 left-0 w-full h-full" src="https://www.youtube.com/embed/<?php echo htmlspecialchars(extractYouTubeId($sobreMidia)); ?>" 
                                                                frameborder="0" allowfullscreen></iframe>
                                                    </div>
                                                <?php elseif (strpos($sobreMidia, 'vimeo.com') !== false) : ?>
                                                    <div class="relative w-full" style="padding-bottom: 56.25%;">
                                                        <iframe class="absolute top-0 left-0 w-full h-full" src="https://player.vimeo.com/video/<?php echo htmlspecialchars(extractVimeoId($sobreMidia)); ?>" 
                                                                frameborder="0" allowfullscreen></iframe>
                                                    </div>
                                                <?php else : ?>
                                                    <img src="../uploads/inicio/<?= htmlspecialchars($sobreMidia) ?>" alt="Preview" class="max-w-full max-h-[300px] rounded-lg">
                                                <?php endif; ?>
                                            <?php else : ?>
                                                <div class="text-center text-gray-400">
                                                    <i class="fas fa-image text-5xl mb-3"></i>
                                                    <p>Nenhuma mídia selecionada</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div id="format-warning" class="text-red-400 mt-2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Seção Contato -->
                        <div>
                            <div class="bg-gray-800 rounded-lg shadow-lg">
                                <div class="p-6">
                                    <h4 class="text-xl font-semibold text-gray-100 mb-4">Informações de Contato</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="whatsapp" class="block text-gray-200 mb-2">WhatsApp</label>
                                            <div class="flex">
                                                <span class="inline-flex items-center px-3 bg-gray-700 border border-indigo-800 rounded-l-lg text-gray-400">
                                                    <i class="fab fa-whatsapp"></i>
                                                </span>
                                                <input type="text" name="whatsapp" id="whatsapp"
                                                    value="<?= htmlspecialchars($config->contato->whatsapp ?? '') ?>"
                                                    class="w-full p-3 bg-gray-800 border border-indigo-800 rounded-r-lg text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-600"
                                                    placeholder="Ex: +5511987654321">
                                            </div>
                                        </div>

                                        <div>
                                            <label for="instagram" class="block text-gray-200 mb-2">Instagram</label>
                                            <div class="flex">
                                                <span class="inline-flex items-center px-3 bg-gray-700 border border-indigo-800 rounded-l-lg text-gray-400">
                                                    <i class="fab fa-instagram"></i>
                                                </span>
                                                <input type="text" name="instagram" id="instagram"
                                                    value="<?= htmlspecialchars($config->contato->instagram ?? '') ?>"
                                                    class="w-full p-3 bg-gray-800 border border-indigo-800 rounded-r-lg text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-600"
                                                    placeholder="Ex: @sua_loja">
                                            </div>
                                        </div>

                                        <div>
                                            <label for="facebook" class="block text-gray-200 mb-2">Facebook</label>
                                            <div class="flex">
                                                <span class="inline-flex items-center px-3 bg-gray-700 border border-indigo-800 rounded-l-lg text-gray-400">
                                                    <i class="fab fa-facebook"></i>
                                                </span>
                                                <input type="text" name="facebook" id="facebook"
                                                    value="<?= htmlspecialchars($config->contato->facebook ?? '') ?>"
                                                    class="w-full p-3 bg-gray-800 border border-indigo-800 rounded-r-lg text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-600"
                                                    placeholder="Ex: https://facebook.com/sua_loja">
                                            </div>
                                        </div>

                                        <div>
                                            <label for="email" class="block text-gray-200 mb-2">Email</label>
                                            <div class="flex">
                                                <span class="inline-flex items-center px-3 bg-gray-700 border border-indigo-800 rounded-l-lg text-gray-400">
                                                    <i class="fas fa-envelope"></i>
                                                </span>
                                                <input type="email" name="email" id="email"
                                                    value="<?= htmlspecialchars($config->contato->email ?? '') ?>"
                                                    class="w-full p-3 bg-gray-800 border border-indigo-800 rounded-r-lg text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-600"
                                                    placeholder="Ex: contato@sualoja.com">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botões de Ação -->
                        <div class="flex justify-end space-x-4">
                            <button type="reset" class="inline-flex items-center px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition duration-300">
                                <i class="fas fa-undo mr-2"></i> Limpar
                            </button>
                            <button type="submit" id="submitConfigBtn" class="inline-flex items-center px-4 py-2 bg-indigo-800 text-white rounded-lg hover:bg-indigo-900 transition duration-300">
                                <i class="fas fa-save mr-2"></i> Salvar Alterações
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal para Adicionar/Editar Banner -->
        <div id="bannerModal" class="modal">
            <div class="modal-dialog modal-lg w-full max-w-4xl">
                <div class="modal-content bg-gray-800 border border-indigo-800 rounded-lg">
                    <div class="modal-header border-b border-indigo-800 p-4 flex justify-between items-center">
                        <h5 class="modal-title text-gray-100 text-xl font-semibold" id="modalTitle">Adicionar Banner</h5>
                        <button type="button" class="text-gray-400 hover:text-gray-100 text-xl" onclick="closeModal()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="bannerForm" action="../pages/Configuracoes/processa_banners.php" method="POST" enctype="multipart/form-data" class="modal-body p-6">
                        <input type="hidden" id="bannerId" name="id">
                        <input type="hidden" id="existingImage" name="existingImage">
                        <input type="hidden" id="action" name="action" value="save_banner">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="mb-6">
                                <label class="block text-gray-200 mb-2">Imagem do Banner</label>
                                <div class="border-2 border-dashed border-indigo-800 rounded-lg p-6 text-center hover:border-indigo-600 transition-colors cursor-pointer">
                                    <input type="file" id="imageUpload" name="image" class="hidden" accept="image/*" onchange="previewImage(this)">
                                    <label for="imageUpload" class="cursor-pointer w-full block">
                                        <div class="mx-auto text-gray-400 mb-2">
                                            <i class="fas fa-cloud-upload-alt text-5xl"></i>
                                        </div>
                                        <span class="text-sm text-gray-400">Clique para fazer upload de uma imagem</span>
                                    </label>
                                </div>
                                <div id="imagePreview" class="mt-4 hidden text-center">
                                    <img id="previewImg" class="img-fluid rounded-lg max-h-48">
                                </div>
                            </div>
                            <div class="mb-6 flex flex-col gap-6">
                                <div>
                                    <label for="titulo" class="block text-gray-200 mb-2">Título (opcional)</label>
                                    <input type="text" id="titulo" name="titulo" 
                                           class="w-full p-3 bg-gray-800 border border-indigo-800 rounded-lg text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-600"
                                           placeholder="Digite o título do banner">
                                </div>
                                <div>
                                    <label for="descricao" class="block text-gray-200 mb-2">Descrição (opcional)</label>
                                    <textarea id="descricao" name="descricao" rows="3"
                                              class="w-full p-3 bg-gray-800 border border-indigo-800 rounded-lg text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-600"
                                              placeholder="Digite a descrição do banner"></textarea>
                                </div>
                                <div>
                                    <label for="link" class="block text-gray-200 mb-2">Link (opcional)</label>
                                    <input type="url" id="link" name="link" 
                                           class="w-full p-3 bg-gray-800 border border-indigo-800 rounded-lg text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-600"
                                           placeholder="https://exemplo.com">
                                </div>
                                <div>
                                    <label for="ordem" class="block text-gray-200 mb-2">Ordem de Exibição</label>
                                    <input type="number" id="ordem" name="ordem" min="0" value="0"
                                           class="w-full p-3 bg-gray-800 border border-indigo-800 rounded-lg text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-600"
                                           placeholder="0">
                                    <p class="text-sm text-gray-400 mt-1">Menor número aparece primeiro</p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-t border-indigo-800 p-4 flex justify-end space-x-3">
                            <button type="button" class="inline-flex items-center px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition duration-300" onclick="closeModal()">
                                Cancelar
                            </button>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-800 text-white rounded-lg hover:bg-indigo-900 transition duration-300">
                                Salvar Banner
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Formulários ocultos para ações de banners -->
        <form id="toggleStatusForm" action="../pages/Configuracoes/processa_banners.php" method="POST" class="hidden">
            <input type="hidden" name="action" value="toggle_status">
            <input type="hidden" name="id" id="toggleStatusId">
        </form>

        <form id="deleteBannerForm" action="../pages/Configuracoes/processa_banners.php" method="POST" class="hidden">
            <input type="hidden" name="action" value="delete_banner">
            <input type="hidden" name="id" id="deleteBannerId">
        </form>

        <form id="tabForm" action="" method="POST" class="hidden">
            <input type="hidden" name="active_tab" id="activeTabInput">
        </form>

        <!-- Scripts -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            // Variáveis globais
            let isEditMode = false;
            const bannerModal = document.getElementById('bannerModal');

            function switchTab(tabName) {
                // Esconder todas as abas
                document.querySelectorAll('.tab-pane').forEach(tab => {
                    tab.classList.remove('active');
                });
                
                // Mostrar a aba selecionada
                document.getElementById(tabName).classList.add('active');
                
                // Atualizar o estilo dos botões de navegação
                document.querySelectorAll('#myTab button').forEach(btn => {
                    btn.classList.remove('text-gray-100', 'border-indigo-600');
                    btn.classList.add('text-gray-400');
                });
                
                // Destacar o botão ativo
                document.getElementById(`${tabName}-tab`).classList.add('text-gray-100', 'border-indigo-600');
                document.getElementById(`${tabName}-tab`).classList.remove('text-gray-400');
                
                // Salvar a aba ativa no servidor via AJAX
                $.ajax({
                    url: 'salvar_aba.php',
                    method: 'POST',
                    data: { active_tab: tabName },
                    success: function(response) {
                        console.log('Aba salva com sucesso');
                    }
                });
            }

            function openAddModal() {
                isEditMode = false;
                document.getElementById('modalTitle').textContent = 'Adicionar Banner';
                document.getElementById('bannerForm').reset();
                document.getElementById('bannerId').value = '';
                document.getElementById('existingImage').value = '';
                document.getElementById('imagePreview').classList.add('hidden');
                document.getElementById('imageUpload').value = '';
                bannerModal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }

            function closeModal() {
                bannerModal.classList.remove('show');
                document.body.style.overflow = 'auto';
            }

            function previewImage(input) {
                const preview = document.getElementById('imagePreview');
                const previewImg = document.getElementById('previewImg');

                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        preview.classList.remove('hidden');
                    }
                    reader.readAsDataURL(input.files[0]);
                } else {
                    preview.classList.add('hidden');
                }
            }

            function editBanner(id) {
                isEditMode = true;
                document.getElementById('modalTitle').textContent = 'Editar Banner';

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '../pages/Configuracoes/processa_banners.php';
                form.style.display = 'none';

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'get_banner';
                form.appendChild(actionInput);

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = id;
                form.appendChild(idInput);

                document.body.appendChild(form);
                form.submit();
            }

            function toggleStatus(id) {
                document.getElementById('toggleStatusId').value = id;
                document.getElementById('toggleStatusForm').submit();
            }

            function deleteBanner(id) {
                if (confirm('Tem certeza que deseja excluir este banner?')) {
                    document.getElementById('deleteBannerId').value = id;
                    document.getElementById('deleteBannerForm').submit();
                }
            }

            function previewMedia(input) {
                const preview = document.getElementById('media-preview');
                const warning = document.getElementById('format-warning');
                warning.textContent = '';

                preview.innerHTML = '';

                if (input.files && input.files[0]) {
                    const file = input.files[0];
                    const fileType = file.type;

                    if (fileType.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            preview.innerHTML = `<img src="${e.target.result}" alt="Preview" class="max-w-full max-h-[300px] rounded-lg">`;
                        }
                        reader.readAsDataURL(file);
                    } else if (fileType.startsWith('video/')) {
                        const url = URL.createObjectURL(file);
                        preview.innerHTML = `<video controls class="max-w-full max-h-[300px] rounded-lg"><source src="${url}" type="${fileType}"></video>`;
                    } else {
                        preview.innerHTML = `<div class="text-center text-gray-400">
                            <i class="fas fa-exclamation-triangle text-3xl mb-2"></i>
                            <p>Formato não suportado</p>
                        </div>`;
                        warning.textContent = "Formato de arquivo não suportado. Use JPG, PNG, GIF ou MP4.";
                    }
                } else if (input.id === 'midia_url' && input.value) {
                    const url = input.value;

                    if (url.includes('youtube.com') || url.includes('youtu.be')) {
                        const videoId = extractYouTubeId(url);
                        if (videoId) {
                            preview.innerHTML = `
                                <div class="relative w-full" style="padding-bottom: 56.25%;">
                                    <iframe class="absolute top-0 left-0 w-full h-full" src="https://www.youtube.com/embed/${videoId}" 
                                            frameborder="0" allowfullscreen></iframe>
                                </div>
                            `;
                            return;
                        }
                    }

                    if (url.includes('vimeo.com')) {
                        const videoId = extractVimeoId(url);
                        if (videoId) {
                            preview.innerHTML = `
                                <div class="relative w-full" style="padding-bottom: 56.25%;">
                                    <iframe class="absolute top-0 left-0 w-full h-full" src="https://player.vimeo.com/video/${videoId}" 
                                            frameborder="0" allowfullscreen></iframe>
                                </div>
                            `;
                            return;
                        }
                    }

                    const extension = url.split('.').pop()?.toLowerCase() || '';
                    const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    const videoExtensions = ['mp4', 'webm', 'ogg'];

                    if (imageExtensions.includes(extension)) {
                        preview.innerHTML = `<img src="${url}" alt="Preview" class="max-w-full max-h-[300px] rounded-lg">`;
                    } else if (videoExtensions.includes(extension)) {
                        preview.innerHTML = `<video controls class="max-w-full max-h-[300px] rounded-lg"><source src="${url}"></video>`;
                    } else {
                        warning.textContent = "Formato de URL não suportado.";
                    }
                }
            }

            // Preencher o modal com dados de edição, se existirem
            <?php if (isset($_SESSION['edit_banner'])): ?>
                document.addEventListener('DOMContentLoaded', function() {
                    isEditMode = true;
                    document.getElementById('modalTitle').textContent = 'Editar Banner';
                    const banner = <?php echo json_encode($_SESSION['edit_banner']); ?>;
                    document.getElementById('bannerId').value = banner.id || '';
                    document.getElementById('titulo').value = banner.titulo || '';
                    document.getElementById('descricao').value = banner.descricao || '';
                    document.getElementById('link').value = banner.link || '';
                    document.getElementById('ordem').value = banner.ordem || '0';
                    document.getElementById('existingImage').value = banner.imagem || '';
                    if (banner.imagem) {
                        document.getElementById('previewImg').src = `../uploads/carousel/${banner.imagem}`;
                        document.getElementById('imagePreview').classList.remove('hidden');
                    }
                    bannerModal.classList.add('show');
                    document.body.style.overflow = 'hidden';
                    <?php unset($_SESSION['edit_banner']); ?>
                });
            <?php endif; ?>

            // Fechar modal ao clicar fora
            window.addEventListener('click', function(event) {
                if (event.target === bannerModal) {
                    closeModal();
                }
            });



            // Interceptar submit do formulário de configurações
            document.getElementById('submitConfigBtn').addEventListener('click', function(e) {
                e.preventDefault();
                
                const formData = new FormData(document.querySelector('.tab-pane.active form'));
                const submitBtn = document.getElementById('submitConfigBtn');
                const originalText = submitBtn.innerHTML;
                
                // Desabilitar botão e mostrar loading
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Salvando...';
                
                fetch('../pages/Configuracoes/processa_configuracao.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // Reabilitar botão
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    
                    if (data.status === 'success') {
                        // Exibir mensagem de sucesso
                        showMessage(data.message || 'Configurações salvas com sucesso!', 'success');
                        
                        // Recarregar a página para atualizar os dados
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        // Exibir mensagem de erro
                        showMessage(data.message || 'Erro ao salvar configurações. Tente novamente.', 'error');
                    }
                })
                .catch(error => {
                    // Reabilitar botão
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    
                    // Exibir mensagem de erro
                    showMessage('Erro ao salvar configurações. Tente novamente.', 'error');
                    console.error('Erro:', error);
                });
            });

            function showMessage(message, type) {
                // Remover mensagem existente se houver
                const existingAlert = document.getElementById('alertaMensagem');
                if (existingAlert) {
                    existingAlert.remove();
                }
                
                // Criar nova mensagem
                const alertDiv = document.createElement('div');
                alertDiv.id = 'alertaMensagem';
                alertDiv.className = `mb-6 p-4 rounded-lg ${type === 'success' ? 'bg-green-700' : 'bg-red-700'} text-white text-center text-lg font-semibold shadow-lg transition-all duration-500`;
                alertDiv.textContent = message;
                
                // Inserir após o cabeçalho
                const header = document.querySelector('.container .flex.justify-between');
                header.parentNode.insertBefore(alertDiv, header.nextSibling);
                
                // Auto-remover após 4 segundos
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.style.display = 'none';
                    }
                }, 4000);
            }

            // Manter aba ativa após reload
            const tabButtons = document.querySelectorAll('#myTab button');
            tabButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    localStorage.setItem('adminViewActiveTab', this.id.replace('-tab',''));
                });
            });

            document.addEventListener('DOMContentLoaded', function() {
                const savedTab = localStorage.getItem('adminViewActiveTab');
                if (savedTab) {
                    switchTab(savedTab);
                }
                // Se sinalizado, reabrir o modal de banner após reload
                if (sessionStorage.getItem('reabrirBannerModal') === '1') {
                    sessionStorage.removeItem('reabrirBannerModal');
                    openAddModal();
                }
            });
        </script>
    </div>
</body>
</html>