<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_GET['token']) || $_GET['token'] !== 'JeovaDeusTodoPoderoso') {
    http_response_code(403);
    exit('Acesso negado');
}

require_once '../config/db.php';
$data = $_POST;

//file_put_contents(__DIR__ . '/log_dados_recebidos.txt', print_r($data, true), FILE_APPEND);

if (!isset($data['leads']['status'][0])) {
    http_response_code(400);
    exit('Lead não detectado');
}

$lead = $data['leads']['status'][0];

$etapa_entrada = 84070027;
if ((int)$lead['status_id'] !== $etapa_entrada || (int)$lead['old_status_id'] === $etapa_entrada) {
    http_response_code(200);
    exit('Etapa ignorada');
}

$campos = array(
    'nome_paciente' => 1181824,
    'telefone' => 203274,
    'procedimento_id' => 1194274,
    'procedimento_nome' => 1194276,
    'profissional_id' => 1194268,
    'profissional_nome' => 1194270,
    'unidade_nome' => 1194016,
    'unidade_id' => 1195154,
    'data' => 1194284,
    'hora' => 1194286,
    'convenio_id' => 1195134,
    'convenio_nome' => 1195152
);

function getCampo($campos_array, $field_id) {
    foreach ($campos_array as $campo) {
        if ($campo['id'] == $field_id && isset($campo['values'][0]['value'])) {
            return $campo['values'][0]['value'];
        }
    }
    return null;
}

$custom_fields = isset($lead['custom_fields']) ? $lead['custom_fields'] : array();

$paciente_nome = getCampo($custom_fields, $campos['nome_paciente']);
$telefone = getCampo($custom_fields, $campos['telefone']);
$procedimento_id = getCampo($custom_fields, $campos['procedimento_id']);
$procedimento_nome = getCampo($custom_fields, $campos['procedimento_nome']);
$profissional_id = getCampo($custom_fields, $campos['profissional_id']);
$profissional_nome = getCampo($custom_fields, $campos['profissional_nome']);
$unidade_nome = getCampo($custom_fields, $campos['unidade_nome']);
$unidade_id = getCampo($custom_fields, $campos['unidade_id']);
$convenio_id = getCampo($custom_fields, $campos['convenio_id']);
$convenio_nome = getCampo($custom_fields, $campos['convenio_nome']);

$data_raw = getCampo($custom_fields, $campos['data']);
$hora_raw = getCampo($custom_fields, $campos['hora']);

$data = null;
$hora = null;

if (is_numeric($data_raw)) {
    $data = gmdate('Y-m-d', intval($data_raw));
}
if (is_numeric($hora_raw)) {
    $hora = gmdate('H:i:s', intval($hora_raw));
}

$lead_id = isset($lead['id']) ? $lead['id'] : 0;
$status_id = isset($lead['status_id']) ? $lead['status_id'] : 0;
$pipeline_id = isset($lead['pipeline_id']) ? $lead['pipeline_id'] : 0;
$responsavel_id = isset($lead['responsible_user_id']) ? $lead['responsible_user_id'] : 0;

$stmt = $pdo->prepare("INSERT INTO agendamentos (
    paciente_nome, paciente_telefone, data, hora, unidade_id,
    procedimento_id, medico_id, convenio_id, numero_cartao_lead, numero_etapa_funil, funil_id, responsavel_id
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->execute(array(
    $paciente_nome, $telefone, $data, $hora, $unidade_id, 
    $procedimento_id,  $profissional_id, $convenio_id, $lead_id, $status_id, $pipeline_id, $responsavel_id
));

http_response_code(200);
echo 'OK';

