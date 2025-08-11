<!-- Tailwind CSS -->
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

<!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link href="../assets/css/style.css" rel="stylesheet">

<div class="sidebar z-50 shadow-xl bg-gradient-to-b from-gray-900 via-gray-800 to-gray-900 rounded-r-2xl border-r-4 border-yellow-400 min-h-screen w-64 fixed top-0 left-0 flex flex-col justify-between">
    <div>
        <div class="side-header text-center py-6">
            <img src="../assets/images/logo.png" alt="Logo" class="mx-auto mb-2 rounded-full shadow-lg" style="width:60px;height:60px;object-fit:cover;">
            <h5 class="text-yellow-400 font-bold text-lg">Olá, Administrador</h5>
        </div>
        <hr class="border-yellow-400 mb-4">
        <a href="javascript:void(0)" class="closebtn absolute top-2 right-2 text-2xl text-yellow-400 hover:text-white transition" onclick="toggleSidebar()">×</a>
        <nav class="flex flex-col gap-2 px-4">
            <a href="#" onclick="showDashboard()" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-200 hover:bg-yellow-400 hover:text-gray-900 font-semibold transition">
                <i class="fa fa-home text-xl"></i> Painel
            </a>
            <a href="#" onclick="loadPage('../visualizar_clientes.php')" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-200 hover:bg-yellow-400 hover:text-gray-900 font-semibold transition">
                <i class="fa fa-user-friends text-xl"></i> Clientes
            </a>
            <a href="#" onclick="loadPage('../visualizar_pedidos.php')" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-200 hover:bg-yellow-400 hover:text-gray-900 font-semibold transition">
                <i class="fa fa-receipt text-xl"></i> Pedidos
            </a>
            <a href="#" onclick="loadPage('../visualizar_rastreio.php')" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-200 hover:bg-yellow-400 hover:text-gray-900 font-semibold transition">
                <i class="fa-solid fa-truck-fast text-xl"></i> Rastreio
            </a>
            <a href="../emitir_nfe.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-200 hover:bg-yellow-400 hover:text-gray-900 font-semibold transition">
                <i class="fa-solid fa-file-invoice text-xl"></i> Notas Fiscais (Betel)
            </a>
            <a href="../visualizar_configuracao.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-200 hover:bg-yellow-400 hover:text-gray-900 font-semibold transition">
                <i class="fa-solid fa-cog text-xl"></i> Configurações
            </a>
            <!-- Dropdown de Edição do Site -->
            <div class="relative">
                <button id="btnEditarSite" type="button" class="w-full text-left flex items-center justify-between px-4 py-3 rounded-lg text-gray-200 hover:bg-yellow-400 hover:text-gray-900 font-semibold transition" onclick="toggleDropdownMenu()">
                    <span class="flex items-center gap-3"><i class="fa-solid fa-edit text-xl"></i> Editar Site</span>
                    <i class="fa-solid fa-chevron-down transition-transform" id="chevronIcon"></i>
                </button>
                <div id="dropdownMenu" class="absolute left-0 w-full bg-gray-900 shadow-lg rounded-md hidden overflow-y-auto max-h-60 z-50">
                    <a href="#" onclick="loadPage('../view/editar_index.php')" class="block px-4 py-2 text-gray-300 hover:bg-yellow-400 hover:text-gray-900 rounded">Editar Página Inicial</a>
                </div>
            </div>
        </nav>
    </div>
    <div class="text-center py-4 text-xs text-gray-500">
        &copy; <?php echo date('Y'); ?> Cristais Gold Lar<br>Todos os direitos reservados
    </div>
</div>

<style>
    /* Scrollbar estilizada para navegadores WebKit (Chrome, Edge, Safari) */
    ::-webkit-scrollbar {
        width: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #1a1a2e;
        /* Fundo escuro indigo */
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: #3f3f7d;
        /* Tom de índigo mais escuro */
        border-radius: 10px;
        transition: background 0.3s ease;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #5757a3;
        /* Um tom um pouco mais claro ao passar o mouse */
    }
</style>

<!-- Script para abrir e fechar o dropdown -->
<script>
    function toggleDropdownMenu() {
        const dropdown = document.getElementById("dropdownMenu");
        const chevron = document.getElementById("chevronIcon");
        if (dropdown.classList.contains("hidden")) {
            dropdown.classList.remove("hidden");
            chevron.style.transform = "rotate(180deg)";
        } else {
            dropdown.classList.add("hidden");
            chevron.style.transform = "rotate(0deg)";
        }
    }

    function loadPage(page) {
        $.ajax({
            url: page,
            type: "GET",
            success: function(response) {
                $("#defaultContent").hide();
                $(".allContent-section").html(response).show();
            },
            error: function(xhr, status, error) {
                console.error("Erro ao carregar a página:", error);
                $(".allContent-section").html("<p class='text-danger'>Erro ao carregar a página.</p>");
            }
        });
    }

    function showDashboard() {
        $("#defaultContent").show();
        $(".allContent-section").empty().hide();
    }

    // Função para abrir/fechar a sidebar
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar.style.display === 'none' || getComputedStyle(sidebar).display === 'none') {
            sidebar.style.display = 'flex';
        } else {
            sidebar.style.display = 'none';
        }
    }
</script>