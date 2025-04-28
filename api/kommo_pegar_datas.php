<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Proteção por token simples
if (!isset($_GET['token']) || $_GET['token'] !== 'JeovaDeusTodoPoderoso') {
    http_response_code(403);
    exit('Acesso negado');
}

require_once '../config/db.php';

// Recebendo dados
$data = $_POST;

if (!isset($data['leads']['status'][0])) {
    http_response_code(400);
    exit('Lead não detectado');
}

$lead = $data['leads']['status'][0];

$campos = array(
    'procedimento_id' => 1194274,
    'profissional_id' => 1194268
);

function getCampo($campos_array, $field_id) {
    foreach ($campos_array as $campo) {
        if ($campo['id'] == $field_id && isset($campo['values'][0]['value'])) {
            return $campo['values'][0]['value'];
        }
    }
    return null;
}

$custom_fields = isset($lead['custom_fields']) ? $lead['custom_fields'] : [];

$procedimento_id = getCampo($custom_fields, $campos['procedimento_id']);
$profissional_id = getCampo($custom_fields, $campos['profissional_id']);

if (!$procedimento_id) {
    http_response_code(400);
    exit('Procedimento não informado');
}

if (!$profissional_id) {
    $profissional_id = 1; // Definido 1 como padrão se vazio
}

// Buscar disponibilidades
$stmt = $pdo->prepare("SELECT dia_semana FROM disponibilidades WHERE procedimento_id = ? AND medico_id = ? AND ativo = 1");
$stmt->execute([$procedimento_id, $profissional_id]);
$disponibilidades = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($disponibilidades)) {
    http_response_code(404);
    exit('Nenhuma disponibilidade encontrada');
}

// Gera as próximas 3 datas possíveis
$hoje = new DateTime();
$datas_sugeridas = [];

while (count($datas_sugeridas) < 3) {
    $diaSemanaHoje = (int) $hoje->format('w'); // 0 = domingo, 1 = segunda...
    if (in_array($diaSemanaHoje, $disponibilidades)) {
        $datas_sugeridas[] = $hoje->format('d/m/Y');
    }
    $hoje->modify('+1 day');
}

// Atualizar no Kommo
$lead_id = $lead['id'];

$payload = [
    'custom_fields_values' => [
        [
            'field_id' => 1195726, // Agendamento_OPCAO_1
            'values' => [['value' => isset($datas_sugeridas[0]) ? $datas_sugeridas[0] : '']]
        ],
        [
            'field_id' => 1195728, // Agendamento_OPCAO_2
            'values' => [['value' => isset($datas_sugeridas[1]) ? $datas_sugeridas[1] : '']]
        ],
        [
            'field_id' => 1195730, // Agendamento_OPCAO_3
            'values' => [['value' => isset($datas_sugeridas[2]) ? $datas_sugeridas[2] : '']]
        ]
    ]
];

// Lê token do Kommo
$dotenvPath = realpath(__DIR__ . '/../.env');
if (file_exists($dotenvPath)) {
    $lines = file($dotenvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

$accessToken = getenv('KOMMO_ACCESS_TOKEN');
if (!$accessToken) {
    http_response_code(500);
    echo json_encode(['erro' => 'Token de acesso do Kommo não definido']);
    exit;
}

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
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode >= 200 && $httpCode < 300) {
    echo json_encode(['sucesso' => true, 'datas' => $datas_sugeridas]);
} else {
    http_response_code($httpCode);
    echo json_encode(['erro' => 'Falha ao atualizar o lead', 'status' => $httpCode, 'resposta' => $response]);
}
