<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.html");
    exit;
}
require_once '../../config/db.php';

$tipo_filtro = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$sql = "SELECT * FROM procedimentos WHERE nome!='' ";
$params = [];

if (!empty($tipo_filtro)) {
    $sql .= " AND tipo = ?";
    $params[] = $tipo_filtro;
}
$sql .= " ORDER BY nome ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$procedimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<h1 class="text-2xl font-bold text-teal-400 mb-6">Procedimentos</h1>

<form method="GET" class="mb-4">
    <label for="tipo" class="text-white">Filtrar por tipo:</label>
    <select name="tipo" id="tipo" onchange="this.form.submit()" class="bg-gray-700 border border-gray-600 text-white px-4 py-2 rounded-lg">
        <option value="">Todos</option>
        <option value="exame" <?php if($tipo_filtro === 'exame') echo 'selected'; ?>>Exame</option>
        <option value="consulta" <?php if($tipo_filtro === 'consulta') echo 'selected'; ?>>Consulta</option>
        <option value="procedimento" <?php if($tipo_filtro === 'procedimento') echo 'selected'; ?>>Procedimento</option>
    </select>
</form>

<table class="w-full text-left text-sm bg-gray-800 rounded-lg">
    <thead class="text-gray-300 uppercase bg-gray-700">
        <tr>
            <th class="px-6 py-3">Nome</th>
            <th class="px-6 py-3">Tipo</th>
            <th class="px-6 py-3">Preço (R$)</th>
            <th class="px-6 py-3">Agendamento</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($procedimentos as $p): ?>
        <tr class="border-b border-gray-700 hover:bg-gray-700">
            <td class="px-6 py-4"><?php echo htmlspecialchars($p['nome']); ?></td>
            <td class="px-6 py-4"><?php echo ucfirst($p['tipo']); ?></td>
            <td class="px-6 py-4"><?php echo number_format($p['preco_particular'], 2, ',', '.'); ?></td>
            <td class="px-6 py-4">
                <?php if ($p['precisa_agendamento'] === 'sim'): ?>
                    <span class="text-teal-400" title="Requer agendamento">📅</span>
                <?php else: ?>
                    <span class="text-gray-400" title="Atendimento imediato">✔️</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
$conteudo = ob_get_clean();
include_once '../layouts/base.php';
