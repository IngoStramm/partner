<?php

// criar um shortcode do WordPress que exibe um texto
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
