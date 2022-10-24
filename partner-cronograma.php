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

    $googlesheet_data = get_transient('googlesheet_data');
    if (!$googlesheet_data) {

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
function partner_cronograma_output_all($rows, $single = false)
{
    if (is_null($rows) || empty($rows))
        return;

    if (!is_array($rows))
        return $rows;

    $output = '';
    $output .= '<table>';
    $output .= '<thead>';
    $theaders = array_shift($rows);

    if ($single)
        $theaders = array_slice($theaders, 1);

    foreach ($theaders as $value) {
        $output .= '<th>' . $value . '</th>';
    }
    $output .= '</thead>';
    $output .= '<tbody>';
    asort($rows);
    foreach ($rows as $row) {
        $output .= '<tr>';
        if ($single)
            $row = array_slice($row, 1);
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
    $meses = [];
    $anos = [];
    $servicos = [];
    $contratados = [];
    $entregues = [];
    $status = [];
    $index = 0;
    foreach ($rows as $row) {
        $servicos[] = array('id' => $index, 'mes' => $row[2], $theaders[4] => $row[4]);

        $meses[] = array('id' => $index, 'ref' => $row[2], 'nome' => $row[1]);
        $anos[] = array('id' => $index, $theaders[3] => $row[3]);

        $contratados[] = array('id' => $index, 'servico' => $row[4], 'mes' => $row[2], 'valor' => $row[5]);

        $entregues[] = array('id' => $index, 'mes' => $row[2], $theaders[6] => $row[6]);

        $status[] = array('id' => $index, 'mes' => $row[2], $theaders[7] => $row[7]);
        $index++;
    }

    $meses_unicos = [];
    $prev_data = '';
    foreach ($meses as $k => $mes) {
        if ($prev_data != $mes['ref']) {
            $meses_unicos[$mes['ref']] = [
                'nome' => $mes['nome']
            ];
            $prev_data = $mes['ref'];
        }
    }

    $contratados_por_servico = [];
    foreach ($contratados as $contratado) {
        $contratados_por_servico[$contratado['servico']][] = [
            'id' => $contratado['id'],
            'mes' => $contratado['mes'],
            'valor' => $contratado['valor']
        ];
    }

    $contratados_por_servico_mes = [];
    $prev_serv = '';
    // parei aqui
    // precisa agrupar os contratados por serviços e por mês, armazendo o valor total no array contratados_por_servico_mes
    
    foreach ($contratados_por_servico as $k => $contratado) {
        if ($k !== $prev_serv) {
            $total = 0;
            $prev_mes = '';
            // partner_debug($k);
            foreach ($contratado as $c) {
                if ($c['mes'] !== $prev_mes) {
                    $total = $total + (int)$c['valor'];
                    $prev_mes = $c['mes'];
                }
                foreach ($c as $v) {
                }
                $contratados_por_servico_mes[$k][$c['mes']] = $total;
            }
            $prev_serv = $k;
        }
    }
    partner_debug($contratados_por_servico_mes);

    $output = '';
    $output .= '<table>';
    $output .= '<thead>';
    $output .= '<tr>';

    $output .= '<th>';
    $output .= '</th>';

    $prev_data = '';
    $total_meses = 0;
    $col_meses = [];
    foreach ($datas as $data) {
        if ($prev_data !== $data['Data']) {
            $output .= '<th colspan="2">';
            $output .= $data['Data'] . ' (' . $data['mes'] . ')';
            $output .= '</th>';
            $prev_data = $data['Data'];
            $col_meses[] = $data['mes'];
            $total_meses++;
        }
    }

    $output .= '</tr>';
    $output .= '<tr>';

    $output .= '<th>';
    $output .= $theaders[4];
    $output .= '</th>';

    for ($i = 0; $i < $total_meses; $i++) {

        $output .= '<th>';
        $output .= $theaders[5];
        $output .= '</th>';

        $output .= '<th>';
        $output .= $theaders[6];
        $output .= '</th>';
    }

    $output .= '</tr>';
    $output .= '</thead>';

    $output .= '<tbody>';

    $prev_servico = [];
    foreach ($servicos as $servico) {
        // partner_debug($prev_servico);
        if (!in_array($servico[$theaders[4]], $prev_servico)) {
            $output .= '<tr>';

            $output .= '<td>';
            $output .= $servico[$theaders[4]];
            $output .= '</td>';

            for ($i = 0; $i < $total_meses; $i++) {
                $output .= '<td>';
                $total = 0;
                foreach ($contratados_por_mes[$servico['mes']] as $contratado) {
                    $total = $total + (int)$contratado['valor'];
                }
                $output .= $total . ' (' . $contratado[$theaders[4]] . ')';
                $output .= '</td>';

                $output .= '<td>';
                $output .= '</td>';
            }


            $output .= '<tr>';
            $prev_servico[] = $servico[$theaders[4]];
        }
    }

    $output .= '</tbody>';

    $output .= '</table>';
    return $output;
}
