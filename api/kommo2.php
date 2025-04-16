<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_GET['token']) || $_GET['token'] !== 'JeovaDeusTodoPoderoso') {
    http_response_code(403);
    exit('Acesso negado');
}

require_once '../config/db.php';
// Usar $_POST diretamente pois Kommo envia como application/x-www-form-urlencoded
$data = $_POST;

// Log para depuração
file_put_contents(__DIR__ . '/log_dados_recebidos.txt', print_r($data, true), FILE_APPEND);

// Verifica se veio lead
if (!isset($data['leads']['status'][0])) {
    http_response_code(400);
    exit('Lead não detectado');
}

$lead = $data['leads']['status'][0];

// Agora o resto do código continua normalmente, usando $lead...

$campos = array(
    'nome_paciente' => 1181824,
    'procedimento_id' => 1194274,
    'procedimento_nome' => 1194276,
    'profissional_id' => 1194268,
    'profissional_nome' => 1194270,
    'unidade_nome' => 1194016,
    'data' => 1194284,
    'hora' => 1194286,
    'convenio_nome' => 1195134
);

function getCampo($campos_array, $field_id) {
    foreach ($campos_array as $campo) {
        if ($campo['id'] == $field_id && isset($campo['values'][0]['value'])) {
            return $campo['values'][0]['value'];
        }
    }
    return null;
}

$custom_fields = array();
if (isset($lead['custom_fields'])) {
    $custom_fields = $lead['custom_fields'];
}

$paciente_nome = getCampo($custom_fields, $campos['nome_paciente']);
$procedimento_id = getCampo($custom_fields, $campos['procedimento_id']);
$procedimento_nome = getCampo($custom_fields, $campos['procedimento_nome']);
$profissional_id = getCampo($custom_fields, $campos['profissional_id']);
$profissional_nome = getCampo($custom_fields, $campos['profissional_nome']);
$unidade_nome = getCampo($custom_fields, $campos['unidade_nome']);
$data = getCampo($custom_fields, $campos['data']);
$hora = getCampo($custom_fields, $campos['hora']);
$convenio_nome = getCampo($custom_fields, $campos['convenio_nome']);

$telefone = null;
if (isset($lead['_embedded']) && isset($lead['_embedded']['contacts'][0])) {
    $contato = $lead['_embedded']['contacts'][0];
    if (isset($contato['custom_fields_values'])) {
        foreach ($contato['custom_fields_values'] as $field) {
            if (isset($field['field_code']) && $field['field_code'] == 'PHONE') {
                if (isset($field['values'][0]['value'])) {
                    $telefone = $field['values'][0]['value'];
                }
            }
        }
    }
}

$lead_id = isset($lead['id']) ? $lead['id'] : 0;
$status_id = isset($lead['status_id']) ? $lead['status_id'] : 0;
$pipeline_id = isset($lead['pipeline_id']) ? $lead['pipeline_id'] : 0;
$responsavel_id = isset($lead['responsible_user_id']) ? $lead['responsible_user_id'] : 0;

$stmt = $pdo->prepare("INSERT INTO agendamentos (
    paciente_nome, paciente_telefone, data, hora, unidade_id, procedimento_id,
    medico_id, convenio_id, numero_cartao_lead,
    numero_etapa_funil, funil_id, responsavel_id
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$unidade_id = 1; // valor fixo temporário
$convenio_id = 1; // valor fixo temporário

$stmt->execute(array(
    $paciente_nome, $telefone, $data, $hora, $unidade_id, $procedimento_id,
    $profissional_id, $convenio_id,
    $lead_id, $status_id, $pipeline_id, $responsavel_id
));

http_response_code(200);
echo 'OK';
