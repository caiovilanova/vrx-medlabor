<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>VRX Medlabor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@1.6.5/dist/flowbite.min.css" rel="stylesheet" />
    <style>
        html,
        body {
            height: 100%;
        }

        body {
            display: flex;
            flex-direction: column;
            background-color: #191C1F;
        }

        .opaco {
            opacity: 0.5;
        }
    </style>
</head>

<body class="text-white flex flex-col min-h-screen">
    <div class="flex-1 flex">
        <!-- Sidebar -->
        <aside class="hidden md:flex flex-col w-64 bg-gray-900 px-6 py-8">
            <h2 class="text-teal-400 text-2xl font-bold mb-6">VRX Medlabor</h2>
            <nav class="flex flex-col space-y-3">
                <a href="https://vrxtecnologia.com.br/medlabor/views/dashboard/" class="p-2 hover:text-teal-300">Início</a>
                <a href="https://vrxtecnologia.com.br/medlabor/views/convenios/" class="p-2 hover:text-teal-300">Convênios</a>
                <a href="https://vrxtecnologia.com.br/medlabor/views/medicos/" class="p-2 hover:text-teal-300">Médicos</a>
                <a href="https://vrxtecnologia.com.br/medlabor/views/procedimentos/" class="p-2 hover:text-teal-300">Procedimentos</a>
                <a href="https://vrxtecnologia.com.br/medlabor/views/unidades/" class="p-2 hover:text-teal-300">Unidades</a>
                <a href="https://vrxtecnologia.com.br/medlabor/views/usuarios/" class="p-2 hover:text-teal-300">Usuários</a>
            </nav>
            <div class="mt-auto pt-6 border-t border-gray-700">
                <a class="text-teal-400 hover:underline" href="https://vrxtecnologia.com.br/medlabor/views/usuarios/editar_perfil.php">👤 <?php echo isset($_SESSION['usuario_nome']) ? $_SESSION['usuario_nome'] : 'Usuário'; ?></a>
            </div>
        </aside>

        <!-- Conteúdo principal -->
        <main class="flex-1 p-6">
            <!-- Botão mobile visível somente em telas pequenas -->
            <div class="md:hidden flex justify-between items-center mb-4">
                <button data-drawer-target="mobile-sidebar" data-drawer-show="mobile-sidebar" aria-controls="mobile-sidebar"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gray-700 rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    ☰ Menu
                </button>
            </div>

            <?php echo isset($conteudo) ? $conteudo : '' ?>

        </main>
    </div>


    <!-- menu mobile -->
    <!-- Menu lateral responsivo (mobile) -->
    <div id="mobile-sidebar"
        class="fixed top-0 left-0 z-40 w-64 h-screen p-4 overflow-y-auto transition-transform -translate-x-full bg-gray-900"
        tabindex="-1" aria-labelledby="sidebar-label">
        <h2 class="text-teal-400 text-xl font-bold mb-6" id="sidebar-label">Menu</h2>
        <button type="button" data-drawer-hide="mobile-sidebar" aria-controls="mobile-sidebar"
            class="absolute top-2 right-2 text-gray-400 hover:text-white">
            ✕
        </button>
        <nav class="flex flex-col space-y-3 mt-8">
            <a href="https://vrxtecnologia.com.br/medlabor/views/dashboard/" class="p-2 hover:text-teal-300">Início</a>
            <a href="https://vrxtecnologia.com.br/medlabor/views/convenios/" class="p-2 hover:text-teal-300">Convênios</a>
            <a href="https://vrxtecnologia.com.br/medlabor/views/medicos/" class="p-2 hover:text-teal-300">Médicos</a>
            <a href="https://vrxtecnologia.com.br/medlabor/views/procedimentos/" class="p-2 hover:text-teal-300">Procedimentos</a>
            <a href="https://vrxtecnologia.com.br/medlabor/views/unidades/" class="p-2 hover:text-teal-300">Unidades</a>
            <a href="https://vrxtecnologia.com.br/medlabor/views/usuarios/" class="p-2 hover:text-teal-300">Usuários</a>
        </nav>
    </div>


    <!-- Rodapé -->
    <footer class="text-sm text-gray-400 text-center py-4">
        Suporte VRX - suporte@vrxtecnologia.com.br - 79 99894-2864
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@1.6.5/dist/flowbite.min.js"></script>
</body>

</html>