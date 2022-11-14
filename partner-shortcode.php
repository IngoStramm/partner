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
    $selected_cliente = get_user_meta($user_id, 'partner_user_cliente', true);

    if (!$selected_cliente)
        return;

    $googlesheet_url = partner_get_option('googlesheet_url');
    if (!$googlesheet_url || !is_string($googlesheet_url))
        return;

    $googlesheet_url = html_entity_decode($googlesheet_url);
    $rows = partner_return_googlesheet_data($googlesheet_url);
    $cliente_data = [];
    $theaders = $rows[0];
    $rows = array_slice($rows, 1);
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
