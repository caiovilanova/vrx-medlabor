<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php';

// Verifica se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método não permitido');
}

// Carrega variáveis do .env
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || !strpos($line, '=')) continue;
        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

// (ATENÇÃO) Não exige mais Authorization para chamadas do Dashboard

// Recebe dados
$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$motivo_id = isset($_POST['motivo_id']) ? (int) $_POST['motivo_id'] : 0;

if ($id <= 0 || $motivo_id <= 0) {
    http_response_code(400);
    echo 'Dados inválidos';
    exit;
}

try {
    // Atualiza agendamento no banco
    $stmt = $pdo->prepare("UPDATE agendamentos SET status = 'cancelado', motivo_cancelamento_id = ? WHERE id = ?");
    $stmt->execute([$motivo_id, $id]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo 'Agendamento não encontrado';
        exit;
    }

    // Agora busca o texto do motivo
    $stmtMotivo = $pdo->prepare("SELECT descricao FROM motivos_cancelamento WHERE id = ?");
    $stmtMotivo->execute([$motivo_id]);
    $motivo = $stmtMotivo->fetchColumn();

    if (!$motivo) {
        http_response_code(404);
        echo 'Motivo de cancelamento não encontrado';
        exit;
    }

    // Descobrir o número do cartão lead (lead_id) relacionado
    $stmtLead = $pdo->prepare("SELECT numero_cartao_lead FROM agendamentos WHERE id = ?");
    $stmtLead->execute([$id]);
    $lead_id = $stmtLead->fetchColumn();

    if (!$lead_id) {
        http_response_code(404);
        echo 'Número do cartão de lead não encontrado';
        exit;
    }

    // Atualiza o campo Agendamento_CANCELAMENTO_MOTIVO no Kommo
    $accessToken = getenv('KOMMO_ACCESS_TOKEN');
    if (!$accessToken) {
        http_response_code(500);
        echo 'Token do Kommo não definido';
        exit;
    }

    $payload = [
        'custom_fields_values' => [
            [
                'field_id' => 1195168, // Campo Agendamento_CANCELAMENTO_MOTIVO
                'values' => [
                    ['value' => $motivo]
                ]
            ]
        ]
    ];

    $url = 'https://medlabor.amocrm.com/api/v4/leads/' . $lead_id;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        file_put_contents(__DIR__ . '/erro_cancelar_agendamento.log', curl_error($ch) . "\n", FILE_APPEND);
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        http_response_code(200);
        echo 'OK';
    } else {
        file_put_contents(__DIR__ . '/erro_cancelar_agendamento.log', $response . "\n", FILE_APPEND);
        http_response_code($httpCode);
        echo 'Falha ao atualizar lead no Kommo';
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo 'Erro no banco de dados: ' . $e->getMessage();
}
?>
