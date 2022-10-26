<?php

/**
 * partner_settings_metabox
 *
 * @return void
 */
function partner_settings_metabox()
{

    $cmb_options = new_cmb2_box(array(
        'id'           => 'partner_settings_page',
        'title'        => esc_html__('Partner', 'partner'),
        'object_types' => array('options-page'),
        'option_key'      => 'partner_settings', // The option key and admin menu page slug.
        'icon_url'        => 'dashicons-media-spreadsheet', // Menu icon. Only applicable if 'parent_slug' is left empty.
        'capability'        => 'edit_others_pages',
        'parent_slug'       =>  'options-general.php', // Make options page a submenu item of the themes menu.
    ));

    $cmb_options->add_field(array(
        'name'    => esc_html__('Configurações', 'partner'),
        'id'      => 'title_1',
        'type'    => 'title',
    ));

    $cmb_options->add_field(array(
        'name'    => esc_html__('URL da Planilha do Google', 'partner'),
        'id'      => 'googlesheet_url',
        'type'    => 'text',
        'desc'    => esc_html__('A URL da planilha do Google compartilhada com a opção Publicar na Web, apenas a página de Clientes, no formato CSV.', 'partner'),
    ));

    $cmb_options->add_field(array(
        'name'    => esc_html__('Página de login', 'partner'),
        'id'      => 'login_page_id',
        'type'    => 'select',
        'desc'    => esc_html__('Selecione qual é a página de login para onde o usuário não logado será redirecionado. Não pode ser igual à pagina de troca de senha!', 'partner'),
        'options' => 'partner_list_published_pages_except_reset_password_page'
    ));

    $cmb_options->add_field(array(
        'name'    => esc_html__('Página de troca de senha', 'partner'),
        'id'      => 'reset_password_page_id',
        'type'    => 'select',
        'desc'    => esc_html__('Selecione qual é a página de troca de senha para onde o usuário não logado será redirecionado. Não pode ser igual à pagina de login!', 'partner'),
        'options' => 'partner_list_published_pages_except_login_page'
    ));
}

add_action('cmb2_admin_init', 'partner_settings_metabox');

function partner_list_published_pages($exclude_page_id)
{
    $pages = get_pages(array(
        'post_status' => 'publish',
        'post_type' => 'page',
        'sort_order' => 'ASC',
        'sort_column' => 'post_title',
    ));
    $pages_array = [];
    $pages_array[] = __('Selecione uma página', 'partner');
    foreach ($pages as $page) {
        if ($page->ID !== (int)$exclude_page_id)
            $pages_array[$page->ID] = $page->post_title;
    }
    return $pages_array;
}

function partner_list_published_pages_except_reset_password_page()
{
    $reset_password_page_id = partner_get_option('reset_password_page_id');
    $pages_array = partner_list_published_pages($reset_password_page_id);
    return $pages_array;
}

function partner_list_published_pages_except_login_page()
{
    $login_page_id = partner_get_option('login_page_id');
    $pages_array = partner_list_published_pages($login_page_id);
    return $pages_array;
}

/**
 * partner_get_option
 *
 * @param  mixed $key
 * @param  mixed $default
 * @return void
 */
function partner_get_option($key = '', $default = false)
{
    if (function_exists('cmb2_get_option')) {
        return cmb2_get_option('partner_settings', $key, $default);
    }

    $opts = get_option('partner_settings', $default);

    $val = $default;

    if ('all' == $key) {
        $val = $opts;
    } elseif (is_array($opts) && array_key_exists($key, $opts) && false !== $opts[$key]) {
        $val = $opts[$key];
    }

    return $val;
}
