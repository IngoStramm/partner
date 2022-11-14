<?php

// add_action('wp_head', function () {
//     $googlesheet_url = partner_get_option('googlesheet_url');
//     if (!$googlesheet_url || !is_string($googlesheet_url))
//         return;
//     // $googlesheet_url = jet_engine()->listings->data->get_option('partner-settings::googlesheet_url');
//     $googlesheet_url = html_entity_decode($googlesheet_url);
//     $rows = partner_return_googlesheet_data($googlesheet_url);
//     echo partner_cronograma_output_all($rows);
// });

/**
 * partner_return_googlesheet_data
 *
 * @param  string $googlesheet_url
 * @return array
 */
function partner_return_googlesheet_data($googlesheet_url)
{
    if (is_null($googlesheet_url) || empty($googlesheet_url) || !filter_var($googlesheet_url, FILTER_VALIDATE_URL))
        return;

    $rows = [];
    $transient_active = partner_get_option('transient_active');
    $googlesheet_data = get_transient('googlesheet_data');
    if (!$transient_active || !$googlesheet_data) {

        // partner_debug($transient_active);

        if (($handle = fopen($googlesheet_url, 'r')) !== FALSE
        ) {
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                // array_splice($data, 8);
                $rows[] = $data;
            }
            fclose($handle);
            set_transient('googlesheet_data', $rows, DAY_IN_SECONDS);
            $googlesheet_data = get_transient('googlesheet_data');
        } else {
            return __('Não foi possível carregar a planilha. Verifique se as configurações estão corretas.', 'partner');
        }
    }

    return $googlesheet_data;
}

/**
 * partner_cronograma_output_all
 *
 * @param  array $rows
 * @return string
 */
function partner_cronograma_output_all($rows)
{
    if (is_null($rows) || empty($rows))
        return;

    if (!is_array($rows))
        return $rows;

    $output = '';
    $output .= '<table>';
    $output .= '<thead>';
    $theaders = array_shift($rows);

    foreach ($theaders as $value) {
        $output .= '<th>' . $value . '</th>';
    }
    $output .= '</thead>';
    $output .= '<tbody>';
    asort($rows);
    foreach ($rows as $row) {
        $output .= '<tr>';
        foreach ($row as $value) {
            $output .= '<td>' . $value . '</td>';
        }
        $output .= '</tr>';
    }
    $output .= '</tbody>';
    $output .= '</table>';
    return $output;
}

function partner_cronograma_output_single($clientes_data)
{
    $theaders = $clientes_data[0];
    $rows = array_slice($clientes_data, 1);
    $datas = [];
    $servicos = [];
    $valores = [];
    $status = [];
    $index = 0;
    // partner_debug($theaders);
    foreach ($rows as $row) {
        $data = $row[1];
        $ref = $row[2];
        $servico = $row[4];
        $contratado = $row[5];
        $entregue = $row[6];
        $status = $row[7];

        // $row['servico']['ref'] = [$contratado, $entregue];

        $servicos[] = $servico;
        $datas[$ref] = $data;
        // $anos[] = array('id' => $index, $theaders[3] => $row[3]);

        $valores[] = array('servico' => $servico, 'ref' => $ref, 'contratado' => $contratado, 'entregue' => $entregue, 'status' => $status);

        // $entregues[] = array('id' => $index, 'mes' => $ref, $theaders[6] => $row[6]);

        // $status[] = array('id' => $index, 'mes' => $ref, $theaders[7] => $row[7]);
        $index++;
    }

    $servicos_unicos = [];
    $prev_serv = '';
    asort($servicos);
    foreach ($servicos as $servico) {
        if ($prev_serv != $servico) {
            $servicos_unicos[] = $servico;
            $prev_serv = $servico;
        }
    }

    // $datas possui a Ref e o nome do mês: $data['ref'] => val
    // $servicos_unicos possui os nomes não repetidos dos servicos

    // parei aqui
    // precisa agrupar os contratados por serviços e por mês, armazendo o valor total no array resultados_por_servico_mes

    $resultados_por_servico = [];
    foreach ($servicos_unicos as $servico) {
        foreach ($valores as $por_servico) {
            if ($servico == $por_servico['servico']) {
                $resultados_por_servico[$por_servico['servico']][] = [
                    'ref' => $por_servico['ref'],
                    'contratado' => $por_servico['contratado'],
                    'entregue' => $por_servico['entregue'],
                    'status' => $por_servico['status']
                ];
            }
        }
    }


    $resultados_por_servico_mes = [];
    foreach ($datas as $ref => $data) {
        foreach ($resultados_por_servico as $servico => $por_servico) {
            $total_contratado_por_servico = 0;
            $total_entregue_por_servico = 0;
            foreach ($por_servico as $item) {
                $qtd_contratada = isset($item['contratado']) ? (int)$item['contratado'] : 0;
                $qtd_entregue = isset($item['entregue']) ? (int)$item['entregue'] : 0;
                if ($ref == $item['ref']) {
                    // partner_debug($item['entregue']);

                    $resultados_por_servico_mes[$servico][$ref]['contratado'] += $qtd_contratada;
                    $resultados_por_servico_mes[$servico][$ref]['entregue'] += $qtd_entregue;
                }
                $total_contratado_por_servico += $qtd_contratada;
                $total_entregue_por_servico += $qtd_entregue;
            }
            $resultados_por_servico_mes[$servico]['total_contratado'] = $total_contratado_por_servico;
            $resultados_por_servico_mes[$servico]['total_entregue'] = $total_entregue_por_servico;
        }
    }

    # pegar o mês atual em português
    $mes_atual = date('F');
    $mes_atual = str_replace('January', 'Janeiro', $mes_atual);
    $mes_atual = str_replace('February', 'Fevereiro', $mes_atual);
    $mes_atual = str_replace('March', 'Março', $mes_atual);
    $mes_atual = str_replace('April', 'Abril', $mes_atual);
    $mes_atual = str_replace('May', 'Maio', $mes_atual);
    $mes_atual = str_replace('June', 'Junho', $mes_atual);
    $mes_atual = str_replace('July', 'Julho', $mes_atual);
    $mes_atual = str_replace('August', 'Agosto', $mes_atual);
    $mes_atual = str_replace('September', 'Setembro', $mes_atual);
    $mes_atual = str_replace('October', 'Outubro', $mes_atual);
    $mes_atual = str_replace('November', 'Novembro', $mes_atual);
    $mes_atual = str_replace('December', 'Dezembro', $mes_atual);
    $index_atual = 0;
    foreach ($datas as $ref => $data) {
        $index = (int)str_replace('M', '', $ref);
        if ($mes_atual === $data) {
            $index_atual = $index;
        }
    }

    $output = '';
    $output .= '<div class="table-wrap">';
    $output .= '<table class="table">';
    $output .= '<thead>';

    $output .= '<tr>';

    $output .= '<th class="wide-th"><div>';
    $output .= __('Projetos contratados vs realizados', 'partner');
    $output .= '</div></th>';

    // Ref
    foreach ($datas as $ref => $data) {
        $index = (int)str_replace('M', '', $ref);
        if ($mes_atual === $data) {
            $index_atual = $index;
        }
        $css_class = (int)$index <= (int)$index_atual ? 'mes-passado' : 'mes-futuro';
        $output .= '<th class="vertical-text th-ref ' . $css_class . '" colspan="2"><div>';
        $output .= $ref;
        $output .= '</div></th>';
    }

    $output .= '<th class="total-geral vertical-text" colspan="2" rowspan="2"><div>';
    $output .= __('Geral', 'partner');
    $output .= '</div></th>';

    $output .= '</tr>';

    $output .= '<tr>';

    $output .= '<th class="th-block"><div>';
    $output .= __('Escopo de Projetos', 'partner');
    $output .= '</div></th>';

    // Mês
    foreach ($datas as $ref => $data) {
        $index = (int)str_replace('M', '', $ref);
        if ($mes_atual === $data) {
            $index_atual = $index;
        }
        $css_class = (int)$index <= (int)$index_atual ? 'mes-passado' : 'mes-futuro';
        $output .= '<th class="vertical-text th-mes ' . $css_class . '" colspan="2"><div>';
        $output .= $data;
        $output .= '</div></th>';
    }

    $output .= '</tr>';

    $output .= '</thead>';

    $output .= '<tbody>';

    foreach ($servicos_unicos as $servico) {
        $output .= '<tr>';

        $output .= '<td><div>';
        $output .= $servico;
        $output .= '</div></td>';

        foreach ($datas as $ref => $data) {
            $resultado_contratado = $resultados_por_servico_mes[$servico][$ref]['contratado'] ?? '';
            $resultado_entregue = $resultados_por_servico_mes[$servico][$ref]['entregue'] ?? '';

            // partner_debug($resultado_entregue);
            // partner_debug(empty($resultado_entregue));
            // partner_debug(is_null($resultado_entregue));

            $css_class_contratado = ($resultado_contratado !== 0 && (empty($resultado_contratado) || is_null($resultado_contratado))) ? 'empty-cell' : 'cell-contratado';

            $css_class_entregue = ($resultado_entregue !== 0 && (empty($resultado_entregue) || is_null($resultado_entregue))) ? 'empty-cell' : 'cell-entregue';

            $output .= '<td class="resultado-contratado ' . $css_class_contratado . '"><div>';
            $output .= $resultado_contratado;
            $output .= '</div></td>';
            // partner_debug($datas);
            $output .= '<td class="resultado-entregue ' . $css_class_entregue . '"><div>';
            $output .= $resultado_entregue;
            $output .= '</div></td>';
        }
        //Total de contratados por Serviço
        $total_contratado = $resultados_por_servico_mes[$servico]['total_contratado'] ?? '';
        $total_entregue = $resultados_por_servico_mes[$servico]['total_entregue'] ?? '';
        $output .= '<td class="total-contratado"><div>';
        $output .= $total_contratado;
        $output .= '</div></td>';

        //Total de entregues por Serviço
        $output .= '<td class="total-entregue"><div>';
        $output .= $total_entregue;
        $output .= '</div></td>';

        $output .= '<tr>';
    }

    $output .= '</tbody>';

    $output .= '</table>';
    $output .= '</div>';
    return $output;
}
