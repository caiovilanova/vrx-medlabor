<?php

// inclui_pre_agendamento.php

if (!function_exists('incluirPreAgendamento')) {
    function incluirPreAgendamento($lead, $pdo)
    {
        

        $campos = array(
            'nome_paciente' => 1181824,
            'telefone' => 203274,
            'procedimento_id' => 1194274,
            'profissional_id' => 1194268,
            'unidade_id' => 1195154,
            'convenio_id' => 1195134,
            'agendamento_data_escolhida' => 1195732,
            'agendamento_hora_escolhida' => 1195734
        );

        function getCampo($campos_array, $field_id)
        {
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
        $profissional_id = getCampo($custom_fields, $campos['profissional_id']);
        $unidade_id = getCampo($custom_fields, $campos['unidade_id']);
        $convenio_id = getCampo($custom_fields, $campos['convenio_id']);

        $data_raw = getCampo($custom_fields, $campos['agendamento_data_escolhida']);
        $hora_raw = getCampo($custom_fields, $campos['agendamento_hora_escolhida']);

        $data = null;
        $hora = null;

        if (!empty($data_raw)) {
            $partes = explode('/', $data_raw);
            if (count($partes) === 3) {
                $data = $partes[2] . '-' . $partes[1] . '-' . $partes[0];
            }
        }

        if (!empty($hora_raw)) {
            $hora = trim($hora_raw);
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
            $procedimento_id, $profissional_id, $convenio_id, $lead_id, $status_id, $pipeline_id, $responsavel_id
        ));

        return true;
    }
}
