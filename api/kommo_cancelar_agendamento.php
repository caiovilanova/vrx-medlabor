<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php';

// Verifica método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método não permitido');
}

// Carrega .env manualmente (se necessário)
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || !strpos($line, '=')) continue;
        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

// Protege com token
$headers = array_change_key_case(getallheaders(), CASE_LOWER);
$auth_token = isset($headers['authorization']) ? trim($headers['authorization']) : '';
$token_esperado = 'Bearer ' . getenv('KOMMO_SECRET_KEY');

if ($auth_token !== $token_esperado) {
    http_response_code(403);
    echo 'Token inválido';
    exit;
}

// Captura os dados
$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$motivo_id = isset($_POST['motivo_id']) ? (int) $_POST['motivo_id'] : 0;

if ($id <= 0 || $motivo_id <= 0) {
    http_response_code(400);
    echo 'Dados inválidos';
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE agendamentos SET status = 'cancelado', motivo_cancelamento_id = ? WHERE id = ?");
    $stmt->execute([$motivo_id, $id]);

    if ($stmt->rowCount() > 0) {
        http_response_code(200);
        echo 'OK';
    } else {
        http_response_code(404);
        echo 'Agendamento não encontrado';
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Erro no banco de dados: ' . $e->getMessage();
}
