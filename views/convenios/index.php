<?php

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.html");
    exit;
}

ob_start(); // Inicia o buffer

require_once '../../config/db.php';

$stmt = $pdo->query("SELECT * FROM convenios ORDER BY nome ASC");
$convenios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sucesso = isset($_GET['sucesso']) ? $_GET['sucesso'] : '';
$erro = isset($_GET['erro']) ? $_GET['erro'] : '';



?>

<!-- Conteúdo da página dentro do layout -->
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-teal-400">Convênios</h1>
    <a href="novo.php" class="flex items-center bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-lg">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2"
            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
        </svg>
        Novo Convênio
    </a>
</div>

<?php if ($sucesso): ?>
    <div class="mb-4 p-3 rounded bg-green-700 text-white">
        <?php
        if ($sucesso == '1') echo "Convênio cadastrado com sucesso!";
        elseif ($sucesso == '2') echo "Convênio atualizado com sucesso!";
        elseif ($sucesso == 'inativado') echo "Convênio inativado com sucesso!";
        ?>
    </div>
<?php endif; ?>

<?php if ($erro): ?>
    <div class="mb-4 p-3 rounded bg-red-700 text-white">
        <?php
        if ($erro == 'dados_invalidos') echo "Preencha corretamente os dados.";
        elseif ($erro == 'bd') echo "Erro ao processar no banco de dados.";
        elseif ($erro == 'id_invalido') echo "ID inválido informado.";
        ?>
    </div>
<?php endif; ?>

<table class="w-full text-left text-sm bg-gray-800 rounded-lg">
    <thead class="text-gray-300 uppercase bg-gray-700">
        <tr>
            <th class="px-6 py-3">Nome</th>
            <th class="px-6 py-3">Status</th>
            <th class="px-6 py-3 text-right">Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($convenios as $conv): ?>
            <tr class="border-b border-gray-700 hover:bg-gray-700">
                <td class="px-6 py-4"><?= htmlspecialchars($conv['nome']) ?></td>
                <td class="px-6 py-4"><?= ucfirst($conv['status']) ?></td>
                <td class="px-6 py-4 text-right flex justify-end gap-2">
                    <a href="editar.php?id=<?= $conv['id'] ?>" class="text-blue-400 hover:text-blue-600" title="Editar">
                        ✏️
                    </a>
                    <a href="inativar.php?id=<?= $conv['id'] ?>" class="text-red-400 hover:text-red-600" title="Inativar">
                        ❌
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>


<?php

$conteudo = ob_get_clean(); // Captura o conteúdo e encerra o buffer
include_once '../layouts/base.php'; // Inclui o layout base passando $conteudo

?>