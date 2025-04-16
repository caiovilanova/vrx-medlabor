<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.html");
    exit;
}

require_once '../../config/db.php';

$stmt = $pdo->query("SELECT u.id, u.nome, u.horario_funcionamento, c.nome AS cidade, c.uf_id
                     FROM unidades u
                     JOIN cidades c ON u.cidade_id = c.id
                     ORDER BY u.nome ASC");
$unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>

<h1 class="text-2xl font-bold text-teal-400 mb-6">Unidades de Atendimento</h1>

<a href="novo.php" class="mb-4 inline-block bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-lg">
    + Nova Unidade
</a>

<div class="overflow-x-auto">
    <table class="w-full text-left text-sm bg-gray-800 rounded-lg">
        <thead class="text-gray-300 uppercase bg-gray-700">
            <tr>
                <th class="px-6 py-3">Nome</th>
                <th class="px-6 py-3">Cidade</th>
                <th class="px-6 py-3">Horário Funcionamento</th>
                <th class="px-6 py-3 text-right">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($unidades as $u): ?>
            <tr class="border-b border-gray-700 hover:bg-gray-700">
                <td class="px-6 py-4"><?php echo htmlspecialchars($u['nome']); ?></td>
                <td class="px-6 py-4"><?php echo $u['cidade']; ?></td>
                <td class="px-6 py-4"><?php echo $u['horario_funcionamento']; ?></td>
                <td class="px-6 py-4 text-right flex justify-end gap-2">
                    <a href="editar.php?id=<?php echo $u['id']; ?>" class="text-blue-400 hover:text-blue-600">✏️</a>
                    <a href="inativar.php?id=<?php echo $u['id']; ?>" class="text-red-400 hover:text-red-600">❌</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
$conteudo = ob_get_clean();
include_once '../layouts/base.php';
