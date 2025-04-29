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

$pipeline_id = isset($lead['pipeline_id']) ? (int)$lead['pipeline_id'] : 0;
$status_id = isset($lead['status_id']) ? (int)$lead['status_id'] : 0;
$old_status_id = isset($lead['old_status_id']) ? (int)$lead['old_status_id'] : 0;

// Inclui funções separadas
require_once __DIR__ . '/../config/db.php';
require_once 'acoes/incluir_pre_agendamento.php';
require_once 'acoes/sugerir_datas_para_lead.php';

// Verifica qual ação tomar

if ($pipeline_id == 10960867 && $status_id == 84070027) {
    // Conferência para evitar reprocessamento na mesma etapa
    if ($old_status_id != $status_id) {
        incluirPreAgendamento($lead, $pdo);
    }

} elseif ($pipeline_id == 10016751 && $status_id == 76970051) {
    if ($old_status_id != $status_id) {
        sugerirDatasParaLead($lead, $pdo);
    }
}

// Se nenhuma condição bater, apenas responde OK sem fazer nada
http_response_code(200);
echo json_encode(['status' => 'ok']);
