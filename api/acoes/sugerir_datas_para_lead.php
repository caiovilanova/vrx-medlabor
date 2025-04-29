<?php

function sugerirDatasParaLead($lead, $pdo)
{
    // Mapear IDs de campos
    $campos = array(
        'procedimento_id' => 1194274,
        'profissional_id' => 1194268
    );

    function getCampoInterno($campos_array, $field_id)
    {
        foreach ($campos_array as $campo) {
            if ($campo['id'] == $field_id && isset($campo['values'][0]['value'])) {
                return $campo['values'][0]['value'];
            }
        }
        return null;
    }

    $custom_fields = isset($lead['custom_fields']) ? $lead['custom_fields'] : array();

    $procedimento_id = getCampoInterno($custom_fields, $campos['procedimento_id']);
    $profissional_id = getCampoInterno($custom_fields, $campos['profissional_id']);

    if (!$procedimento_id) {
        file_put_contents(__DIR__ . '/erro_sugerir_datas.log', "Procedimento não informado\n", FILE_APPEND);
        return;
    }

    if (!$profissional_id) {
        $profissional_id = 1; // Padrão
    }

    // Buscar dias disponíveis e horário
    $stmt = $pdo->prepare("SELECT dia_semana, hora_inicio FROM disponibilidades WHERE procedimento_id = ? AND medico_id = ? AND ativo = 1");
    $stmt->execute(array($procedimento_id, $profissional_id));
    $disponibilidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($disponibilidades)) {
        file_put_contents(__DIR__ . '/erro_sugerir_datas.log', "Nenhuma disponibilidade encontrada\n", FILE_APPEND);
        return;
    }

    // Organizar dias disponíveis
    $dias_disponiveis = array();
    $hora_inicial = null;
    foreach ($disponibilidades as $disp) {
        $dias_disponiveis[] = $disp['dia_semana'];
        if (!$hora_inicial && !empty($disp['hora_inicio'])) {
            $hora_inicial = $disp['hora_inicio']; // Pega o primeiro horário encontrado
        }
    }

    if (!$hora_inicial) {
        $hora_inicial = '08:00'; // Padrão caso não informado
    }

    // Buscar feriados
    $stmtFeriados = $pdo->query("SELECT data FROM feriados");
    $feriados = $stmtFeriados->fetchAll(PDO::FETCH_COLUMN);
    $feriados_formatados = array_map(function ($d) {
        return date('Y-m-d', strtotime($d));
    }, $feriados);

    // Buscar indisponibilidades
    $stmtIndisponibilidades = $pdo->prepare("SELECT data FROM indisponibilidades WHERE medico_id = ?");
    $stmtIndisponibilidades->execute([$profissional_id]);
    $indisponiveis = $stmtIndisponibilidades->fetchAll(PDO::FETCH_COLUMN);
    $indisponiveis_formatados = array_map(function ($d) {
        return date('Y-m-d', strtotime($d));
    }, $indisponiveis);

    // Gerar datas sugeridas
    $hoje = new DateTime();
    $datas_sugeridas = array();

    while (count($datas_sugeridas) < 3) {
        $diaSemanaHoje = (int) $hoje->format('w'); // 0 = domingo, 1 = segunda...
        $data_formatada = $hoje->format('Y-m-d');

        // Evita sugerir a data de hoje (só aceita datas a partir de amanhã)
        if ($hoje->format('Y-m-d') == (new DateTime())->format('Y-m-d')) {
            $hoje->modify('+1 day');
            continue;
        }


        if (
            in_array($diaSemanaHoje, $dias_disponiveis) &&
            !in_array($data_formatada, $feriados_formatados) &&
            !in_array($data_formatada, $indisponiveis_formatados)
        ) {
            $datas_sugeridas[] = $hoje->format('d/m/Y');
        }

        $hoje->modify('+1 day');
    }

    if (empty($datas_sugeridas)) {
        file_put_contents(__DIR__ . '/erro_sugerir_datas.log', "Não foi possível gerar datas\n", FILE_APPEND);
        return;
    }

    // Atualizar no Kommo
    $lead_id = $lead['id'];

    $payload = array(
        'custom_fields_values' => array(
            array(
                'field_id' => 1195726, // Agendamento_OPCAO_1
                'values' => array(array('value' => strval(isset($datas_sugeridas[0]) ? $datas_sugeridas[0] : '')))
            ),
            array(
                'field_id' => 1195728, // Agendamento_OPCAO_2
                'values' => array(array('value' => strval(isset($datas_sugeridas[1]) ? $datas_sugeridas[1] : '')))
            ),
            array(
                'field_id' => 1195730, // Agendamento_OPCAO_3
                'values' => array(array('value' => strval(isset($datas_sugeridas[2]) ? $datas_sugeridas[2] : '')))
            ),
            array(
                'field_id' => 1195734, // Agendamento_HORA_ESCOLHIDA
                'values' => array(array('value' => strval($hora_inicial)))
            )
        )
    );

    // Token do Kommo
    $dotenvPath = realpath(__DIR__ . '/../../.env');
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
        file_put_contents(__DIR__ . '/erro_sugerir_datas.log', "Token de acesso do Kommo não definido\n", FILE_APPEND);
        return;
    }

    $url = 'https://medlabor.amocrm.com/api/v4/leads/' . $lead_id;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ));

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        file_put_contents(__DIR__ . '/erro_curl_sugerir_datas.log', curl_error($ch) . "\n", FILE_APPEND);
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    //file_put_contents(__DIR__ . '/resposta_sugerir_datas.log', $response . "\n", FILE_APPEND);

    if ($httpCode >= 200 && $httpCode < 300) {
        return true;
    } else {
        return false;
    }
}
