<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.html");
    exit;
}

require_once '../../config/db.php';

// Consulta ao banco de dados
$sql = "SELECT 
            a.paciente_nome,
            a.data,
            a.hora,
            a.status,
            a.criado_em AS solicitado_em,
            m.nome AS medico,
            p.nome AS procedimento,
            u.nome AS unidade,
            c.nome AS convenio
        FROM agendamentos a
        LEFT JOIN medicos m ON a.medico_id = m.id
        LEFT JOIN procedimentos p ON a.procedimento_id = p.id
        LEFT JOIN unidades u ON a.unidade_id = u.id
        LEFT JOIN convenios c ON a.convenio_id = c.id
        ORDER BY a.data DESC, a.hora ASC
        LIMIT 0,100";

$stmt = $pdo->query($sql);
$agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupando por data
function agruparPorData($agendamentos)
{
    $agrupados = [];
    foreach ($agendamentos as $ag) {
        $agrupados[$ag['data']][] = $ag;
    }
    krsort($agrupados);
    return $agrupados;
}
$ag_por_data = agruparPorData($agendamentos);

ob_start();

$pendentes = array_filter($agendamentos, function ($a) {
    return $a['status'] === 'pendente';
});

$hoje = date('Y-m-d');
$agendamentos_hoje = array_filter($agendamentos, function ($a) use ($hoje) {
    return $a['data'] === $hoje;
});

$ontem = date('Y-m-d', strtotime('-1 day'));
$agendamentos_ontem = array_filter($agendamentos, function ($a) use ($ontem) {
    return $a['data'] === $ontem;
});

?>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
    <div class="bg-gray-800 p-4 rounded-lg text-center">
        <p class="text-teal-400 text-lg font-semibold">
            <?= count($pendentes) ?>
        </p>
        <p>Pré-agendamentos pendentes</p>
    </div>
    <div class="bg-gray-800 p-4 rounded-lg text-center">
        <p class="text-teal-400 text-lg font-semibold">
            <?= count($agendamentos_hoje) ?>
        </p>
        <p>Hoje</p>
    </div>
    <div class="bg-gray-800 p-4 rounded-lg text-center">
        <p class="text-teal-400 text-lg font-semibold">
            <?= count($agendamentos_ontem) ?>
        </p>
        <p>Ontem</p>
    </div>
</div>

<?php foreach ($ag_por_data as $data => $lista): ?>
    <h2 class="sticky-top text-xl font-bold text-white py-2"><?= date('d/m/Y', strtotime($data)) ?></h2>

    <?php foreach ($lista as $ag): ?>
        <?php $resolvido = in_array($ag['status'], ['confirmado', 'cancelado', 'realizado']); ?>
        <div class="bg-gray-800 p-5 mb-4 rounded-xl shadow-md <?= $resolvido ? 'opaco' : '' ?>">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div><strong>Paciente:</strong> <?= htmlspecialchars($ag['paciente_nome']) ?></div>
                <div><strong>Procedimento:</strong> <?= htmlspecialchars($ag['procedimento']) ?></div>
                <div><strong>Médico:</strong> <?= htmlspecialchars($ag['medico']) ?></div>
                <div><strong>Unidade:</strong> <?= htmlspecialchars($ag['unidade']) ?></div>
                <div><strong>Data:</strong> <?= date('d/m/Y', strtotime($ag['data'])) ?></div>
                <div><strong>Hora:</strong> <?= htmlspecialchars($ag['hora']) ?></div>
                <div><strong>Convênio:</strong> <?= htmlspecialchars($ag['convenio']) ?></div>
                <div><strong>Solicitado em:</strong> <?= date('d/m/Y H:i', strtotime($ag['solicitado_em'])) ?></div>
            </div>
            <?php if (!$resolvido): ?>
                <div class="mt-4 flex gap-3">
                    <button class="bg-teal-500 hover:bg-teal-600 text-white px-6 py-2 rounded-lg font-bold">Confirmar</button>
                    <button data-modal-target="motivoModal" data-modal-toggle="motivoModal" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg font-bold">Cancelar</button>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endforeach; ?>

<!-- Modal permanece o mesmo -->
<?php
$conteudo = ob_get_clean();
include_once '../layouts/base.php';
?>