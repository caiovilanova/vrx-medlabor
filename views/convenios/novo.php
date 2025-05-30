<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.html");
    exit;
}

ob_start();
?>

<h1 class="text-2xl font-bold text-teal-400 mb-6">Novo Convênio</h1>

<form method="POST" action="../../src/controllers/conveniosController.php" class="max-w-lg">
    <div class="mb-4">
        <label for="nome" class="block text-sm font-medium text-gray-300 mb-1">Nome do Convênio</label>
        <input type="text" name="nome" id="nome" required
               class="w-full px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white focus:outline-none focus:ring-2 focus:ring-teal-500" />
    </div>
    <div class="mb-4">
        <label for="status" class="block text-sm font-medium text-gray-300 mb-1">Status</label>
        <select name="status" id="status"
                class="w-full px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white">
            <option value="ativo" selected>Ativo</option>
            <option value="inativo">Inativo</option>
        </select>
    </div>
    <div class="flex justify-between mt-6">
        <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Voltar</a>
        <button type="submit" name="acao" value="criar"
                class="bg-teal-500 hover:bg-teal-600 text-white px-6 py-2 rounded-lg font-bold">Salvar</button>
    </div>
</form>

<?php
$conteudo = ob_get_clean();
include_once '../layouts/base.php';