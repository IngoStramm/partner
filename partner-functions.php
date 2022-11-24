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
            $clientes_array[$cliente_id] = $cliente_title;
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
 * partner_list_post_clientes_with_id
 *
 * @return array
 */
function partner_list_post_clientes_with_id()
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
        $cliente_title = get_the_title($cliente_id);
        $clientes_array[$cliente_id] = $cliente_title;
    }
    return $clientes_array;
}

/**
 * partner_list_admin_users
 *
 * @return array
 */
function partner_list_admin_users()
{
    $users = get_users([
        'role__in' => ['administrator', 'editor'],
        'orderby' => 'display_name',
        'order' => 'ASC',
        'fields' => ['ID', 'display_name'],
    ]);

    $users_array = [];
    $users_array[] = __('Selecione um usuário', 'partner');
    foreach ($users as $user) {
        $users_array[$user->ID] = $user->display_name;
    }
    return $users_array;
}

/**
 * partner_get_status_list
 *
 * @return array
 */
function partner_get_urgencia_list()
{
    $terms = get_terms([
        'taxonomy' => 'urgencia',
        'hide_empty' => false,
        'meta_key' => 'ordem',
        'orderby' => 'meta_value_num',
        'order' => 'ASC',
    ]);
    $status_array = [];
    foreach ($terms as $term) {
        // partner_debug($term);
        // $ordem = get_term_meta($term->term_id, 'ordem', true);
        $status_array[$term->term_id] = $term->name;
    }
    return $status_array;
}

function partner_get_status_list()
{
    $terms = get_terms([
        'taxonomy' => 'status-chamado',
        'hide_empty' => false,
        'meta_key' => 'ordem',
        'orderby' => 'meta_value_num',
        'order' => 'ASC',
    ]);
    $status_array = [];
    foreach ($terms as $term) {
        // $ordem = get_term_meta($term->term_id, 'ordem', true);
        $status_array[$term->term_id] = $term->name;
    }
    return $status_array;
}

/**
 * partner_hide_tags_chamados
 *
 * @return string
 */
function partner_hide_tags_chamados()
{
    global $post;
    if ($post->post_type == 'chamados') {
        echo '<style>
        #tagsdiv-status-chamado,
        #tagsdiv-urgencia {
            display: none;
        }
        </style>';
    }
}

add_action('admin_head', 'partner_hide_tags_chamados');


/**
 * partner_chamados_titulo
 *
 * @param  array $data
 * @param  array $postarr
 * @return array $data
 */
function partner_chamados_titulo($data, $postarr)
{
    if ('chamados' != $data['post_type'])
        return $data;

    $data_formatada = date('d/m/Y H:i', strtotime($postarr['post_date']));
    $data['post_title'] = $data['post_title'] == '' ? 'Chamado #' . $postarr['ID'] . ' - ' . $data_formatada : $data['post_title'];

    return $data;
}

add_action('wp_insert_post_data', 'partner_chamados_titulo', '99', 2);

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

/**
 * partner_delete_transient
 *
 * @return void
 */
function partner_get_cliente_marcas()
{
    $response = '';
    $cliente_id = $_GET['cliente_id'];
    if (empty($cliente_id)) {
        $msg = __('Não foi possível carregar as marcas do cliente.', 'partner');
        $response = array('success' => false, 'msg' => $msg);
    } else {
        $marcas = get_post_meta($cliente_id, 'marcas', true);
        $options = '<option value="">' . __('Selecione uma marca', 'partner') . '</option>';
        if (is_array($marcas)) {
            foreach ($marcas as $marca) {
                $options .= '<option value="' . $marca['nome-da-marca'] . '">' . $marca['nome-da-marca'] . '</option>';
            }
        }
        $response = array('success' => true, 'options' => $options);
    }
    wp_send_json($response);
}

add_action('wp_ajax_partner_get_cliente_marcas', 'partner_get_cliente_marcas');
