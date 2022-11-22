<?php

/**
 * partner_list_planilha_clientes_name
 *
 * @return array
 */
function partner_list_planilha_clientes_name()
{
    $googlesheet_url = partner_get_option('googlesheet_url');

    if (!$googlesheet_url || !is_string($googlesheet_url))
        return;

    $googlesheet_url = html_entity_decode($googlesheet_url);
    $rows = partner_return_googlesheet_data($googlesheet_url);

    if (is_null($rows) || empty($rows))
        return;

    if (!is_array($rows))
        return $rows;

    $rows = array_slice($rows, 1);
    $clientes = [];

    $clientes[] = __('Selecione um cliente', 'partner');

    foreach ($rows as $row) {
        if (!in_array($row[0], $clientes))
            $clientes[$row[0]] = $row[0];
    }
    return $clientes;
}


/**
 * partner_list_planilha_clientes_name
 *
 * @return array
 */
function partner_list_post_clientes($only_names = true)
{
    $clientes = get_posts([
        'post_type' => 'cliente',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
        'post_status' => 'publish',
        'suppress_filters' => true,
        'fields' => 'ids',
    ]);
    $clientes_array = [];
    foreach ($clientes as $cliente_id) {
        $cliente_planilha_name = get_post_meta($cliente_id, 'cliente_planilha', true);
        $cliente_planilha_marcas = get_post_meta($cliente_id, 'marcas', true);
        $cliente_title = get_the_title($cliente_id);
        // partner_debug($cliente_planilha_marcas);
        if ($only_names) {
            $clientes_array[$cliente_planilha_name] = $cliente_title;
        } else {
            $marcas_name = [];
            foreach ($cliente_planilha_marcas as $marca) {
                // partner_debug($marca);
                $marcas_name[] = $marca['nome-da-marca'];
            }
            $clientes_array[$cliente_id] = [
                'nome' => $cliente_planilha_name,
                'marcas' => $marcas_name,
            ];
        }
    }
    return $clientes_array;
}
/**
 * partner_delete_transient
 *
 * @return void
 */
function partner_delete_transient()
{
    $delete_googlesheet_data = delete_transient('googlesheet_data');
    $response = '';
    if ($delete_googlesheet_data) {
        $msg = __('Dados da planilha atualizados com sucesso!', 'partner');
        $response = array('success' => true, 'msg' => $msg);
    } else {
        $msg = __('Ocorreu um erro ao tentar sincronizar os vídeos.', 'abctalks');
        $response = array('success' => false, 'msg' => $msg, 'data' => $delete_googlesheet_data);
    }
    wp_send_json($response);
}

add_action('wp_ajax_partner_delete_transient', 'partner_delete_transient');

/**
 * partner_redirect_if_user_not_logged_in
 *
 * @return void
 */
function partner_redirect_if_user_not_logged_in()
{
    $login_page_id = partner_get_option('login_page_id', true);
    if (!$login_page_id)
        return;

    $reset_password_page_id = partner_get_option('reset_password_page_id');
    if (!$reset_password_page_id)
        return;

    if (!is_user_logged_in() && !is_page($login_page_id) && !is_page($reset_password_page_id)) {
        // echo '<pre>Não logado</pre>';
        wp_redirect(get_page_link($login_page_id));
        exit;
    }
}

add_action('template_redirect', 'partner_redirect_if_user_not_logged_in');
