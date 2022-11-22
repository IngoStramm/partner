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
}

add_action('cmb2_admin_init', 'partner_register_atendimento_metabox');

function partner_register_atendimento_metabox()
{
    $cmb_cliente = new_cmb2_box(array(
        'id'            => 'partner_atendimento_metabox',
        'title'         => esc_html__('Opções', 'cmb2'),
        'object_types'  => array('atendimentos'), // Post type
        'context'    => 'normal',
        'priority'   => 'high',
        'cmb_styles' => true,
    ));

    $cmb_cliente->add_field(array(
        'name'    => esc_html__('Cliente', 'partner'),
        'desc'    => esc_html__('Selecione qual é o cliente.', 'partner'),
        'id'      => 'atendimento_post',
        'type'    => 'select',
        'options' => function () {
            $options_array = [];
            $options_array[] = __('Selecione uma opção', 'partner');
            $clientes = partner_list_post_clientes_with_id();
            foreach ($clientes as $cliente_id => $cliente_name) {
                $options_array[$cliente_id] = $cliente_name;
            }
            return $options_array;
        },
        'date_format' => 'd/m/Y', 'attributes' => array(
            'required' => 'required',
        ),
    ));

    $cmb_cliente->add_field(array(
        'name'    => esc_html__('Marca', 'partner'),
        'desc'    => esc_html__('Selecione qual é a Marca.', 'partner'),
        'id'      => 'atendimento_marca',
        'type'    => 'select',
        'options' => array('' => __('Nenhum cliente selecionado', 'partner')),
        'date_format' => 'd/m/Y', 'attributes' => array(
            'required' => 'required',
        ),
    ));

    $cmb_cliente->add_field(array(
        'name'    => esc_html__('Assunto', 'partner'),
        'desc'    => esc_html__('Descreva o assunto do atendimento.', 'partner'),
        'id'      => 'atendimento_assunto',
        'type'    => 'text',
        'date_format' => 'd/m/Y', 'attributes' => array(
            'required' => 'required',
        ),
    ));

    $cmb_cliente->add_field(array(
        'name'    => esc_html__('Detalhamento', 'partner'),
        'desc'    => esc_html__('Detalhes sobre a solicitação.', 'partner'),
        'id'      => 'atendimento_detalhes_solicitacao',
        'type'    => 'textarea',
        'date_format' => 'd/m/Y', 'attributes' => array(
            'required' => 'required',
        ),
    ));

    $cmb_cliente->add_field(array(
        'name'    => esc_html__('Data da solicitação', 'partner'),
        // 'desc'    => esc_html__('', 'partner'),
        'id'      => 'atendimento_solicitacao',
        'type' => 'text_datetime_timestamp',
        'date_format' => 'd/m/Y',
        'attributes' => array(
            'required' => 'required',
        ),
    ));

    $cmb_cliente->add_field(array(
        'name'    => esc_html__('Previsão de entrega', 'partner'),
        // 'desc'    => esc_html__('', 'partner'),
        'id'      => 'atendimento_entrega',
        'type' => 'text_datetime_timestamp',
        'date_format' => 'd/m/Y',
        'attributes' => array(
            'required' => 'required',
        ),
    ));

    $cmb_cliente->add_field(array(
        'name'    => esc_html__('Urgência', 'partner'),
        // 'desc'    => esc_html__('', 'partner'),
        'id'      => 'atendimento_urgencia',
        'type' => 'select',
        'options' => array(
            '0' => esc_html__('Selecione uma opção', 'partner'),
            '1' => esc_html__('Não Urgente', 'partner'),
            '2' => esc_html__('Pouco Urgente', 'partner'),
            '3' => esc_html__('Urgente ', 'partner'),
            '4' => esc_html__('Muito Urgente', 'partner'),
            '5' => esc_html__('Emergência', 'partner'),
        ),
        'attributes' => array(
            'required' => 'required',
        ),
    ));

    $cmb_cliente->add_field(array(
        'name'    => esc_html__('Ponto Focal', 'partner'),
        // 'desc'    => esc_html__('', 'partner'),
        'id'      => 'atendimento_ponto_focal',
        'type' => 'select',
        'options' => 'partner_list_admin_users',
        'attributes' => array(
            'required' => 'required',
        ),
    ));

    $cmb_cliente->add_field(array(
        'name'    => esc_html__('Status', 'partner'),
        // 'desc'    => esc_html__('', 'partner'),
        'id'      => 'atendimento_status',
        'type' => 'select',
        'options' => array(
            '0' => esc_html__('Selecione uma opção', 'partner'),
            '1' => esc_html__('Agendar Início (laf)', 'partner'),
            '2' => esc_html__('Executando (laf)', 'partner'),
            '3' => esc_html__('Parado c/ Cliente (cliente)', 'partner'),
            '9' => esc_html__('Sempre em execução', 'partner'),
            '10' => esc_html__('Entregue / Resolvido', 'partner'),
        ),
        'attributes' => array(
            'required' => 'required',
        ),
    ));

    $cmb_cliente->add_field(array(
        'name'    => esc_html__('Detalhe a resolução', 'partner'),
        'desc'    => esc_html__('Detalhes sobre a resolução.', 'partner'),
        'id'      => 'atendimento_detalhes_resolucao',
        'type'    => 'textarea',
        'date_format' => 'd/m/Y', 'attributes' => array(
            'required' => 'required',
        ),
    ));
}
