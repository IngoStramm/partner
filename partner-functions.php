<?php

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
    if (!is_user_logged_in() && !is_page($login_page_id)) {
        // echo '<pre>Não logado</pre>';
        wp_redirect(get_page_link($login_page_id));
        exit;
    }
}

add_action('template_redirect', 'partner_redirect_if_user_not_logged_in');
