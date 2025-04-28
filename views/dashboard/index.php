<?php

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.html");
    exit;
}

require_once '../../config/db.php';

$sql = "SELECT 
            a.id,
            a.numero_cartao_lead,
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
                <div><strong>Convênio:</strong> <?= htmlspecialchars($ag['convenio']) ?></div>
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

<!-- Modal Cancelamento -->
<div id="motivoModal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex justify-center items-center">
    <form id="cancelForm" method="POST" class="bg-gray-800 p-6 rounded-xl w-full max-w-md">
        <input type="hidden" name="id" id="cancelar_id">
        <h2 class="text-xl text-white mb-4">Motivo do cancelamento</h2>
        <select name="motivo_id" class="w-full p-2 rounded bg-gray-700 text-white">
            <option value="1">Paciente desistiu</option>
            <option value="2">Convênio não cobre</option>
            <option value="3">Erro de agendamento</option>
            <option value="4">Outro</option>
        </select>
        <div class="mt-4 text-right">
            <button type="button" onclick="fecharModal()" class="mr-2 px-4 py-2 bg-gray-500 text-white rounded">Fechar</button>
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded">Cancelar Agendamento</button>
        </div>
    </form>
</div>

<script>
    document.querySelectorAll('[data-lead-id][data-action="confirmar"]').forEach(btn => {
        btn.addEventListener('click', async () => {
            const leadId = btn.getAttribute('data-lead-id');
            const card = btn.closest('.shadow-md');

            if (!leadId || !card) return alert('Erro ao identificar o agendamento.');

            const confirmar = confirm('Tem certeza que deseja confirmar esse agendamento?');
            if (!confirmar) return;

            btn.disabled = true;
            btn.textContent = 'Confirmando...';

            try {
                const resposta = await fetch('../../api/kommo_confirmar_agendamento.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        lead_id: leadId
                    })
                });

                const resultado = await resposta.json();

                if (resultado.sucesso) {
                    card.classList.add('opacity-50', 'pointer-events-none'); // opaco e não clicável
                    alert('Agendamento confirmado com sucesso.');
                } else {
                    alert('Erro ao confirmar: ' + (resultado.erro || 'Erro desconhecido'));
                }
            } catch (erro) {
                alert('Falha na solicitação: ' + erro.message);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Confirmar';
            }
        });
    });
</script>


<?php
// Buscando os motivos de cancelamento do banco
$motivos = $pdo->query("SELECT id, descricao FROM motivos_cancelamento ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Modal de Cancelamento -->
<div id="modal-cancelar" class="hidden fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-gray-800 rounded-xl p-6 w-full max-w-md">
        <h2 class="text-xl text-white font-semibold mb-4">Motivo do Cancelamento</h2>
        <form id="form-cancelar">
            <input type="hidden" name="agendamento_id" id="cancelar-id">
            <select name="motivo_id" id="motivo_id" required class="w-full mb-4 p-2 bg-gray-700 text-white rounded">
                <option value="">Selecione um motivo</option>
                <?php foreach ($motivos as $motivo): ?>
                    <option value="<?= $motivo['id'] ?>"><?= htmlspecialchars($motivo['descricao']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="fecharModalCancelar()" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 rounded text-white">Fechar</button>
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 rounded text-white">Cancelar Agendamento</button>
            </div>
        </form>
    </div>
</div>



<script>
    const KOMMO_SECRET_KEY = '<?php getenv("KOMMO_SECRET_KEY") ?>';


    function abrirModalCancelar(id) {
        document.getElementById('cancelar-id').value = id;
        document.getElementById('modal-cancelar').classList.remove('hidden');
    }

    function fecharModalCancelar() {
        document.getElementById('modal-cancelar').classList.add('hidden');
    }

    document.getElementById('form-cancelar').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('cancelar-id').value;
        const motivo = document.getElementById('motivo_id').value;

        fetch('../../api/kommo_cancelar_agendamento.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Authorization': 'Bearer ' + KOMMO_SECRET_KEY

                },

                body: `id=${id}&motivo_id=${motivo}`
            })
            .then(res => res.text())
            .then(res => {
                if (res === 'OK') {
                    const card = document.querySelector(`[data-card-id="${id}"]`);
                    if (card) card.classList.add('opacity-40');
                    fecharModalCancelar();
                } else {
                    alert('Erro ao cancelar: ' + res);
                }
            });
    });
</script>   

<?php
$conteudo = ob_get_clean();
include_once '../layouts/base.php';
?>

