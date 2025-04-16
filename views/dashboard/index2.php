<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.html");
    exit;
}


// Simulação de dados (normalmente vindos do banco)
$pre_agendamentos = [
    [
        'paciente' => 'João Silva',
        'procedimento' => 'Ultrassonografia',
        'medico' => 'Dra. Maria Souza',
        'unidade' => 'Estância',
        'data' => '2025-04-17',
        'hora' => '09:30',
        'convenio' => 'UNIMED',
        'status' => 'pendente',
        'solicitado_em' => '2025-04-15 08:12'
    ],
    [
        'paciente' => 'Ana Beatriz',
        'procedimento' => 'Mamografia',
        'medico' => 'Dr. Ricardo Lima',
        'unidade' => 'Boquim',
        'data' => '2025-04-17',
        'hora' => '11:00',
        'convenio' => 'IPASGO',
        'status' => 'pendente',
        'solicitado_em' => '2025-04-16 09:20'
    ],
    [
        'paciente' => 'Carlos Andrade',
        'procedimento' => 'Consulta Clínica Geral',
        'medico' => 'Dra. Júlia Tavares',
        'unidade' => 'Itabaianinha',
        'data' => '2025-04-16',
        'hora' => '10:00',
        'convenio' => 'Particular',
        'status' => 'confirmado',
        'solicitado_em' => '2025-04-14 13:45'
    ],
    [
        'paciente' => 'Mariana Rocha',
        'procedimento' => 'Exame de Sangue',
        'medico' => 'Enf. Paulo Dias',
        'unidade' => 'Estância',
        'data' => '2025-04-16',
        'hora' => '08:00',
        'convenio' => 'HAPVIDA',
        'status' => 'cancelado',
        'solicitado_em' => '2025-04-13 10:05'
    ]
];

function agruparPorData($agendamentos) {
    $agrupados = [];
    foreach ($agendamentos as $ag) {
        $agrupados[$ag['data']][] = $ag;
    }
    krsort($agrupados);
    return $agrupados;
}
$ag_por_data = agruparPorData($pre_agendamentos);

ob_start();
?>

            <!-- Blocos de Indicadores -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
                <div class="bg-gray-800 p-4 rounded-lg text-center">
                    <p class="text-teal-400 text-lg font-semibold">2</p>
                    <p>Pré-agendamentos pendentes</p>
                </div>
                <div class="bg-gray-800 p-4 rounded-lg text-center">
                    <p class="text-teal-400 text-lg font-semibold">1</p>
                    <p>Hoje</p>
                </div>
                <div class="bg-gray-800 p-4 rounded-lg text-center">
                    <p class="text-teal-400 text-lg font-semibold">1</p>
                    <p>Ontem</p>
                </div>
            </div>

            <!-- Lista de Agendamentos Agrupados por Data -->
            <?php foreach ($ag_por_data as $data => $lista): ?>
                <h2 class="sticky-top text-xl font-bold text-white py-2"><?= date('d/m/Y', strtotime($data)) ?></h2>

                <?php foreach ($lista as $ag): ?>
                    <?php $resolvido = in_array($ag['status'], ['confirmado', 'cancelado', 'realizado']); ?>
                    <div class="bg-gray-800 p-5 mb-4 rounded-xl shadow-md <?= $resolvido ? 'opaco' : '' ?>">
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <div><strong>Paciente:</strong> <?= $ag['paciente'] ?></div>
                            <div><strong>Procedimento:</strong> <?= $ag['procedimento'] ?></div>
                            <div><strong>Médico:</strong> <?= $ag['medico'] ?></div>
                            <div><strong>Unidade:</strong> <?= $ag['unidade'] ?></div>
                            <div><strong>Data:</strong> <?= $ag['data'] ?></div>
                            <div><strong>Hora:</strong> <?= $ag['hora'] ?></div>
                            <div><strong>Convênio:</strong> <?= $ag['convenio'] ?></div>
                            <div><strong>Solicitado em:</strong> <?= $ag['solicitado_em'] ?></div>
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
            <!-- Modal de Cancelamento -->
            <div id="motivoModal" tabindex="-1" aria-hidden="true"
                class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full bg-black bg-opacity-50">
                <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md">
                    <h3 class="text-xl font-bold text-teal-400 mb-4">Motivo do Cancelamento</h3>
                    <select class="bg-gray-700 border border-gray-600 rounded-lg text-white px-4 py-2 w-full mb-4" required>
                        <option value="">Selecione o motivo</option>
                        <option value="nao_autorizado">O convênio não autorizou este tipo de procedimento.</option>
                        <option value="sem_programacao">Não haverá atendimento programado para esta data.</option>
                        <option value="excedeu_limite">Este horário excede o número de agendamentos permitidos.</option>
                        <option value="erro_dados">Identificamos inconsistências nos dados enviados.</option>
                        <option value="agenda_fechada">A agenda deste profissional está temporariamente indisponível.</option>
                        <option value="cancelamento_solicitado">O cancelamento foi solicitado pelo paciente.</option>
                        <option value="outro">Outro motivo interno da clínica.</option>
                    </select>
                    <div class="flex justify-end">
                        <button data-modal-hide="motivoModal"
                            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg mr-2">Fechar</button>
                        <button
                            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">Confirmar Cancelamento</button>
                    </div>
                </div>
            </div>



<?php
$conteudo = ob_get_clean();
include_once '../layouts/base.php';
            

