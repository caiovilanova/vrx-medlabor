<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_nivel'] !== 'admin') {
    header("Location: ../../login.html");
    exit;
}

ob_start();
?>

<h1 class="text-2xl font-bold text-teal-400 mb-6">Novo Usuário</h1>

<form method="POST" action="../../src/controllers/usuariosController.php" class="max-w-lg">
    <div class="mb-4">
        <label for="nome" class="block text-sm font-medium text-gray-300 mb-1">Nome</label>
        <input type="text" name="nome" id="nome" required
               class="w-full px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white" />
    </div>
    <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-300 mb-1">E-mail</label>
        <input type="email" name="email" id="email" required
               class="w-full px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white" />
    </div>
    <div class="mb-4">
        <label for="senha" class="block text-sm font-medium text-gray-300 mb-1">Senha</label>
        <input type="password" name="senha" id="senha" required
               class="w-full px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white" />
    </div>
    <div class="mb-4">
        <label for="nivel" class="block text-sm font-medium text-gray-300 mb-1">Nível</label>
        <select name="nivel" id="nivel"
                class="w-full px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white">
            <option value="admin">Administrador</option>
            <option value="atendente">Atendente</option>
        </select>
    </div>
    <div class="mb-4">
        <label for="status" class="block text-sm font-medium text-gray-300 mb-1">Status</label>
        <select name="status" id="status"
                class="w-full px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white">
            <option value="ativo">Ativo</option>
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
