<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../public/login.php");
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
        'convenio' => 'UNIMED'
    ],
    [
        'paciente' => 'Ana Beatriz',
        'procedimento' => 'Mamografia',
        'medico' => 'Dr. Ricardo Lima',
        'unidade' => 'Boquim',
        'data' => '2025-04-17',
        'hora' => '11:00',
        'convenio' => 'IPASGO'
    ]
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard - VRX Medlabor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@1.6.5/dist/flowbite.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #191C1F;
        }
    </style>
</head>
<body class="text-white">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl text-teal-400 font-bold mb-6">Painel de Pré-Agendamentos</h1>

        <?php foreach ($pre_agendamentos as $ag): ?>
        <div class="bg-gray-800 p-5 mb-5 rounded-xl shadow-md">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div><strong>Paciente:</strong> <?= $ag['paciente'] ?></div>
                <div><strong>Procedimento:</strong> <?= $ag['procedimento'] ?></div>
                <div><strong>Médico:</strong> <?= $ag['medico'] ?></div>
                <div><strong>Unidade:</strong> <?= $ag['unidade'] ?></div>
                <div><strong>Data:</strong> <?= $ag['data'] ?></div>
                <div><strong>Hora:</strong> <?= $ag['hora'] ?></div>
                <div><strong>Convênio:</strong> <?= $ag['convenio'] ?></div>
            </div>
            <div class="mt-4 flex flex-col md:flex-row gap-3">
                <button class="bg-teal-500 hover:bg-teal-600 text-white px-6 py-2 rounded-lg font-bold w-full md:w-auto">Confirmar</button>
                <div class="flex flex-col w-full md:w-2/3">
                    <select class="bg-gray-700 border border-gray-600 rounded-lg text-white px-4 py-2 mb-2" required>
                        <option value="">Motivo do cancelamento</option>
                        <option value="nao_coberto">Convênio não inclui esse procedimento/consulta</option>
                        <option value="sem_atendimento">Atualização na programação - não haverá atendimento nesse dia</option>
                        <option value="excedeu_limite">Horário excedeu a quantidade de agendamentos permitidos</option>
                        <option value="outro">Outro motivo</option>
                    </select>
                    <textarea rows="2" placeholder="Descreva se necessário..." class="bg-gray-700 border border-gray-600 rounded-lg text-white px-4 py-2 resize-none"></textarea>
                </div>
                <button class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg font-bold w-full md:w-auto">Cancelar</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
