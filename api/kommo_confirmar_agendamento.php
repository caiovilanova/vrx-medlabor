<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

// Verifica método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
    exit;
}

// Carregar variáveis do .env
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

// Lê token de acesso do Kommo
$accessToken = getenv('KOMMO_ACCESS_TOKEN');
if (!$accessToken) {
    http_response_code(500);
    echo json_encode(['erro' => 'Token de acesso do Kommo não definido']);
    exit;
}

// Lê o corpo JSON da requisição
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['erro' => 'Requisição malformada']);
    exit;
}

$leadId = isset($input['lead_id']) ? (int)$input['lead_id'] : 0;
$statusIdConfirmado = 84070031;

if ($leadId <= 0) {
    http_response_code(400);
    echo json_encode(['erro' => 'ID do lead inválido']);
    exit;
}

// Monta payload
$payload = [
    [
        'id' => $leadId,
        'status_id' => $statusIdConfirmado
    ]
];

// Envia PATCH para Kommo
$url = 'https://medlabor.amocrm.com/api/v4/leads';
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

// Trata a resposta
if ($httpCode >= 200 && $httpCode < 300) {
    // Atualizar o status do agendamento no banco local
    require_once '../config/db.php'; // Garante que a conexão esteja disponível
    $stmt = $pdo->prepare("UPDATE agendamentos SET status = 'confirmado' WHERE numero_cartao_lead = ?");
    $stmt->execute([$leadId]);

    http_response_code(200);
    echo json_encode(['sucesso' => true]);
}
 else {
    http_response_code($httpCode);
    echo json_encode([
        'erro' => 'Falha ao atualizar lead',
        'status' => $httpCode,
        'resposta' => $response
    ]);
}
