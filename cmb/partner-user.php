<?php

function partner_register_user_profile_metabox()
{
    $prefix = 'partner_user_';

    $cmb_user = new_cmb2_box(array(
        'id'               => $prefix . 'edit',
        'title'            => esc_html__('User Profile Metabox', 'partner'), // Doesn't output for user boxes
        'object_types'     => array('user'), // Tells CMB2 to use user_meta vs post_meta
        'show_names'       => true,
        'new_user_section' => 'add-new-user', // where form will show on new user page. 'add-existing-user' is only other valid option.
    ));

    $cmb_user->add_field(array(
        'name'    => esc_html__('Dados do Cliente', 'partner'),
        'desc'    => esc_html__('Informações sobre o cliente da Agência.', 'partner'),
        'id'      => $prefix . 'title_cliente_section',
        'type'    => 'title',
    ));


    $cmb_user->add_field(array(
        'name'    => esc_html__('Cliente', 'partner'),
        'desc'    => esc_html__('Selecione qual é o cliente deste usuário.', 'partner'),
        'id'      => $prefix . 'cliente',
        'type'    => 'select',
        'options_cb' => 'partner_list_post_clientes'
    ));

    $cmb_user->add_field(array(
        'name'    => esc_html__('Shortcode Google Drive', 'partner'),
        // 'desc'    => esc_html__('Selecione qual é o cliente deste usuário.', 'partner'),
        'id'      => $prefix . 'shortcode_gdrive',
        'type'    => 'text'
    ));

    $cmb_user->add_field(array(
        'name'    => esc_html__('Url do relatório do Google Ads', 'partner'),
        'desc'    => __('No Looker Studio, antigo Data Studio, usar a opção de <code>Compartilhar > Incorporar relatório > Incorporar URL</code> para obter a URL do relatório.', 'partner'),
        'id'      => $prefix . 'dados_site',
        'type'    => 'text'
    ));

    $cmb_user->add_field(array(
        'name'    => esc_html__('Url do relatório do Analytics', 'partner'),
        'desc'    => __('No Looker Studio, antigo Data Studio, usar a opção de <code>Compartilhar > Incorporar relatório > Incorporar URL</code> para obter a URL do relatório.', 'partner'),
        'id'      => $prefix . 'dados_midia',
        'type'    => 'text'
    ));


    $cmb_user->add_field(array(
        'name'    => esc_html__('Dados do Atendimento', 'partner'),
        'desc'    => esc_html__('Informações sobre o responsável em atender o(s) cliente(s).', 'partner'),
        'id'      => $prefix . 'title_responsavel_section',
        'type'    => 'title',
    ));

    $cmb_user->add_field(array(
        'name' => esc_html__('Imagem do usuário', 'cmb2'),
        'id'   => $prefix . 'image',
        'type' => 'file',
    ));

    $cmb_user->add_field(array(
        'name'    => esc_html__('Descrição do usuário', 'cmb2'),
        'id'      => $prefix . 'description',
        'type'    => 'wysiwyg',
        'options' => array(
            'textarea_rows' => 5,
        ),
    ));

    $cmb_user->add_field(array(
        'name'    => esc_html__('Link para a reunião', 'cmb2'),
        'id'      => $prefix . 'meeting',
        'type'    => 'text_url',
        'attributes' => array(
            'placeholder' => 'https://',
        )
    ));

    $cmb_user->add_field(array(
        'name'    => esc_html__('E-mail', 'cmb2'),
        'id'      => $prefix . 'email',
        'type'    => 'text_email',
        'attributes' => array(
            'placeholder' => 'exemplo@email.com',
        )
    ));

    $cmb_user->add_field(array(
        'name'    => esc_html__('Telefone', 'cmb2'),
        'id'      => $prefix . 'phone',
        'type'    => 'text',
        'attributes' => array(
            'placeholder' => '(99) 99999-9999',
            'class'        => 'phone-mask',
            'maxlength'    => '15',
        )
    ));
}

add_action('cmb2_admin_init', 'partner_register_user_profile_metabox');


// add_action('wp_head', 'partner_list_planilha_clientes_name');
