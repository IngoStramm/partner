<?php


/**
 * partner_show_all_clients_data_shortcode
 *
 * @return string
 */
function partner_show_all_clients_data_shortcode()
{
    // pegar o ID do usuário
    $user_id = get_current_user_id();
    // verifica se o usuário não existe
    if (!$user_id)
        return;

    $googlesheet_url = partner_get_option('googlesheet_url');
    if (!$googlesheet_url || !is_string($googlesheet_url))
        return;

    $googlesheet_url = html_entity_decode($googlesheet_url);
    $rows = partner_return_googlesheet_data($googlesheet_url);
    $cliente_data = [];
    foreach ($rows as $row) {
        $cliente_data[] = $row;
    }

    return partner_cronograma_output_all($cliente_data);
}

add_shortcode('planilha_clientes', 'partner_show_all_clients_data_shortcode');





/**
 * partner_show_cliente_data_shortcode
 *
 * @return string
 */
function partner_show_cliente_data_shortcode()
{
    // pegar o ID do usuário
    $user_id = get_current_user_id();
    // verifica se o usuário não existe
    if (!$user_id)
        return;

    // pegar o meta dado 'partner_user_cliente' do usuário
    $selected_cliente_id = get_user_meta($user_id, 'partner_user_cliente', true);

    if (!$selected_cliente_id)
        return;

    $googlesheet_url = partner_get_option('googlesheet_url');
    if (!$googlesheet_url || !is_string($googlesheet_url))
        return;

    $googlesheet_url = html_entity_decode($googlesheet_url);
    $rows = partner_return_googlesheet_data($googlesheet_url);
    $cliente_data = [];
    $theaders = $rows[0];
    $rows = array_slice($rows, 1);
    $selected_cliente = get_post_meta($selected_cliente_id, 'cliente_planilha', true);
    foreach ($rows as $row) {
        if ($row[0] === $selected_cliente) {
            $cliente_data[] = $row;
        }
    }

    array_unshift($cliente_data, $theaders);

    return partner_cronograma_output_single($cliente_data);
}

add_shortcode('planilha_cliente', 'partner_show_cliente_data_shortcode');


/**
 * partner_show_site_data_shortcode
 *
 * @return stringg
 */
function partner_show_site_data_shortcode()
{
    $user_id = get_current_user_id();
    if (!$user_id)
        return;

    $dados_site = get_user_meta($user_id, 'partner_user_dados_site', true);
    if (!$dados_site || !filter_var($dados_site, FILTER_VALIDATE_URL))
        return;

    $output = '';
    $output .=
        '<div class="container">
            <iframe id="iframe-dados-site" class="responsive-iframe" src="' . $dados_site . '" frameborder="0" allowfullscreen></iframe>
        </div>
        <style>
            .container {
                position: relative;
                width: 100%;
                overflow: hidden;
                padding-top: 78%;
            }

            .responsive-iframe {
                position: absolute;
                top: 0;
                left: 0;
                bottom: 0;
                right: 0;
                width: 100%;
                height: 100%;
                border: none;
            }
        </style>
        ';
    return $output;
}

add_shortcode('dados_site', 'partner_show_site_data_shortcode');

/**
 * partner_show_media_data_shortcode
 *
 * @return stringg
 */
function partner_show_media_data_shortcode()
{
    $user_id = get_current_user_id();
    if (!$user_id)
        return;

    $dados_site = get_user_meta($user_id, 'partner_user_dados_midia', true);
    if (!$dados_site || !filter_var($dados_site, FILTER_VALIDATE_URL))
        return;

    $output = '';
    $output .=
        '<div class="container">
            <iframe id="iframe-dados-midia" class="responsive-iframe" src="' . $dados_site . '" frameborder="0" allowfullscreen></iframe>
        </div>
        <style>
            .container {
                position: relative;
                width: 100%;
                overflow: hidden;
                padding-top: 78%;
            }

            .responsive-iframe {
                position: absolute;
                top: 0;
                left: 0;
                bottom: 0;
                right: 0;
                width: 100%;
                height: 100%;
                border: none;
            }
        </style>
        ';
    return $output;
}

add_shortcode('dados_midia', 'partner_show_media_data_shortcode');

function partner_list_all_chamados_shortcode()
{
    // pegar todos os post_type "chamados"
    $chamados = get_posts([
        'post_type' => 'chamados',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
        'post_status' => 'publish',
    ]);
    $output = '';
    $output .= '<div class="table-wrap">';
    $output .= '<table class="table">';
    $output .= '<thead>';

    $output .= '<tr>';

    $output .= '<th>';
    $output .= __('Marca', 'partner');
    $output .= '</th>';

    $output .= '<th>';
    $output .= __('Assunto', 'partner');
    $output .= '</th>';

    $output .= '<th>';
    $output .= __('Detalhamento', 'partner');
    $output .= '</th>';

    $output .= '<th>';
    $output .= __('Solicitação', 'partner');
    $output .= '</th>';

    $output .= '<th>';
    $output .= __('Input', 'partner');
    $output .= '</th>';

    $output .= '<th>';
    $output .= __('Entrega', 'partner');
    $output .= '</th>';

    $output .= '<th>';
    $output .= __('Urgência', 'partner');
    $output .= '</th>';

    $output .= '<th>';
    $output .= __('Ponto Focal', 'partner');
    $output .= '</th>';

    $output .= '<th>';
    $output .= __('Status', 'partner');
    $output .= '</th>';

    $output .= '<th>';
    $output .= __('Detalhes da Resolução', 'partner');
    $output .= '</th>';

    $output .= '<th>';
    $output .= __('Ações', 'partner');
    $output .= '</th>';

    $output .= '</tr>';

    $output .= '</thead>';
    $output .= '<tbody>';

    foreach ($chamados as $chamado) {
        $chamado_id = $chamado->ID;

        $chamado_marca_select = get_post_meta($chamado_id, 'chamado_marca_select', true);
        $chamado_assunto = get_post_meta($chamado_id, 'chamado_assunto', true);
        $chamado_detalhes_solicitacao = get_post_meta($chamado_id, 'chamado_detalhes_solicitacao', true);
        $chamado_solicitacao = get_post_meta($chamado_id, 'chamado_solicitacao', true);
        $chamado_entrega = get_post_meta($chamado_id, 'chamado_entrega', true);
        $chamado_urgencia_id = get_post_meta($chamado_id, 'chamado_urgencia', true);
        $chamado_ponto_focal_id = get_post_meta($chamado_id, 'chamado_ponto_focal', true);
        $chamado_status_id = get_post_meta($chamado_id, 'chamado_status', true);
        $chamado_detalhes_resolucao = get_post_meta($chamado_id, 'chamado_detalhes_resolucao', true);
        $chamado_last_update_time = $chamado->post_modified;

        $chamado_solicitacao = date('d/m/Y H:i', $chamado_solicitacao);
        $chamado_entrega = date('d/m/Y H:i', $chamado_entrega);
        $chamado_last_update_time = date('d/m/Y H:i', strtotime($chamado_last_update_time));
        $chamado_urgencia = get_term_by('term_taxonomy_id', $chamado_urgencia_id, 'urgencia');
        $chamado_status = get_term_by('term_taxonomy_id', $chamado_status_id, 'status-chamado');
        $chamado_ponto_focal = get_user_by('id', $chamado_ponto_focal_id);

        // partner_debug($chamado_last_update_time);

        $output .= '<tr>';

        $output .= '<td>';
        $output .= $chamado_marca_select;
        $output .= '</td>';

        $output .= '<td>';
        $output .= $chamado_assunto;
        $output .= '</td>';

        $output .= '<td>';
        $output .= $chamado_detalhes_solicitacao;
        $output .= '</td>';

        $output .= '<td>';
        $output .= $chamado_solicitacao;
        $output .= '</td>';

        $output .= '<td>';
        $output .= $chamado_last_update_time;
        $output .= '</td>';

        $output .= '<td>';
        $output .= $chamado_entrega;
        $output .= '</td>';

        $output .= '<td>';
        $output .= $chamado_urgencia->name;
        $output .= '</td>';

        $output .= '<td>';
        $output .= $chamado_ponto_focal->display_name;
        $output .= '</td>';

        $output .= '<td>';
        $output .= $chamado_status->name;
        $output .= '</td>';

        $output .= '<td>';
        $output .= $chamado_detalhes_resolucao;
        $output .= '</td>';

        $output .= '<td>';
        $output .= '<a class="partner-trigger-popup-chamados partner-btn btn button" href="?post_id=' . $chamado_id . '">' . __('Editar', 'partner') . '</a>';
        $output .= '</td>';

        $output .= '</tr>';
    }

    $output .= '</tbody>';
    $output .= '</table>';

    return $output;
}

add_shortcode('partner_list_all_chamados', 'partner_list_all_chamados_shortcode');

/**
 * partner_display_name_shortcode
 *
 * @param  array $atts
 * @return string
 */
function partner_display_name_shortcode($atts)
{
    extract(shortcode_atts(array(
        'id' => '',
    ), $atts));
    $user = get_user_by('id', $id);
    return $user->display_name;
}

add_shortcode('partner_display_name', 'partner_display_name_shortcode');

function partner_display_cliente_name_shortcode()
{
    $user_id = get_current_user_id();
    $partner_user_cliente_id = get_user_meta($user_id, 'partner_user_cliente', true);
    if (!$partner_user_cliente_id)
        return;
    $partner_user_cliente = get_the_title($partner_user_cliente_id);
    return $partner_user_cliente;
}

add_shortcode('partner_display_cliente_name', 'partner_display_cliente_name_shortcode');

function partner_onclick_edit_chamado_listing_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'mode' => 'view',
    ), $atts);
    $mode = $atts['mode'];
    // partner_debug($mode);
    $post_ID = get_the_ID();

    if (!$post_ID)
        return;

    $random_id = uniqid('partner-btn-');
    ob_start(); ?>
    <button id="<?php echo $random_id ?>" class="<?php echo $mode !== 'view' ? 'btn-edit-chamado' : 'btn-view-chamado'; ?>" onclick="partnerAddNewButton('<?php echo $mode ?>', <?php echo $post_ID ?>);" style="display: none;"><?php echo $mode ?></button>
    <script>
        (function() {
            const btn = document.getElementById('<?php echo $random_id ?>');
            if (typeof(btn) === 'undefined' || btn === null) {
                return;
            }
            const wrapper = btn.closest('.chamado-wrapper');
            if (typeof(wrapper) === 'undefined' || wrapper === null) {
                return;
            }
            wrapper.addEventListener('click', function(e) {
                e.preventDefault();
                btn.click();
            });
        }());
    </script>
<?php return ob_get_clean();
}

add_shortcode('partner_edit_chamado', 'partner_onclick_edit_chamado_listing_shortcode');
