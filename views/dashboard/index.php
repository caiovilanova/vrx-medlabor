<?php

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.html");
    exit;
}

require_once '../../config/db.php';

$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'pendentes';
$where = '';

switch ($filtro) {
    case 'confirmados':
        $where = "WHERE a.status = 'confirmado'";
        break;
    case 'cancelados':
        $where = "WHERE a.status = 'cancelado'";
        break;
    default:
        $where = "WHERE a.status = 'pendente'";
        break;
}

$sql = "SELECT 
            a.id,
            a.numero_cartao_lead,
            a.paciente_nome,
            a.paciente_telefone,
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
        $where
        ORDER BY a.data ASC, a.hora ASC
        LIMIT 0,100";

$stmt = $pdo->query($sql);
$agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

function agruparPorData($agendamentos)
{
    $agrupados = [];
    foreach ($agendamentos as $ag) {
        $agrupados[$ag['data']][] = $ag;
    }
    ksort($agrupados);
    return $agrupados;
}
$ag_por_data = agruparPorData($agendamentos);

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

// Consulta leads aguardando atendimento humano no Kommo
$fila_whatsapp = 0;
try {
    $url = "https://medlabor.amocrm.com/api/v4/leads?filter[status_id]=79093771&filter[pipeline_id]=10314407";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . getenv('KOMMO_ACCESS_TOKEN')
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response, true);
    $fila_whatsapp = isset($data['_total_items']) ? $data['_total_items'] : 0;
} catch (Exception $e) {
    $fila_whatsapp = 0;
}

ob_start();
?>

<div class="grid grid-cols-1 sm:grid-cols-4 gap-6 mb-6">
    <div class="bg-gray-800 p-4 rounded-lg text-center">
        <p class="text-teal-400 text-lg font-semibold"><?= count($pendentes) ?></p>
        <p>Pré-agendamentos pendentes</p>
    </div>
    <div class="bg-gray-800 p-4 rounded-lg text-center">
        <p class="text-teal-400 text-lg font-semibold"><?= count($agendamentos_hoje) ?></p>
        <p>Hoje</p>
    </div>
    <div class="bg-gray-800 p-4 rounded-lg text-center">
        <p class="text-teal-400 text-lg font-semibold"><?= count($agendamentos_ontem) ?></p>
        <p>Ontem</p>
    </div>
    <div class="bg-gray-800 p-4 rounded-lg text-center">
        <p class="text-red-400 text-lg font-semibold flex items-center justify-center gap-1">
            <i class="fab fa-whatsapp"></i> <?= $fila_whatsapp ?>
        </p>
        <p>Na fila de atendimento</p>
    </div>
</div>

<div class="mb-4 flex gap-4">
    <a href="?filtro=pendentes" class="px-4 py-2 rounded bg-teal-600 text-white">Pendentes</a>
    <a href="?filtro=confirmados" class="px-4 py-2 rounded bg-blue-600 text-white">Confirmados</a>
    <a href="?filtro=cancelados" class="px-4 py-2 rounded bg-red-600 text-white">Cancelados</a>
</div>

<?php foreach ($ag_por_data as $data => $lista): ?>
    <h2 class="sticky-top text-xl font-bold text-white py-2"><?= date('d/m/Y', strtotime($data)) ?></h2>
    <?php foreach ($lista as $ag): ?>
        <?php
        $resolvido = in_array($ag['status'], ['confirmado', 'cancelado', 'realizado']);
        $borderClass = $ag['status'] === 'cancelado' ? 'border-red-500' : ($ag['status'] === 'realizado' ? 'border-green-500' : 'border-teal-500');
        ?>
        <div class="bg-gray-800 p-5 mb-4 rounded-xl shadow-md border-l-4 <?= $borderClass ?> <?= $resolvido ? 'opacity-40' : '' ?>" data-card-id="<?= $ag['id'] ?>">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xl font-bold text-white">
                    <?= htmlspecialchars($ag['paciente_nome']) ?>
                </h3>
                <span class="text-sm text-gray-400">#<?= $ag['numero_cartao_lead'] ?></span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                <div><strong>Procedimento:</strong> <?= htmlspecialchars($ag['procedimento']) ?></div>
                <div><strong>Médico:</strong> <?= htmlspecialchars($ag['medico']) ?></div>
                <div><strong>Unidade:</strong> <?= htmlspecialchars($ag['unidade']) ?></div>
                <div><strong>Data:</strong> <?= date('d/m/Y', strtotime($ag['data'])) ?></div>
                <div><strong>Hora:</strong> <?= htmlspecialchars($ag['hora']) ?></div>
                <div><strong>Telefone:</strong> <?= htmlspecialchars($ag['paciente_telefone']) ?></div>
                <div class="text-xs text-gray-400">
                    <strong>Solicitado em:</strong> <?= date('d/m/Y H:i', strtotime($ag['solicitado_em'])) ?>
                </div>
            </div>

            <?php if (!$resolvido): ?>
                <div class="mt-4 flex gap-3">
                    <button
                        class="bg-teal-500 hover:bg-teal-600 text-white px-6 py-2 rounded-lg font-bold"
                        data-lead-id="<?= htmlspecialchars($ag['numero_cartao_lead']) ?>"
                        data-action="confirmar">
                        <i class="fas fa-check"></i> Confirmar
                    </button>

                    <button onclick="abrirModalCancelar(<?= $ag['id'] ?>)" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg font-bold">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endforeach; ?>

<?php
$conteudo = ob_get_clean();
include_once '../layouts/base.php';
?>