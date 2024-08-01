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
    $meses_anos = [];
    $servicos = [];
    $valores = [];
    $status = [];
    $index = 0;
    // partner_debug($theaders);
    foreach ($rows as $row) {
        $data = $row[1];
        $ref = $row[2];
        $ano = $row[3];
        $servico = $row[4];
        $servico_full_name = $row[5] ? $row[4] . ' - ' . $row[5] : $row[4];
        $contratado = $row[6];
        $entregue = $row[7];
        $status = $row[8];
        $comentario = $row[9];

        // se o texto do $comentario possuir URLs, adicionar tag <a> nas URLs
        // incluir símbolos no preg_replace para que não sejam interpretados como regex
        $comentario = preg_replace('/((http|https|ftp):\/\/[\w?=&.\/-;#~%-]+(?![\w\s?&.\/;#~%"=-]*>))/', '<a href="$1" target="_blank">$1</a>', $comentario);
        // $comentario = preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', '<a href="$1" target="_blank">$1</a>', $comentario);

        // $row['servico']['ref'] = [$contratado, $entregue];

        $servicos[] = $servico;
        $servicos_full_name[] = $servico_full_name;
        // parei aqui

        $datas[$ref] = $data;
        $meses_anos[$ref] = ['mes' => $data, 'ano' => $ano];
        // $anos[] = array('id' => $index, $theaders[3] => $row[3]);

        $valores[] = array('servico' => $servico, 'ref' => $ref, 'contratado' => $contratado, 'entregue' => $entregue, 'status' => $status, 'comentario' => $comentario);

        // $entregues[] = array('id' => $index, 'mes' => $ref, $theaders[6] => $row[6]);

        // $status[] = array('id' => $index, 'mes' => $ref, $theaders[7] => $row[7]);
        $index++;
    }

    $servicos_unicos = [];
    $servicos_unicos_full_name = [];
    $prev_serv = '';
    asort($servicos);
    foreach ($servicos as $k => $servico) {
        if ($prev_serv != $servico) {
            $servicos_unicos[] = $servico;
            $servicos_unicos_full_name[] = $servicos_full_name[$k];
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
                    'status' => $por_servico['status'],
                    'comentario' => $por_servico['comentario'],
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
                    // partner_debug($qtd_contratada);
                    // partner_debug($ref);
                    if (!isset($resultados_por_servico_mes[$servico]))
                        $resultados_por_servico_mes[$servico] = array();

                    if (!isset($resultados_por_servico_mes[$servico][$ref]))
                        $resultados_por_servico_mes[$servico][$ref] = array();

                    if (!isset($resultados_por_servico_mes[$servico][$ref]['contratado']))
                        $resultados_por_servico_mes[$servico][$ref]['contratado'] = 0;

                    if (!isset($resultados_por_servico_mes[$servico][$ref]['entregue']))
                        $resultados_por_servico_mes[$servico][$ref]['entregue'] = 0;

                    $resultados_por_servico_mes[$servico][$ref]['contratado'] += $qtd_contratada;
                    $resultados_por_servico_mes[$servico][$ref]['entregue'] += $qtd_entregue;
                    $resultados_por_servico_mes[$servico][$ref]['comentario'] = $item['comentario'];
                    // if (!isset($resultados_por_servico_mes[$servico])) {
                    //     // $soma_qtd_contratada = $qtd_contratada;
                    // } else {
                    //     $resultados_por_servico_mes[$servico] = array(
                    //         $ref => array(
                    //             'contratado' => $qtd_contratada,
                    //             'entregue' => $qtd_entregue,
                    //             'comentario' => $item['comentario'],
                    //         )
                    //     );
                    // }
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
    $ano_atual = date('Y');
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
    foreach ($meses_anos as $ref => $mes_ano) {
        $index = (int)str_replace('M', '', $ref);
        if ($mes_atual === $mes_ano['mes'] && $ano_atual === $mes_ano['ano']) {
            $index_atual = $index;
        }
    }

    $output = '';
    $output .= '<div class="table-wrap">';

    $output .= '<table class="table">';
    $output .= '<thead>';

    $output .= '<tr class="no-bg">';

    $output .= '<th>';
    $output .= __('Status do Projeto', 'partner');
    $output .= '</th>';

    $output .= '</tr>';
    $output .= '<tr>';
    $csv_row_count = 0;
    $csv = array();
    $csv[$csv_row_count] = array();

    $csv[$csv_row_count][] = __('Contratados VS Realizados', 'partner');

    $output .= '<th class="row-title">';
    $output .= __('Contratados VS Realizados', 'partner');
    $output .= '</th>';

    // Ref
    foreach ($meses_anos as $ref => $mes_ano) {
        $index = (int)str_replace('M', '', $ref);
        $mes_ano['mes'] = str_replace('Janeiro', 'Jan', $mes_ano['mes']);
        $mes_ano['mes'] = str_replace('Fevereiro', 'Fev', $mes_ano['mes']);
        $mes_ano['mes'] = str_replace('Março', 'Mar', $mes_ano['mes']);
        $mes_ano['mes'] = str_replace('Abril', 'Abr', $mes_ano['mes']);
        $mes_ano['mes'] = str_replace('Maio', 'Mai', $mes_ano['mes']);
        $mes_ano['mes'] = str_replace('Junho', 'Jun', $mes_ano['mes']);
        $mes_ano['mes'] = str_replace('Julho', 'Jul', $mes_ano['mes']);
        $mes_ano['mes'] = str_replace('Agosto', 'Ago', $mes_ano['mes']);
        $mes_ano['mes'] = str_replace('Setembro', 'Set', $mes_ano['mes']);
        $mes_ano['mes'] = str_replace('Outubro', 'Out', $mes_ano['mes']);
        $mes_ano['mes'] = str_replace('Novembro', 'Nov', $mes_ano['mes']);
        $mes_ano['mes'] = str_replace('Dezembro', 'Dez', $mes_ano['mes']);
        $css_class = (int)$index <= (int)$index_atual || ((int)$index_atual === 0 && (int)$ano_atual === $mes_ano['ano']) ? 'mes-passado' : 'mes-futuro';
        $css_class .= (int)$index === 1 ? ' primeiro-mes-passado' : '';
        $css_class .= (int)$index === (int)$index_atual ? ' ultimo-mes-passado' : '';
        $output .= '<th class="' . $css_class . '" colspan="2">';
        if ((int)$index === (int)$index_atual) {
            $output .= '<span class="progress-mark">' . __('Você está aqui', 'partner') . '</span>';
        } elseif ((int)$index_atual === 0 && (int)$index === count($meses_anos) && (int)$ano_atual === $mes_ano['ano']) {
            $output .= '<span class="progress-mark">' . __('Você está aqui', 'partner') . '</span>';
        }
        $output .= '<div>';
        $output .= $mes_ano['mes'];
        $output .= '</div><div>';
        $output .= $ref;
        $output .= '</div></th>';

        $csv[$csv_row_count][] = $mes_ano['mes'] . '/' . $ref . ' ' . __('Contratado', 'partner');
        $csv[$csv_row_count][] = $mes_ano['mes'] . '/' . $ref . ' ' . __('Entregue', 'partner');
    }

    $output .= '<th class="total-geral" colspan="2">';
    $output .= __('Geral', 'partner');
    $output .= '</th>';

    $csv[$csv_row_count][] = __('Geral Contratado', 'partner');
    $csv[$csv_row_count][] = __('Geral Entregue', 'partner');

    $output .= '</tr>';

    $output .= '</thead>';

    $output .= '<tbody>';

    $servico_slug = '';

    $csv_row_count++;
    foreach ($servicos_unicos as $i => $servico) {
        // criar um slug a partir do texto em $servico e salvar em servico_slug
        $csv_comentarios = array();

        $servico_slug = sanitize_title($servico);
        $output .= '<tr>';
        $nome_servico = $servico !== $servicos_unicos_full_name[$i] ? $servico . '...' : $servico;
        $output .= '<td><div class="tooltip">';
        $output .= '<span class="tooltip-text">' . $servicos_unicos_full_name[$i] . '</span>';
        $output .= '<span class="crop-text">' . $nome_servico . '</span>';
        $output .= '</div></td>';

        $csv[$csv_row_count][] = $servico;

        foreach ($datas as $ref => $data) {
            $resultado_contratado = $resultados_por_servico_mes[$servico][$ref]['contratado'] ?? '';
            $resultado_entregue = $resultados_por_servico_mes[$servico][$ref]['entregue'] ?? '';
            $resultado_comentario = $resultados_por_servico_mes[$servico][$ref]['comentario'] ?? '';

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
            if ($resultado_comentario) {
                $output .= '<a href="#" class="partner-trigger-popup" data-partner-popup-id="partner-popup-' . $servico_slug . '-' . $ref . '">';
            }
            $output .= $resultado_entregue;
            if ($resultado_comentario) {
                $output .= '</a>';
                $output .= '<div id="partner-popup-' . $servico_slug . '-' . $ref . '" class="partner-content-popup">' . $resultado_comentario . '</div>';
                $csv_comentarios[] = $resultado_comentario;
            }
            $output .= '</div></td>';

            $csv[$csv_row_count][] = $resultado_contratado !== 0 && (empty($resultado_contratado) || is_null($resultado_contratado)) ? '0' : $resultado_contratado;
            $csv[$csv_row_count][] = $resultado_entregue !== 0 && (empty($resultado_entregue) || is_null($resultado_entregue)) ? '0' : $resultado_entregue;
        }
        //Total de contratados por Serviço
        $total_contratado = $resultados_por_servico_mes[$servico]['total_contratado'] ?? '';
        $total_entregue = $resultados_por_servico_mes[$servico]['total_entregue'] ?? '';
        $output .= '<td class="total-contratado"><div>';
        $output .= $total_contratado;
        $output .= '</div></td>';

        $csv[$csv_row_count][] = $total_contratado;

        //Total de entregues por Serviço
        $output .= '<td class="total-entregue"><div>';
        $output .= $total_entregue;
        $output .= '</div></td>';

        $csv[$csv_row_count][] = $total_entregue;
        foreach ($csv_comentarios as $comentario) {
            // $csv[0][] = __('Comentário', 'partner');
            // $csv[$csv_row_count][] = $comentario;
        }

        $output .= '<tr>';
        $csv_row_count++;
    }

    $output .= '</tbody>';

    $output .= '</table>';

    $output .= '<button id="partner-download-csv" class="download-csv-button">' . __('Download Formato CSV', 'partner') . '</button>';

    $output .= '</div>';
    // partner_debug($csv);

    $user_id = get_current_user_id();
    update_user_meta($user_id, 'partner_cronograma_csv', $csv);
    // $partner_cronograma_csv = get_user_meta($user_id, 'partner_cronograma_csv', true);
    // partner_debug($partner_cronograma_csv);
    return $output;
}

function partner_aprovacao_output_all($rows)
{
    if (is_null($rows) || empty($rows))
        return;

    if (!is_array($rows))
        return $rows;

    $output = '';
    $output .= '<div class="table-wrap">';
    $output .= '<table class="table table-aprovacao">';
    $output .= '<thead>';
    $theaders = array_shift($rows);

    foreach ($theaders as $k => $value) {
        if (
            $k === 4 ||
            $k === 8 ||
            $k === 9
        ) {
            $output .= '<th>' . $value . '</th>';
        }
    }
    $output .= '</thead>';
    $output .= '<tbody>';
    asort($rows);
    foreach ($rows as $row) {
        $output .= '<tr>';
        foreach ($row as $k => $value) {
            if ($k === 4) {
                $service_name = $row[5] ? $value . ' - ' . $row[5] : $value;
                $output .=
                    '<td><div class="tooltip">' .
                    '<span class="tooltip-text">' . $service_name . '</span>' .
                    '<span class="crop-text">' . $value . '...</span>' .
                    '</div></td>';
            } else if ($k === 8) {
                $output .= '<td>' . $value . '</td>';
            } else if ($k === 9) {
                $output .= '<td>' . preg_replace('/((http|https|ftp):\/\/[\w?=&.\/-;#~%-]+(?![\w\s?&.\/;#~%"=-]*>))/', '<p><a href="$1" target="_blank">$1</a></p>', $value) . '</td>';
            }
        }
        $output .= '</tr>';
    }
    $output .= '</tbody>';
    $output .= '</table>';
    $output .= '</div>';
    return $output;
}
