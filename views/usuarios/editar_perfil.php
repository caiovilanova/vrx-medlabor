<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.html");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$usuario_nome = $_SESSION['usuario_nome'];

ob_start();
?>

<h1 class="text-2xl font-bold text-teal-400 mb-6">Editar Minha Senha</h1>

<form method="POST" action="../../src/controllers/usuariosController.php" class="max-w-lg">
    <input type="hidden" name="id" value="<?php echo $usuario_id; ?>" />
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-300 mb-1">Usuário</label>
        <input type="text" value="<?php echo htmlspecialchars($usuario_nome); ?>" disabled
               class="w-full px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 text-gray-400" />
    </div>
    <div class="mb-4">
        <label for="senha" class="block text-sm font-medium text-gray-300 mb-1">Nova Senha</label>
        <input type="password" name="senha" id="senha" required
               class="w-full px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white" />
    </div>
    <div class="mb-4">
        <label for="senha_confirmacao" class="block text-sm font-medium text-gray-300 mb-1">Confirmar Nova Senha</label>
        <input type="password" name="senha_confirmacao" id="senha_confirmacao" required
               class="w-full px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white" />
    </div>
    <div class="flex justify-between mt-6">
        <a href="../dashboard/" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Voltar</a>
        <button type="submit" name="acao" value="alterar_senha"
                class="bg-teal-500 hover:bg-teal-600 text-white px-6 py-2 rounded-lg font-bold">Atualizar Senha</button>
    </div>
</form>

<?php
$conteudo = ob_get_clean();
include_once '../layouts/base.php';
