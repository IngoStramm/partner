<?php

add_action('cmb2_admin_init', 'partner_register_cliente_metabox');

function partner_register_cliente_metabox()
{
    $cmb_cliente = new_cmb2_box(array(
        'id'            => 'partner_cliente_metabox',
        'title'         => esc_html__('Opções da Planilha', 'cmb2'),
        'object_types'  => array('cliente'), // Post type
    ));

    $cmb_cliente->add_field(array(
        'name'    => esc_html__('Cliente', 'partner'),
        'desc'    => esc_html__('Selecione qual é o cliente da planilha.', 'partner'),
        'id'      => 'cliente_planilha',
        'type'    => 'select',
        'options' => 'partner_list_planilha_clientes_name'
    ));

    $cmb_cliente->add_field(array(
        'name'    => esc_html__('Sucesso do Cliente', 'partner'),
        // 'desc'    => esc_html__('', 'partner'),
        'id'      => 'chamado_sucesso_cliente',
        'type' => 'select',
        'options' => 'partner_list_editor_users',
        'attributes' => array(
            'required' => 'required',
        ),
    ));

    $cmb_cliente->add_field(array(
        'name'    => esc_html__('Contato de Emergência', 'partner'),
        // 'desc'    => esc_html__('', 'partner'),
        'id'      => 'chamado_contato_emergencia',
        'type' => 'select',
        'options' => 'partner_list_editor_users',
        'attributes' => array(
            'required' => 'required',
        ),
    ));
}
