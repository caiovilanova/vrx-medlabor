<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_nivel'] !== 'admin') {
    header("Location: ../../login.html");
    exit;
}

require_once '../../config/db.php';

$sucesso = isset($_GET['sucesso']) ? $_GET['sucesso'] : '';
$erro = isset($_GET['erro']) ? $_GET['erro'] : '';

$stmt = $pdo->query("SELECT id, nome, email, nivel, status FROM usuarios WHERE deleted_at IS NULL ORDER BY nome ASC");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>

<h1 class="text-2xl font-bold text-teal-400 mb-6">Usuários do Sistema</h1>

<?php if ($sucesso): ?>
    <div class="mb-4 p-3 rounded bg-green-700 text-white">
        <?php
        if ($sucesso == '1') echo "Usuário cadastrado com sucesso!";
        elseif ($sucesso == '2') echo "Usuário atualizado com sucesso!";
        elseif ($sucesso == 'inativado') echo "Usuário inativado com sucesso!";
        ?>
    </div>
<?php endif; ?>

<?php if ($erro): ?>
    <div class="mb-4 p-3 rounded bg-red-700 text-white">
        <?php
        if ($erro == 'campos') echo "Preencha todos os campos corretamente.";
        elseif ($erro == 'id') echo "ID inválido para edição.";
        elseif ($erro == 'banco') echo "Erro ao processar no banco de dados.";
        elseif ($erro == 'naoencontrado') echo "Usuário não encontrado.";
        ?>
    </div>
<?php endif; ?>

<a href="novo.php" class="mb-4 inline-block bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-lg">
    + Novo Usuário
</a>

<div class="overflow-x-auto">
    <table class="w-full text-left text-sm bg-gray-800 rounded-lg">
        <thead class="text-gray-300 uppercase bg-gray-700">
            <tr>
                <th class="px-6 py-3">Nome</th>
                <th class="px-6 py-3">E-mail</th>
                <th class="px-6 py-3">Nível</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3 text-right">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $user): ?>
            <tr class="border-b border-gray-700 hover:bg-gray-700">
                <td class="px-6 py-4"><?php echo htmlspecialchars($user['nome']); ?></td>
                <td class="px-6 py-4"><?php echo htmlspecialchars($user['email']); ?></td>
                <td class="px-6 py-4"><?php echo ucfirst($user['nivel']); ?></td>
                <td class="px-6 py-4"><?php echo ucfirst($user['status']); ?></td>
                <td class="px-6 py-4 text-right flex justify-end gap-2">
                    <a href="editar.php?id=<?php echo $user['id']; ?>" class="text-blue-400 hover:text-blue-600">✏️</a>
                    <a href="inativar.php?id=<?php echo $user['id']; ?>" class="text-red-400 hover:text-red-600">❌</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
$conteudo = ob_get_clean();
include_once '../layouts/base.php';
