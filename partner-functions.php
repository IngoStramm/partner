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
        'role__in' => ['editor'],
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
        $status_array[$term->term_id] = $term->name;
    }
    return $status_array;
}

/**
 * partner_admin_head_style
 *
 * @return string
 */
function partner_admin_head_style()
{
    global $post;
    if (isset($post->post_type) && $post->post_type == 'chamados') {
        echo '<style>
        #tagsdiv-status-chamado,
        #radio-tagsdiv-status-chamado,
        #tagsdiv-urgencia,
        #radio-tagsdiv-urgencia,
        #pageparentdiv {
            display: none;
        }
        </style>';
    }
}

add_action('admin_head', 'partner_admin_head_style');


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
    $data['post_title'] = 'Chamado #' . $postarr['ID'] . ' - ' . $data_formatada;

    return $data;
}

add_action('wp_insert_post_data', 'partner_chamados_titulo', '99', 2);

/**
 * partner_chamados_save_term_order
 *
 * @param  int $post_id
 * @return void
 */
function partner_chamados_save_term_order($post_id)
{
    // Verifica se é uma revisão e atribui o ID caso for
    if ($parent_id = wp_is_post_revision($post_id))
        $post_id = $parent_id;

    // Verifica se é um chamado
    $post_type = get_post_type($post_id);
    if ('chamados' !== $post_type) {
        return;
    }

    $urgencia_id = get_post_meta($post_id, 'chamado_urgencia', true);

    if (!$urgencia_id)
        return;

    $status_id = get_post_meta($post_id, 'chamado_status', true);

    if (!$status_id)
        return;

    $urgencia_ordem = get_term_meta($urgencia_id, 'ordem', true);
    $status_ordem = get_term_meta($status_id, 'ordem', true);

    // Previne loop ao atualizar o post
    remove_action('save_post', 'partner_chamados_save_term_order', 999);

    wp_update_post(array('ID' => $post_id, 'menu_order' => $urgencia_ordem));
    update_post_meta($post_id, 'urgencia_ordem', $urgencia_ordem);
    update_post_meta($post_id, 'status_ordem', $status_ordem);

    // readiciona a função após atualizar o post
    add_action('save_post', 'partner_chamados_save_term_order', 999, 1);
}

add_action('save_post', 'partner_chamados_save_term_order', 999, 1);

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

function partner_get_chamado()
{
    $response = '';
    $post_id = $_GET['post_id'];

    $chamado = null;
    $metas = null;
    $cliente_selecionado = null;
    $marca_selecionada = null;
    if (!empty($post_id)) {
        // dados do chamado selecionado
        $post = get_post($post_id);

        if (!$post) {
            $msg = __('Não foi possível carregar o chamado.', 'partner');
            $response = array('success' => false, 'msg' => $msg);
            wp_send_json($response);
        }

        $metas = get_post_meta($post_id);
        $cliente_selecionado = get_post_meta($post_id, 'chamado_post', true);
        $marca_selecionada = get_post_meta($post_id, 'chamado_marca_select', true);

        $chamado = new stdClass();
        $chamado->id = $post->ID;
        $chamado->titulo = $post->post_title;
        $chamado->post_date = $post->post_date;
        $chamado->post_modified = $post->post_modified;
        $chamado->cliente = get_post_meta($post->ID, 'chamado_post', true);
        $chamado->marca = get_post_meta(
            $post->ID,
            'chamado_marca_select',
            true
        );
        $chamado->assunto = get_post_meta($post->ID, 'chamado_assunto', true);
        $chamado->detalhamento_solicitacao = get_post_meta($post->ID, 'chamado_detalhes_solicitacao', true);
        $data_solicitacao = get_post_meta($post->ID, 'chamado_solicitacao', true);
        if ($data_solicitacao) {
            $data_solicitacao = date('Y-m-d\TH:i', $data_solicitacao);
        }
        $chamado->data_solicitacao = $data_solicitacao;
        $data_entrega = get_post_meta($post->ID, 'chamado_entrega', true);
        if ($data_entrega) {
            $data_entrega = date('Y-m-d\TH:i', $data_entrega);
        }
        $chamado->data_entrega = $data_entrega;
        $chamado->urgencia = get_post_meta($post->ID, 'chamado_urgencia', true);
        $chamado->status = get_post_meta($post->ID, 'chamado_status', true);
        $chamado->ponto_focal = get_post_meta($post->ID, 'chamado_ponto_focal', true);
        $chamado->detalhamento_resolucao = get_post_meta($post->ID, 'chamado_detalhes_resolucao', true);
    }

    // dados gerais
    $args_clientes = array(
        'post_type'          => 'cliente',
        'posts_per_page'     => -1,
        'orderby'            => 'title',
        'order'              => 'ASC',
        'post_status'        => 'publish',
        'suppress_filters'   => false
    );

    $clientes = get_posts($args_clientes);
    $marcas_array = array();
    foreach ($clientes as $cliente) {
        $marcas = get_post_meta($cliente->ID, 'marcas', true);
        foreach ($marcas as $marca) {
            $marcas_array[$cliente->ID][] = $marca['nome-da-marca'];
        }
    }

    $args_urgencia = array(
        'taxonomy' => 'urgencia',
        'hide_empty' => false,
        'meta_key' => 'ordem',
        'orderby' => 'meta_value_num',
        'order' => 'ASC',
        'fields' => 'all',
    );
    $urgencias_terms = get_terms($args_urgencia);
    $urgencias = array();
    foreach ($urgencias_terms as $term) {
        $urgencia = $term;
        $cor = get_term_meta($term->term_id, 'cor', true);
        $ordem = get_term_meta($term->term_id, 'ordem', true);
        $ordem = $ordem ? (int)$ordem : $ordem;
        $icone = get_term_meta($term->term_id, 'icone', true);
        $urgencia->cor = $cor;
        $urgencia->ordem = $ordem;
        $urgencia->icone = $icone;
        $urgencias[] = $urgencia;
    }
    $args_status = array(
        'taxonomy' => 'status-chamado',
        'hide_empty' => false,
        'meta_key' => 'ordem',
        'orderby' => 'meta_value_num',
        'order' => 'ASC',
        'fields' => 'all',
    );
    $status = get_terms($args_status);

    $users = partner_list_admin_users();

    $response = array(
        'success' => true, 'clientes' => $clientes, 'marcas' => $marcas_array, 'urgencias' => $urgencias, 'status' => $status, 'users' => $users,
        'chamado' => $chamado, 'metas' => $metas, 'cliente' => $cliente_selecionado, 'marca' => $marca_selecionada
    );
    wp_send_json($response);
}

add_action('wp_ajax_partner_get_chamado', 'partner_get_chamado');
add_action('wp_ajax_nopriv_partner_get_chamado', 'partner_get_chamado');

function partner_save_chamado()
{
    $response = '';
    $partner_nonce = $_GET['partner_nonce'];
    if (!wp_verify_nonce($partner_nonce, 'partner-nonce')) {
        $response = array(
            'success' => false,
            'message' => __('Requisição inválida!', 'partner')
        );
        wp_send_json($response);
    }
    $post_id = isset($_GET['post_id']) ? $_GET['post_id'] : '';
    $chamado_cliente_id = $_GET['partner-chamado-cliente'];
    $chamado_marca = $_GET['partner-chamado-marca'];
    $chamado_assunto = $_GET['chamado-assunto'];
    $chamado_detalhamento_solicitacao = $_GET['chamado-detalhamento-solicitacao'];

    $chamado_data_solicitacao = $_GET['chamado-data-solicitacao'];
    $chamado_data_solicitacao = strtotime($chamado_data_solicitacao);

    $chamado_data_entrega = $_GET['chamado-data-entrega'];
    $chamado_data_entrega = strtotime($chamado_data_entrega);

    $chamado_urgencia = $_GET['chamado-urgencia'];
    $chamado_ponto_focal = $_GET['chamado-ponto-focal'];
    $chamado_status = $_GET['chamado-status'];
    $chamado_detalhamento_resolucao = $_GET['chamado-detalhamento-resolucao'];

    $curr_user = wp_get_current_user();
    $curr_user_id = $curr_user->ID;

    // data atual
    $data_atual = date('Y-m-d');
    $data_formatada = date('d/m/Y H:i', strtotime($data_atual));
    // pegar o título do post $chamado_cliente_id
    $cliente_nome = get_the_title($chamado_cliente_id);
    $post_title = 'Chamado de ' . $cliente_nome . ', aberto em ' . $data_formatada;


    $args = [
        'post_author' => $curr_user_id,
        'post_status' => 'publish',
        'post_title' => $post_title,
        'post_content' => ' ',
        'post_type' => 'chamados',
        'tax_input'    => array(
            'non_hierarchical_tax' => array('urgencia' => $chamado_urgencia, 'status-chamado' => $chamado_status),
        ),
        'meta_input'   => array(
            'chamado_post' => $chamado_cliente_id,
            'chamado_marca_select' => $chamado_marca,
            'chamado_marca' => $chamado_marca,
            'chamado_assunto' => $chamado_assunto,
            'chamado_detalhes_solicitacao' => $chamado_detalhamento_solicitacao,
            'chamado_solicitacao' => $chamado_data_solicitacao,
            'chamado_entrega' => $chamado_data_entrega,
            'chamado_urgencia' => $chamado_urgencia,
            'chamado_ponto_focal' => $chamado_ponto_focal,
            'chamado_status' => $chamado_status,
            'chamado_detalhes_resolucao' => $chamado_detalhamento_resolucao,
        ),
    ];
    if ($post_id) {
        $args['ID'] = $post_id;
    }
    $post_atualizado = wp_insert_post($args);
    if (is_wp_error($post_atualizado)) {
        $response = [
            'status' => 'error',
            'message' => $post_atualizado->get_error_message(),
        ];
        wp_send_json($response);
    }
    $response = array(
        'success' => true,
        'post_id' => $post_atualizado,
        'chamado_cliente_id' => $chamado_cliente_id,
        'chamado_marca' => $chamado_marca,
        'chamado_assunto' => $chamado_assunto,
        'chamado_detalhamento_solicitacao' => $chamado_detalhamento_solicitacao,
        'chamado_data_solicitacao' => $chamado_data_solicitacao,
        'chamado_data_entrega' => $chamado_data_entrega,
        'chamado_urgencia' => $chamado_urgencia,
        'chamado_ponto_focal' => $chamado_ponto_focal,
        'chamado_status' => $chamado_status,
        'chamado_detalhamento_resolucao' => $chamado_detalhamento_resolucao,
    );

    wp_send_json($response);
}

add_action('wp_ajax_partner_save_chamado', 'partner_save_chamado');
add_action('wp_ajax_nopriv_partner_save_chamado', 'partner_save_chamado');

function partner_add_chamado_edit_js()
{
?>
    <script>
        function partnerAddNewButton(mode, postId) {
            triggerPopupChamados(mode, postId);
            return false;
        }
    </script>
<?php
}

add_action('wp_head', 'partner_add_chamado_edit_js');

// add_action('wp_head', function () {
//     $args = array(
//         'post_type'          => 'cliente',
//         'posts_per_page'     => -1,
//         'orderby'            => 'title',
//         'order'              => 'ASC',
//         'post_status'        => 'publish',
//         'suppress_filters'   => false
//     );

//     $clientes = get_posts($args);
//     $marcas_array = array();
//     foreach ($clientes as $cliente) {
//         $marcas = get_post_meta($cliente->ID, 'marcas', true);
//         foreach ($marcas as $marca) {
//             $marcas_array[$cliente->ID][] = $marca['nome-da-marca'];
//         }
//     }
//     partner_debug($marcas_array);
// });
