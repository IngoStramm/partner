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
        'name'    => esc_html__('Cache da Planílha do Google', 'partner'),
        'id'      => 'transient_active',
        'type'    => 'radio',
        'desc'    => esc_html__('A memória em cache ajuda com a performance do site, ao salvar no cache os dados da planílha. Porém, as vezes o cache pode atrapalhar, ao impedir a atualização da planilha. Desabilite o cache se estiver com problemas em ver a planilha atualizada.', 'partner'),
        'default' => '1',
        'options' => array(
            '1' => esc_html__('Habilitar Cache', 'partner'),
            '0' => esc_html__('Desabilitar Cache', 'partner'),
        ),
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

    $cmb_options->add_field(array(
        'name'    => esc_html__('Status Entregue', 'partner'),
        'id'      => 'chamado_status_entregue',
        'type'    => 'select',
        'desc'    => esc_html__('Selecione qual é o status que equivale ao chamado finalizado/entregue.', 'partner'),
        'options' => function () {
            $statuses = partner_get_status_list();
            $options = array();
            $options[] = esc_html__('Selecione um status', 'partner');
            foreach ($statuses as $id => $status) {
                $options[$id] = $status;
            }
            return $options;
        }
    ));
}

add_action('cmb2_admin_init', 'partner_settings_metabox');
